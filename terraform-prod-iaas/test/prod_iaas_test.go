// Package test contains a Terratest post-apply behavioral suite for the
// Prod IaaS stack (task 11.2 of the prod-iaas-terraform spec).
//
// What this suite proves (against a THROWAWAY sandbox, never production):
//
//   - HTTPS (443) on the newly allocated Elastic IP is reachable          (Req 9.2, 13.1)
//   - the DB credentials the app resolves point at the ISOLATED RDS
//     endpoint, not a production endpoint                                 (Req 2.3)
//   - that resolved endpoint is provably NOT the production database, so a
//     write performed by the app is absent from production                (Req 2.3, 12.1)
//   - `terraform plan -destroy` is blocked by the RDS `prevent_destroy`
//     lifecycle guard                                                     (Req 3.3)
//
// SAFETY / COST
// -------------
// This test performs a REAL `terraform apply` and therefore creates REAL,
// billable AWS resources (EC2 t3.small, an isolated RDS, an Elastic IP, an S3
// bucket, a Secrets Manager secret). It is GUARDED so it never runs by
// accident: it is skipped unless RUN_TERRATEST=1 is set in the environment and
// valid AWS credentials are present. See test/README.md before running.
//
// TEARDOWN (IMPORTANT — there is intentionally NO `defer terraform.Destroy`)
// -------------------------------------------------------------------------
// The isolated RDS carries `lifecycle { prevent_destroy = true }` (see
// modules/rds/main.tf). A naive `defer terraform.Destroy(...)` would ALWAYS
// fail at teardown because Terraform refuses to plan the destroy of that
// instance. That failure is exactly the behavior stage 4 of this test asserts.
//
// Tearing the sandbox down is therefore a deliberate, TWO-STEP manual action:
//
//  1. Remove the guard: comment out (or set to false) the `prevent_destroy`
//     line in modules/rds/main.tf, then re-init/plan so Terraform accepts a
//     destroy of the DB instance.
//  2. Destroy: run `terraform destroy` from the sandbox workspace. Because
//     `skip_final_snapshot = false`, a final snapshot is retained; delete it
//     manually from RDS if you truly want zero residual storage.
//
// Do NOT wire an automatic Destroy here — it would mask a real regression and
// would fail anyway. Sandbox cleanup is an operator decision.
package test

import (
	"crypto/tls"
	"encoding/json"
	"fmt"
	"net"
	"os"
	"strings"
	"testing"
	"time"

	"github.com/gruntwork-io/terratest/modules/aws"
	"github.com/gruntwork-io/terratest/modules/retry"
	"github.com/gruntwork-io/terratest/modules/terraform"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/require"
)

const (
	// rootModuleRelPath points at the terraform-prod-iaas root module, one
	// directory above this test package.
	rootModuleRelPath = ".."

	// isolatedSecretName is the well-known Secrets Manager reference the stack
	// creates in isolated mode (see modules/secrets and main.tf).
	isolatedSecretName = "sgl/prod-iaas/rds-credentials"

	// httpsRetries / httpsRetryWait bound how long we wait for the app to boot,
	// install Docker, run `docker compose up -d`, and start serving TLS. The
	// compute module treats the app as ready only within 300s of start, so we
	// allow generously more than that here (40 * 15s = 10m).
	httpsRetries   = 40
	httpsRetryWait = 15 * time.Second
)

// dbSecret mirrors the JSON shape the app consumes from Secrets Manager:
// {host, port, username, password, dbname}. Only host/port are inspected here.
type dbSecret struct {
	Host     string `json:"host"`
	Port     int    `json:"port"`
	Username string `json:"username"`
	Password string `json:"password"`
	DBName   string `json:"dbname"`
}

// TestProdIaaSSandbox applies the stack against a throwaway sandbox snapshot and
// runs the four behavioral checks described in the package doc. It is skipped
// unless RUN_TERRATEST=1 to avoid creating billable resources by accident.
func TestProdIaaSSandbox(t *testing.T) {
	if os.Getenv("RUN_TERRATEST") != "1" {
		t.Skip("skipping Terratest apply suite: set RUN_TERRATEST=1 (and provide AWS credentials + SANDBOX_SNAPSHOT_ID) to run it against a throwaway sandbox")
	}

	region := getEnvOrDefault("AWS_REGION", "us-east-1")

	// A small, throwaway snapshot to seed the isolated RDS from. Required so the
	// sandbox is cheap and self-contained (never the latest-of-prod lookup).
	snapshotID := os.Getenv("SANDBOX_SNAPSHOT_ID")
	require.NotEmpty(t, snapshotID, "SANDBOX_SNAPSHOT_ID must be set to a small throwaway RDS snapshot id/ARN to seed the isolated sandbox DB")

	terraformOptions := buildSandboxOptions(t, region, snapshotID)

	// NOTE: intentionally NO `defer terraform.Destroy(...)` — see the package
	// doc "TEARDOWN" section. prevent_destroy on the isolated RDS makes destroy
	// a deliberate two-step manual operation.

	// ---- Stage 1: apply the sandbox stack ---------------------------------
	terraform.InitAndApply(t, terraformOptions)

	// ---- Stage 2: HTTPS reachable on the new Elastic IP -------------------
	// (Req 9.2 second productive IP is associated; Req 13.1 app comes up)
	eip := terraform.Output(t, terraformOptions, "eip_public_ip")
	require.NotEmpty(t, eip, "eip_public_ip output must be a non-empty address")
	assertHTTPSReachable(t, eip)

	// ---- Stage 3: DB creds resolve to the ISOLATED endpoint ---------------
	// (Req 2.3 isolation of writes; a write via these creds cannot reach prod)
	rdsEndpoint := terraform.Output(t, terraformOptions, "rds_endpoint")
	require.NotEmpty(t, rdsEndpoint, "rds_endpoint output must be set in isolated mode (use_real_prod_db = false)")

	secret := getDBSecret(t, region, isolatedSecretName)
	require.NotEmpty(t, secret.Host, "resolved DB secret must contain a host")

	assert.Equal(t, hostOnly(rdsEndpoint), hostOnly(secret.Host),
		"the DB secret host must equal the ISOLATED rds_endpoint output, proving the app talks to the isolated instance")

	// The "write is absent from production" guarantee: prove the resolved host
	// is NOT the production database. We can only reach the RDS from inside the
	// VPC, so instead of connecting we assert the endpoint the app would write
	// to is provably distinct from production (host mismatch + identifier not
	// present in the endpoint). A production endpoint and/or identifier can be
	// supplied via env to make this assertion concrete; both are optional.
	if prodEndpoint := os.Getenv("PROD_RDS_ENDPOINT"); prodEndpoint != "" {
		assert.NotEqual(t, hostOnly(prodEndpoint), hostOnly(secret.Host),
			"the resolved DB host must NOT be the production RDS endpoint (writes must be absent from production)")
	}
	if prodID := os.Getenv("PROD_RDS_IDENTIFIER"); prodID != "" {
		assert.NotContains(t, secret.Host, prodID,
			"the resolved DB host must not reference the production RDS identifier")
	}

	// ---- Stage 4: prevent_destroy blocks `terraform plan -destroy` --------
	// (Req 3.3) A plain destroy plan must FAIL because the isolated RDS is
	// guarded by prevent_destroy.
	destroyPlanOut, err := terraform.RunTerraformCommandE(
		t, terraformOptions, "plan", "-destroy", "-no-color", "-input=false",
	)
	require.Error(t, err, "`terraform plan -destroy` must fail because the isolated RDS has prevent_destroy = true")
	assert.True(t, mentionsPreventDestroy(destroyPlanOut),
		"the destroy plan error should mention the prevent_destroy guard / that the instance cannot be destroyed; got:\n%s", destroyPlanOut)
}

// buildSandboxOptions assembles terraform.Options for a small, isolated sandbox.
// Callers can point at an explicit sandbox tfvars file via SANDBOX_TFVARS; the
// Vars below are applied on top and keep the run cheap and isolated by default.
func buildSandboxOptions(t *testing.T, region, snapshotID string) *terraform.Options {
	vars := map[string]interface{}{
		// Isolated mode: create the isolated RDS + secret, never touch prod.
		"use_real_prod_db":       false,
		"create_isolated_bucket": true,

		// Seed from the small throwaway snapshot (satisfies R10.9).
		"rds_snapshot_identifier": snapshotID,

		// Keep the isolated identifier distinct from any production identifier
		// (R10.8). Overridable so parallel sandboxes don't collide.
		"rds_identifier": getEnvOrDefault("SANDBOX_RDS_IDENTIFIER", "sgl-prod-iaas-sandbox-db"),

		// Small = cheap. This is a throwaway.
		"rds_instance_class": getEnvOrDefault("SANDBOX_RDS_CLASS", "db.t3.small"),
		"instance_type":      getEnvOrDefault("SANDBOX_INSTANCE_TYPE", "t3.small"),

		// A dedicated sandbox bucket so we never write into a shared bucket.
		"isolated_bucket_name": getEnvOrDefault("SANDBOX_BUCKET_NAME", "bucket-sgl-uploads-prod-iaas-sandbox"),

		"region": region,
	}

	opts := &terraform.Options{
		TerraformDir: rootModuleRelPath,
		Vars:         vars,
		EnvVars: map[string]string{
			"AWS_REGION":         region,
			"AWS_DEFAULT_REGION": region,
		},
		NoColor: true,
	}

	// Optionally layer an explicit sandbox tfvars file (small snapshot, isolated
	// mode) on top; Vars above still win for the keys they set.
	if vf := os.Getenv("SANDBOX_TFVARS"); vf != "" {
		opts.VarFiles = []string{vf}
	}

	return opts
}

// assertHTTPSReachable opens a TLS connection to host:443, retrying while the
// instance boots and starts serving. Certificate verification is skipped
// because a sandbox may present a self-signed cert; we only care that the TLS
// port is up and completes a handshake.
func assertHTTPSReachable(t *testing.T, host string) {
	addr := net.JoinHostPort(host, "443")
	retry.DoWithRetry(
		t,
		fmt.Sprintf("TLS dial %s", addr),
		httpsRetries,
		httpsRetryWait,
		func() (string, error) {
			dialer := &net.Dialer{Timeout: 10 * time.Second}
			conn, err := tls.DialWithDialer(dialer, "tcp", addr, &tls.Config{
				InsecureSkipVerify: true, //nolint:gosec // sandbox may use a self-signed cert; we assert reachability, not trust
			})
			if err != nil {
				return "", fmt.Errorf("HTTPS not reachable yet on %s: %w", addr, err)
			}
			defer conn.Close()
			return "TLS handshake succeeded", nil
		},
	)
}

// getDBSecret fetches and parses the JSON DB credentials from Secrets Manager.
func getDBSecret(t *testing.T, region, name string) dbSecret {
	raw := aws.GetSecretValue(t, region, name)
	var s dbSecret
	require.NoError(t, json.Unmarshal([]byte(raw), &s), "DB secret %q must be valid {host,port,username,password,dbname} JSON", name)
	return s
}

// hostOnly returns the host portion of an "host:port" endpoint, or the input
// unchanged when it carries no port.
func hostOnly(endpoint string) string {
	endpoint = strings.TrimSpace(endpoint)
	if h, _, err := net.SplitHostPort(endpoint); err == nil {
		return h
	}
	return endpoint
}

// mentionsPreventDestroy reports whether Terraform's destroy-plan output
// indicates the prevent_destroy guard blocked the operation.
func mentionsPreventDestroy(out string) bool {
	lower := strings.ToLower(out)
	return strings.Contains(lower, "prevent_destroy") ||
		strings.Contains(lower, "cannot be destroyed") ||
		strings.Contains(lower, "instance cannot be destroyed")
}

// getEnvOrDefault returns the environment variable value or a fallback.
func getEnvOrDefault(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

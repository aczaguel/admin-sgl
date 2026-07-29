# Prod IaaS — Terratest behavioral suite (task 11.2)

This directory holds a [Terratest](https://terratest.gruntwork.io/) (Go) suite that runs
**post-apply behavioral checks** against a **throwaway sandbox** of the `terraform-prod-iaas`
root module. It complements the plan-based property assertions (`../tests/*.tftest.hcl`) and the
OPA/conftest policies (task 11.1) by verifying real, running behavior after `terraform apply`.

## What it verifies

| Stage | Check | Requirement |
|------|-------|-------------|
| 1 | `terraform apply` from a small sandbox snapshot in isolated mode | 12.1 |
| 2 | HTTPS (443) on the newly allocated Elastic IP completes a TLS handshake | 9.2, 13.1 |
| 3 | The DB secret `host` resolves to the **isolated** `rds_endpoint`, not a production endpoint | 2.3 |
| 4 | `terraform plan -destroy` **fails**, blocked by the RDS `prevent_destroy` guard | 3.3 |

The "a write is absent from production" guarantee is enforced in stage 3: the app's resolved DB
host is asserted to be the isolated endpoint and (when `PROD_RDS_ENDPOINT` / `PROD_RDS_IDENTIFIER`
are provided) provably different from production, so any write the app makes cannot land on the
production database.

## Prerequisites

- **Go** 1.21+ (`go version`).
- **Terraform** 1.5+ on `PATH` (the module pins `>= 1.5.0`).
- **AWS credentials** with permission to create EC2, RDS, EIP, S3, IAM, and Secrets Manager
  resources in the target account/region (e.g. via `AWS_PROFILE` or standard `AWS_*` env vars).
- A **small, throwaway RDS snapshot** to seed the sandbox DB from, referenced by
  `SANDBOX_SNAPSHOT_ID`. Use a tiny snapshot to keep cost and apply time low.
- The remote state backend used by the root module (or run against a sandbox backend). Adjust the
  backend before running if you do not want the sandbox to share the configured state key.

## Environment variables

| Variable | Required | Purpose |
|---------|----------|---------|
| `RUN_TERRATEST` | yes (`=1`) | Guard. The suite is **skipped** unless this is set. |
| `SANDBOX_SNAPSHOT_ID` | yes | Small throwaway snapshot id/ARN to seed the isolated RDS. |
| `AWS_REGION` | no (default `us-east-1`) | Target region. |
| `SANDBOX_TFVARS` | no | Path to an optional sandbox tfvars file layered under the built-in vars. |
| `SANDBOX_RDS_IDENTIFIER` | no (default `sgl-prod-iaas-sandbox-db`) | Isolated RDS identifier. |
| `SANDBOX_RDS_CLASS` | no (default `db.t3.small`) | Cheap RDS class for the sandbox. |
| `SANDBOX_INSTANCE_TYPE` | no (default `t3.small`) | Cheap EC2 type for the sandbox. |
| `SANDBOX_BUCKET_NAME` | no (default `bucket-sgl-uploads-prod-iaas-sandbox`) | Dedicated sandbox bucket. |
| `PROD_RDS_ENDPOINT` | no | If set, stage 3 asserts the resolved host differs from it. |
| `PROD_RDS_IDENTIFIER` | no | If set, stage 3 asserts the resolved host does not reference it. |

## How to run

```bash
# from terraform-prod-iaas/test/
go mod download            # first time; needs network access to fetch modules

RUN_TERRATEST=1 \
AWS_REGION=us-east-1 \
SANDBOX_SNAPSHOT_ID=rds:my-small-throwaway-snapshot \
go test -v -timeout 60m ./...
```

Without `RUN_TERRATEST=1` the test is skipped, so `go test ./...` is safe to run in CI as a
compile check that never touches AWS.

## Cost & safety warning

Running with `RUN_TERRATEST=1` performs a **real `terraform apply`** and creates **real, billable**
AWS resources (EC2, an isolated RDS, an Elastic IP, an S3 bucket, a Secrets Manager secret).
Order-of-magnitude cost is a few dollars/day while the sandbox is up. **Always tear the sandbox
down when finished.**

## Teardown (two-step, manual — by design)

The isolated RDS has `lifecycle { prevent_destroy = true }`, which is exactly what stage 4 asserts.
That guard also blocks any `terraform destroy`, so this suite intentionally has **no**
`defer terraform.Destroy` (it would always fail and mask the assertion). To tear down the sandbox:

1. **Remove the guard**: in `../modules/rds/main.tf`, comment out (or set to `false`) the
   `prevent_destroy = true` line, then `terraform init`/`terraform plan` so Terraform will accept a
   destroy of the DB instance.
2. **Destroy**: run `terraform destroy` in the sandbox workspace. Because `skip_final_snapshot =
   false`, a **final snapshot is retained** — delete it manually from RDS if you want zero residual
   storage. Also remove the sandbox S3 bucket contents if the bucket blocks deletion.

Restore the `prevent_destroy` line afterwards so the guard stays in place for real environments.

## Note on offline environments

If Go module downloads are unavailable (offline), `go mod download` / `go build` cannot fetch
`terratest` and `testify`. The Go source in `prod_iaas_test.go` is written to be syntactically
correct and self-contained; run `go vet ./...` once module access is available to confirm it
compiles in your environment.

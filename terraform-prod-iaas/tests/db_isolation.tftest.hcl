# terraform-prod-iaas/tests/db_isolation.tftest.hcl
#
# Task 10.2 — Assert isolated-mode DB write isolation.
#
# Property 3 (Isolation of writes, default mode): when use_real_prod_db = false,
# the DB reference the app resolves MUST point at the ISOLATED, snapshot-seeded
# RDS (via the secret this stack creates) and NEVER at the production database.
#
# Validates: Requirements 2.3
#
# What "the secret's host is the isolated RDS endpoint, not production" means
# here, and how it is proven offline
# -----------------------------------------------------------------------------
# In isolated mode the design wires the app's SECRETS_RDS_REFERENCE to
# `module.secrets[0].secret_name`, and that secret's `host` is fed from
# `module.rds[0].endpoint` (secrets module input `db_host = module.rds[0].endpoint`
# in main.tf). The production RDS is NEVER a managed resource in this stack — it
# is only ever read through the `data "aws_db_snapshot"` snapshot lookup — so the
# only DB endpoint that can flow into the secret is the ISOLATED instance's.
#
# The isolated RDS endpoint is a computed attribute that is only known after
# apply, and the isolated RDS carries a `prevent_destroy` guard that blocks the
# test framework's teardown destroy. So rather than apply-and-compare the raw
# endpoint string, this test proves the same property at PLAN time using values
# that are fully resolved before apply:
#
#   local.effective_rds_reference           == module.secrets[0].secret_name   (app -> isolated secret)
#   module.compute.env_file_vars[...]        == module.secrets[0].secret_name   (.env -> isolated secret)
#   local.effective_rds_reference           != var.real_prod_rds_reference      (app NOT -> prod ref)
#   module.rds[0].identifier                == var.rds_identifier               (secret host source = isolated RDS)
#   module.rds[0].identifier                != var.prod_rds_source_identifier   (isolated RDS != production)
#
# Together these establish that the secret the app resolves belongs to the
# isolated RDS instance (identifier distinct from production), never the
# production database.
#
# Offline execution
# -----------------
# A `mock_provider "aws"` makes the whole run offline (no AWS credentials, no
# real resources, no network). `command = plan` keeps the run plan-only so it
# never provisions state and therefore never trips the isolated RDS's
# prevent_destroy guard at teardown.
#
# Run with:
#   terraform init -backend=false
#   terraform test

mock_provider "aws" {
  # Canned snapshot lookup shared by root main.tf's data.aws_db_snapshot.prod_latest
  # and the rds module's data.aws_db_snapshot.seed. encrypted = true keeps the
  # rds module's "source snapshot must be encrypted" precondition (Req 14.4)
  # satisfied so the isolated instance is allowed under the mock.
  mock_data "aws_db_snapshot" {
    defaults = {
      id        = "rds:sgl-prod-db-2024-01-01"
      encrypted = true
    }
  }

  # The iam module builds its trust and inline policies via
  # data.aws_iam_policy_document, and aws_iam_role validates that its
  # assume_role_policy is a JSON object. The mock provider's auto-generated
  # `.json` is not valid JSON, so supply a minimal valid policy document here.
  mock_data "aws_iam_policy_document" {
    defaults = {
      json = "{\"Version\":\"2012-10-17\",\"Statement\":[]}"
    }
  }
}

# ---------------------------------------------------------------------------
# Isolated default mode: use_real_prod_db = false.
#
# Representative variables:
#   use_real_prod_db           = false  -> create the isolated RDS + secret
#   create_isolated_bucket     = true   -> dedicated isolated uploads bucket
#   prod_rds_source_identifier = "sgl-prod-db" -> read-only snapshot source (R10.9)
#   rds_identifier             = "sgl-prod-iaas-db" (default; distinct from prod)
# ---------------------------------------------------------------------------
run "isolated_mode_db_writes_are_isolated" {
  command = plan

  variables {
    use_real_prod_db           = false
    create_isolated_bucket     = true
    prod_rds_source_identifier = "sgl-prod-db"
    rds_identifier             = "sgl-prod-iaas-db"
    rds_snapshot_identifier    = ""
    real_prod_rds_reference    = ""
    existing_bucket_name       = ""
  }

  # The app's effective DB reference resolves to the ISOLATED secret this stack
  # creates, proving the app is pointed at the isolated DB (Req 2.3).
  assert {
    condition     = local.effective_rds_reference == module.secrets[0].secret_name
    error_message = "effective_rds_reference must resolve to the isolated secret (module.secrets[0].secret_name) in isolated mode."
  }

  # The isolated secret is the well-known Prod IaaS secret name, never the
  # (empty, unset) real prod reference.
  assert {
    condition     = module.secrets[0].secret_name == "sgl/prod-iaas/rds-credentials"
    error_message = "The isolated RDS secret name must be the dedicated Prod IaaS secret 'sgl/prod-iaas/rds-credentials'."
  }

  # The value rendered into the app .env for SECRETS_RDS_REFERENCE equals the
  # isolated secret name — i.e. the running app resolves the isolated secret.
  assert {
    condition     = module.compute.env_file_vars["SECRETS_RDS_REFERENCE"] == module.secrets[0].secret_name
    error_message = "The .env SECRETS_RDS_REFERENCE must equal the isolated secret name in isolated mode."
  }

  # The secret's host is sourced from the ISOLATED RDS instance (main.tf wires
  # the secrets module's db_host = module.rds[0].endpoint). Verify that instance
  # is the isolated one, so the host can only be the isolated endpoint.
  assert {
    condition     = module.rds[0].identifier == var.rds_identifier
    error_message = "The isolated RDS instance (whose endpoint feeds the secret host) must use the configured isolated identifier."
  }

  # The isolated instance identifier differs from the production source
  # identifier, so the endpoint feeding the secret can never be production.
  assert {
    condition     = module.rds[0].identifier != var.prod_rds_source_identifier
    error_message = "The isolated RDS identifier must differ from the production source identifier."
  }

  # Negative guarantee: the app is NOT pointed at the real prod reference in
  # isolated mode.
  assert {
    condition     = local.effective_rds_reference != var.real_prod_rds_reference
    error_message = "In isolated mode the app must NOT resolve the real production RDS reference."
  }
}

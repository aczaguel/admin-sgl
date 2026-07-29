# terraform-prod-iaas/tests/toggle_counts.tftest.hcl
#
# Task 10.3 — Assert the toggle `count = 0` / `count = 1` behavior across the
# four bucket/DB target combinations, and that the effective locals point at the
# right target for each toggle position.
#
# Property 8 (Toggle exclusivity): the two target toggles collapse to exactly
# one target each — either the isolated resource this stack creates OR the
# existing/real resource it reuses, never both, never neither.
#
# Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5
#
#   R10.1  isolated bucket toggle ON  -> exactly one isolated bucket created.
#   R10.2  isolated bucket toggle OFF -> reuse existing bucket by name, create none.
#   R10.3  real prod DB toggle OFF    -> exactly one Isolated_RDS + one secret.
#   R10.4  real prod DB toggle ON     -> rds module count 0 AND secret module count 0.
#   R10.5  real prod DB toggle ON     -> app points at the real prod DB reference.
#
# The toggle counts (`length(module.rds)`, `length(module.secrets)`,
# `length(module.uploads_iaas)`) are statically determined by the toggle
# variables, and the `effective_*` locals resolve from those variables, so
# `command = plan` is sufficient — no AWS resources are provisioned.
#
# Run with:
#   terraform init -backend=false
#   terraform test

# ---------------------------------------------------------------------------
# Offline mocking
# ---------------------------------------------------------------------------
# When the real-prod-DB toggle is OFF the read-only `data "aws_db_snapshot"
# "prod_latest"` lookup in main.tf fires (count = 1). Against the live provider
# it needs AWS credentials and a real snapshot; mocking the aws provider makes
# it resolve to canned defaults so the whole file runs fully offline. The mock
# only feeds the read-only lookup — it never alters any toggle variable, so the
# module counts and effective locals under test evaluate exactly as they would
# against real inputs.
mock_provider "aws" {
  mock_data "aws_db_snapshot" {
    defaults = {
      id        = "rds:sgl-prod-db-2024-01-01"
      encrypted = true
    }
  }

  # The iam module (always instantiated) feeds data.aws_iam_policy_document.json
  # into aws_iam_role.assume_role_policy, which the provider validates as a JSON
  # object at plan time. A bare mock would generate a non-JSON placeholder string
  # and fail that validation, so pin the mocked policy JSON to a valid document.
  mock_data "aws_iam_policy_document" {
    defaults = {
      json = "{\"Version\":\"2012-10-17\",\"Statement\":[]}"
    }
  }
}

# ---------------------------------------------------------------------------
# R10.4 / R10.5 — real prod DB toggle ENABLED (cutover-rehearsal mode).
# The isolated RDS and its secret are NOT created (module count = 0) and the
# app's effective RDS reference points at the operator-supplied real prod
# Secrets Manager reference.
# ---------------------------------------------------------------------------
run "use_real_prod_db_true_zeroes_rds_and_secret" {
  command = plan

  variables {
    use_real_prod_db         = true
    real_prod_rds_reference  = "sgl/prod/rds-credentials"                                             # R10.6 satisfied
    real_prod_rds_secret_arn = "arn:aws:secretsmanager:us-east-1:123456789012:secret:sgl/prod/rds-Ab" # scoped into IAM
    # keep the other cross-variable rules satisfied so the plan reaches assertions:
    create_isolated_bucket     = true               # R10.7 ok
    rds_identifier             = "sgl-prod-iaas-db" # distinct from prod source -> R10.8 ok
    prod_rds_source_identifier = ""                 # use_real_prod_db = true short-circuits R10.9
  }

  # R10.4: isolated RDS and secret modules are both gated off.
  assert {
    condition     = length(module.rds) == 0
    error_message = "R10.4: use_real_prod_db = true must set module.rds count to 0."
  }

  assert {
    condition     = length(module.secrets) == 0
    error_message = "R10.4: use_real_prod_db = true must set module.secrets count to 0."
  }

  # R10.5: the app's effective RDS reference is the real prod reference.
  assert {
    condition     = local.effective_rds_reference == var.real_prod_rds_reference
    error_message = "R10.5: use_real_prod_db = true must point effective_rds_reference at real_prod_rds_reference."
  }
}

# ---------------------------------------------------------------------------
# R10.3 — real prod DB toggle DISABLED (safe isolated default).
# Exactly one Isolated_RDS instance and exactly one associated secret exist.
# ---------------------------------------------------------------------------
run "use_real_prod_db_false_creates_rds_and_secret" {
  command = plan

  variables {
    use_real_prod_db           = false
    prod_rds_source_identifier = "sgl-prod-db" # snapshot source -> R10.9 ok
    rds_snapshot_identifier    = ""
    rds_identifier             = "sgl-prod-iaas-db" # distinct -> R10.8 ok
    create_isolated_bucket     = true               # R10.7 ok
    real_prod_rds_reference    = ""                 # R10.6 ok (toggle off)
  }

  # R10.3: exactly one isolated RDS module instance.
  assert {
    condition     = length(module.rds) == 1
    error_message = "R10.3: use_real_prod_db = false must create exactly one module.rds instance."
  }

  # R10.3: exactly one associated secret module instance.
  assert {
    condition     = length(module.secrets) == 1
    error_message = "R10.3: use_real_prod_db = false must create exactly one module.secrets instance."
  }
}

# ---------------------------------------------------------------------------
# R10.2 — isolated bucket toggle DISABLED: reuse an existing bucket by name and
# create no new bucket. The effective bucket name is the configured existing
# bucket name.
# ---------------------------------------------------------------------------
run "create_isolated_bucket_false_reuses_existing" {
  command = plan

  variables {
    create_isolated_bucket = false
    existing_bucket_name   = "bucket-sgl-uploads-prod" # R10.7 satisfied
    # keep the DB-side rules satisfied so the plan reaches assertions:
    use_real_prod_db           = false
    prod_rds_source_identifier = "sgl-prod-db" # R10.9 ok
    rds_snapshot_identifier    = ""
    rds_identifier             = "sgl-prod-iaas-db" # R10.8 ok
    real_prod_rds_reference    = ""                 # R10.6 ok
  }

  # R10.2: no isolated bucket is created.
  assert {
    condition     = length(module.uploads_iaas) == 0
    error_message = "R10.2: create_isolated_bucket = false must not create module.uploads_iaas."
  }

  # R10.2: the effective bucket name resolves to the configured existing name.
  assert {
    condition     = local.effective_bucket_name == var.existing_bucket_name
    error_message = "R10.2: create_isolated_bucket = false must resolve effective_bucket_name to existing_bucket_name."
  }
}

# ---------------------------------------------------------------------------
# R10.1 — isolated bucket toggle ENABLED: create exactly one dedicated isolated
# bucket for Prod IaaS.
# ---------------------------------------------------------------------------
run "create_isolated_bucket_true_creates_one" {
  command = plan

  variables {
    create_isolated_bucket = true
    isolated_bucket_name   = "bucket-sgl-uploads-prod-iaas"
    existing_bucket_name   = "" # ignored when toggle is on
    # keep the DB-side rules satisfied so the plan reaches assertions:
    use_real_prod_db           = false
    prod_rds_source_identifier = "sgl-prod-db" # R10.9 ok
    rds_snapshot_identifier    = ""
    rds_identifier             = "sgl-prod-iaas-db" # R10.8 ok
    real_prod_rds_reference    = ""                 # R10.6 ok
  }

  # R10.1: exactly one isolated bucket module instance.
  assert {
    condition     = length(module.uploads_iaas) == 1
    error_message = "R10.1: create_isolated_bucket = true must create exactly one module.uploads_iaas instance."
  }
}

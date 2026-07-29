# terraform-prod-iaas/tests/toggle_validation.tftest.hcl
#
# Task 1.4 — Plan-time validation assertions for the toggle/validation rules.
#
# Property 8 (Toggle exclusivity): invalid toggle combinations MUST fail at
# `terraform plan` before any real resource is created.
#
# Validates: Requirements 10.6, 10.7, 10.8, 10.9
#
# The cross-variable rules are enforced by the `lifecycle { precondition }`
# blocks on the `terraform_data.*` resources in checks.tf. A failing
# precondition halts `terraform plan` with a hard error (not a soft warning),
# which is exactly what Requirements 10.6-10.9 mandate
# ("SHALL halt the plan before creating any resources and return a validation
# error message ...").
#
# Each `run` block below drives one invalid combination and asserts, via
# `expect_failures`, that the corresponding precondition (and ONLY that one)
# fails. `expect_failures` tolerates the named checkable object failing; if any
# OTHER checkable object were to fail, the test run itself would fail — so every
# run also implicitly proves the other three rules stay satisfied.
#
# Run with:
#   terraform init -backend=false
#   terraform test
#
# `command = plan` keeps these assertions plan-time only: no AWS resources are
# created, so the checks run without provisioning or AWS credentials.

# ---------------------------------------------------------------------------
# Offline mocking
# ---------------------------------------------------------------------------
# The positive-baseline run below (use_real_prod_db = false) triggers the
# read-only `data "aws_db_snapshot" "prod_latest"` lookup declared in main.tf.
# Against the live provider that lookup needs AWS credentials and a real prod
# snapshot, so it errors in an offline checkpoint and halts the whole test file
# — skipping the four negative validation runs (R10.6-R10.9).
#
# Mocking the aws provider makes the data source resolve to canned defaults so
# the entire file runs fully offline. The mock only supplies the read-only
# snapshot lookup; it does NOT alter any variable inputs, so every
# `terraform_data.*` precondition in checks.tf still evaluates exactly as it
# would against real inputs and the expect_failures assertions stay intact.
mock_provider "aws" {
  mock_data "aws_db_snapshot" {
    defaults = {
      id        = "rds:sgl-prod-db-2024-01-01"
      encrypted = true
    }
  }

  # Mocking the whole aws provider also intercepts the read-only
  # `aws_iam_policy_document` lookups in modules/iam. Their generated `json`
  # attribute would otherwise be a random placeholder string, which fails the
  # aws_iam_role `assume_role_policy` "must be a JSON object" validation during
  # the positive-baseline plan. Supplying a minimal valid JSON policy document
  # keeps the plan offline-clean without touching any validation inputs.
  mock_data "aws_iam_policy_document" {
    defaults = {
      json = "{\"Version\":\"2012-10-17\",\"Statement\":[]}"
    }
  }
}

# ---------------------------------------------------------------------------
# Positive baseline: a valid isolated-mode configuration plans cleanly, proving
# the validation rules do not fire on a legitimate combination.
# ---------------------------------------------------------------------------
run "valid_isolated_defaults_plan_succeeds" {
  command = plan

  variables {
    use_real_prod_db           = false
    create_isolated_bucket     = true
    rds_identifier             = "sgl-prod-iaas-db"
    prod_rds_source_identifier = "sgl-prod-db" # provides a snapshot source (R10.9)
    rds_snapshot_identifier    = ""
    real_prod_rds_reference    = ""
    existing_bucket_name       = ""
  }

  # No expect_failures: the plan must succeed with all four preconditions met.
}

# ---------------------------------------------------------------------------
# R10.6 — use_real_prod_db = true with an empty real_prod_rds_reference must
# halt the plan (Requirement 10.6).
# ---------------------------------------------------------------------------
run "r10_6_real_prod_db_without_reference_fails" {
  command = plan

  variables {
    use_real_prod_db        = true
    real_prod_rds_reference = "" # empty -> must fail
    # keep every other rule satisfied so ONLY R10.6 fails:
    create_isolated_bucket     = true # R10.7 ok
    rds_identifier             = "sgl-prod-iaas-db"
    prod_rds_source_identifier = "" # distinct from rds_identifier -> R10.8 ok
    # use_real_prod_db = true short-circuits R10.9 (no snapshot source needed)
    # use_real_prod_db = true routes real_prod_rds_secret_arn into module.iam
    # (which validates it is non-empty); supply it so the ONLY failure is the
    # R10.6 precondition and not an unrelated iam input-validation error.
    real_prod_rds_secret_arn = "arn:aws:secretsmanager:us-east-1:111122223333:secret:sgl/prod/rds-abc123"
  }

  expect_failures = [
    terraform_data.validate_real_prod_db_reference,
  ]
}

# ---------------------------------------------------------------------------
# R10.7 — create_isolated_bucket = false with an empty existing_bucket_name
# must halt the plan (Requirement 10.7).
# ---------------------------------------------------------------------------
run "r10_7_reuse_bucket_without_name_fails" {
  command = plan

  variables {
    create_isolated_bucket = false
    existing_bucket_name   = "" # empty -> must fail
    # keep every other rule satisfied so ONLY R10.7 fails:
    use_real_prod_db           = false
    real_prod_rds_reference    = ""
    rds_identifier             = "sgl-prod-iaas-db"
    prod_rds_source_identifier = "sgl-prod-db" # non-empty -> R10.8 distinct + R10.9 has a source
    rds_snapshot_identifier    = ""
  }

  expect_failures = [
    terraform_data.validate_existing_bucket_name,
  ]
}

# ---------------------------------------------------------------------------
# R10.8 — rds_identifier equal to prod_rds_source_identifier must halt the
# plan (Requirement 10.8).
# ---------------------------------------------------------------------------
run "r10_8_identifier_collision_fails" {
  command = plan

  variables {
    rds_identifier             = "collision-db-id"
    prod_rds_source_identifier = "collision-db-id" # equal -> must fail
    # keep every other rule satisfied so ONLY R10.8 fails:
    use_real_prod_db        = false # R10.9 satisfied via non-empty prod_rds_source_identifier
    create_isolated_bucket  = true  # R10.7 ok
    real_prod_rds_reference = ""    # R10.6 ok (use_real_prod_db = false)
    rds_snapshot_identifier = ""
  }

  expect_failures = [
    terraform_data.validate_rds_identifier_distinct,
  ]
}

# ---------------------------------------------------------------------------
# R10.9 — use_real_prod_db = false with no snapshot source (both
# rds_snapshot_identifier and prod_rds_source_identifier empty) must halt the
# plan (Requirement 10.9).
# ---------------------------------------------------------------------------
run "r10_9_no_snapshot_source_fails" {
  command = plan

  variables {
    use_real_prod_db           = false
    rds_snapshot_identifier    = "" # empty ...
    prod_rds_source_identifier = "" # ... and empty -> must fail
    # keep every other rule satisfied so ONLY R10.9 fails:
    create_isolated_bucket  = true               # R10.7 ok
    real_prod_rds_reference = ""                 # R10.6 ok (use_real_prod_db = false)
    rds_identifier          = "sgl-prod-iaas-db" # distinct from "" -> R10.8 ok
  }

  expect_failures = [
    terraform_data.validate_snapshot_source_present,
  ]
}

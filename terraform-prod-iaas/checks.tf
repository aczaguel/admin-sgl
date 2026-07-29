# terraform-prod-iaas/checks.tf
#
# Plan-time enforcement of the CROSS-VARIABLE validation rules documented in
# variables.tf. These rules compare two or more variables against each other,
# which is only supported inside a `variable { validation { ... } }` block on
# Terraform >= 1.9. This stack pins `required_version = ">= 1.5.0"` (and is run
# on Terraform 1.8.3), so expressing them as variable-level validations would
# break `terraform validate`.
#
# Instead each rule is implemented as a `terraform_data` resource
# (available since Terraform >= 1.4) carrying a `lifecycle { precondition }`.
# A failing precondition HALTS `terraform plan`/`apply` with a hard error
# BEFORE any real resource is created — exactly the behavior Requirements
# 10.6-10.9 mandate ("SHALL halt the plan before creating any resources and
# return a validation error message ...").
#
# NOTE on `check` blocks: Terraform `check` blocks only emit WARNINGS and let
# the plan succeed, so they are intentionally NOT used here — a soft warning
# would not satisfy the "halt the plan" requirement.
#
# This file is intentionally separate from main.tf (created in task 8.1) so the
# two do not collide during their respective tasks.

# ---- R10.6 ------------------------------------------------------------------
# use_real_prod_db == true requires real_prod_rds_reference to be non-empty.
# (Requirement 10.6: real prod DB toggle enabled but empty reference -> halt.)
resource "terraform_data" "validate_real_prod_db_reference" {
  input = "r10_6_real_prod_db_reference"

  lifecycle {
    precondition {
      condition     = var.use_real_prod_db == false || trimspace(var.real_prod_rds_reference) != ""
      error_message = "R10.6: use_real_prod_db = true requires real_prod_rds_reference to be set (Secrets Manager reference for the REAL prod RDS creds); it is currently empty."
    }
  }
}

# ---- R10.7 ------------------------------------------------------------------
# create_isolated_bucket == false requires existing_bucket_name to be non-empty.
# (Requirement 10.7: isolated bucket disabled but empty existing name -> halt.)
resource "terraform_data" "validate_existing_bucket_name" {
  input = "r10_7_existing_bucket_name"

  lifecycle {
    precondition {
      condition     = var.create_isolated_bucket == true || trimspace(var.existing_bucket_name) != ""
      error_message = "R10.7: create_isolated_bucket = false requires existing_bucket_name to name the bucket to reuse; it is currently empty."
    }
  }
}

# ---- R10.8 ------------------------------------------------------------------
# rds_identifier must not equal prod_rds_source_identifier.
# (Requirement 10.8: isolated RDS id equals prod source id -> halt.)
resource "terraform_data" "validate_rds_identifier_distinct" {
  input = "r10_8_rds_identifier_distinct"

  lifecycle {
    precondition {
      condition     = trimspace(var.rds_identifier) != trimspace(var.prod_rds_source_identifier)
      error_message = "R10.8: rds_identifier must differ from prod_rds_source_identifier so the isolated RDS never collides with the production instance; they are currently equal."
    }
  }
}

# ---- R10.9 ------------------------------------------------------------------
# use_real_prod_db == false requires at least one of rds_snapshot_identifier or
# prod_rds_source_identifier to be non-empty (a snapshot source must exist).
# (Requirement 10.9: isolated mode but no snapshot source -> halt.)
resource "terraform_data" "validate_snapshot_source_present" {
  input = "r10_9_snapshot_source_present"

  lifecycle {
    precondition {
      condition     = var.use_real_prod_db == true || trimspace(var.rds_snapshot_identifier) != "" || trimspace(var.prod_rds_source_identifier) != ""
      error_message = "R10.9: use_real_prod_db = false requires a snapshot source: set rds_snapshot_identifier or prod_rds_source_identifier (both are currently empty), otherwise the isolated RDS has nothing to seed from."
    }
  }
}

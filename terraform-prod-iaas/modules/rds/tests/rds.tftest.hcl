# terraform-prod-iaas/modules/rds/tests/rds.tftest.hcl
#
# Task 5.2 — Plan assertions for RDS destroy safety and backup/PITR config.
#
# Property 2 (Destroy safety on the isolated RDS): a plain `terraform destroy`
# is blocked and a final snapshot is retained.
#
# Validates: Requirements 3.2, 3.3, 3.4, 8.1
#
# These assertions run offline at plan time using a mocked AWS provider, so no
# AWS credentials or real resources are involved. The `mock_data` block feeds
# the read-only `data "aws_db_snapshot" "seed"` lookup an ENCRYPTED source so
# the module's precondition (Req 14.4) is satisfied and the planned
# `aws_db_instance` becomes available for assertion.
#
# On `prevent_destroy` (Req 3.3): `prevent_destroy` is a STATIC lifecycle
# meta-argument. Terraform evaluates it while building the destroy graph, not as
# a value that appears in a normal (create) plan, so it cannot be read back from
# `aws_db_instance.this` inside a `command = plan` assertion. Its presence is
# therefore verified two ways OUTSIDE this file:
#   1. Code inspection of modules/rds/main.tf — the `lifecycle` block sets
#      `prevent_destroy = true` on `aws_db_instance.this`.
#   2. Behaviorally, `terraform plan -destroy` against a state that contains
#      this instance halts with an error of the form:
#        "Instance cannot be destroyed ... has prevent_destroy set".
# What this file DOES assert about destroy safety is the retained-final-snapshot
# half of the guard: `skip_final_snapshot = false` (Req 3.4), which guarantees a
# final snapshot is taken if a destroy is ever force-enabled.
#
# Run with:
#   terraform init -backend=false
#   terraform test
#
# `command = plan` keeps these assertions plan-time only.

mock_provider "aws" {
  # The seeding snapshot lookup is read-only. Force it to report an ENCRYPTED
  # source so the instance precondition passes and the plan can be assembled.
  mock_data "aws_db_snapshot" {
    defaults = {
      id        = "rds:sgl-prod-db-2024-01-01-00-00"
      encrypted = true
    }
  }
}

# ---------------------------------------------------------------------------
# Baseline isolated-mode plan: assert the destroy-safety and backup/PITR /
# network-protection configuration on the planned aws_db_instance.
# ---------------------------------------------------------------------------
run "destroy_safety_and_backup_config" {
  command = plan

  variables {
    identifier              = "sgl-prod-iaas-db"
    snapshot_identifier     = "sgl-prod-db-manual-snapshot"
    subnet_ids              = ["subnet-aaaa1111", "subnet-bbbb2222"]
    vpc_security_group_ids  = ["sg-cccc3333"]
    backup_retention_period = 7
    skip_final_snapshot     = false
    deletion_protection     = true
  }

  # Req 8.1 — the isolated RDS must not be reachable from the public internet.
  assert {
    condition     = aws_db_instance.this.publicly_accessible == false
    error_message = "Isolated RDS must set publicly_accessible = false (Req 8.1)."
  }

  # Req 3.4 — skip_final_snapshot = false guarantees a final snapshot is taken
  # if a destroy is ever force-enabled (the retained-snapshot half of the
  # destroy guard).
  assert {
    condition     = aws_db_instance.this.skip_final_snapshot == false
    error_message = "Isolated RDS must set skip_final_snapshot = false so a destroy retains a final snapshot (Req 3.4)."
  }

  # Req 3.4 — when skip_final_snapshot = false, a unique final snapshot
  # identifier must be planned so the destroy-time snapshot never collides.
  assert {
    condition     = aws_db_instance.this.final_snapshot_identifier != null && aws_db_instance.this.final_snapshot_identifier != ""
    error_message = "A unique final_snapshot_identifier must be set when skip_final_snapshot = false (Req 3.4)."
  }

  # Req 3.2 — deletion protection must be enabled on the isolated RDS.
  assert {
    condition     = aws_db_instance.this.deletion_protection == true
    error_message = "Isolated RDS must enable deletion_protection (Req 3.2)."
  }

  # Req 3.1 — automated backup retention within 7..35 days enables PITR.
  assert {
    condition     = aws_db_instance.this.backup_retention_period >= 7 && aws_db_instance.this.backup_retention_period <= 35
    error_message = "backup_retention_period must be within 7..35 days to enable point-in-time recovery (Req 3.1)."
  }
}

# ---------------------------------------------------------------------------
# Upper-boundary retention: 35 days is still accepted and within the PITR range.
# ---------------------------------------------------------------------------
run "backup_retention_upper_boundary" {
  command = plan

  variables {
    identifier              = "sgl-prod-iaas-db"
    snapshot_identifier     = "sgl-prod-db-manual-snapshot"
    subnet_ids              = ["subnet-aaaa1111", "subnet-bbbb2222"]
    vpc_security_group_ids  = ["sg-cccc3333"]
    backup_retention_period = 35
    skip_final_snapshot     = false
  }

  assert {
    condition     = aws_db_instance.this.backup_retention_period >= 7 && aws_db_instance.this.backup_retention_period <= 35
    error_message = "backup_retention_period 35 must be accepted as the upper PITR boundary (Req 3.1)."
  }
}

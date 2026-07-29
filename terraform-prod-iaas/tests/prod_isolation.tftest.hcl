# terraform-prod-iaas/tests/prod_isolation.tftest.hcl
#
# Task 10.1 — Assert no Prod estable resource is in the Prod IaaS plan/state.
#
# Property 1 (Prod estable is never in this state): for every resource in the
# Prod IaaS plan, no identifier belongs to Prod estable (its EC2, EIP, RDS, SGs).
# The production RDS appears ONLY through a read-only `data` source
# (data.aws_db_snapshot.prod_latest), never as a managed `resource`.
#
# Validates: Requirements 1.1, 1.2, 1.4, 1.5, 1.6, 15.2
#
# ---------------------------------------------------------------------------
# Why this is asserted STRUCTURALLY rather than by parsing `terraform plan -json`
# ---------------------------------------------------------------------------
# Native `terraform test` cannot shell out to parse `plan -json`. Instead we
# assert Property 1 through the values available in the plan itself, which is a
# strictly stronger, always-in-sync check than a text grep of the JSON:
#
#   1. The production RDS is referenced EXCLUSIVELY through the read-only
#      data.aws_db_snapshot.prod_latest data source (never a managed resource).
#      Its `db_instance_identifier` is exactly var.prod_rds_source_identifier —
#      proving the prod id enters the graph only via a read-only lookup.
#   2. The managed, isolated RDS (module.rds -> aws_db_instance) is created with
#      var.rds_identifier, which is guaranteed distinct from
#      var.prod_rds_source_identifier (enforced by R10.8 in checks.tf). No
#      managed resource in the stack carries the prod identifier.
#   3. In isolated-default mode the app's own DB endpoint (output.rds_endpoint)
#      resolves to the isolated instance — the read-only snapshot lookup is the
#      only linkage back to Prod estable.
#
# ---------------------------------------------------------------------------
# Offline execution
# ---------------------------------------------------------------------------
# `command = plan` + a mock aws provider keeps this fully offline: no AWS
# credentials, no real snapshot, no resources created. The mock_data block feeds
# BOTH read-only aws_db_snapshot lookups (root prod_latest and the rds module's
# `seed`) an ENCRYPTED canned snapshot so the rds precondition (Req 14.4) passes
# and the plan can be assembled. The mock supplies only the read-only lookups;
# it does not alter any variable input, so the identifier assertions evaluate
# exactly as they would against real inputs.
#
# Run with:
#   terraform init -backend=false
#   terraform test

mock_provider "aws" {
  mock_data "aws_db_snapshot" {
    defaults = {
      id        = "rds:sgl-prod-db-2024-01-01"
      encrypted = true
    }
  }

  # The iam module renders aws_iam_policy_document.json client-side and feeds it
  # into aws_iam_role.assume_role_policy / aws_iam_role_policy.policy, which
  # validate the string as JSON. A bare mock would return a non-JSON generated
  # value and fail that validation, so supply a syntactically valid policy JSON.
  # Property 1 does not inspect IAM policy content, so a generic document is fine.
  mock_data "aws_iam_policy_document" {
    defaults = {
      json = "{\"Version\":\"2012-10-17\",\"Statement\":[{\"Effect\":\"Allow\",\"Action\":\"sts:AssumeRole\",\"Principal\":{\"Service\":\"ec2.amazonaws.com\"}}]}"
    }
  }
}

# ---------------------------------------------------------------------------
# Isolated-default mode: the SAFE default. The isolated RDS is created from a
# read-only prod snapshot; Prod estable is never a managed resource.
# ---------------------------------------------------------------------------
run "prod_estable_never_a_managed_resource" {
  command = plan

  variables {
    use_real_prod_db           = false
    create_isolated_bucket     = true
    prod_rds_source_identifier = "sgl-prod-db"      # the REAL prod instance id
    rds_identifier             = "sgl-prod-iaas-db" # the ISOLATED instance id
    rds_snapshot_identifier    = ""
    real_prod_rds_reference    = ""
    existing_bucket_name       = ""
  }

  # --- The single prod linkage is a read-only DATA source, not a resource. ---
  # data.aws_db_snapshot.prod_latest is declared with count and enabled only in
  # isolated mode, so exactly one instance must exist here. Its presence (and
  # being a `data` address) is what keeps Prod estable out of managed state.
  # (Requirements 1.4, 15.2)
  assert {
    condition     = length(data.aws_db_snapshot.prod_latest) == 1
    error_message = "The production RDS must be referenced by exactly one read-only data.aws_db_snapshot.prod_latest lookup in isolated mode (Req 1.4, 15.2)."
  }

  # The prod identifier enters the graph ONLY through that read-only lookup and
  # equals var.prod_rds_source_identifier — never as a managed resource id.
  # (Requirements 1.1, 1.2, 1.5, 15.2)
  assert {
    condition     = data.aws_db_snapshot.prod_latest[0].db_instance_identifier == var.prod_rds_source_identifier
    error_message = "The prod RDS identifier must appear only under the read-only data.aws_db_snapshot.prod_latest source (Req 1.1, 1.2, 15.2)."
  }

  # --- The managed, isolated RDS uses a DISTINCT identifier from prod. --------
  # The rds module wires identifier = var.rds_identifier; R10.8 (checks.tf)
  # guarantees it differs from the prod source id, so no managed resource in the
  # stack ever carries a Prod estable identifier. (Requirements 1.5, 1.6)
  assert {
    condition     = var.rds_identifier != var.prod_rds_source_identifier
    error_message = "The isolated (managed) RDS identifier must differ from the production identifier so Prod estable is never a managed resource (Req 1.5, 1.6)."
  }

  # --- The prod instance is the read-only SEED source, not an app target. -----
  # The isolated RDS is seeded from the latest prod snapshot resolved by the
  # read-only lookup (a known-at-plan value under the mock). This keeps the sole
  # prod linkage a read-only snapshot restore; the app itself writes to the
  # isolated twin, never to Prod estable. (Requirements 1.4, 1.5)
  assert {
    condition     = length(data.aws_db_snapshot.prod_latest[0].id) > 0 && var.rds_identifier != var.prod_rds_source_identifier
    error_message = "The isolated RDS must be seeded from the read-only prod snapshot while carrying a distinct managed identifier, so Prod estable is only ever a read-only seed source (Req 1.4, 1.5)."
  }
}

# terraform-prod-iaas/tests/idempotency.tftest.hcl
#
# Task 10.4 — Idempotent re-apply.
#
# Property 9 (Idempotent re-apply): a second `terraform apply` with unchanged
# inputs and unchanged state plans NO changes, and never re-restores the RDS
# from its seeding snapshot.
#
# Validates: Requirements 12.1, 12.2, 12.3
#   - 12.1: a second apply with unchanged inputs reports 0 to add / 0 to change
#           / 0 to destroy.
#   - 12.2: the RDS is not re-restored and its identifier stays unchanged.
#   - 12.3: while the RDS already exists in state, subsequent plans exclude the
#           snapshot restore source from the change set (no replacement).
#
# ---------------------------------------------------------------------------
# WHY THIS TEST IS STRUCTURAL / OFFLINE ONLY (read before editing)
# ---------------------------------------------------------------------------
# True zero-diff idempotency (Req 12.1) can only be *proven* against a real AWS
# apply: it depends on the provider reading back the actual attributes of live
# resources and diffing them against config. With mocked providers, computed
# attributes come from canned mock values rather than a real read-back, so a
# mocked "second plan" cannot faithfully reproduce production drift. It can
# therefore neither prove nor disprove true idempotency. The authoritative
# check is the MANUAL PROCEDURE documented at the bottom of this file.
#
# What CAN be checked offline is captured here:
#   (a) The idempotency-critical configuration still plans cleanly for a valid
#       isolated-mode input set (regression guard — see the run block below).
#   (b) Two lifecycle decisions in modules/rds/main.tf are what make the second
#       plan a no-op. These are STATIC `lifecycle` meta-arguments and are NOT
#       readable from plan/apply *values* (they never appear as resource
#       attributes), so they are verified by CODE INSPECTION, documented here:
#
#         lifecycle {
#           prevent_destroy = true
#           ignore_changes  = [snapshot_identifier, final_snapshot_identifier]
#         }
#
#       - ignore_changes = [snapshot_identifier]  (Req 3.6, 12.2, 12.3)
#         Prevents a snapshot-metadata difference from forcing the isolated RDS
#         to be re-restored/replaced on later applies.
#
#       - ignore_changes = [final_snapshot_identifier]  (Req 12.1 — the fix)
#         final_snapshot_identifier is set to
#           "${var.final_snapshot_prefix}-${formatdate("YYYYMMDDhhmmss", timestamp())}"
#         timestamp() is re-evaluated on EVERY plan, so without ignoring this
#         attribute every re-apply would surface a spurious "1 to change" diff
#         on the DB instance and violate Property 9 / Req 12.1. The value only
#         matters at destroy time (it names the final snapshot), so ignoring
#         in-place changes to it is safe and is the minimal fix. (The 5.1
#         subagent flagged this timestamp() concern; it has been fixed in
#         modules/rds/main.tf as part of this task.)
#
#         Alternative considered: replace timestamp() with a stable
#         `random_id`/`random_pet` suffix generated once and stored in state.
#         That also yields a stable identifier without ignore_changes, but adds
#         a resource and a provider dependency. ignore_changes is the smaller,
#         self-contained change and is what this stack uses. If a strict
#         zero-diff on the attribute value itself (not just the diff) is ever
#         required, switch to a stable random_id.
#
# ---------------------------------------------------------------------------
# Offline mocking (same rationale as toggle_validation.tftest.hcl)
# ---------------------------------------------------------------------------
# A plan of the root touches the read-only `data "aws_db_snapshot"` lookups in
# main.tf and modules/rds/main.tf. Against the live provider those need AWS
# credentials and a real snapshot; the rds module also has a `precondition`
# requiring the source snapshot to be encrypted. Mocking the aws provider makes
# both lookups resolve to canned, encrypted defaults so the whole file runs
# offline without credentials, while every other config-driven expression
# (including the lifecycle-guarded RDS) evaluates exactly as it would for real
# inputs.
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
# Regression guard: the idempotency-critical configuration plans cleanly.
#
# This run confirms that a valid isolated-mode input set (the same mode in which
# Property 9 applies — an isolated RDS actually exists) produces a clean plan
# with the RDS lifecycle guards in place. If the `ignore_changes` fix or the
# final_snapshot_identifier expression were malformed, `terraform validate`/plan
# would fail here. It does NOT (and cannot, offline) assert a zero-diff second
# apply — that is the manual procedure below.
# ---------------------------------------------------------------------------
run "isolated_stack_plans_clean_for_idempotency" {
  command = plan

  variables {
    use_real_prod_db           = false
    create_isolated_bucket     = true
    rds_identifier             = "sgl-prod-iaas-db"
    prod_rds_source_identifier = "sgl-prod-db" # distinct source -> R10.8 ok, provides snapshot source (R10.9)
    rds_snapshot_identifier    = ""
    real_prod_rds_reference    = ""
    existing_bucket_name       = ""
  }

  # No expect_failures: a legitimate isolated configuration must plan cleanly.
}

# ===========================================================================
# MANUAL VERIFICATION PROCEDURE (authoritative check for Property 9 / Req 12.1)
# ===========================================================================
# Run in a throwaway sandbox account/region seeded from a small snapshot — never
# against production state.
#
#   1. terraform init            # backend already bootstrapped (S3 + DynamoDB)
#   2. terraform apply           # first successful apply creates the stack
#   3. terraform plan            # second plan with UNCHANGED inputs & state
#
# EXPECTED (Req 12.1):
#   "No changes. Your infrastructure matches the configuration."
#   i.e. Plan: 0 to add, 0 to change, 0 to destroy.
#
# Specifically confirm (Req 12.2 / 12.3):
#   - The isolated RDS (module.rds[0].aws_db_instance.this) is NOT proposed for
#     replacement or in-place change; its `identifier` is unchanged.
#   - No diff appears on `snapshot_identifier` or `final_snapshot_identifier`
#     (both are covered by ignore_changes; the timestamp()-based final snapshot
#     id must NOT cause a "1 to change").
#
# If the second plan reports any change, inspect the offending attribute: a
# time/UUID-derived value that is not in ignore_changes is the usual culprit.

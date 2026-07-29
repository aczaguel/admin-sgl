# Implementation Plan: Prod IaaS (Terraform, parallel production server)

## Overview

This plan builds the new, isolated `terraform-prod-iaas/` root module and its six child modules
entirely in HCL (Terraform, `hashicorp/aws ~> 5.0`, Terraform `>= 1.5.0`). Work proceeds bottom-up:
first the root scaffolding (backend, providers, variables with validation), then each self-contained
module (network, iam, secrets, rds, compute, eip), then the root wiring that reuses the existing
`terraform/modules/s3_bucket`, and finally static + plan-based verification of the design's
Correctness Properties.

Because this is declarative Infrastructure-as-Code, the design's Correctness Properties are verified
as `terraform plan -json` assertions and OPA/conftest + Terratest checks rather than generator-style
property tests. Those verification sub-tasks are marked optional with `*`.

## Tasks

- [x] 1. Bootstrap the `terraform-prod-iaas/` root module scaffolding
  - [x] 1.1 Create `terraform-prod-iaas/versions.tf` with the remote backend and provider pins
    - Set `required_version = ">= 1.5.0"` and `required_providers` aws `~> 5.0`
    - Configure `backend "s3"` with bucket, DynamoDB lock table, `encrypt = true`, and a distinct
      `key = "prod-iaas/terraform.tfstate"` that differs from the existing stack's state key
    - _Requirements: 4.4, 11.1, 11.2, 11.3, 11.5_

  - [x] 1.2 Create `terraform-prod-iaas/providers.tf` with the AWS provider and default tags
    - Configure `provider "aws"` with `region = var.region` and `default_tags` (`Project`,
      `ManagedBy`, `Server = prod-iaas`) so every resource is identifiable as Prod IaaS only
    - _Requirements: 1.1, 4.4_

  - [x] 1.3 Create `terraform-prod-iaas/variables.tf` with all knobs and validation blocks
    - Declare sizing/config vars: `region`, `project`, `instance_type` (default `t3.medium`),
      `rds_instance_class` (default `db.t3.medium`), `ingress_cidr_https`, `open_http`,
      `root_volume_size`, `backup_retention_period`, `backup_window`
    - Declare toggle/target vars: `create_isolated_bucket`, `isolated_bucket_name`,
      `existing_bucket_name`, `use_real_prod_db`, `prod_rds_source_identifier`,
      `rds_snapshot_identifier`, `real_prod_rds_reference`, `real_prod_rds_secret_arn`,
      `rds_identifier` (default `sgl-prod-iaas-db`)
    - Add `validation` blocks: fail when `use_real_prod_db = true` and `real_prod_rds_reference`
      is empty; fail when `create_isolated_bucket = false` and `existing_bucket_name` is empty;
      fail when `rds_identifier == prod_rds_source_identifier`; fail when `use_real_prod_db = false`
      and both `rds_snapshot_identifier` and `prod_rds_source_identifier` are empty; constrain
      `backup_retention_period` to the 7..35 range
    - _Requirements: 3.1, 5.1, 10.6, 10.7, 10.8, 10.9_

  - [x] 1.4 Add plan-time validation assertions for the toggle/validation rules
    - Assert each invalid combination fails at `terraform validate`/`plan` with the expected message
    - **Property 8: Toggle exclusivity** — invalid toggle combinations fail via variable validation
    - **Validates: Requirements 10.6, 10.7, 10.8, 10.9**

- [x] 2. Implement the `network` module
  - [x] 2.1 Create `terraform-prod-iaas/modules/network/` (variables.tf, main.tf, outputs.tf)
    - Inputs: `vpc_id`, `subnet_id`, `ingress_cidr_https`, `open_http`, `name_prefix`; resolve the
      default VPC/subnets when inputs are empty
    - Create a dedicated app security group: ingress 443 from `0.0.0.0/0`, optional ingress 80 when
      `open_http`, **no port 22 ingress**, and egress allowing TCP 443 for SSM
    - Create a DB security group allowing inbound 3306 **only** from the app security group; add a
      `validation` block rejecting any 3306 source CIDR that is publicly routable (e.g. `0.0.0.0/0`)
    - Outputs: `security_group_id`, `db_security_group_id`, `vpc_id`, `subnet_id`, `subnet_ids`
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 8.3, 8.4, 8.5_

  - [x] 2.2 Add plan assertion that SSH is closed and DB access is restricted
    - Assert SG ingress contains only 443 (and optionally 80), never 22; assert 3306 source is the
      app SG only
    - **Property 6: SSH is closed** — created SG has no ingress rule for port 22
    - **Validates: Requirements 7.1, 7.6, 8.3, 8.4**

- [x] 3. Implement the `iam` module
  - [x] 3.1 Create `terraform-prod-iaas/modules/iam/` (variables.tf, main.tf, outputs.tf)
    - Inputs: `name_prefix`, `s3_bucket_arn`, `rds_secret_arn`, `attach_ssm`
    - Create the EC2 role, instance profile, and least-privilege inline policy: S3 `GetObject`,
      `PutObject`, `DeleteObject` on `${s3_bucket_arn}/*`; `ListBucket` on `${s3_bucket_arn}`;
      `secretsmanager:GetSecretValue` on `rds_secret_arn` only; no `Resource = "*"` statements
    - Attach `AmazonSSMManagedInstanceCore` when `attach_ssm = true`
    - Outputs: `instance_profile_name`, `role_arn`
    - _Requirements: 6.1, 6.3, 6.4, 6.5, 6.6, 7.5_

  - [x] 3.2 Add plan assertion that IAM is scoped to exactly two ARNs
    - Assert policy documents reference only the bucket ARN (+`/*`) and the secret ARN; assert no
      wildcard resources and no actions beyond the enumerated set
    - **Property 5: Least privilege** — role grants S3 only on the bucket ARN and `GetSecretValue`
      only on the secret ARN
    - **Validates: Requirements 6.3, 6.4, 6.5, 6.6**

- [x] 4. Implement the `secrets` module
  - [x] 4.1 Create `terraform-prod-iaas/modules/secrets/` (variables.tf, main.tf, outputs.tf)
    - Inputs: `secret_name`, `db_host`, `db_port`, `db_username`, `db_password` (sensitive),
      `db_name`
    - Create the Secrets Manager secret + version with the JSON shape
      `{host, port, username, password, dbname}` the app already consumes
    - Mark `db_password` sensitive; do not expose the password through any non-sensitive output
    - Outputs: `secret_arn`, `secret_name`
    - _Requirements: 14.5, 14.6_

- [x] 5. Implement the `rds` module (isolated, snapshot-seeded, guarded)
  - [x] 5.1 Create `terraform-prod-iaas/modules/rds/` (variables.tf, main.tf, outputs.tf)
    - Inputs: `identifier`, `snapshot_identifier`, `source_db_identifier`, `instance_class`,
      `allocated_storage`, `subnet_ids`, `vpc_security_group_ids`, `backup_retention_period`,
      `backup_window`, `deletion_protection`, `skip_final_snapshot`, `final_snapshot_prefix`,
      `multi_az`, `new_master_password` (sensitive)
    - Create a private `aws_db_subnet_group` over internet-gateway-free subnets and an
      `aws_db_instance` with `snapshot_identifier` seeding, `publicly_accessible = false`,
      `backup_retention_period`, `backup_window`, `deletion_protection = true`,
      `skip_final_snapshot = false` with a unique `final_snapshot_identifier`
    - Add `lifecycle { prevent_destroy = true, ignore_changes = [snapshot_identifier] }`
    - Add a `validation`/precondition surfacing an error when the source snapshot is unencrypted
    - Outputs: `endpoint`, `port`, `db_name`, `username`, `identifier`, `password_effective`
    - _Requirements: 2.1, 2.2, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 8.1, 8.2, 14.3, 14.4_

  - [x] 5.2 Add plan assertion for RDS destroy safety and backup/PITR configuration
    - Assert `terraform plan -destroy` is blocked by `prevent_destroy`; assert
      `skip_final_snapshot = false`, retention within 7..35, and `publicly_accessible = false`
    - **Property 2: Destroy safety on the isolated RDS** — plain destroy is blocked and a final
      snapshot is retained
    - **Validates: Requirements 3.2, 3.3, 3.4, 8.1**

- [x] 6. Implement the `compute` module (EC2 + user-data)
  - [x] 6.1 Create `terraform-prod-iaas/modules/compute/` (variables.tf, main.tf, outputs.tf)
    - Inputs: `name_prefix`, `ami_id` (look up latest Amazon Linux 2023 x86_64 when empty),
      `instance_type` (default `t3.medium`), `subnet_id`, `security_group_ids`,
      `instance_profile_name`, `root_volume_size`, `env_file_vars`, `compose_source`
    - Attach exactly one instance profile; provision no static access keys anywhere
    - Outputs: `instance_id`, `private_ip`
    - _Requirements: 5.1, 6.1, 6.2_

  - [x] 6.2 Create the user-data template `modules/compute/user-data.sh.tftpl`
    - Install Docker + docker compose, render the stateless `.env` from `env_file_vars`
      (`FILE_STORAGE_DRIVER=s3`, `S3_BUCKET`, `S3_REGION`, `SECRETS_PROVIDER=aws`,
      `SECRETS_RDS_REFERENCE`, `SECRETS_REGION`) with **no** AWS keys, then `docker compose up -d`
    - Guard startup: abort with an operator-observable failure when any required value
      (`S3_BUCKET`, region, `SECRETS_RDS_REFERENCE`) is empty
    - Run a health check and treat the app as ready only on success within 300 seconds
    - _Requirements: 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 13.1, 13.2, 13.3_

  - [x] 6.3 Add assertion that no AWS keys appear in user-data or outputs
    - Grep the rendered user-data and all module/root outputs for `AWS_ACCESS_KEY`/`AWS_SECRET`
      and the RDS master password → expect no matches
    - **Property 4: No AWS keys anywhere** — no key material in `.env`, user-data, or outputs
    - **Validates: Requirements 6.2, 5.8, 14.6**

- [x] 7. Implement the `eip` module (second Elastic IP)
  - [x] 7.1 Create `terraform-prod-iaas/modules/eip/` (variables.tf, main.tf, outputs.tf)
    - Inputs: `instance_id`, `name_prefix`
    - Allocate exactly one new `aws_eip` (VPC domain) and one `aws_eip_association` binding it to the
      instance; never reference Prod estable's EIP
    - Outputs: `public_ip`, `allocation_id`
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6_

  - [x] 7.2 Add plan assertion that exactly one new EIP is allocated and associated
    - Assert the plan adds exactly one `aws_eip` + one association and references no existing EIP
    - **Property 7: Second productive IP exists** — one new EIP is allocated and associated
    - **Validates: Requirements 9.1, 9.2, 9.4**

- [x] 8. Wire the root module and reuse the existing bucket module
  - [x] 8.1 Create `terraform-prod-iaas/main.tf` toggle-resolution locals and read-only snapshot lookup
    - Add `locals` for `effective_bucket_name`, `effective_bucket_arn`, and `effective_rds_reference`
      derived from `create_isolated_bucket` and `use_real_prod_db`
    - Add a read-only `data "aws_db_snapshot" "prod_latest"` (with `count` disabled when
      `use_real_prod_db = true`) that looks up the latest prod snapshot; never declare the prod RDS
      as a managed resource
    - _Requirements: 1.4, 2.1, 10.1, 10.2, 10.3, 10.4, 10.5, 15.1, 15.2, 15.3, 15.4_

  - [x] 8.2 Wire the six modules and the reused `s3_bucket` module in `main.tf`
    - Instantiate `network`, `iam`, `secrets` (`count` gated by `use_real_prod_db`), `rds`
      (`count` gated by `use_real_prod_db`), `compute`, `eip`
    - Reference the existing bucket module via `source = "../terraform/modules/s3_bucket"` with
      `count = var.create_isolated_bucket ? 1 : 0`, leaving that module's files byte-for-byte unchanged
    - Feed `effective_bucket_arn`/`effective_rds_reference`/effective secret ARN into `iam` and the
      `compute` `env_file_vars`
    - _Requirements: 4.1, 4.2, 4.3, 4.5, 5.6, 10.1, 10.2, 10.3, 10.4, 10.5_

  - [x] 8.3 Create `terraform-prod-iaas/outputs.tf`
    - Expose `eip_public_ip`, `instance_id`, and `rds_endpoint` (mark the endpoint/DB identity
      sensitive as needed); never output the RDS master password or any AWS key
    - _Requirements: 9.1, 14.6_

  - [x] 8.4 Create `terraform-prod-iaas/terraform.tfvars.example`
    - Document both toggle scenarios (isolated default vs. cutover-rehearsal) with all knobs and
      commented guidance for `use_real_prod_db` / `create_isolated_bucket`
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 9. Checkpoint - static validation of the whole stack
  - Run `terraform fmt -check` and `terraform validate` on the root and every module; run
    `tflint` and `checkov`/`tfsec` (no public RDS, no `0.0.0.0/0` on 22, encryption on, no wildcard
    IAM). Ensure all checks pass, ask the user if questions arise.

- [x] 10. Author plan-based Correctness Property assertions
  - [x] 10.1 Assert no Prod estable resource is in the Prod IaaS plan/state
    - Parse `terraform plan -json`: zero `resource` addresses reference the prod RDS/EC2/EIP/SG
      identifiers; the prod RDS appears only under `data`
    - **Property 1: Prod estable is never in this state**
    - **Validates: Requirements 1.1, 1.2, 1.4, 1.5, 1.6, 15.2**

  - [x] 10.2 Assert isolated-mode DB write isolation
    - Assert the rendered `.env` `SECRETS_RDS_REFERENCE` resolves to a secret whose `host` is the
      isolated RDS endpoint, not the production endpoint, when `use_real_prod_db = false`
    - **Property 3: Isolation of writes (default mode)**
    - **Validates: Requirements 2.3**

  - [x] 10.3 Assert toggle `count = 0` behavior across the four combinations
    - Assert `use_real_prod_db = true` sets rds/secret module `count = 0` and points `.env` at the
      real reference; assert `create_isolated_bucket = false` reuses the existing bucket
    - **Property 8: Toggle exclusivity**
    - **Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5**

  - [x] 10.4 Assert idempotent re-apply
    - After a first apply, assert a second `terraform plan` reports `0 to add, 0 to change,
      0 to destroy` and does not re-restore the RDS (`ignore_changes = [snapshot_identifier]`)
    - **Property 9: Idempotent re-apply**
    - **Validates: Requirements 12.1, 12.2, 12.3**

- [x] 11. Author behavioral policy/integration tests
  - [x] 11.1 Write OPA/conftest policies over the plan JSON
    - Encode the security posture rules (no port 22, no public RDS, least-privilege IAM, single new
      EIP, no prod resources) as conftest policies runnable in CI
    - _Requirements: 6.6, 7.1, 8.1, 9.1, 15.2, 15.3_

  - [x] 11.2 Write Terratest post-apply checks against an isolated sandbox
    - Apply from a small throwaway snapshot; verify HTTPS reachability on the new EIP, DB creds
      resolve to the isolated endpoint, a write is absent from production, and
      `terraform plan -destroy` is blocked by `prevent_destroy`
    - _Requirements: 2.3, 3.3, 9.2, 12.1, 13.1_

- [x] 12. Final checkpoint - full verification
  - Ensure `fmt`/`validate`/`tflint`/`checkov` pass, plan-based property assertions and policy tests
    pass, and the stack plans cleanly for both the isolated-default and cutover-rehearsal toggle sets.
    Ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional verification sub-tasks and can be skipped for a faster MVP.
- This is declarative IaC: the design's Correctness Properties are verified via `terraform plan -json`
  assertions, OPA/conftest policies, and Terratest behavioral checks rather than generator-style
  property tests (per the design's Testing Strategy).
- The existing `terraform/modules/s3_bucket` module is consumed by source reference only and must
  remain byte-for-byte unchanged (Requirement 4.3).
- Each task references specific requirement sub-clauses for traceability; checkpoints ensure
  incremental validation.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3"] },
    { "id": 1, "tasks": ["1.4", "2.1", "3.1", "4.1", "5.1", "6.1", "7.1"] },
    { "id": 2, "tasks": ["2.2", "3.2", "5.2", "6.2", "7.2"] },
    { "id": 3, "tasks": ["6.3", "8.1"] },
    { "id": 4, "tasks": ["8.2"] },
    { "id": 5, "tasks": ["8.3", "8.4"] },
    { "id": 6, "tasks": ["10.1", "10.2", "10.3", "10.4"] },
    { "id": 7, "tasks": ["11.1", "11.2"] }
  ]
}
```

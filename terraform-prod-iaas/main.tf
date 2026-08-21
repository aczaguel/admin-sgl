# terraform-prod-iaas/main.tf
#
# Root wiring for the Prod IaaS stack. This file starts with the two pieces the
# rest of the stack depends on:
#
#   1. Toggle-resolution `locals` that collapse the bucket/DB target toggles
#      (create_isolated_bucket, use_real_prod_db) into single "effective" values
#      consumed by the iam and compute modules (see design.md "Toggle resolution
#      (target selection)").
#   2. A read-only `data "aws_db_snapshot"` lookup of the latest production
#      snapshot used to seed the isolated RDS. The production instance is NEVER
#      declared as a managed resource here — it is referenced exclusively through
#      this read-only data source (Requirement 1.4, 15.2), so applying, tainting,
#      or destroying this stack can never touch Prod estable.
#
# NOTE: The module blocks (network, iam, secrets, rds, compute, eip, and the
# reused ../terraform/modules/s3_bucket) are added in task 8.2 and are APPENDED
# below these locals. The locals below reference outputs of the not-yet-declared
# `module.uploads_iaas` and `module.secrets` exactly as design.md prescribes;
# they resolve once task 8.2 adds those modules. Do NOT run `terraform validate`
# until 8.2 is complete (it will report the missing modules); `terraform fmt`
# is safe to run now.

# ---- Toggle resolution (target selection) ----------------------------------
#
# Collapse the bucket/DB toggles into single effective values. Every downstream
# consumer (iam policy scoping, compute .env) reads only these locals so the
# toggle logic lives in exactly one place.
locals {
  # Bucket the app will use.
  #   create_isolated_bucket = true  -> the dedicated isolated bucket this stack creates.
  #   create_isolated_bucket = false -> an existing bucket reused by name (e.g. real prod).
  effective_bucket_name = var.create_isolated_bucket ? module.uploads_iaas[0].bucket_id : var.existing_bucket_name
  effective_bucket_arn  = var.create_isolated_bucket ? module.uploads_iaas[0].bucket_arn : "arn:aws:s3:::${var.existing_bucket_name}"

  # Secrets Manager reference the app will use for DB credentials.
  #   use_real_prod_db = false -> the isolated RDS secret this stack creates.
  #   use_real_prod_db = true  -> the existing real prod RDS secret reference.
  effective_rds_reference = var.use_real_prod_db ? var.real_prod_rds_reference : module.secrets[0].secret_name
}

# ---- Read-only production snapshot lookup ----------------------------------
#
# Seeds the isolated RDS from the most recent snapshot of the production RDS.
# This is the ONLY linkage to Prod estable and it is strictly read-only: the
# production instance is never declared as a `resource`, so this stack cannot
# modify or destroy it (Requirement 1.4, 15.2).
#
# Disabled (count = 0) when use_real_prod_db = true — in cutover-rehearsal mode
# the isolated RDS is not created, so there is no snapshot to look up. The rds
# module (added in task 8.2) prefers an explicit var.rds_snapshot_identifier
# when set and otherwise falls back to this latest-snapshot lookup.
data "aws_db_snapshot" "prod_latest" {
  count = var.use_real_prod_db ? 0 : 1

  db_instance_identifier = var.prod_rds_source_identifier
  most_recent            = true
}

# ============================================================================
# Module wiring (task 8.2)
# ============================================================================
#
# Instantiates the six Prod IaaS child modules plus the REUSED existing bucket
# module. Data flows as prescribed by design.md "Root: main.tf (wiring)":
#
#   network ──> subnet/SG ids ──> rds, compute
#   rds     ──> endpoint/creds  ──> secrets
#   secrets ──> secret_arn/name ──> iam, effective_rds_reference (locals above)
#   iam     ──> instance_profile ─> compute
#   compute ──> instance_id      ─> eip
#
# Toggle gating:
#   create_isolated_bucket -> module.uploads_iaas count (0/1)
#   use_real_prod_db       -> module.rds and module.secrets count (0/1)
#
# The reused bucket module (../terraform/modules/s3_bucket) is consumed by
# source reference only and its files remain byte-for-byte unchanged
# (Requirement 4.2, 4.3).

# ---- Network: dedicated SGs + resolved VPC/subnet placement ----------------
module "network" {
  source = "./modules/network"

  name_prefix        = "sgl-prod-iaas"
  ingress_cidr_https = var.ingress_cidr_https
  open_http          = var.open_http
}

# ---- Reused S3 bucket module (isolated uploads bucket) ---------------------
# Consumed as-is from the existing stack. Created only when the isolated-bucket
# toggle is on; when off, the stack reuses an existing bucket by name (resolved
# through the effective_bucket_* locals above).
module "uploads_iaas" {
  source = "../terraform/modules/s3_bucket"
  count  = var.create_isolated_bucket ? 1 : 0

  bucket_name        = var.isolated_bucket_name
  versioning_enabled = true
  tags               = { Environment = "prod-iaas" }
}

# ---- Isolated RDS (snapshot-seeded, guarded) -------------------------------
# count = 0 in cutover-rehearsal mode (use_real_prod_db = true): the app then
# points at the real prod DB and no isolated instance is created (R10.4).
# The snapshot source prefers an explicit id and otherwise falls back to the
# read-only latest-snapshot lookup declared above.
module "rds" {
  source = "./modules/rds"
  count  = var.use_real_prod_db ? 0 : 1

  identifier           = var.rds_identifier
  snapshot_identifier  = var.rds_snapshot_identifier != "" ? var.rds_snapshot_identifier : data.aws_db_snapshot.prod_latest[0].id
  source_db_identifier = var.prod_rds_source_identifier
  instance_class       = var.rds_instance_class

  subnet_ids             = module.network.subnet_ids
  vpc_security_group_ids = [module.network.db_security_group_id]

  backup_retention_period = var.backup_retention_period
  backup_window           = var.backup_window
  deletion_protection     = true
  skip_final_snapshot     = false
}

# ---- Secrets: isolated RDS credentials -------------------------------------
# count = 0 in cutover-rehearsal mode (fed from the real prod secret instead).
module "secrets" {
  source = "./modules/secrets"
  count  = var.use_real_prod_db ? 0 : 1

  secret_name = "sgl/prod-iaas/rds-credentials"
  db_host     = module.rds[0].endpoint
  db_port     = module.rds[0].port
  db_username = module.rds[0].username
  db_password = module.rds[0].password_effective
  db_name     = module.rds[0].db_name
}

# ---- IAM: least-privilege role + instance profile --------------------------
# Scoped to exactly the effective bucket ARN and the effective RDS secret ARN.
# In isolated mode the secret ARN comes from module.secrets; in cutover mode it
# is the operator-supplied real prod secret ARN.
module "iam" {
  source = "./modules/iam"

  name_prefix    = "sgl-prod-iaas"
  s3_bucket_arn  = local.effective_bucket_arn
  rds_secret_arn = var.use_real_prod_db ? var.real_prod_rds_secret_arn : module.secrets[0].secret_arn
}

# ---- Compute: EC2 + stateless .env via user-data ---------------------------
# env_file_vars carries the effective bucket name and effective RDS reference
# (resolved through the toggle locals). NO AWS keys are ever passed here.
module "compute" {
  source = "./modules/compute"

  name_prefix           = "sgl-prod-iaas"
  instance_type         = var.instance_type
  subnet_id             = module.network.subnet_id
  security_group_ids    = [module.network.security_group_id]
  instance_profile_name = module.iam.instance_profile_name
  root_volume_size      = var.root_volume_size

  env_file_vars = {
    FILE_STORAGE_DRIVER   = "s3"
    S3_BUCKET             = local.effective_bucket_name
    S3_REGION             = var.region
    SECRETS_PROVIDER      = "aws"
    SECRETS_RDS_REFERENCE = local.effective_rds_reference
    SECRETS_REGION        = var.region
  }
}

# ---- EIP: the second productive Elastic IP ---------------------------------
module "eip" {
  source = "./modules/eip"

  name_prefix = "sgl-prod-iaas"
  instance_id = module.compute.instance_id
}

# ---- Allow the Prod IaaS EC2 to reach the existing production RDS ----------
#
# The production RDS has its own security groups (managed outside this stack).
# We add an ingress rule on each to allow 3306 from the new Prod IaaS app SG.
# This is the ONLY modification to existing prod infrastructure and is a single
# additive rule — destroying this stack removes the rule, nothing else.
variable "prod_rds_security_group_ids" {
  description = "Security group IDs attached to the existing production RDS. An ingress rule for 3306 from the Prod IaaS app SG is added to each."
  type        = list(string)
  default     = []
}

resource "aws_vpc_security_group_ingress_rule" "prod_rds_from_iaas_app" {
  for_each = var.use_real_prod_db ? toset(var.prod_rds_security_group_ids) : toset([])

  security_group_id            = each.value
  description                  = "MySQL inbound from Prod IaaS app (sgl-prod-iaas)"
  ip_protocol                  = "tcp"
  from_port                    = 3306
  to_port                      = 3306
  referenced_security_group_id = module.network.security_group_id

  tags = {
    Name      = "prod-rds-from-prod-iaas-app"
    ManagedBy = "terraform-prod-iaas"
  }
}

# ---- EC2 Scheduler (start 08:00 / stop 20:00 CDMX, every day) ---------------
module "scheduler" {
  source      = "./modules/scheduler"
  instance_id = module.compute.instance_id
  name_prefix = "sgl-prod-iaas"
  enabled     = true
}

# terraform-prod-iaas/variables.tf
#
# All root-level knobs for the Prod IaaS stack: sizing/config, bucket target,
# and DB target toggles. See design.md "Root variables (selected knobs)".
#
# Validation strategy (see tasks.md 1.3 note):
#   - Single-variable rules (rules that concern only the variable itself, e.g.
#     the backup_retention_period 7..35 range) are enforced here with `validation`
#     blocks, which are supported on the pinned Terraform version (>= 1.5.0).
#   - Cross-variable rules (rules that compare two or more variables, e.g.
#     use_real_prod_db vs real_prod_rds_reference) are NOT expressed here because
#     cross-variable references inside a `variable "..." { validation { ... } }`
#     block require Terraform >= 1.9 and would break `terraform validate` on the
#     pinned >= 1.5.0 baseline. Those checks are implemented as plan-time
#     assertions (check blocks / preconditions in main.tf) in task 1.4.

# ---- Sizing / config knobs ----

variable "region" {
  description = "AWS region for all Prod IaaS resources."
  type        = string
  default     = "us-east-1"
}

variable "project" {
  description = "Project tag applied to every resource via provider default_tags."
  type        = string
  default     = "admin-sgl"
}

variable "instance_type" {
  description = "EC2 instance type for the App instance (cost knob)."
  type        = string
  default     = "t3.medium"
}

variable "rds_instance_class" {
  description = "RDS instance class for the isolated DB (cost knob; db.t3.small to save)."
  type        = string
  default     = "db.t3.medium"
}

variable "ingress_cidr_https" {
  description = "CIDRs allowed to reach 443 (and 80 when open_http). Narrow to an office range if desired."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "open_http" {
  description = "Whether to open inbound TCP 80 for the HTTP->HTTPS redirect."
  type        = bool
  default     = true
}

variable "root_volume_size" {
  description = "Root gp3 volume size in GB (no uploads on disk; app is stateless)."
  type        = number
  default     = 30
}

variable "backup_retention_period" {
  description = "Days of automated RDS backups; enables point-in-time recovery. Must be within 7..35."
  type        = number
  default     = 7

  validation {
    condition     = var.backup_retention_period >= 7 && var.backup_retention_period <= 35
    error_message = "backup_retention_period must be between 7 and 35 days (inclusive) so point-in-time recovery is enabled."
  }
}

variable "backup_window" {
  description = "Preferred RDS backup window in UTC (hh:mm-hh:mm)."
  type        = string
  default     = "03:00-04:00"
}

# ---- Bucket target: isolated test bucket (default) vs. reuse an existing bucket ----

variable "create_isolated_bucket" {
  description = "true = create a dedicated isolated bucket for Prod IaaS test writes; false = reuse an existing bucket by name."
  type        = bool
  default     = true
}

variable "isolated_bucket_name" {
  description = "Name for the dedicated Prod IaaS bucket (used when create_isolated_bucket = true)."
  type        = string
  default     = "bucket-sgl-uploads-prod-iaas"
}

variable "existing_bucket_name" {
  description = "Name of an existing bucket to reuse (used when create_isolated_bucket = false, e.g. the real prod bucket for a cutover rehearsal)."
  type        = string
  default     = ""
}

# ---- DB target: isolated snapshot-seeded RDS (default) vs. real prod RDS ----

variable "use_real_prod_db" {
  description = "false = create isolated RDS from a prod snapshot (SAFE default); true = point the app at the REAL prod RDS for a cutover rehearsal (writes hit production!)."
  type        = bool
  default     = false
}

variable "prod_rds_source_identifier" {
  description = "Identifier of the production RDS, used ONLY as a read-only source to find the latest snapshot to seed from. Never managed."
  type        = string
  default     = ""
}

variable "rds_snapshot_identifier" {
  description = "Explicit snapshot ARN/id to seed the isolated RDS from. Overrides the latest-snapshot lookup when set."
  type        = string
  default     = ""
}

variable "real_prod_rds_reference" {
  description = "Existing Secrets Manager reference (name) for the REAL prod RDS creds (used when use_real_prod_db = true)."
  type        = string
  default     = ""
}

variable "real_prod_rds_secret_arn" {
  description = "ARN of the existing Secrets Manager secret for the REAL prod RDS creds, scoped into the IAM policy (used when use_real_prod_db = true)."
  type        = string
  default     = ""
}

variable "rds_identifier" {
  description = "Identifier for the new isolated RDS instance. MUST differ from prod_rds_source_identifier."
  type        = string
  default     = "sgl-prod-iaas-db"
}

# ---- Cross-variable validation rules (enforced at plan time in task 1.4) ----
#
# The following rules compare multiple variables and are therefore implemented
# as plan-time check blocks / preconditions (see main.tf, task 1.4) rather than
# variable-level `validation` blocks, to stay compatible with Terraform >= 1.5.0:
#
#   R10.6  use_real_prod_db == true  requires real_prod_rds_reference to be non-empty.
#   R10.7  create_isolated_bucket == false requires existing_bucket_name to be non-empty.
#   R10.8  rds_identifier must not equal prod_rds_source_identifier.
#   R10.9  use_real_prod_db == false requires at least one of rds_snapshot_identifier
#          or prod_rds_source_identifier to be non-empty (a snapshot source must exist).

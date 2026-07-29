# terraform-prod-iaas/modules/rds/variables.tf
#
# Inputs for the isolated, snapshot-seeded RDS instance. This module creates a
# NEW database seeded from a snapshot of production; the production instance is
# never declared as a managed resource (see main.tf for the read-only lookup).

variable "identifier" {
  description = "Identifier for the new isolated RDS instance. MUST differ from the production identifier."
  type        = string
  default     = "sgl-prod-iaas-db"
}

variable "snapshot_identifier" {
  description = "Explicit snapshot ARN/id to seed the isolated RDS from. When empty, source_db_identifier is used to look up the latest snapshot."
  type        = string
  default     = ""
}

variable "source_db_identifier" {
  description = "Production RDS identifier, used ONLY as a read-only source to find the latest snapshot to seed from. Never managed."
  type        = string
  default     = ""
}

variable "instance_class" {
  description = "RDS instance class (sizing / cost knob; db.t3.small to save)."
  type        = string
  default     = "db.t3.medium"
}

variable "allocated_storage" {
  description = "Allocated storage in GB."
  type        = number
  default     = 20
}

variable "subnet_ids" {
  description = "Private subnets (no internet-gateway route) for the DB subnet group."
  type        = list(string)
}

variable "vpc_security_group_ids" {
  description = "Security group(s) allowing inbound 3306 from the App instance SG only."
  type        = list(string)
}

variable "backup_retention_period" {
  description = "Days of automated backups; enables point-in-time recovery. Must be within 7..35."
  type        = number
  default     = 7

  validation {
    condition     = var.backup_retention_period >= 7 && var.backup_retention_period <= 35
    error_message = "backup_retention_period must be between 7 and 35 days (inclusive) so point-in-time recovery is enabled."
  }
}

variable "backup_window" {
  description = "Preferred backup window in UTC (hh:mm-hh:mm)."
  type        = string
  default     = "03:00-04:00"
}

variable "deletion_protection" {
  description = "Block accidental console/API deletion of the isolated RDS."
  type        = bool
  default     = true
}

variable "skip_final_snapshot" {
  description = "When false, take a final snapshot on destroy. Kept false so a destroy always leaves a snapshot behind."
  type        = bool
  default     = false
}

variable "final_snapshot_prefix" {
  description = "Prefix for the unique final snapshot identifier created on destroy."
  type        = string
  default     = "sgl-prod-iaas-final"
}

variable "multi_az" {
  description = "Whether to run the isolated RDS as Multi-AZ (single-AZ is cheaper for a test twin)."
  type        = bool
  default     = false
}

variable "new_master_password" {
  description = "Optional master password reset applied to the restored instance. Leave empty to keep the snapshot's credentials."
  type        = string
  default     = ""
  sensitive   = true
}

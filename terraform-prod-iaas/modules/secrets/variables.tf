variable "secret_name" {
  description = "Secret reference used by SECRETS_RDS_REFERENCE (the name the app resolves)."
  type        = string
  default     = "sgl/prod-iaas/rds-credentials"
}

variable "db_host" {
  description = "Isolated RDS endpoint (host), provided by the rds module."
  type        = string
}

variable "db_port" {
  description = "Database port."
  type        = number
  default     = 3306
}

variable "db_username" {
  description = "Master username of the isolated RDS."
  type        = string
}

variable "db_password" {
  description = "Master password of the isolated RDS. Stored only in Secrets Manager and encrypted state."
  type        = string
  sensitive   = true
}

variable "db_name" {
  description = "Database name."
  type        = string
}

variable "tags" {
  description = "Tags applied to the Secrets Manager secret."
  type        = map(string)
  default     = {}
}

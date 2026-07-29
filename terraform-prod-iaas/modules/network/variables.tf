# terraform-prod-iaas/modules/network/variables.tf
#
# Inputs for the network module. This module never modifies existing security
# groups; it resolves VPC/subnet placement (defaulting to the account's default
# VPC when inputs are empty) and creates two brand-new, dedicated security
# groups: one for the App instance and one for the isolated RDS.

variable "vpc_id" {
  description = "Existing VPC to place resources in. If empty, the account's default VPC is looked up."
  type        = string
  default     = ""
}

variable "subnet_id" {
  description = "Existing subnet for the EC2 instance. If empty, the first subnet of the resolved VPC is used."
  type        = string
  default     = ""
}

variable "ingress_cidr_https" {
  description = "CIDRs allowed to reach 443 (and 80 when open_http). Defaults to the public internet; narrow to an office range if desired."
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "open_http" {
  description = "Whether to open inbound TCP 80 for the HTTP->HTTPS redirect."
  type        = bool
  default     = true
}

variable "name_prefix" {
  description = "Naming/tag prefix applied to the created security groups."
  type        = string
  default     = "sgl-prod-iaas"
}

# Optional extra private sources allowed to reach the isolated RDS on 3306. The
# App security group is ALWAYS the primary (and normally only) allowed source;
# this list exists only for narrow, deliberate private-network exceptions.
#
# Requirement 8.5: a source CIDR that includes any publicly routable address
# (for example 0.0.0.0/0) is rejected here so such a rule is never created.
variable "db_ingress_cidrs" {
  description = "Optional extra PRIVATE (RFC1918) CIDR sources allowed to reach the isolated RDS on 3306. Publicly routable CIDRs are rejected."
  type        = list(string)
  default     = []

  validation {
    condition = alltrue([
      for c in var.db_ingress_cidrs :
      can(regex("^(10\\.|192\\.168\\.|172\\.(1[6-9]|2[0-9]|3[0-1])\\.)", c))
    ])
    error_message = "db_ingress_cidrs may only contain private (RFC1918) ranges (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16). A publicly routable source such as 0.0.0.0/0 is not permitted for RDS ingress on port 3306."
  }
}

# terraform-prod-iaas/modules/compute/variables.tf
#
# Inputs for the compute module. This module launches the single Prod IaaS
# App instance (EC2) with the IAM instance profile attached and a user-data
# script that installs Docker + docker compose, writes a stateless .env, and
# starts the app.
#
# SECURITY: no AWS access key / secret key is ever accepted or rendered by this
# module. The instance authenticates exclusively through the attached instance
# profile (see var.instance_profile_name).

variable "name_prefix" {
  description = "Naming/tag prefix for compute resources."
  type        = string
  default     = "sgl-prod-iaas"
}

variable "ami_id" {
  description = "AMI id for the App instance. When empty, the latest Amazon Linux 2023 x86_64 AMI is looked up automatically."
  type        = string
  default     = ""
}

variable "instance_type" {
  description = "EC2 instance type for the App instance."
  type        = string
  default     = "t3.medium"
}

variable "subnet_id" {
  description = "Subnet id (from the network module) to launch the App instance into."
  type        = string
}

variable "security_group_ids" {
  description = "Security group ids (from the network module) to attach to the App instance."
  type        = list(string)
}

variable "instance_profile_name" {
  description = "Name of the single IAM instance profile (from the iam module) attached to the App instance. This is the ONLY credential source on the box."
  type        = string
}

variable "root_volume_size" {
  description = "Root gp3 volume size in GB (no uploads on disk; app is stateless)."
  type        = number
  default     = 30
}

variable "env_file_vars" {
  description = "Key/value pairs rendered into the app .env by user-data. MUST NOT contain any AWS access key or secret key; the instance profile provides credentials."
  type        = map(string)

  # Defense in depth: reject any attempt to smuggle static AWS credentials
  # into the .env via this map. Requirement 5.8 / 6.2 (no keys anywhere).
  validation {
    condition = length([
      for k in keys(var.env_file_vars) : k
      if contains([
        "AWS_ACCESS_KEY_ID",
        "AWS_SECRET_ACCESS_KEY",
        "AWS_SESSION_TOKEN",
      ], upper(k))
    ]) == 0
    error_message = "env_file_vars must not contain AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, or AWS_SESSION_TOKEN; the instance authenticates via its IAM instance profile only."
  }
}

variable "compose_source" {
  description = "How the app image/docker-compose reaches the box (git clone URL, S3 artifact reference, or baked-AMI marker). Passed through to user-data."
  type        = string
  default     = ""
}

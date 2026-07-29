# terraform-prod-iaas/modules/iam/variables.tf
#
# Inputs for the EC2 identity: an IAM role + instance profile carrying a
# least-privilege inline policy. The instance authenticates exclusively through
# this profile (no static access keys anywhere), per design.md "Module: iam".

variable "name_prefix" {
  description = "Naming/tag prefix for the role, instance profile, and inline policy."
  type        = string
  default     = "sgl-prod-iaas"
}

variable "s3_bucket_arn" {
  description = "ARN of the single effective uploads bucket the instance may use. Object-level actions are scoped to the bucket ARN suffixed with /* and ListBucket to the bucket ARN itself."
  type        = string

  validation {
    condition     = length(var.s3_bucket_arn) > 0
    error_message = "s3_bucket_arn must be a non-empty bucket ARN so the S3 policy is scoped to exactly one bucket."
  }
}

variable "rds_secret_arn" {
  description = "ARN of the single effective RDS secret the instance may read via secretsmanager:GetSecretValue. No other secret is granted."
  type        = string

  validation {
    condition     = length(var.rds_secret_arn) > 0
    error_message = "rds_secret_arn must be a non-empty secret ARN so the Secrets Manager policy is scoped to exactly one secret."
  }
}

variable "attach_ssm" {
  description = "Attach the AWS-managed AmazonSSMManagedInstanceCore policy so the operator can reach the instance via SSM Session Manager (SSH stays closed)."
  type        = bool
  default     = true
}

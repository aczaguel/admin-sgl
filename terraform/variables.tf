variable "region" {
  description = "AWS region for all resources."
  type        = string
  default     = "us-east-1"
}

variable "project" {
  description = "Project tag applied to all resources."
  type        = string
  default     = "admin-sgl"
}

# NOTE: S3 bucket names are GLOBALLY unique across all AWS accounts. If these
# defaults are already taken, override them in terraform.tfvars (e.g. append a
# short account/random suffix like "sgl-uploads-dev-451797130916").
variable "dev_bucket_name" {
  description = "Name of the dev/local uploads bucket."
  type        = string
  default     = "bucket-sgl-uploads-dev"
}

variable "prod_bucket_name" {
  description = "Name of the production uploads bucket."
  type        = string
  default     = "bucket-sgl-uploads-prod"
}

variable "dev_iam_user_name" {
  description = "Name of the dedicated dev IAM user used from the local machine."
  type        = string
  default     = "sgl-uploads-dev-user"
}

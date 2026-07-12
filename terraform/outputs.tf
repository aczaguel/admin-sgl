output "dev_bucket_name" {
  description = "Dev uploads bucket name — set as S3_BUCKET in your LOCAL .env."
  value       = module.uploads_dev.bucket_id
}

output "prod_bucket_name" {
  description = "Prod uploads bucket name — set as S3_BUCKET in the EC2 .env."
  value       = module.uploads_prod.bucket_id
}

output "dev_access_key_id" {
  description = "Access key id for the dev IAM user (put in docker/aws.env locally)."
  value       = aws_iam_access_key.dev_uploads.id
}

output "dev_secret_access_key" {
  description = "Secret access key for the dev IAM user. SENSITIVE — never commit."
  value       = aws_iam_access_key.dev_uploads.secret
  sensitive   = true
}

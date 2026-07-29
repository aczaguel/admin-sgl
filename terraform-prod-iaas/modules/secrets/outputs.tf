# Only non-sensitive identifiers are exposed. The database password is never
# emitted through any output (Requirement 14.6); it lives solely in the
# encrypted secret version and encrypted Terraform state.

output "secret_arn" {
  description = "ARN of the Secrets Manager secret holding the isolated RDS credentials."
  value       = aws_secretsmanager_secret.this.arn
}

output "secret_name" {
  description = "Name of the Secrets Manager secret (used as SECRETS_RDS_REFERENCE)."
  value       = aws_secretsmanager_secret.this.name
}

# The RDS endpoint (host) written into the secret's JSON blob. This is the DB
# hostname, not a credential, so it is safe to expose. It lets property
# assertions verify that the secret the app resolves points at the ISOLATED RDS
# endpoint (fed from the rds module) rather than the production endpoint.
output "db_host" {
  description = "The RDS endpoint (host) stored in the secret; used to assert write isolation to the isolated RDS."
  value       = var.db_host
}

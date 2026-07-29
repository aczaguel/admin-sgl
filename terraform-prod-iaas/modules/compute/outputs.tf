# terraform-prod-iaas/modules/compute/outputs.tf
#
# Only non-sensitive identity/addressing values are exposed. No AWS access key,
# secret key, or database password is ever emitted from this module.

output "instance_id" {
  description = "The id of the Prod IaaS App instance."
  value       = aws_instance.app.id
}

output "private_ip" {
  description = "The private IP address of the Prod IaaS App instance."
  value       = aws_instance.app.private_ip
}

# Echo of the key/value pairs rendered into the app .env. This exposes the
# resolved, non-secret configuration (bucket name, region, SECRETS_RDS_REFERENCE,
# etc.) so plan/apply-time property assertions can verify what the .env will
# contain. It NEVER contains AWS access keys/secrets (enforced by the
# env_file_vars validation in variables.tf) or the RDS master password (the app
# resolves that at runtime from Secrets Manager via SECRETS_RDS_REFERENCE).
output "env_file_vars" {
  description = "The key/value pairs rendered into the stateless app .env (no AWS keys, no DB password)."
  value       = var.env_file_vars
}

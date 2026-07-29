# terraform-prod-iaas/modules/rds/outputs.tf
#
# The secrets module consumes host/port/username/db_name to build the RDS
# credentials secret. The master password is only ever surfaced through the
# sensitive `password_effective` output (never in plaintext outputs elsewhere).

output "endpoint" {
  description = "The RDS instance hostname (DNS address), used as the secret host."
  value       = aws_db_instance.this.address
}

output "port" {
  description = "The RDS instance port."
  value       = aws_db_instance.this.port
}

output "db_name" {
  description = "The database name of the restored instance."
  value       = aws_db_instance.this.db_name
}

output "username" {
  description = "The master username of the restored instance."
  value       = aws_db_instance.this.username
}

output "identifier" {
  description = "The isolated RDS instance identifier."
  value       = aws_db_instance.this.identifier
}

output "password_effective" {
  description = "The effective master password published for the app to consume (empty when the snapshot's inherited password is kept)."
  value       = var.new_master_password
  sensitive   = true
}

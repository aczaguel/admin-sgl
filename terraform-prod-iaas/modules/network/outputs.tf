# terraform-prod-iaas/modules/network/outputs.tf

output "security_group_id" {
  description = "ID of the App instance security group (443/80 inbound, no SSH)."
  value       = aws_security_group.app.id
}

output "db_security_group_id" {
  description = "ID of the isolated RDS security group (3306 from the App SG only)."
  value       = aws_security_group.db.id
}

output "vpc_id" {
  description = "Resolved VPC ID (explicit input or the account's default VPC)."
  value       = local.vpc_id
}

output "subnet_id" {
  description = "Resolved subnet ID for the EC2 instance."
  value       = local.subnet_id
}

output "subnet_ids" {
  description = "All subnet IDs in the resolved VPC (used for the DB subnet group)."
  value       = local.resolved_subnet_ids
}

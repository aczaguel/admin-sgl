# terraform-prod-iaas/modules/eip/outputs.tf
#
# Exposes the newly allocated Elastic IP's public address and allocation id.
# These reference only the brand-new EIP created by this module, never the
# Prod estable Elastic IP (Requirement 9.4).

output "public_ip" {
  description = "The public IPv4 address of the newly allocated Prod IaaS Elastic IP."
  value       = aws_eip.this.public_ip
}

output "allocation_id" {
  description = "The allocation ID of the newly allocated Prod IaaS Elastic IP."
  value       = aws_eip.this.allocation_id
}

# terraform-prod-iaas/outputs.tf
#
# Root outputs for the Prod IaaS stack. These expose only the values an operator
# needs to reach and identify the parallel server:
#
#   - eip_public_ip : the second productive Elastic IP address (Requirement 9.1)
#   - instance_id   : the App instance id
#   - rds_endpoint  : the ISOLATED RDS endpoint (null in cutover-rehearsal mode)
#
# Security / secret hygiene (Requirement 14.6, 6.2):
#   - The RDS master password (module.rds[0].password_effective) is NEVER output.
#   - No AWS access key or secret key is ever output.
#   - The RDS endpoint is marked sensitive to keep the DB identity out of plain
#     CLI/log output.

output "eip_public_ip" {
  description = "The public IPv4 address of the second (Prod IaaS) Elastic IP, running in parallel with Prod estable."
  value       = module.eip.public_ip
}

output "instance_id" {
  description = "The id of the Prod IaaS App EC2 instance."
  value       = module.compute.instance_id
}

# The rds module is gated by count (0 when use_real_prod_db = true, where the app
# points at the real prod DB and no isolated instance is created). Guard the
# reference with try(...) so the output resolves to null in cutover-rehearsal
# mode instead of erroring on an out-of-range index.
output "rds_endpoint" {
  description = "The endpoint (host) of the isolated snapshot-seeded RDS instance; null when running in cutover-rehearsal mode (use_real_prod_db = true)."
  value       = try(module.rds[0].endpoint, null)
  sensitive   = true
}

output "scheduler_lambda_name" {
  description = "Name of the EC2 start/stop scheduler Lambda."
  value       = module.scheduler.lambda_name
}

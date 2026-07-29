output "instance_profile_name" {
  description = "Name of the instance profile to attach to the App instance."
  value       = aws_iam_instance_profile.this.name
}

output "role_arn" {
  description = "ARN of the EC2 role backing the instance profile."
  value       = aws_iam_role.this.arn
}

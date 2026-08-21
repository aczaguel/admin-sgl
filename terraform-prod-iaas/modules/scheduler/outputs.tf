# terraform-prod-iaas/modules/scheduler/outputs.tf

output "lambda_arn" {
  description = "ARN of the EC2 scheduler Lambda function."
  value       = aws_lambda_function.scheduler.arn
}

output "lambda_name" {
  description = "Name of the EC2 scheduler Lambda function."
  value       = aws_lambda_function.scheduler.function_name
}

output "start_rule_arn" {
  description = "ARN of the EventBridge rule that starts the instance."
  value       = aws_cloudwatch_event_rule.start.arn
}

output "stop_rule_arn" {
  description = "ARN of the EventBridge rule that stops the instance."
  value       = aws_cloudwatch_event_rule.stop.arn
}

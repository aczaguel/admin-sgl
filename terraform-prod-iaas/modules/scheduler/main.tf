# terraform-prod-iaas/modules/scheduler/main.tf
#
# Schedules daily EC2 start/stop via EventBridge + Lambda.
# The Lambda is packaged inline from the lambda/ subdirectory so no
# external build step is required.
#
# Schedule (default, overridable via variables):
#   Start : 08:00 CDMX = 14:00 UTC  → cron(0 14 * * ? *)
#   Stop  : 20:00 CDMX = 02:00 UTC  → cron(0 2  * * ? *)

terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

# ---- Package the Lambda source -----------------------------------------------

data "archive_file" "lambda_zip" {
  type        = "zip"
  source_file = "${path.module}/lambda/index.py"
  output_path = "${path.module}/lambda/scheduler.zip"
}

# ---- IAM role for the Lambda -------------------------------------------------

data "aws_iam_policy_document" "lambda_assume" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["lambda.amazonaws.com"]
    }
  }
}

data "aws_iam_policy_document" "lambda_policy" {
  # CloudWatch Logs
  statement {
    sid    = "Logs"
    effect = "Allow"
    actions = [
      "logs:CreateLogGroup",
      "logs:CreateLogStream",
      "logs:PutLogEvents",
    ]
    resources = ["arn:aws:logs:*:*:*"]
  }

  # EC2 start/stop scoped to the single instance
  statement {
    sid    = "EC2StartStop"
    effect = "Allow"
    actions = [
      "ec2:StartInstances",
      "ec2:StopInstances",
      "ec2:DescribeInstances",
    ]
    resources = ["*"]
  }
}

resource "aws_iam_role" "scheduler" {
  name               = "${var.name_prefix}-scheduler-role"
  assume_role_policy = data.aws_iam_policy_document.lambda_assume.json

  tags = {
    Name = "${var.name_prefix}-scheduler-role"
  }
}

resource "aws_iam_role_policy" "scheduler" {
  name   = "${var.name_prefix}-scheduler-policy"
  role   = aws_iam_role.scheduler.id
  policy = data.aws_iam_policy_document.lambda_policy.json
}

# ---- Lambda function ---------------------------------------------------------

resource "aws_lambda_function" "scheduler" {
  function_name    = "${var.name_prefix}-ec2-scheduler"
  role             = aws_iam_role.scheduler.arn
  runtime          = "python3.12"
  handler          = "index.handler"
  filename         = data.archive_file.lambda_zip.output_path
  source_code_hash = data.archive_file.lambda_zip.output_base64sha256
  timeout          = 30

  environment {
    variables = {
      INSTANCE_ID = var.instance_id
    }
  }

  tags = {
    Name = "${var.name_prefix}-ec2-scheduler"
  }
}

# ---- EventBridge rules -------------------------------------------------------

resource "aws_cloudwatch_event_rule" "start" {
  name                = "${var.name_prefix}-ec2-start"
  description         = "Start ${var.name_prefix} EC2 at 08:00 CDMX every day"
  schedule_expression = var.start_cron
  state               = var.enabled ? "ENABLED" : "DISABLED"
}

resource "aws_cloudwatch_event_rule" "stop" {
  name                = "${var.name_prefix}-ec2-stop"
  description         = "Stop ${var.name_prefix} EC2 at 20:00 CDMX every day"
  schedule_expression = var.stop_cron
  state               = var.enabled ? "ENABLED" : "DISABLED"
}

# ---- EventBridge targets (with action payload) --------------------------------

resource "aws_cloudwatch_event_target" "start" {
  rule  = aws_cloudwatch_event_rule.start.name
  arn   = aws_lambda_function.scheduler.arn
  input = jsonencode({ action = "start" })
}

resource "aws_cloudwatch_event_target" "stop" {
  rule  = aws_cloudwatch_event_rule.stop.name
  arn   = aws_lambda_function.scheduler.arn
  input = jsonencode({ action = "stop" })
}

# ---- Lambda permissions for EventBridge to invoke ----------------------------

resource "aws_lambda_permission" "start" {
  statement_id  = "AllowEventBridgeStart"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.scheduler.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.start.arn
}

resource "aws_lambda_permission" "stop" {
  statement_id  = "AllowEventBridgeStop"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.scheduler.function_name
  principal     = "events.amazonaws.com"
  source_arn    = aws_cloudwatch_event_rule.stop.arn
}

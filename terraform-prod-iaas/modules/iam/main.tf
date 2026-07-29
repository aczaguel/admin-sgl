# -----------------------------------------------------------------------------
# IAM module: EC2 role + instance profile with least-privilege permissions.
#
# The App instance authenticates ONLY through this instance profile; no static
# AWS access keys are ever provisioned. The inline policy is scoped to exactly
# two resources:
#   - the single effective uploads bucket (object actions on "${arn}/*",
#     ListBucket on the bucket ARN), and
#   - the single effective RDS secret (GetSecretValue only).
#
# There are no `Resource = "*"` statements and no actions beyond the enumerated
# set (design.md "Module: iam", Correctness Property 5, Requirements 6.3-6.6).
# Operator shell access is provided via SSM Session Manager by attaching the
# AWS-managed AmazonSSMManagedInstanceCore policy (Requirement 7.5).
# -----------------------------------------------------------------------------

# Trust policy: only the EC2 service may assume this role.
data "aws_iam_policy_document" "assume_role" {
  statement {
    sid     = "AllowEC2AssumeRole"
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["ec2.amazonaws.com"]
    }
  }
}

# Least-privilege inline policy document: two resources only, no wildcards.
data "aws_iam_policy_document" "instance" {
  # S3 object-level actions scoped to the objects inside the one bucket.
  statement {
    sid    = "S3ObjectAccess"
    effect = "Allow"
    actions = [
      "s3:GetObject",
      "s3:PutObject",
      "s3:DeleteObject",
    ]
    resources = ["${var.s3_bucket_arn}/*"]
  }

  # S3 ListBucket scoped to the one bucket ARN itself.
  statement {
    sid       = "S3ListBucket"
    effect    = "Allow"
    actions   = ["s3:ListBucket"]
    resources = [var.s3_bucket_arn]
  }

  # Secrets Manager read scoped to the one RDS secret ARN only.
  statement {
    sid       = "SecretsManagerRead"
    effect    = "Allow"
    actions   = ["secretsmanager:GetSecretValue"]
    resources = [var.rds_secret_arn]
  }
}

resource "aws_iam_role" "this" {
  name               = "${var.name_prefix}-ec2-role"
  assume_role_policy = data.aws_iam_policy_document.assume_role.json

  tags = {
    Name = "${var.name_prefix}-ec2-role"
  }
}

resource "aws_iam_role_policy" "this" {
  name   = "${var.name_prefix}-least-privilege"
  role   = aws_iam_role.this.id
  policy = data.aws_iam_policy_document.instance.json
}

# Operator access via SSM Session Manager (gated by attach_ssm).
resource "aws_iam_role_policy_attachment" "ssm" {
  count      = var.attach_ssm ? 1 : 0
  role       = aws_iam_role.this.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonSSMManagedInstanceCore"
}

resource "aws_iam_instance_profile" "this" {
  name = "${var.name_prefix}-instance-profile"
  role = aws_iam_role.this.name
}

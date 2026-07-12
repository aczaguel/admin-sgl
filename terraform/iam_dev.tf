# -----------------------------------------------------------------------------
# Dedicated DEV IAM user for local development.
#
# This user's access keys are the ONLY AWS keys that ever exist for this app,
# and they are used exclusively from your local machine/container. Production
# uses the EC2 IAM Instance Profile and has NO keys.
#
# The policy is least-privilege: it can only read/write objects in the DEV
# bucket, nothing else.
# -----------------------------------------------------------------------------

resource "aws_iam_user" "dev_uploads" {
  name = var.dev_iam_user_name
  tags = { Environment = "dev" }
}

data "aws_iam_policy_document" "dev_uploads" {
  # List only the dev bucket.
  statement {
    sid       = "ListDevBucket"
    effect    = "Allow"
    actions   = ["s3:ListBucket"]
    resources = [module.uploads_dev.bucket_arn]
  }

  # Object-level read/write only inside the dev bucket.
  statement {
    sid    = "ReadWriteDevObjects"
    effect = "Allow"
    actions = [
      "s3:PutObject",
      "s3:GetObject",
      "s3:DeleteObject",
    ]
    resources = ["${module.uploads_dev.bucket_arn}/*"]
  }
}

resource "aws_iam_user_policy" "dev_uploads" {
  name   = "sgl-uploads-dev-access"
  user   = aws_iam_user.dev_uploads.name
  policy = data.aws_iam_policy_document.dev_uploads.json
}

# Access key for local use. Terraform stores the SECRET in state, so treat the
# state file as sensitive (see README) and prefer a remote encrypted backend.
resource "aws_iam_access_key" "dev_uploads" {
  user = aws_iam_user.dev_uploads.name
}

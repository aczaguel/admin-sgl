# =============================================================================
# Task 3.2 - Plan assertion that IAM is scoped to exactly two ARNs.
#
# Property 5 (Least privilege): the EC2 role grants S3 actions only on the one
# effective bucket ARN (the bucket ARN and "${bucket_arn}/*") and
# secretsmanager:GetSecretValue only on the one effective RDS secret ARN, with
# no wildcard ("*") resources and no actions beyond the enumerated set.
#
# Validates: Requirements 6.3, 6.4, 6.5, 6.6
#
# Runs entirely offline (command = plan). Note on provider choice: a
# `mock_provider "aws" {}` replaces the *computed* `json` attribute of the
# `aws_iam_policy_document` data source with a generated mock value, so the real
# rendered policy cannot be inspected under a mock. Instead we configure the AWS
# provider with mock credentials plus the skip_* flags, which makes zero network
# calls (aws_iam_policy_document is rendered client-side and `plan` performs no
# create API calls) while still producing the genuine policy JSON to assert on.
# =============================================================================

provider "aws" {
  region                      = "us-east-1"
  access_key                  = "mock_access_key"
  secret_key                  = "mock_secret_key"
  skip_credentials_validation = true
  skip_requesting_account_id  = true
  skip_metadata_api_check     = true
}

run "iam_policy_scoped_to_exactly_two_arns" {
  command = plan

  variables {
    name_prefix    = "sgl-prod-iaas-test"
    s3_bucket_arn  = "arn:aws:s3:::sgl-prod-iaas-test-bucket"
    rds_secret_arn = "arn:aws:secretsmanager:us-east-1:123456789012:secret:sgl/prod-iaas/rds-credentials-AbCdEf"
    attach_ssm     = true
  }

  # --- Requirements 6.3/6.4/6.5: every Resource in the inline policy is one of
  # the exactly two effective ARNs (the bucket ARN, its object form
  # "${bucket_arn}/*", and the RDS secret ARN) -- nothing else is referenced.
  assert {
    condition = alltrue([
      for st in jsondecode(data.aws_iam_policy_document.instance.json).Statement :
      alltrue([
        for r in flatten([st.Resource]) :
        contains([
          "arn:aws:s3:::sgl-prod-iaas-test-bucket",
          "arn:aws:s3:::sgl-prod-iaas-test-bucket/*",
          "arn:aws:secretsmanager:us-east-1:123456789012:secret:sgl/prod-iaas/rds-credentials-AbCdEf",
        ], r)
      ])
    ])
    error_message = "Inline policy references a resource other than the bucket ARN, its /* object form, or the RDS secret ARN."
  }

  # --- The complete set of referenced resources equals exactly the three
  # expected strings, proving only the two underlying ARNs (bucket + secret) are
  # in scope and no additional resource has been added.
  assert {
    condition = toset(flatten([
      for st in jsondecode(data.aws_iam_policy_document.instance.json).Statement : flatten([st.Resource])
      ])) == toset([
      "arn:aws:s3:::sgl-prod-iaas-test-bucket",
      "arn:aws:s3:::sgl-prod-iaas-test-bucket/*",
      "arn:aws:secretsmanager:us-east-1:123456789012:secret:sgl/prod-iaas/rds-credentials-AbCdEf",
    ])
    error_message = "The set of policy resources must be exactly the bucket ARN, the bucket object ARN (/*), and the RDS secret ARN."
  }

  # --- Requirement 6.6: no statement uses a wildcard "*" resource.
  assert {
    condition = alltrue([
      for st in jsondecode(data.aws_iam_policy_document.instance.json).Statement :
      !contains(flatten([st.Resource]), "*")
    ])
    error_message = "Inline policy must not contain a wildcard (\"*\") resource."
  }

  # --- Requirement 6.6: no action beyond the enumerated least-privilege set.
  assert {
    condition = alltrue([
      for st in jsondecode(data.aws_iam_policy_document.instance.json).Statement :
      alltrue([
        for a in flatten([st.Action]) :
        contains([
          "s3:GetObject",
          "s3:PutObject",
          "s3:DeleteObject",
          "s3:ListBucket",
          "secretsmanager:GetSecretValue",
        ], a)
      ])
    ])
    error_message = "Inline policy grants an action outside the enumerated set (GetObject/PutObject/DeleteObject/ListBucket/GetSecretValue)."
  }

  # --- No action may be a wildcard "*" or service-level wildcard (e.g. "s3:*").
  assert {
    condition = alltrue([
      for st in jsondecode(data.aws_iam_policy_document.instance.json).Statement :
      alltrue([
        for a in flatten([st.Action]) :
        !strcontains(a, "*")
      ])
    ])
    error_message = "Inline policy must not contain any wildcard action."
  }

  # --- Requirement 6.3/6.4: S3 actions are scoped only to the bucket ARN and
  # its object form -- the RDS secret ARN never appears on an S3 statement.
  assert {
    condition = alltrue([
      for st in jsondecode(data.aws_iam_policy_document.instance.json).Statement :
      alltrue([for r in flatten([st.Resource]) :
        contains([
          "arn:aws:s3:::sgl-prod-iaas-test-bucket",
          "arn:aws:s3:::sgl-prod-iaas-test-bucket/*",
      ], r)])
      if length([for a in flatten([st.Action]) : a if startswith(a, "s3:")]) > 0
    ])
    error_message = "S3 actions must be scoped only to the bucket ARN and its /* object form."
  }

  # --- Requirement 6.5: secretsmanager:GetSecretValue is scoped only to the one
  # RDS secret ARN.
  assert {
    condition = alltrue([
      for st in jsondecode(data.aws_iam_policy_document.instance.json).Statement :
      flatten([st.Resource]) == ["arn:aws:secretsmanager:us-east-1:123456789012:secret:sgl/prod-iaas/rds-credentials-AbCdEf"]
      if contains(flatten([st.Action]), "secretsmanager:GetSecretValue")
    ])
    error_message = "secretsmanager:GetSecretValue must be scoped only to the single RDS secret ARN."
  }
}

# terraform-prod-iaas/modules/compute/tests/no_aws_keys.tftest.hcl
#
# Task 6.3 — Assertion that no AWS keys appear in user-data (the rendered .env).
#
# Property 4 (No AWS keys anywhere): no module writes an AWS access key / secret
# key / session token into the .env, user-data, or any output. Authentication
# is exclusively via the EC2 IAM instance profile.
#
# Validates: Requirements 6.2, 5.8, 14.6
#
# This suite runs entirely offline (command = plan against a mock_provider), so
# no AWS credentials or real resources are required.
#
#   terraform init -backend=false
#   terraform test
#
# Assertion strategy (the most robust choice, per two complementary angles):
#
#   1. The rendered .env is produced solely from env_file_vars as one
#      "KEY=VALUE" line per map entry. So the authoritative, deterministic check
#      is that env_file_vars carries NONE of the AWS credential key names. This
#      is known at plan time and is not confused by any literal text elsewhere.
#
#   2. We ALSO render the user-data template exactly as the module does
#      (templatefile with the same inputs) and assert that the rendered .env
#      section contains no line of the form `^AWS_ACCESS_KEY_ID=` (etc.). We use
#      a line-anchored regex ((?m)^\s*NAME\s*=) instead of a naive substring
#      match on purpose: the template legitimately mentions the strings
#      AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY / AWS_SESSION_TOKEN inside its
#      defense-in-depth `grep` guard, so a substring match would wrongly flag
#      that guard. A real credential would appear as an assignment LINE, which
#      only originates from env_file_vars.
#
# We do NOT assert on aws_instance.app.user_data directly: the AWS provider
# stores user_data as a hash in state, so the resource attribute is not the raw
# rendered script. Rendering the template ourselves gives us the true content.

mock_provider "aws" {}

# Representative stateless .env inputs — exactly what the root module feeds the
# compute module in isolated-default mode. Contains NO AWS key material.
variables {
  name_prefix           = "sgl-prod-iaas-test"
  subnet_id             = "subnet-0123456789abcdef0"
  security_group_ids    = ["sg-0123456789abcdef0"]
  instance_profile_name = "sgl-prod-iaas-test-profile"
  compose_source        = ""
  env_file_vars = {
    FILE_STORAGE_DRIVER   = "s3"
    S3_BUCKET             = "bucket-sgl-uploads-prod-iaas"
    S3_REGION             = "us-east-1"
    SECRETS_PROVIDER      = "aws"
    SECRETS_RDS_REFERENCE = "sgl/prod-iaas/rds-credentials"
    SECRETS_REGION        = "us-east-1"
  }
}

run "no_aws_keys_in_user_data_or_env" {
  command = plan

  # --- Angle 1 (authoritative): env_file_vars carries none of the AWS
  # credential key names, so no AWS_* assignment can ever reach the rendered
  # .env. Case-insensitive to reject AWS_access_key_id etc. as well.
  assert {
    condition = length([
      for k in keys(var.env_file_vars) : k
      if contains([
        "AWS_ACCESS_KEY_ID",
        "AWS_SECRET_ACCESS_KEY",
        "AWS_SESSION_TOKEN",
      ], upper(k))
    ]) == 0
    error_message = "env_file_vars must not contain AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, or AWS_SESSION_TOKEN: no AWS key material may reach the .env."
  }

  # --- Angle 2 (rendered user-data): render the template exactly as the module
  # does and assert no line of the form `^\s*AWS_ACCESS_KEY_ID\s*=` (or the
  # secret/session variants) appears. The (?m) flag anchors ^ to each line, so
  # the guard text `if grep -Eiq '^[[:space:]]*(AWS_ACCESS_KEY_ID|...)` is NOT
  # matched (that line starts with `if grep`, not with the key name).
  assert {
    condition = length(regexall(
      "(?mi)^[[:space:]]*(AWS_ACCESS_KEY_ID|AWS_SECRET_ACCESS_KEY|AWS_SESSION_TOKEN)[[:space:]]*=",
      templatefile("${path.module}/user-data.sh.tftpl", {
        env_file_vars  = var.env_file_vars
        compose_source = var.compose_source
      })
    )) == 0
    error_message = "The rendered user-data .env must contain no AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY / AWS_SESSION_TOKEN assignment line."
  }

  # --- Sanity: rendering actually produced the expected stateless .env lines,
  # proving the negative assertions above ran against real content (not an empty
  # or failed render).
  assert {
    condition = alltrue([
      for line in [
        "FILE_STORAGE_DRIVER=s3",
        "S3_BUCKET=bucket-sgl-uploads-prod-iaas",
        "SECRETS_PROVIDER=aws",
        "SECRETS_RDS_REFERENCE=sgl/prod-iaas/rds-credentials",
        ] : strcontains(templatefile("${path.module}/user-data.sh.tftpl", {
          env_file_vars  = var.env_file_vars
          compose_source = var.compose_source
      }), line)
    ])
    error_message = "The rendered user-data must contain the expected stateless .env lines (FILE_STORAGE_DRIVER, S3_BUCKET, SECRETS_PROVIDER, SECRETS_RDS_REFERENCE)."
  }
}

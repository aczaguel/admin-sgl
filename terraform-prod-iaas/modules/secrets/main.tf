# -----------------------------------------------------------------------------
# Secrets module.
#
# Stores the isolated RDS credentials as a Secrets Manager secret whose JSON
# blob matches the shape the SGL application already consumes at runtime via
# SECRETS_PROVIDER=aws:
#
#   { "host": ..., "port": ..., "username": ..., "password": ..., "dbname": ... }
#
# The master password is provided as a sensitive input and is written ONLY into
# the encrypted secret version (and encrypted Terraform state). It is never
# surfaced through any output of this module (Requirements 14.5, 14.6).
# -----------------------------------------------------------------------------

terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

resource "aws_secretsmanager_secret" "this" {
  name        = var.secret_name
  description = "Isolated RDS credentials for the SGL Prod IaaS application."
  tags        = var.tags
}

resource "aws_secretsmanager_secret_version" "this" {
  secret_id = aws_secretsmanager_secret.this.id

  secret_string = jsonencode({
    host     = var.db_host
    port     = var.db_port
    username = var.db_username
    password = var.db_password
    dbname   = var.db_name
  })
}

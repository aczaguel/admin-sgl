terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }

  # -----------------------------------------------------------------------
  # Remote state (recommended next step for the certification track):
  # once the dev bucket exists you can promote Terraform's own state to an
  # S3 backend with DynamoDB locking. Left commented so the FIRST apply can
  # run with local state (no chicken-and-egg). See terraform/README.md.
  #
  # backend "s3" {
  #   bucket         = "sgl-terraform-state"
  #   key            = "s3-file-storage/terraform.tfstate"
  #   region         = "us-east-1"
  #   dynamodb_table = "sgl-terraform-locks"
  #   encrypt        = true
  # }
  # -----------------------------------------------------------------------
}

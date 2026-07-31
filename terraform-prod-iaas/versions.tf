terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }

  # Separate state key so Prod IaaS NEVER shares/clobbers the existing stack's state.
  backend "s3" {
    bucket         = "bucket-sgl-terraform-state"
    key            = "prod-iaas/terraform.tfstate" # distinct from s3-file-storage/...
    region         = "us-east-1"
    dynamodb_table = "sgl-terraform-locks"
    encrypt        = true
  }
}

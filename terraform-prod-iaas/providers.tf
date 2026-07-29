provider "aws" {
  region = var.region

  # Every resource created by this stack is tagged so it is unambiguously
  # identifiable as Prod IaaS only and never confused with Prod estable.
  default_tags {
    tags = {
      Project   = var.project
      ManagedBy = "terraform"
      Server    = "prod-iaas"
    }
  }
}

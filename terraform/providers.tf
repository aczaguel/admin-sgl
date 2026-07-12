provider "aws" {
  region = var.region

  # Tags applied to every taggable resource, so everything this stack creates
  # is easy to find and attribute in the console / cost explorer.
  default_tags {
    tags = {
      Project   = var.project
      ManagedBy = "terraform"
    }
  }
}

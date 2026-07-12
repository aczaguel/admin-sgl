# Two uploads buckets from the SAME reusable module: dev (consumed from your
# local machine) and prod (consumed by the EC2 via its Instance Profile).

module "uploads_dev" {
  source = "./modules/s3_bucket"

  bucket_name        = var.dev_bucket_name
  versioning_enabled = false # throwaway dev data; keep it cheap
  tags               = { Environment = "dev" }
}

module "uploads_prod" {
  source = "./modules/s3_bucket"

  bucket_name        = var.prod_bucket_name
  versioning_enabled = true # recoverable in production
  tags               = { Environment = "prod" }
}

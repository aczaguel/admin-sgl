# -----------------------------------------------------------------------------
# Reusable private S3 bucket module.
#
# Creates a single private bucket hardened with the settings the SGL file
# storage abstraction expects:
#   - Server-side encryption (SSE-S3 / AES256) enabled by default.
#   - Versioning (optional) so an accidental overwrite/delete is recoverable.
#   - Full "block public access" so objects are ONLY reachable via the
#     presigned URLs the app generates.
#
# This module is intentionally small so it can be reused for the dev bucket,
# the prod bucket, and any future environment by instantiating it again.
# -----------------------------------------------------------------------------

resource "aws_s3_bucket" "this" {
  bucket = var.bucket_name
  tags   = var.tags
}

# Block every form of public access. Objects are served exclusively through
# the short-lived presigned URLs produced by S3FileStorage::url().
resource "aws_s3_bucket_public_access_block" "this" {
  bucket = aws_s3_bucket.this.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

# Default server-side encryption at rest (SSE-S3 / AES256). Matches the
# ServerSideEncryption the app sends on every PutObject.
resource "aws_s3_bucket_server_side_encryption_configuration" "this" {
  bucket = aws_s3_bucket.this.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

# Optional versioning. Recommended for prod so an overwrite/delete is
# recoverable; can be disabled for a throwaway dev bucket.
resource "aws_s3_bucket_versioning" "this" {
  bucket = aws_s3_bucket.this.id

  versioning_configuration {
    status = var.versioning_enabled ? "Enabled" : "Disabled"
  }
}

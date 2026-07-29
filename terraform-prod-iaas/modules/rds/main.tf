# terraform-prod-iaas/modules/rds/main.tf
#
# Isolated, snapshot-seeded RDS with backups and destroy guards.
#
# Safety model (see design.md "Module: rds" and "Correctness Properties"):
#   - The production RDS is NEVER a managed resource here. It is touched only
#     through the read-only `data "aws_db_snapshot"` lookup below, which also
#     lets us surface an error when the source snapshot is unencrypted.
#   - The instance is placed in a private DB subnet group (internet-gateway-free
#     subnets supplied by the caller) and is not publicly accessible.
#   - Destroy guards: prevent_destroy (literal), deletion_protection, and a
#     non-skipped final snapshot with a unique identifier.

# Read-only lookup of the seeding snapshot. Used to (a) verify the source is
# encrypted before we ever create the instance, and (b) resolve the latest
# snapshot when only the source_db_identifier is provided.
data "aws_db_snapshot" "seed" {
  db_snapshot_identifier = var.snapshot_identifier != "" ? var.snapshot_identifier : null
  db_instance_identifier = var.snapshot_identifier == "" && var.source_db_identifier != "" ? var.source_db_identifier : null
  most_recent            = var.snapshot_identifier == "" ? true : false
}

# Private DB subnet group over internet-gateway-free subnets (Req 8.2).
resource "aws_db_subnet_group" "this" {
  name       = "${var.identifier}-subnets"
  subnet_ids = var.subnet_ids

  tags = {
    Name = "${var.identifier}-subnets"
  }
}

resource "aws_db_instance" "this" {
  identifier          = var.identifier
  snapshot_identifier = var.snapshot_identifier != "" ? var.snapshot_identifier : data.aws_db_snapshot.seed.id
  instance_class      = var.instance_class
  allocated_storage   = var.allocated_storage

  db_subnet_group_name   = aws_db_subnet_group.this.name
  vpc_security_group_ids = var.vpc_security_group_ids
  multi_az               = var.multi_az
  publicly_accessible    = false

  # Backups managed in Terraform (retention > 0 enables PITR).
  backup_retention_period = var.backup_retention_period
  backup_window           = var.backup_window

  # Destroy guards. skip_final_snapshot = false means a destroy still produces a
  # final snapshot; its identifier is made unique so it never collides.
  deletion_protection       = var.deletion_protection
  skip_final_snapshot       = var.skip_final_snapshot
  final_snapshot_identifier = var.skip_final_snapshot ? null : "${var.final_snapshot_prefix}-${formatdate("YYYYMMDDhhmmss", timestamp())}"

  # Optional master password reset on the restored instance. When empty, the
  # snapshot's inherited credentials are kept.
  password = var.new_master_password != "" ? var.new_master_password : null

  lifecycle {
    prevent_destroy = true # a plain `terraform destroy` cannot remove it (Req 3.3)

    # ignore_changes keeps re-applies a no-op (Property 9 / Req 12.1, 12.2, 12.3):
    #   - snapshot_identifier: don't re-restore the instance on later applies
    #     (Req 3.6, 12.3). Snapshot metadata differences must not force a replace.
    #   - final_snapshot_identifier: this attribute embeds timestamp() (see below),
    #     which is re-evaluated on every plan and would otherwise surface a spurious
    #     "1 to change" diff on each re-apply, violating idempotency (Req 12.1). It
    #     only matters at destroy time, so ignoring in-place changes is safe.
    ignore_changes = [snapshot_identifier, final_snapshot_identifier]

    # Fail closed when the source snapshot is unencrypted so we never create an
    # unencrypted isolated instance (Req 14.4). An encrypted source yields an
    # encrypted restore automatically (Req 14.3).
    precondition {
      condition     = data.aws_db_snapshot.seed.encrypted
      error_message = "Source snapshot '${data.aws_db_snapshot.seed.id}' is not encrypted; refusing to create an unencrypted isolated RDS. Provide an encrypted snapshot source."
    }
  }
}

# terraform-prod-iaas/modules/eip/main.tf
#
# Allocates the SECOND productive Elastic IP for the SGL platform and binds it
# to the Prod IaaS App instance so it runs in parallel with Prod estable on its
# own public address.
#
# ISOLATION (Requirements 9.3, 9.4): this module allocates a brand-new EIP and
# associates it with var.instance_id only. It never references, imports, or
# modifies the Prod estable Elastic IP, so Prod estable's address is untouched.
#
# FAILURE BEHAVIOR (Requirements 9.5, 9.6): allocation and association are two
# distinct resources. Terraform creates the aws_eip first; if allocation fails
# the apply halts with an allocation error and the association is never created
# (no EIP is bound to the instance). If the association subsequently fails,
# Terraform surfaces the association error and the instance is left without a
# newly associated Elastic IP.

terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

# Exactly one new Elastic IP, allocated in the VPC domain (Requirement 9.1).
resource "aws_eip" "this" {
  domain = "vpc"

  tags = {
    Name = "${var.name_prefix}-eip"
  }
}

# Bind the newly allocated Elastic IP to the Prod IaaS App instance so exactly
# one EIP is associated with the instance (Requirement 9.2).
resource "aws_eip_association" "this" {
  instance_id   = var.instance_id
  allocation_id = aws_eip.this.allocation_id
}

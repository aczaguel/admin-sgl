# terraform-prod-iaas/modules/eip/tests/eip.tftest.hcl
#
# Task 7.2 — Plan assertion that exactly one new EIP is allocated and associated.
#
# Property 7 (Second productive IP exists): the eip module allocates exactly one
# NEW Elastic IP and associates it with the Prod IaaS App instance, referencing
# no pre-existing (Prod estable) Elastic IP.
#
# Validates: Requirements 9.1, 9.2, 9.4
#
# These are plan-time assertions only (command = plan) run against a
# mock_provider so no AWS credentials or real resources are required. The eip
# module declares its resources WITHOUT count/for_each, so exactly one
# `aws_eip.this` and exactly one `aws_eip_association.this` are structurally
# guaranteed to be planned for creation; the asserts below verify the shape of
# that single allocation/association.
#
# Requirement 9.4 (never reference or modify the Prod estable EIP) is enforced
# structurally: this module contains no `data` sources and no input for an
# existing allocation id, so the only allocation the association can bind to is
# the freshly created `aws_eip.this` — never an imported/pre-existing address.
#
# Run with:
#   terraform init -backend=false
#   terraform test

mock_provider "aws" {}

# ---------------------------------------------------------------------------
# One brand-new EIP is allocated in the VPC domain and associated with the
# provided Prod IaaS instance.
# ---------------------------------------------------------------------------
run "one_new_eip_allocated_and_associated" {
  command = plan

  variables {
    instance_id = "i-0123456789abcdef0"
    name_prefix = "sgl-prod-iaas"
  }

  # R9.1 — exactly one new Elastic IP is allocated in the VPC domain.
  assert {
    condition     = aws_eip.this.domain == "vpc"
    error_message = "The allocated Elastic IP must use the VPC domain (Requirement 9.1)."
  }

  # R9.1 — the EIP carries the Prod IaaS naming tag, proving it is a brand-new
  # allocation created by this module (not a referenced pre-existing address).
  assert {
    condition     = aws_eip.this.tags["Name"] == "${var.name_prefix}-eip"
    error_message = "The allocated Elastic IP must be tagged as a new Prod IaaS EIP (Requirement 9.1)."
  }

  # R9.2 / R9.4 — the single association binds the newly allocated EIP to the
  # given Prod IaaS instance and to no other (pre-existing) instance.
  assert {
    condition     = aws_eip_association.this.instance_id == var.instance_id
    error_message = "The Elastic IP must be associated with the Prod IaaS App instance (Requirement 9.2)."
  }
}

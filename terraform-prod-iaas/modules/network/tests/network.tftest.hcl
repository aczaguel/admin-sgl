# terraform-prod-iaas/modules/network/tests/network.tftest.hcl
#
# Plan-based assertions for the network module's firewall posture.
#
# Property 6 (SSH is closed): the created App security group has NO ingress rule
# for TCP port 22. This is verified by asserting that the only ingress ports on
# the App SG are 443 (and optionally 80), and that port 22 never appears.
#
# Also asserts the RDS network protection: the DB security group's 3306 ingress
# references the App security group as its ONLY source (Requirements 8.3, 8.4).
#
# The App<->DB rules use referenced_security_group_id and the module resolves
# the default VPC through data sources, so these tests run against a MOCKED AWS
# provider. Because the DB->App reference resolves to a computed security group
# id (unknown until applied), the runs use command = apply against the mock
# provider. No real AWS API is ever called, so the suite stays fully offline
# (no AWS credentials or a real default VPC required).
#
# Validates: Requirements 7.1, 7.6, 8.3, 8.4

mock_provider "aws" {}

variables {
  name_prefix        = "sgl-prod-iaas-test"
  ingress_cidr_https = ["0.0.0.0/0"]
  open_http          = true
  db_ingress_cidrs   = []
}

# ---------------------------------------------------------------------------
# Property 6: SSH is closed AND DB access is restricted to the App SG.
# open_http = true, so both 443 and 80 are present but 22 is not.
# ---------------------------------------------------------------------------
run "ssh_closed_and_db_restricted_with_http" {
  command = apply

  # Every HTTPS ingress rule opens only port 443.
  assert {
    condition = alltrue([
      for r in values(aws_vpc_security_group_ingress_rule.app_https) :
      r.from_port == 443 && r.to_port == 443
    ])
    error_message = "App HTTPS ingress rules must open only TCP port 443."
  }

  # When open_http = true, every HTTP ingress rule opens only port 80.
  assert {
    condition = alltrue([
      for r in values(aws_vpc_security_group_ingress_rule.app_http) :
      r.from_port == 80 && r.to_port == 80
    ])
    error_message = "App HTTP ingress rules must open only TCP port 80."
  }

  # The complete set of App ingress ports is a subset of {443, 80}: nothing
  # else (and specifically not 22) may be opened on the App security group.
  assert {
    condition = length(setsubtract(
      toset(concat(
        [for r in values(aws_vpc_security_group_ingress_rule.app_https) : r.from_port],
        [for r in values(aws_vpc_security_group_ingress_rule.app_http) : r.from_port],
      )),
      toset([443, 80])
    )) == 0
    error_message = "App security group ingress ports must be limited to 443/80 (no SSH/22 or any other port)."
  }

  # Explicit port-22 check: 22 must never be an App ingress port.
  assert {
    condition = !contains(
      concat(
        [for r in values(aws_vpc_security_group_ingress_rule.app_https) : r.from_port],
        [for r in values(aws_vpc_security_group_ingress_rule.app_http) : r.from_port],
      ),
      22
    )
    error_message = "SSH (TCP port 22) ingress must never be present on the App security group. Operator access is via SSM only."
  }

  # DB 3306 ingress opens only the MySQL port.
  assert {
    condition = (
      aws_vpc_security_group_ingress_rule.db_from_app.from_port == 3306 &&
      aws_vpc_security_group_ingress_rule.db_from_app.to_port == 3306
    )
    error_message = "The DB security group ingress must open only TCP port 3306."
  }

  # DB 3306 ingress references the App security group as its source (not a CIDR).
  assert {
    condition     = aws_vpc_security_group_ingress_rule.db_from_app.referenced_security_group_id == aws_security_group.app.id
    error_message = "The DB 3306 ingress must reference the App security group as its only source."
  }

  # By default there are no additional CIDR sources on the DB SG, so the App SG
  # is the ONLY permitted source of 3306 traffic (Requirement 8.4).
  assert {
    condition     = length(aws_vpc_security_group_ingress_rule.db_from_cidr) == 0
    error_message = "By default the DB security group must accept 3306 only from the App SG, with no additional CIDR sources."
  }
}

# ---------------------------------------------------------------------------
# Property 6 again with open_http = false: still no port 22, and now no port 80
# either. The only App ingress port must be 443.
# ---------------------------------------------------------------------------
run "ssh_closed_without_http" {
  command = apply

  variables {
    open_http = false
  }

  # With HTTP disabled, no HTTP (80) ingress rule is created.
  assert {
    condition     = length(aws_vpc_security_group_ingress_rule.app_http) == 0
    error_message = "When open_http = false, no HTTP (port 80) ingress rule may be created."
  }

  # The only App ingress port is 443 (never 22).
  assert {
    condition = alltrue([
      for r in values(aws_vpc_security_group_ingress_rule.app_https) :
      r.from_port == 443 && r.to_port == 443
    ]) && !contains([for r in values(aws_vpc_security_group_ingress_rule.app_https) : r.from_port], 22)
    error_message = "With open_http = false the App security group must open only port 443 and never SSH/22."
  }
}

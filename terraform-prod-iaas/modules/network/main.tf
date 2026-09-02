# terraform-prod-iaas/modules/network/main.tf
#
# Network placement + firewall for Prod IaaS. Reuses the account's default VPC
# (or an explicit VPC/subnet) and creates two dedicated security groups:
#
#   - app: 443 inbound (and optional 80), NO SSH (22). Operator access is via
#     SSM Session Manager, enabled by egress 443 to the AWS APIs.
#   - db:  3306 inbound from the App security group ONLY. All other inbound is
#     denied by the security group's default-deny behavior.
#
# Rules are declared as standalone aws_vpc_security_group_(in|e)gress_rule
# resources (AWS provider 5.x) so the app<->db cross-references do not create a
# dependency cycle between the two aws_security_group resources.

terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

# ---- VPC / subnet resolution (default when inputs are empty) ----

data "aws_vpc" "default" {
  count   = var.vpc_id == "" ? 1 : 0
  default = true
}

locals {
  vpc_id = var.vpc_id != "" ? var.vpc_id : data.aws_vpc.default[0].id
}

data "aws_subnets" "in_vpc" {
  filter {
    name   = "vpc-id"
    values = [local.vpc_id]
  }
}

locals {
  resolved_subnet_ids = data.aws_subnets.in_vpc.ids
  subnet_id           = var.subnet_id != "" ? var.subnet_id : (length(local.resolved_subnet_ids) > 0 ? local.resolved_subnet_ids[0] : "")
}

# ---- App security group ----

resource "aws_security_group" "app" {
  name_prefix = "${var.name_prefix}-app-"
  description = "Prod IaaS App instance: 443/80 inbound, no SSH; SSM-only operator access."
  vpc_id      = local.vpc_id

  tags = {
    Name = "${var.name_prefix}-app"
  }

  lifecycle {
    create_before_destroy = true
  }
}

# Inbound HTTPS (443) from the allowed CIDRs.
resource "aws_vpc_security_group_ingress_rule" "app_https" {
  for_each = toset(var.ingress_cidr_https)

  security_group_id = aws_security_group.app.id
  description       = "HTTPS inbound"
  ip_protocol       = "tcp"
  from_port         = 443
  to_port           = 443
  cidr_ipv4         = each.value
}

# Optional inbound HTTP (80) for the HTTP->HTTPS redirect.
resource "aws_vpc_security_group_ingress_rule" "app_http" {
  for_each = var.open_http ? toset(var.ingress_cidr_https) : toset([])

  security_group_id = aws_security_group.app.id
  description       = "HTTP redirect inbound"
  ip_protocol       = "tcp"
  from_port         = 80
  to_port           = 80
  cidr_ipv4         = each.value
}

# BookStack documentation portal — port 8090
resource "aws_vpc_security_group_ingress_rule" "app_bookstack" {
  security_group_id = aws_security_group.app.id
  description       = "BookStack documentation portal"
  ip_protocol       = "tcp"
  from_port         = 8090
  to_port           = 8090
  cidr_ipv4         = "0.0.0.0/0"
}

# NOTE: There is intentionally NO ingress rule for TCP 22 (SSH). Operator shell
# access is provided exclusively through SSM Session Manager (Requirement 7.1).

# Outbound HTTPS (443) so the instance can reach SSM Session Manager endpoints
# and other AWS APIs (Secrets Manager, S3, ECR). Requirement 7.4.
resource "aws_vpc_security_group_egress_rule" "app_https_egress" {
  security_group_id = aws_security_group.app.id
  description       = "HTTPS egress for SSM Session Manager and AWS APIs"
  ip_protocol       = "tcp"
  from_port         = 443
  to_port           = 443
  cidr_ipv4         = "0.0.0.0/0"
}

# Outbound HTTP (80) so Docker builds inside the instance can reach package
# repositories (apt-get, dnf) that serve over plain HTTP.
resource "aws_vpc_security_group_egress_rule" "app_http_egress" {
  security_group_id = aws_security_group.app.id
  description       = "HTTP egress for package repositories (apt/dnf)"
  ip_protocol       = "tcp"
  from_port         = 80
  to_port           = 80
  cidr_ipv4         = "0.0.0.0/0"
}

# Outbound MySQL (3306) to any destination in the VPC so the App can reach the
# existing production RDS (whose security groups are not managed by this stack).
# This is broader than referencing a specific SG but is required when pointing
# at an external RDS in cutover-rehearsal mode.
resource "aws_vpc_security_group_egress_rule" "app_mysql_egress" {
  security_group_id = aws_security_group.app.id
  description       = "MySQL egress for reaching the production RDS"
  ip_protocol       = "tcp"
  from_port         = 3306
  to_port           = 3306
  cidr_ipv4         = "0.0.0.0/0"
}

# Outbound MySQL (3306) to the isolated RDS security group so the App can reach
# the database. Declared separately to avoid an app<->db dependency cycle.
resource "aws_vpc_security_group_egress_rule" "app_to_db" {
  security_group_id            = aws_security_group.app.id
  description                  = "MySQL egress to the isolated RDS security group"
  ip_protocol                  = "tcp"
  from_port                    = 3306
  to_port                      = 3306
  referenced_security_group_id = aws_security_group.db.id
}

# ---- DB security group ----

resource "aws_security_group" "db" {
  name_prefix = "${var.name_prefix}-db-"
  description = "Prod IaaS isolated RDS: 3306 inbound from the App security group only."
  vpc_id      = local.vpc_id

  tags = {
    Name = "${var.name_prefix}-db"
  }

  lifecycle {
    create_before_destroy = true
  }
}

# Inbound MySQL (3306) from the App security group ONLY (Requirements 8.3, 8.4).
resource "aws_vpc_security_group_ingress_rule" "db_from_app" {
  security_group_id            = aws_security_group.db.id
  description                  = "MySQL inbound from the App instance security group only"
  ip_protocol                  = "tcp"
  from_port                    = 3306
  to_port                      = 3306
  referenced_security_group_id = aws_security_group.app.id
}

# Optional inbound MySQL (3306) from deliberate PRIVATE CIDR sources. The
# variable's validation block guarantees these are RFC1918 and never publicly
# routable, so no rule with a public source is ever created (Requirement 8.5).
resource "aws_vpc_security_group_ingress_rule" "db_from_cidr" {
  for_each = toset(var.db_ingress_cidrs)

  security_group_id = aws_security_group.db.id
  description       = "MySQL inbound from an approved private CIDR"
  ip_protocol       = "tcp"
  from_port         = 3306
  to_port           = 3306
  cidr_ipv4         = each.value
}

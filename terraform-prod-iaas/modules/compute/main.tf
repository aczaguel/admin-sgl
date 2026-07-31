# terraform-prod-iaas/modules/compute/main.tf
#
# Launches the single Prod IaaS App instance:
#   - Resolves the AMI (latest Amazon Linux 2023 x86_64) when ami_id is empty.
#   - Attaches exactly one IAM instance profile (the only credential source).
#   - Uses a gp3 root volume.
#   - Renders user-data from user-data.sh.tftpl (installs Docker + compose,
#     writes a stateless .env with NO AWS keys, starts the app).

terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

# Look up the latest Amazon Linux 2023 x86_64 AMI. Only consulted when the
# caller did not pin an explicit ami_id.
data "aws_ami" "al2023" {
  count       = var.ami_id == "" ? 1 : 0
  most_recent = true
  owners      = ["amazon"]

  filter {
    name   = "name"
    values = ["al2023-ami-2023.*-x86_64"]
  }

  filter {
    name   = "architecture"
    values = ["x86_64"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }

  filter {
    name   = "root-device-type"
    values = ["ebs"]
  }
}

locals {
  effective_ami_id = var.ami_id != "" ? var.ami_id : data.aws_ami.al2023[0].id
}

resource "aws_instance" "app" {
  ami                    = local.effective_ami_id
  instance_type          = var.instance_type
  subnet_id              = var.subnet_id
  vpc_security_group_ids = var.security_group_ids

  # Exactly one instance profile: the sole AWS credential source on the box.
  # No static access key / secret key is provisioned anywhere.
  iam_instance_profile = var.instance_profile_name

  # Enforce IMDSv2 so instance-profile credentials cannot be pulled via the
  # legacy, token-less metadata endpoint. Hop limit = 2 so Docker containers
  # running inside the instance can reach the metadata service for credentials.
  metadata_options {
    http_endpoint               = "enabled"
    http_tokens                 = "required"
    http_put_response_hop_limit = 2
  }

  root_block_device {
    volume_type           = "gp3"
    volume_size           = var.root_volume_size
    encrypted             = true
    delete_on_termination = true
  }

  # Stateless boot: install Docker + compose, render the .env (no AWS keys),
  # start the app. The template file is provided by task 6.2.
  user_data = templatefile("${path.module}/user-data.sh.tftpl", {
    env_file_vars  = var.env_file_vars
    compose_source = var.compose_source
  })

  tags = {
    Name = "${var.name_prefix}-app"
  }
}

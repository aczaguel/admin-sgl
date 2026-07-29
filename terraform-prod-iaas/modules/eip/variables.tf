# terraform-prod-iaas/modules/eip/variables.tf
#
# Inputs for the eip module. This module allocates the SECOND productive
# Elastic IP for the SGL platform and binds it to the Prod IaaS App instance so
# it runs in parallel with Prod estable on its own public address.
#
# ISOLATION: this module never references, imports, or modifies the Prod estable
# Elastic IP. It only allocates a brand-new EIP and associates it with the
# instance identified by var.instance_id (Requirements 9.3, 9.4).

variable "instance_id" {
  description = "Id of the Prod IaaS App instance (from the compute module) to associate the newly allocated Elastic IP with."
  type        = string

  validation {
    condition     = length(trimspace(var.instance_id)) > 0
    error_message = "instance_id must be a non-empty EC2 instance id so the allocated Elastic IP can be associated with the Prod IaaS App instance."
  }
}

variable "name_prefix" {
  description = "Naming/tag prefix for the Elastic IP resources."
  type        = string
  default     = "sgl-prod-iaas"
}

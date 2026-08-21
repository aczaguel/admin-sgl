# terraform-prod-iaas/modules/scheduler/variables.tf

variable "instance_id" {
  description = "EC2 instance ID to start/stop on schedule."
  type        = string
}

variable "name_prefix" {
  description = "Naming prefix for all scheduler resources."
  type        = string
  default     = "sgl-prod-iaas"
}

variable "start_cron" {
  description = "EventBridge cron expression for starting the instance (UTC)."
  type        = string
  default     = "cron(0 14 * * ? *)" # 08:00 CDMX (UTC-6)
}

variable "stop_cron" {
  description = "EventBridge cron expression for stopping the instance (UTC)."
  type        = string
  default     = "cron(0 2 * * ? *)" # 20:00 CDMX (UTC-6)
}

variable "enabled" {
  description = "Whether the scheduler rules are enabled."
  type        = bool
  default     = true
}

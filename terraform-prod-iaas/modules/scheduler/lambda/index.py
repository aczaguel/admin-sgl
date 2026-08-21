"""
EC2 Scheduler Lambda — start or stop an EC2 instance.

The action ('start' or 'stop') is passed in the EventBridge event detail
or as the top-level 'action' key so both EventBridge and manual invocations
work identically.

Environment variables:
  INSTANCE_ID  — the EC2 instance ID to manage
  AWS_REGION   — set automatically by the Lambda runtime
"""

import os
import boto3
import logging

logger = logging.getLogger()
logger.setLevel(logging.INFO)


def handler(event, context):
    instance_id = os.environ["INSTANCE_ID"]
    region = os.environ.get("AWS_REGION", "us-east-1")

    # Accept action from top-level key or from EventBridge detail
    action = event.get("action") or event.get("detail", {}).get("action", "")
    action = action.lower().strip()

    if action not in ("start", "stop"):
        logger.error("Unknown action: %r — event: %s", action, event)
        raise ValueError(f"action must be 'start' or 'stop', got: {action!r}")

    ec2 = boto3.client("ec2", region_name=region)

    if action == "start":
        logger.info("Starting instance %s", instance_id)
        response = ec2.start_instances(InstanceIds=[instance_id])
        state = response["StartingInstances"][0]["CurrentState"]["Name"]
        logger.info("Instance %s state: %s", instance_id, state)
    else:
        logger.info("Stopping instance %s", instance_id)
        response = ec2.stop_instances(InstanceIds=[instance_id])
        state = response["StoppingInstances"][0]["CurrentState"]["Name"]
        logger.info("Instance %s state: %s", instance_id, state)

    return {"instance_id": instance_id, "action": action, "state": state}

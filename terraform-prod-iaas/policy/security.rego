# terraform-prod-iaas/policy/security.rego
#
# Conftest (OPA/Rego) policies enforcing the Prod IaaS security posture over a
# Terraform plan rendered as JSON:
#
#   terraform plan -out=tfplan
#   terraform show -json tfplan > plan.json
#   conftest test plan.json -p policy/
#
# The rules operate on `input.resource_changes[]` from the plan JSON. Only
# resources with `mode == "managed"` are provisioned by this stack; data sources
# (`mode == "data"`) are read-only lookups and are intentionally exempt from the
# "no prod resource" rule (the production RDS is referenced ONLY via a read-only
# `data "aws_db_snapshot"` — see design.md "Correctness Properties" #1).
#
# Requirements enforced:
#   - 7.1  No security group ingress rule for TCP port 22 (SSH is closed).
#   - 8.1  No RDS instance with publicly_accessible = true.
#   - 6.6  No IAM policy statement with Resource "*" or wildcard actions.
#   - 9.1  Exactly one new aws_eip resource (the single second Elastic IP).
#   - 15.2 / 15.3  No managed resource referencing the Prod estable
#          RDS/EC2/EIP identifiers; the production RDS only via a data source.
#
# Configurable input (optional): the set of Prod estable identifiers that must
# never appear on a managed resource. Supply it at test time with, e.g.:
#
#   conftest test plan.json -p policy/ \
#     --data prod-identifiers.yaml
#
# where prod-identifiers.yaml contains:
#
#   params:
#     prod_identifiers:
#       - sgl-prod-estable-db          # production RDS identifier
#       - i-0abc123prodestableec2      # production EC2 instance id
#       - eipalloc-0abc123prodestable  # production EIP allocation id
#       - "52.1.2.3"                   # production EIP public address
#
# When no prod identifiers are supplied the rule is a no-op (nothing to match),
# so the policy set still runs cleanly in a bare CI job.

package main

import rego.v1

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

# All resource changes in the plan (empty when the key is absent).
resource_changes := object.get(input, "resource_changes", [])

# True when a resource change creates a resource.
is_create(rc) if "create" in rc.change.actions

# True when a resource change removes a resource.
is_delete(rc) if "delete" in rc.change.actions

# The post-plan attribute object for a resource change.
after(rc) := object.get(rc.change, "after", {})

# Managed resource changes of a given type that are not being destroyed.
managed_of_type(t) := [rc |
	some rc in resource_changes
	rc.mode == "managed"
	rc.type == t
	not is_delete(rc)
]

# Configurable Prod estable identifiers (default: none).
prod_identifiers := object.get(object.get(data, "params", {}), "prod_identifiers", [])

# ---------------------------------------------------------------------------
# Requirement 7.1 — SSH (TCP 22) ingress must never be opened.
# ---------------------------------------------------------------------------

# Standalone ingress rule resources (AWS provider 5.x style).
deny contains msg if {
	some rc in resource_changes
	rc.mode == "managed"
	rc.type == "aws_vpc_security_group_ingress_rule"
	not is_delete(rc)
	a := after(rc)
	_protocol_covers_tcp(a.ip_protocol)
	_port_range_covers(a.from_port, a.to_port, 22)
	msg := sprintf("Requirement 7.1: security group ingress rule '%s' opens TCP port 22 (SSH); operator access must use SSM Session Manager only.", [rc.address])
}

# Inline ingress blocks on an aws_security_group resource.
deny contains msg if {
	some rc in resource_changes
	rc.mode == "managed"
	rc.type == "aws_security_group"
	not is_delete(rc)
	some ingress in object.get(after(rc), "ingress", [])
	_protocol_covers_tcp(ingress.protocol)
	_port_range_covers(ingress.from_port, ingress.to_port, 22)
	msg := sprintf("Requirement 7.1: security group '%s' has an inline ingress block opening TCP port 22 (SSH); SSH must remain closed.", [rc.address])
}

# A protocol string covers TCP when it is "tcp" or the all-protocols wildcard.
_protocol_covers_tcp(p) if lower(p) == "tcp"

_protocol_covers_tcp(p) if p == "-1"

_protocol_covers_tcp(p) if p == "all"

# A [from,to] port range covers `port` (a null/absent bound is treated as open).
_port_range_covers(from_port, to_port, port) if {
	f := _as_port(from_port, 0)
	t := _as_port(to_port, 65535)
	f <= port
	port <= t
}

_as_port(v, _) := v if is_number(v)

_as_port(v, fallback) := fallback if not is_number(v)

# ---------------------------------------------------------------------------
# Requirement 8.1 — RDS instances must not be publicly accessible.
# ---------------------------------------------------------------------------

deny contains msg if {
	some rc in managed_of_type("aws_db_instance")
	after(rc).publicly_accessible == true
	msg := sprintf("Requirement 8.1: RDS instance '%s' sets publicly_accessible = true; the isolated database must not be reachable from the public internet.", [rc.address])
}

# ---------------------------------------------------------------------------
# Requirement 6.6 — No IAM policy statement with Resource "*" or wildcard
# actions (least privilege).
# ---------------------------------------------------------------------------

# Inline role policies and standalone managed policies both carry a JSON policy
# document string in `after.policy`.
_policy_docs contains {"address": rc.address, "doc": doc} if {
	some rc in resource_changes
	rc.mode == "managed"
	rc.type in {"aws_iam_role_policy", "aws_iam_policy", "aws_iam_user_policy", "aws_iam_group_policy"}
	not is_delete(rc)
	policy_json := after(rc).policy
	is_string(policy_json)
	doc := json.unmarshal(policy_json)
}

# Wildcard Resource ("*") anywhere in a statement.
deny contains msg if {
	some entry in _policy_docs
	stmt := _statements(entry.doc)[_]
	resources := _values(object.get(stmt, "Resource", []))
	resources[_] == "*"
	msg := sprintf("Requirement 6.6: IAM policy on '%s' contains a statement with Resource \"*\"; scope it to the effective bucket and secret ARNs only.", [entry.address])
}

# Wildcard Action ("*" or "service:*") in an Allow statement.
deny contains msg if {
	some entry in _policy_docs
	stmt := _statements(entry.doc)[_]
	object.get(stmt, "Effect", "Allow") == "Allow"
	act := _values(object.get(stmt, "Action", []))[_]
	_action_is_wildcard(act)
	msg := sprintf("Requirement 6.6: IAM policy on '%s' grants a wildcard action '%s'; enumerate only the required S3 and Secrets Manager actions.", [entry.address, act])
}

# Normalize the statement list (may be a single object or an array).
_statements(doc) := s if {
	is_array(doc.Statement)
	s := doc.Statement
}

_statements(doc) := [doc.Statement] if not is_array(doc.Statement)

# Normalize a Resource/Action field into a list of strings.
_values(v) := v if is_array(v)

_values(v) := [v] if is_string(v)

_action_is_wildcard(a) if a == "*"

_action_is_wildcard(a) if endswith(a, ":*")

# ---------------------------------------------------------------------------
# Requirement 9.1 — Exactly one (new) Elastic IP.
# ---------------------------------------------------------------------------

deny contains msg if {
	eips := managed_of_type("aws_eip")
	count(eips) != 1
	msg := sprintf("Requirement 9.1: expected exactly one aws_eip resource, found %d; the stack allocates a single second Elastic IP.", [count(eips)])
}

# ---------------------------------------------------------------------------
# Requirements 15.2 / 15.3 — No managed resource may reference a Prod estable
# identifier. The production RDS may be referenced only through a data source.
# ---------------------------------------------------------------------------

# Attributes whose values act as resource identities we scan for prod ids.
_identity_keys := {"identifier", "id", "instance_id", "allocation_id", "public_ip", "db_instance_identifier"}

deny contains msg if {
	count(prod_identifiers) > 0
	some rc in resource_changes
	rc.mode == "managed"
	not is_delete(rc)
	some key in _identity_keys
	val := object.get(after(rc), key, null)
	is_string(val)
	some prod_id in prod_identifiers
	val == prod_id
	msg := sprintf("Requirement 15.2/15.3: managed resource '%s' references Prod estable identifier '%s' via attribute '%s'; Prod estable resources must not be managed by this stack.", [rc.address, prod_id, key])
}

# The production RDS must be a data source, never a managed aws_db_instance.
deny contains msg if {
	count(prod_identifiers) > 0
	some rc in managed_of_type("aws_db_instance")
	src := object.get(after(rc), "identifier", "")
	some prod_id in prod_identifiers
	src == prod_id
	msg := sprintf("Requirement 15.3: the production RDS '%s' is declared as a managed aws_db_instance ('%s'); it must be referenced only through a read-only data source.", [prod_id, rc.address])
}

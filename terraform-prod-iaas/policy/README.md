# Prod IaaS plan policies (conftest / OPA)

Behavioral security policies for the `terraform-prod-iaas` stack, encoded as
[conftest](https://www.conftest.dev/) (OPA/Rego) rules that run against a
Terraform **plan rendered as JSON**. These enforce the design's Correctness
Properties in CI without applying anything.

## What is enforced

`policy/security.rego` (package `main`) emits `deny` messages for:

| Rule | Requirement | Meaning |
|------|-------------|---------|
| No SSH ingress | 7.1 | No security group ingress rule opens TCP port 22 |
| No public RDS | 8.1 | No `aws_db_instance` with `publicly_accessible = true` |
| Least-privilege IAM | 6.6 | No IAM policy statement with `Resource "*"` or a wildcard action (`*` / `service:*`) |
| Single Elastic IP | 9.1 | Exactly one managed `aws_eip` resource in the plan |
| No prod resources | 15.2 / 15.3 | No managed resource references a Prod estable identifier; the production RDS appears only as a data source |

Only `mode == "managed"` resources are provisioned by this stack. Read-only
`data` sources (e.g. `data.aws_db_snapshot` used to seed the isolated RDS) are
intentionally exempt from the "no prod resources" rule.

## How to run

```bash
cd terraform-prod-iaas

# 1. Produce a plan and render it as JSON.
terraform plan -out=tfplan
terraform show -json tfplan > plan.json

# 2. Evaluate the policies against the plan JSON.
conftest test plan.json -p policy/
```

A clean plan produces no failures. Any violation is reported as a `FAIL` with
the requirement number and offending resource address.

### Optionally supply Prod estable identifiers

Rules 15.2 / 15.3 match managed resources against a configurable set of known
Prod estable identifiers. Without this data the rules are a no-op (there is
nothing to match), so the suite still runs cleanly in a bare CI job. To enable
the check, pass a data file:

```bash
conftest test plan.json -p policy/ --data prod-identifiers.yaml
```

```yaml
# prod-identifiers.yaml
params:
  prod_identifiers:
    - sgl-prod-estable-db          # production RDS identifier
    - i-0abc123prodestableec2      # production EC2 instance id
    - eipalloc-0abc123prodestable  # production EIP allocation id
    - "52.1.2.3"                   # production EIP public address
```

## CI dependency note

`conftest` (and its embedded OPA engine) is a **CI dependency** and is **not**
installed by this repository. Install it in the CI image (or locally) before
running the commands above — see the
[conftest install docs](https://www.conftest.dev/install/). To sanity-check that
the policies parse, run `conftest verify -p policy/` or `opa check policy/`.

# Requirements Document

## Introduction

This feature provisions a second, parallel production server ("Prod IaaS") for the SGL application
entirely through Terraform, running side by side with the existing production server ("Prod estable")
in a blue/green style deployment. Each server keeps its own public Elastic IP so two productive IPs
coexist. Because the application is already stateless (file storage on S3, database credentials
resolved at runtime from AWS Secrets Manager, both consumed through the EC2 IAM Instance Profile),
the Prod IaaS stack only needs to provision compute, an identity, a target bucket, and a database
reference.

The overriding safety goal is isolation of Terraform state and blast radius: applying, tainting, or
destroying the Prod IaaS stack must be provably incapable of modifying or destroying any Prod estable
resource, especially the live production RDS. This is achieved through a separate Terraform state, an
isolated RDS seeded from a read-only production snapshot, and destroy guards on stateful resources.

The requirements below are derived from the approved design document (`design.md`) and capture its
Goals/Non-Goals, Correctness Properties, Error Handling, and Security Considerations.

## Glossary

- **Prod_IaaS_Stack**: The new, self-contained Terraform root module (`terraform-prod-iaas/`) that provisions all Prod IaaS resources and owns its own state.
- **Prod_Estable**: The pre-existing production server and its associated AWS resources (EC2, EIP, RDS, security groups), which are managed outside this Terraform state.
- **Terraform**: The infrastructure-as-code tool executing plan, apply, and destroy operations against the Prod_IaaS_Stack state.
- **Isolated_RDS**: A new RDS instance created by the Prod_IaaS_Stack, seeded from a snapshot of the production database, whose writes never reach production data.
- **Production_RDS**: The live Prod_Estable database, referenced by the Prod_IaaS_Stack only as a read-only snapshot source.
- **Network_Module**: The `network` module that creates the security group and resolves VPC/subnet placement.
- **IAM_Module**: The `iam` module that creates the EC2 role and instance profile with least-privilege policies.
- **Secrets_Module**: The `secrets` module that stores the Isolated_RDS credentials in AWS Secrets Manager.
- **RDS_Module**: The `rds` module that creates the Isolated_RDS from a snapshot, with backups and destroy guards.
- **Compute_Module**: The `compute` module that launches the EC2 instance and runs the user-data script.
- **EIP_Module**: The `eip` module that allocates and associates the second Elastic IP.
- **S3_Bucket_Module**: The pre-existing `terraform/modules/s3_bucket` module, reused as-is.
- **Instance_Profile**: The IAM instance profile attached to the EC2 instance, providing AWS credentials without static access keys.
- **App_Instance**: The EC2 instance running the SGL application via Docker and docker compose.
- **State_Backend**: The remote Terraform backend (S3 bucket plus DynamoDB lock table) that stores the Prod_IaaS_Stack state under a distinct state key.
- **Operator**: The engineer running Terraform commands and administering the Prod IaaS server.

## Requirements

### Requirement 1: State and blast-radius isolation

**User Story:** As an operator, I want the Prod IaaS stack to be fully isolated from Prod estable in Terraform state, so that applying or destroying it can never modify or delete production resources.

#### Acceptance Criteria

1. THE Prod_IaaS_Stack SHALL declare only Prod IaaS resources in its configuration.
2. THE Prod_IaaS_Stack SHALL NOT declare, import, or manage any Prod_Estable resource in its Terraform state.
3. THE Prod_IaaS_Stack SHALL persist its Terraform state in a backend location (distinct state file or state key) that is separate from the Prod_Estable state and shares zero state entries with it.
4. THE Prod_IaaS_Stack SHALL reference the Production_RDS exclusively through a read-only data source and SHALL NOT declare the Production_RDS as a managed resource.
5. WHEN an operator runs a plan of the Prod_IaaS_Stack, THE Terraform SHALL report zero add, change, and destroy actions against any Prod_Estable resource.
6. WHEN an operator runs a destroy of the Prod_IaaS_Stack, THE Terraform SHALL affect zero Prod_Estable resources, because no Prod_Estable resource exists in the Prod_IaaS_Stack state.

### Requirement 2: Isolated RDS seeded from a production snapshot

**User Story:** As an operator, I want an isolated database seeded from a production snapshot, so that the parallel server has realistic data while production data stays untouched.

#### Acceptance Criteria

1. WHEN the Prod_IaaS_Stack is applied in isolated database mode and no explicit snapshot identifier is provided, THE RDS_Module SHALL create the Isolated_RDS from the most recent available snapshot of the Production_RDS.
2. THE Isolated_RDS SHALL use an instance identifier that differs from the Production_RDS identifier.
3. WHILE the Prod_IaaS_Stack operates in isolated database mode, THE App_Instance SHALL resolve database credentials whose host is the Isolated_RDS endpoint rather than the Production_RDS endpoint.
4. WHEN the operator provides an explicit snapshot identifier, THE RDS_Module SHALL seed the Isolated_RDS from that snapshot instead of the latest-snapshot lookup.
5. IF the resolved snapshot source cannot be found at apply time because the latest-snapshot lookup returns no snapshot or the explicit snapshot identifier does not exist, THEN THE RDS_Module SHALL fail the apply with an error identifying the unavailable snapshot source and SHALL NOT create the Isolated_RDS.

### Requirement 3: RDS backups and destroy guards

**User Story:** As an operator, I want backups and destroy protection on the isolated database, so that data is recoverable and cannot be deleted accidentally.

#### Acceptance Criteria

1. THE RDS_Module SHALL configure the Isolated_RDS with an automated backup retention period between 7 and 35 days so that point-in-time recovery is enabled.
2. THE RDS_Module SHALL enable deletion protection on the Isolated_RDS.
3. THE RDS_Module SHALL configure a prevent_destroy lifecycle guard on the Isolated_RDS.
4. IF a destroy of the Isolated_RDS is executed, THEN THE RDS_Module SHALL create a final snapshot with a unique snapshot identifier before the instance is removed.
5. IF the final snapshot cannot be created during a destroy of the Isolated_RDS, THEN THE RDS_Module SHALL halt the destroy operation, retain the Isolated_RDS instance, and surface an error indicating the snapshot failure.
6. WHEN the Prod_IaaS_Stack is re-applied with unchanged inputs, THE RDS_Module SHALL ignore changes to the snapshot identifier so the Isolated_RDS is not re-restored.

### Requirement 4: Module structure and reuse

**User Story:** As an operator, I want the stack organized into focused modules, so that the infrastructure is maintainable and the existing bucket module is reused rather than duplicated.

#### Acceptance Criteria

1. THE Prod_IaaS_Stack SHALL be composed of exactly six discrete Terraform modules, each defined in its own module directory rather than as inline resource blocks in the root module: the Network_Module, IAM_Module, Secrets_Module, RDS_Module, Compute_Module, and EIP_Module.
2. THE Prod_IaaS_Stack SHALL consume the existing S3_Bucket_Module through a Terraform module source reference pointing at the existing module location.
3. THE Prod_IaaS_Stack SHALL leave the S3_Bucket_Module source files (its variables, resources, and outputs) byte-for-byte unchanged from the existing stack.
4. THE Prod_IaaS_Stack SHALL be defined as a separate Terraform root module whose backend configuration and state file reference a backend key/path distinct from the existing Terraform stack, such that an apply operation on either stack does not read or write the other stack's state file.
5. IF the referenced S3_Bucket_Module source cannot be resolved during initialization, THEN THE Prod_IaaS_Stack SHALL fail initialization with an error indicating the unresolved module reference and SHALL NOT create or modify any resources.

### Requirement 5: Stateless EC2 compute and configuration

**User Story:** As an operator, I want a Docker-based EC2 instance configured from a stateless .env, so that the application runs without local state and reads all configuration at boot.

#### Acceptance Criteria

1. THE Compute_Module SHALL launch an EC2 instance whose default instance type is t3.medium.
2. WHEN the App_Instance boots, THE Compute_Module SHALL run a user-data script that installs Docker and docker compose.
3. WHEN the user-data script completes installing Docker and docker compose, THE Compute_Module SHALL start the application.
4. IF the user-data script fails to install Docker or docker compose, or fails to start the application, THEN THE Compute_Module SHALL leave the application in a not-started state and expose a failure indication observable to the operator (the instance shall not report the application as running).
5. WHILE the application is starting, THE Compute_Module SHALL treat the application as ready only after it responds successfully to a health check within 300 seconds of the start command; if it does not become ready within 300 seconds, THE Compute_Module SHALL mark the startup as failed.
6. THE Compute_Module SHALL write an application `.env` containing FILE_STORAGE_DRIVER set to s3, the target S3 bucket name, the region, SECRETS_PROVIDER set to aws, and the RDS secret reference.
7. IF any of the required values (target S3 bucket name, region, or RDS secret reference) is empty or not provided, THEN THE Compute_Module SHALL not start the application and SHALL expose a configuration-error indication observable to the operator.
8. THE Compute_Module SHALL write the `.env` without any AWS access key or secret key values.

### Requirement 6: Instance-profile authentication and least-privilege IAM

**User Story:** As a security owner, I want the instance to authenticate via an IAM instance profile with least-privilege permissions, so that no static credentials exist and access is tightly scoped.

#### Acceptance Criteria

1. THE Compute_Module SHALL attach exactly one Instance_Profile to the App_Instance and SHALL provision no other AWS credential source (no static access key, no secret key) on the App_Instance.
2. THE Prod_IaaS_Stack SHALL NOT write any AWS access key or secret key into user-data, the `.env` file, Terraform outputs, or any value retrievable from Terraform state.
3. THE IAM_Module SHALL grant S3 object-level actions (GetObject, PutObject, DeleteObject) scoped exactly to the object ARN of the one effective bucket (the bucket ARN suffixed with `/*`) and no other resource.
4. THE IAM_Module SHALL grant the S3 ListBucket action scoped exactly to the one effective bucket ARN and no other resource.
5. THE IAM_Module SHALL grant the secretsmanager GetSecretValue action scoped exactly to the one effective RDS secret ARN and no other resource.
6. THE IAM_Module SHALL NOT include any policy statement whose Resource is a wildcard (`*`) or whose Action set exceeds the actions enumerated in criteria 3 through 5.

### Requirement 7: SSH closed with SSM operator access

**User Story:** As a security owner, I want SSH disabled and operator access provided through SSM, so that the instance has no open shell port exposed to the network.

#### Acceptance Criteria

1. THE Network_Module SHALL create a security group that has no ingress rule allowing TCP port 22 from any source (including 0.0.0.0/0 and ::/0).
2. THE Network_Module SHALL create a security group that allows inbound TCP port 443 from source 0.0.0.0/0.
3. WHERE HTTP redirect is enabled, THE Network_Module SHALL allow inbound TCP port 80 from source 0.0.0.0/0.
4. THE Network_Module SHALL allow outbound TCP port 443 from the App_Instance to enable SSM Session Manager connectivity.
5. THE IAM_Module SHALL attach the SSM managed policy so the Operator can access the App_Instance via SSM Session Manager.
6. IF a security group configuration includes an ingress rule for TCP port 22, THEN THE Network_Module SHALL fail validation and reject the configuration with an error indicating that SSH ingress is not permitted.

### Requirement 8: RDS network protection

**User Story:** As a security owner, I want the isolated database unreachable from the public internet, so that it is only accessible from the application instance.

#### Acceptance Criteria

1. THE RDS_Module SHALL configure the Isolated_RDS with its publicly accessible attribute set to false.
2. THE RDS_Module SHALL place the Isolated_RDS in subnets that have no route to an internet gateway.
3. THE Network_Module SHALL allow inbound TCP traffic on port 3306 to the Isolated_RDS from the App_Instance security group as the only permitted source.
4. THE Network_Module SHALL deny all inbound traffic to the Isolated_RDS other than TCP port 3306 originating from the App_Instance security group.
5. IF an inbound rule targeting the Isolated_RDS specifies a source CIDR that includes any publicly routable address (for example 0.0.0.0/0), THEN THE Network_Module SHALL not create that rule and SHALL surface a validation error indicating a disallowed public source.

### Requirement 9: Second Elastic IP

**User Story:** As an operator, I want a second public Elastic IP for Prod IaaS, so that it runs in parallel with Prod estable on its own address.

#### Acceptance Criteria

1. WHEN the Prod_IaaS_Stack is applied, THE EIP_Module SHALL allocate exactly one new Elastic IP.
2. WHEN a new Elastic IP has been allocated, THE EIP_Module SHALL associate that Elastic IP with the App_Instance such that exactly one Elastic IP is bound to the App_Instance.
3. THE EIP_Module SHALL allocate an Elastic IP whose public address value is not equal to the Prod_Estable Elastic IP address.
4. THE Prod_IaaS_Stack SHALL NOT reference or modify the Prod_Estable Elastic IP.
5. IF allocation of the new Elastic IP fails, THEN THE EIP_Module SHALL halt the apply operation, SHALL NOT associate any Elastic IP with the App_Instance, and SHALL return an error indicating that allocation failed.
6. IF association of the allocated Elastic IP with the App_Instance fails, THEN THE EIP_Module SHALL return an error indicating that association failed and SHALL leave the App_Instance without a newly associated Elastic IP.

### Requirement 10: Configurable targets with validation

**User Story:** As an operator, I want configurable toggles for the bucket and database targets, so that I can run in isolated mode by default or rehearse a cutover against real production resources.

#### Acceptance Criteria

1. WHERE the isolated bucket toggle is enabled, THE Prod_IaaS_Stack SHALL create exactly one dedicated isolated bucket for Prod IaaS.
2. WHERE the isolated bucket toggle is disabled, THE Prod_IaaS_Stack SHALL reuse the single existing bucket identified by the configured existing bucket name and SHALL NOT create a new bucket.
3. WHERE the real production database toggle is disabled, THE Prod_IaaS_Stack SHALL create exactly one Isolated_RDS instance and exactly one associated secret.
4. WHERE the real production database toggle is enabled, THE Prod_IaaS_Stack SHALL set the Isolated_RDS module count to zero and the secret module count to zero.
5. WHERE the real production database toggle is enabled, THE Prod_IaaS_Stack SHALL point the App_Instance at the configured real production database reference.
6. IF the real production database toggle is enabled and the real production RDS reference is empty, THEN THE Prod_IaaS_Stack SHALL halt the plan before creating any resources and return a validation error message identifying the empty real production RDS reference.
7. IF the isolated bucket toggle is disabled and the existing bucket name is empty, THEN THE Prod_IaaS_Stack SHALL halt the plan before creating any resources and return a validation error message identifying the empty existing bucket name.
8. IF the isolated RDS identifier is equal to the Production_RDS source identifier, THEN THE Prod_IaaS_Stack SHALL halt the plan before creating any resources and return a validation error message identifying the conflicting identifiers.
9. IF the real production database toggle is disabled and both the explicit snapshot identifier and the production source identifier are empty, THEN THE Prod_IaaS_Stack SHALL halt the plan before creating any resources and return a validation error message indicating that no snapshot source is available.

### Requirement 11: Separate remote state backend

**User Story:** As an operator, I want the Prod IaaS stack to use a distinct remote state key, so that it can never clobber the existing stack's Terraform state.

#### Acceptance Criteria

1. THE Prod_IaaS_Stack SHALL configure a remote State_Backend that stores its Terraform state in an S3 bucket and manages state locking through a DynamoDB lock table.
2. THE Prod_IaaS_Stack SHALL use a state key whose string value is not equal to the existing stack's state key value, such that the two stacks never read from or write to the same state object.
3. WHEN initialization of the Prod_IaaS_Stack runs, THE Prod_IaaS_Stack SHALL verify that both the configured S3 bucket and DynamoDB lock table already exist before writing any state.
4. IF the State_Backend S3 bucket or DynamoDB lock table does not exist when initialization runs, THEN THE Terraform SHALL abort backend configuration, produce an error indicating the missing backend resource, and leave the existing stack's state unmodified until the backend is bootstrapped.
5. WHILE a state read or write operation is in progress, THE Prod_IaaS_Stack SHALL hold a lock in the DynamoDB lock table so that concurrent operations cannot modify the same state simultaneously.
6. IF the DynamoDB state lock cannot be acquired because it is already held, THEN THE Terraform SHALL fail the operation and produce an error indicating that the state is locked, without modifying the state.

### Requirement 12: Idempotent re-apply

**User Story:** As an operator, I want a second apply with unchanged inputs to be a no-op, so that re-running Terraform is safe and never re-restores the database.

#### Acceptance Criteria

1. WHEN the Prod_IaaS_Stack is applied a second time with all input variables and state unchanged from the first successful apply, THE Terraform SHALL report exactly zero resources to add, zero to change, and zero to destroy in the plan output.
2. WHEN the Prod_IaaS_Stack is re-applied with unchanged inputs, THE RDS_Module SHALL NOT re-restore the Isolated_RDS from the snapshot, and the Isolated_RDS resource identifier SHALL remain unchanged from the prior apply.
3. WHILE the Isolated_RDS already exists in Terraform state, WHEN a subsequent plan is generated, THE RDS_Module SHALL exclude the snapshot restore source from the computed change set so that snapshot metadata differences do not produce a resource replacement.
4. IF one or more input variables have changed between applies, THEN THE Terraform SHALL report a non-empty change set limited to only the resources affected by those specific changed inputs, and SHALL NOT report changes to unaffected resources.

### Requirement 13: Secret resolution failure handling

**User Story:** As an operator, I want the application to fail closed when it cannot resolve database credentials, so that no partial or insecure database connection is established.

#### Acceptance Criteria

1. IF the App_Instance cannot resolve the RDS secret due to a missing GetSecretValue permission, an incorrect region, or a nonexistent secret reference, THEN THE App_Instance SHALL abort initialization without establishing any database connection.
2. IF the App_Instance cannot resolve the RDS secret, THEN THE App_Instance SHALL refuse to accept incoming application requests until the RDS secret is successfully resolved.
3. WHEN secret resolution fails, THE App_Instance SHALL record an error that names the secret reference and the failure reason without including the secret value.
4. WHILE resolving the RDS secret, IF a single resolution attempt does not complete within 10 seconds, THEN THE App_Instance SHALL abort that attempt and treat it as failed.
5. WHEN a secret resolution attempt fails, THE App_Instance SHALL retry up to 3 total attempts before failing closed.

### Requirement 14: Encryption and secret hygiene

**User Story:** As a security owner, I want data encrypted and the database password kept only in managed locations, so that sensitive material is never exposed in plaintext configuration.

#### Acceptance Criteria

1. THE S3_Bucket_Module SHALL enable server-side encryption on the isolated bucket such that every object stored in the bucket is encrypted at rest, and SHALL apply this encryption by default to all newly written objects without requiring per-request encryption headers.
2. IF the S3_Bucket_Module cannot enable server-side encryption on the isolated bucket during provisioning, THEN THE S3_Bucket_Module SHALL halt provisioning of the bucket and surface an error indicating that encryption could not be enabled, leaving no unencrypted isolated bucket in the deployed state.
3. WHERE the source snapshot is encrypted, THE RDS_Module SHALL produce an encrypted Isolated_RDS restore.
4. IF the source snapshot is not encrypted, THEN THE RDS_Module SHALL halt the Isolated_RDS restore and surface an error indicating that the source snapshot is unencrypted, and SHALL NOT create an unencrypted Isolated_RDS instance.
5. THE Prod_IaaS_Stack SHALL store the Isolated_RDS master password only in Secrets Manager and encrypted Terraform state.
6. THE Prod_IaaS_Stack SHALL NOT write the Isolated_RDS master password in plaintext into the `.env` file or into any Terraform output value.

### Requirement 15: Scope boundaries

**User Story:** As an operator, I want the stack limited to standing up the parallel server, so that user-facing cutover and decommission remain deliberate, separate operations.

#### Acceptance Criteria

1. WHEN the Prod_IaaS_Stack is planned or applied, THE Prod_IaaS_Stack SHALL NOT create, modify, or delete any DNS record or Elastic IP association that directs end-user traffic to the Prod IaaS server.
2. WHEN the Prod_IaaS_Stack is planned or applied, THE Prod_IaaS_Stack SHALL NOT create, modify, terminate, stop, or delete the Prod_Estable server or any resource attached to it.
3. THE Prod_IaaS_Stack SHALL NOT provision autoscaling groups, application load balancers, network load balancers, secret rotation lambdas, or customer-managed KMS keys.
4. IF a plan or apply operation would create, modify, or destroy the Prod_Estable server or its end-user-facing DNS records or Elastic IP associations, THEN THE Prod_IaaS_Stack SHALL fail the operation with an error indicating an out-of-scope resource change and SHALL leave all existing Prod_Estable resources unchanged.

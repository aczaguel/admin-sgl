# Requirements Document

## Introduction

This feature introduces an **AWS Secrets Manager integration** into the CodeIgniter 4 (PHP 8.2) application so that sensitive credentials — starting with the RDS database connection — are resolved at runtime from a secret **name/ARN** stored in `.env`, instead of storing raw secret **values** in `.env`, `app/Config/Database.php`, or any committed artifact. The goal is to eliminate the risk of accidentally committing real credentials to GitHub.

The design mirrors the existing `s3-file-storage` spec pattern: a single `.env` switch (`SECRETS_PROVIDER=aws|env`) toggles between resolving secrets from AWS Secrets Manager and reading plain local `.env` values, exactly as `FILE_STORAGE_DRIVER=local|s3` toggles the storage layer. The AWS SDK (`aws/aws-sdk-php ^3.365`, already a dependency) authenticates via the **IAM Instance Profile** on EC2 — no AWS access keys are ever placed in the repository, preserving the security stance established in `INFRA_AWS_S3_MIGRACION.md` §4.2 and the `s3-file-storage` design (Requirement 10 / Property 10, "No secrets in artifacts").

Sensitive values in Secrets Manager are stored as a JSON blob (e.g. `{"username":"...","password":"...","host":"...","port":3306,"dbname":"..."}`); the resolver parses the JSON and maps fields into CI4's `Config\Database`. Resolved secrets are cached for the request lifecycle to avoid repeated paid `GetSecretValue` calls. Resolved secret values are never logged, echoed, written to disk, or committed.

Existing deployments continue to work unchanged with plain `.env` DB credentials until an operator flips `SECRETS_PROVIDER=aws`.

**In scope:** the secret-resolution abstraction with an `aws|env` provider switch; `.env` holding only secret name/ARN + region + provider flag; RDS credential resolution (JSON parsing → `Config\Database`) wired early in the CI4 bootstrap; per-request caching; fail-closed error handling with secret-free logging; IAM Instance Profile authentication; a working local `env` fallback path; and operator documentation for creating the secret and adding the `secretsmanager:GetSecretValue` IAM permission.

**Out of scope (mentioned as related/future only):** automated secret rotation (Secrets Manager rotation lambdas), KMS key management specifics, and GroceryCrud-specific credential handling.

## Glossary

- **Secrets_Manager_Service**: The application component (CodeIgniter 4 library/service) that resolves sensitive credentials from a configured provider and exposes them to callers. It is the single entry point for secret resolution.
- **Secret_Provider**: The interchangeable backend selected by configuration that supplies raw secret material. Two implementations exist: **Aws_Secret_Provider** (AWS Secrets Manager) and **Env_Secret_Provider** (plain local `.env`).
- **Aws_Secret_Provider**: The provider that fetches secret material from AWS Secrets Manager using `Aws\SecretsManager\SecretsManagerClient`.
- **Env_Secret_Provider**: The provider that reads secret material directly from local `.env`/environment values, reproducing today's behavior.
- **Secret_Reference**: The identifier of a secret in AWS Secrets Manager — its name or ARN — stored in `.env`. Never the secret value itself.
- **Secret_Value**: The sensitive material (e.g. database password) that must remain confidential.
- **Secret_Cache**: The in-memory store that holds a resolved secret for at least the lifetime of a single request to avoid repeated fetches.
- **Database_Config**: CodeIgniter 4's `Config\Database` configuration object whose `default` connection group requires host, port, username, password, and database name.
- **IAM_Instance_Profile**: The EC2 instance role from which the AWS SDK obtains temporary credentials, with no access keys stored in the repository.
- **Operator**: The DevOps person who configures `.env`, creates the AWS secret, and manages the IAM policy.
- **Provider_Flag**: The `.env` variable `SECRETS_PROVIDER` whose value is `aws` or `env` and which selects the active Secret_Provider.

## Requirements

### Requirement 1: Secret resolution abstraction with provider switch

**User Story:** As a developer, I want a single secret-resolution service selected by a configuration flag, so that I can switch between AWS Secrets Manager and local `.env` without changing application code.

#### Acceptance Criteria

1. THE Secrets_Manager_Service SHALL expose a single operation that returns resolved secret material for a requested credential set.
2. WHERE the Provider_Flag equals `aws`, THE Secrets_Manager_Service SHALL use the Aws_Secret_Provider to resolve secret material.
3. WHERE the Provider_Flag equals `env`, THE Secrets_Manager_Service SHALL use the Env_Secret_Provider to resolve secret material.
4. IF the Provider_Flag is absent or empty, THEN THE Secrets_Manager_Service SHALL default to the Env_Secret_Provider.
5. IF the Provider_Flag holds a value other than `aws` or `env`, THEN THE Secrets_Manager_Service SHALL reject the configuration with a descriptive error that names the invalid value and the accepted values.
6. THE Secrets_Manager_Service SHALL return resolved secret material through the same interface regardless of which Secret_Provider is active.

### Requirement 2: Configuration holds only secret references, never values

**User Story:** As an operator, I want `.env` to contain only the secret name/ARN, region, and provider flag, so that raw credentials are never committed to GitHub.

#### Acceptance Criteria

1. WHERE the Provider_Flag equals `aws`, THE Secrets_Manager_Service SHALL read the Secret_Reference and AWS region from configuration and SHALL NOT require any Secret_Value in configuration.
2. THE Aws_Secret_Provider SHALL construct the `SecretsManagerClient` without literal AWS access keys, obtaining credentials from the IAM_Instance_Profile.
3. IF the Provider_Flag equals `aws` and the Secret_Reference is absent or empty, THEN THE Secrets_Manager_Service SHALL reject the configuration with a descriptive error identifying the missing Secret_Reference.
4. IF the Provider_Flag equals `aws` and the AWS region is absent or empty, THEN THE Secrets_Manager_Service SHALL reject the configuration with a descriptive error identifying the missing region.

### Requirement 3: Resolve RDS credentials into the database configuration

**User Story:** As a developer, I want the RDS credentials resolved from Secrets Manager and applied to the database configuration before any connection is opened, so that the application connects using secret-managed credentials.

#### Acceptance Criteria

1. WHERE the Provider_Flag equals `aws`, THE Secrets_Manager_Service SHALL parse the resolved RDS Secret_Value as JSON into the fields host, port, username, password, and database name.
2. WHEN the RDS secret is resolved, THE Secrets_Manager_Service SHALL populate the Database_Config `default` connection group with the resolved host, port, username, password, and database name before the first database connection is opened.
3. IF the resolved RDS Secret_Value is not valid JSON, THEN THE Secrets_Manager_Service SHALL reject the resolution with a descriptive error that excludes the Secret_Value.
4. IF the resolved RDS Secret_Value is missing any of the required fields host, username, password, or database name, THEN THE Secrets_Manager_Service SHALL reject the resolution with an error that names the missing field and excludes the Secret_Value.
5. WHERE the resolved RDS Secret_Value omits the port field, THE Secrets_Manager_Service SHALL apply the default MySQL port 3306.
6. WHILE RDS secret resolution is in progress, THE Secrets_Manager_Service SHALL block any database connection attempt until resolution completes.

### Requirement 4: Runtime caching of resolved secrets

**User Story:** As an operator, I want resolved secrets cached during a request, so that repeated database connections do not trigger repeated paid Secrets Manager calls.

#### Acceptance Criteria

1. WHEN a secret is resolved for a given Secret_Reference during a request, THE Secrets_Manager_Service SHALL store the resolved material in the Secret_Cache for the remainder of that request.
2. WHEN secret material for a Secret_Reference already present in the Secret_Cache is requested again within the same request, THE Secrets_Manager_Service SHALL return the cached material without issuing another Secrets Manager call.
3. WHERE a cache time-to-live is configured, THE Secrets_Manager_Service SHALL treat cached material as valid only until the configured time-to-live elapses.
4. WHERE no cache time-to-live is configured, THE Secrets_Manager_Service SHALL treat cached material as valid for the remainder of the request.

### Requirement 5: Fail-closed error handling with secret-free logging

**User Story:** As an operator, I want a clear failure when a secret cannot be resolved, so that the application never starts with unknown or partial database credentials and no secret material leaks into logs.

#### Acceptance Criteria

1. IF the Aws_Secret_Provider cannot reach AWS Secrets Manager or the requested Secret_Reference does not exist, THEN THE Secrets_Manager_Service SHALL abort database credential resolution with an operator-facing error identifying the Secret_Reference.
2. WHEN the Secrets_Manager_Service records any resolution error, THE Secrets_Manager_Service SHALL write log entries that exclude the Secret_Value.
3. IF RDS credential resolution fails for any reason, THEN THE Secrets_Manager_Service SHALL prevent the application from opening a database connection with incomplete credentials.
4. WHEN the Secrets_Manager_Service reports a resolution error to the caller, THE Secrets_Manager_Service SHALL include the Secret_Reference and the failure reason and SHALL exclude the Secret_Value.

### Requirement 6: IAM Instance Profile authentication, no access keys

**User Story:** As a security owner, I want the SDK to authenticate through the IAM Instance Profile, so that no AWS access keys exist anywhere in the repository or configuration.

#### Acceptance Criteria

1. THE Aws_Secret_Provider SHALL obtain AWS credentials from the IAM_Instance_Profile through the AWS SDK default credential provider chain.
2. THE Secrets_Manager_Service SHALL operate without any AWS access key or secret access key present in `.env`, configuration files, or the repository.
3. THE Operator documentation SHALL state that the IAM policy attached to the IAM_Instance_Profile must grant the `secretsmanager:GetSecretValue` action scoped to the configured Secret_Reference.

### Requirement 7: Local development path with plain `.env`

**User Story:** As a developer without AWS access, I want the application to keep reading plain `.env` credentials when the provider is `env`, so that local development is unaffected.

#### Acceptance Criteria

1. WHERE the Provider_Flag equals `env`, THE Env_Secret_Provider SHALL read RDS credentials from the existing `.env` keys `database.default.hostname`, `database.default.database`, `database.default.username`, and `database.default.password`.
2. WHERE the Provider_Flag equals `env`, THE Secrets_Manager_Service SHALL resolve secrets without contacting AWS Secrets Manager.
3. WHERE the Provider_Flag equals `env`, THE Secrets_Manager_Service SHALL populate the Database_Config `default` connection group with the same values that the application uses today.

### Requirement 8: No secret value is logged, persisted, or committed

**User Story:** As a security owner, I want resolved secret values kept only in memory, so that they never appear in logs, on disk, or in the repository.

#### Acceptance Criteria

1. THE Secrets_Manager_Service SHALL keep resolved Secret_Value material only in the in-memory Secret_Cache during the request.
2. THE Secrets_Manager_Service SHALL exclude the Secret_Value from all log entries.
3. THE Secrets_Manager_Service SHALL exclude the Secret_Value from all files written to disk.
4. THE repository SHALL contain no Secret_Value in `.env`, configuration files, or documentation, holding only the Secret_Reference, region, and Provider_Flag.

### Requirement 9: Operator documentation for secret and IAM setup

**User Story:** As an operator, I want documentation for creating the secret and updating the IAM policy, so that I can enable the AWS provider correctly.

#### Acceptance Criteria

1. THE Operator documentation SHALL describe how to create the RDS secret in AWS Secrets Manager as a JSON blob containing host, port, username, password, and database name.
2. THE Operator documentation SHALL specify the `.env` keys required for the `aws` provider, limited to the Provider_Flag, Secret_Reference, and AWS region.
3. THE Operator documentation SHALL provide the IAM policy statement granting `secretsmanager:GetSecretValue` scoped to the Secret_Reference.
4. THE Operator documentation SHALL state that automated rotation, KMS key management, and GroceryCrud credential handling are out of scope for this feature.

### Requirement 10: Backward compatibility with existing deployments

**User Story:** As an operator, I want existing deployments to keep working with plain `.env` credentials until I opt in, so that enabling Secrets Manager is a controlled, reversible change.

#### Acceptance Criteria

1. WHERE the Provider_Flag is absent, THE Secrets_Manager_Service SHALL behave identically to the application's current plain-`.env` database configuration.
2. WHEN an Operator changes the Provider_Flag from `env` to `aws`, THE Secrets_Manager_Service SHALL switch to AWS resolution without requiring changes to application code.
3. WHEN an Operator changes the Provider_Flag from `aws` back to `env`, THE Secrets_Manager_Service SHALL revert to reading plain `.env` credentials.

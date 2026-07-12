# Implementation Plan: AWS Secrets Manager Integration for RDS Credentials

## Overview

This plan builds the secret-resolution abstraction in `env` mode first, so current behavior is byte-for-byte identical to today, then wires the AWS provider, then the caching facade, and finally the bootstrap hook that populates `Config\Database->default` before the first connection. Every step is incremental and safe to run against the existing system while `SECRETS_PROVIDER` is absent or `env`, so nothing changes for the running app until an operator flips `SECRETS_PROVIDER=aws`.

The stack is PHP 8.2 / CodeIgniter 4; `aws/aws-sdk-php ^3.365` is already installed (no dependency task needed). Tests run via PHPUnit inside the `admin-sgl-app` Docker container (host PHP is broken). Property-based tests use PHPUnit data-provider generators (no new runtime dependency); the `AwsSecretProvider` is exercised through the AWS SDK `MockHandler` so no live AWS calls are ever made in tests. Correctness Properties 1–8 from the design are each encoded as their own property-test sub-task.

The critical bootstrap-timing change (overriding `Config\Database::__construct`) is deferred until the service and both providers are complete and is guarded so `ENVIRONMENT === 'testing'` skips resolution. Automated secret rotation, KMS key management, and GroceryCrud credential handling are explicitly out of scope (Requirement 9.4) and have no tasks.

## Tasks

- [x] 1. Configuration layer (references only, no values)
  - [x] 1.1 Create `app/Config/Secrets.php`
    - Add `Config\Secrets extends BaseConfig` with typed properties `provider` (default `'env'`), `rdsReference` (default `''`), `region` (default `'us-east-1'`), `cacheTtl` (default `0`), each bound in the constructor to `SECRETS_PROVIDER`, `SECRETS_RDS_REFERENCE`, `SECRETS_REGION`, `SECRETS_CACHE_TTL` via `env()`; preserve the `env` default when the flag is absent/empty; contain NO secret VALUE settings and NO AWS access-key/secret-key settings
    - _Requirements: 1.4, 2.1, 8.4, 10.1_

  - [x] 1.2 Add commented `SECRETS_*` keys to `.env` and `env`
    - Add a `SECRETS` section with commented `SECRETS_PROVIDER`, `SECRETS_RDS_REFERENCE`, `SECRETS_REGION`, `SECRETS_CACHE_TTL` keys and NO values and NO access-key/secret-key entries, to both `.env` and the tracked `env` template
    - _Requirements: 2.1, 6.2, 8.4_

- [x] 2. Define the secret-resolution contract
  - [x] 2.1 Create the `App\Libraries\Secrets\SecretProvider` interface
    - Declare `getSecret(string $reference): array` returning a flat map (at least `host`, `username`, `password`, `dbname`, optional `port`) as the only contract the service and callers depend on; document that `$reference` is a name/ARN, never a value, and that failures throw `SecretResolutionException`
    - _Requirements: 1.1, 1.6_

  - [x] 2.2 Create the `App\Libraries\Secrets\SecretResolutionException`
    - Implement a `RuntimeException` constructed only from readonly `reference` + `reason` strings, building a message of the form `Secret resolution failed [reference=...]: <reason>` that never embeds secret material
    - _Requirements: 5.4, 8.2_

- [x] 3. Implement the Env provider (behavior-identical to today)
  - [x] 3.1 Implement `App\Libraries\Secrets\EnvSecretProvider`
    - `getSecret` ignores `$reference` and reads `database.default.hostname/database/username/password` (and optional `database.default.port`) via CI4 `env()`, mapping to canonical keys `host`, `dbname`, `username`, `password`, `port`; never contacts AWS and never throws for connectivity, returning whatever `.env` provides so absent keys fall back to `Config\Database` defaults
    - _Requirements: 7.1, 7.2, 7.3, 10.1_

  - [x] 3.2 Write contract tests for `EnvSecretProvider`
    - Assert values returned equal the configured `database.default.*` values and that no AWS SDK call is attempted
    - _Requirements: 7.1, 7.2, 7.3_

  - [x] 3.3 Write property test for env pass-through
    - **Property 6: Env provider passes through today's `.env` values unchanged**
    - **Validates: Requirements 7.1, 7.2, 7.3, 10.1**
    - For arbitrary `database.default.hostname/database/username/password` (and optional `port`), assert the resolved group fields equal exactly those values with no AWS contact

- [x] 4. Implement the AWS provider (fetch + parse + validate + redacted logging)
  - [x] 4.1 Implement `App\Libraries\Secrets\AwsSecretProvider`
    - Build `Aws\SecretsManager\SecretsManagerClient` from `version` + `region` only (NO `credentials` key, so the SDK default provider chain uses the IAM Instance Profile); `getSecret` calls `getSecretValue(['SecretId' => $reference])`, reads `SecretString`, rejects empty (`empty-secret-string`), `json_decode`s (reject `invalid-json`), validates non-empty `host`/`username`/`password`/`dbname` (reject `missing-field:<name>`), validates optional positive-integer `port` (reject `invalid-port`), and returns the in-memory map; every failure logs only `reference` + `reason` via `log_message('error', ...)` and throws `SecretResolutionException`, never logging the raw value; allow an injectable client/handler for tests
    - _Requirements: 2.2, 3.1, 3.3, 3.4, 5.1, 5.2, 6.1_

  - [x] 4.2 Write `AwsSecretProvider` contract tests (AWS SDK MockHandler)
    - Using `MockHandler`, cover: valid JSON blob returns the mapped fields; `AwsException` (unreachable / ResourceNotFound / AccessDenied) throws with the reference + reason; empty `SecretString`, malformed JSON, and each missing required field throw the matching reason — asserting no live AWS call occurs
    - _Requirements: 3.1, 3.3, 3.4, 5.1, 6.1_

  - [x] 4.3 Write property test for JSON field-mapping with port default
    - **Property 2: JSON field-mapping correctness with port default**
    - **Validates: Requirements 3.1, 3.5**
    - For arbitrary valid RDS secret JSON (host, username, password with special/unicode chars, dbname, optional port) via MockHandler, assert `hostname=host`, `username=username`, `password=password`, `database=dbname`, and `port` equals the given port when present or `3306` when omitted

- [x] 5. Checkpoint - contract and providers complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement the service facade (selection, cache, RDS mapping)
  - [x] 6.1 Implement `App\Libraries\Secrets\SecretsManagerService` provider selection
    - `implements SecretProvider`; constructor takes `Config\Secrets` and builds the active provider via a `match` on `provider` (`aws` → `AwsSecretProvider`, `env` → `EnvSecretProvider`, default → `InvalidArgumentException` naming the offending value and accepted values `aws`, `env`)
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

  - [x] 6.2 Implement per-request cached `getSecret` with optional TTL
    - Maintain an in-memory `cache` keyed by reference; return the cached value on hit when `expiresAt` is null or in the future without calling the provider; on miss call `provider->getSecret` (may throw — fail closed), store with `expiresAt = ttl > 0 ? now + ttl : null`, and return it
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 8.1_

  - [x] 6.3 Implement `resolveRdsInto(array &$group)` and `assertConfigured`
    - `assertConfigured` requires non-empty `rdsReference` and `region` when `provider === 'aws'` (throw `InvalidArgumentException` naming the missing setting); `resolveRdsInto` calls `assertConfigured`, resolves via cached `getSecret($config->rdsReference)`, and mutates `$group['hostname'|'username'|'password'|'database']` from the map with `port` defaulting to `3306`; on any failure `$group` is left unmodified and the exception propagates (fail closed)
    - _Requirements: 2.3, 2.4, 3.2, 3.5, 3.6, 5.3, 7.3_

  - [x] 6.4 Write unit tests for selection, cache, and mapping
    - Assert `env`/`aws` select the right provider; absent flag defaults to `env`; invalid flag throws naming the value and accepted values; missing reference/region under `aws` throws; `resolveRdsInto` populates all five keys and defaults port to `3306`; a failing resolve leaves `$group` unchanged
    - _Requirements: 1.4, 1.5, 2.3, 2.4, 3.2, 3.5, 5.3_

  - [x] 6.5 Write property test for cache single-fetch and TTL
    - **Property 3: Cache performs at most one fetch per reference per request**
    - **Validates: Requirements 4.1, 4.2, 4.3, 4.4**
    - For `k ≥ 1` resolutions of the same reference (no TTL or within an unexpired TTL) assert the underlying provider is invoked exactly once and all results are equal; when a configured TTL has elapsed, assert exactly one refetch occurs

  - [x] 6.6 Write property test for fail-closed behavior
    - **Property 4: Fail closed on invalid config or unresolvable secret**
    - **Validates: Requirements 2.3, 2.4, 3.3, 3.4, 3.6, 5.1, 5.3**
    - For each failure mode (missing/empty reference or region under `aws`, unreachable Secrets Manager, non-existent reference, access denied, invalid JSON, missing required field), assert resolution raises an error naming the reference (and missing field where applicable) and leaves the target group unmodified

  - [x] 6.7 Write property test for invalid provider-flag rejection
    - **Property 7: Invalid provider flag is rejected**
    - **Validates: Requirements 1.5**
    - For arbitrary non-empty flag strings that are neither `aws` nor `env`, assert constructing the service raises a descriptive error whose message contains the offending value and the accepted values `aws` and `env`

- [x] 7. Bootstrap wiring (resolution point + shared service)
  - [x] 7.1 Register the `Services::secrets()` shared service
    - Add a `secrets(bool $getShared = true)` factory to `app/Config/Services.php` returning a shared `SecretsManagerService` built from `config('Secrets')`, so the provider and SDK client are built once per request
    - _Requirements: 1.1, 4.1_

  - [x] 7.2 Override `Config\Database::__construct` to resolve RDS credentials
    - After `parent::__construct()` and the existing `DOCKER_DB_HOST` override, short-circuit when `ENVIRONMENT === 'testing'` (set `defaultGroup = 'tests'` and return, never resolving secrets); otherwise call `service('secrets')->resolveRdsInto($this->default)` so credentials are populated before the first connection on both the web and `spark` CLI paths, and any resolution error propagates (fail closed)
    - _Requirements: 3.2, 3.6, 5.3, 10.1, 10.2, 10.3_

  - [x] 7.3 Write property test for provider transparency and selection
    - **Property 1: Provider transparency and selection**
    - **Validates: Requirements 1.2, 1.3, 1.4, 1.6, 10.1, 10.2, 10.3**
    - For provider flag in `{aws (MockHandler), env}` and the absent/empty flag (defaults to `env`), assert resolving through the single `SecretsManagerService` interface produces a fully-populated connection group using the provider-appropriate backend with no change to caller code

- [x] 8. Checkpoint - service and bootstrap wired
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Cross-cutting security property tests
  - [x] 9.1 Write property test for secret-free logging and errors
    - **Property 5: Secret value never appears in logs or error messages**
    - **Validates: Requirements 5.2, 5.4, 8.2, 8.3**
    - For arbitrary secret values across every error path (via MockHandler), capture log output and exception messages and assert neither ever contains the secret value — only the reference and a reason code appear

  - [x] 9.2 Write assertion/property test for no secrets in artifacts
    - **Property 8: No secrets in artifacts**
    - **Validates: Requirements 8.4, 2.2, 6.2**
    - Assert the `SecretsManagerClient` is constructed with no literal `credentials` entry, and scan `.env`, `env`, `app/Config/Secrets.php`, and the operator docs for the absence of AWS access-key/secret-key patterns and RDS secret values (only reference, region, and provider flag present)

- [x] 10. Integration tests (bootstrap timing + fail-closed)
  - [x] 10.1 Write bootstrap integration test with provider toggled
    - Bootstrap `Config\Database` with `SECRETS_PROVIDER=env` and with `SECRETS_PROVIDER=aws` (MockHandler-backed) and assert the `default` group is fully populated before the first query on both the web front-controller path and the `spark` CLI path
    - _Requirements: 3.2, 3.6, 7.3, 10.2, 10.3_

  - [x] 10.2 Write fail-closed bootstrap smoke test
    - Simulate a failing resolver (unreachable/missing secret via MockHandler) and assert `Config\Database` construction propagates the error so no connection is opened with partial credentials, and that `ENVIRONMENT === 'testing'` skips resolution entirely
    - _Requirements: 5.1, 5.3, 3.6_

- [x] 11. Operator documentation
  - [x] 11.1 Create `SECRETS_MANAGER_README.md`
    - Following the repo's existing `*_README.md` conventions, document: creating the RDS secret as a JSON blob (`host`/`port`/`username`/`password`/`dbname`) via `aws secretsmanager create-secret` and rotation via `put-secret-value`; the exact `.env` keys for the `aws` provider (`SECRETS_PROVIDER`, `SECRETS_RDS_REFERENCE`, `SECRETS_REGION`, optional `SECRETS_CACHE_TTL`) with no values; the IAM policy statement granting `secretsmanager:GetSecretValue` scoped to the secret ARN; enabling/reverting via the provider flag; and an explicit note that automated rotation, KMS key management, and GroceryCrud credential handling are out of scope
    - _Requirements: 6.3, 9.1, 9.2, 9.3, 9.4_

- [x] 12. Final checkpoint - full abstraction verified in env mode
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP; core implementation tasks are never optional.
- Steps 1–7 build and verify the abstraction with `EnvSecretProvider` behavior-identical to today, so nothing changes for the running system until an operator sets `SECRETS_PROVIDER=aws`.
- Each task references specific requirement clauses for traceability; property-test sub-tasks reference the exact correctness property from the design and the requirement clauses it validates.
- Checkpoints ensure incremental validation. Property tests use PHPUnit data-provider generators (minimum 100 iterations); the `AwsSecretProvider` is exercised through the AWS SDK `MockHandler` so no live AWS calls occur. All tests run via PHPUnit inside the `admin-sgl-app` Docker container.
- The bootstrap-timing change (task 7.2) intentionally depends on the service (task 6.x) and both providers (tasks 3, 4) being complete, and is verified by the bootstrap integration test (task 10.1) and the fail-closed smoke test (task 10.2).
- AWS secret creation and IAM policy attachment are operator CLI actions; only their documentation is produced here (task 11.1).
- Requirement 9.4 out-of-scope items (automated rotation, KMS key management, GroceryCrud credential handling) intentionally have no implementation tasks.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "2.1", "2.2"] },
    { "id": 1, "tasks": ["1.2", "3.1", "4.1"] },
    { "id": 2, "tasks": ["3.2", "3.3", "4.2", "4.3", "6.1"] },
    { "id": 3, "tasks": ["6.2", "7.1"] },
    { "id": 4, "tasks": ["6.3"] },
    { "id": 5, "tasks": ["6.4", "6.5", "6.6", "6.7", "7.2"] },
    { "id": 6, "tasks": ["7.3", "9.1", "9.2", "10.1"] },
    { "id": 7, "tasks": ["10.2", "11.1"] }
  ]
}
```

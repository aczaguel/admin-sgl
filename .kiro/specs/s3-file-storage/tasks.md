# Implementation Plan: S3 File Storage Abstraction & Non-Destructive Migration

## Overview

This plan builds the storage abstraction in `local` mode first so current behavior is unchanged, then wires the S3 driver, then integrates the existing upload call sites, and finally adds the read-only migration verification command. Every step is incremental and safe to run against the existing system while `FILE_STORAGE_DRIVER=local` (or unset), so no current behavior changes until the operator flips the flag to `s3`.

The stack is PHP / CodeIgniter 4 (`php: ^7.2 || ^8.0`). Property-based tests use PHPUnit data-provider generators (no new runtime dependency); `mikey179/vfsstream` (existing dev dep) backs local-driver contract tests and the AWS SDK `MockHandler` backs S3-driver tests. Correctness Properties 1–10 from the design are each encoded as their own property-test sub-task.

GroceryCrud deep integration, Redis sessions, CloudFront/OAC, autoscaling/EC2/Terraform are explicitly out of scope (Requirement 11) and have no tasks.

## Tasks

- [x] 1. Add AWS SDK dependency
  - [x] 1.1 Add `aws/aws-sdk-php` as a runtime dependency
    - Run `composer require aws/aws-sdk-php` (PHP 7.2+ compatible per design Dependencies); commit the updated `composer.json` and `composer.lock`
    - Confirm no S3 access key or secret is added anywhere in config or the repo (credentials will come from the Instance Profile)
    - _Requirements: 10.1, 10.2_

- [x] 2. Define the storage contract and configuration wiring (local defaults only)
  - [x] 2.1 Create the `App\Libraries\Storage\FileStorage` interface
    - Declare `put(string $key, string $localTmpPath): bool`, `delete(string $key): bool`, `url(string $key, int $ttlSeconds = 300): string`, `exists(string $key): bool` as the only storage contract application code depends on
    - _Requirements: 1.1, 2.1, 2.2, 2.3, 2.9_

  - [x] 2.2 Create `Config\FileStorage` config and `.env` keys
    - Add `app/Config/FileStorage.php` with `driver` (default `local`), `bucket`, `region` (default `us-east-1`), `dualWrite` (default `false`), `presignTtl` (default `300`), `localRoot` (`FCPATH.'assets/uploads'`), `sse` (`AES256`), each bound to `FILE_STORAGE_DRIVER`, `S3_BUCKET`, `S3_REGION`, `FILE_STORAGE_DUAL_WRITE`, presign TTL, and SSE env keys
    - Add the corresponding commented keys to `.env` (and `.env` example if present); include NO access-key/secret keys
    - _Requirements: 1.2, 1.3, 9.4, 10.2, 10.5_

  - [x] 2.3 Register the `Services::fileStorage` shared service
    - Add a `fileStorage()` factory to `app/Config/Services.php` returning a shared `FileStorageService` built from `config('FileStorage')`
    - _Requirements: 1.1, 1.7_

- [x] 3. Implement key derivation, legacy normalization, and the URL helper
  - [x] 3.1 Implement `buildKey(category, id, originalName)`
    - Sanitize the base name to `[A-Za-z0-9._-]`, append a `>=8` char random suffix, place numeric id as the second segment for per-id categories (`pago_gestor`, `pago_derechos`, `cobro_cliente`), and reject per-id categories missing a valid positive-integer id; assert the result matches `^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$` and 1–1024 chars
    - _Requirements: 3.1, 3.2, 3.3, 3.5, 3.6, 3.7_

  - [x] 3.2 Write property test for key safety
    - **Property 4: Key safety**
    - **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**
    - For arbitrary `category`/`originalName`, assert `buildKey` never yields a `..` segment, leading `/`, or backslash and always matches the required pattern

  - [x] 3.3 Implement `keyFromStored(storedValue, category, id)` legacy normalizer
    - Handle bare filename (rebuild `category[+/id]/filename`), relative key (strip leading `/`), absolute URL with `/assets/uploads/` prefix (strip origin+prefix), and absolute URL without that prefix (strip origin only); classify bare filename as no `/` and no scheme; return canonical key unchanged when applied to its own output
    - _Requirements: 4.3, 4.4, 4.5, 4.6, 4.8, 4.9, 5.1, 5.4_

  - [x] 3.4 Write property test for backward-compatible read
    - **Property 5: Backward-compatible read**
    - **Validates: Requirements 4.2, 4.3, 4.4, 4.5, 4.6, 5.1, 5.3**
    - For arbitrary category/id/filename, assert `keyFromStored(bareFilename)` equals the canonical key and is a fixed point when re-applied to its own output

  - [x] 3.5 Implement the `file_url(storedValue, category, id, ttl)` helper
    - Return `''` for empty/null/whitespace-only stored values without invoking the normalizer or service; otherwise normalize via `keyFromStored` and delegate to `service('fileStorage')->url(key, ttl)`; return `''` when a legacy value cannot be normalized (no unhandled error)
    - _Requirements: 4.1, 4.2, 4.7, 5.3, 5.5_

  - [x] 3.6 Write unit tests for `file_url` / `keyFromStored` (table-driven)
    - Cover bare filename, relative key, absolute URL (with and without prefix), per-id vs flat categories, and empty/whitespace input
    - _Requirements: 4.1, 4.7, 5.5_

- [x] 4. Implement the Local driver (behavior-identical to current system)
  - [x] 4.1 Implement `LocalFileStorage`
    - `put`: create target dir (mirroring current `mkdir(...,0777,true)`) and `move_uploaded_file()`/`rename()` into `FCPATH.'assets/uploads/'.$key`, returning `false` with no partial-visible state on failure; `url`: `base_url('/assets/uploads/'.$key)` (rawurlencoded per segment); `delete`: idempotent `@unlink`; `exists`: `is_file`; reject keys with `..`, leading `/`, or backslash before any filesystem action
    - _Requirements: 1.5, 2.1, 2.2, 2.3, 2.4, 2.6, 2.9, 3.4_

  - [x] 4.2 Write local-driver contract tests (vfsStream)
    - put→exists→url→delete round-trip, traversal rejection, and same-path/`base_url` behavior as the current system
    - _Requirements: 1.5, 2.6, 3.4_

  - [x] 4.3 Write property test for round-trip against the local driver
    - **Property 2: Round-trip**
    - **Validates: Requirements 2.1, 2.2**
    - Random valid keys and payloads: after `put`, `exists=true`; after `delete`, `exists=false`

  - [x] 4.4 Write property test for delete idempotence against the local driver
    - **Property 3: Delete idempotence**
    - **Validates: Requirements 2.3, 2.4**
    - Repeated `delete` on random keys always returns `true` and never affects any key ≠ k

- [x] 5. Checkpoint - local abstraction complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement the S3 driver
  - [x] 6.1 Implement `S3FileStorage`
    - Build `Aws\S3\S3Client` from region only (no credentials key, so the SDK resolves Instance Profile creds); `put`: `PutObject` with `SourceFile` + `ServerSideEncryption`, returning `false` on exception and false if SSE cannot be applied; `url`: presigned `GetObject` for `+ttl seconds`, returning an error indication for `ttl <= 0` or `ttl > 604800`; `delete`: idempotent `DeleteObject`; `exists`: `doesObjectExist`; reject traversal keys before any S3 action
    - _Requirements: 1.3, 2.5, 2.6, 2.7, 2.8, 2.9, 3.4, 10.1, 10.3, 10.6, 10.7_

  - [x] 6.2 Write S3-driver contract tests (AWS SDK MockHandler)
    - put→exists→url→delete round-trip, delete idempotence on absent key, traversal rejection, SSE header present on `PutObject`, and `ttl<=0` / `ttl>604800` rejected
    - _Requirements: 2.5, 2.6, 2.7, 2.8, 10.3, 10.7_

  - [x] 6.3 Write property test for round-trip and delete idempotence against the mocked S3 driver
    - **Property 2: Round-trip** and **Property 3: Delete idempotence**
    - **Validates: Requirements 2.1, 2.2, 2.3, 2.4**
    - Random valid keys against the MockHandler-backed driver

  - [x] 6.4 Write property test for presigned expiry
    - **Property 9: Presigned expiry**
    - **Validates: Requirements 2.5, 10.5**
    - For random valid `ttl` in (0, 604800], assert the presigned URL encodes an expiry of `ttl` seconds from issuance and defaults to 300; assert out-of-range `ttl` yields an error indication rather than a URL

  - [x] 6.5 Write property/assertion test for no secrets in artifacts
    - **Property 10: No secrets in artifacts**
    - **Validates: Requirements 10.1, 10.2**
    - Assert the S3 client is constructed without a literal `credentials` entry and scan `.env`/config for the absence of access-key/secret patterns

- [x] 7. Implement the service facade (driver selection, fallback, dual-write)
  - [x] 7.1 Implement `FileStorageService` driver selection with safe fallback
    - Select `S3FileStorage` when `driver === 's3'`, otherwise `LocalFileStorage`; when `FILE_STORAGE_DRIVER` is unset/empty/unrecognized, select the local driver and log the fallback; switching the flag must require no code or stored-data change
    - _Requirements: 1.2, 1.3, 1.6, 1.7_

  - [x] 7.2 Implement optional dual-write in the facade
    - When `dualWrite` is enabled, `put` writes to both local disk and S3 under the identical key and reports failure if either write fails; when disabled or unset, write only to the active driver
    - _Requirements: 9.1, 9.2, 9.3, 9.4_

  - [x] 7.3 Write property test for driver transparency
    - **Property 1: Driver transparency**
    - **Validates: Requirements 1.4, 1.5**
    - For random keys under both `local` and mocked `s3`, `put(k)` then `url(k)` yields a URL resolving to the stored bytes

  - [x] 7.4 Write unit tests for fallback and dual-write
    - Assert unrecognized driver falls back to local and logs; assert dual-write failure on either store surfaces as a failed persist
    - _Requirements: 1.6, 9.3_

- [x] 8. Checkpoint - both drivers and facade complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Integrate existing upload call sites through the service
  - [x] 9.1 Integrate the unified-tramite flow in `app/Controllers/Deskapp/Tramitesn.php`
    - Replace the custom `move_uploaded_file()` block(s) with `buildKey` + `service('fileStorage')->put(key, tmpName)`; return 400 when no file / empty tmp path; store the canonical source-of-truth value (bare filename for legacy fields, key otherwise); return `file_url(...)` on success and an error response when `put` fails or the URL cannot be resolved
    - _Requirements: 6.1, 6.3, 6.5, 6.6, 6.8, 5.1, 5.2_

  - [x] 9.2 Add compensating-delete-on-DB-failure at the `Tramitesn.php` call sites
    - On DB write failure after a successful `put`, call `delete(key)` exactly once and return 500 with a persist-failure body; on compensating-delete failure, return 500 and record the orphaned key for later cleanup; on full success insert the row and do not call `delete`
    - _Requirements: 7.1, 7.2, 7.4, 7.5, 7.6, 6.6_

  - [x] 9.3 Integrate the custom endpoints in `app/Controllers/Deskapp/Tramites.php`
    - Apply the same `put` replacement, 400-on-missing-file, canonical-value storage, `file_url` return, and compensating-delete-on-DB-failure as 9.1/9.2
    - _Requirements: 6.1, 6.3, 6.5, 6.6, 6.8, 7.1, 7.2, 7.4_

  - [x] 9.4 Write test for no-orphan-on-failure at integrated call sites
    - **Property 8: No orphan on failure**
    - **Validates: Requirements 7.1**
    - Simulate a DB failure after a successful `put` and assert exactly one compensating `delete(key)` runs so no object without a referencing row remains

  - [x] 9.5 Integrate the CI4 `$file->move()` path in `app/Controllers/Deskapp/Users.php`
    - Replace `$file->move()` with `buildKey` + `service('fileStorage')->put(key, $file->getTempName())`; return 400 on missing file/empty temp path; store canonical value; route deletes through `delete`; compensate on DB failure
    - _Requirements: 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 7.1_

  - [x] 9.6 Integrate the CI4 `$file->move()` path in `TramiteWizard.php`
    - Same `put`/`getTempName` replacement, 400 handling, canonical-value storage, delete routing, and compensating-delete as 9.5
    - _Requirements: 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 7.1_

  - [x] 9.7 Integrate the CI4 `$file->move()` path in `app/Services/ExternalTramiteService.php`
    - Replace `$archivo->move(...)` with a `tramites/<id>/<name>` key + `put($file->getTempName())`; store canonical value; route deletes through `delete`; compensate on DB failure
    - _Requirements: 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 7.1_

  - [x] 9.8 Route record-removal deletes through the service
    - At the model/controller record-removal paths for integrated categories, resolve the key from the stored value+category+id and call `delete`; on delete failure return an error and retain the existing reference unchanged
    - _Requirements: 6.4, 6.7_

- [x] 10. Update view/gallery URL generation
  - [x] 10.1 Replace `base_url('assets/uploads/...')` construction with `file_url()` in views/galleries
    - Update the gallery/preview views for steps 1–5 and any JSON responses to call `file_url(storedValue, category, id)` so URLs are presigned when `driver=s3` and `base_url` when `local`; leave stored DB values unmodified
    - _Requirements: 4.2, 5.3, 10.4_

- [x] 11. Implement the migration integrity command
  - [x] 11.1 Implement the `s3:migrate-check` spark command
    - Count local files under the uploads root (excluding `*.tmp`) and count S3 objects; report drift = local − s3; success when drift ≤ 0 (and drift 0 when local count is 0); warning with drift value when local > s3; error (no drift reported) when the bucket is unreachable; perform read-only operations and never delete any local or S3 object
    - _Requirements: 8.3, 8.4, 8.5, 8.6, 8.8, 8.9_

  - [x] 11.2 Write unit tests for the migrate-check command
    - Cover drift ≤ 0 success, local > s3 warning with drift value, zero-local success, and bucket-unreachable error (no drift reported)
    - _Requirements: 8.3, 8.4, 8.5, 8.8, 8.9_

  - [x] 11.3 Write property test for non-destructive migration
    - **Property 6: Non-destructive migration**
    - **Validates: Requirements 8.2, 8.6**
    - For arbitrary seeded local file sets, assert the local file count after running the check equals the count before (read-only over both stores)

  - [x] 11.4 Write property/integration test for migration coverage before flip
    - **Property 7: Migration coverage before flip**
    - **Validates: Requirements 8.3, 8.4, 8.5**
    - For arbitrary local file sets synced (no `--delete`) to a mocked/sandbox bucket, assert every local file has an S3 counterpart (drift ≤ 0) before the flag flip

- [x] 12. Write the driver-transparency and migration-rehearsal integration tests
  - End-to-end upload → DB → `file_url` render with `FILE_STORAGE_DRIVER` toggled between `local` and mocked/sandbox `s3` to confirm driver transparency (Property 1); dual-write window test asserting a new upload lands in both stores; automated migration rehearsal that seeds local files, syncs without `--delete`, runs `s3:migrate-check`, and confirms local file count is unchanged
  - _Requirements: 1.4, 8.2, 8.6, 9.1_

- [x] 13. Final checkpoint - full abstraction verified in local mode
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP; core implementation tasks are never optional.
- Steps 1–8 build and verify the abstraction with `LocalFileStorage` behavior-identical to today, so nothing changes for the running system until the operator sets `FILE_STORAGE_DRIVER=s3`.
- Each task references specific requirement clauses for traceability; property-test sub-tasks reference the exact correctness property from the design and the requirement clauses it validates.
- Checkpoints ensure incremental validation. Property tests use PHPUnit data-provider generators; local-driver tests use vfsStream and S3-driver tests use the AWS SDK MockHandler (no live AWS calls in unit tests).
- The `aws s3 sync` copy itself is an operator CLI action (per `INFRA_AWS_S3_MIGRACION.md` §4.6); only the read-only count/verify step is automated in `s3:migrate-check`.
- Requirement 11 (GroceryCrud deep integration, Redis sessions, CloudFront/OAC, autoscaling/EC2/Terraform) is out of scope and intentionally has no tasks.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "2.1", "3.1", "3.3"] },
    { "id": 1, "tasks": ["2.2", "2.3", "3.2", "3.4", "3.5"] },
    { "id": 2, "tasks": ["3.6", "4.1", "6.1"] },
    { "id": 3, "tasks": ["4.2", "4.3", "4.4", "6.2", "6.3", "6.4", "6.5", "7.1"] },
    { "id": 4, "tasks": ["7.2", "7.3", "7.4", "10.1", "11.1"] },
    { "id": 5, "tasks": ["9.1", "9.3", "9.5", "9.6", "9.7", "11.2", "11.3", "11.4"] },
    { "id": 6, "tasks": ["9.2", "9.8"] },
    { "id": 7, "tasks": ["9.4", "12"] }
  ]
}
```

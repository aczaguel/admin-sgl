# Requirements Document

## Introduction

This feature introduces a storage abstraction layer into the CodeIgniter 4 application (admin-sgl) so that uploaded documents and images can be persisted on Amazon S3 instead of the local EC2 disk, while preserving today's exact behavior when running in `local` mode. The abstraction exposes two swappable drivers — a local driver (current `FCPATH` + `base_url()` behavior) and an S3 driver (private bucket, presigned URLs, IAM Instance Profile credentials) — selected at runtime via a single `.env` flag.

The goal is to make the application stateless with respect to files, the prerequisite for scaling the compute tier. Existing files are copied to S3 through a one-time, non-destructive migration (`aws s3 sync` without `--delete`), verified by an integrity check (local file count vs S3 object count), with the local disk preserved as a backup and bucket versioning enabled.

These requirements are derived from the approved design document (`design.md`) and follow `INFRA_AWS_S3_MIGRACION.md` (§4.1–§4.6) as the source of truth. They cover the storage service and both drivers, configuration wiring, URL resolution with backward-compatible reading of legacy values, key derivation, integration of the existing upload mechanisms, the non-destructive migration procedure with integrity verification, an optional dual-write window, and the security posture (private bucket, presigned URLs, Instance Profile credentials, encryption, path-traversal defense).

## Glossary

- **Storage_Service**: The `FileStorageService` facade/factory that resolves and exposes the active storage driver based on configuration. Implements the storage contract.
- **Storage_Contract**: The `FileStorage` interface defining the operations `put`, `delete`, `url`, and `exists`. All drivers implement it and all application code depends only on it.
- **Local_Driver**: The `LocalFileStorage` implementation that reproduces the current `FCPATH` + `base_url()` behavior.
- **S3_Driver**: The `S3FileStorage` implementation that persists to a private S3 bucket and serves via presigned URLs using IAM Instance Profile credentials.
- **Active_Driver**: Whichever of Local_Driver or S3_Driver is selected by the `FILE_STORAGE_DRIVER` configuration value at runtime.
- **URL_Resolver**: The `file_url()` helper that normalizes a stored value to a canonical key and delegates URL generation to the Storage_Service.
- **Key_Builder**: The `buildKey()` routine that derives a traversal-safe relative key from a category, optional id, and original filename.
- **Legacy_Normalizer**: The `keyFromStored()` routine that converts a legacy stored value (bare filename, relative key, or absolute URL) into a canonical relative key.
- **Migration_Command**: The `s3:migrate-check` spark CLI command that verifies migration integrity by comparing local file count to S3 object count and reporting drift.
- **Upload_Handler**: Application upload code paths integrated with the Storage_Service — the custom `move_uploaded_file()` endpoints (`Tramitesn.php`, `Tramites.php`) and the CI4 `$file->move()` paths (`Users.php`, `TramiteWizard.php`, `ExternalTramiteService.php`).
- **Relative_Key**: The canonical storage identifier of the form `<category>/<id?>/<uniqueName>.<ext>` (e.g. `pago_gestor/12472/abc.jpg`), stored as the source of truth in the database going forward.
- **Presigned_URL**: A time-limited S3 GET URL generated at render time, valid for a configured TTL.
- **Instance_Profile**: The EC2 IAM Instance Profile from which the AWS SDK reads temporary credentials, so no access keys exist in configuration or the repository.

## Requirements

### Requirement 1: Storage abstraction with swappable drivers

**User Story:** As a platform operator, I want a single configuration flag to switch the entire application between local and S3 storage, so that I can move files off the EC2 disk without code or data changes.

#### Acceptance Criteria

1. THE Storage_Service SHALL expose the Storage_Contract operations `put`, `delete`, `url`, and `exists` as the only interface application code uses for file storage.
2. WHERE `FILE_STORAGE_DRIVER` is set to `local`, THE Storage_Service SHALL select the Local_Driver as the Active_Driver.
3. WHERE `FILE_STORAGE_DRIVER` is set to `s3`, THE Storage_Service SHALL select the S3_Driver as the Active_Driver.
4. FOR ALL Relative_Keys, WHEN `put` is followed by `url` under either Active_Driver, THE Storage_Service SHALL return a URL that resolves to the bytes stored by that `put`.
5. WHILE the Active_Driver is the Local_Driver, THE Local_Driver SHALL produce the same on-disk paths (`FCPATH + 'assets/uploads/' + key`) and `base_url('/assets/uploads/' + key)` URLs as the current system.
6. IF `FILE_STORAGE_DRIVER` is unset, empty, or set to an unrecognized value, THEN THE Storage_Service SHALL select the Local_Driver as the Active_Driver and SHALL log the fallback.
7. WHEN `FILE_STORAGE_DRIVER` is changed between `local` and `s3`, THE Storage_Service SHALL switch the Active_Driver without any change to application code or to stored database values.

### Requirement 2: Storage contract operations

**User Story:** As a developer, I want a stable put/delete/url/exists contract, so that upload, render, and delete paths behave consistently regardless of the backing store.

#### Acceptance Criteria

1. WHEN `put(key, localTmpPath)` completes successfully, THE Active_Driver SHALL make `exists(key)` return true.
2. WHEN `delete(key)` completes, THE Active_Driver SHALL make `exists(key)` return false.
3. WHEN `delete(key)` is invoked and no object exists at `key`, THE Active_Driver SHALL return true without modifying any object at a key other than `key`.
4. WHEN `delete(key)` is invoked more than once for the same `key`, THE Active_Driver SHALL return true on each invocation and affect only the object at `key`.
5. WHERE the Active_Driver is the S3_Driver, WHEN `url(key, ttl)` is invoked with `ttl` greater than zero, THE S3_Driver SHALL return a Presigned_URL that grants access within `ttl` seconds of issuance and denies access after `ttl` seconds have elapsed.
6. IF `put(key, localTmpPath)` fails at the storage layer, THEN THE Active_Driver SHALL return false and leave no partially written object visible at `key`.
7. IF `url(key, ttl)` is invoked with `ttl` less than or equal to zero, THEN THE S3_Driver SHALL return an error indication and SHALL NOT return a Presigned_URL.
8. IF `url(key, ttl)` is invoked with `ttl` greater than 604800 seconds, THEN THE S3_Driver SHALL return an error indication and SHALL NOT return a Presigned_URL.
9. WHEN `exists(key)` is invoked for a key that was never written, THE Active_Driver SHALL return false without raising an error.

### Requirement 3: Key derivation and path-traversal defense

**User Story:** As a security-conscious engineer, I want all storage keys to be derived safely, so that no upload can escape its intended prefix or produce an invalid S3 key.

#### Acceptance Criteria

1. WHEN the Key_Builder derives a key from a category, optional id, and original filename, THE Key_Builder SHALL produce a Relative_Key that matches the pattern `^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$` and is between 1 and 1024 characters in length.
2. WHEN the Key_Builder derives a key, THE Key_Builder SHALL produce a Relative_Key that contains no `..` segment, no leading `/`, and no backslash.
3. WHERE a category is per-id (`pago_gestor`, `pago_derechos`, `cobro_cliente`), WHEN the Key_Builder derives a key, THE Key_Builder SHALL place the numeric id as the second path segment.
4. IF a key containing a `..` segment, a leading `/`, or a backslash reaches the Active_Driver, THEN THE Active_Driver SHALL reject the operation before performing any filesystem or S3 action.
5. WHEN the Key_Builder derives a key, THE Key_Builder SHALL append a random suffix of at least 8 alphanumeric characters to the filename so that keys derived for the same category and id within the same second are distinct.
6. IF a per-id category (`pago_gestor`, `pago_derechos`, `cobro_cliente`) is supplied without a valid positive-integer id, THEN THE Key_Builder SHALL reject the operation and SHALL NOT produce a key.
7. WHEN the original filename contains characters outside `[A-Za-z0-9._-]`, THE Key_Builder SHALL sanitize those characters so that the produced Relative_Key still matches the required pattern.

### Requirement 4: URL resolution with backward-compatible legacy reading

**User Story:** As a developer maintaining views and galleries, I want a single helper that resolves any stored value to a browser URL, so that legacy rows keep working without a data backfill.

#### Acceptance Criteria

1. WHEN the URL_Resolver receives an empty stored value, THE URL_Resolver SHALL return an empty string.
2. WHEN the URL_Resolver receives a stored value together with its category and optional id, THE URL_Resolver SHALL normalize the value to a canonical Relative_Key via the Legacy_Normalizer and return the URL produced by the Storage_Service for that key.
3. IF a stored value is a bare filename, THEN THE Legacy_Normalizer SHALL rebuild the canonical Relative_Key as `category` plus the id segment when the id is provided plus the filename.
4. IF a stored value is already a relative key containing a `/` with no URL scheme, THEN THE Legacy_Normalizer SHALL use that value as the canonical Relative_Key after removing any leading `/`.
5. IF a stored value is an absolute URL beginning with `http://` or `https://`, THEN THE Legacy_Normalizer SHALL strip the origin and the `/assets/uploads/` prefix to recover the canonical Relative_Key.
6. WHEN the Legacy_Normalizer is applied to its own output, THE Legacy_Normalizer SHALL return that same canonical Relative_Key unchanged.
7. WHEN the URL_Resolver receives a null or whitespace-only stored value, THE URL_Resolver SHALL return an empty string without invoking the Legacy_Normalizer or the Storage_Service.
8. THE Legacy_Normalizer SHALL classify a stored value as a bare filename when, after trimming, it contains no `/` and no `http://` or `https://` scheme.
9. IF a stored value is an absolute URL that does not contain the `/assets/uploads/` prefix, THEN THE Legacy_Normalizer SHALL strip only the origin and remove any leading `/` to recover the Relative_Key.

### Requirement 5: Relative keys as the source of truth

**User Story:** As a data owner, I want the database to store relative keys going forward, so that the driver, bucket, or CDN can change without touching stored data.

#### Acceptance Criteria

1. WHEN an upload is persisted going forward, THE Upload_Handler SHALL store a value that, when passed to the Legacy_Normalizer, resolves to the canonical Relative_Key of the stored object.
2. THE Upload_Handler SHALL NOT store a value containing an `http://` or `https://` scheme or a host origin as the source-of-truth value for an upload.
3. WHERE existing rows contain bare filenames, relative keys, or absolute URLs, THE URL_Resolver SHALL return the Storage_Service URL for the Legacy_Normalizer-recovered key using the stored value, its category, and its optional id, without modifying the row.
4. THE source-of-truth value stored going forward SHALL match the Relative_Key pattern with no leading `/`, no `..` segment, and no backslash.
5. IF a legacy stored value cannot be normalized to a Relative_Key, THEN THE URL_Resolver SHALL return an empty string without raising an unhandled error and without modifying the row.

### Requirement 6: Integration of existing upload mechanisms

**User Story:** As a developer, I want the existing upload endpoints routed through the storage service, so that all writes, deletes, and URLs go through one abstraction.

#### Acceptance Criteria

1. WHEN a custom `move_uploaded_file()` endpoint in `Tramitesn.php` or `Tramites.php` receives a valid upload, THE Upload_Handler SHALL persist the file by calling `put` on the Storage_Service instead of calling `move_uploaded_file()` directly.
2. WHEN a CI4 `$file->move()` path in `Users.php`, `TramiteWizard.php`, or `ExternalTramiteService.php` receives a valid upload, THE Upload_Handler SHALL persist the file by calling `put` on the Storage_Service using the uploaded file's temporary path.
3. IF a request contains no file or an empty temporary path, THEN THE Upload_Handler SHALL return a 400 response and SHALL NOT call `put`.
4. WHEN a record referencing a stored file is removed, THE Upload_Handler SHALL delete the underlying object by calling `delete` on the Storage_Service.
5. WHEN an upload succeeds, THE Upload_Handler SHALL return the browser URL produced by the URL_Resolver for the stored value.
6. IF the `put` call on the Storage_Service fails or throws an exception, THEN THE Upload_Handler SHALL return an error response indicating the upload could not be persisted and SHALL NOT record a file reference in the associated database record.
7. IF the `delete` call on the Storage_Service fails or throws an exception, THEN THE Upload_Handler SHALL return an error response indicating the deletion failed and SHALL retain the existing record reference unchanged.
8. IF the URL_Resolver cannot produce a browser URL for the stored value, THEN THE Upload_Handler SHALL return an error response indicating the stored value could not be resolved rather than returning an empty or malformed URL.

### Requirement 7: No orphaned objects on failure

**User Story:** As a data owner, I want a failed database write to clean up its uploaded object, so that the store never accumulates objects with no referencing row.

#### Acceptance Criteria

1. IF the database write fails after a successful `put` within the same request, THEN THE Upload_Handler SHALL call `delete` on the Storage_Service exactly once for the just-written key before returning a response.
2. IF the database write fails after a successful `put`, THEN THE Upload_Handler SHALL return a 500 response and SHALL NOT retain any referencing row for the just-written key.
3. WHEN a `put` fails before any database write, THE Upload_Handler SHALL return a 500 response and SHALL NOT insert a referencing row.
4. WHEN the `put` succeeds and the database write succeeds within the same request, THE Upload_Handler SHALL insert the referencing row for the written key and SHALL NOT call `delete` on the Storage_Service for that key.
5. IF the compensating `delete` on the Storage_Service fails after a failed database write, THEN THE Upload_Handler SHALL return a 500 response and SHALL record the orphaned key so it can be identified for later cleanup.
6. IF the database write fails after a successful `put`, THEN THE Upload_Handler SHALL return a 500 response whose body includes an error indication that the upload could not be persisted.

### Requirement 8: Non-destructive migration with integrity verification

**User Story:** As a DevOps operator, I want to copy existing files to S3 without ever deleting local files, so that the migration is safe and reversible.

#### Acceptance Criteria

1. THE migration procedure SHALL copy local files to S3 using `aws s3 sync` and SHALL NOT include the `--delete` flag in the command.
2. WHEN the migration procedure runs, THE migration procedure SHALL perform only read operations against the local disk, so that the count of local files after migration equals the count of local files before migration.
3. WHEN the Migration_Command runs, THE Migration_Command SHALL compute the local file count and the S3 object count and SHALL report the drift, where drift is defined as the absolute difference between the local file count and the S3 object count.
4. WHEN the local file count is less than or equal to the S3 object count, THE Migration_Command SHALL report a success result indicating that every local file has a corresponding S3 counterpart.
5. IF the local file count exceeds the S3 object count, THEN THE Migration_Command SHALL report a warning that includes the drift value, indicating that the operator must re-run the incremental sync.
6. THE Migration_Command SHALL preserve the local disk and SHALL NOT delete any local file or any S3 object.
7. IF the AWS CLI is unavailable or the S3 bucket is unreachable WHEN the migration procedure runs, THEN THE migration procedure SHALL abort without modifying any local file or any S3 object and SHALL report an error indicating that the migration could not complete.
8. IF the S3 bucket is unreachable WHEN the Migration_Command runs, THEN THE Migration_Command SHALL report an error indicating that the S3 object count could not be retrieved and SHALL NOT report a drift value.
9. WHEN the Migration_Command runs and the local file count equals zero, THE Migration_Command SHALL report a success result with a drift value of zero.

### Requirement 9: Optional temporary dual-write

**User Story:** As a DevOps operator, I want an optional dual-write window during migration, so that uploads made while syncing are not lost.

#### Acceptance Criteria

1. WHERE `FILE_STORAGE_DUAL_WRITE` is enabled, WHEN an upload is persisted, THE Storage_Service SHALL write the object to both the local disk and S3 under the identical relative key.
2. WHERE `FILE_STORAGE_DUAL_WRITE` is disabled, WHEN an upload is persisted, THE Storage_Service SHALL write the object only to the Active_Driver.
3. WHERE `FILE_STORAGE_DUAL_WRITE` is enabled, IF either the local-disk write or the S3 write fails, THEN THE Storage_Service SHALL report the persist operation as failed to the caller with an error indication and SHALL NOT record the upload as successfully persisted.
4. IF `FILE_STORAGE_DUAL_WRITE` is not set, THEN THE Storage_Service SHALL treat dual-write as disabled and write the object only to the Active_Driver.

### Requirement 10: Security posture

**User Story:** As a security owner, I want private storage, short-lived URLs, and credential-free code, so that sensitive documents are protected and no secrets live in the repository.

#### Acceptance Criteria

1. THE S3_Driver SHALL construct its S3 client without literal credentials, so that credentials are resolved from the Instance_Profile metadata endpoint.
2. THE configuration and repository SHALL contain no S3 access key or secret key value.
3. WHEN the S3_Driver stores an object, THE S3_Driver SHALL request server-side encryption at rest.
4. WHERE the Active_Driver is the S3_Driver, THE S3_Driver SHALL serve objects only via Presigned_URLs generated at render time and SHALL NOT persist those URLs.
5. THE Presigned_URL default time-to-live SHALL be 300 seconds.
6. IF the S3 client cannot resolve credentials from the Instance_Profile, THEN THE S3_Driver SHALL fail the operation with an error indication and SHALL NOT store the object unencrypted or on a publicly readable path.
7. IF server-side encryption cannot be applied when storing an object, THEN THE S3_Driver SHALL treat the `put` as failed and SHALL return false.
8. WHEN a Presigned_URL is accessed after its time-to-live has elapsed, THE S3_Driver SHALL cause the access to be denied.

### Requirement 11: Out-of-scope non-goals

**User Story:** As a project stakeholder, I want the boundaries of this feature stated explicitly, so that related infrastructure work is not assumed to be included.

#### Acceptance Criteria

1. THE feature SHALL exclude deep GroceryCrud Enterprise S3 integration, and WHEN a GroceryCrud Enterprise upload occurs, THE system SHALL write the uploaded file to local storage rather than directly to S3, until GroceryCrud is retired.
2. WHILE GroceryCrud is not yet retired, THE system SHALL synchronize locally stored GroceryCrud Enterprise uploads to S3 on a fixed recurring schedule with a maximum interval of 60 minutes between successive synchronization runs.
3. THE feature SHALL exclude session externalization to Redis, and THE system SHALL continue to store session state using the pre-existing (non-Redis) session mechanism.
4. THE feature SHALL exclude CloudFront and Origin Access Control configuration, and THE feature SHALL NOT create, modify, or require any CloudFront distribution or Origin Access Control setting.
5. THE feature SHALL exclude autoscaling, EC2 compute migration, and Terraform provisioning, and THE feature SHALL NOT create, modify, or require any autoscaling group, EC2 compute migration step, or Terraform-managed resource.

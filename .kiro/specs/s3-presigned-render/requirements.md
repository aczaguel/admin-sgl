# Requirements Document

## Introduction

This feature completes the S3 file-storage migration for the READ/RENDER path of the CodeIgniter 4 application (admin-sgl). The sibling spec `s3-file-storage` introduced a storage abstraction (`FileStorage` contract, `FileStorageService` via `service('fileStorage')`, `S3FileStorage` and `LocalFileStorage` drivers) and the `file_url(storedValue, category, id, ttl)` helper, and migrated the WRITE/upload path. Several READ/render call sites were not migrated: they still build local URLs by hand (`base_url('/assets/uploads/<category>/<id>/<file>')`) or emit a `fileBaseUrl` string that the frontend concatenates with a filename. Both patterns break when `FILE_STORAGE_DRIVER=s3`, because objects no longer live on the local disk and a presigned URL is per-object, signed, and expiring — a "base" URL cannot be presigned and appended to.

This feature routes every rendered file through the existing `file_url()` helper (which returns a presigned URL under `s3` and a `base_url()` path under `local`), replaces the `fileBaseUrl` contract with a per-file resolved URL map/list emitted by controllers and consumed by views/JS, introduces the thin helpers `file_url_map()`, `file_url_list()`, and `is_image_filename()`, and makes the local-disk existence gate driver-aware. Stored database values remain the source of truth and are never modified.

These requirements are derived from the approved design document (`design.md`) and are constrained to be behavior-identical under `FILE_STORAGE_DRIVER=local`. In scope: `Tramitesn.php` (prototype step forms 1–5), `Concluido.php` (`getPagoGestorFiles`/`getPagoDerechosFiles`/`getCobroClienteFiles`), `Tramites.php` (`getCobroClienteFiles`), `ClienteTramites.php` (`resolveDocUrl` for `documentostatus`/`docstatus`), `CobranzaDashboardService.php`, and the views `tramite_unified/_stepN_row.php`, `extra-pages/tramites_layout_prototipo.php`, and `ui/grocery_timeline.php`. Out of scope: GroceryCrud upload widgets (write path), delete paths, the bucket file sync, and Terraform.

## Glossary

- **URL_Resolver**: The existing `file_url(storedValue, category, id, ttl)` helper that normalizes a stored value to a canonical key and delegates URL generation to the Storage_Service. Returns a Presigned_URL under `s3`, a `base_url()` path under `local`, or an empty string when unresolvable.
- **Storage_Service**: The `FileStorageService` facade resolved via `service('fileStorage')`, delegating to the Active_Driver.
- **Active_Driver**: Whichever storage driver is selected by `FILE_STORAGE_DRIVER` at runtime — Local_Driver (`local`) or S3_Driver (`s3`).
- **Legacy_Normalizer**: The existing `keyFromStored()` routine that converts a stored value (bare filename, relative key, or absolute URL) into a canonical Relative_Key.
- **Map_Helper**: The new `file_url_map(storedValues, category, id, ttl)` helper that resolves a list of stored values into a `{ storedValue => url }` map.
- **List_Helper**: The new `file_url_list(storedValues, category, id, ttl)` helper that resolves a list of stored values into an ordered list of `{ name, url }` entries.
- **Image_Predicate**: The new `is_image_filename(name)` helper that reports whether a filename extension is a browser-renderable image.
- **Render_Site**: Any in-scope controller action, service method, or view that produces a browser URL for a stored file on the read/render path.
- **Files_Endpoint**: Any of the `getFiles` AJAX actions that return a JSON list of file entries — `Concluido.php::getPagoGestorFiles`/`getPagoDerechosFiles`/`getCobroClienteFiles`, and `Tramites.php::getCobroClienteFiles`.
- **Step_Form_Builder**: The `Tramitesn.php` prototype step form logic (steps 1–5) that builds per-document rows for the step views.
- **Doc_Resolver**: The `ClienteTramites.php::resolveDocUrl` routine and the equivalent `documentostatus`/`docstatus` resolution logic in `CobranzaDashboardService.php`.
- **Resolved_Row**: A server-rendered document row enriched with a resolved `url` field and an `is_image` flag, replacing the removed `fileBaseUrl` contract.
- **URL_Map**: An `array<string,string>` keyed by the original stored value, where each value equals the URL_Resolver output for that key.
- **Presigned_URL**: A time-limited S3 GET URL generated at render time under the S3_Driver, valid for a configured time-to-live (TTL).
- **Stored_Value**: The raw database value for a file (bare filename, relative key, or absolute URL); the immutable source of truth.
- **Local_Upload_Path**: A URL or path containing the substring `/assets/uploads/`, characteristic of the legacy hand-built local URL pattern.
- **Existence_Gate**: The filtering step in a Files_Endpoint or Doc_Resolver that decides whether a file row is included based on `file_exists()`/`is_file()` on the local disk.
- **Category**: The storage category for a file, one of `{documentostatus, docstatus, pago_derechos, pago_gestor, cobro_cliente, evidencias}`.

## Requirements

### Requirement 1: Single resolution point for every rendered file

**User Story:** As a developer maintaining views and galleries, I want every rendered file to resolve through one helper, so that the same code works under both `local` and `s3` without per-site URL construction.

#### Acceptance Criteria

1. WHEN a Render_Site produces a browser URL for a Stored_Value, THE Render_Site SHALL obtain that URL as the return value of the URL_Resolver, the Map_Helper, or the List_Helper.
2. THE Render_Site SHALL NOT construct a file URL by concatenating a base URL string with a filename segment.
3. THE Render_Site SHALL NOT emit a `fileBaseUrl` key, or any equivalent base-URL string intended for client-side concatenation with a filename, on any render-path payload.
4. WHEN a view renders a document link or image, THE view SHALL read a URL pre-resolved by its controller from the row `url` or `existing_path` field and SHALL NOT derive the URL from a filename combined with a base path.
5. FOR ALL non-empty browser URLs produced by a Render_Site, THE produced URL SHALL be byte-for-byte equal to the URL_Resolver output for the corresponding Stored_Value, its Category, and its optional id.
6. WHEN a Render_Site resolves a list of Stored_Values through the Map_Helper or the List_Helper, THE resolved URL for each Stored_Value SHALL be byte-for-byte equal to the URL_Resolver output for that Stored_Value, its Category, and its optional id.

### Requirement 2: No local upload path under the S3 driver

**User Story:** As a platform operator, I want no rendered URL to point at the local `/assets/uploads/` route when running on S3, so that documents resolve from the bucket instead of returning 404.

#### Acceptance Criteria

1. WHILE the Active_Driver is the S3_Driver, WHEN a Render_Site resolves a Stored_Value to a non-empty URL, THE resolved URL SHALL NOT contain the substring `/assets/uploads/`.
2. WHILE the Active_Driver is the S3_Driver, WHEN a Files_Endpoint returns a file entry for a non-empty Stored_Value, THE `existing_path` field SHALL be a Presigned_URL.
3. WHILE the Active_Driver is the S3_Driver, WHEN a Files_Endpoint returns a file entry for a non-empty Stored_Value, THE `existing_path` field SHALL NOT contain the substring `/assets/uploads/`.
4. WHILE the Active_Driver is the S3_Driver, WHEN a Doc_Resolver resolves a non-empty `documentostatus` value to a non-empty URL, THE resolved URL SHALL be a Presigned_URL for the canonical `documentostatus/<basename>` key.
5. WHILE the Active_Driver is the S3_Driver, WHEN a Doc_Resolver resolves a non-empty `docstatus` value to a non-empty URL, THE resolved URL SHALL be a Presigned_URL for the canonical `documentostatus/<basename>` key.

### Requirement 3: Behavior-identical output under the local driver

**User Story:** As a release manager, I want the render path to produce identical output under `local` before S3 is enabled, so that the change is safe to deploy without behavior drift.

#### Acceptance Criteria

1. WHILE the Active_Driver is the Local_Driver, WHEN a Render_Site resolves a non-empty, normalizable Stored_Value, THE resolved URL SHALL equal, character-for-character, the string `base_url('/assets/uploads/' + keyFromStored(storedValue, category, id))` produced by the pre-change code, including identical per-segment percent-encoding of the normalized key.
2. WHILE the Active_Driver is the Local_Driver, WHEN a Render_Site resolves a Stored_Value that is empty, whitespace-only, or non-normalizable, THE resolved URL SHALL equal the value produced by the pre-change code for that same input.
3. WHILE the Active_Driver is the Local_Driver, WHEN a Files_Endpoint builds a file entry, THE Files_Endpoint SHALL apply the Existence_Gate using `file_exists()` such that the row is included or excluded identically to the pre-change code for the same input row.
4. WHILE the Active_Driver is the Local_Driver, WHEN a Files_Endpoint builds a file entry for a file present on the local disk, THE Files_Endpoint SHALL include a `size` field whose value equals the `filesize()` result produced by the pre-change code for that file.
5. WHILE the Active_Driver is the Local_Driver, WHEN a Doc_Resolver resolves a Stored_Value, THE Doc_Resolver SHALL return a result identical to the pre-change code's result for the same input — the same non-empty URL string when the pre-change code returns a URL, and null when the pre-change code returns null.
6. WHILE the Active_Driver is the Local_Driver, WHEN a Doc_Resolver probes for a file, THE Doc_Resolver SHALL evaluate the same set of candidate directories as the pre-change code.

### Requirement 4: Elimination of the `fileBaseUrl` contract

**User Story:** As a frontend maintainer, I want controllers to emit per-file resolved URLs instead of a base URL, so that views never concatenate a base with a filename.

#### Acceptance Criteria

1. THE Step_Form_Builder SHALL NOT emit a `fileBaseUrl` key on any step form payload for steps 1 through 5.
2. WHEN the Step_Form_Builder builds a document row for steps 2 through 5 whose filename is non-empty, THE Step_Form_Builder SHALL enrich the row with a `url` field equal to the URL_Resolver output for the row filename, its Category, and its id.
3. WHEN the Step_Form_Builder builds a document row whose filename is non-empty, THE Step_Form_Builder SHALL enrich the row with an `is_image` field equal to the Image_Predicate output for the row filename.
4. WHEN the Step_Form_Builder builds a document row whose filename is empty or composed solely of whitespace characters, THE Step_Form_Builder SHALL set the row `url` field to an empty string.
5. WHEN the Step_Form_Builder builds a document row whose filename is empty or composed solely of whitespace characters, THE Step_Form_Builder SHALL set the row `is_image` field to false.
6. WHEN a step view renders a document link whose row `url` field is non-empty, THE step view SHALL set the link target to that `url` value.
7. IF a step view renders a document link whose row `url` field is empty, THEN THE step view SHALL set the link target to the literal string `#`.

### Requirement 5: Per-file URL map resolution

**User Story:** As a developer, I want a helper that resolves a list of stored values into a keyed lookup, so that a view can look up each file's URL by the filename it already renders.

#### Acceptance Criteria

1. WHEN the Map_Helper receives a list of Stored_Values with a Category, an optional id, and an optional ttl, THE Map_Helper SHALL return a URL_Map in which every distinct non-empty Stored_Value is a key mapped to the URL_Resolver output for that value, Category, id, and ttl.
2. WHEN the Map_Helper receives a Stored_Value that is empty or whitespace-only, THE Map_Helper SHALL exclude that value from the returned URL_Map.
3. WHEN the Map_Helper receives a list containing duplicate Stored_Values, THE Map_Helper SHALL collapse the duplicates so that the returned URL_Map contains exactly one entry per distinct non-empty Stored_Value.
4. FOR ALL keys in a returned URL_Map, THE mapped value SHALL equal the URL_Resolver output for that key, and no mapped value SHALL be a hand-built Local_Upload_Path.
5. WHEN the Map_Helper receives an empty list of Stored_Values, THE Map_Helper SHALL return an empty URL_Map.
6. WHEN the Map_Helper receives a non-empty Stored_Value that the URL_Resolver resolves to an empty string, THE Map_Helper SHALL retain that Stored_Value as a key mapped to an empty string rather than excluding it.

### Requirement 6: Ordered list resolution

**User Story:** As a developer building galleries, I want a helper that resolves stored values into an ordered list, so that files render in their original order.

#### Acceptance Criteria

1. WHEN the List_Helper receives a list of Stored_Values with a Category, an optional id, and an optional TTL, THE List_Helper SHALL return, for each non-empty Stored_Value, exactly one entry whose `name` field equals that Stored_Value.
2. WHEN the List_Helper builds an entry for a non-empty Stored_Value, THE List_Helper SHALL set the entry `url` field to the URL_Resolver output for that Stored_Value, Category, id, and TTL.
3. WHEN the List_Helper receives a Stored_Value that is empty or whitespace-only, THE List_Helper SHALL exclude that value from the returned list.
4. FOR ALL pairs of non-empty Stored_Values at input positions i and j where i precedes j, THE List_Helper SHALL place the entry for the value at position i before the entry for the value at position j in the returned list.
5. WHEN the List_Helper receives a list containing duplicate non-empty Stored_Values, THE List_Helper SHALL retain one entry per occurrence, preserving the input order of those occurrences.
6. FOR ALL entries in the returned list, THE `url` field SHALL equal the URL_Resolver output for the entry `name`, and no `url` field SHALL be a hand-built Local_Upload_Path.

### Requirement 7: Image detection

**User Story:** As a developer rendering galleries, I want a shared predicate for image filenames, so that image-versus-icon decisions are consistent across step forms and Files_Endpoints.

#### Acceptance Criteria

1. WHEN the Image_Predicate receives a filename whose extension — defined as the substring following the final `.` character in the filename — is one of `png`, `jpg`, `jpeg`, `gif`, `webp`, `bmp`, or `svg`, THE Image_Predicate SHALL return true.
2. WHEN the Image_Predicate receives a filename whose extension is not one of the seven extensions listed in criterion 1, THE Image_Predicate SHALL return false.
3. WHEN the Image_Predicate receives a filename that contains no `.` character, THE Image_Predicate SHALL return false.
4. WHEN the Image_Predicate receives a filename that is empty or whitespace-only, THE Image_Predicate SHALL return false.
5. WHEN the Image_Predicate receives a filename whose only `.` character is its first character, THE Image_Predicate SHALL return false.
6. WHEN the Image_Predicate compares a filename extension against the seven extensions listed in criterion 1, THE Image_Predicate SHALL perform the comparison case-insensitively.

### Requirement 8: Safe degradation for empty or unresolvable values

**User Story:** As a data owner, I want unresolvable legacy values to degrade gracefully, so that a single bad row never breaks a gallery or modifies stored data.

#### Acceptance Criteria

1. WHEN the URL_Resolver receives a Stored_Value that is empty, contains only whitespace characters, or cannot be normalized to a Relative_Key by the Legacy_Normalizer, THE URL_Resolver SHALL return an empty string.
2. IF the URL_Resolver returns an empty string for a Stored_Value that a Render_Site is rendering, THEN THE Render_Site SHALL render every other file row or gallery item in the same view whose Stored_Value resolves to a non-empty URL, without omitting or corrupting those items.
3. IF the URL_Resolver returns an empty string for a Stored_Value that a Render_Site is rendering, THEN THE Render_Site SHALL complete rendering the view without raising an uncaught exception and without terminating the request.
4. WHEN a Render_Site resolves any Stored_Value, THE Render_Site SHALL NOT issue any insert, update, or delete operation against the originating database row.
5. WHEN a server-rendered view receives an empty resolved URL for a document, THE view SHALL set the document link target to the literal string `#`.

### Requirement 9: Side-effect-free, expiring presigned URLs

**User Story:** As a performance and security owner, I want presigned URLs generated locally and short-lived, so that gallery rendering is cheap and shared links expire.

#### Acceptance Criteria

1. WHILE the Active_Driver is the S3_Driver, WHEN a Render_Site resolves a Stored_Value through the URL_Resolver, THE resolution SHALL generate the Presigned_URL by local signing only and SHALL perform no network request to S3.
2. WHILE the Active_Driver is the S3_Driver, WHEN a Render_Site resolves a Stored_Value, THE resolution SHALL perform no write to the database, the local filesystem, or the S3 bucket.
3. WHILE the Active_Driver is the S3_Driver, WHEN the URL_Resolver produces a Presigned_URL, THE Presigned_URL SHALL be signed with a time-to-live equal to the configured presigned TTL (default 300 seconds) and SHALL grant HTTP GET access to the referenced S3 object while the current time is at or before the signing time plus that time-to-live.
4. IF the configured time-to-live of a Presigned_URL has elapsed, THEN THE Presigned_URL SHALL cause any subsequent HTTP GET request that uses it to be denied.
5. THE Render_Site SHALL NOT persist any resolved URL to the database.
6. WHEN a Files_Endpoint is invoked, THE Files_Endpoint SHALL re-resolve every returned URL on that invocation rather than returning a previously stored or cached URL.

### Requirement 10: Driver-aware existence gate

**User Story:** As a platform operator, I want the local-disk existence check applied only under the local driver, so that migrated files render on S3 instead of being filtered out.

#### Acceptance Criteria

1. WHILE the Active_Driver is the Local_Driver, WHEN a Files_Endpoint processes a file row, THE Files_Endpoint SHALL apply the Existence_Gate using `file_exists()` on the local path resolved from the row Stored_Value.
2. IF the Active_Driver is the Local_Driver and the local file resolved from a row Stored_Value is absent, THEN THE Files_Endpoint SHALL exclude that row from the response.
3. WHILE the Active_Driver is the S3_Driver, WHEN a Files_Endpoint processes a file row for a non-empty Stored_Value, THE Files_Endpoint SHALL include the row without applying any local-disk Existence_Gate.
4. WHILE the Active_Driver is the S3_Driver, WHEN a Files_Endpoint builds a file entry, THE Files_Endpoint SHALL omit the `size` field rather than invoking `filesize()` on the local path.
5. WHILE the Active_Driver is the S3_Driver, WHEN a Doc_Resolver resolves a non-empty value, THE Doc_Resolver SHALL resolve the URL directly through the URL_Resolver without applying any local-disk gate.
6. WHILE the Active_Driver is the Local_Driver, WHEN a Doc_Resolver resolves a value, THE Doc_Resolver SHALL apply the `is_file()` candidate-directory gate.
7. IF the Active_Driver is the Local_Driver and no candidate directory contains the file for a resolved value, THEN THE Doc_Resolver SHALL return null.
8. WHILE the Active_Driver is the S3_Driver, WHEN a Files_Endpoint processes multiple file rows, THE Files_Endpoint SHALL NOT invoke a per-file S3 existence check.

### Requirement 11: `getFiles` AJAX response contract

**User Story:** As a frontend consumer of gallery endpoints, I want the JSON response to keep its existing keys while resolving URLs through the helper, so that existing JS needs no change beyond reading `existing_path`.

#### Acceptance Criteria

1. WHEN a Files_Endpoint returns a file entry, THE entry SHALL contain the `id`, `name`, `existing_path`, `icon`, and the existing type field (for example `cobro_correcto`) with their existing key names.
2. WHEN a Files_Endpoint builds a file entry, THE `existing_path` field SHALL equal the URL_Resolver output for the entry `name`, its Category, and its id.
3. WHEN a Files_Endpoint builds a file entry whose `name` yields Image_Predicate true, THE `icon` field SHALL equal the resolved `existing_path` URL.
4. WHEN a Files_Endpoint builds a file entry whose `name` yields Image_Predicate false, THE `icon` field SHALL equal the existing static icon path for that file type.
5. WHEN a Files_Endpoint is invoked, THE Files_Endpoint SHALL apply its existing access-control guards unchanged.
6. WHEN a Files_Endpoint processes a file row whose Stored_Value is empty or whitespace-only, THE Files_Endpoint SHALL exclude that row from the response.

### Requirement 12: Path-traversal safety on the render path

**User Story:** As a security-conscious engineer, I want render-path resolution to reject traversal attempts, so that no stored value can escape its intended prefix.

#### Acceptance Criteria

1. IF a Doc_Resolver receives a value whose basename is empty, `.`, or `..`, THEN THE Doc_Resolver SHALL return null.
2. IF a Stored_Value contains a `..` path segment or a byte with character code 0, THEN THE Render_Site SHALL NOT produce a Local_Upload_Path for that value.
3. IF a Stored_Value contains a `..` path segment or a byte with character code 0, THEN THE Render_Site SHALL resolve that value to an empty string.
4. WHEN a Render_Site resolves any Stored_Value, THE resolution SHALL pass through the Legacy_Normalizer so that the `..` path-segment and null-byte defenses are applied.

### Requirement 13: Out-of-scope non-goals

**User Story:** As a project stakeholder, I want the boundaries of this feature stated explicitly, so that adjacent work is not assumed to be included.

#### Acceptance Criteria

1. THE feature SHALL NOT add, remove, or modify GroceryCrud upload widget configuration (`setFieldUpload`/`setFieldUploadMultiple`) at any write-path call site.
2. THE feature SHALL NOT modify any file deletion path.
3. THE feature SHALL NOT modify the `@unlink` delete logic in `TraCobroClienteModel.php`.
4. THE feature SHALL NOT perform, invoke, or require an `aws s3 sync` bucket file synchronization procedure as part of its code changes.
5. THE feature SHALL NOT create, modify, or require any Terraform-managed resource.

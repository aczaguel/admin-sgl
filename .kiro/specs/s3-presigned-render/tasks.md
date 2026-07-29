# Implementation Plan: S3 Presigned Render — Migrate Read/Render Call Sites to `file_url`

## Overview

This plan migrates every read/render call site off the two broken patterns (`base_url('/assets/uploads/...')` hand-built URLs and the `fileBaseUrl + filename` contract) and routes each rendered file through the existing `file_url()` helper. It builds bottom-up: first the three thin helpers (`is_image_filename`, `file_url_map`, `file_url_list`) on top of the existing `file_url()`/`keyFromStored()`, then the `getFiles` AJAX endpoints and `Doc_Resolver`s with a driver-aware existence gate, then the `Tramitesn` step-form builders, and finally the views/JS that consume the per-file URLs — ending by wiring integration tests across both drivers. No code reimplements resolution; everything delegates to the already-shipped `s3-file-storage` abstraction.

Every step is behavior-identical under `FILE_STORAGE_DRIVER=local` (the presign helper returns the same `base_url('/assets/uploads/...')` string these sites build today), so nothing observable changes for the running app until the operator flips the flag to `s3`.

The stack is PHP 8.2 / CodeIgniter 4.0.4. Tests extend `CIUnitTestCase` under `tests/app/...` and run via PHPUnit **inside** the `admin-sgl-app` Docker container (host PHP is broken; there is no root `phpunit.xml` and `./vendor/bin/phpunit` is not directly executable):

```
docker exec admin-sgl-app php vendor/bin/phpunit --bootstrap system/Test/bootstrap.php --filter <TestClass> tests
```

`giorgiosironi/eris` is **not** installed, so — per the design's fallback — property-based tests use PHPUnit **data-provider fuzz harnesses** that sweep the same value classes (bare filenames, `category/id/file` relative keys, absolute `base_url` URLs, empty/whitespace, adversarial `..`/null-byte). The `fileStorage` service is exercised with a recording/fake double via `Services::injectMock('fileStorage', ...)` (matching the sibling spec's convention); the S3 driver is exercised through the AWS SDK `MockHandler` — **no live AWS calls** in any test. Each of the design's 9 correctness properties is encoded as its own property-test sub-task.

Out of scope (no tasks): GroceryCrud upload widgets (`setFieldUpload`/`setFieldUploadMultiple`), file deletion paths (including `TraCobroClienteModel::@unlink`), the `aws s3 sync` bucket file migration, and Terraform (Requirement 13).

## Tasks

- [x] 1. Add render-path helpers on top of `file_url()`
  - [x] 1.1 Implement `is_image_filename()` in `app/Helpers/filestorage_helper.php`
    - Add `is_image_filename(string $name): bool` returning true only when the substring after the final `.` is one of `png|jpe?g|gif|webp|bmp|svg`, matched case-insensitively; trim input first; return false for empty/whitespace-only, no `.`, or a leading-`.`-only name
    - Guard with `if (!function_exists('is_image_filename'))` to match the existing helper style
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

  - [x] 1.2 Implement `file_url_map()` and `file_url_list()` in `app/Helpers/filestorage_helper.php`
    - `file_url_map(array $storedValues, string $category='', ?int $id=null, int $ttl=300): array` — trim each value, skip empty/whitespace, collapse duplicate keys, map each distinct value to `file_url($name, $category, $id, $ttl)`; retain a key whose resolved URL is `''`
    - `file_url_list(array $storedValues, string $category='', ?int $id=null, int $ttl=300): array` — return ordered `['name'=>value,'url'=>file_url(...)]` entries for every non-empty value, preserving input order and one entry per occurrence (duplicates kept)
    - Both delegate solely to `file_url()` (no new resolution logic); guard each with `if (!function_exists(...))`
    - _Requirements: 1.1, 1.6, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [x] 1.3 Write unit tests for `is_image_filename()`
    - `tests/app/Helpers/IsImageFilenameTest.php`, data-provider extension matrix: png/jpg/jpeg/gif/webp/bmp/svg → true; pdf/xml/doc/txt/no-extension/`.hidden`/empty/whitespace → false; assert case-insensitivity (`.JPG`, `.PnG`)
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

  - [x] 1.4 Write property test for the map helper
    - `tests/app/Helpers/FileUrlMapPropertyTest.php`, data-provider fuzz harness over generated stored-value lists (with duplicates and blanks); inject a recording `fileStorage` double via `Services::injectMock`
    - **Property 5: Map cardinality and fidelity**
    - **Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6**

  - [x] 1.5 Write property test for the list helper's order preservation
    - `tests/app/Helpers/FileUrlListPropertyTest.php`, fuzz harness asserting for all i<j with non-empty values, the entry for position i precedes position j, empties excluded, duplicates retained one-per-occurrence, and each `url` equals `file_url(name,...)`
    - **Property 9: Order preservation for lists**
    - **Validates: Requirements 6.1, 6.4, 6.5, 6.6**

  - [x] 1.6 Write property test for safe degradation of empty/unresolvable values
    - `tests/app/Helpers/SafeDegradationPropertyTest.php`, fuzz harness feeding empty, whitespace-only, `..`-segment, and null-byte values through `file_url`/`file_url_map`/`file_url_list`; assert result is `''` (or excluded), no exception propagates, and the double records no DB/store mutation
    - **Property 6: Empty/unresolvable degrade safely**
    - **Validates: Requirements 8.1, 8.2, 8.3, 8.4, 12.3, 12.4**

- [x] 2. Checkpoint - helpers complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Migrate the `getFiles` AJAX endpoints (driver-aware existence gate)
  - [x] 3.1 Migrate the three `getFiles` endpoints in `app/Controllers/Deskapp/Concluido.php`
    - In `getPagoGestorFiles`/`getPagoDerechosFiles`/`getCobroClienteFiles`: keep ACL guards unchanged; skip empty/whitespace `file` rows; under `local` keep the `file_exists(FCPATH...)` gate and `filesize()` for `size`; under `s3` skip the local-disk gate, omit `size`, and never call a per-file S3 `exists()`; set `existing_path = file_url($name, $category, $id)` and `icon = is_image_filename($name) ? existing_path : <static icon path>`; preserve all existing keys (`id`, `name`, type field such as `cobro_correcto`)
    - Read `config('FileStorage')->driver` once per call; do not modify stored DB values
    - _Requirements: 1.1, 1.5, 2.1, 2.2, 2.3, 9.6, 10.1, 10.2, 10.3, 10.4, 10.8, 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

  - [x] 3.2 Migrate `getCobroClienteFiles` in `app/Controllers/Deskapp/Tramites.php`
    - Apply the identical driver-aware pattern from 3.1 (ACL unchanged, driver-aware gate, `existing_path`/`icon` via `file_url`/`is_image_filename`, `size` omitted under s3, empty rows skipped)
    - _Requirements: 1.1, 1.5, 2.1, 2.2, 2.3, 9.6, 10.1, 10.2, 10.3, 10.4, 10.8, 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

  - [x] 3.3 Write unit tests for the driver-aware gate and JSON contract
    - `tests/app/Controllers/Deskapp/GetFilesEndpointTest.php`: with a mocked `Config\FileStorage->driver`, assert `local` applies the `file_exists` gate and includes `size`, `s3` skips the gate and omits `size`; assert response keys (`id`, `name`, `existing_path`, `icon`, type field) are preserved and empty rows excluded
    - _Requirements: 3.3, 3.4, 10.1, 10.2, 10.3, 10.4, 11.1, 11.6_

  - [x] 3.4 Write property test for the driver-correct existence gate
    - `tests/app/Controllers/Deskapp/ExistenceGatePropertyTest.php`, fuzz harness over present/absent file rows under both drivers
    - **Property 8: Existence gate is driver-correct**
    - **Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7, 10.8**

  - [x] 3.5 Write property test for no local upload path under s3
    - `tests/app/Controllers/Deskapp/NoLocalPathUnderS3PropertyTest.php`, fuzz harness with a fake s3 driver returning `https://bucket/<key>?sig`; assert no `existing_path`/`icon`/resolved URL contains `/assets/uploads/`
    - **Property 1: No local upload path under s3**
    - **Validates: Requirements 2.1, 2.2, 2.3**

  - [x] 3.6 Write property test that every rendered file resolves through `file_url`
    - `tests/app/Controllers/Deskapp/ResolvesThroughFileUrlPropertyTest.php`, fuzz harness asserting each produced `existing_path` equals `file_url(name, category, id)` for the row's category/id (recording double)
    - **Property 2: Every rendered file resolves through `file_url`**
    - **Validates: Requirements 1.1, 1.5, 1.6, 11.2**

- [x] 4. Migrate the `Doc_Resolver`s (driver-aware, traversal-safe)
  - [x] 4.1 Migrate `resolveDocUrl` in `app/Controllers/Deskapp/ClienteTramites.php`
    - Compute `basename()`; return null for empty/`.`/`..`/null-byte/`..`-segment basenames; under `local` keep today's candidate-directory `is_file()` probing over `[documentostatus, docstatus]` and return `file_url($base, $cand->category)` (byte-identical), else null; under `s3` resolve directly via `file_url($base, 'documentostatus')` with no local gate
    - _Requirements: 1.1, 2.4, 2.5, 3.5, 3.6, 10.5, 10.6, 10.7, 12.1, 12.2, 12.3, 12.4_

  - [x] 4.2 Migrate the `documentostatus`/`docstatus` resolution in `app/Services/CobranzaDashboardService.php`
    - Apply the same driver-aware resolution as 4.1 (local candidate probing preserved; s3 resolves canonical `documentostatus/<basename>` via `file_url`); do not emit `/assets/uploads/...` under s3; leave stored values unmodified
    - _Requirements: 1.1, 2.4, 2.5, 3.5, 3.6, 10.5, 10.6, 10.7, 12.1, 12.2, 12.3, 12.4_

  - [x] 4.3 Write unit tests for the doc resolvers under both drivers
    - `tests/app/Controllers/Deskapp/DocResolverTest.php`: assert local returns today's URL/null for present/absent candidates and evaluates the same candidate set; assert s3 returns a presigned canonical `documentostatus/<base>` URL with no local gate; assert `..`/null-byte/`.`/empty basenames return null
    - _Requirements: 2.4, 2.5, 3.5, 3.6, 10.5, 10.6, 10.7, 12.1, 12.2, 12.3_

  - [x] 4.4 Write property test for byte-identical local output
    - `tests/app/Helpers/LocalByteIdenticalPropertyTest.php`, fuzz harness using the real `LocalFileStorage::url`; assert `file_url(v, cat, id) == base_url('/assets/uploads/'.keyFromStored(v,cat,id))` (per-segment encoding) for every generated `v`, and that empty/unresolvable inputs match pre-change output
    - **Property 3: Local output is byte-identical to today**
    - **Validates: Requirements 3.1, 3.2, 3.5**

- [x] 5. Checkpoint - endpoints and resolvers complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Migrate the `Tramitesn` step-form builders (eliminate `fileBaseUrl`)
  - [x] 6.1 Enrich step forms 1–5 and drop `fileBaseUrl` in `app/Controllers/Deskapp/Tramitesn.php`
    - For steps 1–5, remove the `fileBaseUrl` key from every prototype step-form payload; for each `*_docs_raw` row, add `url = ($file!=='') ? file_url($file, <category>, <idFor(category)>) : ''` and `is_image = is_image_filename($file)`; pass `documentostatus` with null id and per-id categories (`pago_derechos`/`pago_gestor`/`cobro_cliente`) with the tramite id; keep step1's existing per-doc `file_url` behavior
    - _Requirements: 1.1, 1.3, 1.5, 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 6.2 Write unit tests for the step-form builder enrichment
    - `tests/app/Controllers/Deskapp/StepFormBuilderTest.php`: assert no `fileBaseUrl` key on any step 1–5 payload; assert non-empty rows get `url = file_url(...)` and `is_image` from the predicate; assert empty/whitespace filenames yield `url=''` and `is_image=false`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 6.3 Write property test that `fileBaseUrl` is eliminated
    - `tests/app/Controllers/Deskapp/FileBaseUrlEliminatedPropertyTest.php`, fuzz harness over generated docs_raw sets; assert `fileBaseUrl` never appears in controller output keys and every row `url` equals `file_url(...)` (never a base+filename concatenation)
    - **Property 4: `fileBaseUrl` is eliminated**
    - **Validates: Requirements 1.3, 4.1, 1.2**

- [x] 7. Migrate the views/JS to consume per-file URLs
  - [x] 7.1 Update the step row views `app/Views/deskapp/tramite_unified/_step1_row.php` through `_step5_row.php`
    - Replace every `rtrim($fileBaseUrl,'/').'/'.rawurlencode($docFile)` concatenation with `$docUrl = ($doc['url'] ?? '') !== '' ? $doc['url'] : '#'`; use `$doc['is_image']` for the image-vs-link decision; drop `$docFileBaseUrl` from `_step1_row.php`
    - _Requirements: 1.2, 1.4, 4.6, 4.7, 8.2, 8.3, 8.5_

  - [x] 7.2 Update the mirrored step4/step5 blocks in `app/Views/deskapp/extra-pages/tramites_layout_prototipo.php`
    - Set `data-doc-preview-url` and link targets from the pre-resolved row `url` (fallback `#`); remove any base+filename concatenation and reliance on `fileBaseUrl`
    - _Requirements: 1.2, 1.4, 4.6, 4.7, 8.2, 8.5_

  - [x] 7.3 Update `app/Views/deskapp/ui/grocery_timeline.php`
    - Replace inline `base_url().'/assets/uploads/'.$val_image` + `file_exists()` with `$url = file_url($val_image, '')`; when non-empty render `<img>` (image) or `<a>` (pdf), else render an "unavailable" placeholder; preserve `basename()`/`..` guards; do not gate on local disk under s3
    - _Requirements: 1.1, 1.2, 2.1, 8.2, 8.3, 8.5, 12.2, 12.3, 12.4_

- [x] 8. Presign guarantees and cross-driver integration tests
  - [x] 8.1 Write property test for side-effect-free, expiring presigned URLs
    - `tests/app/Libraries/Storage/PresignSideEffectPropertyTest.php`, AWS SDK `MockHandler`-backed s3 driver; assert resolving N values issues no network request (no handler invocations) and no DB/fs/bucket write, that the URL encodes the configured TTL (default 300s), and that URLs are never persisted
    - **Property 7: Presign is side-effect free and expiring**
    - **Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5, 9.6**

  - [x] 8.2 Write integration tests for the `getFiles` endpoints under both drivers
    - `tests/app/Controllers/Deskapp/GetFilesEndpointIntegrationTest.php`: with the fake s3 driver assert JSON `existing_path` is presigned and no present DB row is dropped; with the local driver assert byte-identical output against a golden snapshot; assert ACL guards still apply
    - _Requirements: 2.2, 2.3, 3.3, 3.4, 9.6, 10.3, 10.4, 11.5_

  - [x] 8.3 Write render integration tests for the step views under both drivers
    - `tests/app/Views/StepRowRenderIntegrationTest.php`: render each `_stepN_row.php` (and mirrored `tramites_layout_prototipo.php` blocks) with a stub form payload; assert links resolve from row `url`, `fileBaseUrl` is absent, empty rows fall back to `#`, and no `/assets/uploads/` appears under s3
    - _Requirements: 1.2, 1.4, 4.6, 4.7, 8.2, 8.3_

- [x] 9. Final checkpoint - render path fully migrated (verified in local mode)
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional test sub-tasks and can be skipped for a faster MVP; core migration tasks are never optional.
- Every step is behavior-identical under `FILE_STORAGE_DRIVER=local`; nothing changes for the running app until the operator flips to `s3` (after the out-of-scope bucket sync completes).
- No task reimplements URL resolution — all new helpers and call sites delegate to the existing `file_url()`/`keyFromStored()` from `s3-file-storage`.
- Property tests use PHPUnit **data-provider fuzz harnesses** (giorgiosironi/eris is not installed); the `fileStorage` service is a recording/fake double via `Services::injectMock`, and the s3 driver uses the AWS SDK `MockHandler` — no live AWS calls.
- Run tests inside the container: `docker exec admin-sgl-app php vendor/bin/phpunit --bootstrap system/Test/bootstrap.php --filter <TestClass> tests`.
- Each property-test sub-task references the exact correctness property from the design and the requirement clauses it validates.
- Requirement 13 (GroceryCrud upload widgets, delete paths, `TraCobroClienteModel::@unlink`, `aws s3 sync`, Terraform) is out of scope and intentionally has no tasks.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2"] },
    { "id": 2, "tasks": ["1.3", "1.4", "1.5", "1.6", "3.1", "3.2", "4.1", "4.2", "6.1"] },
    { "id": 3, "tasks": ["3.3", "3.4", "3.5", "3.6", "4.3", "4.4", "6.2", "6.3", "7.1", "7.2", "7.3", "8.1", "8.2"] },
    { "id": 4, "tasks": ["8.3"] }
  ]
}
```

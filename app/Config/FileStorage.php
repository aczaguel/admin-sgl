<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * FileStorage configuration.
 *
 * Central wiring for the storage abstraction layer. A single driver switch
 * (`driver`) flips the whole application between local-disk and Amazon S3
 * storage with no code or stored-data changes.
 *
 * The properties below are bound to the following environment variables:
 *   - driver     <- FILE_STORAGE_DRIVER      (local | s3)
 *   - bucket     <- S3_BUCKET
 *   - region     <- S3_REGION
 *   - dualWrite  <- FILE_STORAGE_DUAL_WRITE  (true | false)
 *   - presignTtl <- S3_PRESIGN_TTL           (seconds)
 *   - sse        <- S3_SSE                    (AES256 | aws:kms)
 *
 * IMPORTANT (security): there are intentionally NO access-key or secret-key
 * settings here. On S3 the AWS SDK resolves temporary credentials from the
 * EC2 IAM Instance Profile metadata endpoint. No secrets live in config or in
 * the repository.
 */
class FileStorage extends BaseConfig
{
    /**
     * Active storage driver: 'local' or 's3'.
     *
     * Bound to FILE_STORAGE_DRIVER. Defaults to 'local' so behavior is
     * identical to the current system until the operator flips the flag.
     */
    public string $driver = 'local';

    /**
     * Target S3 bucket name (S3 driver only).
     *
     * Bound to S3_BUCKET.
     */
    public string $bucket = '';

    /**
     * AWS region for the S3 client (S3 driver only).
     *
     * Bound to S3_REGION.
     */
    public string $region = 'us-east-1';

    /**
     * Optional temporary dual-write during the migration window.
     *
     * When true, put() writes to both the local disk AND S3 under the
     * identical relative key. Bound to FILE_STORAGE_DUAL_WRITE.
     */
    public bool $dualWrite = false;

    /**
     * Default presigned-URL time-to-live, in seconds (S3 driver only).
     *
     * Bound to S3_PRESIGN_TTL. Defaults to 300 (5 minutes).
     */
    public int $presignTtl = 300;

    /**
     * Root directory for the local driver.
     *
     * Mirrors the current on-disk layout (public/assets/uploads).
     */
    public string $localRoot = FCPATH . 'assets/uploads';

    /**
     * Server-side encryption algorithm for S3 PutObject.
     *
     * 'AES256' (SSE-S3) or 'aws:kms'. Bound to S3_SSE.
     */
    public string $sse = 'AES256';

    public function __construct()
    {
        parent::__construct();

        // Explicit env binding: the design uses descriptive env key names
        // (FILE_STORAGE_DRIVER, S3_BUCKET, ...) that do not match CI4's
        // automatic "<configname>.<property>" convention, so we read them here.
        $driver = trim((string) env('FILE_STORAGE_DRIVER', ''));
        if ($driver !== '') {
            $this->driver = $driver;
        }

        $bucket = trim((string) env('S3_BUCKET', ''));
        if ($bucket !== '') {
            $this->bucket = $bucket;
        }

        $region = trim((string) env('S3_REGION', ''));
        if ($region !== '') {
            $this->region = $region;
        }

        $dualWrite = env('FILE_STORAGE_DUAL_WRITE', null);
        if ($dualWrite !== null && $dualWrite !== '') {
            $this->dualWrite = filter_var($dualWrite, FILTER_VALIDATE_BOOLEAN);
        }

        $presignTtl = env('S3_PRESIGN_TTL', null);
        if ($presignTtl !== null && $presignTtl !== '' && is_numeric($presignTtl)) {
            $this->presignTtl = (int) $presignTtl;
        }

        $sse = trim((string) env('S3_SSE', ''));
        if ($sse !== '') {
            $this->sse = $sse;
        }
    }
}

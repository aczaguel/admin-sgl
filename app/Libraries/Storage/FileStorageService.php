<?php

namespace App\Libraries\Storage;

use Config\FileStorage as FileStorageConfig;

/**
 * FileStorageService — storage facade / driver factory.
 *
 * Resolves the active storage driver from configuration and exposes it to
 * callers through the {@see FileStorage} contract. Registered as a shared
 * service in Config\Services, so application code obtains a single instance
 * per request via service('fileStorage').
 *
 * Driver selection (Requirements 1.2, 1.3, 1.6):
 *   - driver === 's3'    => S3FileStorage
 *   - driver === 'local' => LocalFileStorage
 *   - unset / empty / unrecognized => LocalFileStorage, and the fallback is
 *     logged. Exactly 'local' and 's3' are recognized; any other value falls
 *     back to the local driver with a warning.
 *
 * Because the driver is selected purely from the FILE_STORAGE_DRIVER flag and
 * the database stores relative keys (not URLs), switching between 'local' and
 * 's3' requires no application-code change and no stored-data change
 * (Requirement 1.7).
 *
 * Optional dual-write (Requirement 9):
 *   During the migration window an operator may enable FILE_STORAGE_DUAL_WRITE.
 *   When enabled, put() persists the object to BOTH the local disk AND S3 under
 *   the identical relative key (Req 9.1), and reports the persist as FAILED if
 *   EITHER write fails (Req 9.3). When disabled or unset, put() writes only to
 *   the active driver (Req 9.2, 9.4). delete()/url()/exists() ALWAYS delegate to
 *   the active driver only — dual-write governs writes exclusively.
 *
 * @see .kiro/specs/s3-file-storage/design.md ("Component 2: FileStorageService")
 */
final class FileStorageService implements FileStorage
{
    /**
     * The resolved storage configuration.
     */
    private FileStorageConfig $config;

    /**
     * The active driver selected from configuration. Governs delete/url/exists
     * and single-store put() when dual-write is off.
     */
    private FileStorage $driver;

    /**
     * Whether optional dual-write is enabled (Req 9.1).
     */
    private bool $dualWrite;

    /**
     * Local-disk driver used by dual-write. Null when dual-write is off.
     */
    private ?FileStorage $localDriver = null;

    /**
     * S3 driver used by dual-write. Null when dual-write is off.
     */
    private ?FileStorage $s3Driver = null;

    /**
     * Optional injected local driver (testing seam — see class docblock note
     * on the injection mechanism below).
     */
    private ?FileStorage $injectedLocal;

    /**
     * Optional injected S3 driver (testing seam).
     */
    private ?FileStorage $injectedS3;

    /**
     * @param FileStorageConfig $config      Resolved storage configuration.
     * @param FileStorage|null  $localDriver Optional pre-built local driver. When
     *                                       provided it is used wherever a local
     *                                       driver is needed (active driver and/or
     *                                       dual-write local leg). Lets tests inject
     *                                       a driver that forces a failure without
     *                                       touching the real filesystem.
     * @param FileStorage|null  $s3Driver    Optional pre-built S3 driver, same
     *                                       semantics for the S3 leg. Lets tests
     *                                       inject a driver whose put() fails.
     */
    public function __construct(
        FileStorageConfig $config,
        ?FileStorage $localDriver = null,
        ?FileStorage $s3Driver = null
    ) {
        $this->config        = $config;
        $this->injectedLocal = $localDriver;
        $this->injectedS3    = $s3Driver;

        $activeName   = $this->resolveActiveDriverName($config);
        $this->driver = $activeName === 's3'
            ? $this->makeS3Driver()
            : $this->makeLocalDriver();

        $this->dualWrite = $config->dualWrite === true;

        if ($this->dualWrite) {
            // Both stores are required. Reuse the active instance for its own
            // store so the SDK client / local root is built once, and build the
            // secondary driver for the other store (Req 9.1).
            $this->localDriver = $activeName === 'local' ? $this->driver : $this->makeLocalDriver();
            $this->s3Driver    = $activeName === 's3' ? $this->driver : $this->makeS3Driver();
        }
    }

    /**
     * Resolve the active driver NAME ('local' or 's3') from the configured
     * FILE_STORAGE_DRIVER value.
     *
     * Recognizes exactly 'local' and 's3'. Any unset, empty, or unrecognized
     * value falls back to the local driver and logs the fallback so the whole
     * application never fails closed when the flag is misconfigured
     * (Requirement 1.6).
     */
    private function resolveActiveDriverName(FileStorageConfig $config): string
    {
        $driver = strtolower(trim($config->driver));

        switch ($driver) {
            case 's3':
                return 's3';

            case 'local':
                return 'local';

            default:
                log_message(
                    'warning',
                    'FileStorageService: FILE_STORAGE_DRIVER value ' .
                    ($config->driver === '' ? '(unset/empty)' : '"' . $config->driver . '"') .
                    ' is unset or unrecognized; falling back to the local driver.'
                );

                return 'local';
        }
    }

    /**
     * Build (or reuse an injected) local-disk driver.
     *
     * Testing seam: when a local driver was injected via the constructor it is
     * returned as-is, letting tests force a local-write outcome.
     */
    private function makeLocalDriver(): FileStorage
    {
        return $this->injectedLocal ?? new LocalFileStorage($this->config);
    }

    /**
     * Build (or reuse an injected) S3 driver.
     *
     * Testing seam: when an S3 driver was injected via the constructor it is
     * returned as-is, letting tests force an S3-write outcome.
     */
    private function makeS3Driver(): FileStorage
    {
        return $this->injectedS3 ?? new S3FileStorage($this->config);
    }

    /**
     * {@inheritDoc}
     *
     * With dual-write ENABLED, persist to both stores under the identical key
     * and return true only if BOTH succeed; otherwise return false and log
     * which leg failed (Req 9.1, 9.3).
     *
     * Ordering rationale: the S3 leg reads the temp file in place (PutObject
     * with SourceFile), whereas the local leg CONSUMES the temp file (it moves
     * it via move_uploaded_file()/rename(), or copy()+unlink()). The S3 write
     * therefore runs FIRST so both stores can read the same source; if local
     * ran first it would move the file out from under the S3 read.
     *
     * With dual-write DISABLED or unset, write only to the active driver
     * (Req 9.2, 9.4).
     */
    public function put(string $key, string $localTmpPath): bool
    {
        if (!$this->dualWrite) {
            return $this->driver->put($key, $localTmpPath);
        }

        // S3 first (non-destructive read of the temp file), then local (which
        // consumes the temp file). Attempt both legs so the object can land in
        // both stores; aggregate the outcome.
        $s3Ok = $this->s3Driver->put($key, $localTmpPath);
        if (!$s3Ok) {
            log_message('error', 'FileStorageService dual-write: S3 put failed for key: ' . $key);
        }

        $localOk = $this->localDriver->put($key, $localTmpPath);
        if (!$localOk) {
            log_message('error', 'FileStorageService dual-write: local put failed for key: ' . $key);
        }

        // Report failure if EITHER write fails (Req 9.3).
        return $s3Ok && $localOk;
    }

    /**
     * {@inheritDoc}
     *
     * Always delegates to the active driver only; dual-write governs writes
     * exclusively.
     */
    public function delete(string $key): bool
    {
        return $this->driver->delete($key);
    }

    /**
     * {@inheritDoc}
     *
     * Always delegates to the active driver only.
     */
    public function url(string $key, int $ttlSeconds = 300): string
    {
        return $this->driver->url($key, $ttlSeconds);
    }

    /**
     * {@inheritDoc}
     *
     * Always delegates to the active driver only.
     */
    public function downloadUrl(string $key, int $ttlSeconds = 300, string $downloadName = ''): string
    {
        return $this->driver->downloadUrl($key, $ttlSeconds, $downloadName);
    }

    /**
     * {@inheritDoc}
     *
     * Always delegates to the active driver only.
     */
    public function exists(string $key): bool
    {
        return $this->driver->exists($key);
    }
}

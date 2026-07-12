<?php

namespace App\Libraries\Storage;

use Config\FileStorage as FileStorageConfig;

/**
 * Local-disk storage driver.
 *
 * Reproduces the current system's exact behavior so that `local` mode is
 * behavior-identical to today: files live under FCPATH.'assets/uploads/'.$key,
 * URLs are base_url('/assets/uploads/'.$key), directories are created with the
 * same permissions the legacy upload code uses (mkdir(..., 0777, true)), and
 * deletes are idempotent.
 *
 * All operations reject traversal-unsafe keys (containing a `..` segment, a
 * leading `/`, or a backslash) BEFORE touching the filesystem. Per the storage
 * contract, driver operations return false (or '' for url()) on a rejected key
 * rather than throwing.
 *
 * @see .kiro/specs/s3-file-storage/design.md ("Component 3: LocalFileStorage")
 */
final class LocalFileStorage implements FileStorage
{
    /**
     * Absolute root directory for local uploads (no trailing slash).
     *
     * Mirrors the current on-disk layout (FCPATH.'assets/uploads').
     */
    private string $root;

    public function __construct(FileStorageConfig $config)
    {
        $this->root = rtrim($config->localRoot, '/\\');
    }

    /**
     * Persist the file at $localTmpPath under $key on the local disk.
     *
     * Creates the target directory recursively (mirroring the legacy
     * mkdir(..., 0777, true)), then moves the source into place. When the
     * source is an HTTP upload, move_uploaded_file() is used; otherwise the
     * file is moved with rename() (falling back to copy()+unlink() across
     * filesystem boundaries). On any failure it returns false and leaves no
     * partially written file visible at the target path.
     */
    public function put(string $key, string $localTmpPath): bool
    {
        if (!$this->assertKey($key)) {
            return false;
        }

        if ($localTmpPath === '' || !is_file($localTmpPath) || !is_readable($localTmpPath)) {
            log_message('error', 'LocalFileStorage put: source temp file missing or unreadable: ' . $localTmpPath);
            return false;
        }

        $target = $this->pathFor($key);
        $dir    = dirname($target);

        // Create the target directory recursively, matching the legacy behavior.
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                log_message('error', 'LocalFileStorage put: could not create target directory: ' . $dir);
                return false;
            }
        }

        // Choose the move strategy based on whether the source is an HTTP upload.
        if (is_uploaded_file($localTmpPath)) {
            if (@move_uploaded_file($localTmpPath, $target)) {
                return true;
            }
            log_message('error', 'LocalFileStorage put: move_uploaded_file failed for key: ' . $key);
            return false;
        }

        // Non-upload source: try an atomic rename first.
        if (@rename($localTmpPath, $target)) {
            return true;
        }

        // rename() can fail across filesystem boundaries; fall back to copy.
        // If the copy fails partway, remove the partial target so no
        // partially written file is left visible.
        if (@copy($localTmpPath, $target)) {
            @unlink($localTmpPath);
            return true;
        }

        if (is_file($target)) {
            @unlink($target);
        }

        log_message('error', 'LocalFileStorage put: rename/copy failed for key: ' . $key);
        return false;
    }

    /**
     * Remove the object at $key. Idempotent: returns true even if the file is
     * already absent, and never touches any path other than $key's.
     */
    public function delete(string $key): bool
    {
        if (!$this->assertKey($key)) {
            return false;
        }

        $target = $this->pathFor($key);
        if (!is_file($target)) {
            return true;
        }

        @unlink($target);

        return true;
    }

    /**
     * Resolve a browser-usable URL for $key, matching the current system:
     * base_url('/assets/uploads/'.$key) with each path segment rawurlencoded.
     * Returns '' for a rejected key.
     */
    public function url(string $key, int $ttlSeconds = 300): string
    {
        if (!$this->assertKey($key)) {
            return '';
        }

        $encoded = implode('/', array_map('rawurlencode', explode('/', $key)));

        return base_url('/assets/uploads/' . $encoded);
    }

    /**
     * True if a regular file exists at $key. Returns false for a rejected key
     * or a key that was never written, without raising an error.
     */
    public function exists(string $key): bool
    {
        if (!$this->assertKey($key)) {
            return false;
        }

        return is_file($this->pathFor($key));
    }

    /**
     * Absolute filesystem path for a (already validated) relative key.
     */
    private function pathFor(string $key): string
    {
        return $this->root . '/' . $key;
    }

    /**
     * Reject traversal-unsafe keys before any filesystem action.
     *
     * A key is rejected when it is empty, contains a `..` segment, begins with
     * a `/`, or contains a backslash (Req 3.4).
     */
    private function assertKey(string $key): bool
    {
        if ($key === '') {
            return false;
        }

        if (strpos($key, '\\') !== false) {
            return false;
        }

        if ($key[0] === '/') {
            return false;
        }

        // Reject any `..` path segment (not merely the substring `..`, which
        // could legitimately appear inside a filename like `a..b`).
        foreach (explode('/', $key) as $segment) {
            if ($segment === '..') {
                return false;
            }
        }

        return true;
    }
}

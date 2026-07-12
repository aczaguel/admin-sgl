<?php

namespace App\Libraries\Storage;

/**
 * FileStorage contract.
 *
 * The single, stable storage contract that all application code depends on.
 * Every driver (local disk, Amazon S3, ...) implements this interface, keeping
 * the application decoupled from FCPATH/base_url() and from the AWS SDK.
 */
interface FileStorage
{
    /**
     * Persist the file at $localTmpPath under $key.
     *
     * @param string $key          Relative key, e.g. "pago_gestor/12472/abc.jpg"
     * @param string $localTmpPath Absolute path to a readable temp file (e.g. $_FILES tmp_name)
     *
     * @return bool true on success
     */
    public function put(string $key, string $localTmpPath): bool;

    /**
     * Remove the object at $key.
     *
     * Idempotent: returns true if the object is already absent.
     */
    public function delete(string $key): bool;

    /**
     * Resolve a browser-usable URL for $key.
     *
     * local: base_url('/assets/uploads/'.$key). s3: presigned GET valid for $ttlSeconds.
     */
    public function url(string $key, int $ttlSeconds = 300): string;

    /**
     * True if an object exists at $key.
     */
    public function exists(string $key): bool;
}

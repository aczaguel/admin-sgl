<?php

namespace App\Libraries\Storage;

use Aws\S3\S3Client;
use Config\FileStorage as FileStorageConfig;

/**
 * Amazon S3 storage driver.
 *
 * Persists uploaded files to a private S3 bucket and serves them via
 * short-lived presigned URLs. Credentials are NEVER supplied literally: the
 * S3 client is built from region only, so the AWS SDK's default provider
 * chain resolves temporary credentials from the EC2 IAM Instance Profile
 * metadata endpoint (design §4.2 / Requirement 10.1). No access key or secret
 * lives in config or the repository.
 *
 * All objects are written with server-side encryption; if encryption cannot
 * be applied the put is treated as failed (Requirement 10.7). Every operation
 * runs the key through {@see self::assertKey()} first, rejecting traversal
 * keys before any S3 action (Requirement 3.4).
 *
 * @see .kiro/specs/s3-file-storage/design.md ("S3 driver — put/url/delete/exists")
 */
final class S3FileStorage implements FileStorage
{
    /**
     * Maximum presigned-URL TTL, in seconds. AWS SigV4 presigned URLs cannot
     * be valid for longer than 7 days (604800 seconds); anything above this is
     * rejected as an out-of-range TTL (Requirement 2.8).
     */
    private const MAX_PRESIGN_TTL = 604800;

    private S3Client $client;

    private string $bucket;

    private string $sse;

    /**
     * @param FileStorageConfig $config Storage config (bucket, region, sse).
     * @param S3Client|null     $client Optional preconstructed client. When null,
     *                                  a client is built from region only (no
     *                                  credentials key) so the SDK resolves
     *                                  Instance Profile credentials. Tests inject
     *                                  a MockHandler-backed client here.
     */
    public function __construct(FileStorageConfig $config, ?S3Client $client = null)
    {
        $this->bucket = $config->bucket;
        $this->sse    = $config->sse;

        // No 'credentials' key: the SDK's default provider chain reads
        // temporary credentials from the EC2 Instance Metadata endpoint
        // (IAM Instance Profile). No keys in config or the repo (Req 10.1).
        $this->client = $client ?? new S3Client([
            'version' => 'latest',
            'region'  => $config->region,
        ]);
    }

    /**
     * Persist the file at $localTmpPath under $key using PutObject with
     * server-side encryption. Returns false on any error, including the case
     * where SSE cannot be applied (Requirement 10.7).
     */
    public function put(string $key, string $localTmpPath): bool
    {
        try {
            $this->assertKey($key);

            $this->client->putObject([
                'Bucket'               => $this->bucket,
                'Key'                  => $key,
                'SourceFile'           => $localTmpPath,
                'ServerSideEncryption' => $this->sse,
            ]);

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'S3 put failed for ' . $key . ': ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Resolve a presigned GetObject URL for $key valid for $ttlSeconds.
     *
     * Returns an error indication (an empty string) — and does NOT produce a
     * presigned URL — when $ttlSeconds is out of range: <= 0 (Requirement 2.7)
     * or > 604800 seconds (Requirement 2.8).
     */
    public function url(string $key, int $ttlSeconds = 300): string
    {
        // Out-of-range TTL: error indication ('') with no presigned URL.
        if ($ttlSeconds <= 0 || $ttlSeconds > self::MAX_PRESIGN_TTL) {
            log_message(
                'error',
                'S3 url rejected: ttl ' . $ttlSeconds . ' out of range (0, ' . self::MAX_PRESIGN_TTL . '] for key ' . $key
            );

            return '';
        }

        try {
            $this->assertKey($key);

            $cmd = $this->client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);

            return (string) $this->client
                ->createPresignedRequest($cmd, '+' . $ttlSeconds . ' seconds')
                ->getUri();
        } catch (\Throwable $e) {
            log_message('error', 'S3 url failed for ' . $key . ': ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Presigned GetObject URL that forces download via
     * ResponseContentDisposition=attachment. Same TTL rules as url().
     */
    public function downloadUrl(string $key, int $ttlSeconds = 300, string $downloadName = ''): string
    {
        if ($ttlSeconds <= 0 || $ttlSeconds > self::MAX_PRESIGN_TTL) {
            log_message(
                'error',
                'S3 downloadUrl rejected: ttl ' . $ttlSeconds . ' out of range (0, ' . self::MAX_PRESIGN_TTL . '] for key ' . $key
            );

            return '';
        }

        try {
            $this->assertKey($key);

            $name = $downloadName !== '' ? $downloadName : basename($key);
            // Sanitize the suggested filename for the header (no quotes/CR/LF).
            $name = preg_replace('/["\r\n]+/', '', $name);

            $cmd = $this->client->getCommand('GetObject', [
                'Bucket'                     => $this->bucket,
                'Key'                        => $key,
                'ResponseContentDisposition' => 'attachment; filename="' . $name . '"',
            ]);

            return (string) $this->client
                ->createPresignedRequest($cmd, '+' . $ttlSeconds . ' seconds')
                ->getUri();
        } catch (\Throwable $e) {
            log_message('error', 'S3 downloadUrl failed for ' . $key . ': ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Delete the object at $key. Idempotent: S3 returns success even when the
     * key is absent, so this returns true whether or not the object existed.
     * Returns false only on an actual error (e.g. permission/network).
     */
    public function delete(string $key): bool
    {
        try {
            $this->assertKey($key);

            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'S3 delete failed for ' . $key . ': ' . $e->getMessage());

            return false;
        }
    }

    /**
     * True if an object exists at $key. Returns false (never raises) for a
     * key that was never written or when the key is invalid.
     */
    public function exists(string $key): bool
    {
        try {
            $this->assertKey($key);

            return $this->client->doesObjectExist($this->bucket, $key);
        } catch (\Throwable $e) {
            log_message('error', 'S3 exists failed for ' . $key . ': ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Reject traversal / malformed keys before any S3 action (Requirement 3.4).
     *
     * A key is rejected when it is empty, contains a `..` segment, has a
     * leading `/`, or contains a backslash.
     *
     * @throws \InvalidArgumentException When the key is unsafe.
     */
    private function assertKey(string $key): void
    {
        if ($key === '') {
            throw new \InvalidArgumentException('S3FileStorage: key must not be empty.');
        }

        if ($key[0] === '/') {
            throw new \InvalidArgumentException('S3FileStorage: key must not start with "/": ' . $key);
        }

        if (strpos($key, '\\') !== false) {
            throw new \InvalidArgumentException('S3FileStorage: key must not contain a backslash: ' . $key);
        }

        // Reject any `..` path segment (not merely the substring, so filenames
        // like "a..b" that contain no traversal segment are still allowed).
        foreach (explode('/', $key) as $segment) {
            if ($segment === '..') {
                throw new \InvalidArgumentException('S3FileStorage: key must not contain a ".." segment: ' . $key);
            }
        }
    }
}

<?php

namespace App\Commands;

use Aws\S3\S3Client;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\FileStorage as FileStorageConfig;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * s3:migrate-check — migration integrity verification command.
 *
 * Automates the read-only count/verify step of the non-destructive migration
 * (design §4.6 / Requirement 8): it counts local files under the uploads root
 * (excluding `*.tmp`) and counts S3 objects in the configured bucket, then
 * reports the drift (drift = localCount - s3Count).
 *
 * Result semantics:
 *   - local count == 0            => SUCCESS, drift 0                (Req 8.9)
 *   - drift <= 0 (local <= s3)    => SUCCESS, every local file has an
 *                                    S3 counterpart                 (Req 8.4)
 *   - drift  > 0 (local  > s3)    => WARNING including the drift value;
 *                                    operator must re-run the sync  (Req 8.5)
 *   - bucket unreachable          => ERROR, and NO drift is reported (Req 8.8)
 *
 * Read-only guarantee: this command NEVER deletes any local or S3 object. It
 * only enumerates the local disk and lists S3 objects (ListObjectsV2), so both
 * stores are untouched (Req 8.6).
 *
 * ── Testing seams (task 11.2 exercises drift<=0, local>s3, zero-local, and
 *    bucket-unreachable WITHOUT real AWS or a real filesystem) ──────────────
 *   1. {@see self::setConfig()} injects a FileStorageConfig (sets localRoot /
 *      bucket / region) so tests point localRoot at a temp dir.
 *   2. {@see self::setS3Client()} injects a preconstructed S3Client (e.g. one
 *      backed by the AWS SDK MockHandler) so countS3Objects() never calls real
 *      AWS. When none is injected, {@see self::s3Client()} builds a
 *      credential-free client (region only) exactly like S3FileStorage.
 *   3. {@see self::countLocalFiles()} and {@see self::countS3Objects()} are
 *      `protected`, so a test subclass may override either to simulate any
 *      local/S3 count — including making countS3Objects() throw to reproduce
 *      an unreachable bucket — with no filesystem or network access.
 *
 * @see .kiro/specs/s3-file-storage/design.md ("Migration integrity check (s3:migrate-check)")
 */
final class S3MigrateCheck extends BaseCommand
{
    protected $group       = 'Storage';
    protected $name        = 's3:migrate-check';
    protected $description = 'Verifica la integridad de la migración a S3: cuenta archivos locales vs objetos en S3 y reporta el drift (solo lectura).';

    protected $usage = 's3:migrate-check';

    /**
     * Optional injected config (testing seam). When null, config('FileStorage')
     * is resolved on demand.
     */
    private ?FileStorageConfig $config = null;

    /**
     * Optional injected S3 client (testing seam). When null, a credential-free
     * client is built on demand from the configured region.
     */
    private ?S3Client $s3Client = null;

    public function run(array $params)
    {
        $config    = $this->getConfig();
        $localRoot = $config->localRoot;

        $localCount = $this->countLocalFiles($localRoot);

        // Count S3 objects. An unreachable bucket (or any listing failure) must
        // produce an ERROR with NO drift reported (Req 8.8).
        try {
            $s3Count = $this->countS3Objects();
        } catch (\Throwable $e) {
            CLI::error('ERROR: no se pudo obtener el conteo de objetos de S3 (bucket inalcanzable).');
            CLI::error('Detalle: ' . $e->getMessage());
            CLI::write('No se reporta drift porque el conteo de S3 no está disponible.');

            return EXIT_ERROR;
        }

        $drift = $localCount - $s3Count;

        CLI::write('Bucket : ' . ($config->bucket !== '' ? $config->bucket : '(no configurado)'));
        CLI::write('Local  : ' . $localCount);
        CLI::write('S3     : ' . $s3Count);
        CLI::write('Drift  : ' . $drift . ' (local - s3)');

        // Local count zero => success with drift 0 (Req 8.9).
        if ($localCount === 0) {
            CLI::write(CLI::color('OK: no hay archivos locales que migrar (drift 0).', 'green'));

            return EXIT_SUCCESS;
        }

        // drift <= 0 => every local file has an S3 counterpart (Req 8.4).
        if ($drift <= 0) {
            CLI::write(CLI::color('OK: cada archivo local tiene su contraparte en S3 (drift <= 0).', 'green'));

            return EXIT_SUCCESS;
        }

        // drift > 0 => local > s3: warn including the drift value (Req 8.5).
        CLI::write(CLI::color(
            'ADVERTENCIA: faltan ' . $drift . ' objeto(s) en S3. Vuelva a ejecutar la sincronización incremental (aws s3 sync, sin --delete).',
            'yellow'
        ));

        return EXIT_SUCCESS;
    }

    /**
     * Inject the storage configuration (testing seam).
     */
    public function setConfig(FileStorageConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Inject a preconstructed S3 client (testing seam). Lets tests supply a
     * MockHandler-backed client so no real AWS call is made.
     */
    public function setS3Client(S3Client $client): self
    {
        $this->s3Client = $client;

        return $this;
    }

    /**
     * Resolve the storage configuration, preferring an injected instance.
     */
    protected function getConfig(): FileStorageConfig
    {
        return $this->config ?? config('FileStorage');
    }

    /**
     * Build (or reuse an injected) S3 client.
     *
     * Mirrors S3FileStorage exactly: NO 'credentials' key is supplied, so the
     * SDK's default provider chain resolves temporary credentials from the EC2
     * IAM Instance Profile metadata endpoint. No secrets in config or the repo.
     */
    protected function s3Client(): S3Client
    {
        if ($this->s3Client !== null) {
            return $this->s3Client;
        }

        $config = $this->getConfig();

        return $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => $config->region,
        ]);
    }

    /**
     * Count regular files under $root recursively, EXCLUDING any file whose
     * name matches `*.tmp` (Req 8.6 — read-only; in-flight temp files are not
     * migration targets). Read-only: it only enumerates the directory tree and
     * never modifies or deletes anything.
     *
     * A non-existent or unreadable root yields 0 (nothing to migrate).
     *
     * @param string $root Absolute uploads root (e.g. config('FileStorage')->localRoot).
     */
    protected function countLocalFiles(string $root): int
    {
        if ($root === '' || !is_dir($root)) {
            return 0;
        }

        $count = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $root,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            // Exclude *.tmp (case-insensitive) — never counted, never touched.
            if (strtolower($file->getExtension()) === 'tmp') {
                continue;
            }

            $count++;
        }

        return $count;
    }

    /**
     * Count objects in the configured S3 bucket via ListObjectsV2 (paginated).
     *
     * Read-only: listing does not modify or delete any object. This method does
     * NOT catch listing errors — an unreachable bucket (or any SDK/network
     * failure) propagates so run() can report an ERROR with no drift (Req 8.8).
     *
     * @throws \Throwable When the bucket is unreachable or listing fails.
     */
    protected function countS3Objects(): int
    {
        $config = $this->getConfig();
        $client = $this->s3Client();

        $count     = 0;
        $paginator = $client->getPaginator('ListObjectsV2', [
            'Bucket' => $config->bucket,
        ]);

        foreach ($paginator as $page) {
            $count += (int) ($page['KeyCount'] ?? count($page['Contents'] ?? []));
        }

        return $count;
    }
}

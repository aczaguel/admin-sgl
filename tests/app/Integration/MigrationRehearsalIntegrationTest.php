<?php

namespace Tests\App\Integration;

use App\Commands\S3MigrateCheck;
use Aws\CommandInterface;
use Aws\Result;
use Aws\S3\S3Client;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\FileStorage as FileStorageConfig;
use Config\Services;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;

/**
 * Integration test — automated migration rehearsal (Req 8.2, 8.6).
 *
 * Rehearses the whole non-destructive migration verify step end to end:
 *
 *   1. Seed a temp uploads root with a known set of files (some *.tmp).
 *   2. Simulate `aws s3 sync` WITHOUT `--delete` as an ADDITIVE copy: every
 *      local non-`*.tmp` file is unioned into an in-memory "bucket" set (never
 *      removing pre-existing objects — the hallmark of omitting `--delete`).
 *   3. Run `s3:migrate-check` wired to the temp root and a MockHandler-backed
 *      S3 client that reports the synced object count (ListObjectsV2 KeyCount).
 *   4. Assert the command reports SUCCESS with drift <= 0 (every local file has
 *      an S3 counterpart), AND the local file set is byte-for-byte UNCHANGED
 *      after the run (the verify step is strictly read-only — Req 8.2/8.6).
 *
 * The `*.tmp` files are intentionally seeded to confirm they are excluded from
 * the migration count yet left untouched on disk.
 *
 * Offline by construction: the command's setConfig()/setS3Client() seams point
 * localRoot at the temp dir and inject an offline S3 client, so no live AWS is
 * used. CLI output is captured with CITestStreamFilter (project convention).
 *
 * @internal
 */
final class MigrationRehearsalIntegrationTest extends CIUnitTestCase
{
    private const BUCKET = 'sgl-uploads-test';
    private const REGION = 'us-east-1';

    /** Absolute uploads root seeded per test. */
    private string $root = '';

    /** @var resource */
    private $streamFilterStdout;

    /** @var resource */
    private $streamFilterStderr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/e2e_migrate_' . bin2hex(random_bytes(6));
        if (! is_dir($this->root)) {
            mkdir($this->root, 0777, true);
        }

        CITestStreamFilter::$buffer = '';
        $this->streamFilterStdout   = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilterStderr   = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilterStdout);
        stream_filter_remove($this->streamFilterStderr);

        $this->removeTree($this->root);

        parent::tearDown();
    }

    /**
     * Full rehearsal: seed → additive sync → migrate-check → assert drift <= 0
     * success AND the local disk is unchanged.
     *
     * @dataProvider provideSeedSets
     *
     * @param array<int, string> $relativePaths    files to seed under the root
     * @param array<int, string> $preExistingInS3  keys already in the bucket before the sync
     */
    public function testRehearsalReportsCoverageAndLeavesLocalDiskUnchanged(array $relativePaths, array $preExistingInS3): void
    {
        // 1) Seed the local uploads tree (nested dirs + some *.tmp files).
        foreach ($relativePaths as $relative) {
            $this->seedFile($relative);
        }

        // Snapshot BEFORE the verify step (paths + content hashes).
        $before = $this->snapshot($this->root);

        // 2) Simulate `aws s3 sync` WITHOUT --delete: additive copy of every
        //    local non-*.tmp file into the bucket set, unioned with whatever
        //    objects already existed (nothing is ever removed).
        $bucket = $this->simulateAdditiveSync($relativePaths, $preExistingInS3);
        $s3Count = count($bucket);

        // The migration excludes *.tmp files, so the count that must be covered
        // is the number of non-*.tmp local files.
        $localNonTmp = $this->countNonTmp($relativePaths);
        $this->assertGreaterThanOrEqual(
            $localNonTmp,
            $s3Count,
            'additive sync (no --delete) must cover at least every non-tmp local file'
        );

        // 3) Run the real command through its injection seams.
        $command = (new S3MigrateCheck(Services::logger(), service('commands')))
            ->setConfig($this->makeConfig())
            ->setS3Client($this->makeSyncedClient($s3Count));

        $exit   = $command->run([]);
        $buffer = CITestStreamFilter::$buffer;

        // 4a) Coverage before flip: SUCCESS with drift <= 0 (Req 8.4 path, which
        //     is what Property 7 asserts once the additive sync has run).
        $this->assertSame(EXIT_SUCCESS, $exit, 'migrate-check must succeed after an additive sync. Output: ' . $buffer);

        $drift = $localNonTmp - $s3Count;
        $this->assertLessThanOrEqual(0, $drift, 'drift must be <= 0 after additive sync');
        $this->assertStringContainsString('Drift  : ' . $drift, $buffer, 'the drift value must be reported');

        if ($localNonTmp === 0) {
            $this->assertStringContainsString('no hay archivos locales que migrar', $buffer);
        } else {
            $this->assertStringContainsString('cada archivo local tiene su contraparte en S3', $buffer);
        }
        $this->assertStringNotContainsString('ADVERTENCIA', $buffer, 'no warning expected when drift <= 0');

        // 4b) Non-destructive (Req 8.2/8.6): the local disk is byte-for-byte
        //     unchanged — nothing added, removed, renamed, or rewritten, and the
        //     *.tmp files are still present.
        $after = $this->snapshot($this->root);
        ksort($before);
        ksort($after);
        $this->assertSame($before, $after, 'the local uploads tree must be unchanged after the verify step');
        $this->assertCount(count($before), $after, 'local file count must be unchanged');
    }

    /**
     * Seeded sets mixing flat/nested files and *.tmp entries, plus buckets that
     * may already hold unrelated objects (the sync must never remove those).
     *
     * @return array<string, array{0: array<int, string>, 1: array<int, string>}>
     */
    public function provideSeedSets(): array
    {
        return [
            'flat + tmp, empty bucket' => [
                ['a.jpg', 'b.png', 'scratch.tmp', 'c.pdf'],
                [],
            ],
            'nested + per-id, empty bucket' => [
                ['documentostatus/doc_1.pdf', 'pago_gestor/12472/comp.jpg', 'evidencias/1/scan.png', 'evidencias/1/scan.tmp'],
                [],
            ],
            'bucket already has extra objects (drift < 0)' => [
                ['x.jpg', 'y.pdf'],
                ['legacy/old1.jpg', 'legacy/old2.jpg', 'legacy/old3.jpg'],
            ],
            'only tmp files (nothing to migrate)' => [
                ['a.tmp', 'evidencias/1/b.tmp'],
                [],
            ],
            'empty local set' => [
                [],
                ['legacy/pre.jpg'],
            ],
            'deep nested tree' => [
                ['a/b/c/d/e/file.pdf', 'a/b/g.jpg', 'a/h.tmp'],
                [],
            ],
        ];
    }

    // ---------------------------------------------------------------------
    // Sync simulation + counts
    // ---------------------------------------------------------------------

    /**
     * Model `aws s3 sync <root> s3://bucket` WITHOUT `--delete`: start from the
     * pre-existing bucket objects and ADD every local non-*.tmp file's key. The
     * union semantics (no removals) mirror omitting `--delete`.
     *
     * @param array<int, string> $relativePaths
     * @param array<int, string> $preExistingInS3
     *
     * @return array<string, true> the resulting bucket key set
     */
    private function simulateAdditiveSync(array $relativePaths, array $preExistingInS3): array
    {
        $bucket = [];
        foreach ($preExistingInS3 as $key) {
            $bucket[$key] = true;
        }
        foreach ($relativePaths as $relative) {
            if (strtolower(pathinfo($relative, PATHINFO_EXTENSION)) === 'tmp') {
                continue; // excluded from the sync (matches --exclude "*.tmp")
            }
            $bucket[$relative] = true;
        }

        return $bucket;
    }

    /**
     * Count non-*.tmp entries in a seed set (what migrate-check counts locally).
     *
     * @param array<int, string> $relativePaths
     */
    private function countNonTmp(array $relativePaths): int
    {
        $n = 0;
        foreach ($relativePaths as $relative) {
            if (strtolower(pathinfo($relative, PATHINFO_EXTENSION)) !== 'tmp') {
                $n++;
            }
        }

        return $n;
    }

    // ---------------------------------------------------------------------
    // Command wiring (offline)
    // ---------------------------------------------------------------------

    private function makeConfig(): FileStorageConfig
    {
        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->localRoot = $this->root;
        $config->bucket    = self::BUCKET;
        $config->region    = self::REGION;

        return $config;
    }

    /**
     * S3Client whose network layer answers ListObjectsV2 with the synced object
     * count and no continuation token, so the paginator terminates after one
     * page. Dummy static creds keep everything offline.
     */
    private function makeSyncedClient(int $keyCount): S3Client
    {
        $handler = static function (CommandInterface $command, RequestInterface $request) use ($keyCount) {
            if ($command->getName() === 'ListObjectsV2') {
                return new FulfilledPromise(new Result([
                    'KeyCount'    => $keyCount,
                    'Contents'    => [],
                    'IsTruncated' => false,
                ]));
            }

            return new FulfilledPromise(new Result([]));
        };

        return new S3Client([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $handler,
        ]);
    }

    // ---------------------------------------------------------------------
    // Local disk seeding + snapshotting
    // ---------------------------------------------------------------------

    private function seedFile(string $relative): void
    {
        $path = rtrim($this->root, '/') . '/' . $relative;
        $dir  = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, 'seed:' . $relative);
    }

    /**
     * Map relative-path => sha1(content) for every regular file under $root.
     *
     * @return array<string, string>
     */
    private function snapshot(string $root): array
    {
        $out = [];
        if (! is_dir($root)) {
            return $out;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $prefix = rtrim($root, '/') . '/';
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $relative       = substr($file->getPathname(), strlen($prefix));
            $out[$relative] = sha1_file($file->getPathname()) ?: '';
        }

        return $out;
    }

    private function removeTree(string $dir): void
    {
        if ($dir === '' || ! is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeTree($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}

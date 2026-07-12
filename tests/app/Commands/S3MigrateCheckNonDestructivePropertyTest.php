<?php

namespace Tests\App\Commands;

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
 * Property-based test for Property 6: Non-destructive migration.
 *
 * Validates: Requirements 8.2, 8.6
 *
 * For arbitrary seeded local file sets, running `s3:migrate-check` must leave
 * the local uploads directory exactly as it was: the command performs only
 * READS over both stores and deletes/creates/renames nothing.
 *
 *   ∀ seeded local file set F:
 *     files(root) after run([]) === files(root) before run([])
 *
 * We assert BOTH the file COUNT and the exact SET of relative file paths are
 * unchanged (a stronger invariant than count alone: it also rejects any
 * add/remove/rename that happens to preserve the count).
 *
 * Offline by construction:
 *   - S3MigrateCheck is `final`, so instead of a subclass overriding
 *     countS3Objects() we use its {@see S3MigrateCheck::setS3Client()} seam and
 *     inject an S3Client whose network layer is a handler closure returning a
 *     fixed ListObjectsV2 KeyCount. No AWS call ever leaves the process and the
 *     S3 count value is irrelevant to this property — the point is the LOCAL
 *     disk is untouched.
 *   - {@see S3MigrateCheck::setConfig()} points localRoot at a per-run temp dir.
 *
 * PBT generators are seeded PHPUnit data providers so any counterexample is
 * reproducible and no new runtime dependency is introduced.
 *
 * @internal
 */
final class S3MigrateCheckNonDestructivePropertyTest extends CIUnitTestCase
{
    /** Absolute root directory (local uploads) handed to the command per run. */
    private string $root = '';

    /** @var resource */
    private $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/s3mc_nondestructive_' . bin2hex(random_bytes(6));
        if (! is_dir($this->root)) {
            mkdir($this->root, 0777, true);
        }

        // Swallow the command's CLI output so the test run stays quiet.
        CITestStreamFilter::$buffer = '';
        $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        $this->removeTree($this->root);

        parent::tearDown();
    }

    /**
     * Property 6: running the check never mutates the local uploads directory.
     *
     * @dataProvider provideLocalFileSets
     *
     * @param array<int, string> $relativePaths relative file paths to seed
     * @param int                $s3Count       stubbed S3 object count
     */
    public function testMigrateCheckIsNonDestructiveOverLocalDisk(array $relativePaths, int $s3Count): void
    {
        // Materialize the seeded local file set (nested dirs + some *.tmp).
        foreach ($relativePaths as $relative) {
            $this->seedFile($relative);
        }

        $message = sprintf('files=%d s3Count=%d', count($relativePaths), $s3Count);

        // Snapshot BEFORE: exact set of file paths (with content hashes) + count.
        $before = $this->snapshot($this->root);

        $command = (new S3MigrateCheck(Services::logger(), service('commands')))
            ->setConfig($this->makeConfig())
            ->setS3Client($this->makeStubClient($s3Count));

        // Run the read-only integrity check.
        $command->run([]);

        // Snapshot AFTER.
        $after = $this->snapshot($this->root);

        // Req 8.2 / 8.6: count is preserved (read-only over the local store).
        $this->assertSame(
            count($before),
            count($after),
            'Local file count changed after migrate-check. ' . $message
        );

        // Stronger: the exact set of paths (and their bytes) is preserved, so
        // nothing was added, removed, or renamed. ksort for a stable compare.
        ksort($before);
        ksort($after);
        $this->assertSame(
            $before,
            $after,
            'Local file set (paths/content) changed after migrate-check. ' . $message
        );
    }

    /**
     * Seeded pseudo-random generator of local file sets. Each case is a set of
     * relative paths that includes: flat files, nested subdirectories, and a
     * proportion of *.tmp files (which the command must also leave untouched).
     * Includes the empty set as an explicit edge case (Req 8.9 path).
     *
     * @return array<string, array{0: array<int, string>, 1: int}>
     */
    public function provideLocalFileSets(): array
    {
        // Deterministic seed so any counterexample is reproducible.
        mt_srand(20240711);

        $categories = ['documentostatus', 'evidencias', 'avatars', 'tramites', 'pago_gestor'];
        $extensions = ['jpg', 'png', 'pdf', 'tmp', 'txt', 'tmp'];

        $cases = [];
        $count = 120;

        for ($i = 0; $i < $count; $i++) {
            $fileCount = mt_rand(0, 12);
            $paths     = [];

            for ($f = 0; $f < $fileCount; $f++) {
                $paths[] = $this->randomRelativePath($categories, $extensions);
            }

            // De-duplicate so a path is never seeded twice in the same case.
            $paths = array_values(array_unique($paths));

            $cases['case_' . $i] = [$paths, mt_rand(0, 500)];
        }

        // Explicit edge cases.
        $cases['edge_empty_set']       = [[], 0];
        $cases['edge_only_tmp']        = [['a.tmp', 'evidencias/1/scan.tmp'], 3];
        $cases['edge_deep_nested']     = [['a/b/c/d/e/file.pdf'], 1];
        $cases['edge_mixed']           = [['x.jpg', 'x.tmp', 'documentostatus/9/y.png'], 999];

        return $cases;
    }

    // --- generators -------------------------------------------------------

    /**
     * Build a relative path with 1..4 segments; the final segment is a file
     * name with one of the allowed extensions (some *.tmp on purpose).
     *
     * @param array<int, string> $categories
     * @param array<int, string> $extensions
     */
    private function randomRelativePath(array $categories, array $extensions): string
    {
        $shape = mt_rand(0, 2);
        $file  = $this->randomSegment() . '.' . $extensions[mt_rand(0, count($extensions) - 1)];

        if ($shape === 0) {
            return $file; // flat file at the root
        }

        if ($shape === 1) {
            // category/<id>/<file> (per-id layout).
            $category = $categories[mt_rand(0, count($categories) - 1)];

            return $category . '/' . mt_rand(1, 9999) . '/' . $file;
        }

        // Arbitrary 1..3 intermediate dirs + file.
        $segments = [];
        $dirCount = mt_rand(1, 3);
        for ($s = 0; $s < $dirCount; $s++) {
            $segments[] = $this->randomSegment();
        }
        $segments[] = $file;

        return implode('/', $segments);
    }

    /**
     * A single non-empty path segment from `[A-Za-z0-9._-]`, never "." / "..".
     */
    private function randomSegment(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';
        $len      = mt_rand(1, 16);

        do {
            $segment = '';
            for ($i = 0; $i < $len; $i++) {
                $segment .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
        } while ($segment === '.' || $segment === '..');

        return $segment;
    }

    // --- helpers ----------------------------------------------------------

    private function makeConfig(): FileStorageConfig
    {
        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->localRoot = $this->root;
        $config->bucket    = 'unit-test-bucket';
        $config->region    = 'us-east-1';

        return $config;
    }

    /**
     * An S3Client whose network layer is replaced by a handler closure that
     * answers ListObjectsV2 with a fixed KeyCount and no continuation token, so
     * the command's paginator terminates after one page. Dummy static creds
     * keep everything offline; nothing leaves the process.
     */
    private function makeStubClient(int $keyCount): S3Client
    {
        $handler = static function (CommandInterface $command, RequestInterface $request) use ($keyCount) {
            if ($command->getName() === 'ListObjectsV2') {
                return new FulfilledPromise(new Result([
                    'KeyCount'    => $keyCount,
                    'Contents'    => [],
                    'IsTruncated' => false,
                ]));
            }

            // No other S3 command should be issued by a read-only check.
            return new FulfilledPromise(new Result([]));
        };

        return new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $handler,
        ]);
    }

    /**
     * Create a file at $relative under the root, making intermediate dirs, and
     * write deterministic-ish content so the snapshot can detect any rewrite.
     */
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
     * Map of relative-path => sha1(content) for every regular file under $root.
     * Capturing content hashes makes the before/after compare detect not only
     * add/remove/rename but also any in-place rewrite.
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

            $absolute            = $file->getPathname();
            $relative            = substr($absolute, strlen($prefix));
            $out[$relative]      = sha1_file($absolute) ?: '';
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

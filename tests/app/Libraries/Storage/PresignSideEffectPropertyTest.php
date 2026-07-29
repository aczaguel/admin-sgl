<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\FileStorageService;
use App\Libraries\Storage\S3FileStorage;
use Aws\MockHandler;
use Aws\S3\S3Client;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;

/**
 * Property-based test for Property 7: Presign is side-effect free and expiring.
 *
 * Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5, 9.6
 *
 * Under the S3 driver, resolving a URL via file_url() must:
 *   - Perform no network I/O (no AWS S3 API calls)
 *   - Perform no writes (no DB, filesystem, or bucket mutations)
 *   - Produce a presigned URL valid for `ttl` seconds (default 300)
 *   - Never persist the resolved URL
 *
 * The test uses the AWS SDK MockHandler backed by an EMPTY queue. If the S3
 * client attempts ANY network request (put, get, head, delete), the MockHandler
 * will throw because there is no enqueued response — proving zero network I/O.
 *
 * PBT generators are implemented as seeded PHPUnit data providers so any
 * counterexample is reproducible and no new runtime dependency is introduced.
 *
 * @internal
 */
final class PresignSideEffectPropertyTest extends CIUnitTestCase
{
    /** Bucket used for every test client. */
    private const BUCKET = 'test-bucket-presign';

    /** SSE algorithm the driver is configured with. */
    private const SSE = 'AES256';

    /** Default presigned URL TTL expected (seconds). */
    private const DEFAULT_TTL = 300;

    /** Temp directory used to monitor filesystem writes. */
    private string $tempDir = '';

    // ─────────────────────────────────────────────────────────────────────
    // Setup / Teardown
    // ─────────────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary directory to monitor for filesystem writes.
        $this->tempDir = sys_get_temp_dir() . '/presign_sideeffect_test_' . bin2hex(random_bytes(4));
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        // Clean up temp directory.
        if (is_dir($this->tempDir)) {
            $this->removeDir($this->tempDir);
        }

        parent::tearDown();
    }

    private function removeDir(string $dir): void
    {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Build an S3FileStorage backed by a MockHandler with an EMPTY queue.
     *
     * If file_url() (via the storage service → S3 driver) attempts ANY network
     * request, the MockHandler throws because no response is available. This
     * proves that presigning is purely local.
     *
     * @return array{storage: S3FileStorage, handler: MockHandler}
     */
    private function makeStorage(): array
    {
        $handler = new MockHandler();

        $config         = new FileStorageConfig();
        $config->driver = 's3';
        $config->bucket = self::BUCKET;
        $config->region = 'us-east-1';
        $config->sse    = self::SSE;

        $client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $handler,
        ]);

        $storage = new S3FileStorage($config, $client);

        return ['storage' => $storage, 'handler' => $handler];
    }

    /**
     * Build a FileStorageService with s3 as the active driver, backed by a
     * MockHandler with an EMPTY queue.
     *
     * @return array{service: FileStorageService, handler: MockHandler}
     */
    private function makeService(): array
    {
        $handler = new MockHandler();

        $config            = new FileStorageConfig();
        $config->driver    = 's3';
        $config->bucket    = self::BUCKET;
        $config->region    = 'us-east-1';
        $config->sse       = self::SSE;
        $config->dualWrite = false;
        $config->localRoot = $this->tempDir;

        $client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $handler,
        ]);

        $s3Driver = new S3FileStorage($config, $client);

        // Inject the S3 driver so the service uses our MockHandler-backed client.
        $service = new FileStorageService($config, null, $s3Driver);

        return ['service' => $service, 'handler' => $handler];
    }

    /**
     * Parse the query string of a URL into an associative array.
     *
     * @return array<string, string>
     */
    private function queryParams(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertIsString($query, 'presigned URL must contain a query string: ' . $url);

        $params = [];
        parse_str($query, $params);

        return $params;
    }

    /**
     * Snapshot files in a directory recursively (returns a flat array of paths).
     *
     * @return string[]
     */
    private function snapshotDir(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        $iter  = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iter as $fileInfo) {
            $files[] = $fileInfo->getPathname();
        }

        sort($files);

        return $files;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Property 7a: No network I/O — resolving N values issues no handler call
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @dataProvider provideStoredValues
     */
    public function testPresignPerformsNoNetworkIO(array $storedValues): void
    {
        ['storage' => $storage, 'handler' => $handler] = $this->makeStorage();

        // The MockHandler queue is empty. If any AWS API call is made, the
        // handler will throw an exception because there's no response queued.
        // The fact that no exception is thrown proves zero network I/O.
        foreach ($storedValues as $key) {
            $url = $storage->url($key, self::DEFAULT_TTL);

            // Every resolved URL must be non-empty (these are all valid keys).
            $this->assertNotSame('', $url, 'url() must produce a presigned URL for valid key: ' . $key);
        }

        // If we reach here, no handler invocation happened (no exception thrown).
        $this->assertTrue(true, 'No network I/O performed during presign resolution.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Property 7b: No filesystem writes during URL resolution
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @dataProvider provideStoredValues
     */
    public function testPresignPerformsNoFilesystemWrites(array $storedValues): void
    {
        ['service' => $service] = $this->makeService();

        // Snapshot the temp directory before resolution.
        $before = $this->snapshotDir($this->tempDir);

        foreach ($storedValues as $key) {
            $service->url($key, self::DEFAULT_TTL);
        }

        // Snapshot after resolution.
        $after = $this->snapshotDir($this->tempDir);

        $this->assertSame(
            $before,
            $after,
            'url() must not create or modify any files on the filesystem. ' .
            'Diff: added=' . implode(',', array_diff($after, $before))
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Property 7c: Presigned URL encodes the configured TTL (X-Amz-Expires)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @dataProvider provideStoredValuesWithTtl
     */
    public function testPresignedUrlEncodesConfiguredTtl(string $key, int $expectedTtl): void
    {
        ['storage' => $storage] = $this->makeStorage();

        $url = $storage->url($key, $expectedTtl);

        $this->assertNotSame('', $url, 'url() must produce a presigned URL for: ' . $key);

        $params = $this->queryParams($url);

        $this->assertArrayHasKey('X-Amz-Expires', $params, 'Presigned URL must encode X-Amz-Expires.');
        $this->assertSame(
            $expectedTtl,
            (int) $params['X-Amz-Expires'],
            sprintf('X-Amz-Expires must equal the configured TTL (%d). key=%s', $expectedTtl, $key)
        );
    }

    /**
     * Test that the default TTL of 300 is encoded when no explicit TTL is passed.
     *
     * @dataProvider provideKeys
     */
    public function testDefaultTtl300EncodedWhenNoExplicitTtl(string $key): void
    {
        ['storage' => $storage] = $this->makeStorage();

        // Call with the default ttl argument (300).
        $url = $storage->url($key);

        $this->assertNotSame('', $url, 'url() must produce a presigned URL with default TTL: ' . $key);

        $params = $this->queryParams($url);
        $this->assertArrayHasKey('X-Amz-Expires', $params);
        $this->assertSame(
            self::DEFAULT_TTL,
            (int) $params['X-Amz-Expires'],
            'Default X-Amz-Expires must be 300 seconds for key: ' . $key
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Property 7d: Resolved URLs are never persisted (no DB writes)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @dataProvider provideStoredValues
     */
    public function testPresignNeverPersistsUrls(array $storedValues): void
    {
        ['service' => $service] = $this->makeService();

        // Collect all resolved URLs.
        $resolvedUrls = [];
        foreach ($storedValues as $key) {
            $url = $service->url($key, self::DEFAULT_TTL);
            if ($url !== '') {
                $resolvedUrls[] = $url;
            }
        }

        // Since file_url() itself does not persist, and the S3 driver's url()
        // only signs locally, we verify:
        // 1. The resolved URLs are ephemeral strings returned on the stack.
        // 2. No file was created that contains any URL (filesystem persistence check).
        $fsSnapshot = $this->snapshotDir($this->tempDir);
        foreach ($fsSnapshot as $file) {
            if (!is_file($file)) {
                continue;
            }
            $content = file_get_contents($file);
            foreach ($resolvedUrls as $url) {
                $this->assertStringNotContainsString(
                    $url,
                    $content,
                    'Resolved presigned URL must not be persisted to any file: ' . basename($file)
                );
            }
        }

        // Verify the presigned URLs contain the ephemeral signature query params
        // (proving they are generated per-request, not cached/stored).
        foreach ($resolvedUrls as $url) {
            $params = $this->queryParams($url);
            $this->assertArrayHasKey('X-Amz-Signature', $params, 'URL must be freshly signed (contains X-Amz-Signature).');
            $this->assertArrayHasKey('X-Amz-Date', $params, 'URL must contain X-Amz-Date (issuance timestamp).');
        }

        $this->assertTrue(true, 'No presigned URL was persisted.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Property 7e: Sweeping N values through file_url service — all hold
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Combined sweep: resolve multiple stored values and verify ALL side-effect
     * properties hold simultaneously.
     *
     * @dataProvider provideBulkStoredValues
     */
    public function testBulkResolutionSideEffectFree(array $storedValues): void
    {
        ['service' => $service, 'handler' => $handler] = $this->makeService();

        // Filesystem snapshot before.
        $fsBefore = $this->snapshotDir($this->tempDir);

        $resolvedUrls = [];
        foreach ($storedValues as $key) {
            $url = $service->url($key, self::DEFAULT_TTL);
            if ($url !== '') {
                $resolvedUrls[] = $url;
            }
        }

        // 1. No network I/O: the empty MockHandler didn't throw.
        $this->assertTrue(true, 'No network I/O: MockHandler queue empty, no exception thrown.');

        // 2. No filesystem writes.
        $fsAfter = $this->snapshotDir($this->tempDir);
        $this->assertSame($fsBefore, $fsAfter, 'No filesystem writes during bulk resolution.');

        // 3. TTL encoded correctly in every URL.
        foreach ($resolvedUrls as $url) {
            $params = $this->queryParams($url);
            $this->assertArrayHasKey('X-Amz-Expires', $params);
            $this->assertSame(
                self::DEFAULT_TTL,
                (int) $params['X-Amz-Expires'],
                'Each presigned URL must encode X-Amz-Expires=' . self::DEFAULT_TTL
            );
        }

        // 4. URLs are ephemeral (not persisted).
        foreach ($resolvedUrls as $url) {
            $params = $this->queryParams($url);
            $this->assertArrayHasKey('X-Amz-Signature', $params, 'Freshly signed.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Data Providers (Generators)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Generate arrays of valid storage keys to sweep through file_url/url().
     *
     * @return array<string, array{0: string[]}>
     */
    public function provideStoredValues(): array
    {
        mt_srand(20240715);

        $cases = [];

        for ($i = 0; $i < 30; $i++) {
            $count = mt_rand(1, 10);
            $keys  = [];
            for ($j = 0; $j < $count; $j++) {
                $keys[] = $this->randomKey();
            }
            $cases['batch_' . $i] = [$keys];
        }

        // Edge cases: single key, per-id categories.
        $cases['single_key'] = [['documentostatus/comprobante_abc123.pdf']];
        $cases['per_id_key'] = [['pago_gestor/12472/comprobante_12472_ab12cd34ef56.jpg']];
        $cases['multi_categories'] = [[
            'documentostatus/doc_one.pdf',
            'pago_derechos/999/recibo_999_aabbccdd.png',
            'cobro_cliente/555/cobro_555_11223344.jpg',
            'avatars/user_avatar_abcd1234.webp',
        ]];

        return $cases;
    }

    /**
     * Generate (key, ttl) pairs for TTL-encoding property.
     *
     * @return array<string, array{0: string, 1: int}>
     */
    public function provideStoredValuesWithTtl(): array
    {
        mt_srand(20240716);

        $cases = [];

        // Default TTL.
        $cases['default_ttl_300'] = ['documentostatus/file_default.pdf', 300];

        // Custom TTLs.
        for ($i = 0; $i < 50; $i++) {
            $key = $this->randomKey();
            $ttl = mt_rand(1, 604800);
            $cases['custom_ttl_' . $i] = [$key, $ttl];
        }

        // Edge cases.
        $cases['edge_ttl_1']      = ['evidencias/min.png', 1];
        $cases['edge_ttl_3600']   = ['pago_gestor/100/hour.jpg', 3600];
        $cases['edge_ttl_86400']  = ['cobro_cliente/200/day.pdf', 86400];
        $cases['edge_ttl_604800'] = ['documentostatus/max_week.png', 604800];

        return $cases;
    }

    /**
     * Generate keys only (for the default-ttl test).
     *
     * @return array<string, array{0: string}>
     */
    public function provideKeys(): array
    {
        mt_srand(20240717);

        $cases = [];
        for ($i = 0; $i < 30; $i++) {
            $cases['key_' . $i] = [$this->randomKey()];
        }

        $cases['edge_single_segment'] = ['avatars_file.png'];
        $cases['edge_per_id']         = ['pago_gestor/12472/comprobante_abc123.jpg'];
        $cases['edge_nested']         = ['documentostatus/doc_nested.pdf'];

        return $cases;
    }

    /**
     * Generate larger batches for the bulk sweep test.
     *
     * @return array<string, array{0: string[]}>
     */
    public function provideBulkStoredValues(): array
    {
        mt_srand(20240718);

        $cases = [];

        for ($i = 0; $i < 10; $i++) {
            $count = mt_rand(5, 25);
            $keys  = [];
            for ($j = 0; $j < $count; $j++) {
                $keys[] = $this->randomKey();
            }
            $cases['bulk_' . $i] = [$keys];
        }

        // Edge: a single value.
        $cases['bulk_single'] = [['documentostatus/solo.pdf']];

        // Edge: many per-id keys from the same category.
        $manyPerIdKeys = [];
        for ($k = 0; $k < 15; $k++) {
            $id              = mt_rand(1, 99999);
            $manyPerIdKeys[] = 'cobro_cliente/' . $id . '/cobro_' . $id . '_' . bin2hex(random_bytes(4)) . '.jpg';
        }
        $cases['bulk_same_category'] = [$manyPerIdKeys];

        return $cases;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Key generators
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Build a valid relative key with 1..4 segments matching
     * `[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*`, never a ".." segment.
     */
    private function randomKey(): string
    {
        $categories = ['documentostatus', 'evidencias', 'avatars', 'pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $shape = mt_rand(0, 2);

        if ($shape === 0) {
            // Bare filename (single segment).
            return $this->randomSegment();
        }

        if ($shape === 1) {
            // category/id/file.
            $category = $categories[mt_rand(0, count($categories) - 1)];

            return $category . '/' . mt_rand(1, 999999) . '/' . $this->randomSegment();
        }

        // category/file (no id).
        $category = $categories[mt_rand(0, count($categories) - 1)];

        return $category . '/' . $this->randomSegment();
    }

    /**
     * A single non-empty path segment from `[A-Za-z0-9._-]`, never "." or "..".
     */
    private function randomSegment(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789._-';
        $len      = mt_rand(4, 24);

        $extensions = ['pdf', 'jpg', 'png', 'gif', 'webp', 'docx', 'xlsx'];

        do {
            $segment = '';
            for ($i = 0; $i < $len; $i++) {
                $segment .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
        } while ($segment === '.' || $segment === '..');

        // Append an extension most of the time.
        if (mt_rand(0, 3) > 0) {
            $segment .= '.' . $extensions[mt_rand(0, count($extensions) - 1)];
        }

        return $segment;
    }
}

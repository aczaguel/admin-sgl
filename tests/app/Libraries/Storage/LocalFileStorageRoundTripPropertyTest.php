<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\LocalFileStorage;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;

/**
 * Property-based test for Property 2: Round-trip (against the Local driver).
 *
 * Validates: Requirements 2.1, 2.2
 *
 * For arbitrary valid relative keys (single- and multi-segment, including the
 * per-id `category/<id>/<file>` shape) and arbitrary payload bytes:
 *   - after put(key, tmp) the object exists  -> exists(key) === true (Req 2.1)
 *   - the stored bytes are exactly the payload written
 *   - after delete(key) the object is gone   -> exists(key) === false (Req 2.2)
 *
 * The driver is backed by a real temporary directory (sys_get_temp_dir()).
 * LocalFileStorage::put falls back to rename()/copy() for non-upload sources,
 * so a plain temp file is a valid source. A fresh temp source is created for
 * each put because put() moves the source out of place.
 *
 * PBT generators are implemented as seeded PHPUnit data providers so any
 * counterexample is reproducible and no new runtime dependency is introduced.
 *
 * @internal
 */
final class LocalFileStorageRoundTripPropertyTest extends CIUnitTestCase
{
    /** The canonical key pattern the generated keys must satisfy. */
    private const KEY_PATTERN = '#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#';

    /** Absolute root directory handed to the driver for this test run. */
    private string $root = '';

    /** Temp source files created during a test, cleaned up in tearDown. */
    private array $tmpSources = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/lfs_roundtrip_' . bin2hex(random_bytes(6));
        if (! is_dir($this->root)) {
            mkdir($this->root, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Remove any leftover temp source files.
        foreach ($this->tmpSources as $src) {
            if (is_file($src)) {
                @unlink($src);
            }
        }
        $this->tmpSources = [];

        // Recursively remove the driver root created for this run.
        $this->removeTree($this->root);

        parent::tearDown();
    }

    /**
     * Property 2: put makes the object exist with the exact bytes written,
     * and delete makes it stop existing.
     *
     * @dataProvider provideKeysAndPayloads
     */
    public function testRoundTrip(string $key, string $payload): void
    {
        // Guard: the generator must only emit keys the contract accepts.
        $this->assertSame(1, preg_match(self::KEY_PATTERN, $key), 'Generator produced an invalid key: ' . $key);

        $storage = new LocalFileStorage($this->makeConfig());

        $message = sprintf('key=%s payloadLen=%d', $key, strlen($payload));

        // Precondition: nothing stored at the key yet.
        $this->assertFalse($storage->exists($key), 'Key unexpectedly exists before put. ' . $message);

        // put() from a fresh temp source (put moves the source out of place).
        $src = $this->makeTmpSource($payload);
        $this->assertTrue($storage->put($key, $src), 'put() returned false. ' . $message);

        // Req 2.1: after a successful put the object exists.
        $this->assertTrue($storage->exists($key), 'exists() false after put. ' . $message);

        // The stored bytes match the payload exactly.
        $storedPath = rtrim($this->root, '/') . '/' . $key;
        $this->assertFileExists($storedPath, $message);
        $this->assertSame($payload, file_get_contents($storedPath), 'Stored bytes differ from payload. ' . $message);

        // Req 2.2: after delete the object no longer exists.
        $this->assertTrue($storage->delete($key), 'delete() returned false. ' . $message);
        $this->assertFalse($storage->exists($key), 'exists() true after delete. ' . $message);
        $this->assertFileDoesNotExist($storedPath, $message);
    }

    /**
     * Seeded pseudo-random generator of (key, payload) pairs. Produces valid
     * single- and multi-segment keys (including per-id style `cat/<id>/file`)
     * and payloads of varying length, including empty and binary content.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public function provideKeysAndPayloads(): array
    {
        // Deterministic seed so any counterexample is reproducible.
        mt_srand(20240517);

        $categories = ['documentostatus', 'evidencias', 'avatars', 'tramites', 'pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $cases = [];
        $count = 300;

        for ($i = 0; $i < $count; $i++) {
            $key     = $this->randomKey($categories);
            $payload = $this->randomPayload();

            $cases['case_' . $i . '_' . bin2hex(random_bytes(3))] = [$key, $payload];
        }

        // A few explicit edge cases alongside the random ones.
        $cases['edge_single_segment']   = ['avatars_only_file.png', ''];
        $cases['edge_deep_multi']       = ['a/b/c/d/e/file.name-1.2.3', 'deep'];
        $cases['edge_dotted_filename']  = ['documentostatus/a..b.jpg', 'dots-in-name-not-a-traversal'];
        $cases['edge_per_id']           = ['pago_gestor/12472/comprobante_12472_abc123.jpg', "binary\x00\xff\x01bytes"];

        return $cases;
    }

    // --- generators -------------------------------------------------------

    /**
     * Build a valid relative key with 1..4 segments. Every segment matches
     * `[A-Za-z0-9._-]+` and is never "..", so the key satisfies KEY_PATTERN.
     *
     * @param array<int, string> $categories
     */
    private function randomKey(array $categories): string
    {
        $shape = mt_rand(0, 2);

        if ($shape === 0) {
            // Single-segment key (e.g. legacy flat categories).
            return $this->randomSegment();
        }

        if ($shape === 1) {
            // category/<id>/<file> (per-id style).
            $category = $categories[mt_rand(0, count($categories) - 1)];

            return $category . '/' . mt_rand(1, 999999) . '/' . $this->randomSegment();
        }

        // Arbitrary 2..4 segment key.
        $segments   = [];
        $segmentCnt = mt_rand(2, 4);
        for ($s = 0; $s < $segmentCnt; $s++) {
            $segments[] = $this->randomSegment();
        }

        return implode('/', $segments);
    }

    /**
     * A single non-empty path segment drawn from the allowed character set
     * `[A-Za-z0-9._-]`. Guaranteed not to be "." or "..".
     */
    private function randomSegment(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789._-';
        $len      = mt_rand(1, 24);

        do {
            $segment = '';
            for ($i = 0; $i < $len; $i++) {
                $segment .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
        } while ($segment === '.' || $segment === '..');

        return $segment;
    }

    /**
     * Random payload bytes: sometimes empty, sometimes text, sometimes binary.
     */
    private function randomPayload(): string
    {
        $kind = mt_rand(0, 3);

        if ($kind === 0) {
            return ''; // empty payload
        }

        $len = mt_rand(1, 4096);

        if ($kind === 1) {
            // Printable text.
            $out = '';
            for ($i = 0; $i < $len; $i++) {
                $out .= chr(mt_rand(32, 126));
            }

            return $out;
        }

        // Arbitrary binary content (full byte range).
        return random_bytes($len);
    }

    // --- helpers ----------------------------------------------------------

    private function makeConfig(): FileStorageConfig
    {
        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->localRoot = $this->root;

        return $config;
    }

    /**
     * Write $payload to a fresh temp file and track it for cleanup. The file
     * lives outside the driver root so put()'s rename does not collide.
     */
    private function makeTmpSource(string $payload): string
    {
        $src = sys_get_temp_dir() . '/lfs_src_' . bin2hex(random_bytes(8));
        file_put_contents($src, $payload);
        $this->tmpSources[] = $src;

        return $src;
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

<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\LocalFileStorage;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;

/**
 * Property-based test for Property 3: Delete idempotence.
 *
 * Validates: Requirements 2.3, 2.4
 *
 * For arbitrary valid relative keys, this asserts:
 *   1. delete(k) returns true whether or not an object exists at k
 *      (Req 2.3): calling delete on a never-written key returns true, and
 *      put→delete→delete returns true on every invocation (Req 2.4).
 *   2. Deleting key k never affects any other key k2 (Req 2.3, 2.4): given a
 *      set of distinct keys each with distinct bytes, deleting one leaves
 *      every other object present with its bytes intact.
 *
 * The driver is exercised against a real, isolated temporary directory
 * (sys_get_temp_dir()) used as localRoot; every file created is removed in
 * tearDown. Property tests use PHPUnit data-provider generators (seeded
 * pseudo-random) so no new runtime dependency is introduced and any
 * counterexample is reproducible.
 *
 * @internal
 */
final class LocalFileStorageDeleteIdempotencePropertyTest extends CIUnitTestCase
{
    /** The canonical key pattern required by the design (Req 3.1). */
    private const KEY_PATTERN = '#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#';

    private string $root = '';

    private LocalFileStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        // Isolated real temp dir used as the local uploads root.
        $this->root = rtrim(sys_get_temp_dir(), '/\\')
            . '/sgl-local-delete-idempotence-' . bin2hex(random_bytes(6));

        $config            = new FileStorageConfig();
        $config->localRoot = $this->root;

        $this->storage = new LocalFileStorage($config);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->root);

        parent::tearDown();
    }

    /**
     * Property (Req 2.3, 2.4): delete is idempotent — it returns true whether
     * or not the key existed, and returns true on every repeated invocation.
     *
     * @dataProvider provideRandomKeys
     */
    public function testDeleteIsIdempotent(string $key): void
    {
        $this->assertSame(1, preg_match(self::KEY_PATTERN, $key), "Generated key is not valid: {$key}");

        // (Req 2.3) delete on a never-written key returns true.
        $this->assertTrue(
            $this->storage->delete($key),
            "delete() on a never-written key must return true (key: {$key})"
        );
        $this->assertFalse(
            $this->storage->exists($key),
            "A never-written key must not exist after delete (key: {$key})"
        );

        // put the object so it exists.
        $this->putKey($key, 'payload-for-' . $key);
        $this->assertTrue($this->storage->exists($key), "put() should make the key exist (key: {$key})");

        // (Req 2.4) repeated delete returns true on every invocation.
        $this->assertTrue($this->storage->delete($key), "first delete() must return true (key: {$key})");
        $this->assertFalse($this->storage->exists($key), "key must be gone after first delete (key: {$key})");

        $this->assertTrue($this->storage->delete($key), "second delete() must return true (key: {$key})");
        $this->assertTrue($this->storage->delete($key), "third delete() must return true (key: {$key})");
        $this->assertFalse($this->storage->exists($key), "key must stay gone after repeated delete (key: {$key})");
    }

    /**
     * Property (Req 2.3, 2.4): deleting key k never affects any key k2 ≠ k.
     *
     * Given a set of distinct keys each written with distinct bytes, deleting
     * one of them leaves every other object present and byte-for-byte intact.
     *
     * @dataProvider provideDistinctKeySets
     *
     * @param array<int, string> $keys
     */
    public function testDeleteNeverAffectsOtherKeys(array $keys, int $victimIndex): void
    {
        // Assign distinct content to each key and write them all.
        $contents = [];
        foreach ($keys as $i => $key) {
            $contents[$key] = "distinct-content-{$i}-" . bin2hex(random_bytes(4));
            $this->putKey($key, $contents[$key]);
            $this->assertTrue($this->storage->exists($key), "setup: key should exist after put (key: {$key})");
        }

        $victim = $keys[$victimIndex];

        // Delete exactly one key.
        $this->assertTrue($this->storage->delete($victim), "delete() of the victim must return true (key: {$victim})");
        $this->assertFalse($this->storage->exists($victim), "victim key must be gone after delete (key: {$victim})");

        // Every other key must remain present with its exact original bytes.
        foreach ($keys as $key) {
            if ($key === $victim) {
                continue;
            }

            $this->assertTrue(
                $this->storage->exists($key),
                "delete('{$victim}') must not remove a different key '{$key}'"
            );
            $this->assertSame(
                $contents[$key],
                file_get_contents($this->root . '/' . $key),
                "delete('{$victim}') must not alter the bytes of a different key '{$key}'"
            );
        }
    }

    /**
     * Generator: seeded pseudo-random valid relative keys, mixing flat and
     * per-id categories, varied filenames and extensions.
     *
     * @return array<string, array{0:string}>
     */
    public function provideRandomKeys(): array
    {
        mt_srand(20240811);

        $cases = [];
        for ($i = 0; $i < 300; $i++) {
            $cases['key_' . $i] = [$this->randomKey()];
        }

        return $cases;
    }

    /**
     * Generator: seeded sets of DISTINCT valid keys plus the index of the key
     * to delete, so the "never affects any key ≠ k" property is exercised
     * across many shapes and set sizes.
     *
     * @return array<string, array{0:array<int,string>,1:int}>
     */
    public function provideDistinctKeySets(): array
    {
        mt_srand(20240812);

        $cases = [];
        for ($i = 0; $i < 120; $i++) {
            $size = mt_rand(2, 6);
            $set  = [];
            // Guarantee distinctness within the set.
            while (count($set) < $size) {
                $key = $this->randomKey();
                if (!in_array($key, $set, true)) {
                    $set[] = $key;
                }
            }

            $victimIndex = mt_rand(0, $size - 1);

            $cases['set_' . $i] = [$set, $victimIndex];
        }

        return $cases;
    }

    /**
     * Build a single random valid relative key matching the canonical pattern.
     */
    private function randomKey(): string
    {
        $flat  = ['documentostatus', 'evidencias', 'avatars', 'tramites'];
        $perId = ['pago_gestor', 'pago_derechos', 'cobro_cliente'];
        $exts  = ['jpg', 'pdf', 'png', 'jpeg', 'docx', 'zip'];

        $usePerId = mt_rand(0, 1) === 1;
        $category = $usePerId
            ? $perId[mt_rand(0, count($perId) - 1)]
            : $flat[mt_rand(0, count($flat) - 1)];

        $base = 'file_' . bin2hex(random_bytes(mt_rand(2, 5)));
        $ext  = $exts[mt_rand(0, count($exts) - 1)];
        $name = $base . '.' . $ext;

        if ($usePerId) {
            return $category . '/' . mt_rand(1, 999999) . '/' . $name;
        }

        return $category . '/' . $name;
    }

    /**
     * Write $content under $key via the driver's put(), using a temp source
     * file (non-upload source path, so put() uses rename()/copy()).
     */
    private function putKey(string $key, string $content): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'sgl-src-');
        $this->assertNotFalse($tmp, 'failed to create source temp file');
        file_put_contents($tmp, $content);

        $ok = $this->storage->put($key, $tmp);
        $this->assertTrue($ok, "put() should succeed for key: {$key}");

        // put() moves the source into place; clean up if anything remained.
        if (is_file($tmp)) {
            @unlink($tmp);
        }
    }

    /**
     * Recursively remove a directory tree created during the test.
     */
    private function removeTree(string $dir): void
    {
        if ($dir === '' || !is_dir($dir)) {
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

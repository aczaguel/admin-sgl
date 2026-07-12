<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\LocalFileStorage;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Contract tests for the Local driver (task 4.2).
 *
 * These tests exercise LocalFileStorage against a vfsStream-backed root so the
 * put/exists/url/delete contract is verified without touching the real
 * uploads directory. LocalFileStorage::put() uses is_uploaded_file() /
 * move_uploaded_file() for genuine HTTP uploads and falls back to
 * rename()/copy() for other sources; in the test harness the source temp file
 * is NOT an uploaded file, so the rename()->copy() fallback path is exercised.
 * rename() cannot cross the real-fs -> vfs:// stream boundary, so put()
 * transparently falls back to copy(), which vfsStream supports.
 *
 * Covers:
 *  - put -> exists -> url -> delete round-trip: after put() exists() is true
 *    and the byte payload lands on disk; after delete() exists() is false
 *    (Req 1.5, 2.6).
 *  - delete() idempotence: a second delete() on the same (now absent) key
 *    still returns true (Req 2.6-adjacent contract behavior).
 *  - Traversal rejection: keys containing a `..` segment, a leading `/`, or a
 *    backslash are rejected by every operation before any filesystem action -
 *    put/exists/delete return false and url() returns '' (Req 3.4).
 *  - url(): returns base_url('/assets/uploads/'.$key) with each path segment
 *    rawurlencoded, matching the current system exactly (Req 1.5).
 *
 * @internal
 */
final class LocalFileStorageContractTest extends CIUnitTestCase
{
    /** vfsStream root standing in for the uploads directory. */
    private vfsStreamDirectory $root;

    private LocalFileStorage $storage;

    /** Absolute paths of real temp files created during a test, cleaned up in tearDown. */
    private array $tmpFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper('url');

        $this->root = vfsStream::setup('uploads');

        $config            = new FileStorageConfig();
        $config->localRoot = $this->root->url(); // e.g. vfs://uploads
        $this->storage     = new LocalFileStorage($config);
    }

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $tmp) {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
        $this->tmpFiles = [];

        parent::tearDown();
    }

    /**
     * Create a real (non-upload) temp file holding $contents and return its path.
     * Because it is not an uploaded file, put() exercises the rename()->copy()
     * fallback rather than move_uploaded_file().
     */
    private function makeTempSource(string $contents): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'lfs_test_');
        $this->assertNotFalse($tmp, 'Could not create a temp source file');
        file_put_contents($tmp, $contents);
        $this->tmpFiles[] = $tmp;

        return $tmp;
    }

    /** Expected local URL: base_url('/assets/uploads/'.$key) with rawurlencoded segments. */
    private function expectedUrl(string $key): string
    {
        $encoded = implode('/', array_map('rawurlencode', explode('/', $key)));

        return base_url('/assets/uploads/' . $encoded);
    }

    // ---------------------------------------------------------------------
    // put -> exists -> url -> delete round-trip
    // ---------------------------------------------------------------------

    public function testPutExistsUrlDeleteRoundTrip(): void
    {
        $key     = 'pago_gestor/12472/comprobante_ab12.jpg';
        $payload = 'binary-image-bytes-' . bin2hex(random_bytes(8));
        $tmp     = $this->makeTempSource($payload);

        // Before put: nothing exists at the key.
        $this->assertFalse($this->storage->exists($key), 'Key should not exist before put()');

        // put() must succeed and land the exact bytes under the key on disk.
        $this->assertTrue($this->storage->put($key, $tmp), 'put() should succeed for a valid key');
        $this->assertTrue($this->storage->exists($key), 'exists() must be true after put()');
        $this->assertTrue($this->root->hasChild($key), 'File should be present on the vfs root under the key');
        $this->assertSame($payload, file_get_contents($this->root->url() . '/' . $key), 'Stored bytes must match the source payload');

        // url() reflects the current-system contract.
        $this->assertSame($this->expectedUrl($key), $this->storage->url($key), 'url() must match base_url(/assets/uploads/key)');

        // delete() removes the object; exists() is then false.
        $this->assertTrue($this->storage->delete($key), 'delete() should return true on an existing key');
        $this->assertFalse($this->storage->exists($key), 'exists() must be false after delete()');
    }

    public function testDeleteIsIdempotentOnAbsentKey(): void
    {
        $key = 'documentostatus/never_written_5001.pdf';

        // Deleting a key that was never written is a no-op that reports success.
        $this->assertFalse($this->storage->exists($key));
        $this->assertTrue($this->storage->delete($key), 'delete() on an absent key must return true');
        $this->assertTrue($this->storage->delete($key), 'repeated delete() must still return true');
        $this->assertFalse($this->storage->exists($key));
    }

    public function testDeleteOnlyAffectsTargetKey(): void
    {
        $keep   = 'evidencias/keep_me.png';
        $remove = 'evidencias/remove_me.png';

        $this->assertTrue($this->storage->put($keep, $this->makeTempSource('keep')));
        $this->assertTrue($this->storage->put($remove, $this->makeTempSource('remove')));

        $this->assertTrue($this->storage->delete($remove));

        $this->assertFalse($this->storage->exists($remove), 'Deleted key must be gone');
        $this->assertTrue($this->storage->exists($keep), 'A sibling key must be untouched by delete()');
    }

    // ---------------------------------------------------------------------
    // Traversal rejection (Req 3.4)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideTraversalKeys
     */
    public function testTraversalKeysAreRejectedByEveryOperation(string $unsafeKey): void
    {
        $tmp = $this->makeTempSource('should-never-be-written');

        // put() must refuse and write nothing.
        $this->assertFalse($this->storage->put($unsafeKey, $tmp), "put() must reject unsafe key: {$unsafeKey}");

        // exists() must return false without raising an error.
        $this->assertFalse($this->storage->exists($unsafeKey), "exists() must reject unsafe key: {$unsafeKey}");

        // delete() must refuse (returns false, not the idempotent true).
        $this->assertFalse($this->storage->delete($unsafeKey), "delete() must reject unsafe key: {$unsafeKey}");

        // url() must return '' for an unsafe key.
        $this->assertSame('', $this->storage->url($unsafeKey), "url() must return '' for unsafe key: {$unsafeKey}");
    }

    /**
     * Keys that must be rejected before any filesystem action: a `..` segment,
     * a leading `/`, a backslash, or an empty key.
     *
     * @return array<string, array{0: string}>
     */
    public function provideTraversalKeys(): array
    {
        return [
            'parent segment in middle'   => ['pago_gestor/../secret.jpg'],
            'parent segment leading'     => ['../etc/passwd'],
            'parent segment only'        => ['..'],
            'leading slash'              => ['/etc/passwd'],
            'leading slash with prefix'  => ['/assets/uploads/x.jpg'],
            'backslash windows path'     => ['pago_gestor\\12472\\x.jpg'],
            'backslash parent'           => ['..\\..\\secret.txt'],
            'empty key'                  => [''],
        ];
    }

    // ---------------------------------------------------------------------
    // url() encoding / base_url behavior (Req 1.5)
    // ---------------------------------------------------------------------

    /**
     * @dataProvider provideUrlKeys
     */
    public function testUrlMatchesBaseUrlWithRawUrlEncodedSegments(string $key): void
    {
        $this->assertSame($this->expectedUrl($key), $this->storage->url($key));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function provideUrlKeys(): array
    {
        return [
            'simple key'          => ['documentostatus/documento_5001.pdf'],
            'per-id key'          => ['pago_gestor/12472/comprobante_ab12.jpg'],
            'space in segment'    => ['pago gestor/archivo final.jpg'],
            'parentheses segment' => ['evidencias/archivo (1).png'],
            'ampersand and hash'  => ['tramites/a&b#c.jpg'],
            'single segment'      => ['avatar_1234.png'],
        ];
    }

    public function testUrlEncodesReservedCharactersPerSegmentWithoutEncodingSlashes(): void
    {
        $key = 'pago gestor/archivo (1).jpg';
        $url = $this->storage->url($key);

        // Segment separators stay as '/'; reserved chars within a segment are encoded.
        $this->assertStringContainsString('/assets/uploads/pago%20gestor/archivo%20%281%29.jpg', $url);
        $this->assertStringNotContainsString(' ', $url, 'Spaces must be percent-encoded');
    }

    public function testUrlUsesDefaultTtlSignatureWithoutAffectingLocalUrl(): void
    {
        // ttl is irrelevant to the local driver; the URL is identical regardless.
        $key = 'documentostatus/doc.pdf';
        $this->assertSame($this->storage->url($key), $this->storage->url($key, 900));
    }
}

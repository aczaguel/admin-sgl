<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\S3FileStorage;
use Aws\CommandInterface;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Property-based tests for the S3 driver against a STATEFUL in-memory object
 * store (no live AWS, no MockHandler queue).
 *
 * Covers:
 *   - Property 2: Round-trip       — Validates Requirements 2.1, 2.2
 *   - Property 3: Delete idempotence — Validates Requirements 2.3, 2.4
 *
 * The S3Client is backed by a custom `handler` closure over an associative
 * array keyed by object key. It dispatches on `$command->getName()`:
 *   - PutObject     stores the key           -> Result (success)
 *   - HeadObject    (used by doesObjectExist) -> Result when present, or a
 *                                                404 S3Exception when absent
 *                                                (doesObjectExist catches the
 *                                                404 and returns false)
 *   - DeleteObject  removes the key and succeeds even when absent (idempotent)
 *   - GetObject     returns success when present, 404 when absent
 *
 * This faithfully models the real S3 semantics the driver relies on, so the
 * properties are exercised across many random valid keys.
 *
 * PBT generators are implemented as seeded PHPUnit data providers so any
 * counterexample is reproducible and no new runtime dependency is introduced.
 *
 * @internal
 */
final class S3FileStorageRoundTripPropertyTest extends CIUnitTestCase
{
    /** Bucket used for every test client. */
    private const BUCKET = 'test-bucket';

    /** SSE algorithm configured for the driver. */
    private const SSE = 'AES256';

    /** The canonical key pattern the generated keys must satisfy (Req 3.1). */
    private const KEY_PATTERN = '#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#';

    /**
     * The in-memory object store: key => true when an object is present.
     *
     * @var array<string, bool>
     */
    private array $store = [];

    /** A real temp file so PutObject's SourceFile can be serialized offline. */
    private string $tmpFile = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->store   = [];
        $this->tmpFile = tempnam(sys_get_temp_dir(), 's3prop_');
        file_put_contents($this->tmpFile, 'property-test-bytes');
    }

    protected function tearDown(): void
    {
        if ($this->tmpFile !== '' && is_file($this->tmpFile)) {
            @unlink($this->tmpFile);
        }

        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // Property 2: Round-trip (Req 2.1, 2.2)
    // ---------------------------------------------------------------------

    /**
     * ∀ k: after put(k, tmp), exists(k) === true (Req 2.1);
     *      after delete(k), exists(k) === false (Req 2.2).
     *
     * @dataProvider provideKeys
     */
    public function testRoundTrip(string $key): void
    {
        // Guard: the generator must only emit keys the contract accepts.
        $this->assertSame(1, preg_match(self::KEY_PATTERN, $key), 'Generator produced an invalid key: ' . $key);

        $storage = $this->makeStorage();

        // Precondition: nothing stored at the key yet.
        $this->assertFalse($storage->exists($key), "key must not exist before put: {$key}");

        // Req 2.1: after a successful put the object exists.
        $this->assertTrue($storage->put($key, $this->tmpFile), "put() should succeed: {$key}");
        $this->assertTrue($storage->exists($key), "exists() must be true after put: {$key}");

        // Req 2.2: after delete the object no longer exists.
        $this->assertTrue($storage->delete($key), "delete() should succeed: {$key}");
        $this->assertFalse($storage->exists($key), "exists() must be false after delete: {$key}");
    }

    // ---------------------------------------------------------------------
    // Property 3: Delete idempotence (Req 2.3, 2.4)
    // ---------------------------------------------------------------------

    /**
     * ∀ k: delete(k) returns true whether or not k existed, and returns true
     * on every repeated invocation (Req 2.3, 2.4).
     *
     * @dataProvider provideKeys
     */
    public function testDeleteIsIdempotent(string $key): void
    {
        $this->assertSame(1, preg_match(self::KEY_PATTERN, $key), 'Generator produced an invalid key: ' . $key);

        $storage = $this->makeStorage();

        // Req 2.3: delete on a never-written key returns true.
        $this->assertTrue($storage->delete($key), "delete() on absent key must return true: {$key}");
        $this->assertFalse($storage->exists($key), "absent key must still not exist: {$key}");

        // put so the object exists, then repeatedly delete.
        $this->assertTrue($storage->put($key, $this->tmpFile), "put() should succeed: {$key}");
        $this->assertTrue($storage->exists($key), "key should exist after put: {$key}");

        // Req 2.4: repeated delete returns true on every invocation.
        $this->assertTrue($storage->delete($key), "first delete() must return true: {$key}");
        $this->assertTrue($storage->delete($key), "second delete() must return true: {$key}");
        $this->assertTrue($storage->delete($key), "third delete() must return true: {$key}");
        $this->assertFalse($storage->exists($key), "key must stay gone after repeated delete: {$key}");
    }

    /**
     * ∀ distinct k, k2: delete(k) never affects k2 (Req 2.3, 2.4).
     *
     * Given a set of distinct keys all put into the store, deleting one leaves
     * every other object present.
     *
     * @dataProvider provideDistinctKeySets
     *
     * @param array<int, string> $keys
     */
    public function testDeleteNeverAffectsOtherKeys(array $keys, int $victimIndex): void
    {
        $storage = $this->makeStorage();

        foreach ($keys as $key) {
            $this->assertTrue($storage->put($key, $this->tmpFile), "setup put() should succeed: {$key}");
            $this->assertTrue($storage->exists($key), "setup: key should exist after put: {$key}");
        }

        $victim = $keys[$victimIndex];

        $this->assertTrue($storage->delete($victim), "delete() of victim must return true: {$victim}");
        $this->assertFalse($storage->exists($victim), "victim must be gone after delete: {$victim}");

        foreach ($keys as $key) {
            if ($key === $victim) {
                continue;
            }

            $this->assertTrue(
                $storage->exists($key),
                "delete('{$victim}') must not remove a different key '{$key}'"
            );
        }
    }

    // ---------------------------------------------------------------------
    // Generators (seeded, reproducible)
    // ---------------------------------------------------------------------

    /**
     * Seeded pseudo-random valid relative keys: single- and multi-segment,
     * including the per-id `category/<id>/<file>` shape.
     *
     * @return array<string, array{0: string}>
     */
    public function provideKeys(): array
    {
        mt_srand(20240918);

        $categories = ['documentostatus', 'evidencias', 'avatars', 'tramites', 'pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $cases = [];
        for ($i = 0; $i < 300; $i++) {
            $cases['key_' . $i] = [$this->randomKey($categories)];
        }

        // Explicit edge cases alongside the random ones.
        $cases['edge_single_segment'] = ['avatars_only_file.png'];
        $cases['edge_deep_multi']     = ['a/b/c/d/e/file.name-1.2.3'];
        $cases['edge_dotted_name']    = ['documentostatus/a..b.jpg'];
        $cases['edge_per_id']         = ['pago_gestor/12472/comprobante_12472_abc123.jpg'];

        return $cases;
    }

    /**
     * Seeded sets of DISTINCT valid keys plus the index of the key to delete.
     *
     * @return array<string, array{0: array<int, string>, 1: int}>
     */
    public function provideDistinctKeySets(): array
    {
        mt_srand(20240919);

        $categories = ['documentostatus', 'evidencias', 'avatars', 'tramites', 'pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $cases = [];
        for ($i = 0; $i < 120; $i++) {
            $size = mt_rand(2, 6);
            $set  = [];
            while (count($set) < $size) {
                $key = $this->randomKey($categories);
                if (! in_array($key, $set, true)) {
                    $set[] = $key;
                }
            }

            $cases['set_' . $i] = [$set, mt_rand(0, $size - 1)];
        }

        return $cases;
    }

    /**
     * Build a valid relative key with 1..4 segments; every segment matches
     * `[A-Za-z0-9._-]+` and is never "..", so the key satisfies KEY_PATTERN.
     *
     * @param array<int, string> $categories
     */
    private function randomKey(array $categories): string
    {
        $shape = mt_rand(0, 2);

        if ($shape === 0) {
            return $this->randomSegment();
        }

        if ($shape === 1) {
            $category = $categories[mt_rand(0, count($categories) - 1)];

            return $category . '/' . mt_rand(1, 999999) . '/' . $this->randomSegment();
        }

        $segments   = [];
        $segmentCnt = mt_rand(2, 4);
        for ($s = 0; $s < $segmentCnt; $s++) {
            $segments[] = $this->randomSegment();
        }

        return implode('/', $segments);
    }

    /**
     * A single non-empty path segment from `[A-Za-z0-9._-]`, never "." or "..".
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

    // ---------------------------------------------------------------------
    // Stateful in-memory S3 client
    // ---------------------------------------------------------------------

    /**
     * Build an S3FileStorage backed by a stateful in-memory object store.
     *
     * Dummy static credentials keep any presigning offline; the custom handler
     * replaces the network layer entirely so no request ever leaves.
     */
    private function makeStorage(): S3FileStorage
    {
        $config         = new FileStorageConfig();
        $config->bucket = self::BUCKET;
        $config->region = 'us-east-1';
        $config->sse    = self::SSE;

        $client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $this->makeHandler(),
        ]);

        return new S3FileStorage($config, $client);
    }

    /**
     * A handler closure over $this->store that models a stateful object store.
     * Dispatches on the command name and returns an Aws\Result (success) or a
     * rejected 404 S3Exception (absent, for HeadObject/GetObject).
     */
    private function makeHandler(): callable
    {
        $store = &$this->store;

        return static function (CommandInterface $command, RequestInterface $request) use (&$store) {
            $name = $command->getName();
            $key  = (string) ($command['Key'] ?? '');

            switch ($name) {
                case 'PutObject':
                    $store[$key] = true;

                    return new FulfilledPromise(new Result([]));

                case 'DeleteObject':
                    // Idempotent: succeeds whether or not the key was present.
                    unset($store[$key]);

                    return new FulfilledPromise(new Result([]));

                case 'HeadObject':
                case 'GetObject':
                    if (isset($store[$key])) {
                        return new FulfilledPromise(new Result([]));
                    }

                    return new RejectedPromise(new S3Exception(
                        'Not Found',
                        $command,
                        ['code' => 'NotFound', 'response' => new Response(404)]
                    ));

                default:
                    // Any unexpected command surfaces as an error.
                    return new RejectedPromise(new S3Exception(
                        'Unexpected command: ' . $name,
                        $command,
                        ['code' => 'Unexpected', 'response' => new Response(500)]
                    ));
            }
        };
    }
}

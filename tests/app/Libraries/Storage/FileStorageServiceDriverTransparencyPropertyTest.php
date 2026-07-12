<?php

namespace Tests\App\Libraries\Storage;

use App\Libraries\Storage\FileStorageService;
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
 * Property-based test for Property 1: Driver transparency.
 *
 * Validates: Requirements 1.4, 1.5
 *
 * For every relative key `k` and payload bytes, the sequence `put(k, tmp)`
 * then `url(k)` yields a URL that resolves to the bytes stored by that put,
 * regardless of FILE_STORAGE_DRIVER:
 *
 *   ∀ k, driver ∈ {local, s3}: readable(url(k)) === bytes put under k.
 *
 * Both legs are exercised through the SAME facade — FileStorageService — which
 * selects the Active_Driver purely from `$config->driver` (Req 1.2/1.3), so the
 * caller uses one interface irrespective of the backing store.
 *
 * local leg (Req 1.5):
 *   - `$config->driver = 'local'`, `$config->localRoot` = a real temp dir.
 *   - After put, `url(k)` equals `base_url('/assets/uploads/' + rawurlencoded k)`
 *     (identical to the current system), and the file at `localRoot/k` on disk
 *     holds exactly the payload — i.e. the URL path corresponds to those bytes.
 *
 * s3 leg (mocked, offline):
 *   - `$config->driver = 's3'` with an injected S3FileStorage backed by a
 *     STATEFUL in-memory object store (custom handler closure, dummy static
 *     creds — no live AWS, nothing leaves the process).
 *   - After put, the backing store holds the exact bytes under `k` (the object
 *     is retrievable), and `url(k)` returns a non-empty, SigV4-signed presigned
 *     GetObject URL that encodes the bucket and the key. A real HTTP GET is not
 *     possible offline, so "resolves to the stored bytes" is modeled as: the
 *     store holds the put bytes under `k`, and `url()` yields a valid presigned
 *     GetObject URL for that same `k`/bucket.
 *
 * PBT generators are implemented as seeded PHPUnit data providers so any
 * counterexample is reproducible and no new runtime dependency is introduced.
 *
 * @internal
 */
final class FileStorageServiceDriverTransparencyPropertyTest extends CIUnitTestCase
{
    /** Bucket used for the S3 leg. */
    private const BUCKET = 'test-bucket';

    /** Region used for the S3 leg. */
    private const REGION = 'us-east-1';

    /** SSE algorithm configured for the S3 driver. */
    private const SSE = 'AES256';

    /** The canonical key pattern the generated keys must satisfy (Req 3.1). */
    private const KEY_PATTERN = '#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#';

    /** Absolute root directory handed to the local driver for this run. */
    private string $root = '';

    /** Temp source files created during a test, cleaned up in tearDown. */
    private array $tmpSources = [];

    /**
     * In-memory S3 object store: key => raw stored bytes.
     *
     * @var array<string, string>
     */
    private array $store = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/fss_transp_' . bin2hex(random_bytes(6));
        if (! is_dir($this->root)) {
            mkdir($this->root, 0777, true);
        }

        $this->store = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->tmpSources as $src) {
            if (is_file($src)) {
                @unlink($src);
            }
        }
        $this->tmpSources = [];

        $this->removeTree($this->root);

        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // Property 1: Driver transparency (Req 1.4, 1.5)
    // ---------------------------------------------------------------------

    /**
     * ∀ k, payload, driver ∈ {local, s3}: after put(k, tmp) the URL from
     * url(k) resolves to exactly the bytes stored by that put.
     *
     * @dataProvider provideKeysAndPayloads
     */
    public function testDriverTransparency(string $key, string $payload): void
    {
        // Guard: the generator must only emit keys the contract accepts.
        $this->assertSame(1, preg_match(self::KEY_PATTERN, $key), 'Generator produced an invalid key: ' . $key);

        $message = sprintf('key=%s payloadLen=%d', $key, strlen($payload));

        $this->assertLocalTransparency($key, $payload, $message);
        $this->assertS3Transparency($key, $payload, $message);
    }

    /**
     * local leg (Req 1.5): url(k) is the legacy base_url path and the file at
     * localRoot/k holds exactly the payload the put stored.
     */
    private function assertLocalTransparency(string $key, string $payload, string $message): void
    {
        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->localRoot = $this->root;

        // FileStorageService selects the local driver purely from config.
        $service = new FileStorageService($config);

        // put() consumes the source, so use a fresh temp file holding payload.
        $src = $this->makeTmpSource($payload);
        $this->assertTrue($service->put($key, $src), 'local put() returned false. ' . $message);

        // url(k) equals the current system's base_url('/assets/uploads/'+key).
        $encoded  = implode('/', array_map('rawurlencode', explode('/', $key)));
        $expected = base_url('/assets/uploads/' . $encoded);
        $url      = $service->url($key);
        $this->assertSame($expected, $url, 'local url() must match legacy base_url path. ' . $message);

        // The URL path corresponds to the stored file, which holds the bytes.
        $storedPath = rtrim($this->root, '/') . '/' . $key;
        $this->assertFileExists($storedPath, 'local stored file missing. ' . $message);
        $this->assertSame($payload, file_get_contents($storedPath), 'local stored bytes differ from payload. ' . $message);

        // The url path segment (decoded) is exactly the key -> URL maps to that file.
        $path        = parse_url($url, PHP_URL_PATH) ?? '';
        $decodedPath = rawurldecode($path);
        $this->assertStringEndsWith('/assets/uploads/' . $key, $decodedPath, 'local url path must address the stored key. ' . $message);
    }

    /**
     * s3 leg (mocked): the backing store holds exactly the put bytes under k,
     * and url(k) returns a valid presigned GetObject URL encoding bucket + key.
     */
    private function assertS3Transparency(string $key, string $payload, string $message): void
    {
        // Fresh store per key so preconditions are clean.
        $this->store = [];

        $config         = new FileStorageConfig();
        $config->driver = 's3';
        $config->bucket = self::BUCKET;
        $config->region = self::REGION;
        $config->sse    = self::SSE;

        $client   = $this->makeMockClient();
        $s3Driver = new S3FileStorage($config, $client);

        // Inject the mocked S3 driver via the facade's 3rd constructor arg.
        // With driver='s3' the facade selects this as the Active_Driver.
        $service = new FileStorageService($config, null, $s3Driver);

        $src = $this->makeTmpSource($payload);
        $this->assertTrue($service->put($key, $src), 's3 put() returned false. ' . $message);

        // The object is retrievable from the backing store with the exact bytes.
        $this->assertArrayHasKey($key, $this->store, 's3 store must hold the object after put. ' . $message);
        $this->assertSame($payload, $this->store[$key], 's3 stored bytes differ from payload. ' . $message);
        $this->assertTrue($service->exists($key), 's3 exists() must be true after put. ' . $message);

        // url(k): a non-empty, SigV4-signed presigned GetObject URL for k/bucket.
        $url = $service->url($key);
        $this->assertNotSame('', $url, 's3 url() must produce a presigned URL. ' . $message);
        $this->assertStringStartsWith('https://', $url, 's3 presigned URL must be https. ' . $message);
        $this->assertStringContainsString('X-Amz-Signature', $url, 's3 presigned URL must be SigV4-signed. ' . $message);
        $this->assertStringContainsString(self::BUCKET, $url, 's3 presigned URL must encode the bucket. ' . $message);

        // Generated keys use only unreserved chars, so each segment appears
        // verbatim in the URL path -> the URL addresses the stored object.
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        foreach (explode('/', $key) as $segment) {
            $this->assertStringContainsString($segment, $path, 's3 presigned URL path must encode the key. ' . $message);
        }
    }

    // ---------------------------------------------------------------------
    // Generator (seeded, reproducible)
    // ---------------------------------------------------------------------

    /**
     * Seeded pseudo-random (key, payload) pairs: single- and multi-segment
     * keys (including the per-id `category/<id>/<file>` shape) and payloads of
     * varying length, including empty, text, and binary content.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public function provideKeysAndPayloads(): array
    {
        mt_srand(20240920);

        $categories = ['documentostatus', 'evidencias', 'avatars', 'tramites', 'pago_gestor', 'pago_derechos', 'cobro_cliente'];

        $cases = [];
        for ($i = 0; $i < 150; $i++) {
            $cases['case_' . $i] = [$this->randomKey($categories), $this->randomPayload()];
        }

        // Explicit edge cases alongside the random ones.
        $cases['edge_single_segment'] = ['avatars_only_file.png', ''];
        $cases['edge_deep_multi']     = ['a/b/c/d/e/file.name-1.2.3', 'deep'];
        $cases['edge_dotted_name']    = ['documentostatus/a..b.jpg', 'dots-in-name-not-a-traversal'];
        $cases['edge_per_id']         = ['pago_gestor/12472/comprobante_12472_abc123.jpg', "binary\x00\xff\x01bytes"];

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

    /**
     * Random payload bytes: sometimes empty, sometimes text, sometimes binary.
     */
    private function randomPayload(): string
    {
        $kind = mt_rand(0, 3);

        if ($kind === 0) {
            return '';
        }

        $len = mt_rand(1, 4096);

        if ($kind === 1) {
            $out = '';
            for ($i = 0; $i < $len; $i++) {
                $out .= chr(mt_rand(32, 126));
            }

            return $out;
        }

        return random_bytes($len);
    }

    // ---------------------------------------------------------------------
    // Helpers: temp sources, stateful S3 client, dir cleanup
    // ---------------------------------------------------------------------

    /**
     * Write $payload to a fresh temp file and track it for cleanup. The file
     * lives outside the driver root so the local put()'s rename cannot collide.
     */
    private function makeTmpSource(string $payload): string
    {
        $src = sys_get_temp_dir() . '/fss_src_' . bin2hex(random_bytes(8));
        file_put_contents($src, $payload);
        $this->tmpSources[] = $src;

        return $src;
    }

    /**
     * Build an S3Client backed by a stateful in-memory object store. Dummy
     * static credentials keep presigning fully offline; the custom handler
     * replaces the network layer so no request ever leaves the process.
     */
    private function makeMockClient(): S3Client
    {
        return new S3Client([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $this->makeHandler(),
        ]);
    }

    /**
     * Handler closure over $this->store modeling a stateful object store that
     * retains the PUT bytes so the object is retrievable under its key.
     * Dispatches on the command name:
     *   - PutObject   captures the request-body bytes -> Result (success)
     *   - HeadObject  Result when present, else 404 (doesObjectExist -> false)
     *   - GetObject   the stored bytes when present, else 404
     *   - DeleteObject removes the key, succeeds even when absent (idempotent)
     */
    private function makeHandler(): callable
    {
        $store = &$this->store;

        return static function (CommandInterface $command, RequestInterface $request) use (&$store) {
            $name = $command->getName();
            $key  = (string) ($command['Key'] ?? '');

            switch ($name) {
                case 'PutObject':
                    // Capture the exact bytes the driver uploaded for this key.
                    $store[$key] = (string) $request->getBody();

                    return new FulfilledPromise(new Result([]));

                case 'DeleteObject':
                    unset($store[$key]);

                    return new FulfilledPromise(new Result([]));

                case 'HeadObject':
                    if (isset($store[$key])) {
                        return new FulfilledPromise(new Result([]));
                    }

                    return new RejectedPromise(new S3Exception(
                        'Not Found',
                        $command,
                        ['code' => 'NotFound', 'response' => new Response(404)]
                    ));

                case 'GetObject':
                    if (isset($store[$key])) {
                        return new FulfilledPromise(new Result(['Body' => $store[$key]]));
                    }

                    return new RejectedPromise(new S3Exception(
                        'Not Found',
                        $command,
                        ['code' => 'NotFound', 'response' => new Response(404)]
                    ));

                default:
                    return new RejectedPromise(new S3Exception(
                        'Unexpected command: ' . $name,
                        $command,
                        ['code' => 'Unexpected', 'response' => new Response(500)]
                    ));
            }
        };
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

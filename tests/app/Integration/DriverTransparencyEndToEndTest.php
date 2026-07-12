<?php

namespace Tests\App\Integration;

use App\Libraries\Storage\FileStorageService;
use App\Libraries\Storage\S3FileStorage;
use Aws\CommandInterface;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CodeIgniter\Test\CIUnitTestCase;
use Config\FileStorage as FileStorageConfig;
use Config\Services;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Integration test — end-to-end driver transparency (Property 1).
 *
 * Validates: Requirements 1.4 (and 1.5 for the local leg).
 *
 * Exercises the WHOLE storage stack together, the way the application uses it:
 *
 *   buildKey(category, id, name)            (app/Helpers/filestorage_helper.php)
 *       -> service('fileStorage')->put(key, tmp)   (FileStorageService facade)
 *       -> persist the canonical DB value (bare filename, as legacy fields store)
 *       -> render via file_url(storedValue, category, id)  (URL_Resolver helper)
 *
 * The SAME FileStorageService instance is injected as the `fileStorage` shared
 * service via Services::injectMock(), so file_url() (which internally calls
 * service('fileStorage')->url()) resolves through exactly the driver under test.
 * We run the identical end-to-end flow twice — once with FILE_STORAGE_DRIVER
 * toggled to `local` (real temp localRoot) and once toggled to `s3` (a stateful
 * in-memory S3FileStorage, no live AWS) — and assert the rendered URL resolves
 * to the bytes that were stored:
 *
 *   local (Req 1.5): file_url == base_url('/assets/uploads/'+key) AND the file
 *                    at localRoot/key on disk holds exactly the payload.
 *   s3 (Req 1.4):    the in-memory store holds exactly the payload under key AND
 *                    file_url returns a presigned GetObject URL for that key.
 *
 * Only FILE_STORAGE_DRIVER (via the config handed to the facade) differs between
 * the two runs; the application-level flow (buildKey/put/store/file_url) and the
 * stored DB value are byte-for-byte identical, which is the transparency claim.
 *
 * @internal
 */
final class DriverTransparencyEndToEndTest extends CIUnitTestCase
{
    private const BUCKET = 'sgl-uploads-test';
    private const REGION = 'us-east-1';
    private const SSE    = 'AES256';

    /** Absolute local uploads root used by the local leg. */
    private string $root = '';

    /** Temp source files created during a test, removed in tearDown. */
    private array $tmpSources = [];

    /**
     * In-memory S3 object store: key => stored bytes.
     *
     * @var array<string, string>
     */
    private array $store = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Load the storage helper so buildKey()/keyFromStored()/file_url() are
        // available (these are global functions defined in the helper file).
        helper('filestorage');

        $this->root = sys_get_temp_dir() . '/e2e_transp_' . bin2hex(random_bytes(6));
        if (! is_dir($this->root)) {
            mkdir($this->root, 0777, true);
        }
        $this->store = [];
    }

    protected function tearDown(): void
    {
        // Reset the shared service so an injected mock never leaks between tests.
        Services::reset(true);

        foreach ($this->tmpSources as $src) {
            if (is_file($src)) {
                @unlink($src);
            }
        }
        $this->tmpSources = [];

        $this->removeTree($this->root);

        parent::tearDown();
    }

    /**
     * The same end-to-end upload→DB→render flow yields a URL that resolves to
     * the stored bytes under BOTH drivers, with only the driver flag toggled.
     *
     * @dataProvider provideUploadScenarios
     *
     * @param string   $category the storage category (per-id or flat)
     * @param int|null $id       tramite id for per-id categories, null otherwise
     * @param string   $name     the client-supplied original filename
     * @param string   $payload  the uploaded bytes
     */
    public function testUploadRenderIsTransparentAcrossDrivers(string $category, ?int $id, string $name, string $payload): void
    {
        // The canonical relative key is derived identically regardless of driver.
        $key = buildKey($category, $id, $name);

        // The DB stores the canonical bare filename for these legacy fields; the
        // key's last segment is that filename. file_url() rebuilds the key from
        // it plus the category/id, so the DB value is driver-independent.
        $storedValue = basename($key);

        $this->assertSame(
            $key,
            keyFromStored($storedValue, $category, $id),
            'stored bare filename must rebuild the canonical key for both drivers'
        );

        // --- Leg A: FILE_STORAGE_DRIVER = local -------------------------------
        $this->runLocalLeg($category, $id, $key, $storedValue, $payload);

        // --- Leg B: FILE_STORAGE_DRIVER = s3 (mocked, offline) ----------------
        $this->runS3Leg($category, $id, $key, $storedValue, $payload);
    }

    /**
     * local leg (Req 1.5): render URL is the legacy base_url path AND the file
     * on disk under localRoot/key holds exactly the uploaded payload.
     */
    private function runLocalLeg(string $category, ?int $id, string $key, string $storedValue, string $payload): void
    {
        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->localRoot = $this->root;

        $service = new FileStorageService($config);

        // Inject as the shared service so file_url() resolves through it.
        Services::injectMock('fileStorage', $service);

        $src = $this->makeTmpSource($payload);
        $this->assertTrue($service->put($key, $src), 'local put() should succeed');

        // Render exactly as a view would, from the DB value + category/id.
        $rendered = file_url($storedValue, $category, $id);

        $encoded  = implode('/', array_map('rawurlencode', explode('/', $key)));
        $expected = base_url('/assets/uploads/' . $encoded);
        $this->assertSame($expected, $rendered, 'local file_url() must equal the legacy base_url path');

        // The URL addresses the on-disk file, which holds the payload bytes.
        $storedPath = rtrim($this->root, '/') . '/' . $key;
        $this->assertFileExists($storedPath, 'local object must exist on disk');
        $this->assertSame($payload, file_get_contents($storedPath), 'local bytes must equal the payload');

        $decodedPath = rawurldecode((string) parse_url($rendered, PHP_URL_PATH));
        $this->assertStringEndsWith('/assets/uploads/' . $key, $decodedPath, 'local URL path must address the key');
    }

    /**
     * s3 leg (Req 1.4): the in-memory store holds the payload under key AND the
     * rendered URL is a presigned GetObject URL that encodes bucket + key.
     */
    private function runS3Leg(string $category, ?int $id, string $key, string $storedValue, string $payload): void
    {
        // Fresh service resolution + store for a clean precondition.
        Services::reset(true);
        $this->store = [];

        $config         = new FileStorageConfig();
        $config->driver = 's3';
        $config->bucket = self::BUCKET;
        $config->region = self::REGION;
        $config->sse    = self::SSE;

        $s3Driver = new S3FileStorage($config, $this->makeStatefulClient());
        // driver='s3' => facade selects the injected S3 driver as Active_Driver.
        $service  = new FileStorageService($config, null, $s3Driver);

        Services::injectMock('fileStorage', $service);

        $src = $this->makeTmpSource($payload);
        $this->assertTrue($service->put($key, $src), 's3 put() should succeed');

        // The object is retrievable from the backing store with the exact bytes.
        $this->assertArrayHasKey($key, $this->store, 's3 store must hold the object after put');
        $this->assertSame($payload, $this->store[$key], 's3 stored bytes must equal the payload');
        $this->assertTrue($service->exists($key), 's3 exists() must be true after put');

        // Render exactly as a view would.
        $rendered = file_url($storedValue, $category, $id);

        $this->assertNotSame('', $rendered, 's3 file_url() must produce a presigned URL');
        $this->assertStringStartsWith('https://', $rendered, 's3 URL must be https');
        $this->assertStringContainsString('X-Amz-Signature', $rendered, 's3 URL must be SigV4-signed');
        $this->assertStringContainsString(self::BUCKET, $rendered, 's3 URL must encode the bucket');

        $path = (string) parse_url($rendered, PHP_URL_PATH);
        foreach (explode('/', $key) as $segment) {
            $this->assertStringContainsString($segment, $path, 's3 URL path must encode the key');
        }
    }

    /**
     * Concrete upload scenarios spanning per-id and flat categories, text and
     * binary payloads, and messy original filenames the Key_Builder sanitizes.
     *
     * @return array<string, array{0: string, 1: int|null, 2: string, 3: string}>
     */
    public function provideUploadScenarios(): array
    {
        return [
            'pago_gestor per-id jpg'        => ['pago_gestor', 12472, 'Comprobante Pago.jpg', "binary\x00\xff\x01bytes"],
            'pago_derechos per-id pdf'      => ['pago_derechos', 8801, 'derechos final (v2).pdf', str_repeat('D', 2048)],
            'cobro_cliente per-id png'      => ['cobro_cliente', 501, 'recibo#cliente.png', 'recibo-bytes'],
            'documentostatus flat pdf'      => ['documentostatus', null, 'documento 5001 11.pdf', 'pdf-content-here'],
            'evidencias flat png'           => ['evidencias', null, 'evidencia@escena!.png', random_bytes(777)],
            'avatars flat jpg'              => ['avatars', null, 'MiFoto Perfil.JPG', 'avatar-bytes'],
            'tramites per-id (flat rebuild) => key' => ['tramites', 3, 'oficio.docx', 'x'],
        ];
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function makeTmpSource(string $payload): string
    {
        $src = sys_get_temp_dir() . '/e2e_src_' . bin2hex(random_bytes(8));
        file_put_contents($src, $payload);
        $this->tmpSources[] = $src;

        return $src;
    }

    /**
     * S3Client backed by a stateful in-memory object store. Dummy static creds
     * keep presigning offline; the handler closure replaces the network layer.
     */
    private function makeStatefulClient(): S3Client
    {
        $store = &$this->store;

        $handler = static function (CommandInterface $command, RequestInterface $request) use (&$store) {
            $name = $command->getName();
            $key  = (string) ($command['Key'] ?? '');

            switch ($name) {
                case 'PutObject':
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

        return new S3Client([
            'version'     => 'latest',
            'region'      => self::REGION,
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler'     => $handler,
        ]);
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

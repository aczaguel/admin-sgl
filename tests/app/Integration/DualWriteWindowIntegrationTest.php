<?php

namespace Tests\App\Integration;

use App\Libraries\Storage\FileStorageService;
use App\Libraries\Storage\LocalFileStorage;
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
 * Integration test — dual-write window (Req 9.1).
 *
 * During the migration window an operator may enable FILE_STORAGE_DUAL_WRITE so
 * that any upload made while `aws s3 sync` is running lands in BOTH stores and
 * is not lost. This test wires the whole facade with two REAL legs:
 *
 *   - local leg: a real LocalFileStorage rooted at a temp uploads dir
 *     (injected as the facade's 2nd constructor arg), and
 *   - s3 leg:    a real S3FileStorage backed by a STATEFUL in-memory object
 *     store (injected as the facade's 3rd constructor arg, no live AWS).
 *
 * With `dualWrite = true` a single `put(key, tmp)` must persist the object to
 * BOTH stores under the identical relative key:
 *
 *   ∀ key: after put(key, tmp)  =>  file exists at localRoot/key
 *                                   AND the in-memory s3 store holds key
 *                                   AND both hold exactly the uploaded bytes.
 *
 * As a control, the same wiring with `dualWrite = false` must write ONLY to the
 * active driver, proving the window is opt-in (Req 9.2/9.4).
 *
 * @internal
 */
final class DualWriteWindowIntegrationTest extends CIUnitTestCase
{
    private const BUCKET = 'sgl-uploads-test';
    private const REGION = 'us-east-1';
    private const SSE    = 'AES256';

    /** Absolute local uploads root for the real LocalFileStorage leg. */
    private string $root = '';

    /** Temp source files, removed in tearDown. */
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

        $this->root = sys_get_temp_dir() . '/e2e_dualwrite_' . bin2hex(random_bytes(6));
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

    /**
     * With dual-write enabled, a new upload lands in BOTH stores under the same
     * key with identical bytes.
     *
     * @dataProvider provideKeysAndPayloads
     */
    public function testNewUploadLandsInBothStores(string $key, string $payload): void
    {
        $config            = new FileStorageConfig();
        $config->driver    = 'local';           // active driver = local during the window
        $config->localRoot = $this->root;
        $config->bucket    = self::BUCKET;
        $config->region    = self::REGION;
        $config->sse       = self::SSE;
        $config->dualWrite = true;

        $local = new LocalFileStorage($config);
        $s3    = new S3FileStorage($config, $this->makeStatefulClient());

        // Inject BOTH real legs; the facade reuses them for the dual-write legs.
        $service = new FileStorageService($config, $local, $s3);

        $src = $this->makeTmpSource($payload);
        $this->assertTrue($service->put($key, $src), 'dual-write put() should succeed when both legs succeed');

        // Local store holds the object with the exact bytes.
        $localPath = rtrim($this->root, '/') . '/' . $key;
        $this->assertFileExists($localPath, 'object must land on the local disk');
        $this->assertSame($payload, file_get_contents($localPath), 'local bytes must equal the payload');

        // S3 store holds the object with the exact bytes.
        $this->assertArrayHasKey($key, $this->store, 'object must land in the in-memory s3 store');
        $this->assertSame($payload, $this->store[$key], 's3 bytes must equal the payload');

        // The single upload is discoverable through both legs individually.
        $this->assertTrue($local->exists($key), 'local leg exists() must be true');
        $this->assertTrue($s3->exists($key), 's3 leg exists() must be true');
    }

    /**
     * Control: with dual-write DISABLED the same wiring writes to the active
     * (local) leg only — the s3 store stays empty (Req 9.2/9.4).
     */
    public function testDisabledDualWriteWritesActiveDriverOnly(): void
    {
        $key     = 'pago_gestor/12472/comprobante_12472_ab12cd34.jpg';
        $payload = 'single-store-bytes';

        $config            = new FileStorageConfig();
        $config->driver    = 'local';
        $config->localRoot = $this->root;
        $config->bucket    = self::BUCKET;
        $config->region    = self::REGION;
        $config->sse       = self::SSE;
        $config->dualWrite = false;

        $local = new LocalFileStorage($config);
        $s3    = new S3FileStorage($config, $this->makeStatefulClient());

        $service = new FileStorageService($config, $local, $s3);

        $src = $this->makeTmpSource($payload);
        $this->assertTrue($service->put($key, $src), 'single-store put() should succeed');

        $localPath = rtrim($this->root, '/') . '/' . $key;
        $this->assertFileExists($localPath, 'object must land on the active (local) store');
        $this->assertSame([], $this->store, 's3 store must remain empty when dual-write is off');
    }

    /**
     * Keys spanning the per-id and flat layouts, with text and binary payloads.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public function provideKeysAndPayloads(): array
    {
        return [
            'per-id jpg'        => ['pago_gestor/12472/comprobante_12472_abc12345.jpg', "binary\x00\xff\x02bytes"],
            'per-id pdf'        => ['pago_derechos/8801/derechos_8801_def67890.pdf', str_repeat('P', 4096)],
            'flat documento'    => ['documentostatus/documento_5001_11_a1b2c3d4.pdf', 'pdf-bytes'],
            'flat evidencia'    => ['evidencias/evidencia_x9y8z7w6.png', random_bytes(1234)],
            'single segment'    => ['avatars_only_abc12345.png', ''],
            'deep nested'       => ['tramites/3/oficio_3_0f1e2d3c.docx', 'docx-bytes'],
        ];
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function makeTmpSource(string $payload): string
    {
        $src = sys_get_temp_dir() . '/dw_src_' . bin2hex(random_bytes(8));
        file_put_contents($src, $payload);
        $this->tmpSources[] = $src;

        return $src;
    }

    /**
     * S3Client backed by a stateful in-memory object store (offline). The S3
     * leg runs FIRST in the facade's dual-write put (it reads the temp file in
     * place), so the handler captures the exact uploaded bytes before the local
     * leg consumes the source.
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

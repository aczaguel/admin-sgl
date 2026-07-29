<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\FileStorage as FileStorageConfig;
use Config\Services;
use Psr\Log\NullLogger;
use Tests\Support\Controllers\TestableConcluido;
use Tests\Support\Controllers\TestableTramites;

/**
 * Integration tests for the `getFiles` endpoints across BOTH drivers.
 *
 * These are higher-level tests than the unit tests in GetFilesEndpointTest:
 * they exercise the REAL endpoint methods with seeded DB rows and verify:
 *
 * - S3 driver: `existing_path` is a presigned URL (https://bucket...),
 *   no `/assets/uploads/` substring present, no present DB row is dropped.
 * - Local driver: output is byte-identical to the expected format
 *   (`base_url + per-segment encoded key`), includes `size` field.
 * - ACL guards: when session lacks required permissions, endpoint returns
 *   403 with error payload (no file data leaked).
 * - Re-resolve on every invocation (Req 9.6): calling the endpoint twice
 *   produces freshly generated URLs on each call.
 *
 * Validates: Requirements 2.2, 2.3, 3.3, 3.4, 9.6, 10.3, 10.4, 11.5
 *
 * @internal
 */
final class GetFilesEndpointIntegrationTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    /** @var string[] absolute paths of files created on disk for local-gate tests */
    private array $createdFiles = [];

    /** @var string[] absolute directories created for local-gate tests */
    private array $createdDirs = [];

    protected function setUp(): void
    {
        parent::setUp();

        helper(['filestorage', 'permissions', 'cliente_filter', 'acl_guard']);

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
        $this->recreateTables();

        $this->seedSession();
        $this->seedTenantAccess();
        $this->seedTramite(200);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        foreach ($this->createdDirs as $directory) {
            if (is_dir($directory)) {
                @rmdir($directory);
            }
        }
        $this->createdFiles = [];
        $this->createdDirs = [];

        foreach (['tra_pago_gestor', 'tra_pago_derechos', 'tra_cobro_cliente', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        Factories::reset('config');
        Services::reset(true);

        parent::tearDown();
    }

    // ==================================================================
    // S3 DRIVER: presigned URLs, no local path, no row dropped
    // ==================================================================

    /**
     * Under s3, existing_path is a presigned URL and never contains
     * /assets/uploads/.
     *
     * Validates: Requirements 2.2, 2.3
     */
    public function testS3DriverReturnsPresignedUrlsWithoutLocalPath(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        $this->seedFileRow('tra_pago_gestor', 200, 'comprobante_pago.jpg');
        $this->seedFileRow('tra_pago_gestor', 200, 'factura_2024.pdf');

        $payload = $this->callEndpoint(new TestableConcluido(), 'getPagoGestorFiles', 200);

        $this->assertCount(2, $payload, 'S3 driver must not drop any present DB row');

        foreach ($payload as $entry) {
            // existing_path starts with https://bucket (presigned URL)
            $this->assertStringStartsWith(
                'https://bucket.s3.amazonaws.com/',
                $entry['existing_path'],
                'existing_path must be a presigned URL under s3'
            );
            // No local upload path leakage
            $this->assertStringNotContainsString(
                '/assets/uploads/',
                $entry['existing_path'],
                'existing_path must not contain /assets/uploads/ under s3'
            );
            // icon for non-images should not contain /assets/uploads/ either
            // (it's a static icon path or a presigned URL)
            $this->assertStringNotContainsString(
                '/assets/uploads/',
                $entry['icon'],
                'icon must not contain /assets/uploads/ under s3'
            );
        }
    }

    /**
     * Under s3, no present DB row with a non-empty filename is dropped from
     * the response, even though no file exists on the local disk.
     *
     * Validates: Requirements 10.3, 10.4
     */
    public function testS3DriverDoesNotDropPresentDbRows(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        // Seed multiple rows, none with files on local disk.
        $this->seedFileRow('tra_pago_derechos', 200, 'recibo_derechos.pdf');
        $this->seedFileRow('tra_pago_derechos', 200, 'constancia.jpg');
        $this->seedFileRow('tra_pago_derechos', 200, 'arancel.png');

        $payload = $this->callEndpoint(new TestableConcluido(), 'getPagoDerechosFiles', 200);

        // All 3 rows returned (local gate is skipped)
        $this->assertCount(3, $payload, 'S3 must not filter out rows based on local-disk existence');

        // size field must be omitted (no filesize probe under s3)
        foreach ($payload as $entry) {
            $this->assertArrayNotHasKey('size', $entry, 'size must be omitted under s3 driver');
        }
    }

    /**
     * Under s3, cobro_cliente endpoint (Tramites controller) also returns
     * presigned existing_path with no local paths.
     *
     * Validates: Requirements 2.2, 2.3
     */
    public function testS3DriverTramitesCobroClientePresignedUrls(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        $this->seedFileRow('tra_cobro_cliente', 200, 'deposito_bancario.jpg', 'completo');
        $this->seedFileRow('tra_cobro_cliente', 200, 'contrato_firmado.pdf', 'parcial');

        $payload = $this->callEndpoint(new TestableTramites(), 'getCobroClienteFiles', 200);

        $this->assertCount(2, $payload);

        foreach ($payload as $entry) {
            $this->assertStringStartsWith('https://bucket.s3.amazonaws.com/', $entry['existing_path']);
            $this->assertStringNotContainsString('/assets/uploads/', $entry['existing_path']);
            $this->assertArrayNotHasKey('size', $entry);
        }
    }

    // ==================================================================
    // LOCAL DRIVER: byte-identical output against golden format
    // ==================================================================

    /**
     * Under local, the endpoint produces existing_path matching
     * file_url(name, category, id) which equals
     * base_url('/assets/uploads/category/id/name') — the pre-change output.
     *
     * Validates: Requirements 3.3, 3.4
     */
    public function testLocalDriverProducesByteIdenticalOutput(): void
    {
        $this->setDriver('local');
        $this->injectRecordingStorage();

        $fileContent = 'golden content pago gestor integration test';
        $this->createLocalFile('pago_gestor', 200, 'golden_file.jpg', $fileContent);
        $this->seedFileRow('tra_pago_gestor', 200, 'golden_file.jpg');

        $payload = $this->callEndpoint(new TestableConcluido(), 'getPagoGestorFiles', 200);

        $this->assertCount(1, $payload);
        $entry = $payload[0];

        // existing_path equals file_url output (which under local is base_url path)
        $expectedUrl = file_url('golden_file.jpg', 'pago_gestor', 200);
        $this->assertSame($expectedUrl, $entry['existing_path'], 'existing_path must be byte-identical to file_url() under local');

        // size must equal filesize
        $this->assertArrayHasKey('size', $entry);
        $this->assertSame(strlen($fileContent), $entry['size']);

        // For an image, icon equals existing_path
        $this->assertSame($entry['existing_path'], $entry['icon']);
    }

    /**
     * Under local, pago_derechos endpoint also produces byte-identical
     * existing_path with size and proper icon for non-images.
     *
     * Validates: Requirements 3.3, 3.4
     */
    public function testLocalDriverPagoDerechosByteIdentical(): void
    {
        $this->setDriver('local');
        $this->injectRecordingStorage();

        $fileContent = 'pdf binary content for derechos';
        $this->createLocalFile('pago_derechos', 200, 'tramite_derechos.pdf', $fileContent);
        $this->seedFileRow('tra_pago_derechos', 200, 'tramite_derechos.pdf');

        $payload = $this->callEndpoint(new TestableConcluido(), 'getPagoDerechosFiles', 200);

        $this->assertCount(1, $payload);
        $entry = $payload[0];

        $expectedUrl = file_url('tramite_derechos.pdf', 'pago_derechos', 200);
        $this->assertSame($expectedUrl, $entry['existing_path']);
        $this->assertSame(strlen($fileContent), $entry['size']);

        // pdf -> icon is static icon, not the file URL
        $this->assertNotSame($entry['existing_path'], $entry['icon']);
        $this->assertStringContainsString('pdf-icon.png', $entry['icon']);
    }

    /**
     * Under local, Tramites::getCobroClienteFiles produces byte-identical
     * output including the cobro_correcto type field.
     *
     * Validates: Requirements 3.3, 3.4
     */
    public function testLocalDriverTramitesCobroClienteByteIdentical(): void
    {
        $this->setDriver('local');
        $this->injectRecordingStorage();

        $fileContent = 'cobro client evidence';
        $this->createLocalFile('cobro_cliente', 200, 'evidencia.png', $fileContent);
        $this->seedFileRow('tra_cobro_cliente', 200, 'evidencia.png', 'completo');

        $payload = $this->callEndpoint(new TestableTramites(), 'getCobroClienteFiles', 200);

        $this->assertCount(1, $payload);
        $entry = $payload[0];

        $expectedUrl = file_url('evidencia.png', 'cobro_cliente', 200);
        $this->assertSame($expectedUrl, $entry['existing_path']);
        $this->assertSame(strlen($fileContent), $entry['size']);
        $this->assertSame('completo', $entry['cobro_correcto']);
    }

    // ==================================================================
    // ACL GUARDS: endpoints return 403/error when session lacks access
    // ==================================================================

    /**
     * When session has no permission for pago_gestor, endpoint returns
     * 403 JSON error (no file data leaked).
     *
     * Validates: Requirement 11.5
     */
    public function testAclGuardDeniesAccessWithoutPagoGestorPermission(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        // Set session with NO pago_gestor permission
        session()->set([
            'id' => 99,
            'username' => 'tester',
            'user_roles' => ['Ejecutivo'],
            'user_permissions' => [
                // missing 'section_pago_gestor'
                'section_pago_derechos',
                'section_final_costos',
            ],
        ]);

        $this->seedFileRow('tra_pago_gestor', 200, 'secret_file.pdf');

        $result = $this->callEndpointRaw(new TestableConcluido(), 'getPagoGestorFiles', 200);
        $statusCode = $result->getStatusCode();
        $body = json_decode($result->getBody(), true);

        $this->assertSame(403, $statusCode, 'Endpoint must return 403 when permission is missing');
        // No file data leaked
        $this->assertArrayNotHasKey('existing_path', $body ?? []);
        $this->assertArrayHasKey('message', $body ?? []);
    }

    /**
     * When session has no permission for pago_derechos, endpoint returns 403.
     *
     * Validates: Requirement 11.5
     */
    public function testAclGuardDeniesAccessWithoutPagoDerechosPermission(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        session()->set([
            'id' => 99,
            'username' => 'tester',
            'user_roles' => ['Ejecutivo'],
            'user_permissions' => [
                'section_pago_gestor',
                // missing 'section_pago_derechos'
                'section_final_costos',
            ],
        ]);

        $this->seedFileRow('tra_pago_derechos', 200, 'protected.pdf');

        $result = $this->callEndpointRaw(new TestableConcluido(), 'getPagoDerechosFiles', 200);
        $this->assertSame(403, $result->getStatusCode());
    }

    /**
     * When session has no permission for cobro_cliente (section_final_costos),
     * both Concluido and Tramites endpoints return 403.
     *
     * Validates: Requirement 11.5
     */
    public function testAclGuardDeniesAccessWithoutFinalCostosPermission(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        session()->set([
            'id' => 99,
            'username' => 'tester',
            'user_roles' => ['Ejecutivo'],
            'user_permissions' => [
                'section_pago_gestor',
                'section_pago_derechos',
                // missing 'section_final_costos'
            ],
        ]);

        $this->seedFileRow('tra_cobro_cliente', 200, 'confidential.jpg', 'completo');

        $resultConcluido = $this->callEndpointRaw(new TestableConcluido(), 'getCobroClienteFiles', 200);
        $this->assertSame(403, $resultConcluido->getStatusCode());

        $resultTramites = $this->callEndpointRaw(new TestableTramites(), 'getCobroClienteFiles', 200);
        $this->assertSame(403, $resultTramites->getStatusCode());
    }

    /**
     * When session is not logged in (id=0), endpoints return 401.
     *
     * Validates: Requirement 11.5
     */
    public function testAclGuardDeniesAccessWhenNotLoggedIn(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        // Clear session id to simulate not logged in
        session()->set([
            'id' => 0,
            'username' => '',
            'user_roles' => [],
            'user_permissions' => [],
        ]);

        $this->seedFileRow('tra_pago_gestor', 200, 'should_not_leak.pdf');

        $result = $this->callEndpointRaw(new TestableConcluido(), 'getPagoGestorFiles', 200);
        $this->assertContains($result->getStatusCode(), [401, 403], 'Must deny unauthenticated access');
    }

    /**
     * When session user has no tenant access to the tramite, endpoint
     * returns 403.
     *
     * Validates: Requirement 11.5
     */
    public function testAclGuardDeniesTenantAccessViolation(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        // Session with valid permissions but for a DIFFERENT user that has no
        // association to the tramite's cliente
        session()->set([
            'id' => 888, // user 888 has no cliente_user row
            'username' => 'intruder',
            'user_roles' => ['Admin'],
            'user_permissions' => [
                'section_pago_gestor',
                'section_pago_derechos',
                'section_final_costos',
            ],
        ]);

        $this->seedFileRow('tra_pago_gestor', 200, 'internal_doc.pdf');

        $result = $this->callEndpointRaw(new TestableConcluido(), 'getPagoGestorFiles', 200);
        $this->assertSame(403, $result->getStatusCode());
    }

    // ==================================================================
    // RE-RESOLVE ON EVERY INVOCATION (Req 9.6)
    // ==================================================================

    /**
     * Calling the endpoint twice produces freshly generated URLs each time,
     * proving the URL is re-resolved on every invocation and never cached.
     *
     * Validates: Requirement 9.6
     */
    public function testReResolvesUrlOnEveryInvocation(): void
    {
        $this->setDriver('s3');

        // Use a counter-based fake that produces a unique URL each call
        $callCount = 0;
        $counterFake = new class($callCount) {
            private int $counter;

            public function __construct(int &$counter)
            {
                $this->counter = 0;
            }

            public function url(string $key, int $ttl = 300): string
            {
                $this->counter++;
                return 'https://bucket.s3.amazonaws.com/' . $key . '?call=' . $this->counter . '&X-Amz-Expires=' . $ttl;
            }

            public function getCounter(): int
            {
                return $this->counter;
            }
        };

        Services::injectMock('fileStorage', $counterFake);

        $this->seedFileRow('tra_pago_gestor', 200, 'timestamped.jpg');

        // First invocation
        $payload1 = $this->callEndpoint(new TestableConcluido(), 'getPagoGestorFiles', 200);
        $url1 = $payload1[0]['existing_path'];

        // Second invocation
        $payload2 = $this->callEndpoint(new TestableConcluido(), 'getPagoGestorFiles', 200);
        $url2 = $payload2[0]['existing_path'];

        // URLs differ because they were re-resolved (counter incremented)
        $this->assertNotSame($url1, $url2, 'URLs must be freshly generated on each invocation (Req 9.6)');
        // Counter incremented at least twice (once per url() call per invocation)
        $this->assertGreaterThanOrEqual(2, $counterFake->getCounter());
    }

    // ==================================================================
    // INFRASTRUCTURE
    // ==================================================================

    /**
     * Call endpoint and return decoded JSON payload (asserts it's a valid array).
     *
     * @return array<int,array<string,mixed>>
     */
    private function callEndpoint(object $controller, string $method, int $tramiteId): array
    {
        $result = $this->callEndpointRaw($controller, $method, $tramiteId);
        $decoded = json_decode($result->getBody(), true);
        $this->assertIsArray($decoded, 'endpoint did not return a JSON array: ' . $result->getBody());

        return $decoded;
    }

    /**
     * Call endpoint and return raw Response object (for ACL tests that check
     * status codes).
     */
    private function callEndpointRaw(object $controller, string $method, int $tramiteId): Response
    {
        $config = new App();
        $uri = 'http://example.com/deskapp/x/' . $method . '/' . $tramiteId;
        $_SERVER['REQUEST_URI'] = '/deskapp/x/' . $method . '/' . $tramiteId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new IncomingRequest($config, new URI($uri), null, new UserAgent());
        $request->setMethod('get');
        $request->setHeader('Accept', 'application/json');
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller->initController($request, $response, $logger);

        $result = $controller->{$method}($tramiteId);

        return $result;
    }

    /**
     * Register a recording fake as the `fileStorage` service. Returns a
     * deterministic presigned-style URL for any key.
     */
    private function injectRecordingStorage(): object
    {
        $fake = new class {
            public array $calls = [];

            public function url(string $key, int $ttl = 300): string
            {
                $this->calls[] = [$key, $ttl];

                return 'https://bucket.s3.amazonaws.com/' . $key . '?X-Amz-Signature=deadbeef&X-Amz-Expires=' . $ttl;
            }
        };

        Services::injectMock('fileStorage', $fake);

        return $fake;
    }

    private function setDriver(string $driver): void
    {
        $config = new FileStorageConfig();
        $config->driver = $driver;
        Factories::injectMock('config', 'FileStorage', $config);
    }

    private function createLocalFile(string $category, int $id, string $fileName, string $contents): void
    {
        $directory = FCPATH . 'assets/uploads/' . $category . '/' . $id;
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
            $this->createdDirs[] = $directory;
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($filePath, $contents);
        $this->createdFiles[] = $filePath;
    }

    private function seedFileRow(string $table, int $tramiteId, string $file, ?string $cobroCorrecto = null): void
    {
        $row = [
            'tramite_id' => $tramiteId,
            'file' => $file,
        ];
        if ($table === 'tra_cobro_cliente') {
            $row['cobro_correcto'] = $cobroCorrecto;
        }

        $this->db->table($table)->insert($row);
    }

    private function recreateTables(): void
    {
        foreach (['tra_pago_gestor', 'tra_pago_derechos', 'tra_cobro_cliente', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cliente');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'cliente_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cli_directo');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'reembolso_status_id' => ['type' => 'INTEGER', 'null' => true],
            'cobro_status_id' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite');

        $this->forge->addField([
            'user_id' => ['type' => 'INTEGER'],
            'cliente_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->createTable('cliente_user');

        foreach (['tra_pago_gestor', 'tra_pago_derechos'] as $table) {
            $this->forge->addField([
                'id' => ['type' => 'INTEGER', 'auto_increment' => true],
                'tramite_id' => ['type' => 'INTEGER'],
                'file' => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable($table);
        }

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'file' => ['type' => 'TEXT', 'null' => true],
            'cobro_correcto' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_cobro_cliente');
    }

    private function seedSession(): void
    {
        session()->set([
            'id' => 99,
            'username' => 'tester',
            'user_roles' => ['Admin'],
            'user_permissions' => [
                'section_pago_gestor',
                'section_pago_derechos',
                'section_final_costos',
            ],
        ]);
    }

    private function seedTenantAccess(): void
    {
        $this->db->table('cliente')->insert(['id' => 5001]);
        $this->db->table('cli_directo')->insert([
            'id' => 6001,
            'cliente_id' => 5001,
        ]);
        $this->db->table('cliente_user')->insert([
            'user_id' => 99,
            'cliente_id' => 5001,
        ]);
    }

    private function seedTramite(int $tramiteId): void
    {
        $this->db->table('tramite')->insert([
            'id' => $tramiteId,
            'cli_directo_id' => 6001,
            'tra_status_id' => 1,
            'reembolso_status_id' => 0,
            'cobro_status_id' => 1,
        ]);
    }
}

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
 * Unit tests for the driver-aware existence gate and the getFiles JSON
 * contract across the migrated endpoints:
 *   - Concluido::getPagoGestorFiles / getPagoDerechosFiles / getCobroClienteFiles
 *   - Tramites::getCobroClienteFiles
 *
 * These assert the behavior migrated in tasks 3.1 / 3.2:
 *   - under `local`, the endpoint applies the file_exists() gate and includes
 *     a `size` field (Req 3.3, 3.4, 10.1, 10.2);
 *   - under `s3`, the endpoint skips the local-disk gate, omits `size`, and
 *     resolves `existing_path` through file_url() (never a /assets/uploads/
 *     path) (Req 10.3, 10.4, 11.1);
 *   - the response entry keys (id, name, existing_path, icon, and the existing
 *     type field such as cobro_correcto) are preserved and empty/whitespace
 *     rows are excluded (Req 11.1, 11.6).
 *
 * The `fileStorage` service is a recording fake injected via
 * Services::injectMock (no live AWS calls); the active driver is toggled via
 * the shared Config\FileStorage instance.
 *
 * Validates: Requirements 3.3, 3.4, 10.1, 10.2, 10.3, 10.4, 11.1, 11.6
 *
 * @internal
 */
final class GetFilesEndpointTest extends CIUnitTestCase
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

        helper(['filestorage']);

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
        $this->recreateTables();

        $this->seedSession();
        $this->seedTenantAccess();
        $this->seedTramite(123);
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

    // ------------------------------------------------------------------
    // Local driver: file_exists gate applied + size included
    // ------------------------------------------------------------------

    public function testLocalDriverAppliesFileExistsGateAndIncludesSize(): void
    {
        $this->setDriver('local');
        $this->injectRecordingStorage();

        // Present file on disk (included), plus an absent DB row (excluded).
        $presentContents = 'contenido de prueba pago gestor';
        $this->createLocalFile('pago_gestor', 123, 'presente.jpg', $presentContents);

        $this->seedFileRow('tra_pago_gestor', 123, 'presente.jpg');
        $this->seedFileRow('tra_pago_gestor', 123, 'ausente.pdf'); // no file on disk

        $payload = $this->callEndpoint(new TestableConcluido(), 'getPagoGestorFiles', 123);

        // Only the present file survives the file_exists() gate.
        $this->assertCount(1, $payload);
        $entry = $payload[0];

        $this->assertSame('presente.jpg', $entry['name']);
        // size is present under local and equals filesize() of the file.
        $this->assertArrayHasKey('size', $entry);
        $this->assertSame(strlen($presentContents), $entry['size']);
    }

    public function testLocalDriverExcludesRowsWhoseFileIsAbsentOnDisk(): void
    {
        $this->setDriver('local');
        $this->injectRecordingStorage();

        // No files created on disk at all -> every row is gated out.
        $this->seedFileRow('tra_pago_derechos', 123, 'a.pdf');
        $this->seedFileRow('tra_pago_derechos', 123, 'b.jpg');

        $payload = $this->callEndpoint(new TestableConcluido(), 'getPagoDerechosFiles', 123);

        $this->assertSame([], $payload);
    }

    // ------------------------------------------------------------------
    // S3 driver: gate skipped + size omitted + presigned existing_path
    // ------------------------------------------------------------------

    public function testS3DriverSkipsGateOmitsSizeAndResolvesPresignedPath(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        // No files on disk. Under s3 the local gate must be skipped so every
        // non-empty row is still returned.
        $this->seedFileRow('tra_pago_gestor', 123, 'factura.pdf');
        $this->seedFileRow('tra_pago_gestor', 123, 'foto.PNG');

        $payload = $this->callEndpoint(new TestableConcluido(), 'getPagoGestorFiles', 123);

        $this->assertCount(2, $payload);

        foreach ($payload as $entry) {
            // No local-disk size probe under s3.
            $this->assertArrayNotHasKey('size', $entry);

            // existing_path resolves through file_url -> presigned, never a
            // local upload path.
            $this->assertArrayHasKey('existing_path', $entry);
            $this->assertStringStartsWith('https://bucket.s3.amazonaws.com/', $entry['existing_path']);
            $this->assertStringNotContainsString('/assets/uploads/', $entry['existing_path']);
        }

        // Image row: icon equals the presigned existing_path.
        $imageEntry = $this->entryByName($payload, 'foto.PNG');
        $this->assertSame($imageEntry['existing_path'], $imageEntry['icon']);

        // Non-image row: icon is the static icon path (not the presigned URL).
        $pdfEntry = $this->entryByName($payload, 'factura.pdf');
        $this->assertNotSame($pdfEntry['existing_path'], $pdfEntry['icon']);
        $this->assertStringContainsString('pdf-icon.png', $pdfEntry['icon']);
    }

    public function testS3DriverExcludesEmptyAndWhitespaceRows(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        $this->seedFileRow('tra_cobro_cliente', 123, 'valido.jpg', 'completo');
        $this->seedFileRow('tra_cobro_cliente', 123, '', 'completo');       // empty -> excluded
        $this->seedFileRow('tra_cobro_cliente', 123, '   ', 'parcial');     // whitespace -> excluded

        $payload = $this->callEndpoint(new TestableConcluido(), 'getCobroClienteFiles', 123);

        $this->assertCount(1, $payload);
        $this->assertSame('valido.jpg', $payload[0]['name']);
    }

    // ------------------------------------------------------------------
    // JSON contract: keys preserved incl. the existing type field
    // ------------------------------------------------------------------

    public function testResponseContractPreservesKeysIncludingTypeField(): void
    {
        $this->setDriver('s3');
        $this->injectRecordingStorage();

        $this->seedFileRow('tra_cobro_cliente', 123, 'comprobante.jpg', 'completo');

        // Tramites::getCobroClienteFiles selects the `cobro_correcto` type field.
        $payload = $this->callEndpoint(new TestableTramites(), 'getCobroClienteFiles', 123);

        $this->assertCount(1, $payload);
        $entry = $payload[0];

        // Existing contract keys are preserved.
        $this->assertArrayHasKey('id', $entry);
        $this->assertArrayHasKey('name', $entry);
        $this->assertArrayHasKey('existing_path', $entry);
        $this->assertArrayHasKey('icon', $entry);
        // The existing type field is preserved verbatim.
        $this->assertArrayHasKey('cobro_correcto', $entry);
        $this->assertSame('completo', $entry['cobro_correcto']);

        $this->assertSame('comprobante.jpg', $entry['name']);
        // existing_path equals file_url(name, category, id) via the fake resolver.
        $this->assertSame(file_url('comprobante.jpg', 'cobro_cliente', 123), $entry['existing_path']);
    }

    public function testLocalDriverIncludesSizeAndContractKeysForTramitesEndpoint(): void
    {
        $this->setDriver('local');
        $this->injectRecordingStorage();

        $contents = 'evidencia cobro cliente';
        $this->createLocalFile('cobro_cliente', 123, 'deposito.jpg', $contents);
        $this->seedFileRow('tra_cobro_cliente', 123, 'deposito.jpg', 'parcial');

        $payload = $this->callEndpoint(new TestableTramites(), 'getCobroClienteFiles', 123);

        $this->assertCount(1, $payload);
        $entry = $payload[0];

        $this->assertArrayHasKey('size', $entry);
        $this->assertSame(strlen($contents), $entry['size']);
        $this->assertArrayHasKey('cobro_correcto', $entry);
        $this->assertSame('parcial', $entry['cobro_correcto']);
    }

    // ------------------------------------------------------------------
    // Infrastructure
    // ------------------------------------------------------------------

    /**
     * @param object $controller a TestableConcluido or TestableTramites
     * @param string $method     endpoint method name
     * @param int    $tramiteId  route id argument
     *
     * @return array<int,array<string,mixed>> decoded JSON entries
     */
    private function callEndpoint(object $controller, string $method, int $tramiteId): array
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

        $decoded = json_decode($result->getBody(), true);
        $this->assertIsArray($decoded, 'endpoint did not return a JSON array: ' . $result->getBody());

        return $decoded;
    }

    /**
     * Register a recording fake as the `fileStorage` service. It returns a
     * deterministic presigned-style URL for any key so tests can assert the
     * resolved `existing_path`/`icon` without live AWS calls.
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

    /**
     * @param array<int,array<string,mixed>> $payload
     */
    private function entryByName(array $payload, string $name): array
    {
        foreach ($payload as $entry) {
            if (($entry['name'] ?? null) === $name) {
                return $entry;
            }
        }

        $this->fail('no entry named ' . $name . ' in payload');
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

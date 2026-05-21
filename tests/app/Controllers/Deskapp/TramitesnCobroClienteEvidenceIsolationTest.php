<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use Psr\Log\NullLogger;
use Tests\Support\Controllers\TestableTramitesn;

class TramitesnCobroClienteEvidenceIsolationTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
        $this->recreateTables();

        TestableTramitesn::$tramiteRow = [];
        TestableTramitesn::$denyTenantAccess = false;
        TestableTramitesn::$useDatabaseLookups = true;
        TestableTramitesn::$skipLegacyFinalSaveSideEffects = false;
        TestableTramitesn::$fakeUploadMoves = true;

        $this->seedSession();
        $this->seedTenantAccess();
        $this->seedTramite(123);
        $this->seedTramite(124);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $directories = [
            FCPATH . 'assets/uploads/cobro_cliente/123',
            FCPATH . 'assets/uploads/cobro_cliente/124',
        ];
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                @rmdir($directory);
            }
        }

        foreach (['tra_cobro_cliente', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        TestableTramitesn::$tramiteRow = [];
        TestableTramitesn::$denyTenantAccess = false;
        TestableTramitesn::$useDatabaseLookups = false;
        TestableTramitesn::$skipLegacyFinalSaveSideEffects = false;
        TestableTramitesn::$fakeUploadMoves = false;

        parent::tearDown();
    }

    public function testDeleteCobroClienteOnlyRemovesEvidenceForSelectedTramite(): void
    {
        $sharedFileName = 'evidencia_compartida.png';
        $firstFile = $this->seedCobroClienteEvidence(123, $sharedFileName, 'parcial');
        $secondFile = $this->seedCobroClienteEvidence(124, $sharedFileName, 'completo');

        $response = $this->executeDeleteCobroCliente(123, $sharedFileName);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success'] ?? false);
        $this->assertSame(0, $this->db->table('tra_cobro_cliente')->where('tramite_id', 123)->where('file', $sharedFileName)->countAllResults());
        $this->assertSame(1, $this->db->table('tra_cobro_cliente')->where('tramite_id', 124)->where('file', $sharedFileName)->countAllResults());
        $this->assertFalse(is_file($firstFile));
        $this->assertTrue(is_file($secondFile));
    }

    public function testUploadCobroClienteOnlyCreatesEvidenceForSelectedTramite(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'cobro-cliente-');
        file_put_contents($tempFile, 'contenido temporal de evidencia');
        $this->createdFiles[] = $tempFile;

        $response = $this->executeUploadCobroCliente(123, [
            'name' => 'deposito cliente.png',
            'type' => 'image/png',
            'tmp_name' => $tempFile,
            'error' => 0,
            'size' => filesize($tempFile),
        ], [
            'cobro_correcto' => 'completo',
        ]);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode(), json_encode($payload));
        $this->assertTrue($payload['success'] ?? false, json_encode($payload));

        $uploadedRows = $this->db->table('tra_cobro_cliente')->where('tramite_id', 123)->get()->getResultArray();
        $this->assertCount(1, $uploadedRows);
        $this->assertSame('completo', $uploadedRows[0]['cobro_correcto']);
        $this->assertSame(0, $this->db->table('tra_cobro_cliente')->where('tramite_id', 124)->countAllResults());

        $storedFile = (string) ($uploadedRows[0]['file'] ?? '');
        $this->assertNotSame('', $storedFile);
        $this->assertStringStartsWith('deposito_cliente_', $storedFile);
        $this->assertTrue(is_file(FCPATH . 'assets/uploads/cobro_cliente/123/' . $storedFile));
        $this->assertFalse(is_file(FCPATH . 'assets/uploads/cobro_cliente/124/' . $storedFile));
    }

    public function testUploadCobroClienteRequiresFineUploadPermission(): void
    {
        session()->set('user_permissions', ['list_cobro_cliente']);

        $tempFile = tempnam(sys_get_temp_dir(), 'cobro-cliente-guard-');
        file_put_contents($tempFile, 'contenido temporal de evidencia');
        $this->createdFiles[] = $tempFile;

        $response = $this->executeUploadCobroCliente(123, [
            'name' => 'deposito cliente.png',
            'type' => 'image/png',
            'tmp_name' => $tempFile,
            'error' => 0,
            'size' => filesize($tempFile),
        ], [
            'cobro_correcto' => 'completo',
        ]);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    private function executeDeleteCobroCliente(int $tramiteId, string $fileName): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/delete_cobro_cliente';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $post = [
            'tramite_id' => (string) $tramiteId,
            'file' => $fileName,
        ];
        $_POST = $post;
        $_REQUEST = $post;

        $request = new IncomingRequest($config, new URI('http://example.com/deskapp/tramitesn/delete_cobro_cliente'), http_build_query($post), new UserAgent());
        $request->setMethod('post');
        $request->setGlobal('post', $post);
        $request->setGlobal('request', $post);
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request->setHeader('Accept', 'application/json');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller->delete_cobro_cliente();
    }

    private function executeUploadCobroCliente(int $tramiteId, array $file, array $post): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/upload_cobro_cliente/' . $tramiteId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $post;
        $_REQUEST = $post;
        $_FILES = ['file' => $file];

        $request = new IncomingRequest($config, new URI('http://example.com/deskapp/tramitesn/upload_cobro_cliente/' . $tramiteId), http_build_query($post), new UserAgent());
        $request->setMethod('post');
        $request->setGlobal('post', $post);
        $request->setGlobal('request', $post);
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request->setHeader('Accept', 'application/json');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller->upload_cobro_cliente();
    }

    private function recreateTables(): void
    {
        foreach (['tra_cobro_cliente', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
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

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'file' => ['type' => 'TEXT', 'null' => true],
            'cobro_correcto' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'status' => ['type' => 'INTEGER', 'default' => 1],
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
            'user_permissions' => ['list_cobro_cliente', 'can_upload_dropzone_cobro_cliente'],
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
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
            'reembolso_status_id' => 0,
            'cobro_status_id' => 1,
        ]);
    }

    private function seedCobroClienteEvidence(int $tramiteId, string $fileName, string $cobroCorrecto): string
    {
        $directory = FCPATH . 'assets/uploads/cobro_cliente/' . $tramiteId;
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($filePath, 'evidencia de prueba ' . $tramiteId);
        $this->createdFiles[] = $filePath;

        $now = date('Y-m-d H:i:s');
        $this->db->table('tra_cobro_cliente')->insert([
            'tramite_id' => $tramiteId,
            'file' => $fileName,
            'cobro_correcto' => $cobroCorrecto,
            'created_at' => $now,
            'updated_at' => $now,
            'user_id' => 99,
            'status' => 1,
        ]);

        return $filePath;
    }
}
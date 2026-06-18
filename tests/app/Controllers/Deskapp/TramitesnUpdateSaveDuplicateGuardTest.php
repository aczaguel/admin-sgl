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

class TramitesnUpdateSaveDuplicateGuardTest extends CIUnitTestCase
{
    private const TEST_USER_ID = 990099;

    private BaseConnection $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
        $this->recreateTables();

        TestableTramitesn::$tramiteRow = [];
        TestableTramitesn::$denyTenantAccess = false;
        TestableTramitesn::$useDatabaseLookups = true;
        TestableTramitesn::$skipLegacyUpdateSaveSideEffects = true;

        helper('audit');
        $this->seedUsers();
        $this->seedSession();
    }

    protected function tearDown(): void
    {
        if ($this->db->tableExists('users')) {
            $this->db->table('users')->where('id', self::TEST_USER_ID)->delete();
        }

        foreach (['tramite_audit_log', 'tra_user_log', 'bitacora', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        TestableTramitesn::$tramiteRow = [];
        TestableTramitesn::$denyTenantAccess = false;
        TestableTramitesn::$useDatabaseLookups = false;
        TestableTramitesn::$skipLegacyUpdateSaveSideEffects = false;

        parent::tearDown();
    }

    public function testUpdateSaveAllowsEditingStep1FieldsWhenSerieAndTipoRemainUnchanged(): void
    {
        $this->seedTenantAccess();
        $this->seedTramite(12468, [
            'folio' => 'ALD425717',
            'contrato' => 'CONT010205',
            'unidad' => '431DSGD',
            'serie' => 'SDDGDFFF',
            'placas' => 'GDR234SD',
            'entidad_id' => 1,
            'observaciones' => '',
            'tra_tipos_id' => 10,
            'tra_status_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
        ]);
        $this->seedTramite(12469, [
            'folio' => 'ALD425718',
            'contrato' => 'CONT010206',
            'unidad' => '999ZZZ',
            'serie' => 'SDDGDFFF',
            'placas' => 'XYZ1234',
            'entidad_id' => 1,
            'observaciones' => 'Duplicado existente',
            'tra_tipos_id' => 10,
            'tra_status_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
        ]);

        $response = $this->executeUpdateSave(12468, [
            'folio' => 'ALD425717',
            'current_step' => '1',
            'contrato' => 'CONT010205',
            'unidad' => '431DSGD',
            'serie' => 'SDDGDFFF',
            'placas' => 'GDR234SD',
            'entidad_id' => '1',
            'observaciones' => 'Edicion permitida sin cambiar clave duplicada',
        ]);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success'] ?? false, json_encode($payload));
        $this->assertSame('El trámite se guardó correctamente.', $payload['message'] ?? '');

        $updated = $this->db->table('tramite')->where('id', 12468)->get()->getRowArray();
        $this->assertSame('Edicion permitida sin cambiar clave duplicada', $updated['observaciones']);
        $this->assertSame('SDDGDFFF', $updated['serie']);
    }

    public function testUpdateSaveStillBlocksWhenSerieChangesToDuplicateCombination(): void
    {
        $this->seedTenantAccess();
        $this->seedTramite(12468, [
            'folio' => 'ALD425717',
            'contrato' => 'CONT010205',
            'unidad' => '431DSGD',
            'serie' => 'SERIE-ORIGINAL',
            'placas' => 'GDR234SD',
            'entidad_id' => 1,
            'observaciones' => '',
            'tra_tipos_id' => 10,
            'tra_status_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
        ]);
        $this->seedTramite(12469, [
            'folio' => 'ALD425718',
            'contrato' => 'CONT010206',
            'unidad' => '999ZZZ',
            'serie' => 'SERIE-DUPLICADA',
            'placas' => 'XYZ1234',
            'entidad_id' => 1,
            'observaciones' => 'Duplicado existente',
            'tra_tipos_id' => 10,
            'tra_status_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
        ]);

        $response = $this->executeUpdateSave(12468, [
            'folio' => 'ALD425717',
            'current_step' => '1',
            'contrato' => 'CONT010205',
            'unidad' => '431DSGD',
            'serie' => 'SERIE-DUPLICADA',
            'placas' => 'GDR234SD',
            'entidad_id' => '1',
            'observaciones' => 'Debe bloquearse por duplicado real',
        ]);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($payload['success'] ?? true, json_encode($payload));
        $this->assertSame('Ya existe un tramite con el mismo tipo y serie dentro del ultimo ano.', $payload['message'] ?? '');

        $updated = $this->db->table('tramite')->where('id', 12468)->get()->getRowArray();
        $this->assertSame('SERIE-ORIGINAL', $updated['serie']);
        $this->assertSame('', $updated['observaciones']);
    }

    private function executeUpdateSave(int $tramiteId, array $post): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/update_save/' . $tramiteId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $post;
        $_REQUEST = $post;

        $uri = new URI('http://example.com/deskapp/tramitesn/update_save/' . $tramiteId);
        $request = new IncomingRequest($config, $uri, http_build_query($post), new UserAgent());
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

        return $controller->update_save();
    }

    private function seedSession(): void
    {
        session()->set([
            'id' => self::TEST_USER_ID,
            'username' => 'tester',
            'firstname' => 'QA',
            'lastname' => 'Tester',
            'email' => 'qa@example.com',
            'user_roles' => ['Admin'],
            'user_permissions' => ['editar_tramite', 'write_tramite_datos_tramite'],
        ]);
    }

    private function recreateTables(): void
    {
        foreach (['tramite_audit_log', 'tra_user_log', 'bitacora', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
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
            'folio' => ['type' => 'TEXT', 'null' => true],
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_ejecutivo_id' => ['type' => 'INTEGER', 'null' => true],
            'contrato' => ['type' => 'TEXT', 'null' => true],
            'unidad' => ['type' => 'TEXT', 'null' => true],
            'serie' => ['type' => 'TEXT', 'null' => true],
            'placas' => ['type' => 'TEXT', 'null' => true],
            'entidad_id' => ['type' => 'INTEGER', 'null' => true],
            'observaciones' => ['type' => 'TEXT', 'null' => true],
            'tra_tipos_id' => ['type' => 'INTEGER', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'reembolso_status_id' => ['type' => 'INTEGER', 'null' => true],
            'cobro_status_id' => ['type' => 'INTEGER', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
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
            'tipo' => ['type' => 'TEXT', 'null' => true],
            'origen' => ['type' => 'TEXT', 'null' => true],
            'folio_tramite' => ['type' => 'TEXT', 'null' => true],
            'tramite_id' => ['type' => 'INTEGER', 'null' => true],
            'cambios' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('bitacora');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_user_log');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'folio' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'username' => ['type' => 'TEXT', 'null' => true],
            'user_email' => ['type' => 'TEXT', 'null' => true],
            'action' => ['type' => 'TEXT'],
            'entity_type' => ['type' => 'TEXT'],
            'description' => ['type' => 'TEXT'],
            'field_name' => ['type' => 'TEXT', 'null' => true],
            'old_value' => ['type' => 'TEXT', 'null' => true],
            'new_value' => ['type' => 'TEXT', 'null' => true],
            'metadata' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'TEXT', 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite_audit_log');
    }

    private function seedTenantAccess(): void
    {
        $this->db->table('cliente')->insert(['id' => 5001]);
        $this->db->table('cli_directo')->insert([
            'id' => 6001,
            'cliente_id' => 5001,
        ]);
        $this->db->table('cliente_user')->insert([
            'user_id' => self::TEST_USER_ID,
            'cliente_id' => 5001,
        ]);
    }

    private function seedUsers(): void
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        $exists = $this->db->table('users')->where('id', self::TEST_USER_ID)->countAllResults() > 0;
        if ($exists) {
            return;
        }

        $this->db->table('users')->insert([
            'id' => self::TEST_USER_ID,
            'firstname' => 'QA',
            'midname' => '',
            'lastname' => 'Tester',
            'email' => 'qa-update-save@example.com',
            'status' => 1,
        ]);
    }

    private function seedTramite(int $tramiteId, array $data): void
    {
        $now = date('Y-m-d H:i:s');
        $defaults = [
            'id' => $tramiteId,
            'cli_directo_id' => 6001,
            'reembolso_status_id' => 0,
            'cobro_status_id' => 0,
            'user_id' => self::TEST_USER_ID,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->db->table('tramite')->insert(array_merge($defaults, $data));
    }
}
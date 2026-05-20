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

class TramitesnUpdateFinalSaveIsolationTest extends CIUnitTestCase
{
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
        TestableTramitesn::$skipLegacyFinalSaveSideEffects = true;

        helper('audit');
        $this->seedSession();
    }

    protected function tearDown(): void
    {
        foreach (['tramite_audit_log', 'tra_status', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        TestableTramitesn::$tramiteRow = [];
        TestableTramitesn::$denyTenantAccess = false;
        TestableTramitesn::$useDatabaseLookups = false;
        TestableTramitesn::$skipLegacyFinalSaveSideEffects = false;

        parent::tearDown();
    }

    public function testUpdateFinalSaveOnlyUpdatesSelectedTramite(): void
    {
        $this->seedTenantAccess();
        $this->seedTraStatusCatalog();
        $this->seedTramite(123, [
            'folio' => 'TR-123',
            'id_give_cliente' => 'GIVE-OLD-123',
            'numero_factura' => 'FAC-OLD-123',
            'numero_refactura' => 'RE-OLD-123',
            'cobro_status_id' => 1,
            'evidencia_cobro_txt' => 'Texto anterior 123',
            'costo_gestoria' => 100.00,
            'costo_pago_cliente' => 200.00,
            'comision_derechos' => 50.00,
            'iva' => 16.00,
            'costo_total' => 366.00,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
        ]);
        $this->seedTramite(124, [
            'folio' => 'TR-124',
            'id_give_cliente' => 'GIVE-OLD-124',
            'numero_factura' => 'FAC-OLD-124',
            'numero_refactura' => 'RE-OLD-124',
            'cobro_status_id' => 2,
            'evidencia_cobro_txt' => 'Texto anterior 124',
            'costo_gestoria' => 500.00,
            'costo_pago_cliente' => 600.00,
            'comision_derechos' => 70.00,
            'iva' => 80.00,
            'costo_total' => 1250.00,
            'tra_status_id' => SGL_TRA_STATUS_COBRO_CLIENTE,
        ]);

        $response = $this->executeUpdateFinalSave(123, [
            'id_give_cliente' => 'GIVE-NEW-123',
            'numero_factura' => 'FAC-NEW-123',
            'numero_refactura' => 'RE-NEW-123',
            'cobro_status_id' => '2',
            'evidencia_cobro_txt' => 'Solo cambia el expediente 123',
            'costo_pago_cliente' => '325.50',
            'comision_derechos' => '75.25',
            'costo_gestoria_hidden' => '100.00',
            'iva' => '64.12',
        ]);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success'] ?? false, json_encode($payload));

        $updated = $this->db->table('tramite')->where('id', 123)->get()->getRowArray();
        $untouched = $this->db->table('tramite')->where('id', 124)->get()->getRowArray();

        $this->assertSame('GIVE-NEW-123', $updated['id_give_cliente']);
        $this->assertSame('FAC-NEW-123', $updated['numero_factura']);
        $this->assertSame('RE-NEW-123', $updated['numero_refactura']);
        $this->assertSame('Solo cambia el expediente 123', $updated['evidencia_cobro_txt']);
        $this->assertSame(2, (int) $updated['cobro_status_id']);
        $this->assertSame(325.50, (float) $updated['costo_pago_cliente']);
        $this->assertSame(75.25, (float) $updated['comision_derechos']);
        $this->assertSame(100.00, (float) $updated['costo_gestoria']);
        $this->assertSame(64.12, (float) $updated['iva']);
        $this->assertSame(564.87, (float) $updated['costo_total']);

        $this->assertSame('GIVE-OLD-124', $untouched['id_give_cliente']);
        $this->assertSame('FAC-OLD-124', $untouched['numero_factura']);
        $this->assertSame('RE-OLD-124', $untouched['numero_refactura']);
        $this->assertSame('Texto anterior 124', $untouched['evidencia_cobro_txt']);
        $this->assertSame(2, (int) $untouched['cobro_status_id']);
        $this->assertSame(600.00, (float) $untouched['costo_pago_cliente']);
        $this->assertSame(70.00, (float) $untouched['comision_derechos']);
        $this->assertSame(500.00, (float) $untouched['costo_gestoria']);
        $this->assertSame(80.00, (float) $untouched['iva']);
        $this->assertSame(1250.00, (float) $untouched['costo_total']);

        $this->assertGreaterThan(0, $this->db->table('tramite_audit_log')->where('tramite_id', 123)->countAllResults());
        $this->assertSame(0, $this->db->table('tramite_audit_log')->where('tramite_id', 124)->countAllResults());
    }

    private function executeUpdateFinalSave(int $tramiteId, array $post): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/update_final_save/' . $tramiteId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $post;
        $_REQUEST = $post;
        $uri = new URI('http://example.com/deskapp/tramitesn/update_final_save/' . $tramiteId);

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

        return $controller->update_final_save();
    }

    private function seedSession(): void
    {
        session()->set([
            'id' => 99,
            'username' => 'tester',
            'firstname' => 'Cobranza',
            'midname' => '',
            'lastname' => 'QA',
            'email' => 'qa@example.com',
            'user_roles' => ['Admin'],
            'user_permissions' => ['list_cobro_cliente', 'editar_final'],
        ]);
    }

    private function recreateTables(): void
    {
        foreach (['tramite_audit_log', 'tra_status', 'cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
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
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'reembolso_status_id' => ['type' => 'INTEGER', 'null' => true],
            'cobro_status_id' => ['type' => 'INTEGER', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'id_give_cliente' => ['type' => 'TEXT', 'null' => true],
            'numero_factura' => ['type' => 'TEXT', 'null' => true],
            'numero_refactura' => ['type' => 'TEXT', 'null' => true],
            'evidencia_cobro_txt' => ['type' => 'TEXT', 'null' => true],
            'costo_gestoria' => ['type' => 'REAL', 'default' => 0],
            'costo_pago_cliente' => ['type' => 'REAL', 'default' => 0],
            'comision_derechos' => ['type' => 'REAL', 'default' => 0],
            'iva' => ['type' => 'REAL', 'default' => 0],
            'costo_total' => ['type' => 'REAL', 'default' => 0],
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
            'id' => ['type' => 'INTEGER'],
            'tra_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_status');

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
            'user_id' => 99,
            'cliente_id' => 5001,
        ]);
    }

    private function seedTraStatusCatalog(): void
    {
        $this->db->table('tra_status')->insertBatch([
            ['id' => SGL_TRA_STATUS_PAGO_GESTOR, 'tra_status' => 'Pago gestor'],
            ['id' => SGL_TRA_STATUS_COBRO_CLIENTE, 'tra_status' => 'Cobro cliente'],
        ]);
    }

    private function seedTramite(int $tramiteId, array $data): void
    {
        $now = date('Y-m-d H:i:s');
        $defaults = [
            'id' => $tramiteId,
            'cli_directo_id' => 6001,
            'reembolso_status_id' => 0,
            'cobro_status_id' => 1,
            'user_id' => 99,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->db->table('tramite')->insert(array_merge($defaults, $data));
    }
}
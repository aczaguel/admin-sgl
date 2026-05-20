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
use Config\Services;
use Tests\Support\Controllers\TestableTramites;

class TramitesStatusWebhookTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();

        $this->recreateTables();
        $this->seedRequestContext();
        $this->seedSession();
        helper('audit');
        Factories::reset();
    }

    public function testUpdateTramiteStatusQueuesWebhookForTrackedExternalTramite(): void
    {
        $this->seedTrackedTramite(1001, 22, 'ERP-REF-1001');

        $controller = new TestableTramites();
        $result = $controller->updateTramiteStatus(1001, 25);

        $this->assertTrue($result['success'] ?? false);
        $this->assertSame(25, (int) $this->db->table('tramite')->where('id', 1001)->get()->getRowArray()['tra_status_id']);
        $this->assertSame(1, $this->db->table('tramite_audit_log')->countAllResults());
        $this->assertSame(1, $this->db->table('external_api_webhook_event')->countAllResults());

        $event = $this->db->table('external_api_webhook_event')->get()->getRowArray();
        $payload = json_decode((string) ($event['payload_json'] ?? '{}'), true);

        $this->assertSame('tramite.status_changed', $event['event_name']);
        $this->assertSame('erp_demo', $event['source_system']);
        $this->assertSame('ERP-REF-1001', $event['external_reference']);
        $this->assertSame(22, $payload['previous_status']['id']);
        $this->assertSame(25, $payload['current_status']['id']);
        $this->assertSame(1001, $payload['tramite']['id']);

        $reference = $this->db->table('external_api_tramite_reference')->where('tramite_id', 1001)->get()->getRowArray();
        $snapshot = json_decode((string) ($reference['last_status_payload_json'] ?? '{}'), true);

        $this->assertSame(25, $snapshot['tra_status_id']);
        $this->assertSame('ERP-REF-1001', $snapshot['external_reference']);
    }

    public function testUpdateTramiteStatusDoesNotQueueWebhookWithoutExternalTracking(): void
    {
        $this->seedBaseTramite(1002, 22);

        $controller = new TestableTramites();
        $result = $controller->updateTramiteStatus(1002, 25);

        $this->assertTrue($result['success'] ?? false);
        $this->assertSame(1, $this->db->table('tramite_audit_log')->countAllResults());
        $this->assertSame(0, $this->db->table('external_api_webhook_event')->countAllResults());
    }

    private function recreateTables(): void
    {
        foreach ([
            'external_api_webhook_event',
            'external_api_tramite_reference',
            'tra_doc_status',
            'cli_directo_ejecutivo',
            'cli_directo',
            'pago_gestor_status',
            'reembolso_status',
            'cobro_statuses',
            'tra_status',
            'tramite_audit_log',
            'tramite',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->createTramiteTable();
        $this->createTramiteAuditLogTable();
        $this->createTraStatusTable();
        $this->createCobroStatusesTable();
        $this->createReembolsoStatusTable();
        $this->createPagoGestorStatusTable();
        $this->createCliDirectoTable();
        $this->createCliDirectoEjecutivoTable();
        $this->createTraDocStatusTable();
        $this->createExternalReferenceTable();
        $this->createExternalWebhookEventTable();

        $this->db->table('tra_status')->insertBatch([
            ['id' => 22, 'tra_status' => 'DCTOS COMPLETOS'],
            ['id' => 25, 'tra_status' => 'PAGO DERECHOS COTIZACION'],
        ]);
    }

    private function seedRequestContext(): void
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramites/update/1001';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new IncomingRequest($config, new URI('http://example.com/deskapp/tramites/update/1001'), null, new UserAgent());
        $response = new Response($config);

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);
    }

    private function seedSession(): void
    {
        session()->set([
            'id' => 99,
            'firstname' => 'Webhook',
            'midname' => 'Test',
            'lastname' => 'User',
            'email' => 'webhook-test@example.com',
        ]);
    }

    private function seedTrackedTramite(int $tramiteId, int $statusId, string $externalReference): void
    {
        $this->seedBaseTramite($tramiteId, $statusId);

        $now = date('Y-m-d H:i:s');
        $this->db->table('external_api_tramite_reference')->insert([
            'source_system' => 'erp_demo',
            'external_reference' => $externalReference,
            'tramite_id' => $tramiteId,
            'last_status_payload_json' => json_encode(['id' => $tramiteId, 'tra_status_id' => $statusId]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedBaseTramite(int $tramiteId, int $statusId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('tramite')->insert([
            'id' => $tramiteId,
            'folio' => 'TR-' . $tramiteId,
            'contrato' => 'CTR-' . $tramiteId,
            'tra_status_id' => $statusId,
            'cobro_status_id' => null,
            'reembolso_status_id' => null,
            'pago_gestor_st_id' => null,
            'cli_directo_id' => null,
            'cli_directo_ejecutivo_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createTramiteTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'folio' => ['type' => 'TEXT', 'null' => true],
            'contrato' => ['type' => 'TEXT', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
            'cobro_status_id' => ['type' => 'INTEGER', 'null' => true],
            'reembolso_status_id' => ['type' => 'INTEGER', 'null' => true],
            'pago_gestor_st_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'cli_directo_ejecutivo_id' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite');
    }

    private function createTramiteAuditLogTable(): void
    {
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

    private function createTraStatusTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'tra_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_status');
    }

    private function createCobroStatusesTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'cobro_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobro_statuses');
    }

    private function createReembolsoStatusTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'reembolso_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('reembolso_status');
    }

    private function createPagoGestorStatusTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'pago_status' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pago_gestor_status');
    }

    private function createCliDirectoTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'razon_social' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cli_directo');
    }

    private function createCliDirectoEjecutivoTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER'],
            'nombre' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cli_directo_ejecutivo');
    }

    private function createTraDocStatusTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_doc_status');
    }

    private function createExternalReferenceTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'source_system' => ['type' => 'TEXT'],
            'external_reference' => ['type' => 'TEXT'],
            'tramite_id' => ['type' => 'INTEGER'],
            'last_status_payload_json' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT'],
            'updated_at' => ['type' => 'TEXT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('external_api_tramite_reference');
    }

    private function createExternalWebhookEventTable(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'source_system' => ['type' => 'TEXT'],
            'event_name' => ['type' => 'TEXT'],
            'webhook_url' => ['type' => 'TEXT', 'null' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'external_reference' => ['type' => 'TEXT', 'null' => true],
            'delivery_status' => ['type' => 'TEXT'],
            'attempts' => ['type' => 'INTEGER', 'default' => 0],
            'payload_json' => ['type' => 'TEXT'],
            'last_attempt_at' => ['type' => 'TEXT', 'null' => true],
            'delivered_at' => ['type' => 'TEXT', 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TEXT'],
            'updated_at' => ['type' => 'TEXT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('external_api_webhook_event');
    }
}
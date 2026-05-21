<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Controllers\TestableTramitesn;

class TramitesnPendingPagoConciliationTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
        $this->recreateTables();
    }

    protected function tearDown(): void
    {
        foreach (['cobranza_pago', 'cobranza_expediente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        parent::tearDown();
    }

    public function testHasPendingPagoConciliationReturnsTrueWhenActiveExpedienteHasUnconfirmedPagos(): void
    {
        $this->db->table('cobranza_expediente')->insert([
            'id' => 7001,
            'tramite_id' => 4012,
            'is_active' => 1,
        ]);
        $this->db->table('cobranza_pago')->insert([
            'expediente_id' => 7001,
            'status_code' => 'reportado',
        ]);

        $controller = new TestableTramitesn();

        $this->assertTrue($controller->hasPendingPagoConciliationForTest(4012));
    }

    public function testHasPendingPagoConciliationReturnsFalseWhenAllPagosAreConfirmed(): void
    {
        $this->db->table('cobranza_expediente')->insert([
            'id' => 7002,
            'tramite_id' => 4013,
            'is_active' => 1,
        ]);
        $this->db->table('cobranza_pago')->insert([
            'expediente_id' => 7002,
            'status_code' => 'confirmado',
        ]);

        $controller = new TestableTramitesn();

        $this->assertFalse($controller->hasPendingPagoConciliationForTest(4013));
    }

    private function recreateTables(): void
    {
        foreach (['cobranza_pago', 'cobranza_expediente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'tramite_id' => ['type' => 'INTEGER', 'null' => true],
            'is_active' => ['type' => 'INTEGER', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobranza_expediente');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'expediente_id' => ['type' => 'INTEGER', 'null' => true],
            'status_code' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cobranza_pago');
    }
}
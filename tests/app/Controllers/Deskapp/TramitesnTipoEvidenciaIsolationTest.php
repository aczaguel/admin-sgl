<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Controllers\TestableTramitesn;

class TramitesnTipoEvidenciaIsolationTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
        $this->recreateTables();
        $this->seedUsers();
    }

    protected function tearDown(): void
    {
        foreach (['tra_evidencias', 'users'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        parent::tearDown();
    }

    public function testGeneralPagoGestorAndCobroClienteEvidenceStaySeparatedByTipo(): void
    {
        $this->seedEvidence(500, 'Nota operativa general', 1, 1, '2026-06-11 10:00:00');
        $this->seedEvidence(500, 'Nota interna pago gestor', 2, 1, '2026-06-11 11:00:00');
        $this->seedEvidence(500, 'Nota cobro cliente', 3, 1, '2026-06-11 12:00:00');

        $controller = new TestableTramitesn();

        $generalItems = $this->invokePrivate($controller, 'getPrototypeEvidencias', [500]);
        $step4Items = $this->invokePrivate($controller, 'getPrototypeStep4Notes', [500]);
        $step5Items = $this->invokePrivate($controller, 'getPrototypeStep5Notes', [500]);

        $this->assertCount(1, $generalItems);
        $this->assertSame('Nota operativa general', $generalItems[0]['comment']);

        $this->assertCount(1, $step4Items);
        $this->assertSame('Nota interna pago gestor', $step4Items[0]['comment']);

        $this->assertCount(1, $step5Items);
        $this->assertSame('Nota cobro cliente', $step5Items[0]['comment']);
    }

    private function invokePrivate(object $instance, string $methodName, array $arguments = [])
    {
        $method = new \ReflectionMethod($instance, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($instance, $arguments);
    }

    private function recreateTables(): void
    {
        foreach (['tra_evidencias', 'users'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'firstname' => ['type' => 'TEXT', 'null' => true],
            'midname' => ['type' => 'TEXT', 'null' => true],
            'lastname' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'folio_tramite' => ['type' => 'TEXT', 'null' => true],
            'tramite_id' => ['type' => 'INTEGER'],
            'comentario' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'INTEGER', 'null' => true],
            'status' => ['type' => 'INTEGER', 'default' => 1],
            'tipo_evidencia' => ['type' => 'INTEGER', 'default' => 1],
            'created_at' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tra_evidencias');
    }

    private function seedUsers(): void
    {
        $this->db->table('users')->insert([
            'id' => 1,
            'firstname' => 'Ana',
            'midname' => 'Maria',
            'lastname' => 'Lopez',
        ]);
    }

    private function seedEvidence(int $tramiteId, string $comentario, int $tipoEvidencia, int $userId, string $createdAt): void
    {
        $this->db->table('tra_evidencias')->insert([
            'folio_tramite' => 'FOL-' . $tramiteId,
            'tramite_id' => $tramiteId,
            'comentario' => $comentario,
            'user_id' => $userId,
            'status' => 1,
            'tipo_evidencia' => $tipoEvidencia,
            'created_at' => $createdAt,
        ]);
    }
}
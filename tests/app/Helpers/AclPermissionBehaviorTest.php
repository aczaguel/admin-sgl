<?php

namespace Tests\App\Helpers;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;

class AclPermissionBehaviorTest extends CIUnitTestCase
{
    private BaseConnection $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
        $this->recreateTables();

        session()->remove([
            'id',
            'username',
            'user_roles',
            'user_permissions',
            'clients_by_user',
            'admin_global_client_access',
            'user_client',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (['cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        parent::tearDown();
    }

    public function testHasPermissionRequiresExplicitPermissionEvenForSuperAdminRole(): void
    {
        $this->assertFalse(has_permission('list_cobro_cliente', [], ['Super Admin']));
        $this->assertTrue(has_permission('list_cobro_cliente', ['list_cobro_cliente'], ['Super Admin']));
    }

    public function testCanAccessCobroClienteSurfaceAcceptsNewOrLegacyPermission(): void
    {
        $this->assertTrue(can_access_cobro_cliente_surface(['Admin'], ['list_cobro_cliente']));
        $this->assertTrue(can_access_cobro_cliente_surface(['Admin'], ['section_final_costos']));
        $this->assertFalse(can_access_cobro_cliente_surface(['Admin'], []));
    }

    public function testCanEditCobroClienteSurfaceRequiresEditarFinalAlongsideSurfaceAccess(): void
    {
        $this->assertTrue(can_edit_cobro_cliente_surface(['Admin'], ['list_cobro_cliente', 'editar_final']));
        $this->assertTrue(can_edit_cobro_cliente_surface(['Admin'], ['section_final_costos', 'editar_final']));
        $this->assertFalse(can_edit_cobro_cliente_surface(['Admin'], ['list_cobro_cliente']));
        $this->assertFalse(can_edit_cobro_cliente_surface(['Admin'], ['editar_final']));
    }

    public function testValidateTramiteAccessRequiresClienteRelationEvenForAdminRole(): void
    {
        session()->set([
            'id' => 99,
            'user_roles' => ['Admin'],
        ]);
        $this->seedTramiteGraph(123, 1001, 2001);

        $this->assertFalse(validate_tramite_access(123, 99));
    }

    public function testValidateTramiteAccessAllowsAssignedClienteRelation(): void
    {
        session()->set([
            'id' => 99,
            'user_roles' => ['Admin'],
        ]);
        $this->seedTramiteGraph(123, 1001, 2001);
        $this->db->table('cliente_user')->insert([
            'user_id' => 99,
            'cliente_id' => 2001,
        ]);

        $this->assertTrue(validate_tramite_access(123, 99));
    }

    public function testAclHasTramiteTenantAccessIgnoresLegacyBypassPermission(): void
    {
        session()->set([
            'id' => 99,
            'user_roles' => ['Admin'],
            'user_permissions' => ['bypass_tramite_tenant_access'],
        ]);
        $this->seedTramiteGraph(123, 1001, 2001);

        $this->assertFalse(acl_has_tramite_tenant_access(123, 99, ['Admin'], ['bypass_tramite_tenant_access']));
    }

    private function recreateTables(): void
    {
        foreach (['cliente_user', 'tramite', 'cli_directo', 'cliente'] as $table) {
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
            'cli_directo_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite');

        $this->forge->addField([
            'user_id' => ['type' => 'INTEGER'],
            'cliente_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->createTable('cliente_user');
    }

    private function seedTramiteGraph(int $tramiteId, int $cliDirectoId, int $clienteId): void
    {
        $this->db->table('cliente')->insert(['id' => $clienteId]);
        $this->db->table('cli_directo')->insert([
            'id' => $cliDirectoId,
            'cliente_id' => $clienteId,
        ]);
        $this->db->table('tramite')->insert([
            'id' => $tramiteId,
            'cli_directo_id' => $cliDirectoId,
        ]);
    }
}
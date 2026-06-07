<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use Psr\Log\NullLogger;
use Tests\Support\Controllers\TestableTramitesn;

class TramitesnCobranzaAccessTest extends CIUnitTestCase
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
        TestableTramitesn::$useDatabaseLookups = false;
        TestableTramitesn::$skipLegacyFinalSaveSideEffects = false;
        session()->remove(['id', 'username', 'user_roles', 'user_permissions']);
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

    public function testVerSeccionCobroClienteInvokesCobroClienteSectionWithListPermissionOnly(): void
    {
        $this->seedSession([
            'list_cobro_cliente',
        ]);
        $this->seedTramiteWithTenantAccess(123, SGL_TRA_STATUS_COBRO_CLIENTE, 99, 1001, 2001);

        $response = $this->executeVerSeccionCobroCliente(123);

        $this->assertSame('update:123:deskapp/extra-pages/tramite_cobro_cliente_view:', $response);
    }

    public function testVerSeccionCobroClienteDoesNotRedirectToEvidenciasFinalesWhenAdvancedPermissionsArePresent(): void
    {
        $this->seedSession([
            'list_cobro_cliente',
            'section_pago_gestor',
            'important_ir_pago_gestor',
        ]);
        $this->seedTramiteWithTenantAccess(123, SGL_TRA_STATUS_COBRO_CLIENTE, 99, 1001, 2001);

        $response = $this->executeVerSeccionCobroCliente(123);

        $this->assertSame('update:123:deskapp/extra-pages/tramite_cobro_cliente_view:', $response);
    }

    public function testCobroClienteVerRedirectsToCanonicalVerSeccionCobroClienteRoute(): void
    {
        $this->seedSession([
            'list_cobro_cliente',
        ]);
        $this->seedTramiteWithTenantAccess(123, SGL_TRA_STATUS_COBRO_CLIENTE, 99, 1001, 2001);

        $response = $this->executeCobroClienteVer(123);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/deskapp/tramitesn/ver_seccion_cobro_cliente/123', $response->getHeaderLine('Location'));
    }

    public function testVerSeccionCobroClienteNoLongerAcceptsLegacyExtraPermissionsWithoutListPermission(): void
    {
        $this->seedSession([
            'section_final_costos',
            'important_ir_cobro_cliente',
        ]);
        $this->seedTramiteWithTenantAccess(123, SGL_TRA_STATUS_COBRO_CLIENTE, 99, 1001, 2001);

        $response = $this->executeVerSeccionCobroCliente(123);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/deskapp/tramitesn/update/123', $response->getHeaderLine('Location'));
        $this->assertSame('No tienes permisos para acceder a Cobranza', session()->getFlashdata('error'));
    }

    public function testUpdateRedirectsPagoGestorStatusToEvidenciasFinalesWithSectionPermissionOnly(): void
    {
        $this->seedSession([
            'section_pago_gestor',
        ]);
        $this->seedTramiteWithTenantAccess(123, SGL_TRA_STATUS_PAGO_GESTOR, 99, 1001, 2001);

        $response = $this->executeUpdate(123);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/deskapp/tramitesn/ver_seccion_evidencias_finales/123', $response->getHeaderLine('Location'));
    }

    public function testUpdateRedirectsCobroClienteStatusToCobroClienteSectionWithListPermission(): void
    {
        $this->seedSession([
            'list_cobro_cliente',
        ]);
        $this->seedTramiteWithTenantAccess(123, SGL_TRA_STATUS_COBRO_CLIENTE, 99, 1001, 2001);

        $response = $this->executeUpdate(123);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/deskapp/tramitesn/ver_seccion_cobro_cliente/123', $response->getHeaderLine('Location'));
    }

    private function executeVerSeccionCobroCliente(int $tramiteId)
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/ver_seccion_cobro_cliente/' . $tramiteId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $uri = new URI('http://example.com/deskapp/tramitesn/ver_seccion_cobro_cliente/' . $tramiteId);
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setMethod('get');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new class extends TestableTramitesn {
            public function update($id, $viewName = null, ?string $onlySection = null)
            {
                return 'update:' . (int) $id . ':' . (string) ($viewName ?? '') . ':' . (string) ($onlySection ?? '');
            }
        };
        $controller->initController($request, $response, $logger);

        return $controller->ver_seccion_cobro_cliente($tramiteId);
    }

    private function executeCobroClienteVer(int $tramiteId)
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/cobro_cliente/' . $tramiteId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $uri = new URI('http://example.com/deskapp/tramitesn/cobro_cliente/' . $tramiteId);
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setMethod('get');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller->cobro_cliente_ver($tramiteId);
    }

    private function executeUpdate(int $tramiteId)
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/update/' . $tramiteId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $uri = new URI('http://example.com/deskapp/tramitesn/update/' . $tramiteId);
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setMethod('get');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller->update($tramiteId);
    }

    private function seedSession(array $permissions): void
    {
        session()->set([
            'id' => 99,
            'username' => 'tester',
            'user_roles' => ['Admin'],
            'user_permissions' => $permissions,
        ]);
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
            'cli_directo_id' => ['type' => 'INTEGER', 'null' => true],
            'tra_status_id' => ['type' => 'INTEGER', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tramite');

        $this->forge->addField([
            'user_id' => ['type' => 'INTEGER'],
            'cliente_id' => ['type' => 'INTEGER'],
        ]);
        $this->forge->createTable('cliente_user');
    }

    private function seedTramiteWithTenantAccess(int $tramiteId, int $statusId, int $userId, int $cliDirectoId, int $clienteId): void
    {
        $this->db->table('cliente')->insert(['id' => $clienteId]);
        $this->db->table('cli_directo')->insert([
            'id' => $cliDirectoId,
            'cliente_id' => $clienteId,
        ]);
        $this->db->table('tramite')->insert([
            'id' => $tramiteId,
            'cli_directo_id' => $cliDirectoId,
            'tra_status_id' => $statusId,
        ]);
        $this->db->table('cliente_user')->insert([
            'user_id' => $userId,
            'cliente_id' => $clienteId,
        ]);
    }
}
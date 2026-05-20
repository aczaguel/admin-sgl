<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;
use Psr\Log\NullLogger;
use Tests\Support\Controllers\TestableTramitesn;

class TramitesnUpdateFinalSaveTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestableTramitesn::$tramiteRow = [];
        TestableTramitesn::$denyTenantAccess = false;
        TestableTramitesn::$useDatabaseLookups = false;
        TestableTramitesn::$skipLegacyFinalSaveSideEffects = false;
        session()->remove(['id', 'username', 'user_roles', 'user_permissions']);
    }

    public function testUpdateFinalSaveReturns403WhenTenantAccessIsDenied(): void
    {
        TestableTramitesn::$denyTenantAccess = true;

        $this->seedSession([
            'section_final_costos',
            'editar_final',
        ]);

        $response = $this->executeUpdateFinalSave();
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Acceso denegado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    public function testUpdateFinalSaveRequiresCobroClienteAccessPermission(): void
    {
        $this->seedSession([
            'editar_final',
        ]);

        $response = $this->executeUpdateFinalSave();
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Acceso denegado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    public function testUpdateFinalSaveRequiresEditarFinalPermission(): void
    {
        $this->seedSession([
            'section_final_costos',
        ]);

        $response = $this->executeUpdateFinalSave();
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Acceso denegado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    public function testUpdateFinalSaveReturns404WhenTramiteDoesNotExist(): void
    {
        $this->seedSession([
            'list_cobro_cliente',
            'editar_final',
        ]);

        $response = $this->executeUpdateFinalSave();
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('El trámite no existe.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    public function testUpdateFinalSaveReturns409WhenTramiteIsLocked(): void
    {
        TestableTramitesn::$tramiteRow = [
            'id' => 123,
            'tra_status_id' => SGL_TRA_STATUS_CONCLUIDO,
            'reembolso_status_id' => 0,
            'cobro_status_id' => 0,
        ];

        $this->seedSession([
            'list_cobro_cliente',
            'editar_final',
        ]);

        $response = $this->executeUpdateFinalSave();
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertStringContainsString('El trámite está concluido o cancelado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    private function executeUpdateFinalSave(): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/update_final_save/123';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $uri = new URI('http://example.com/deskapp/tramitesn/update_final_save/123');
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setMethod('post');
        $request->setGlobal('post', []);
        $request->setHeader('Accept', 'application/json');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller->update_final_save();
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
}
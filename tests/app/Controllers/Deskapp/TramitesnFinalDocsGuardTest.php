<?php

namespace Tests\App\Controllers\Deskapp;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use Psr\Log\NullLogger;
use Tests\Support\Controllers\TestableTramitesn;

class TramitesnFinalDocsGuardTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestableTramitesn::$tramiteRow = [];
        TestableTramitesn::$denyTenantAccess = false;
        $_FILES = [];
        session()->remove(['id', 'username', 'user_roles', 'user_permissions']);
    }

    public function testUploadFinalDocReturns403WhenTenantAccessIsDenied(): void
    {
        TestableTramitesn::$denyTenantAccess = true;

        $this->seedSession([
            'section_final_costos',
        ]);

        $response = $this->executeUploadFinalDoc(123, 16);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Acceso denegado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    public function testUploadFinalDocReturns404WhenTramiteDoesNotExist(): void
    {
        $this->seedSession([
            'section_final_costos',
        ]);

        $response = $this->executeUploadFinalDoc(123, 16);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('Trámite no encontrado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    public function testDeleteFinalDocRequiresSectionFinalCostosPermission(): void
    {
        $this->seedSession([]);

        $response = $this->executeDeleteFinalDoc(123, 16);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Acceso denegado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    public function testDeleteFinalDocReturns409WhenTramiteIsLocked(): void
    {
        TestableTramitesn::$tramiteRow = [
            'id' => 123,
            'tra_status_id' => SGL_TRA_STATUS_CONCLUIDO,
            'reembolso_status_id' => 0,
            'cobro_status_id' => 0,
        ];

        $this->seedSession([
            'section_final_costos',
        ]);

        $response = $this->executeDeleteFinalDoc(123, 16);
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertStringContainsString('El trámite está concluido o cancelado.', $response->getBody());
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success'] ?? true);
    }

    private function executeUploadFinalDoc(int $tramiteId, int $documentoId): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/upload_final_doc/' . $tramiteId . '/' . $documentoId;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $uri = new URI('http://example.com/deskapp/tramitesn/upload_final_doc/' . $tramiteId . '/' . $documentoId);
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setMethod('post');
        $request->setHeader('Accept', 'application/json');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller->upload_final_doc($tramiteId, $documentoId);
    }

    private function executeDeleteFinalDoc(int $tramiteId, int $documentoId, string $fileName = ''): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/deskapp/tramitesn/delete_final_doc';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $uri = new URI('http://example.com/deskapp/tramitesn/delete_final_doc');
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setMethod('post');
        $request->setGlobal('post', [
            'tramite_id' => $tramiteId,
            'documento_id' => $documentoId,
            'file' => $fileName,
        ]);
        $request->setHeader('Accept', 'application/json');

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller->delete_final_doc();
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
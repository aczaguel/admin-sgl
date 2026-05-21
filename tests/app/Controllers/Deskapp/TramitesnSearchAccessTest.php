<?php

namespace Tests\App\Controllers\Deskapp;

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

class TramitesnSearchAccessTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        session()->remove(['id', 'username', 'user_roles', 'user_permissions']);
    }

    public function testSearchRedirectsToLoginWhenSessionExpired(): void
    {
        $controller = $this->makeController('/deskapp/tramitesn/search', 'GET');

        $result = $controller->search();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/deskapp/auth/login', $result->getHeaderLine('Location'));
        $this->assertSame('Sesión expirada.', session()->getFlashdata('error'));
    }

    public function testSearchRedirectsToDashboardWithoutSearchPermission(): void
    {
        session()->set([
            'id' => 99,
            'username' => 'tester',
            'user_roles' => ['Admin'],
            'user_permissions' => [],
        ]);

        $controller = $this->makeController('/deskapp/tramitesn/search', 'GET');

        $result = $controller->search();

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/deskapp/dashboard', $result->getHeaderLine('Location'));
        $this->assertSame('No tienes permisos para buscar trámites.', session()->getFlashdata('error'));
    }

    private function makeController(string $path, string $method): TestableTramitesn
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = $method;

        $request = new IncomingRequest($config, new URI('http://example.com' . $path), null, new UserAgent());
        $request->setMethod(strtolower($method));

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableTramitesn();
        $controller->initController($request, $response, $logger);

        return $controller;
    }
}
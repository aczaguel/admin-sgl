<?php

namespace Tests\App\Controllers\Api\V1;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use Psr\Log\NullLogger;
use Tests\Support\Controllers\TestableApiV1Tramites;
use Tests\Support\Services\TestableExternalTramiteService;

class TramitesApiControllerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestableExternalTramiteService::resetState();
        TestableApiV1Tramites::$service = new TestableExternalTramiteService();
    }

    public function testCreateUsesIdempotencyHeaderAndReturns201(): void
    {
        $response = $this->executeCreate([
            'external_reference' => 'ERP-REF-CTRL-001',
            'contrato' => 'CTR-CTRL-001',
            'serie' => 'SER-CTRL-001',
            'tra_tipos_id' => 7,
            'entidad_id' => 14,
            'ent_municipio_id' => 266,
            'cli_directo_id' => 22,
            'cli_directo_ejecutivo_id' => 18,
        ], 'idem-ctrl-001', 'erp_ctrl');

        $payload = json_decode($response->getBody(), true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame('idem-ctrl-001', TestableExternalTramiteService::$lastCreateContext['context']['idempotencyKey']);
        $this->assertSame('erp_ctrl', TestableExternalTramiteService::$lastCreateContext['context']['sourceSystem']);
    }

    public function testShowByReferenceReturns200(): void
    {
        TestableExternalTramiteService::resetState();
        $service = new TestableExternalTramiteService();
        TestableApiV1Tramites::$service = $service;
        $service->createFromExternalPayload([
            'external_reference' => 'ERP-REF-CTRL-002',
            'contrato' => 'CTR-CTRL-002',
            'serie' => 'SER-CTRL-002',
            'tra_tipos_id' => 7,
            'entidad_id' => 14,
            'ent_municipio_id' => 266,
            'cli_directo_id' => 22,
            'cli_directo_ejecutivo_id' => 18,
        ], [], [
            'idempotencyKey' => 'idem-ctrl-002',
            'sourceSystem' => 'erp_ctrl',
        ]);

        $response = $this->executeShowByReference('ERP-REF-CTRL-002', 'erp_ctrl');
        $payload = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame('ERP-REF-CTRL-002', $payload['data']['external_reference']);
    }

    private function executeCreate(array $payload, string $idempotencyKey, string $sourceSystem): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/api/v1/tramites';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $uri = new URI('http://example.com/api/v1/tramites');
        $request = new IncomingRequest($config, $uri, json_encode($payload), new UserAgent());
        $request->setMethod('post');
        $request->setHeader('Accept', 'application/json');
        $request->setHeader('Content-Type', 'application/json');
        $request->setHeader('Idempotency-Key', $idempotencyKey);
        $request->setHeader('X-Source-System', $sourceSystem);

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableApiV1Tramites();
        $controller->initController($request, $response, $logger);

        return $controller->create();
    }

    private function executeShowByReference(string $externalReference, string $sourceSystem): Response
    {
        $config = new App();
        $_SERVER['REQUEST_URI'] = '/api/v1/tramites/referencia/' . $externalReference;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $uri = new URI('http://example.com/api/v1/tramites/referencia/' . $externalReference);
        $request = new IncomingRequest($config, $uri, null, new UserAgent());
        $request->setMethod('get');
        $request->setHeader('Accept', 'application/json');
        $request->setHeader('X-Source-System', $sourceSystem);

        $response = new Response($config);
        $logger = new NullLogger();

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);

        $controller = new TestableApiV1Tramites();
        $controller->initController($request, $response, $logger);

        return $controller->showByReference($externalReference);
    }
}
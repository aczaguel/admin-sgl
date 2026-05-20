<?php

namespace Tests\App\Services;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Services\TestableExternalTramiteService;

class ExternalTramiteServiceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestableExternalTramiteService::resetState();
    }

    public function testCreateFromExternalPayloadRequiresIdempotencyKey(): void
    {
        $service = new TestableExternalTramiteService();

        $result = $service->createFromExternalPayload($this->basePayload());

        $this->assertFalse($result['success']);
        $this->assertSame(400, $result['statusCode']);
        $this->assertSame('El header Idempotency-Key es obligatorio.', $result['message']);
    }

    public function testCreateFromExternalPayloadCachesResponseForSameIdempotencyKeyAndPayload(): void
    {
        $service = new TestableExternalTramiteService();

        $first = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-001',
            'sourceSystem' => 'erp_demo',
        ]);
        $second = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-001',
            'sourceSystem' => 'erp_demo',
        ]);

        $this->assertTrue($first['success']);
        $this->assertTrue($second['success']);
        $this->assertSame($first['tramite_id'], $second['tramite_id']);
        $this->assertTrue($second['idempotent_replay']);
        $this->assertCount(1, TestableExternalTramiteService::$webhookEvents);
    }

    public function testCreateFromExternalPayloadRejectsSameIdempotencyKeyWithDifferentPayload(): void
    {
        $service = new TestableExternalTramiteService();
        $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-002',
            'sourceSystem' => 'erp_demo',
        ]);

        $payload = $this->basePayload();
        $payload['contrato'] = 'CTR-999';
        $result = $service->createFromExternalPayload($payload, [], [
            'idempotencyKey' => 'idem-002',
            'sourceSystem' => 'erp_demo',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(409, $result['statusCode']);
        $this->assertSame('El Idempotency-Key ya fue utilizado con un payload distinto.', $result['message']);
    }

    public function testCreateFromExternalPayloadRejectsDuplicateExternalReference(): void
    {
        $service = new TestableExternalTramiteService();
        $first = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-003-a',
            'sourceSystem' => 'erp_demo',
        ]);

        $result = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-003-b',
            'sourceSystem' => 'erp_demo',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(409, $result['statusCode']);
        $this->assertSame($first['tramite_id'], $result['existing_tramite_id']);
    }

    public function testCreateFromExternalPayloadRejectsDuplicateBusinessTramiteAndReturnsDetails(): void
    {
        TestableExternalTramiteService::$duplicateTramite = [
            'id' => 777,
            'folio' => 'TR-2026-000777',
            'contrato' => 'CTR-EXISTENTE',
            'serie' => 'SER123',
            'user_id' => 44,
            'created_at' => '2026-05-13 09:55:00',
            'tra_tipos_id' => 7,
            'tipo_tramite' => 'TENENCIA',
        ];

        $service = new TestableExternalTramiteService();
        $result = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-dup-business-001',
            'sourceSystem' => 'erp_demo',
        ]);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['duplicate']);
        $this->assertSame(409, $result['statusCode']);
        $this->assertSame(777, $result['existing_tramite_id']);
        $this->assertSame('TENENCIA', $result['duplicate_details']['tipo_tramite_existente']);
        $this->assertSame('Usuario Previo Test', $result['duplicate_details']['nombre_usuario_existente']);
        $this->assertSame('TR-2026-000777', $result['existing_data']['folio']);
        $this->assertSame('SER123', $result['existing_data']['serie']);
    }

    public function testCreateFromExternalPayloadQueuesCreatedWebhookEnvelope(): void
    {
        $service = new TestableExternalTramiteService();

        $created = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-005',
            'sourceSystem' => 'erp_demo',
        ]);

        $this->assertCount(1, TestableExternalTramiteService::$webhookEvents);
        $event = TestableExternalTramiteService::$webhookEvents[0];

        $this->assertSame('tramite.created', $event['event_name']);
        $this->assertSame('tramite.created', $event['payload']['event']);
        $this->assertSame('erp_demo', $event['payload']['source_system']);
        $this->assertSame('ERP-REF-001', $event['payload']['external_reference']);
        $this->assertSame($created['tramite_id'], $event['payload']['tramite']['id']);
    }

    public function testQueueStatusChangedEventIfTrackedQueuesWebhook(): void
    {
        $service = new TestableExternalTramiteService();
        $created = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-006',
            'sourceSystem' => 'erp_demo',
        ]);

        TestableExternalTramiteService::$snapshots[$created['tramite_id']]['tra_status_id'] = 25;
        TestableExternalTramiteService::$snapshots[$created['tramite_id']]['tra_status'] = 'PAGO DERECHOS COTIZACION';

        $queued = $service->queueStatusChangedEventIfTracked($created['tramite_id'], 22, 25);

        $this->assertTrue($queued);
        $this->assertCount(2, TestableExternalTramiteService::$webhookEvents);

        $event = TestableExternalTramiteService::$webhookEvents[1];
        $this->assertSame('tramite.status_changed', $event['event_name']);
        $this->assertSame(22, $event['payload']['previous_status']['id']);
        $this->assertSame(25, $event['payload']['current_status']['id']);
        $this->assertSame($created['tramite_id'], $event['payload']['tramite']['id']);
    }

    public function testGetStatusSnapshotByExternalReferenceReturnsSnapshot(): void
    {
        $service = new TestableExternalTramiteService();
        $created = $service->createFromExternalPayload($this->basePayload(), [], [
            'idempotencyKey' => 'idem-004',
            'sourceSystem' => 'erp_demo',
        ]);

        $result = $service->getStatusSnapshotByExternalReference('ERP-REF-001', 'erp_demo');

        $this->assertTrue($result['success']);
        $this->assertSame(200, $result['statusCode']);
        $this->assertSame($created['tramite_id'], $result['data']['id']);
        $this->assertSame('ERP-REF-001', $result['data']['external_reference']);
    }

    private function basePayload(): array
    {
        return [
            'external_reference' => 'ERP-REF-001',
            'contrato' => 'CTR-001',
            'unidad' => 'Unidad 1',
            'serie' => 'SER123',
            'placas' => 'ABC123',
            'tra_tipos_id' => 7,
            'entidad_id' => 14,
            'ent_municipio_id' => 266,
            'cli_directo_id' => 22,
            'cli_directo_ejecutivo_id' => 18,
            'observaciones' => 'Alta externa',
        ];
    }
}
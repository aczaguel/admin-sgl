<?php

namespace Tests\Support\Services;

use App\Services\ExternalTramiteService;

class TestableExternalTramiteService extends ExternalTramiteService
{
    public static $idempotencyRecords = [];
    public static $referenceMappings = [];
    public static $webhookEvents = [];
    public static $snapshots = [];
    public static $ejecutivoUserId = 44;
    public static $nextTramiteId = 1001;
    public static $nextWebhookEventId = 1;
    public static $lastCreateContext = [];
    public static $duplicateTramite = null;

    public function __construct()
    {
    }

    public static function resetState(): void
    {
        static::$idempotencyRecords = [];
        static::$referenceMappings = [];
        static::$webhookEvents = [];
        static::$snapshots = [];
        static::$ejecutivoUserId = 44;
        static::$nextTramiteId = 1001;
        static::$nextWebhookEventId = 1;
        static::$lastCreateContext = [];
        static::$duplicateTramite = null;
    }

    protected function ensureExternalApiSupportTables(): ?array
    {
        return null;
    }

    protected function ensureReferenceTableAvailable(): ?array
    {
        return null;
    }

    protected function isWebhookQueueAvailable(): bool
    {
        return true;
    }

    protected function resolveEjecutivoUserId(int $ejecutivoId): int
    {
        return static::$ejecutivoUserId;
    }

    protected function validatePayload(array $payload, bool $allowStatusOverride, array $context = []): array
    {
        $errors = [];
        if (!empty($context['requireExternalReference']) && trim((string) ($payload['external_reference'] ?? '')) === '') {
            $errors['external_reference'] = 'La referencia externa es obligatoria.';
        }

        if ((int) ($payload['cli_directo_ejecutivo_id'] ?? 0) <= 0) {
            $errors['cli_directo_ejecutivo_id'] = 'El ejecutivo es obligatorio.';
        }

        return $errors;
    }

    protected function createTramite(array $payload, array $requestFiles, array $context): array
    {
        static::$lastCreateContext = [
            'payload' => $payload,
            'context' => $context,
        ];

        $normalized = $this->normalizePayload($payload);
        $tramiteId = static::$nextTramiteId++;
        $snapshot = [
            'id' => $tramiteId,
            'folio' => $normalized['folio'] !== '' ? $normalized['folio'] : 'TR-TEST-' . $tramiteId,
            'tra_status_id' => 22,
            'tra_status' => 'DCTOS COMPLETOS',
            'cobro_status_id' => 1,
            'cobro_status' => 'PENDIENTE',
            'empresa_solicitante' => 'Cliente Test',
            'ejecutivo' => 'Ejecutivo Test',
        ];

        if (!empty($context['requireExternalReference'])) {
            $this->createReferenceMapping((string) $context['sourceSystem'], $normalized['external_reference'], $tramiteId, $snapshot);
        }

        static::$snapshots[$tramiteId] = $snapshot;

        $result = [
            'success' => true,
            'message' => 'Trámite creado exitosamente.',
            'tramite_id' => $tramiteId,
            'folio' => $snapshot['folio'],
            'documentos_registrados' => 0,
            'status_snapshot' => $this->buildStatusSnapshot($tramiteId),
            'statusCode' => (int) ($context['responseStatusCode'] ?? 201),
        ];

        if (!empty($context['requireExternalReference'])) {
            $result['external_reference'] = $normalized['external_reference'];
            $result['source_system'] = (string) $context['sourceSystem'];
            $this->rememberIdempotentResponse(
                (string) $context['sourceSystem'],
                (string) ($context['idempotencyKey'] ?? ''),
                (string) ($context['requestHash'] ?? ''),
                $tramiteId,
                $result,
                (int) ($result['statusCode'] ?? 201)
            );
            $this->queueWebhookEvent('tramite.created', (string) $context['sourceSystem'], $tramiteId, $normalized['external_reference'], $result['status_snapshot']);
        }

        return $result;
    }

    protected function findDuplicateTramiteByTipoSerie(int $traTiposId, string $serie): ?array
    {
        return is_array(static::$duplicateTramite) ? static::$duplicateTramite : null;
    }

    protected function resolveUserFullName(int $userId): string
    {
        return $userId > 0 ? 'Usuario Previo Test' : 'N/A';
    }

    protected function formatDuplicateCreatedAt(string $createdAt): string
    {
        return $createdAt !== '' ? '13/05/2026 09:55' : 'N/A';
    }

    protected function findIdempotencyRecord(string $sourceSystem, string $idempotencyKey): ?array
    {
        $key = $sourceSystem . '|' . $idempotencyKey;
        return static::$idempotencyRecords[$key] ?? null;
    }

    protected function rememberIdempotentResponse(string $sourceSystem, string $idempotencyKey, string $requestHash, int $tramiteId, array $response, int $statusCode): void
    {
        if ($idempotencyKey === '') {
            return;
        }

        static::$idempotencyRecords[$sourceSystem . '|' . $idempotencyKey] = [
            'request_hash' => $requestHash,
            'status_code' => $statusCode,
            'response' => $response,
            'tramite_id' => $tramiteId,
        ];
    }

    protected function findReferenceMapping(string $sourceSystem, string $externalReference): ?array
    {
        $key = $sourceSystem . '|' . $externalReference;
        return static::$referenceMappings[$key] ?? null;
    }

    protected function createReferenceMapping(string $sourceSystem, string $externalReference, int $tramiteId, array $snapshot): void
    {
        static::$referenceMappings[$sourceSystem . '|' . $externalReference] = [
            'source_system' => $sourceSystem,
            'external_reference' => $externalReference,
            'tramite_id' => $tramiteId,
            'last_status_payload_json' => $snapshot,
        ];
    }

    protected function loadReferenceMetadataByTramiteId(int $tramiteId): ?array
    {
        foreach (static::$referenceMappings as $mapping) {
            if ((int) ($mapping['tramite_id'] ?? 0) === $tramiteId) {
                return [
                    'source_system' => $mapping['source_system'],
                    'external_reference' => $mapping['external_reference'],
                ];
            }
        }

        return null;
    }

    protected function updateReferenceSnapshot(int $tramiteId, array $snapshot): void
    {
        foreach (static::$referenceMappings as $key => $mapping) {
            if ((int) ($mapping['tramite_id'] ?? 0) === $tramiteId) {
                static::$referenceMappings[$key]['last_status_payload_json'] = $snapshot;
            }
        }
    }

    protected function queueWebhookEvent(string $eventName, string $sourceSystem, int $tramiteId, string $externalReference, array $payload, array $extraPayload = []): void
    {
        static::$webhookEvents[] = [
            'id' => static::$nextWebhookEventId++,
            'event_name' => $eventName,
            'source_system' => $sourceSystem,
            'tramite_id' => $tramiteId,
            'external_reference' => $externalReference,
            'webhook_url' => 'https://example.test/webhook',
            'delivery_status' => 'pending',
            'attempts' => 0,
            'payload' => $this->buildWebhookEventPayload($eventName, $sourceSystem, $externalReference, $payload, $extraPayload),
        ];
    }

    protected function resolveTramiteStatusName(int $statusId): string
    {
        $names = [
            22 => 'DCTOS COMPLETOS',
            25 => 'PAGO DERECHOS COTIZACION',
            26 => 'PAGO DERECHOS LINEA CAPTURA',
            27 => 'PAGO DERECHOS DOCUMENTOS',
            28 => 'COBRO CLIENTE',
        ];

        return $names[$statusId] ?? 'STATUS ' . $statusId;
    }

    protected function buildStatusSnapshot(int $tramiteId): array
    {
        $snapshot = static::$snapshots[$tramiteId] ?? [];
        if ($snapshot === []) {
            return [];
        }

        $meta = $this->loadReferenceMetadataByTramiteId($tramiteId);
        if ($meta !== null) {
            $snapshot['external_reference'] = $meta['external_reference'];
            $snapshot['source_system'] = $meta['source_system'];
        }

        return $snapshot;
    }
}
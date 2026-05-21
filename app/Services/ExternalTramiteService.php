<?php

namespace App\Services;

class ExternalTramiteService
{
    private $db;

    public function __construct($db = null)
    {
        helper(['cliente_filter', 'cliente_context']);
        $this->db = $db ?? \Config\Database::connect();
    }

    public function createFromWizardPayload(array $payload, array $requestFiles, int $actorUserId): array
    {
        return $this->createTramite($payload, $requestFiles, [
            'actorUserId' => $actorUserId,
            'ownerUserId' => $actorUserId,
            'origin' => 'wizard',
            'validateClienteAccessUserId' => $actorUserId,
            'allowStatusOverride' => false,
            'responseStatusCode' => 200,
        ]);
    }

    public function createFromExternalPayload(array $payload, array $requestFiles = [], array $options = []): array
    {
        if ($setupError = $this->ensureExternalApiSupportTables()) {
            return $setupError;
        }

        $sourceSystem = $this->resolveSourceSystem($payload, $options);
        $idempotencyKey = trim((string) ($options['idempotencyKey'] ?? ''));
        if ($idempotencyKey === '') {
            return [
                'success' => false,
                'message' => 'El header Idempotency-Key es obligatorio.',
                'statusCode' => 400,
            ];
        }

        $cliDirectoEjecutivoId = (int) ($payload['cli_directo_ejecutivo_id'] ?? 0);
        $ownerUserId = $this->resolveEjecutivoUserId($cliDirectoEjecutivoId);
        if ($ownerUserId <= 0) {
            return [
                'success' => false,
                'message' => 'El ejecutivo no tiene un usuario ligado para asignar el trámite.',
                'statusCode' => 422,
            ];
        }

        $config = config('ExternalApi');
        $actorUserId = (int) ($config->integrationUserId > 0 ? $config->integrationUserId : $ownerUserId);

        $normalized = $this->normalizePayload($payload);
        if ($normalized['external_reference'] === '') {
            return [
                'success' => false,
                'message' => 'external_reference es obligatorio para la API externa.',
                'statusCode' => 422,
                'errors' => ['external_reference' => 'La referencia externa es obligatoria.'],
            ];
        }

        $requestHash = $this->buildIdempotencyRequestHash($normalized, $requestFiles, $sourceSystem);
        $idempotencyHit = $this->findIdempotencyRecord($sourceSystem, $idempotencyKey);
        if ($idempotencyHit !== null) {
            if (($idempotencyHit['request_hash'] ?? '') !== $requestHash) {
                return [
                    'success' => false,
                    'message' => 'El Idempotency-Key ya fue utilizado con un payload distinto.',
                    'statusCode' => 409,
                ];
            }

            $cached = $idempotencyHit['response'] ?? [];
            if (!is_array($cached)) {
                $cached = [];
            }
            $cached['statusCode'] = (int) ($idempotencyHit['status_code'] ?? 200);
            $cached['idempotent_replay'] = true;
            return $cached;
        }

        $existingReference = $this->findReferenceMapping($sourceSystem, $normalized['external_reference']);
        if ($existingReference !== null) {
            $existingTramiteId = (int) ($existingReference['tramite_id'] ?? 0);
            return [
                'success' => false,
                'message' => 'La referencia externa ya existe para este sistema origen.',
                'statusCode' => 409,
                'existing_tramite_id' => $existingTramiteId,
                'external_reference' => $normalized['external_reference'],
                'source_system' => $sourceSystem,
                'data' => $existingTramiteId > 0 ? $this->buildStatusSnapshot($existingTramiteId) : null,
            ];
        }

        $duplicateTramite = $this->findDuplicateTramiteByTipoSerie((int) $normalized['tra_tipos_id'], (string) $normalized['serie']);
        if ($duplicateTramite !== null) {
            return $this->buildDuplicateTramiteResponse($duplicateTramite);
        }

        return $this->createTramite($payload, $requestFiles, [
            'actorUserId' => $actorUserId,
            'ownerUserId' => $ownerUserId,
            'origin' => 'api_externa',
            'validateClienteAccessUserId' => null,
            'allowStatusOverride' => true,
            'responseStatusCode' => 201,
            'sourceSystem' => $sourceSystem,
            'idempotencyKey' => $idempotencyKey,
            'requestHash' => $requestHash,
            'requireExternalReference' => true,
        ]);
    }

    public function getStatusSnapshot(int $tramiteId): array
    {
        if ($tramiteId <= 0) {
            return [
                'success' => false,
                'message' => 'ID de trámite inválido.',
                'statusCode' => 400,
            ];
        }

        $snapshot = $this->buildStatusSnapshot($tramiteId);
        if ($snapshot === []) {
            return [
                'success' => false,
                'message' => 'Trámite no encontrado.',
                'statusCode' => 404,
            ];
        }

        $this->updateReferenceSnapshot($tramiteId, $snapshot);

        return [
            'success' => true,
            'message' => 'Consulta realizada correctamente.',
            'statusCode' => 200,
            'data' => $snapshot,
        ];
    }

    public function getStatusSnapshotByExternalReference(string $externalReference, string $sourceSystem = ''): array
    {
        $externalReference = trim($externalReference);
        if ($externalReference === '') {
            return [
                'success' => false,
                'message' => 'La referencia externa es obligatoria.',
                'statusCode' => 400,
            ];
        }

        if ($setupError = $this->ensureReferenceTableAvailable()) {
            return $setupError;
        }

        $sourceSystem = trim($sourceSystem) !== '' ? trim($sourceSystem) : config('ExternalApi')->defaultSourceSystem;
        $mapping = $this->findReferenceMapping($sourceSystem, $externalReference);
        if ($mapping === null) {
            return [
                'success' => false,
                'message' => 'No existe un trámite ligado a esa referencia externa.',
                'statusCode' => 404,
            ];
        }

        $result = $this->getStatusSnapshot((int) ($mapping['tramite_id'] ?? 0));
        if (!($result['success'] ?? false)) {
            return $result;
        }

        if (is_array($result['data'] ?? null)) {
            $result['data']['external_reference'] = $externalReference;
            $result['data']['source_system'] = $sourceSystem;
        }

        return $result;
    }

    public function queueStatusChangedEventIfTracked(int $tramiteId, int $oldStatusId, int $newStatusId): bool
    {
        if ($tramiteId <= 0 || $oldStatusId <= 0 || $newStatusId <= 0 || $oldStatusId === $newStatusId) {
            return false;
        }

        if (!$this->isWebhookQueueAvailable()) {
            return false;
        }

        $referenceMeta = $this->loadReferenceMetadataByTramiteId($tramiteId);
        if ($referenceMeta === null) {
            return false;
        }

        $snapshot = $this->buildStatusSnapshot($tramiteId);
        if ($snapshot === []) {
            return false;
        }

        $this->updateReferenceSnapshot($tramiteId, $snapshot);
        $this->queueWebhookEvent(
            'tramite.status_changed',
            (string) ($referenceMeta['source_system'] ?? config('ExternalApi')->defaultSourceSystem),
            $tramiteId,
            (string) ($referenceMeta['external_reference'] ?? ''),
            $snapshot,
            [
                'previous_status' => [
                    'id' => $oldStatusId,
                    'name' => $this->resolveTramiteStatusName($oldStatusId),
                ],
                'current_status' => [
                    'id' => $newStatusId,
                    'name' => (string) ($snapshot['tra_status'] ?? $this->resolveTramiteStatusName($newStatusId)),
                ],
            ]
        );

        return true;
    }

    public function dispatchPendingWebhookEvents(int $limit = 20): array
    {
        if ($setupError = $this->ensureExternalApiSupportTables()) {
            return [
                'success' => false,
                'message' => (string) ($setupError['message'] ?? 'No fue posible validar la infraestructura de webhooks.'),
                'statusCode' => (int) ($setupError['statusCode'] ?? 503),
                'processed' => 0,
                'delivered' => 0,
                'failed' => 0,
            ];
        }

        $events = $this->listPendingWebhookEvents($limit > 0 ? $limit : 20);
        $summary = [
            'success' => true,
            'message' => 'No hay webhooks pendientes.',
            'processed' => 0,
            'delivered' => 0,
            'failed' => 0,
        ];

        foreach ($events as $event) {
            $summary['processed']++;

            $url = trim((string) ($event['webhook_url'] ?? ''));
            if ($url === '') {
                $this->markWebhookFailed($event, 'El evento no tiene webhook_url configurada.');
                $summary['failed']++;
                continue;
            }

            $payload = json_decode((string) ($event['payload_json'] ?? '{}'), true);
            if (!is_array($payload)) {
                $this->markWebhookFailed($event, 'El payload_json del evento no es un JSON válido.');
                $summary['failed']++;
                continue;
            }

            try {
                $response = $this->sendWebhookHttpRequest($url, $payload);
                $statusCode = (int) ($response['statusCode'] ?? 0);
                if ($statusCode >= 200 && $statusCode < 300) {
                    $this->markWebhookDelivered($event);
                    $summary['delivered']++;
                    continue;
                }

                $this->markWebhookFailed($event, 'Respuesta HTTP no exitosa: ' . $statusCode);
                $summary['failed']++;
            } catch (\Throwable $e) {
                $this->markWebhookFailed($event, $e->getMessage());
                $summary['failed']++;
            }
        }

        if ($summary['processed'] > 0) {
            $summary['message'] = sprintf(
                'Se procesaron %d webhooks pendientes (%d entregados, %d con error).',
                $summary['processed'],
                $summary['delivered'],
                $summary['failed']
            );
        }

        return $summary;
    }

    protected function createTramite(array $payload, array $requestFiles, array $context): array
    {
        $normalized = $this->normalizePayload($payload);
        $errors = $this->validatePayload($normalized, (bool) ($context['allowStatusOverride'] ?? false), $context);
        if ($errors !== []) {
            return [
                'success' => false,
                'message' => 'Payload inválido.',
                'errors' => $errors,
                'statusCode' => 422,
            ];
        }

        $validateClienteAccessUserId = $context['validateClienteAccessUserId'] ?? null;
        if ($validateClienteAccessUserId !== null && !$this->validarAccesoCliente((int) $validateClienteAccessUserId, (int) $normalized['cli_directo_id'])) {
            return [
                'success' => false,
                'message' => 'No tiene permisos para crear trámites para este cliente.',
                'statusCode' => 403,
            ];
        }

        $actorUserId = (int) ($context['actorUserId'] ?? 0);
        $ownerUserId = (int) ($context['ownerUserId'] ?? $actorUserId);
        $folio = $normalized['folio'] !== '' ? $normalized['folio'] : $this->generateSuggestedFolio();
        $statusValues = $this->resolveStatusValues($normalized, (bool) ($context['allowStatusOverride'] ?? false));

        $tramiteData = [
            'folio' => $folio,
            'contrato' => $normalized['contrato'],
            'unidad' => $normalized['unidad'],
            'serie' => $normalized['serie'],
            'placas' => $normalized['placas'],
            'tra_tipos_id' => (int) $normalized['tra_tipos_id'],
            'entidad_id' => (int) $normalized['entidad_id'],
            'ent_municipio_id' => (int) $normalized['ent_municipio_id'],
            'cli_directo_id' => (int) $normalized['cli_directo_id'],
            'cli_directo_ejecutivo_id' => (int) $normalized['cli_directo_ejecutivo_id'],
            'empresa_gestora_id' => $normalized['empresa_gestora_id'] !== null ? (int) $normalized['empresa_gestora_id'] : null,
            'gestor_id' => $normalized['gestor_id'] !== null ? (int) $normalized['gestor_id'] : null,
            'tra_status_id' => $statusValues['tra_status_id'],
            'cobro_status_id' => $statusValues['cobro_status_id'],
            'reembolso_status_id' => $statusValues['reembolso_status_id'],
            'pago_gestor_st_id' => $statusValues['pago_gestor_st_id'],
            'observaciones' => $normalized['observaciones'],
            'user_id' => $ownerUserId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'started_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->transStart();

        try {
            $this->db->table('tramite')->insert($tramiteData);
            $tramiteId = (int) $this->db->insertID();
            if ($tramiteId <= 0) {
                throw new \RuntimeException('No se pudo crear el trámite.');
            }

            $documentosRegistrados = $this->seedBaseDocumentsForTipoTramite(
                $tramiteId,
                $folio,
                (int) $normalized['tra_tipos_id'],
                $actorUserId > 0 ? $actorUserId : $ownerUserId
            );
            $documentosRegistrados += $this->storeUploadedDocuments($tramiteId, $requestFiles);
            $documentosRegistrados += $this->storeInlineDocuments($tramiteId, $normalized['documentos']);

            $this->registrarBitacora(
                $tramiteId,
                sprintf('Trámite creado mediante %s', $context['origin'] ?? 'integración'),
                $actorUserId > 0 ? $actorUserId : $ownerUserId,
                $folio
            );

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción de creación.');
            }

            if (!empty($context['requireExternalReference'])) {
                $this->createReferenceMapping(
                    (string) ($context['sourceSystem'] ?? config('ExternalApi')->defaultSourceSystem),
                    $normalized['external_reference'],
                    $tramiteId,
                    $this->buildStatusSnapshot($tramiteId)
                );
            }

            $result = [
                'success' => true,
                'message' => 'Trámite creado exitosamente.',
                'tramite_id' => $tramiteId,
                'folio' => $folio,
                'documentos_registrados' => $documentosRegistrados,
                'status_snapshot' => $this->buildStatusSnapshot($tramiteId),
                'statusCode' => (int) ($context['responseStatusCode'] ?? 201),
            ];

            if (!empty($context['requireExternalReference'])) {
                $result['external_reference'] = $normalized['external_reference'];
                $result['source_system'] = (string) ($context['sourceSystem'] ?? config('ExternalApi')->defaultSourceSystem);
                $this->rememberIdempotentResponse(
                    $result['source_system'],
                    (string) ($context['idempotencyKey'] ?? ''),
                    (string) ($context['requestHash'] ?? ''),
                    $tramiteId,
                    $result,
                    (int) ($result['statusCode'] ?? 201)
                );
                $this->queueWebhookEvent('tramite.created', $result['source_system'], $tramiteId, $normalized['external_reference'], (array) $result['status_snapshot']);
            }

            return $result;
        } catch (\Throwable $e) {
            $this->db->transRollback();

            return [
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
                'statusCode' => 500,
            ];
        }
    }

    protected function normalizePayload(array $payload): array
    {
        $statuses = is_array($payload['statuses'] ?? null) ? $payload['statuses'] : [];
        $documentos = is_array($payload['documentos'] ?? null) ? $payload['documentos'] : [];

        return [
            'external_reference' => trim((string) ($payload['external_reference'] ?? '')),
            'folio' => trim((string) ($payload['folio'] ?? '')),
            'contrato' => trim((string) ($payload['contrato'] ?? '')),
            'unidad' => trim((string) ($payload['unidad'] ?? '')),
            'serie' => trim((string) ($payload['serie'] ?? '')),
            'placas' => trim((string) ($payload['placas'] ?? '')),
            'tra_tipos_id' => (int) ($payload['tra_tipos_id'] ?? 0),
            'entidad_id' => (int) ($payload['entidad_id'] ?? 0),
            'ent_municipio_id' => (int) ($payload['ent_municipio_id'] ?? 0),
            'cli_directo_id' => (int) ($payload['cli_directo_id'] ?? 0),
            'cli_directo_ejecutivo_id' => (int) ($payload['cli_directo_ejecutivo_id'] ?? 0),
            'empresa_gestora_id' => $this->normalizeNullableInt($payload['empresa_gestora_id'] ?? null),
            'gestor_id' => $this->normalizeNullableInt($payload['gestor_id'] ?? null),
            'observaciones' => trim((string) ($payload['observaciones'] ?? '')),
            'documentos' => $documentos,
            'tra_status_id' => $this->normalizeNullableInt($statuses['tra_status_id'] ?? ($payload['tra_status_id'] ?? null)),
            'cobro_status_id' => $this->normalizeNullableInt($statuses['cobro_status_id'] ?? ($payload['cobro_status_id'] ?? null)),
            'reembolso_status_id' => $this->normalizeNullableInt($statuses['reembolso_status_id'] ?? ($payload['reembolso_status_id'] ?? null)),
            'pago_gestor_st_id' => $this->normalizeNullableInt($statuses['pago_gestor_st_id'] ?? ($payload['pago_gestor_st_id'] ?? null)),
        ];
    }

    protected function validatePayload(array $payload, bool $allowStatusOverride, array $context = []): array
    {
        $errors = [];

        if (!empty($context['requireExternalReference']) && $payload['external_reference'] === '') {
            $errors['external_reference'] = 'La referencia externa es obligatoria.';
        }

        if ($payload['contrato'] === '') {
            $errors['contrato'] = 'El contrato es obligatorio.';
        }
        if ($payload['serie'] === '') {
            $errors['serie'] = 'La serie es obligatoria.';
        }
        if ($payload['tra_tipos_id'] <= 0) {
            $errors['tra_tipos_id'] = 'El tipo de trámite es obligatorio.';
        }
        if ($payload['entidad_id'] <= 0) {
            $errors['entidad_id'] = 'La entidad es obligatoria.';
        }
        if ($payload['ent_municipio_id'] <= 0) {
            $errors['ent_municipio_id'] = 'El municipio es obligatorio.';
        }
        if ($payload['cli_directo_id'] <= 0) {
            $errors['cli_directo_id'] = 'La empresa solicitante es obligatoria.';
        }
        if ($payload['cli_directo_ejecutivo_id'] <= 0) {
            $errors['cli_directo_ejecutivo_id'] = 'El ejecutivo es obligatorio.';
        }

        if ($errors !== []) {
            return $errors;
        }

        if (!$this->existsById('tra_tipos', 'id', $payload['tra_tipos_id'])) {
            $errors['tra_tipos_id'] = 'El tipo de trámite no existe.';
        }
        if (!$this->existsById('entidad', 'id', $payload['entidad_id'])) {
            $errors['entidad_id'] = 'La entidad no existe.';
        }
        if (!$this->existsMunicipioForEntidad($payload['ent_municipio_id'], $payload['entidad_id'])) {
            $errors['ent_municipio_id'] = 'El municipio no pertenece a la entidad indicada.';
        }
        if (!$this->existsById('cli_directo', 'id', $payload['cli_directo_id'])) {
            $errors['cli_directo_id'] = 'La empresa solicitante no existe.';
        }
        if (!$this->existsEjecutivoForCliente($payload['cli_directo_ejecutivo_id'], $payload['cli_directo_id'])) {
            $errors['cli_directo_ejecutivo_id'] = 'El ejecutivo no pertenece a la empresa solicitante.';
        }
        if ($payload['empresa_gestora_id'] !== null && !$this->existsById('ges_empresa_gestora', 'id', (int) $payload['empresa_gestora_id'])) {
            $errors['empresa_gestora_id'] = 'La empresa gestora no existe.';
        }
        if ($payload['gestor_id'] !== null && !$this->existsById('ges_gestor', 'id', (int) $payload['gestor_id'])) {
            $errors['gestor_id'] = 'El gestor no existe.';
        }
        if ($allowStatusOverride && $payload['tra_status_id'] !== null && in_array((int) $payload['tra_status_id'], SGL_TRA_STATUS_LOCKED_IDS, true)) {
            $errors['tra_status_id'] = 'No se puede crear un trámite externo directamente en estatus bloqueado.';
        }

        foreach ($payload['documentos'] as $index => $documento) {
            if (!is_array($documento)) {
                $errors['documentos.' . $index] = 'Cada documento debe ser un objeto JSON.';
                continue;
            }

            $nombre = trim((string) ($documento['nombre'] ?? ''));
            $contenido = trim((string) ($documento['contenido_base64'] ?? ''));
            if ($nombre === '' || $contenido === '') {
                $errors['documentos.' . $index] = 'Cada documento inline requiere nombre y contenido_base64.';
            }
        }

        return $errors;
    }

    protected function resolveStatusValues(array $payload, bool $allowStatusOverride): array
    {
        $traStatusId = $allowStatusOverride && $payload['tra_status_id'] !== null
            ? (int) $payload['tra_status_id']
            : SGL_TRA_STATUS_DCTOS_COMPLETOS;

        $cobroStatusId = $allowStatusOverride && $payload['cobro_status_id'] !== null
            ? (int) $payload['cobro_status_id']
            : $this->resolveDefaultCobroStatusId();

        return [
            'tra_status_id' => $traStatusId,
            'cobro_status_id' => $cobroStatusId,
            'reembolso_status_id' => $allowStatusOverride ? $payload['reembolso_status_id'] : null,
            'pago_gestor_st_id' => $allowStatusOverride ? $payload['pago_gestor_st_id'] : null,
        ];
    }

    protected function resolveDefaultCobroStatusId(): ?int
    {
        $preferredIds = [];
        if (defined('SGL_COBRO_STATUS_PENDIENTE_ALTA')) {
            $preferredIds[] = (int) SGL_COBRO_STATUS_PENDIENTE_ALTA;
        }
        $preferredIds[] = 22;

        foreach (array_unique(array_filter($preferredIds)) as $statusId) {
            if ($this->existsById('cobro_statuses', 'id', (int) $statusId)) {
                return (int) $statusId;
            }
        }

        foreach (['En Proceso', 'Pendiente Alta', 'Pendiente'] as $statusName) {
            $row = $this->db->table('cobro_statuses')
                ->select('id')
                ->where('cobro_status', $statusName)
                ->get(1)
                ->getRowArray();
            if ($row) {
                return (int) $row['id'];
            }
        }

        $fallback = $this->db->table('cobro_statuses')
            ->select('id')
            ->orderBy('id', 'asc')
            ->get(1)
            ->getRowArray();

        return $fallback ? (int) $fallback['id'] : null;
    }

    protected function buildStatusSnapshot(int $tramiteId): array
    {
        $row = $this->db->table('tramite t')
            ->select('t.id, t.folio, t.contrato, t.tra_status_id, ts.tra_status, t.cobro_status_id, cs.cobro_status, t.reembolso_status_id, rs.reembolso_status, t.pago_gestor_st_id, pgs.pago_status, t.cli_directo_id, cd.razon_social as empresa_solicitante, t.cli_directo_ejecutivo_id, cde.nombre as ejecutivo, t.created_at, t.updated_at')
            ->join('tra_status ts', 'ts.id = t.tra_status_id', 'left')
            ->join('cobro_statuses cs', 'cs.id = t.cobro_status_id', 'left')
            ->join('reembolso_status rs', 'rs.id = t.reembolso_status_id', 'left')
            ->join('pago_gestor_status pgs', 'pgs.id = t.pago_gestor_st_id', 'left')
            ->join('cli_directo cd', 'cd.id = t.cli_directo_id', 'left')
            ->join('cli_directo_ejecutivo cde', 'cde.id = t.cli_directo_ejecutivo_id', 'left')
            ->where('t.id', $tramiteId)
            ->get(1)
            ->getRowArray();

        if (!$row) {
            return [];
        }

        $row['documentos_registrados'] = (int) $this->db->table('tra_doc_status')
            ->where('tramite_id', $tramiteId)
            ->countAllResults();

        $referenceMeta = $this->loadReferenceMetadataByTramiteId($tramiteId);
        if ($referenceMeta !== null) {
            $row['external_reference'] = $referenceMeta['external_reference'] ?? null;
            $row['source_system'] = $referenceMeta['source_system'] ?? null;
        }

        return $row;
    }

    protected function generateSuggestedFolio(): string
    {
        $result = $this->db->table('tramite')->selectMax('id')->get()->getRow();
        $nextId = ((int) ($result->id ?? 0)) + 1;
        return 'TR-' . date('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
    }

    protected function existsById(string $table, string $column, int $id): bool
    {
        return $this->db->table($table)->where($column, $id)->countAllResults() > 0;
    }

    protected function findDuplicateTramiteByTipoSerie(int $traTiposId, string $serie): ?array
    {
        $serie = trim($serie);
        if ($traTiposId <= 0 || $serie === '') {
            return null;
        }

        $row = $this->db->table('tramite t')
            ->select('t.id, t.folio, t.contrato, t.serie, t.user_id, t.created_at, t.tra_tipos_id, tt.tipo_tramite')
            ->join('tra_tipos tt', 'tt.id = t.tra_tipos_id', 'left')
            ->where('t.tra_tipos_id', $traTiposId)
            ->where('t.serie', $serie)
            ->where('t.created_at >=', date('Y-m-d H:i:s', strtotime('-1 year')))
            ->orderBy('t.created_at', 'desc')
            ->get(1)
            ->getRowArray();

        return $row ?: null;
    }

    protected function buildDuplicateTramiteResponse(array $duplicateTramite): array
    {
        $existingTramiteId = (int) ($duplicateTramite['id'] ?? 0);
        $createdAt = (string) ($duplicateTramite['created_at'] ?? '');
        $createdAtFormatted = $this->formatDuplicateCreatedAt($createdAt);
        $previousUserId = (int) ($duplicateTramite['user_id'] ?? 0);
        $previousUserName = $this->resolveUserFullName($previousUserId);
        $tipoTramite = trim((string) ($duplicateTramite['tipo_tramite'] ?? ''));

        return [
            'success' => false,
            'duplicate' => true,
            'confirmable' => true,
            'message' => 'El trámite ya fue registrado previamente.',
            'statusCode' => 409,
            'existing_tramite_id' => $existingTramiteId,
            'duplicate_details' => [
                'id_existente' => $existingTramiteId,
                'folio_existente' => (string) ($duplicateTramite['folio'] ?? ''),
                'contrato_existente' => (string) ($duplicateTramite['contrato'] ?? ''),
                'serie_existente' => (string) ($duplicateTramite['serie'] ?? ''),
                'tipo_tramite_existente' => $tipoTramite,
                'nombre_usuario_existente' => $previousUserName,
                'created_at_existente' => $createdAtFormatted,
                'created_at_existente_raw' => $createdAt,
            ],
            'existing_data' => [
                'id' => $existingTramiteId,
                'folio' => (string) ($duplicateTramite['folio'] ?? ''),
                'contrato' => (string) ($duplicateTramite['contrato'] ?? ''),
                'serie' => (string) ($duplicateTramite['serie'] ?? ''),
                'tipo_tramite' => $tipoTramite,
                'created_at' => $createdAt,
                'created_at_formatted' => $createdAtFormatted,
                'requested_by_user_id' => $previousUserId,
                'requested_by_name' => $previousUserName,
            ],
            'data' => $existingTramiteId > 0 ? $this->buildStatusSnapshot($existingTramiteId) : null,
        ];
    }

    protected function formatDuplicateCreatedAt(string $createdAt): string
    {
        if ($createdAt === '') {
            return 'N/A';
        }

        try {
            helper('datetime_es');
            if (function_exists('format_datetime_es')) {
                return (string) format_datetime_es($createdAt, true, 'N/A');
            }
        } catch (\Throwable $e) {
        }

        return $createdAt;
    }

    protected function resolveUserFullName(int $userId): string
    {
        if ($userId <= 0) {
            return 'N/A';
        }

        try {
            $userModel = new \App\Models\UserModel($this->db);
            $fullName = trim((string) ($userModel->getFullNameById($userId) ?? ''));
            if ($fullName !== '') {
                return $fullName;
            }
        } catch (\Throwable $e) {
        }

        $row = $this->db->table('users')
            ->select('firstname, midname, lastname, username')
            ->where('id', $userId)
            ->get(1)
            ->getRowArray();

        if ($row === null) {
            return 'N/A';
        }

        $fullName = trim(implode(' ', array_filter([
            (string) ($row['firstname'] ?? ''),
            (string) ($row['midname'] ?? ''),
            (string) ($row['lastname'] ?? ''),
        ])));

        return $fullName !== '' ? $fullName : trim((string) ($row['username'] ?? 'N/A'));
    }

    protected function existsMunicipioForEntidad(int $entMunicipioId, int $entidadId): bool
    {
        return $this->db->table('rel_ent_municipio')
            ->where('ent_municipality_id', $entMunicipioId)
            ->where('id_entity', $entidadId)
            ->countAllResults() > 0;
    }

    protected function existsEjecutivoForCliente(int $ejecutivoId, int $cliDirectoId): bool
    {
        return $this->db->table('cli_directo_ejecutivo')
            ->where('id', $ejecutivoId)
            ->where('cli_directo_id', $cliDirectoId)
            ->countAllResults() > 0;
    }

    protected function resolveEjecutivoUserId(int $ejecutivoId): int
    {
        if ($ejecutivoId <= 0) {
            return 0;
        }

        $row = $this->db->table('cli_directo_ejecutivo')
            ->select('user_id')
            ->where('id', $ejecutivoId)
            ->get(1)
            ->getRowArray();

        return (int) ($row['user_id'] ?? 0);
    }

    protected function validarAccesoCliente(int $userId, int $cliDirectoId): bool
    {
        if (user_has_global_cliente_access($userId)) {
            return true;
        }

        $row = $this->db->table('cli_directo')
            ->select('cliente_id')
            ->where('id', $cliDirectoId)
            ->get(1)
            ->getRowArray();

        if (empty($row['cliente_id'])) {
            return false;
        }

        $clienteIds = get_user_cliente_ids($userId);
        return is_array($clienteIds) && in_array((int) $row['cliente_id'], array_map('intval', $clienteIds), true);
    }

    protected function storeUploadedDocuments(int $tramiteId, array $requestFiles): int
    {
        $documentFiles = $this->flattenUploadedFiles($requestFiles['documentos'] ?? []);
        if ($documentFiles === []) {
            return 0;
        }

        $uploadPath = $this->ensureUploadPath($tramiteId);
        $count = 0;

        foreach ($documentFiles as $archivo) {
            if (!$archivo->isValid() || $archivo->hasMoved()) {
                continue;
            }

            $nuevoNombre = $archivo->getRandomName();
            $archivo->move($uploadPath, $nuevoNombre);

            $this->db->table('tra_doc_status')->insert([
                'tramite_id' => $tramiteId,
                'nombre_archivo' => $nuevoNombre,
                'nombre_original' => $archivo->getClientName(),
                'ruta' => $uploadPath . $nuevoNombre,
                'tipo' => $archivo->getClientMimeType(),
                'tamano' => $archivo->getSize(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        return $count;
    }

    protected function seedBaseDocumentsForTipoTramite(int $tramiteId, string $folio, int $traTiposId, int $userId): int
    {
        if ($tramiteId <= 0 || $traTiposId <= 0) {
            return 0;
        }

        $query = $this->db->table('tra_tipo_documentos')
            ->select('documento_id')
            ->where('tra_tipos_id', $traTiposId);

        if ($this->db->fieldExists('es_obligatorio', 'tra_tipo_documentos')) {
            $query->where('es_obligatorio', 1);
        }

        $documentos = $query->get()->getResultArray();
        if ($documentos === []) {
            return 0;
        }

        $statusDocumentoId = defined('SGL_TRA_STATUS_RECOLECCION_DCTOS')
            ? (int) SGL_TRA_STATUS_RECOLECCION_DCTOS
            : 11;
        $count = 0;

        foreach ($documentos as $documento) {
            $documentoId = (int) ($documento['documento_id'] ?? 0);
            if ($documentoId <= 0) {
                continue;
            }

            $exists = $this->db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->countAllResults() > 0;
            if ($exists) {
                continue;
            }

            $this->db->table('tra_doc_status')->insert([
                'folio_tramite' => $folio,
                'tramite_id' => $tramiteId,
                'documento_id' => $documentoId,
                'status_documento_id' => $statusDocumentoId,
                'file' => null,
                'comentario' => null,
                'user_id' => $userId > 0 ? $userId : null,
            ]);
            $count++;
        }

        return $count;
    }

    protected function storeInlineDocuments(int $tramiteId, array $documentos): int
    {
        if ($documentos === []) {
            return 0;
        }

        $uploadPath = $this->ensureUploadPath($tramiteId);
        $count = 0;

        foreach ($documentos as $documento) {
            if (!is_array($documento)) {
                continue;
            }

            $nombreOriginal = trim((string) ($documento['nombre'] ?? ''));
            $contenidoBase64 = trim((string) ($documento['contenido_base64'] ?? ''));
            if ($nombreOriginal === '' || $contenidoBase64 === '') {
                continue;
            }

            $binary = base64_decode($contenidoBase64, true);
            if ($binary === false) {
                continue;
            }

            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nuevoNombre = uniqid('api_doc_', true) . ($extension !== '' ? '.' . $extension : '');
            $ruta = $uploadPath . $nuevoNombre;
            file_put_contents($ruta, $binary);

            $this->db->table('tra_doc_status')->insert([
                'tramite_id' => $tramiteId,
                'nombre_archivo' => $nuevoNombre,
                'nombre_original' => $nombreOriginal,
                'ruta' => $ruta,
                'tipo' => (string) ($documento['mime_type'] ?? 'application/octet-stream'),
                'tamano' => strlen($binary),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        return $count;
    }

    protected function ensureUploadPath(int $tramiteId): string
    {
        $uploadPath = WRITEPATH . 'uploads/tramites/' . $tramiteId . '/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        return $uploadPath;
    }

    protected function flattenUploadedFiles($files): array
    {
        if (!is_array($files)) {
            return $files ? [$files] : [];
        }

        $flat = [];
        foreach ($files as $file) {
            foreach ($this->flattenUploadedFiles($file) as $child) {
                $flat[] = $child;
            }
        }

        return $flat;
    }

    protected function registrarBitacora(int $tramiteId, string $descripcion, int $userId, string $folio = ''): void
    {
        $now = date('Y-m-d H:i:s');
        $fieldNames = array_flip($this->db->getFieldNames('bitacora'));

        if (isset($fieldNames['descripcion'])) {
            $this->db->table('bitacora')->insert([
                'tramite_id' => $tramiteId,
                'descripcion' => $descripcion,
                'user_id' => $userId,
                'created_at' => $now,
            ]);
            return;
        }

        $data = [
            'folio_tramite' => $folio !== '' ? $folio : (string) $tramiteId,
            'tramite_id' => (string) $tramiteId,
            'cambios' => json_encode(['mensaje' => $descripcion], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'tipo' => 'create',
            'origen' => 'api_externa',
            'user_id' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (isset($fieldNames['status'])) {
            $data['status'] = 1;
        }

        $this->db->table('bitacora')->insert($data);
    }

    protected function normalizeNullableInt($value): ?int
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        return (int) $value;
    }

    protected function ensureExternalApiSupportTables(): ?array
    {
        $requiredTables = [
            'external_api_tramite_reference',
            'external_api_idempotency',
            'external_api_webhook_event',
        ];

        foreach ($requiredTables as $table) {
            if (!$this->db->tableExists($table)) {
                return [
                    'success' => false,
                    'message' => 'Falta configurar las tablas de integración externa. Ejecuta create_external_api_integration_tables.sql.',
                    'statusCode' => 503,
                ];
            }
        }

        return null;
    }

    protected function ensureReferenceTableAvailable(): ?array
    {
        if (!$this->db->tableExists('external_api_tramite_reference')) {
            return [
                'success' => false,
                'message' => 'La tabla de referencias externas no está configurada.',
                'statusCode' => 503,
            ];
        }

        return null;
    }

    protected function isWebhookQueueAvailable(): bool
    {
        return $this->db->tableExists('external_api_tramite_reference')
            && $this->db->tableExists('external_api_webhook_event');
    }

    protected function resolveSourceSystem(array $payload, array $options = []): string
    {
        $config = config('ExternalApi');
        $sourceSystem = trim((string) ($options['sourceSystem'] ?? ($payload['source_system'] ?? '')));
        return $sourceSystem !== '' ? $sourceSystem : $config->defaultSourceSystem;
    }

    protected function buildIdempotencyRequestHash(array $normalizedPayload, array $requestFiles, string $sourceSystem): string
    {
        $hashPayload = [
            'source_system' => $sourceSystem,
            'payload' => $normalizedPayload,
            'uploaded_files' => $this->summarizeUploadedFiles($requestFiles['documentos'] ?? []),
        ];

        return hash('sha256', json_encode($this->sortPayloadRecursively($hashPayload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function summarizeUploadedFiles($files): array
    {
        $documentFiles = $this->flattenUploadedFiles($files);
        $summary = [];
        foreach ($documentFiles as $archivo) {
            if (!is_object($archivo) || !method_exists($archivo, 'getClientName')) {
                continue;
            }

            $summary[] = [
                'name' => (string) $archivo->getClientName(),
                'size' => method_exists($archivo, 'getSize') ? (int) $archivo->getSize() : 0,
                'mime_type' => method_exists($archivo, 'getClientMimeType') ? (string) $archivo->getClientMimeType() : '',
            ];
        }

        return $summary;
    }

    protected function sortPayloadRecursively($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->sortPayloadRecursively($item);
        }

        if ($this->isAssociativeArray($value)) {
            ksort($value);
        }

        return $value;
    }

    protected function isAssociativeArray(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }

    protected function findIdempotencyRecord(string $sourceSystem, string $idempotencyKey): ?array
    {
        $row = $this->db->table('external_api_idempotency')
            ->where('source_system', $sourceSystem)
            ->where('idempotency_key', $idempotencyKey)
            ->get(1)
            ->getRowArray();

        if (!$row) {
            return null;
        }

        return [
            'request_hash' => (string) ($row['request_hash'] ?? ''),
            'status_code' => (int) ($row['response_status_code'] ?? 200),
            'response' => json_decode((string) ($row['response_body_json'] ?? '{}'), true) ?: [],
            'tramite_id' => (int) ($row['tramite_id'] ?? 0),
        ];
    }

    protected function rememberIdempotentResponse(string $sourceSystem, string $idempotencyKey, string $requestHash, int $tramiteId, array $response, int $statusCode): void
    {
        if ($idempotencyKey === '') {
            return;
        }

        $existing = $this->db->table('external_api_idempotency')
            ->where('source_system', $sourceSystem)
            ->where('idempotency_key', $idempotencyKey)
            ->countAllResults();

        $data = [
            'source_system' => $sourceSystem,
            'idempotency_key' => $idempotencyKey,
            'request_hash' => $requestHash,
            'tramite_id' => $tramiteId,
            'response_status_code' => $statusCode,
            'response_body_json' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing > 0) {
            $this->db->table('external_api_idempotency')
                ->where('source_system', $sourceSystem)
                ->where('idempotency_key', $idempotencyKey)
                ->update($data);
            return;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('external_api_idempotency')->insert($data);
    }

    protected function findReferenceMapping(string $sourceSystem, string $externalReference): ?array
    {
        $row = $this->db->table('external_api_tramite_reference')
            ->where('source_system', $sourceSystem)
            ->where('external_reference', $externalReference)
            ->get(1)
            ->getRowArray();

        return $row ?: null;
    }

    protected function createReferenceMapping(string $sourceSystem, string $externalReference, int $tramiteId, array $snapshot): void
    {
        $this->db->table('external_api_tramite_reference')->insert([
            'source_system' => $sourceSystem,
            'external_reference' => $externalReference,
            'tramite_id' => $tramiteId,
            'last_status_payload_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function loadReferenceMetadataByTramiteId(int $tramiteId): ?array
    {
        if (!$this->db->tableExists('external_api_tramite_reference')) {
            return null;
        }

        $row = $this->db->table('external_api_tramite_reference')
            ->select('source_system, external_reference')
            ->where('tramite_id', $tramiteId)
            ->get(1)
            ->getRowArray();

        return $row ?: null;
    }

    protected function updateReferenceSnapshot(int $tramiteId, array $snapshot): void
    {
        if ($snapshot === [] || !$this->db->tableExists('external_api_tramite_reference')) {
            return;
        }

        $this->db->table('external_api_tramite_reference')
            ->where('tramite_id', $tramiteId)
            ->update([
                'last_status_payload_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    protected function queueWebhookEvent(string $eventName, string $sourceSystem, int $tramiteId, string $externalReference, array $payload, array $extraPayload = []): void
    {
        $config = config('ExternalApi');
        $urls = $config->webhookUrls !== [] ? $config->webhookUrls : [null];
        $eventPayload = $this->buildWebhookEventPayload($eventName, $sourceSystem, $externalReference, $payload, $extraPayload);

        foreach ($urls as $url) {
            $this->db->table('external_api_webhook_event')->insert([
                'source_system' => $sourceSystem,
                'event_name' => $eventName,
                'webhook_url' => $url,
                'tramite_id' => $tramiteId,
                'external_reference' => $externalReference,
                'delivery_status' => 'pending',
                'attempts' => 0,
                'payload_json' => json_encode($eventPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    protected function buildWebhookEventPayload(string $eventName, string $sourceSystem, string $externalReference, array $tramitePayload, array $extraPayload = []): array
    {
        return array_merge([
            'event' => $eventName,
            'source_system' => $sourceSystem,
            'external_reference' => $externalReference,
            'tramite' => $tramitePayload,
        ], $extraPayload);
    }

    protected function listPendingWebhookEvents(int $limit): array
    {
        return $this->db->table('external_api_webhook_event')
            ->where('delivery_status', 'pending')
            ->where('attempts <', $this->getWebhookMaxAttempts())
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    protected function markWebhookDelivered(array $event): void
    {
        $eventId = (int) ($event['id'] ?? 0);
        if ($eventId <= 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('external_api_webhook_event')
            ->where('id', $eventId)
            ->update([
                'delivery_status' => 'delivered',
                'attempts' => ((int) ($event['attempts'] ?? 0)) + 1,
                'last_attempt_at' => $now,
                'delivered_at' => $now,
                'error_message' => null,
                'updated_at' => $now,
            ]);
    }

    protected function markWebhookFailed(array $event, string $errorMessage): void
    {
        $eventId = (int) ($event['id'] ?? 0);
        if ($eventId <= 0) {
            return;
        }

        $nextAttempts = ((int) ($event['attempts'] ?? 0)) + 1;
        $deliveryStatus = $nextAttempts >= $this->getWebhookMaxAttempts() ? 'failed' : 'pending';
        $now = date('Y-m-d H:i:s');

        $this->db->table('external_api_webhook_event')
            ->where('id', $eventId)
            ->update([
                'delivery_status' => $deliveryStatus,
                'attempts' => $nextAttempts,
                'last_attempt_at' => $now,
                'error_message' => $this->truncateWebhookErrorMessage($errorMessage),
                'updated_at' => $now,
            ]);
    }

    protected function sendWebhookHttpRequest(string $url, array $payload): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $handle = curl_init($url);
        if ($handle === false) {
            throw new \RuntimeException('No fue posible inicializar cURL para el webhook.');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Content-Length: ' . strlen((string) $body),
                'User-Agent: admin-sgl-external-api/1.0',
            ],
            CURLOPT_TIMEOUT => $this->getWebhookTimeoutSeconds(),
        ]);

        $responseBody = curl_exec($handle);
        if ($responseBody === false) {
            $error = curl_error($handle);
            curl_close($handle);
            throw new \RuntimeException($error !== '' ? $error : 'Error desconocido al enviar el webhook.');
        }

        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        return [
            'statusCode' => $statusCode,
            'body' => (string) $responseBody,
        ];
    }

    protected function getWebhookTimeoutSeconds(): int
    {
        return max(1, (int) config('ExternalApi')->webhookTimeoutSeconds);
    }

    protected function getWebhookMaxAttempts(): int
    {
        return max(1, (int) config('ExternalApi')->webhookMaxAttempts);
    }

    protected function truncateWebhookErrorMessage(string $errorMessage): string
    {
        $errorMessage = trim($errorMessage);
        if ($errorMessage === '') {
            return 'Error desconocido al despachar el webhook.';
        }

        return substr($errorMessage, 0, 65535);
    }

    protected function resolveTramiteStatusName(int $statusId): string
    {
        if ($statusId <= 0) {
            return '';
        }

        $row = $this->db->table('tra_status')
            ->select('tra_status')
            ->where('id', $statusId)
            ->get(1)
            ->getRowArray();

        return (string) ($row['tra_status'] ?? '');
    }
}
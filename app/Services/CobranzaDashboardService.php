<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use DateTimeInterface;

class CobranzaDashboardService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        helper(['audit', 'url']);
        $this->db = $db ?? \Config\Database::connect();
    }

    public function buildDashboard(int $userId, string $tenantFilterSql = '1 = 1', array $filters = [], ?int $selectedTramiteId = null): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);
        $items = $this->fetchCollection($tenantFilterSql);
        $summary = $this->buildSummary($items, $userId);
        $filteredItems = $this->applyFilters($items, $normalizedFilters, $userId);
        $selectedPool = empty($filteredItems) ? $items : $filteredItems;
        $selectedExpediente = $this->findSelectedExpediente($selectedPool, $selectedTramiteId);
        if ($selectedTramiteId !== null && $selectedTramiteId > 0) {
            $selectedPage = $this->resolveSelectedPage($filteredItems, (int) $selectedTramiteId, (int) ($normalizedFilters['per_page'] ?? 20));
            if ($selectedPage !== null) {
                $normalizedFilters['page'] = $selectedPage;
            }
        }
        $paginatedItems = $this->paginateItems($filteredItems, $normalizedFilters);

        return [
            'summary' => $summary,
            'filters' => $normalizedFilters,
            'items' => $paginatedItems['items'],
            'pagination' => $paginatedItems['pagination'],
            'selected_expediente' => $selectedExpediente,
            'cobranza_schema_ready' => $this->hasCobranzaModuleTables(),
            'available_buckets' => [
                'all' => 'Toda la cartera',
                'my-portfolio' => 'Mi cartera',
                'en-seguimiento' => 'En seguimiento',
                'listos-apertura' => 'Listos para apertura',
                'sin-evidencia' => 'Sin evidencia',
                'pago-parcial' => 'Pago parcial',
                'pago-completo' => 'Pago completo',
                'aging-8-plus' => '8+ dias',
            ],
        ];
    }

    public function loadSelectedExpediente(int $userId, string $tenantFilterSql = '1 = 1', int $tramiteId = 0, array $filters = []): ?array
    {
        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return null;
        }

        $normalizedFilters = $this->normalizeFilters($filters);
        $items = $this->fetchCollection($tenantFilterSql, $tramiteId);
        if (empty($items)) {
            return null;
        }

        $filteredItems = $this->applyFilters($items, $normalizedFilters, $userId);
        $selectedPool = empty($filteredItems) ? $items : $filteredItems;

        return $this->findSelectedExpediente($selectedPool, $tramiteId);
    }

    public function isCobranzaSchemaReady(): bool
    {
        return $this->hasCobranzaModuleTables();
    }

    private function fetchCollection(string $tenantFilterSql, ?int $selectedTramiteId = null): array
    {
        $paidStatusIds = $this->getPaidPagoGestorStatusIds();
        $schemaReady = $this->hasCobranzaModuleTables();

        $builder = $this->db->table('tramite');
        $builder->select([
            'tramite.id',
            'tramite.folio',
            'tramite.contrato',
            'tramite.unidad',
            'tramite.serie',
            'tramite.placas',
            'tramite.id_give_cliente',
            'tramite.numero_factura',
            'tramite.numero_refactura',
            'tramite.evidencia_cobro_txt',
            'tramite.costo_gestoria',
            'tramite.costo_pago_cliente',
            'tramite.comision_derechos',
            'tramite.iva',
            'tramite.costo_total',
            'tramite.cli_directo_id',
            'tramite.cli_directo_ejecutivo_id',
            'tramite.user_id',
            'tramite.cobrar_cliente',
            'tramite.pago_gestor_st_id',
            'tramite.tra_status_id',
            'tramite.cobro_status_id',
            'tramite.created_at',
            'tramite.updated_at',
            'tramite.started_at',
            'cli_directo.razon_social as cliente_nombre',
            'cli_directo.cliente_id as cliente_id',
            'cli_directo_ejecutivo.nombre as cliente_ejecutivo_nombre',
            'tra_status.tra_status as tramite_status_nombre',
            'cobro_statuses.cobro_status as cobro_status_nombre',
            'pago_gestor_status.pago_status as pago_gestor_status_nombre',
            'users.firstname as owner_firstname',
            'users.midname as owner_midname',
            'users.lastname as owner_lastname',
        ]);
        $builder->join('cli_directo', 'cli_directo.id = tramite.cli_directo_id', 'left');
        $builder->join('cli_directo_ejecutivo', 'cli_directo_ejecutivo.id = tramite.cli_directo_ejecutivo_id', 'left');
        $builder->join('tra_status', 'tra_status.id = tramite.tra_status_id', 'left');
        $builder->join('cobro_statuses', 'cobro_statuses.id = tramite.cobro_status_id', 'left');
        $builder->join('pago_gestor_status', 'pago_gestor_status.id = tramite.pago_gestor_st_id', 'left');
        $builder->join('users', 'users.id = tramite.user_id', 'left');

        $tenantFilterSql = trim($tenantFilterSql) === '' ? '1 = 1' : $tenantFilterSql;
        $builder->where($tenantFilterSql, null, false);
        if ($selectedTramiteId !== null && $selectedTramiteId > 0) {
            $builder->where('tramite.id', (int) $selectedTramiteId);
        }

        if (!empty($paidStatusIds)) {
            $builder->groupStart()
                ->where('tramite.tra_status_id', SGL_TRA_STATUS_COBRO_CLIENTE)
                ->orGroupStart()
                    ->where('tramite.tra_status_id', SGL_TRA_STATUS_PAGO_GESTOR)
                    ->where('tramite.cobrar_cliente', 1)
                    ->whereIn('tramite.pago_gestor_st_id', $paidStatusIds)
                ->groupEnd()
            ->groupEnd();
        } else {
            $builder->where('tramite.tra_status_id', SGL_TRA_STATUS_COBRO_CLIENTE);
        }

        $builder->orderBy('tramite.updated_at', 'DESC');
        $rows = $builder->get()->getResultArray();
        if (empty($rows)) {
            return [];
        }

        $tramiteIds = array_values(array_unique(array_map(static fn (array $row): int => (int) $row['id'], $rows)));
        $evidenceStats = $this->loadEvidenceStats($tramiteIds);
        $expedienteMap = $schemaReady ? $this->loadExpedienteMap($tramiteIds) : [];

        $items = [];
        foreach ($rows as $row) {
            $tramiteId = (int) ($row['id'] ?? 0);
            $items[] = $this->normalizeRow($row, $evidenceStats[$tramiteId] ?? [], $expedienteMap[$tramiteId] ?? []);
        }

        usort($items, function (array $left, array $right): int {
            if ($left['priority_rank'] !== $right['priority_rank']) {
                return $left['priority_rank'] <=> $right['priority_rank'];
            }

            if ($left['aging_days'] !== $right['aging_days']) {
                return $right['aging_days'] <=> $left['aging_days'];
            }

            return strcmp((string) ($right['updated_at'] ?? ''), (string) ($left['updated_at'] ?? ''));
        });

        return $items;
    }

    private function normalizeRow(array $row, array $evidenceStats, array $expediente): array
    {
        $tramiteId = (int) ($row['id'] ?? 0);
        $statusId = (int) ($row['tra_status_id'] ?? 0);
        $cobroStatusId = (int) ($row['cobro_status_id'] ?? 0);
        $evidenceTotal = (int) ($evidenceStats['total'] ?? 0);
        $completeCount = (int) ($evidenceStats['complete_count'] ?? 0);
        $partialCount = (int) ($evidenceStats['partial_count'] ?? 0);
        $isReadyForCobranza = $this->isReadyForCobroCliente($row);
        $referenceDate = $expediente['fecha_ultimo_contacto']
            ?? $evidenceStats['latest_evidence_at']
            ?? $row['started_at']
            ?? $row['updated_at']
            ?? $row['created_at']
            ?? null;
        $agingDays = $this->calculateAgingDays($referenceDate);
        $hasActiveExpediente = !empty($expediente);

        $ownerUserId = $hasActiveExpediente
            ? (int) ($expediente['owner_user_id'] ?? 0)
            : (int) ($row['user_id'] ?? 0);
        $ownerName = $hasActiveExpediente
            ? $this->formatName($expediente['owner_firstname'] ?? null, $expediente['owner_midname'] ?? null, $expediente['owner_lastname'] ?? null)
            : $this->formatName($row['owner_firstname'] ?? null, $row['owner_midname'] ?? null, $row['owner_lastname'] ?? null);

        $stageLabel = 'En seguimiento';
        $stageTone = 'info';
        if ($hasActiveExpediente && !empty($expediente['status_name'])) {
            $stageLabel = (string) $expediente['status_name'];
            $stageTone = 'accent';
        }
        if ($completeCount > 0 || $cobroStatusId === SGL_COBRO_STATUS_COBRADO) {
            $stageLabel = 'Pago completo reportado';
            $stageTone = 'success';
        } elseif ($partialCount > 0) {
            $stageLabel = 'Pago parcial reportado';
            $stageTone = 'warning';
        } elseif ($statusId !== SGL_TRA_STATUS_COBRO_CLIENTE && $isReadyForCobranza) {
            $stageLabel = 'Listo para apertura';
            $stageTone = 'accent';
        } elseif ($evidenceTotal === 0) {
            $stageLabel = 'Sin evidencia de pago';
            $stageTone = 'danger';
        }

        $attentionLabel = 'Seguimiento normal';
        $attentionTone = 'info';
        $priorityRank = 2;
        if ($completeCount > 0 || $cobroStatusId === SGL_COBRO_STATUS_COBRADO) {
            $attentionLabel = 'Listo para cierre';
            $attentionTone = 'success';
            $priorityRank = 3;
        } elseif ($agingDays >= 8 && $evidenceTotal === 0) {
            $attentionLabel = 'Atencion prioritaria';
            $attentionTone = 'danger';
            $priorityRank = 0;
        } elseif ($statusId !== SGL_TRA_STATUS_COBRO_CLIENTE && $isReadyForCobranza) {
            $attentionLabel = 'Mover a cobranza';
            $attentionTone = 'accent';
            $priorityRank = 1;
        } elseif ($partialCount > 0) {
            $attentionLabel = 'Validar pago parcial';
            $attentionTone = 'warning';
            $priorityRank = 1;
        }

        return [
            'id' => $tramiteId,
            'folio' => (string) ($row['folio'] ?? ''),
            'contrato' => (string) ($row['contrato'] ?? ''),
            'unidad' => (string) ($row['unidad'] ?? ''),
            'serie' => (string) ($row['serie'] ?? ''),
            'placas' => (string) ($row['placas'] ?? ''),
            'cliente_id' => (int) ($row['cliente_id'] ?? 0),
            'cliente_nombre' => (string) ($row['cliente_nombre'] ?? 'Sin cliente'),
            'cliente_ejecutivo_nombre' => (string) ($row['cliente_ejecutivo_nombre'] ?? 'Sin ejecutivo'),
            'owner_user_id' => $ownerUserId,
            'owner_name' => $ownerName,
            'id_give_cliente' => (string) ($row['id_give_cliente'] ?? ''),
            'numero_factura' => (string) ($row['numero_factura'] ?? ''),
            'numero_refactura' => (string) ($row['numero_refactura'] ?? ''),
            'evidencia_cobro_txt' => (string) ($row['evidencia_cobro_txt'] ?? ''),
            'costo_gestoria' => (float) ($row['costo_gestoria'] ?? 0),
            'costo_pago_cliente' => (float) ($row['costo_pago_cliente'] ?? 0),
            'comision_derechos' => (float) ($row['comision_derechos'] ?? 0),
            'iva' => (float) ($row['iva'] ?? 0),
            'costo_total' => (float) ($row['costo_total'] ?? 0),
            'tramite_status_id' => $statusId,
            'tramite_status_nombre' => (string) ($row['tramite_status_nombre'] ?? ''),
            'cobro_status_id' => $cobroStatusId,
            'cobro_status_nombre' => (string) ($row['cobro_status_nombre'] ?? ''),
            'pago_gestor_status_id' => (int) ($row['pago_gestor_st_id'] ?? 0),
            'pago_gestor_status_nombre' => (string) ($row['pago_gestor_status_nombre'] ?? ''),
            'expediente_id' => (int) ($expediente['id'] ?? 0),
            'has_active_expediente' => $hasActiveExpediente,
            'expediente_status_nombre' => (string) ($expediente['status_name'] ?? ''),
            'expediente_owner_name' => $hasActiveExpediente ? $ownerName : '',
            'fecha_proximo_seguimiento' => $expediente['fecha_proximo_seguimiento'] ?? null,
            'fecha_ultimo_contacto' => $expediente['fecha_ultimo_contacto'] ?? null,
            'is_ready_for_cobranza' => $isReadyForCobranza,
            'evidence_total' => $evidenceTotal,
            'evidence_complete_count' => $completeCount,
            'evidence_partial_count' => $partialCount,
            'latest_evidence_at' => $evidenceStats['latest_evidence_at'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'started_at' => $row['started_at'] ?? null,
            'aging_days' => $agingDays,
            'stage_label' => $stageLabel,
            'stage_tone' => $stageTone,
            'attention_label' => $attentionLabel,
            'attention_tone' => $attentionTone,
            'priority_rank' => $priorityRank,
            'can_open_expediente' => $isReadyForCobranza && !$hasActiveExpediente,
            'can_register_gestion' => $hasActiveExpediente,
            'detail_url' => site_url('deskapp/cobranza/expediente/' . $tramiteId),
            'tramite_url' => site_url('deskapp/tramitesn/update/' . $tramiteId),
        ];
    }

    private function buildSummary(array $items, int $userId): array
    {
        $summary = [
            'active' => 0,
            'my_portfolio' => 0,
            'in_follow_up' => 0,
            'ready_to_open' => 0,
            'without_evidence' => 0,
            'partial_payment' => 0,
            'complete_payment' => 0,
            'priority_overdue' => 0,
        ];

        foreach ($items as $item) {
            $summary['active']++;

            if ((int) $item['owner_user_id'] === $userId) {
                $summary['my_portfolio']++;
            }
            if ((int) $item['tramite_status_id'] === SGL_TRA_STATUS_COBRO_CLIENTE) {
                $summary['in_follow_up']++;
            }
            if ($item['is_ready_for_cobranza'] && (int) $item['tramite_status_id'] !== SGL_TRA_STATUS_COBRO_CLIENTE) {
                $summary['ready_to_open']++;
            }
            if ((int) $item['evidence_total'] === 0) {
                $summary['without_evidence']++;
            }
            if ((int) $item['evidence_partial_count'] > 0) {
                $summary['partial_payment']++;
            }
            if ((int) $item['evidence_complete_count'] > 0 || (int) $item['cobro_status_id'] === SGL_COBRO_STATUS_COBRADO) {
                $summary['complete_payment']++;
            }
            if ((int) $item['aging_days'] >= 8 && (int) $item['evidence_total'] === 0) {
                $summary['priority_overdue']++;
            }
        }

        return $summary;
    }

    private function applyFilters(array $items, array $filters, int $userId): array
    {
        $filtered = array_filter($items, function (array $item) use ($filters, $userId): bool {
            if (!$this->matchesBucket($item, $filters['bucket'], $userId)) {
                return false;
            }

            $query = $filters['q'];
            if ($query === '') {
                return true;
            }

            $haystack = implode(' ', [
                (string) $item['id'],
                $item['folio'],
                $item['contrato'],
                $item['cliente_nombre'],
                $item['cliente_ejecutivo_nombre'],
                $item['owner_name'],
                $item['serie'],
                $item['placas'],
                $item['unidad'],
            ]);

            $normalizedHaystack = function_exists('mb_strtolower')
                ? mb_strtolower($haystack, 'UTF-8')
                : strtolower($haystack);

            return strpos($normalizedHaystack, $query) !== false;
        });

        return array_values($filtered);
    }

    private function matchesBucket(array $item, string $bucket, int $userId): bool
    {
        switch ($bucket) {
            case 'my-portfolio':
                return (int) $item['owner_user_id'] === $userId;
            case 'en-seguimiento':
                return (int) $item['tramite_status_id'] === SGL_TRA_STATUS_COBRO_CLIENTE;
            case 'listos-apertura':
                return $item['is_ready_for_cobranza'] && (int) $item['tramite_status_id'] !== SGL_TRA_STATUS_COBRO_CLIENTE;
            case 'sin-evidencia':
                return (int) $item['evidence_total'] === 0;
            case 'pago-parcial':
                return (int) $item['evidence_partial_count'] > 0;
            case 'pago-completo':
                return (int) $item['evidence_complete_count'] > 0 || (int) $item['cobro_status_id'] === SGL_COBRO_STATUS_COBRADO;
            case 'aging-8-plus':
                return (int) $item['aging_days'] >= 8;
            case 'all':
            default:
                return true;
        }
    }

    private function findSelectedExpediente(array $items, ?int $selectedTramiteId = null): ?array
    {
        $selectedTramiteId = (int) $selectedTramiteId;
        if ($selectedTramiteId <= 0) {
            return empty($items) ? null : $this->hydrateSelectedExpediente($items[0]);
        }

        foreach ($items as $item) {
            if ((int) $item['id'] === $selectedTramiteId) {
                return $this->hydrateSelectedExpediente($item);
            }
        }

        return null;
    }

    private function hydrateSelectedExpediente(array $item): array
    {
        $item['promesa_activa'] = $this->loadActivePromesa((int) ($item['expediente_id'] ?? 0));
        $item['pago_summary'] = $this->loadPagoSummary((int) ($item['expediente_id'] ?? 0));
        $item['pagos_pendientes'] = $this->loadPendingPagos((int) ($item['expediente_id'] ?? 0));
        $item['cobro_cliente_files'] = $this->loadCobroClienteFiles((int) $item['id']);
        $item['timeline'] = $this->loadTimeline((int) $item['id']);
        $item = array_merge($item, $this->loadLegacyWizardReadonlySummary((int) $item['id']));

        return $item;
    }

    private function loadLegacyWizardReadonlySummary(int $tramiteId): array
    {
        $defaults = [
            'doc_status_docs' => [],
            'servicios_asociados' => [],
            'readonly_step1' => [],
            'readonly_step2' => [],
            'readonly_step3' => [],
            'pago_gestor_resumen' => [],
            'pago_derechos_db' => [],
            'pago_gestor_evidencias_db' => [],
            'pago_gestor_pago_db' => [],
            'step1_complete' => false,
            'step2_complete' => false,
            'step3_complete' => false,
            'has_comprobante_tramite_recibido' => false,
            'has_comprobante_acuse_recibo' => false,
            'has_factura_gestor' => false,
            'has_comprobante_pago' => false,
        ];

        if ($tramiteId <= 0 || ! $this->db->tableExists('tramite')) {
            return $defaults;
        }

        $tramite = $this->db->table('tramite')
            ->where('id', $tramiteId)
            ->get(1)
            ->getRowArray();

        if (empty($tramite)) {
            return $defaults;
        }

        $clienteNombre = $this->lookupLabel('cli_directo', 'id', (int) ($tramite['cli_directo_id'] ?? 0), 'razon_social');
        $ejecutivoCliente = $this->lookupLabel('cli_directo_ejecutivo', 'id', (int) ($tramite['cli_directo_ejecutivo_id'] ?? 0), 'nombre');
        $entidadNombre = $this->lookupLabel('entidad', 'id', (int) ($tramite['entidad_id'] ?? 0), 'entidad');
        $empresaGestora = $this->lookupLabel('ges_empresa_gestora', 'id', (int) ($tramite['empresa_gestora_id'] ?? 0), 'nombre');
        $gestorNombre = $this->lookupLabel('ges_gestor', 'id', (int) ($tramite['gestor_id'] ?? 0), 'nombre');
        $pagoGestorStatus = $this->lookupLabel('pago_gestor_status', 'id', (int) ($tramite['pago_gestor_st_id'] ?? 0), 'pago_status');
        $reembolsoStatus = $this->lookupLabel('reembolso_status', 'id', (int) ($tramite['reembolso_status_id'] ?? 0), 'reembolso_status');

        $serviciosAsociados = $this->loadAssociatedServices($tramiteId, (int) ($tramite['tra_tipos_id'] ?? 0));
    $documentStatusDocs = $this->loadDocumentStatusDocuments($tramiteId);
        $pagoDerechosDocs = $this->loadPagoDerechosDocuments($tramiteId);
        $pagoGestorDocs = $this->loadPagoGestorDocuments($tramiteId);

        $hasTramiteRecibido = false;
        $hasAcuseRecibo = false;
        $hasFacturaGestor = false;
        $hasComprobantePago = false;
        $pagoGestorEvidencias = [];
        $pagoGestorPago = [];

        foreach ($pagoGestorDocs as $doc) {
            $docType = (string) ($doc['comprobante_final'] ?? '');
            if ($docType === 'tramite_recibido') {
                $hasTramiteRecibido = true;
                $pagoGestorEvidencias[] = $doc;
                continue;
            }

            if ($docType === 'acuse_recibo_cliente') {
                $hasAcuseRecibo = true;
                $pagoGestorEvidencias[] = $doc;
                continue;
            }

            if ($docType === 'factura_gestor') {
                $hasFacturaGestor = true;
            }

            if ($docType === 'comprobante_pago') {
                $hasComprobantePago = true;
            }

            $pagoGestorPago[] = $doc;
        }

        return array_merge($defaults, [
            'doc_status_docs' => $documentStatusDocs,
            'servicios_asociados' => $serviciosAsociados,
            'readonly_step1' => [
                ['label' => 'Contrato', 'value' => $tramite['contrato'] ?? ''],
                ['label' => 'Unidad', 'value' => $tramite['unidad'] ?? ''],
                ['label' => 'Serie', 'value' => $tramite['serie'] ?? ''],
                ['label' => 'Placas', 'value' => $tramite['placas'] ?? ''],
                ['label' => 'Cliente', 'value' => $clienteNombre],
                ['label' => 'Ejecutivo de Cliente', 'value' => $ejecutivoCliente],
                ['label' => 'Entidad', 'value' => $entidadNombre],
                ['label' => 'Observaciones', 'value' => $tramite['observaciones'] ?? ''],
            ],
            'readonly_step2' => [
                ['label' => 'Empresa Gestora', 'value' => $empresaGestora],
                ['label' => 'Gestor', 'value' => $gestorNombre],
            ],
            'readonly_step3' => [
                ['label' => 'Monto pago de derechos', 'value' => $tramite['derechos_tramite'] ?? ''],
                ['label' => 'Pago', 'value' => $this->mapDerechosPagoSitio($tramite['derechos_pago_sitio'] ?? '')],
                ['label' => 'Fecha Vigencia', 'value' => $tramite['derechos_vigencia'] ?? ''],
                ['label' => 'Forma de Pago', 'value' => $this->mapDerechosFormaPago($tramite['derechos_revol_cliente'] ?? '')],
                ['label' => 'Referencia Bancaria', 'value' => $tramite['derechos_refer_banc'] ?? ''],
            ],
            'pago_gestor_resumen' => [
                ['label' => 'Gestor', 'value' => $gestorNombre],
                ['label' => 'Costo del Tramite', 'value' => $tramite['costo_tramite'] ?? ''],
                ['label' => 'Deposito a Gestor', 'value' => $tramite['deposito_gestor'] ?? ''],
                ['label' => 'Saldo Pendiente', 'value' => $tramite['col_a_favor'] ?? ''],
                ['label' => 'Numero de Factura', 'value' => $tramite['num_factura_gestor'] ?? ''],
                ['label' => 'Estatus del Pago', 'value' => $pagoGestorStatus !== '' ? $pagoGestorStatus : ($tramite['pago_gestor_st_id'] ?? '')],
                ['label' => 'Estatus de Documentos', 'value' => $this->mapStatusDoctosGestor($tramite['status_doctos_gestor'] ?? '')],
                ['label' => 'Honorarios de Gestoria', 'value' => $tramite['impuesto_gestoria'] ?? ''],
                ['label' => 'Gratificacion', 'value' => $tramite['gestoria_comision'] ?? ''],
                ['label' => 'Costo Paqueteria', 'value' => $tramite['costo_paqueteria'] ?? ''],
                ['label' => 'Pago Total', 'value' => $tramite['gestor_total_pago'] ?? ''],
                ['label' => 'Estatus del Reembolso', 'value' => $reembolsoStatus !== '' ? $reembolsoStatus : ($tramite['reembolso_status_id'] ?? '')],
            ],
            'pago_derechos_db' => $pagoDerechosDocs,
            'pago_gestor_evidencias_db' => $pagoGestorEvidencias,
            'pago_gestor_pago_db' => $pagoGestorPago,
            'step1_complete' => !empty($tramite['contrato']) && !empty($tramite['entidad_id']),
            'step2_complete' => !empty($tramite['empresa_gestora_id']) && !empty($tramite['gestor_id']),
            'step3_complete' => !empty($tramite['derechos_tramite']) && !empty($tramite['derechos_revol_cliente']) && !empty($tramite['derechos_refer_banc']),
            'has_comprobante_tramite_recibido' => $hasTramiteRecibido,
            'has_comprobante_acuse_recibo' => $hasAcuseRecibo,
            'has_factura_gestor' => $hasFacturaGestor,
            'has_comprobante_pago' => $hasComprobantePago,
        ]);
    }

    private function loadAssociatedServices(int $tramiteId, int $principalTipoId = 0): array
    {
        if ($tramiteId <= 0 || ! $this->db->tableExists('tra_tramite_asociado') || ! $this->db->tableExists('tra_tipos')) {
            return [];
        }

        $rows = $this->db->table('tra_tramite_asociado')
            ->select('tra_tramite_asociado.id, tra_tramite_asociado.tra_tipos_id, tra_tramite_asociado.costo_tramite, tra_tipos.tipo_tramite')
            ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id', 'left')
            ->where('tra_tramite_asociado.tramite_id', $tramiteId)
            ->orderBy('tra_tramite_asociado.id', 'ASC')
            ->get()
            ->getResultArray();

        usort($rows, static function (array $left, array $right) use ($principalTipoId): int {
            $leftIsPrincipal = (int) ($left['tra_tipos_id'] ?? 0) === $principalTipoId;
            $rightIsPrincipal = (int) ($right['tra_tipos_id'] ?? 0) === $principalTipoId;

            if ($leftIsPrincipal === $rightIsPrincipal) {
                return ((int) ($left['id'] ?? 0)) <=> ((int) ($right['id'] ?? 0));
            }

            return $leftIsPrincipal ? -1 : 1;
        });

        return array_map(static function (array $row): array {
            return [
                'asociado_id' => (int) ($row['id'] ?? 0),
                'tra_tipos_id' => (int) ($row['tra_tipos_id'] ?? 0),
                'label' => (string) ($row['tipo_tramite'] ?? ''),
                'costo_tramite' => $row['costo_tramite'] ?? '',
            ];
        }, $rows);
    }

    private function loadPagoDerechosDocuments(int $tramiteId): array
    {
        if ($tramiteId <= 0 || ! $this->db->tableExists('tra_pago_derechos')) {
            return [];
        }

        $builder = $this->db->table('tra_pago_derechos')
            ->select('id, file, comentario, costo, created_at, updated_at')
            ->where('tramite_id', $tramiteId);

        if ($this->db->fieldExists('status', 'tra_pago_derechos')) {
            $builder->where('status', 1);
        }

        $rows = $builder->orderBy('id', 'DESC')->get()->getResultArray();

        return array_map(function (array $row) use ($tramiteId): array {
            $fileName = (string) ($row['file'] ?? '');

            return $row + [
                'file_url' => file_url($fileName, 'pago_derechos', $tramiteId),
                'is_image' => $this->isImageFile($fileName),
            ];
        }, $rows);
    }

    private function loadDocumentStatusDocuments(int $tramiteId): array
    {
        if (
            $tramiteId <= 0
            || ! $this->db->tableExists('tra_doc_status')
            || ! $this->db->fieldExists('file', 'tra_doc_status')
            || ! $this->db->fieldExists('tramite_id', 'tra_doc_status')
        ) {
            return [];
        }

        $hasDocumentoCatalog = $this->db->tableExists('documento')
            && ($this->db->fieldExists('documento_id', 'documento') || $this->db->fieldExists('id', 'documento'))
            && $this->db->fieldExists('documento', 'documento');
        $hasDocStatuses = $this->db->tableExists('doc_statuses');
        $hasDocumentoId = $this->db->fieldExists('documento_id', 'tra_doc_status');
        $hasStatusDocumentoId = $this->db->fieldExists('status_documento_id', 'tra_doc_status');
        $hasComentario = $this->db->fieldExists('comentario', 'tra_doc_status');
        $hasCreatedAt = $this->db->fieldExists('created_at', 'tra_doc_status');
        $hasStatus = $this->db->fieldExists('status', 'tra_doc_status');
        $docCatalogPk = null;

        if ($hasDocumentoCatalog) {
            $docCatalogPk = $this->db->fieldExists('documento_id', 'documento') ? 'documento_id' : 'id';
        }

        $builder = $this->db->table('tra_doc_status tds');
        $selectParts = ['tds.id', 'tds.file'];
        if ($hasDocumentoId) {
            $selectParts[] = 'tds.documento_id';
        }
        if ($hasStatusDocumentoId) {
            $selectParts[] = 'tds.status_documento_id';
        }
        if ($hasComentario) {
            $selectParts[] = 'tds.comentario';
        }
        if ($hasCreatedAt) {
            $selectParts[] = 'tds.created_at';
        }

        $builder->select(implode(', ', $selectParts));

        if ($hasDocumentoCatalog && $hasDocumentoId && $docCatalogPk !== null) {
            $builder->select('d.documento as documento_nombre');
            $builder->join('documento d', 'd.' . $docCatalogPk . ' = tds.documento_id', 'left');
        }

        if ($hasDocStatuses && $hasStatusDocumentoId) {
            $builder->select('ds.st_documento as status_nombre');
            $builder->join('doc_statuses ds', 'ds.id = tds.status_documento_id', 'left');
        }

        $builder->where('tds.tramite_id', $tramiteId);
        if ($hasStatus) {
            $builder->where('tds.status', 1);
        }

        $rows = $builder
            ->orderBy('tds.id', 'DESC')
            ->get()
            ->getResultArray();

        $latestByDocId = [];
        foreach ($rows as $row) {
            $docId = isset($row['documento_id']) ? (int) $row['documento_id'] : 0;
            if ($docId <= 0 || isset($latestByDocId[$docId])) {
                continue;
            }

            $label = trim((string) ($row['documento_nombre'] ?? ''));
            if ($label === '') {
                $label = 'Documento #' . $docId;
            }

            $pickedFile = '';
            $pickedUrl = '';
            foreach ($this->extractDocumentStatusFileNames($row['file'] ?? '') as $fileName) {
                $resolvedUrl = $this->resolveDocumentStatusUrl($fileName);
                if ($resolvedUrl === null) {
                    continue;
                }

                $pickedFile = basename($fileName);
                $pickedUrl = $resolvedUrl;
                break;
            }

            $latestByDocId[$docId] = [
                'id' => (int) ($row['id'] ?? 0),
                'documento_id' => $docId,
                'documento_nombre' => $label,
                'status_nombre' => (string) ($row['status_nombre'] ?? ''),
                'comentario' => (string) ($row['comentario'] ?? ''),
                'created_at' => $row['created_at'] ?? null,
                'file' => $pickedFile,
                'file_url' => $pickedUrl,
                'is_image' => $pickedFile !== '' && $this->isImageFile($pickedFile),
            ];
        }

        $documents = array_values($latestByDocId);
        usort($documents, static function (array $left, array $right): int {
            $leftName = (string) ($left['documento_nombre'] ?? '');
            $rightName = (string) ($right['documento_nombre'] ?? '');
            $compare = strcasecmp($leftName, $rightName);
            if ($compare !== 0) {
                return $compare;
            }

            return ((int) ($left['documento_id'] ?? 0)) <=> ((int) ($right['documento_id'] ?? 0));
        });

        return $documents;
    }

    private function extractDocumentStatusFileNames($fileField): array
    {
        if ($fileField === null) {
            return [];
        }

        $raw = trim((string) $fileField);
        if ($raw === '') {
            return [];
        }

        if ($raw[0] === '[' || $raw[0] === '{') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $files = [];
                foreach ($decoded as $item) {
                    if (is_string($item) && trim($item) !== '') {
                        $files[] = trim($item);
                        continue;
                    }

                    if (! is_array($item)) {
                        continue;
                    }

                    foreach (['file', 'fileName', 'filename', 'name', 'path'] as $key) {
                        if (!empty($item[$key]) && is_string($item[$key])) {
                            $files[] = trim($item[$key]);
                            break;
                        }
                    }
                }

                if ($files !== []) {
                    return $files;
                }
            }
        }

        if (strpos($raw, ',') !== false) {
            return array_values(array_filter(array_map('trim', explode(',', $raw)), static function (string $part): bool {
                return $part !== '';
            }));
        }

        return [$raw];
    }

    private function resolveDocumentStatusUrl(string $fileName): ?string
    {
        $fileName = trim($fileName);
        if ($fileName === '' || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
            return null;
        }

        // Si viene una ruta, nos quedamos solo con el nombre base.
        $fileBase = basename($fileName);
        if (
            $fileBase === '' || $fileBase === '.' || $fileBase === '..'
            || strpos($fileBase, "\0") !== false || strpos($fileBase, '..') !== false
        ) {
            return null;
        }

        $driver = config('FileStorage')->driver;

        if ($driver === 'local') {
            // Se preserva el sondeo por directorio candidato para que la salida
            // en modo local sea byte-idéntica al comportamiento previo:
            // file_url($fileBase, $category) == base_url('/assets/uploads/'.$category.'/'.$fileBase).
            $candidates = [
                ['dir' => 'assets/uploads/documentostatus/', 'category' => 'documentostatus'],
                ['dir' => 'assets/uploads/docstatus/', 'category' => 'docstatus'],
            ];

            foreach ($candidates as $candidate) {
                $path = FCPATH . $candidate['dir'] . $fileBase;
                if (is_file($path)) {
                    return file_url($fileBase, $candidate['category']);
                }
            }

            return null;
        }

        // Driver s3: no hay disco local que sondear. `documentostatus` es la
        // categoría canónica; se resuelve directamente a una URL prefirmada
        // para la clave `documentostatus/<basename>` sin emitir /assets/uploads/.
        return file_url($fileBase, 'documentostatus');
    }

    private function loadPagoGestorDocuments(int $tramiteId): array
    {
        if ($tramiteId <= 0 || ! $this->db->tableExists('tra_pago_gestor')) {
            return [];
        }

        $rows = $this->db->table('tra_pago_gestor')
            ->select('id, file, comprobante_final, created_at')
            ->where('tramite_id', $tramiteId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return array_map(function (array $row) use ($tramiteId): array {
            $fileName = (string) ($row['file'] ?? '');

            return $row + [
                'file_url' => file_url($fileName, 'pago_gestor', $tramiteId),
                'is_image' => $this->isImageFile($fileName),
                'doc_label' => $this->mapPagoGestorDocumentType($row['comprobante_final'] ?? ''),
            ];
        }, $rows);
    }

    private function lookupLabel(string $table, string $keyField, int $keyValue, string $labelField): string
    {
        if ($keyValue <= 0 || ! $this->db->tableExists($table) || ! $this->db->fieldExists($keyField, $table) || ! $this->db->fieldExists($labelField, $table)) {
            return '';
        }

        $row = $this->db->table($table)
            ->select($labelField)
            ->where($keyField, $keyValue)
            ->get(1)
            ->getRowArray();

        return (string) ($row[$labelField] ?? '');
    }

    private function mapDerechosPagoSitio($value): string
    {
        $map = [
            'online' => 'En Linea',
            'ventanilla' => 'En Ventanilla',
        ];

        $value = (string) $value;
        return $map[$value] ?? $value;
    }

    private function mapDerechosFormaPago($value): string
    {
        $map = [
            'revolvente' => 'Fondo Revolvente',
            'cliente' => 'Pago Cliente',
        ];

        $value = (string) $value;
        return $map[$value] ?? $value;
    }

    private function mapStatusDoctosGestor($value): string
    {
        $map = [
            'en proceso' => 'En Proceso',
            'entregados' => 'Entregados',
        ];

        $value = (string) $value;
        return $map[$value] ?? $value;
    }

    private function mapPagoGestorDocumentType($value): string
    {
        $map = [
            'tramite_recibido' => 'Tramite Entregado por Gestor',
            'acuse_recibo_cliente' => 'Acuse de Recibo del Cliente',
            'factura_gestor' => 'Factura del Gestor',
            'comprobante_pago' => 'Comprobante de Pago',
            'otro' => 'Otro',
        ];

        $value = (string) $value;
        return $map[$value] ?? $value;
    }

    private function isImageFile(string $fileName): bool
    {
        $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    private function loadCobroClienteFiles(int $tramiteId): array
    {
        if ($tramiteId <= 0 || !$this->db->tableExists('tra_cobro_cliente')) {
            return [];
        }

        $rows = $this->db->table('tra_cobro_cliente')
            ->select('id, file, cobro_correcto, created_at')
            ->where('tramite_id', $tramiteId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return array_map(static function (array $row) use ($tramiteId): array {
            $fileName = (string) ($row['file'] ?? '');
            $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);

            return [
                'id' => (int) ($row['id'] ?? 0),
                'file' => $fileName,
                'cobro_correcto' => (string) ($row['cobro_correcto'] ?? 'otro'),
                'created_at' => $row['created_at'] ?? null,
                'is_image' => $isImage,
                'file_url' => file_url($fileName, 'cobro_cliente', $tramiteId),
            ];
        }, $rows);
    }

    private function loadActivePromesa(int $expedienteId): ?array
    {
        if ($expedienteId <= 0 || !$this->hasCobranzaFinanceTables()) {
            return null;
        }

        $row = $this->db->table('cobranza_promesa_pago cpp')
            ->select('cpp.id, cpp.monto_prometido, cpp.fecha_promesa, cpp.status_code, cpp.observaciones, cmp.name as medio_pago_nombre')
            ->join('cobranza_medio_pago cmp', 'cmp.id = cpp.medio_pago_id', 'left')
            ->where('cpp.expediente_id', $expedienteId)
            ->where('cpp.status_code', 'activa')
            ->orderBy('cpp.fecha_promesa', 'DESC')
            ->get(1)
            ->getRowArray();

        return empty($row) ? null : $row;
    }

    private function loadPagoSummary(int $expedienteId): array
    {
        if ($expedienteId <= 0 || !$this->hasCobranzaFinanceTables()) {
            return [
                'count' => 0,
                'amount_total' => 0.0,
                'partial_count' => 0,
                'latest_pago_reportado' => null,
                'latest_pago_confirmado' => null,
                'pending_count' => 0,
                'confirmed_count' => 0,
            ];
        }

        $rows = $this->db->table('cobranza_pago')
            ->select('monto, tipo_pago, fecha_pago_reportada, fecha_pago_confirmada, status_code')
            ->where('expediente_id', $expedienteId)
            ->get()
            ->getResultArray();

        $summary = [
            'count' => 0,
            'amount_total' => 0.0,
            'partial_count' => 0,
            'latest_pago_reportado' => null,
            'latest_pago_confirmado' => null,
            'pending_count' => 0,
            'confirmed_count' => 0,
        ];

        foreach ($rows as $row) {
            $summary['count']++;
            $summary['amount_total'] += (float) ($row['monto'] ?? 0);
            if (($row['tipo_pago'] ?? '') === 'parcial') {
                $summary['partial_count']++;
            }
            $fecha = $row['fecha_pago_reportada'] ?? null;
            if ($fecha !== null && ($summary['latest_pago_reportado'] === null || $fecha > $summary['latest_pago_reportado'])) {
                $summary['latest_pago_reportado'] = $fecha;
            }

            if (($row['status_code'] ?? '') === 'confirmado') {
                $summary['confirmed_count']++;
                $fechaConfirmada = $row['fecha_pago_confirmada'] ?? null;
                if ($fechaConfirmada !== null && ($summary['latest_pago_confirmado'] === null || $fechaConfirmada > $summary['latest_pago_confirmado'])) {
                    $summary['latest_pago_confirmado'] = $fechaConfirmada;
                }
            } else {
                $summary['pending_count']++;
            }
        }

        return $summary;
    }

    private function loadPendingPagos(int $expedienteId): array
    {
        if ($expedienteId <= 0 || !$this->hasCobranzaFinanceTables()) {
            return [];
        }

        return $this->db->table('cobranza_pago cp')
            ->select('cp.id, cp.monto, cp.tipo_pago, cp.fecha_pago_reportada, cp.referencia_pago, cp.status_code, cmp.name as medio_pago_nombre')
            ->join('cobranza_medio_pago cmp', 'cmp.id = cp.medio_pago_id', 'left')
            ->where('cp.expediente_id', $expedienteId)
            ->where('cp.status_code', 'reportado')
            ->orderBy('cp.fecha_pago_reportada', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function loadExpedienteMap(array $tramiteIds): array
    {
        if (empty($tramiteIds) || !$this->hasCobranzaModuleTables()) {
            return [];
        }

        $rows = $this->db->table('cobranza_expediente ce')
            ->select([
                'ce.id',
                'ce.tramite_id',
                'ce.owner_user_id',
                'ce.status_id',
                'ce.fecha_ultimo_contacto',
                'ce.fecha_proximo_seguimiento',
                'cs.name as status_name',
                'u.firstname as owner_firstname',
                'u.midname as owner_midname',
                'u.lastname as owner_lastname',
            ])
            ->join('cobranza_status cs', 'cs.id = ce.status_id', 'left')
            ->join('users u', 'u.id = ce.owner_user_id', 'left')
            ->whereIn('ce.tramite_id', $tramiteIds)
            ->where('ce.is_active', 1)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['tramite_id']] = $row;
        }

        return $map;
    }

    private function loadEvidenceStats(array $tramiteIds): array
    {
        if (empty($tramiteIds) || !$this->db->tableExists('tra_cobro_cliente')) {
            return [];
        }

        $rows = $this->db->table('tra_cobro_cliente')
            ->select('tramite_id, cobro_correcto, created_at')
            ->whereIn('tramite_id', $tramiteIds)
            ->get()
            ->getResultArray();

        $stats = [];
        foreach ($rows as $row) {
            $tramiteId = (int) ($row['tramite_id'] ?? 0);
            if (!isset($stats[$tramiteId])) {
                $stats[$tramiteId] = [
                    'total' => 0,
                    'complete_count' => 0,
                    'partial_count' => 0,
                    'latest_evidence_at' => null,
                ];
            }

            $stats[$tramiteId]['total']++;
            if (($row['cobro_correcto'] ?? '') === 'completo') {
                $stats[$tramiteId]['complete_count']++;
            }
            if (($row['cobro_correcto'] ?? '') === 'parcial') {
                $stats[$tramiteId]['partial_count']++;
            }

            $createdAt = $row['created_at'] ?? null;
            if ($createdAt !== null && ($stats[$tramiteId]['latest_evidence_at'] === null || $createdAt > $stats[$tramiteId]['latest_evidence_at'])) {
                $stats[$tramiteId]['latest_evidence_at'] = $createdAt;
            }
        }

        return $stats;
    }

    private function loadTimeline(int $tramiteId): array
    {
        $timeline = [];

        if ($this->hasCobranzaModuleTables()) {
            $expediente = $this->db->table('cobranza_expediente')
                ->select('id')
                ->where('tramite_id', $tramiteId)
                ->where('is_active', 1)
                ->get(1)
                ->getRowArray();

            if (!empty($expediente)) {
                $gestionRows = $this->db->table('cobranza_gestion cg')
                    ->select('cg.fecha_gestion, cg.comentarios, cg.siguiente_accion, ctg.name as tipo_name, cc.name as canal_name, crg.name as resultado_name')
                    ->join('cobranza_tipo_gestion ctg', 'ctg.id = cg.tipo_gestion_id', 'left')
                    ->join('cobranza_canal cc', 'cc.id = cg.canal_id', 'left')
                    ->join('cobranza_resultado_gestion crg', 'crg.id = cg.resultado_id', 'left')
                    ->where('cg.expediente_id', (int) $expediente['id'])
                    ->orderBy('cg.fecha_gestion', 'DESC')
                    ->limit(8)
                    ->get()
                    ->getResultArray();

                foreach ($gestionRows as $row) {
                    $description = trim((string) ($row['comentarios'] ?? ''));
                    $nextAction = trim((string) ($row['siguiente_accion'] ?? ''));
                    if ($nextAction !== '') {
                        $description .= ($description !== '' ? ' ' : '') . 'Siguiente accion: ' . $nextAction . '.';
                    }

                    $timeline[] = [
                        'timestamp' => $row['fecha_gestion'] ?? null,
                        'title' => trim(implode(' · ', array_filter([
                            (string) ($row['tipo_name'] ?? 'Gestion'),
                            (string) ($row['canal_name'] ?? ''),
                            (string) ($row['resultado_name'] ?? ''),
                        ]))),
                        'description' => $description,
                        'tone' => 'accent',
                    ];
                }

                if ($this->hasCobranzaFinanceTables()) {
                    $promesaRows = $this->db->table('cobranza_promesa_pago cpp')
                        ->select('cpp.fecha_promesa, cpp.monto_prometido, cpp.status_code, cmp.name as medio_pago_nombre')
                        ->join('cobranza_medio_pago cmp', 'cmp.id = cpp.medio_pago_id', 'left')
                        ->where('cpp.expediente_id', (int) $expediente['id'])
                        ->orderBy('cpp.fecha_promesa', 'DESC')
                        ->limit(4)
                        ->get()
                        ->getResultArray();

                    foreach ($promesaRows as $row) {
                        $timeline[] = [
                            'timestamp' => $row['fecha_promesa'] ?? null,
                            'title' => 'Promesa de pago',
                            'description' => 'Monto prometido: $' . number_format((float) ($row['monto_prometido'] ?? 0), 2) . ' via ' . ((string) ($row['medio_pago_nombre'] ?? 'medio no especificado')) . '.',
                            'tone' => ($row['status_code'] ?? '') === 'activa' ? 'warning' : 'info',
                        ];
                    }

                    $pagoRows = $this->db->table('cobranza_pago cp')
                        ->select('cp.fecha_pago_reportada, cp.fecha_pago_confirmada, cp.monto, cp.tipo_pago, cp.status_code, cmp.name as medio_pago_nombre')
                        ->join('cobranza_medio_pago cmp', 'cmp.id = cp.medio_pago_id', 'left')
                        ->where('cp.expediente_id', (int) $expediente['id'])
                        ->orderBy('COALESCE(cp.fecha_pago_confirmada, cp.fecha_pago_reportada)', 'DESC', false)
                        ->limit(4)
                        ->get()
                        ->getResultArray();

                    foreach ($pagoRows as $row) {
                        $isConfirmed = ($row['status_code'] ?? '') === 'confirmado';
                        $timeline[] = [
                            'timestamp' => $isConfirmed ? ($row['fecha_pago_confirmada'] ?? null) : ($row['fecha_pago_reportada'] ?? null),
                            'title' => $isConfirmed ? 'Pago confirmado' : 'Pago reportado',
                            'description' => ucfirst((string) ($row['tipo_pago'] ?? 'pago')) . ' por $' . number_format((float) ($row['monto'] ?? 0), 2) . ' via ' . ((string) ($row['medio_pago_nombre'] ?? 'medio no especificado')) . '.',
                            'tone' => $isConfirmed ? 'success' : 'warning',
                        ];
                    }
                }
            }
        }

        if ($this->db->tableExists('tra_cobro_cliente')) {
            $evidenceRows = $this->db->table('tra_cobro_cliente')
                ->select('file, cobro_correcto, created_at')
                ->where('tramite_id', $tramiteId)
                ->orderBy('created_at', 'DESC')
                ->limit(8)
                ->get()
                ->getResultArray();

            foreach ($evidenceRows as $row) {
                $timeline[] = [
                    'timestamp' => $row['created_at'] ?? null,
                    'title' => 'Evidencia de cobro cargada',
                    'description' => $this->buildEvidenceDescription($row),
                    'tone' => ($row['cobro_correcto'] ?? '') === 'completo' ? 'success' : (($row['cobro_correcto'] ?? '') === 'parcial' ? 'warning' : 'info'),
                ];
            }
        }

        if ($this->db->tableExists('tramite_audit_log')) {
            $auditRows = $this->db->table('tramite_audit_log')
                ->select('created_at, description, action, username')
                ->where('tramite_id', $tramiteId)
                ->orderBy('created_at', 'DESC')
                ->limit(8)
                ->get()
                ->getResultArray();

            foreach ($auditRows as $row) {
                $timeline[] = [
                    'timestamp' => $row['created_at'] ?? null,
                    'title' => !empty($row['action']) ? (string) $row['action'] : 'Movimiento registrado',
                    'description' => trim((string) ($row['description'] ?? '')),
                    'tone' => 'info',
                ];
            }
        }

        usort($timeline, static function (array $left, array $right): int {
            return strcmp((string) ($right['timestamp'] ?? ''), (string) ($left['timestamp'] ?? ''));
        });

        return array_slice($timeline, 0, 10);
    }

    private function buildEvidenceDescription(array $row): string
    {
        $kind = (string) ($row['cobro_correcto'] ?? 'otro');
        $labels = [
            'completo' => 'Pago completo',
            'parcial' => 'Pago parcial',
            'otro' => 'Evidencia registrada',
        ];
        $label = $labels[$kind] ?? $labels['otro'];
        $file = trim((string) ($row['file'] ?? '')); 

        return $file === '' ? $label : $label . ' - ' . $file;
    }

    private function hasCobranzaModuleTables(): bool
    {
        return $this->db->tableExists('cobranza_expediente')
            && $this->db->tableExists('cobranza_gestion')
            && $this->db->tableExists('cobranza_status')
            && $this->db->tableExists('cobranza_tipo_gestion')
            && $this->db->tableExists('cobranza_canal')
            && $this->db->tableExists('cobranza_resultado_gestion');
    }

    private function hasCobranzaFinanceTables(): bool
    {
        return $this->hasCobranzaModuleTables()
            && $this->db->tableExists('cobranza_medio_pago')
            && $this->db->tableExists('cobranza_promesa_pago')
            && $this->db->tableExists('cobranza_pago');
    }

    private function normalizeFilters(array $filters): array
    {
        $bucket = (string) ($filters['bucket'] ?? 'all');
        $allowedBuckets = ['all', 'my-portfolio', 'en-seguimiento', 'listos-apertura', 'sin-evidencia', 'pago-parcial', 'pago-completo', 'aging-8-plus'];
        if (!in_array($bucket, $allowedBuckets, true)) {
            $bucket = 'all';
        }

        $query = trim((string) ($filters['q'] ?? ''));
        $query = function_exists('mb_strtolower') ? mb_strtolower($query, 'UTF-8') : strtolower($query);
        $page = max(1, (int) ($filters['page'] ?? 1));

        return [
            'bucket' => $bucket,
            'q' => $query,
            'page' => $page,
            'per_page' => 20,
        ];
    }

    private function paginateItems(array $items, array $filters): array
    {
        $totalItems = count($items);
        $perPage = max(1, (int) ($filters['per_page'] ?? 20));
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = min(max(1, (int) ($filters['page'] ?? 1)), $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        return [
            'items' => array_slice($items, $offset, $perPage),
            'pagination' => [
                'total_items' => $totalItems,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'from' => $totalItems === 0 ? 0 : ($offset + 1),
                'to' => $totalItems === 0 ? 0 : min($offset + $perPage, $totalItems),
                'has_prev' => $currentPage > 1,
                'has_next' => $currentPage < $totalPages,
            ],
        ];
    }

    private function resolveSelectedPage(array $items, int $selectedTramiteId, int $perPage): ?int
    {
        if ($selectedTramiteId <= 0 || $perPage <= 0) {
            return null;
        }

        foreach ($items as $index => $item) {
            if ((int) ($item['id'] ?? 0) === $selectedTramiteId) {
                return (int) floor($index / $perPage) + 1;
            }
        }

        return null;
    }

    private function getPaidPagoGestorStatusIds(): array
    {
        if (!$this->db->tableExists('pago_gestor_status')) {
            return [];
        }

        $rows = $this->db->table('pago_gestor_status')->select('id, pago_status')->get()->getResultArray();
        $paidIds = [];
        foreach ($rows as $row) {
            if ($this->isPaidLabel($row['pago_status'] ?? null)) {
                $paidIds[] = (int) ($row['id'] ?? 0);
            }
        }

        return array_values(array_unique(array_filter($paidIds)));
    }

    private function isPaidForCobroCliente(array $tramiteRow): bool
    {
        $paidIds = $this->getPaidPagoGestorStatusIds();
        if (empty($paidIds)) {
            return false;
        }

        return in_array((int) ($tramiteRow['pago_gestor_st_id'] ?? 0), $paidIds, true);
    }

    private function isReadyForCobroCliente(array $tramiteRow): bool
    {
        return $this->isPaidForCobroCliente($tramiteRow)
            && (int) ($tramiteRow['cobrar_cliente'] ?? 0) === 1;
    }

    private function isPaidLabel(?string $label): bool
    {
        $label = trim((string) $label);
        if ($label === '') {
            return false;
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
        return strpos($normalized, 'pagado') !== false;
    }

    private function calculateAgingDays(?string $date): int
    {
        if ($date === null || trim($date) === '') {
            return 0;
        }

        try {
            $referenceDate = new DateTimeImmutable($date);
            $today = new DateTimeImmutable('now');
            return (int) $referenceDate->diff($today)->format('%a');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function formatName(?string $firstname, ?string $midname, ?string $lastname): string
    {
        $parts = array_filter([
            trim((string) $firstname),
            trim((string) $midname),
            trim((string) $lastname),
        ], static fn (string $part): bool => $part !== '');

        return empty($parts) ? 'Sin asignar' : implode(' ', $parts);
    }
}
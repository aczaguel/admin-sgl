<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use Config\Database as ConfigDatabase;
use App\Models\TraTiposModel;
use App\Models\EntidadesModel;
use App\Models\ClienteDirectoModel;
use App\Models\EmpresaGestoraModel;
use App\Models\TraStatusModel;
use App\Models\ReembolsoStatusModel;
use App\Models\CobroStatusModel;
use App\Models\PagoDerechosModel;
use App\Models\PagoGestorStatusModel;
use App\Models\GestorModel;
use App\Models\TraTramiteAsociadoModel;
use App\Models\TraCobroClienteModel;
use App\Models\TraEvidenciasFinalesModel;
use App\Models\ClienteDirectoEjecutivoModel;
use App\Models\BitacoraModel;
use App\Models\TramitesModel;
use App\Models\TraUserLogModel;
use App\Controllers\Deskapp\Tramites;

class Tramitesn extends Tramites
{
    private const ATTENTION_LOCAL_WARNING_DAYS = 5;
    private const ATTENTION_LOCAL_EXPIRED_DAYS = 12;
    private const ATTENTION_FORANEO_WARNING_DAYS = 10;
    private const ATTENTION_FORANEO_EXPIRED_DAYS = 16;
    private const TRA_EVIDENCIA_TIPO_GENERAL = 1;
    private const TRA_EVIDENCIA_TIPO_PAGO_GESTOR = 2;
    private const TRA_EVIDENCIA_TIPO_COBRO_CLIENTE = 3;
    private const PROTOTYPE_STEP4_NOTE_ORIGIN = 'gestor';
    private const PROTOTYPE_STEP4_NOTE_FIELD = 'Nota paso 4';
    private const PROTOTYPE_STEP5_NOTE_ORIGIN = 'cliente';
    private const PROTOTYPE_STEP5_NOTE_FIELD = 'Nota paso 5';

    /**
     * Flag to indicate that the unified layout view should be rendered
     * instead of the legacy prototipo layout.
     */
    protected bool $_unifiedLayoutMode = false;

    private function traEvidenciasSupportsTipo(): bool
    {
        static $supportsTipo = null;

        if ($supportsTipo !== null) {
            return $supportsTipo;
        }

        try {
            $db = ConfigDatabase::connect();
            $supportsTipo = $db->tableExists('tra_evidencias')
                && $db->fieldExists('tipo_evidencia', 'tra_evidencias');
        } catch (\Throwable $e) {
            $supportsTipo = false;
        }

        return $supportsTipo;
    }

    private function applyTraEvidenciaTipoFilter($builder, int $tipoEvidencia, string $tableAlias = 'tra_evidencias'): void
    {
        if (!$this->traEvidenciasSupportsTipo()) {
            return;
        }

        $builder->where($tableAlias . '.tipo_evidencia', $tipoEvidencia);
    }

    private function withTraEvidenciaTipo(array $data, int $tipoEvidencia): array
    {
        if ($this->traEvidenciasSupportsTipo()) {
            $data['tipo_evidencia'] = $tipoEvidencia;
        }

        return $data;
    }

    protected function recordUpdateSaveSideEffects(int $tramiteId, ?string $folio, int $userId, int $statusUpdatedTo, array $changes): void
    {
        $db2 = $this->_getDbData();

        $bitacoraModel = new BitacoraModel($db2);
        $diferencias = [];
        try {
            $diferencias = $this->buildBitacoraChanges($changes);
        } catch (\Throwable $e) {
            log_message('error', 'Error en buildBitacoraChanges (Tramitesn::update_save): ' . $e->getMessage());
        }
        $insert_bitacora = [
            'id' => null,
            'tipo' => 'update',
            'origen' => 'tramite',
            'folio_tramite' => $folio,
            'tramite_id' => (int) $tramiteId,
            'cambios' => json_encode($diferencias),
            'user_id' => (int) $userId,
        ];
        $bitacoraModel->insert($insert_bitacora, 'bitacora');

        $tra_user_log = new TraUserLogModel($db2);
        $log = [
            'tramite_id' => (int) $tramiteId,
            'user_id' => (int) $userId,
            'tra_status_id' => $statusUpdatedTo > 0 ? $statusUpdatedTo : SGL_TRA_STATUS_RECOLECCION_DCTOS,
        ];
        $tra_user_log->insert($log, 'tra_user_log');

        if (!empty($changes)) {
            try {
                $changeCount = log_tramite_bulk_changes($tramiteId, $changes, 'tramite', [
                    'form_name' => 'Datos Generales',
                    'form_step' => 1,
                    'form_section' => 'update_save',
                ]);
                log_message('info', "[Tramitesn::update_save] Registrados {$changeCount} cambios para trámite ID: {$tramiteId}");
            } catch (\Throwable $e) {
                log_message('error', 'Error en log_tramite_bulk_changes (Tramitesn::update_save): ' . $e->getMessage());
            }

            try {
                $cambiosTexto = implode(', ', array_keys($changes));
                notify_tramite_actualizado($tramiteId, $folio ?? "Trámite #{$tramiteId}", $cambiosTexto, $userId);
            } catch (\Throwable $e) {
                log_message('error', 'Error en notify_tramite_actualizado (Tramitesn::update_save): ' . $e->getMessage());
            }
        }
    }

    private function isLockedStatusId(int $statusId): bool
    {
        return in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true);
    }

    protected function isAttentionLocalMunicipio(?int $municipioId): bool
    {
        $municipioId = (int) ($municipioId ?? 0);

        return ($municipioId >= 266 && $municipioId <= 281)
            || ($municipioId >= 657 && $municipioId <= 781);
    }

    protected function resolveAttentionDays(?string $startedAt, ?string $createdAt = null): ?int
    {
        $reference = $startedAt;
        if (empty($reference) || !strtotime((string) $reference)) {
            $reference = $createdAt;
        }

        if (empty($reference) || !strtotime((string) $reference)) {
            return null;
        }

        $days = (int) floor((time() - strtotime((string) $reference)) / 86400);
        return max(0, $days);
    }

    protected function classifyAttentionBucket(int $statusId, ?int $municipioId, ?string $startedAt, ?string $createdAt = null, ?array $trackedStatusIds = null): array
    {
        $trackedIds = $trackedStatusIds ?? [];
        $isTracked = in_array($statusId, $trackedIds, true);
        $days = $this->resolveAttentionDays($startedAt, $createdAt);
        $isLocal = $this->isAttentionLocalMunicipio($municipioId);

        if (!$isTracked || $days === null) {
            return [
                'bucket' => 'normal',
                'tracked' => $isTracked,
                'days' => $days,
                'scope' => $isLocal ? 'local' : 'foraneo',
            ];
        }

        $warningThreshold = $isLocal
            ? self::ATTENTION_LOCAL_WARNING_DAYS
            : self::ATTENTION_FORANEO_WARNING_DAYS;
        $expiredThreshold = $isLocal
            ? self::ATTENTION_LOCAL_EXPIRED_DAYS
            : self::ATTENTION_FORANEO_EXPIRED_DAYS;

        $bucket = $days >= $expiredThreshold ? 'vencido' : 'normal';

        return [
            'bucket' => $bucket,
            'tracked' => true,
            'days' => $days,
            'scope' => $isLocal ? 'local' : 'foraneo',
        ];
    }

    protected function getAttentionTrackedStatusIds(): array
    {
        static $trackedIds = null;

        if ($trackedIds !== null) {
            return $trackedIds;
        }

        try {
            $lockedStatusIds = array_values(array_unique(array_map('intval', SGL_TRA_STATUS_LOCKED_IDS)));
            $rows = ConfigDatabase::connect()
                ->table('tra_status')
                ->select('id')
                ->where('step >=', 1)
                ->where('step <=', 3)
                ->whereNotIn('id', $lockedStatusIds)
                ->get()
                ->getResultArray();

            $trackedIds = array_values(array_unique(array_map('intval', array_column($rows, 'id'))));
        } catch (\Throwable $e) {
            $trackedIds = [];
        }

        return $trackedIds;
    }

    protected function buildAttentionBucketSql(string $bucket = 'attention', string $tableAlias = 'tramite'): string
    {
        $lockedStatusIds = array_values(array_unique(array_map('intval', SGL_TRA_STATUS_LOCKED_IDS)));
        $lockedStatusSql = implode(',', $lockedStatusIds);
        $trackedSql = sprintf(
            '%1$s.tra_status_id IN (SELECT ts.id FROM tra_status ts WHERE ts.step BETWEEN 1 AND 3 AND ts.id NOT IN (%2$s))',
            $tableAlias,
            $lockedStatusSql !== '' ? $lockedStatusSql : '0'
        );
        $daysSql = sprintf(
            'DATEDIFF(CURDATE(), COALESCE(%1$s.started_at, %1$s.created_at))',
            $tableAlias
        );
        $municipioSql = sprintf('COALESCE(%s.ent_municipio_id, 0)', $tableAlias);
        $localSql = sprintf(
            '((%1$s BETWEEN 266 AND 281) OR (%1$s BETWEEN 657 AND 781))',
            $municipioSql
        );
        $foraneoSql = sprintf('NOT (%s)', $localSql);

        $vencidoSql = sprintf(
            '((%1$s AND %2$s >= %3$d) OR (%4$s AND %2$s >= %5$d))',
            $localSql,
            $daysSql,
            self::ATTENTION_LOCAL_EXPIRED_DAYS,
            $foraneoSql,
            self::ATTENTION_FORANEO_EXPIRED_DAYS
        );
        if ($bucket === 'vencido') {
            return sprintf('(%s AND %s)', $trackedSql, $vencidoSql);
        }

        if ($bucket === 'normal') {
            return sprintf('NOT (%s AND %s)', $trackedSql, $vencidoSql);
        }

        return sprintf('(%s AND %s)', $trackedSql, $vencidoSql);
    }

    private function formatPrototypeEvidenceUserName(array $row): string
    {
        $parts = array_filter([
            trim((string) ($row['firstname'] ?? '')),
            trim((string) ($row['midname'] ?? '')),
            trim((string) ($row['lastname'] ?? '')),
        ], static fn ($value): bool => $value !== '');

        return !empty($parts) ? implode(' ', $parts) : 'Sistema';
    }

    private function formatPrototypeEvidenceDateLabel(?string $createdAt): string
    {
        $createdAt = trim((string) $createdAt);
        if ($createdAt === '' || strtotime($createdAt) === false) {
            return 'Sin fecha';
        }

        return date('d/m/Y · H:i', strtotime($createdAt));
    }

    private function buildPrototypeEvidenceItem(array $row): array
    {
        $comment = trim((string) ($row['comentario'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'comment' => $comment,
            'author' => $this->formatPrototypeEvidenceUserName($row),
            'createdAt' => (string) ($row['created_at'] ?? ''),
            'createdAtLabel' => $this->formatPrototypeEvidenceDateLabel((string) ($row['created_at'] ?? '')),
        ];
    }

    private function getPrototypeEvidencias(int $tramiteId): array
    {
        if ($tramiteId <= 0) {
            return [];
        }

        try {
            $db = ConfigDatabase::connect();
            if (!$db->tableExists('tra_evidencias')) {
                return [];
            }

            $builder = $db->table('tra_evidencias')
                ->select('tra_evidencias.id, tra_evidencias.comentario, tra_evidencias.created_at, tra_evidencias.user_id, users.firstname, users.midname, users.lastname')
                ->join('users', 'users.id = tra_evidencias.user_id', 'left')
                ->where('tra_evidencias.tramite_id', $tramiteId)
                ->where('tra_evidencias.status', 1);

            $this->applyTraEvidenciaTipoFilter($builder, self::TRA_EVIDENCIA_TIPO_GENERAL);

            $rows = $builder
                ->orderBy('tra_evidencias.created_at', 'DESC')
                ->orderBy('tra_evidencias.id', 'DESC')
                ->limit(40)
                ->get()
                ->getResultArray();

            return array_map(fn (array $row): array => $this->buildPrototypeEvidenceItem($row), $rows);
        } catch (\Throwable $e) {
            log_message('error', 'Error loading prototype evidencias for tramite ' . $tramiteId . ': ' . $e->getMessage());
            return [];
        }
    }

    private function extractPrototypeBitacoraComment($rawChanges): string
    {
        if (is_string($rawChanges)) {
            $decoded = json_decode($rawChanges, true);
            $rawChanges = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($rawChanges)) {
            return '';
        }

        foreach ($rawChanges as $values) {
            if (!is_array($values)) {
                continue;
            }

            $candidate = trim((string) ($values['valor_nuevo'] ?? $values['comment'] ?? ''));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    private function getPrototypeStep4Notes(int $tramiteId): array
    {
        if ($tramiteId <= 0) {
            return [];
        }

        try {
            $db = ConfigDatabase::connect();
            if ($db->tableExists('tra_evidencias') && $this->traEvidenciasSupportsTipo()) {
                $builder = $db->table('tra_evidencias')
                    ->select('tra_evidencias.id, tra_evidencias.comentario, tra_evidencias.created_at, tra_evidencias.user_id, users.firstname, users.midname, users.lastname')
                    ->join('users', 'users.id = tra_evidencias.user_id', 'left')
                    ->where('tra_evidencias.tramite_id', $tramiteId)
                    ->where('tra_evidencias.status', 1);

                $this->applyTraEvidenciaTipoFilter($builder, self::TRA_EVIDENCIA_TIPO_PAGO_GESTOR);

                $rows = $builder
                    ->orderBy('tra_evidencias.created_at', 'DESC')
                    ->orderBy('tra_evidencias.id', 'DESC')
                    ->limit(40)
                    ->get()
                    ->getResultArray();

                return array_map(fn (array $row): array => $this->buildPrototypeEvidenceItem($row), $rows);
            }

            $bitacoraDb = $this->_getDbData();
            if (!is_object($bitacoraDb) || !method_exists($bitacoraDb, 'tableExists') || !$bitacoraDb->tableExists('bitacora')) {
                return [];
            }

            $rows = $bitacoraDb->table('bitacora')
                ->select('bitacora.id, bitacora.cambios, bitacora.created_at, bitacora.user_id, users.firstname, users.midname, users.lastname')
                ->join('users', 'users.id = bitacora.user_id', 'left')
                ->where('bitacora.tramite_id', $tramiteId)
                ->where('bitacora.origen', self::PROTOTYPE_STEP4_NOTE_ORIGIN)
                ->orderBy('bitacora.created_at', 'DESC')
                ->orderBy('bitacora.id', 'DESC')
                ->limit(40)
                ->get()
                ->getResultArray();

            $items = [];
            foreach ($rows as $row) {
                $comment = $this->extractPrototypeBitacoraComment($row['cambios'] ?? []);
                if ($comment === '') {
                    continue;
                }

                $items[] = $this->buildPrototypeEvidenceItem([
                    'id' => (int) ($row['id'] ?? 0),
                    'comentario' => $comment,
                    'created_at' => (string) ($row['created_at'] ?? ''),
                    'user_id' => (int) ($row['user_id'] ?? 0),
                    'firstname' => (string) ($row['firstname'] ?? ''),
                    'midname' => (string) ($row['midname'] ?? ''),
                    'lastname' => (string) ($row['lastname'] ?? ''),
                ]);
            }

            return $items;
        } catch (\Throwable $e) {
            log_message('error', 'Error loading prototype step4 notes for tramite ' . $tramiteId . ': ' . $e->getMessage());
            return [];
        }
    }

    private function getPrototypeStep5Notes(int $tramiteId): array
    {
        if ($tramiteId <= 0) {
            return [];
        }

        try {
            $db = ConfigDatabase::connect();
            if ($db->tableExists('tra_evidencias') && $this->traEvidenciasSupportsTipo()) {
                $builder = $db->table('tra_evidencias')
                    ->select('tra_evidencias.id, tra_evidencias.comentario, tra_evidencias.created_at, tra_evidencias.user_id, users.firstname, users.midname, users.lastname')
                    ->join('users', 'users.id = tra_evidencias.user_id', 'left')
                    ->where('tra_evidencias.tramite_id', $tramiteId)
                    ->where('tra_evidencias.status', 1);

                $this->applyTraEvidenciaTipoFilter($builder, self::TRA_EVIDENCIA_TIPO_COBRO_CLIENTE);

                $rows = $builder
                    ->orderBy('tra_evidencias.created_at', 'DESC')
                    ->orderBy('tra_evidencias.id', 'DESC')
                    ->limit(40)
                    ->get()
                    ->getResultArray();

                return array_map(fn (array $row): array => $this->buildPrototypeEvidenceItem($row), $rows);
            }

            $bitacoraDb = $this->_getDbData();
            if (!is_object($bitacoraDb) || !method_exists($bitacoraDb, 'tableExists') || !$bitacoraDb->tableExists('bitacora')) {
                return [];
            }

            $rows = $bitacoraDb->table('bitacora')
                ->select('bitacora.id, bitacora.cambios, bitacora.created_at, bitacora.user_id, users.firstname, users.midname, users.lastname')
                ->join('users', 'users.id = bitacora.user_id', 'left')
                ->where('bitacora.tramite_id', $tramiteId)
                ->where('bitacora.origen', self::PROTOTYPE_STEP5_NOTE_ORIGIN)
                ->orderBy('bitacora.created_at', 'DESC')
                ->orderBy('bitacora.id', 'DESC')
                ->limit(40)
                ->get()
                ->getResultArray();

            $items = [];
            foreach ($rows as $row) {
                $comment = $this->extractPrototypeBitacoraComment($row['cambios'] ?? []);
                if ($comment === '') {
                    continue;
                }

                $items[] = $this->buildPrototypeEvidenceItem([
                    'id' => (int) ($row['id'] ?? 0),
                    'comentario' => $comment,
                    'created_at' => (string) ($row['created_at'] ?? ''),
                    'user_id' => (int) ($row['user_id'] ?? 0),
                    'firstname' => (string) ($row['firstname'] ?? ''),
                    'midname' => (string) ($row['midname'] ?? ''),
                    'lastname' => (string) ($row['lastname'] ?? ''),
                ]);
            }

            return $items;
        } catch (\Throwable $e) {
            log_message('error', 'Error loading prototype step5 notes for tramite ' . $tramiteId . ': ' . $e->getMessage());
            return [];
        }
    }

    private function getPrototypeServiceRowsRaw(int $tramiteId, int $principalTipoId): array
    {
        if ($tramiteId <= 0) {
            return [];
        }

        try {
            $db = ConfigDatabase::connect();
            $serviceRows = $db->table('tra_tramite_asociado')
                ->select('tra_tramite_asociado.id, tra_tramite_asociado.tra_tipos_id, tra_tramite_asociado.costo_tramite, tra_tipos.tipo_tramite')
                ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id', 'left')
                ->where('tra_tramite_asociado.tramite_id', $tramiteId)
                ->orderBy('tra_tramite_asociado.id', 'ASC')
                ->get()
                ->getResultArray();

            $serviceRowsRaw = [];
            foreach ($serviceRows as $serviceRow) {
                $serviceTipoId = (int) ($serviceRow['tra_tipos_id'] ?? 0);
                $serviceLabel = trim((string) ($serviceRow['tipo_tramite'] ?? ('Tipo #' . $serviceTipoId)));
                if ($serviceTipoId <= 0 || $serviceLabel === '') {
                    continue;
                }

                $serviceRowsRaw[] = [
                    'asociado_id' => (int) ($serviceRow['id'] ?? 0),
                    'tra_tipos_id' => $serviceTipoId,
                    'label' => $serviceLabel,
                    'costo_tramite' => is_numeric($serviceRow['costo_tramite'] ?? null)
                        ? (float) $serviceRow['costo_tramite']
                        : 0.0,
                    'is_principal' => $serviceTipoId === $principalTipoId,
                ];
            }

            if ($principalTipoId > 0) {
                $hasPrincipal = false;
                foreach ($serviceRowsRaw as $serviceRow) {
                    if ((int) ($serviceRow['tra_tipos_id'] ?? 0) === $principalTipoId) {
                        $hasPrincipal = true;
                        break;
                    }
                }

                if (!$hasPrincipal) {
                    $principalRow = $db->table('tra_tipos')
                        ->select('id, tipo_tramite')
                        ->where('id', $principalTipoId)
                        ->get()
                        ->getRowArray();
                    $principalLabel = trim((string) ($principalRow['tipo_tramite'] ?? ('Tipo #' . $principalTipoId)));
                    if ($principalLabel !== '') {
                        array_unshift($serviceRowsRaw, [
                            'asociado_id' => 0,
                            'tra_tipos_id' => $principalTipoId,
                            'label' => $principalLabel,
                            'costo_tramite' => 0.0,
                            'is_principal' => true,
                        ]);
                    }
                }
            }

            return $serviceRowsRaw;
        } catch (\Throwable $e) {
            log_message('error', 'Error loading service rows for prototype tramite ' . $tramiteId . ': ' . $e->getMessage());
            return [];
        }
    }

    private function buildPrototypeStep1DocumentState(int $tramiteId, int $principalTipoId, ?array $serviceRowsRaw = null): array
    {
        $defaultState = [
            'documents' => [],
            'documentOptions' => [],
            'documentOptionMeta' => [],
            'summary' => [
                'requiredTotal' => 0,
                'uploadedRequired' => 0,
                'uploadedTotal' => 0,
            ],
            'allowedDocIds' => [],
        ];

        if ($tramiteId <= 0) {
            return $defaultState;
        }

        try {
            $db = ConfigDatabase::connect();
            $serviceRowsRaw = is_array($serviceRowsRaw)
                ? array_values($serviceRowsRaw)
                : $this->getPrototypeServiceRowsRaw($tramiteId, $principalTipoId);

            $tipoLabelsById = [];
            foreach ($serviceRowsRaw as $serviceRow) {
                $tipoId = (int) ($serviceRow['tra_tipos_id'] ?? 0);
                $tipoLabel = trim((string) ($serviceRow['label'] ?? ''));
                if ($tipoId > 0 && $tipoLabel !== '') {
                    $tipoLabelsById[$tipoId] = $tipoLabel;
                }
            }

            if ($principalTipoId > 0 && !isset($tipoLabelsById[$principalTipoId])) {
                $principalRow = $db->table('tra_tipos')
                    ->select('id, tipo_tramite')
                    ->where('id', $principalTipoId)
                    ->get()
                    ->getRowArray();
                $principalLabel = trim((string) ($principalRow['tipo_tramite'] ?? ''));
                if ($principalLabel !== '') {
                    $tipoLabelsById[$principalTipoId] = $principalLabel;
                }
            }

            $tipoIds = array_values(array_unique(array_filter(array_map('intval', array_keys($tipoLabelsById)))));
            $docCatalogPk = $db->fieldExists('documento_id', 'documento') ? 'documento_id' : 'id';
            $hasOptionalFlag = $db->tableExists('tra_tipo_documentos') && $db->fieldExists('es_obligatorio', 'tra_tipo_documentos');
            $documentsById = [];

            if ($db->tableExists('documento')) {
                $catalogRows = $db->table('documento')
                    ->select($docCatalogPk . ' AS documento_id, documento AS documento_nombre')
                    ->orderBy('documento', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($catalogRows as $row) {
                    $documentoId = (int) ($row['documento_id'] ?? 0);
                    if ($documentoId <= 0) {
                        continue;
                    }

                    $documentsById[$documentoId] = [
                        'documento_id' => $documentoId,
                        'documento_nombre' => trim((string) ($row['documento_nombre'] ?? ('Documento #' . $documentoId))) ?: ('Documento #' . $documentoId),
                        'is_required' => false,
                        'is_configured' => false,
                        'source_origin' => 'catalog',
                        'source_badge' => 'Catálogo general',
                        'source_tone' => 'neutral',
                        'source_types' => [],
                        'source_types_label' => '',
                        'has_file' => false,
                        'file' => '',
                        'file_url' => '',
                        'status_label' => 'Pendiente',
                        'comentario' => '',
                    ];
                }
            }

            if ($tipoIds !== [] && $db->tableExists('tra_tipo_documentos')) {
                $query = $db->table('tra_tipo_documentos ttd')
                    ->select('ttd.tra_tipos_id, ttd.documento_id, d.documento AS documento_nombre' . ($hasOptionalFlag ? ', ttd.es_obligatorio' : ''))
                    ->join('documento d', 'd.' . $docCatalogPk . ' = ttd.documento_id', 'left')
                    ->whereIn('ttd.tra_tipos_id', $tipoIds)
                    ->orderBy('d.documento', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($query as $row) {
                    $documentoId = (int) ($row['documento_id'] ?? 0);
                    if ($documentoId <= 0) {
                        continue;
                    }

                    if (!isset($documentsById[$documentoId])) {
                        $documentsById[$documentoId] = [
                            'documento_id' => $documentoId,
                            'documento_nombre' => trim((string) ($row['documento_nombre'] ?? ('Documento #' . $documentoId))) ?: ('Documento #' . $documentoId),
                            'is_required' => !$hasOptionalFlag || (int) ($row['es_obligatorio'] ?? 1) === 1,
                            'is_configured' => true,
                            'source_origin' => 'configured',
                            'source_badge' => 'Ligado al tipo',
                            'source_tone' => 'success',
                            'source_types' => [],
                            'source_types_label' => '',
                            'has_file' => false,
                            'file' => '',
                            'file_url' => '',
                            'status_label' => 'Pendiente',
                            'comentario' => '',
                        ];
                    }

                    $documentsById[$documentoId]['is_configured'] = true;
                    $documentsById[$documentoId]['source_origin'] = 'configured';
                    $documentsById[$documentoId]['source_badge'] = 'Ligado al tipo';
                    $documentsById[$documentoId]['source_tone'] = 'success';

                    if ($hasOptionalFlag && (int) ($row['es_obligatorio'] ?? 0) === 1) {
                        $documentsById[$documentoId]['is_required'] = true;
                    }

                    $tipoId = (int) ($row['tra_tipos_id'] ?? 0);
                    $tipoLabel = $tipoLabelsById[$tipoId] ?? '';
                    if ($tipoLabel !== '' && !in_array($tipoLabel, $documentsById[$documentoId]['source_types'], true)) {
                        $documentsById[$documentoId]['source_types'][] = $tipoLabel;
                    }
                }
            }

            if ($db->tableExists('tra_doc_status')) {
                $statusQuery = $db->table('tra_doc_status tds')
                    ->select('tds.documento_id, tds.file, tds.comentario, tds.updated_at, tds.created_at, ds.st_documento AS status_documento_label, d.documento AS documento_nombre')
                    ->join('doc_statuses ds', 'ds.id = tds.status_documento_id', 'left')
                    ->join('documento d', 'd.' . $docCatalogPk . ' = tds.documento_id', 'left')
                    ->where('tds.tramite_id', $tramiteId)
                    ->orderBy('tds.updated_at', 'DESC')
                    ->orderBy('tds.created_at', 'DESC')
                    ->orderBy('tds.id', 'DESC')
                    ->get()
                    ->getResultArray();

                foreach ($statusQuery as $row) {
                    $documentoId = (int) ($row['documento_id'] ?? 0);
                    if ($documentoId <= 0) {
                        continue;
                    }

                    if (!isset($documentsById[$documentoId])) {
                        $documentsById[$documentoId] = [
                            'documento_id' => $documentoId,
                            'documento_nombre' => trim((string) ($row['documento_nombre'] ?? ('Documento #' . $documentoId))) ?: ('Documento #' . $documentoId),
                            'is_required' => false,
                            'is_configured' => false,
                            'source_origin' => 'direct',
                            'source_badge' => 'Histórico del trámite',
                            'source_tone' => 'neutral',
                            'source_types' => ['Ligado directo al tramite'],
                            'source_types_label' => 'Ligado directo al tramite',
                            'has_file' => false,
                            'file' => '',
                            'file_url' => '',
                            'status_label' => 'Pendiente',
                            'comentario' => '',
                        ];
                    }

                    $fileName = trim((string) ($row['file'] ?? ''));
                    $existingFile = trim((string) ($documentsById[$documentoId]['file'] ?? ''));
                    if ($fileName !== '' || $existingFile === '') {
                        $documentsById[$documentoId]['file'] = $fileName;
                        $documentsById[$documentoId]['has_file'] = $fileName !== '';
                        // A single tra_doc_status.file column may hold several
                        // comma-separated filenames (multi-upload). Split them so
                        // each renders as its own link with its own resolved URL.
                        $splitNames = $fileName !== ''
                            ? array_values(array_filter(
                                array_map('trim', explode(',', $fileName)),
                                static function (string $f): bool {
                                    return $f !== '';
                                }
                            ))
                            : [];
                        $documentsById[$documentoId]['files'] = array_map(
                            static function (string $f): array {
                                // XML forces a download (browsers render raw XML
                                // inline); everything else keeps the inline URL.
                                $isXml = strtolower((string) pathinfo($f, PATHINFO_EXTENSION)) === 'xml';
                                return [
                                    'name' => $f,
                                    'url' => $isXml
                                        ? file_download_url($f, 'documentostatus')
                                        : file_url($f, 'documentostatus'),
                                    'is_image' => is_image_filename($f),
                                ];
                            },
                            $splitNames
                        );
                        // Backward-compat: file_url keeps the first file's URL.
                        $documentsById[$documentoId]['file_url'] = !empty($documentsById[$documentoId]['files'])
                            ? (string) $documentsById[$documentoId]['files'][0]['url']
                            : '';
                        $documentsById[$documentoId]['status_label'] = trim((string) ($row['status_documento_label'] ?? '')) ?: ($fileName !== '' ? 'Cargado' : 'Pendiente');
                        $documentsById[$documentoId]['comentario'] = trim((string) ($row['comentario'] ?? ''));
                    }
                }
            }

            $documents = array_values($documentsById);
            foreach ($documents as &$document) {
                $document['source_types_label'] = !empty($document['source_types'])
                    ? implode(', ', $document['source_types'])
                    : '';

                if (!empty($document['is_configured']) && $document['source_types_label'] === '') {
                    $document['source_types_label'] = 'Configurado para los tipos ligados actualmente';
                }
            }
            unset($document);

            usort($documents, static function (array $left, array $right): int {
                $configuredCompare = ((int) !empty($right['is_configured'])) <=> ((int) !empty($left['is_configured']));
                if ($configuredCompare !== 0) {
                    return $configuredCompare;
                }

                $requiredCompare = ((int) !empty($right['is_required'])) <=> ((int) !empty($left['is_required']));
                if ($requiredCompare !== 0) {
                    return $requiredCompare;
                }

                return strcasecmp((string) ($left['documento_nombre'] ?? ''), (string) ($right['documento_nombre'] ?? ''));
            });

            $documentOptions = [];
            $documentOptionMeta = [];
            foreach ($documents as $document) {
                $documentoId = (int) ($document['documento_id'] ?? 0);
                if ($documentoId <= 0) {
                    continue;
                }

                $label = trim((string) ($document['documento_nombre'] ?? ('Documento #' . $documentoId)));
                if (empty($document['is_required'])) {
                    $label .= ' (opcional)';
                }
                $documentOptions[$documentoId] = $label;
                $documentOptionMeta[$documentoId] = [
                    'documento_nombre' => trim((string) ($document['documento_nombre'] ?? ('Documento #' . $documentoId))) ?: ('Documento #' . $documentoId),
                    'isConfigured' => !empty($document['is_configured']),
                    'isRequired' => !empty($document['is_required']),
                    'sourceBadge' => (string) ($document['source_badge'] ?? 'Catálogo general'),
                    'sourceTone' => (string) ($document['source_tone'] ?? 'neutral'),
                ];
            }

            $requiredTotal = 0;
            $uploadedRequired = 0;
            $uploadedTotal = 0;
            foreach ($documents as $document) {
                $hasFile = !empty($document['has_file']);
                if ($hasFile) {
                    $uploadedTotal++;
                }
                if (!empty($document['is_required'])) {
                    $requiredTotal++;
                    if ($hasFile) {
                        $uploadedRequired++;
                    }
                }
            }

            return [
                'documents' => $documents,
                'documentOptions' => $documentOptions,
                'documentOptionMeta' => $documentOptionMeta,
                'summary' => [
                    'requiredTotal' => $requiredTotal,
                    'uploadedRequired' => $uploadedRequired,
                    'uploadedTotal' => $uploadedTotal,
                ],
                'allowedDocIds' => array_values(array_map('intval', array_keys($documentOptions))),
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error loading step1 document state for prototype tramite ' . $tramiteId . ': ' . $e->getMessage());
            return $defaultState;
        }
    }

    public function prototype_evidencias_add($tramiteId = null)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        $tramiteId = (int) ($tramiteId ?? $this->request->uri->getSegment(4) ?? 0);

        if ($tramiteId <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $canQuickAction = has_permission('quick_action_bitacora', $perms, $roles);
        $canAdd = $canQuickAction && has_permission('quick_action_bitacora_add', $perms, $roles);
        if (!$canAdd) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'comentario' => 'required|min_length[3]|max_length[2000]',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode(' ', $validation->getErrors()),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db = ConfigDatabase::connect();
        $tramiteRow = $db->table('tramite')
            ->select('id, folio, tra_status_id')
            ->where('id', $tramiteId)
            ->get()
            ->getRowArray();

        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'No se encontró el trámite solicitado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if ($this->isLockedStatusId((int) ($tramiteRow['tra_status_id'] ?? 0))) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Esta sección está en modo de solo lectura.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $comment = trim((string) $this->request->getPost('comentario'));
        $insertData = $this->withTraEvidenciaTipo([
            'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
            'tramite_id' => $tramiteId,
            'comentario' => $comment,
            'user_id' => $userId,
            'status' => 1,
        ], self::TRA_EVIDENCIA_TIPO_GENERAL);

        try {
            $db->table('tra_evidencias')->insert($insertData);
            $insertId = (int) $db->insertID();

            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->encontrarDiferencias($insertData, []);
            $bitacoraModel->insert([
                'tipo' => 'insert',
                'origen' => 'evidencia',
                'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                'tramite_id' => $tramiteId,
                'cambios' => json_encode($diferencias),
                'user_id' => $userId,
            ], 'bitacora');

            $insertedRow = $db->table('tra_evidencias')
                ->select('tra_evidencias.id, tra_evidencias.comentario, tra_evidencias.created_at, tra_evidencias.user_id, users.firstname, users.midname, users.lastname')
                ->join('users', 'users.id = tra_evidencias.user_id', 'left')
                ->where('tra_evidencias.id', $insertId)
                ->get()
                ->getRowArray();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Comentario guardado correctamente.',
                'item' => $this->buildPrototypeEvidenceItem($insertedRow ?: $insertData),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en Tramitesn::prototype_evidencias_add: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'No se pudo guardar el comentario.',
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function prototype_step4_notes_add($tramiteId = null)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        $tramiteId = (int) ($tramiteId ?? $this->request->uri->getSegment(4) ?? 0);

        if ($tramiteId <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $canQuickAction = has_permission('quick_action_bitacora', $perms, $roles);
        $canAdd = $canQuickAction && has_permission('quick_action_bitacora_add', $perms, $roles);
        $canSectionPagoGestor = has_permission('section_pago_gestor', $perms, $roles);
        if (!$canAdd || !$canSectionPagoGestor) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'comentario' => 'required|min_length[3]|max_length[2000]',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode(' ', $validation->getErrors()),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db = ConfigDatabase::connect();
        $tramiteRow = $db->table('tramite')
            ->select('id, folio, tra_status_id, reembolso_status_id, cobro_status_id, pago_gestor_st_id, status_doctos_gestor')
            ->where('id', $tramiteId)
            ->get()
            ->getRowArray();

        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'No se encontró el trámite solicitado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db2 = $this->_getDbData();
        $pagoGestorStatusModel = new PagoGestorStatusModel($db2);
        $canKeepStep4Editable = $this->canKeepStep4Editable(
            (int) ($tramiteRow['reembolso_status_id'] ?? 0),
            (int) ($tramiteRow['pago_gestor_st_id'] ?? 0),
            $pagoGestorStatusModel->getPagoGestorStatusOptions(),
            (string) ($tramiteRow['status_doctos_gestor'] ?? '')
        );
        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $step4ReadOnly = $this->isLockedStatusId($traStatusId);
        if ($step4ReadOnly) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Pago a gestor está en modo de solo lectura para este trámite.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $comment = trim((string) $this->request->getPost('comentario'));

        try {
            if ($this->traEvidenciasSupportsTipo()) {
                $insertData = $this->withTraEvidenciaTipo([
                    'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                    'tramite_id' => $tramiteId,
                    'comentario' => $comment,
                    'user_id' => $userId,
                    'status' => 1,
                ], self::TRA_EVIDENCIA_TIPO_PAGO_GESTOR);

                $db->table('tra_evidencias')->insert($insertData);
                $insertId = (int) $db->insertID();

                $db2 = $this->_getDbData();
                $bitacoraModel = new BitacoraModel($db2);
                $diferencias = $this->encontrarDiferencias($insertData, []);
                $bitacoraModel->insert([
                    'tipo' => 'insert',
                    'origen' => self::PROTOTYPE_STEP4_NOTE_ORIGIN,
                    'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                    'tramite_id' => $tramiteId,
                    'cambios' => json_encode($diferencias),
                    'user_id' => $userId,
                ], 'bitacora');

                $insertedRow = $db->table('tra_evidencias')
                    ->select('tra_evidencias.id, tra_evidencias.comentario, tra_evidencias.created_at, tra_evidencias.user_id, users.firstname, users.midname, users.lastname')
                    ->join('users', 'users.id = tra_evidencias.user_id', 'left')
                    ->where('tra_evidencias.id', $insertId)
                    ->get()
                    ->getRowArray();

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Nota interna guardada correctamente.',
                    'item' => $this->buildPrototypeEvidenceItem($insertedRow ?: $insertData),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $changes = [
                self::PROTOTYPE_STEP4_NOTE_FIELD => [
                    'valor_original' => '',
                    'valor_nuevo' => $comment,
                ],
            ];

            $bitacoraDb = $this->_getDbData();
            if (!is_object($bitacoraDb) || !method_exists($bitacoraDb, 'table') || !method_exists($bitacoraDb, 'insertID')) {
                throw new \RuntimeException('La conexión legacy de bitácora no está disponible.');
            }

            $bitacoraDb->table('bitacora')->insert([
                'tipo' => 'insert',
                'origen' => self::PROTOTYPE_STEP4_NOTE_ORIGIN,
                'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                'tramite_id' => $tramiteId,
                'cambios' => json_encode($changes),
                'user_id' => $userId,
                'status' => 1,
            ]);
            $insertId = (int) $bitacoraDb->insertID();

            $insertedRow = $bitacoraDb->table('bitacora')
                ->select('bitacora.id, bitacora.cambios, bitacora.created_at, bitacora.user_id, users.firstname, users.midname, users.lastname')
                ->join('users', 'users.id = bitacora.user_id', 'left')
                ->where('bitacora.id', $insertId)
                ->get()
                ->getRowArray();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Nota interna guardada correctamente.',
                'item' => $this->buildPrototypeEvidenceItem([
                    'id' => (int) ($insertedRow['id'] ?? $insertId),
                    'comentario' => $this->extractPrototypeBitacoraComment($insertedRow['cambios'] ?? $changes),
                    'created_at' => (string) ($insertedRow['created_at'] ?? date('Y-m-d H:i:s')),
                    'user_id' => (int) ($insertedRow['user_id'] ?? $userId),
                    'firstname' => (string) ($insertedRow['firstname'] ?? ''),
                    'midname' => (string) ($insertedRow['midname'] ?? ''),
                    'lastname' => (string) ($insertedRow['lastname'] ?? ''),
                ]),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en Tramitesn::prototype_step4_notes_add: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'No se pudo guardar la nota interna de Pago a gestor.',
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function prototype_step5_notes_add($tramiteId = null)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        $tramiteId = (int) ($tramiteId ?? $this->request->uri->getSegment(4) ?? 0);

        if ($tramiteId <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $canQuickAction = has_permission('quick_action_bitacora', $perms, $roles);
        $canAdd = $canQuickAction && has_permission('quick_action_bitacora_add', $perms, $roles);
        $canAccessCobroCliente = can_access_cobro_cliente_surface($roles, $perms);
        $canEditCobroCliente = can_edit_cobro_cliente_surface($roles, $perms);
        if (!$canAdd || !$canAccessCobroCliente || !$canEditCobroCliente) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'comentario' => 'required|min_length[3]|max_length[2000]',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode(' ', $validation->getErrors()),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db = ConfigDatabase::connect();
        $tramiteRow = $db->table('tramite')
            ->select('id, folio, tra_status_id, reembolso_status_id, cobro_status_id')
            ->where('id', $tramiteId)
            ->get()
            ->getRowArray();

        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'No se encontró el trámite solicitado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $step5ReadOnly = $this->isLockedStatusId($traStatusId);
        if ($step5ReadOnly) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Cobro a cliente está en modo de solo lectura para este trámite.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if (!puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', (int) ($tramiteRow['reembolso_status_id'] ?? 0), (int) ($tramiteRow['cobro_status_id'] ?? 0), 5)) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Cobro a cliente no está editable para este estatus y este perfil.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $comment = trim((string) $this->request->getPost('comentario'));

        try {
            if ($this->traEvidenciasSupportsTipo()) {
                $insertData = $this->withTraEvidenciaTipo([
                    'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                    'tramite_id' => $tramiteId,
                    'comentario' => $comment,
                    'user_id' => $userId,
                    'status' => 1,
                ], self::TRA_EVIDENCIA_TIPO_COBRO_CLIENTE);

                $db->table('tra_evidencias')->insert($insertData);
                $insertId = (int) $db->insertID();

                $db2 = $this->_getDbData();
                $bitacoraModel = new BitacoraModel($db2);
                $diferencias = $this->encontrarDiferencias($insertData, []);
                $bitacoraModel->insert([
                    'tipo' => 'insert',
                    'origen' => self::PROTOTYPE_STEP5_NOTE_ORIGIN,
                    'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                    'tramite_id' => $tramiteId,
                    'cambios' => json_encode($diferencias),
                    'user_id' => $userId,
                ], 'bitacora');

                $insertedRow = $db->table('tra_evidencias')
                    ->select('tra_evidencias.id, tra_evidencias.comentario, tra_evidencias.created_at, tra_evidencias.user_id, users.firstname, users.midname, users.lastname')
                    ->join('users', 'users.id = tra_evidencias.user_id', 'left')
                    ->where('tra_evidencias.id', $insertId)
                    ->get()
                    ->getRowArray();

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Nota interna guardada correctamente.',
                    'item' => $this->buildPrototypeEvidenceItem($insertedRow ?: $insertData),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $changes = [
                self::PROTOTYPE_STEP5_NOTE_FIELD => [
                    'valor_original' => '',
                    'valor_nuevo' => $comment,
                ],
            ];

            $bitacoraDb = $this->_getDbData();
            if (!is_object($bitacoraDb) || !method_exists($bitacoraDb, 'table') || !method_exists($bitacoraDb, 'insertID')) {
                throw new \RuntimeException('La conexión legacy de bitácora no está disponible.');
            }

            $bitacoraDb->table('bitacora')->insert([
                'tipo' => 'insert',
                'origen' => self::PROTOTYPE_STEP5_NOTE_ORIGIN,
                'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                'tramite_id' => $tramiteId,
                'cambios' => json_encode($changes),
                'user_id' => $userId,
                'status' => 1,
            ]);
            $insertId = (int) $bitacoraDb->insertID();

            $insertedRow = $bitacoraDb->table('bitacora')
                ->select('bitacora.id, bitacora.cambios, bitacora.created_at, bitacora.user_id, users.firstname, users.midname, users.lastname')
                ->join('users', 'users.id = bitacora.user_id', 'left')
                ->where('bitacora.id', $insertId)
                ->get()
                ->getRowArray();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Nota interna guardada correctamente.',
                'item' => $this->buildPrototypeEvidenceItem([
                    'id' => (int) ($insertedRow['id'] ?? $insertId),
                    'comentario' => $this->extractPrototypeBitacoraComment($insertedRow['cambios'] ?? $changes),
                    'created_at' => (string) ($insertedRow['created_at'] ?? date('Y-m-d H:i:s')),
                    'user_id' => (int) ($insertedRow['user_id'] ?? $userId),
                    'firstname' => (string) ($insertedRow['firstname'] ?? ''),
                    'midname' => (string) ($insertedRow['midname'] ?? ''),
                    'lastname' => (string) ($insertedRow['lastname'] ?? ''),
                ]),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en Tramitesn::prototype_step5_notes_add: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'No se pudo guardar la nota interna de Cobro a cliente.',
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    protected function resolveAttentionListBucket(?string $bucket): string
    {
        $bucket = strtolower(trim((string) ($bucket ?? 'normal')));
        $allowedBuckets = ['normal', 'vencido'];

        return in_array($bucket, $allowedBuckets, true) ? $bucket : 'normal';
    }

    protected function resolveAttentionListMeta(string $bucket): array
    {
        switch ($bucket) {
            case 'vencido':
                return [
                    'title' => 'Trámites Vencidos',
                    'description' => 'Muestra trámites que ya rebasaron el umbral operativo y requieren atención inmediata.',
                    'badge_label' => 'Muy tardados',
                    'badge_tone' => 'vencido',
                ];

            default:
                return [
                    'title' => 'Trámites en Flujo Normal',
                    'description' => 'Listado operativo del nuevo flujo, excluyendo por defecto los trámites en atención prioritaria.',
                    'badge_label' => 'Operación normal',
                    'badge_tone' => 'normal',
                ];
        }
    }

    protected function buildAttentionListUrls(array $queryParams = []): array
    {
        $baseUrl = base_url('/deskapp/tramitesn/tramite');
        unset($queryParams['bucket']);

        $buildUrl = static function (?string $bucket) use ($baseUrl, $queryParams): string {
            $nextQuery = $queryParams;
            if (!empty($bucket) && $bucket !== 'normal') {
                $nextQuery['bucket'] = $bucket;
            }

            return empty($nextQuery)
                ? $baseUrl
                : $baseUrl . '?' . http_build_query($nextQuery);
        };

        return [
            'normal_url' => $buildUrl('normal'),
            'vencido_url' => $buildUrl('vencido'),
        ];
    }

    protected function buildAttentionListSummary(int $userId): array
    {
        $filterSql = get_tramite_filter_sql($userId);
        $db = ConfigDatabase::connect();

        $row = $db->table('tramite')
            ->select(sprintf(
                'SUM(CASE WHEN %1$s THEN 1 ELSE 0 END) AS vencido_total',
                $this->buildAttentionBucketSql('vencido')
            ), false)
            ->where($filterSql)
            ->where('tramite.created_at >=', '2026-01-01 00:00:00')
            ->get()
            ->getRowArray();

        return [
            'vencido_total' => (int) ($row['vencido_total'] ?? 0),
        ];
    }

    protected function resolveAttentionTrackedPresentation(array $daysData): array
    {
        $bucket = (string) ($daysData['bucket'] ?? 'normal');
        $days = (int) ($daysData['days'] ?? 0);
        $isLocal = ($daysData['scope'] ?? 'foraneo') === 'local';

        if ($bucket === 'vencido') {
            return ['label' => 'Vencido', 'class' => 'background-rojo'];
        }

        return ['label' => 'Normal', 'class' => 'background-verde'];
    }

    protected function resolveAttentionPresentation(array $daysData, int $statusId): array
    {
        if (!empty($daysData['tracked'])) {
            return $this->resolveAttentionTrackedPresentation($daysData);
        }

        if ($statusId === SGL_TRA_STATUS_PAGO_GESTOR) {
            return ['label' => 'Paso 4', 'class' => 'background-azul-claro'];
        }

        if ($statusId === SGL_TRA_STATUS_COBRO_CLIENTE) {
            return ['label' => 'Paso 5', 'class' => 'background-azul-cobro-cliente'];
        }

        if ($statusId === SGL_TRA_STATUS_CONCLUIDO) {
            return ['label' => 'Concluido', 'class' => 'background-azul'];
        }

        if ($statusId === SGL_TRA_STATUS_CANCELADO) {
            return ['label' => 'Cancelado', 'class' => 'background-gris'];
        }

        return ['label' => 'No aplica', 'class' => 'background-gris'];
    }

    private function renderTramiteList()
    {
        try {
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            $currentBucket = $this->resolveAttentionListBucket((string) $this->request->getGet('bucket'));
            [$roles, $perms] = $this->normalizeRolesPermsFromSession();
            $trackedStatusIds = $this->getAttentionTrackedStatusIds();
            $attentionSummary = $this->buildAttentionListSummary((int) $myid);
            $attentionUrls = $this->buildAttentionListUrls((array) $this->request->getGet());
            $attentionMeta = $this->resolveAttentionListMeta($currentBucket);

            $tramite_crud = $this->_getGroceryCrudEnterprise();

            $tramite_crud->setConfig('remember_state_upon_refresh', false);
            $tramite_crud->setConfig('remember_filters_upon_refresh', false);

            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);

            if ($currentBucket === 'normal') {
                $tramite_crud->where($this->buildAttentionBucketSql('normal'));
            } else {
                $tramite_crud->where($this->buildAttentionBucketSql($currentBucket));
            }

            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            $tramite_crud->unsetDeleteMultiple();
            $tramite_crud->unsetDelete();

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
            }

            if (can_edit_tramite($roles, $perms) || has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramitesn/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', $perms, $roles)){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites (Nuevo Flujo)');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');

            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2026-01-01 00:00:00']
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie',
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs('started_at', 'Desde Asignación');
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs('user_id', 'Ejecutivo');

            $tramite_crud->callbackColumn('started_at', function ($value, $row) use ($trackedStatusIds, $self) {
                $daysData = $self->classifyAttentionBucket(
                    (int) ($row->tra_status_id ?? 0),
                    isset($row->ent_municipio_id) ? (int) $row->ent_municipio_id : null,
                    $row->started_at ?? null,
                    $row->created_at ?? null,
                    $trackedStatusIds
                );
                $diasDiferencia = $daysData['days'];
                if ($diasDiferencia === null) {
                    return '<span class="background-gris">Sin fecha</span>';
                }

                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';
                $claseAzulClaro = 'background-azul-claro';
                $claseAzul = 'background-azul';
                $claseAzulCobroCliente = 'background-azul-cobro-cliente';

                if ($row->tra_status_id == SGL_TRA_STATUS_PAGO_GESTOR || $row->tra_status_id == SGL_TRA_STATUS_COBRO_CLIENTE) {
                    if ($row->tra_status_id == SGL_TRA_STATUS_PAGO_GESTOR) {
                        $clase = $claseAzulClaro;
                    }
                    $txt_generar_factura = '';

                    $traCobroClienteModel = new TraCobroClienteModel();
                    $registrosCobroCliente = $traCobroClienteModel->getByTramiteId($row->id);

                    $traEvidenciasFinalesModel = new TraEvidenciasFinalesModel();
                    $registrosEvidenciasFinales = $traEvidenciasFinalesModel->getByTramiteId($row->id);

                    if (count($registrosCobroCliente) > 0 || count($registrosEvidenciasFinales) > 0) {
                        $txt_generar_factura = 'Facturar';
                    }

                    if ($row->tra_status_id == SGL_TRA_STATUS_COBRO_CLIENTE) {
                        $clase = $claseAzulCobroCliente;
                        return '<span class="' . $clase . '">' . $txt_generar_factura . '</span>';
                    }
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CANCELADO) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CONCLUIDO) {
                    $clase = $claseAzul;
                } else {
                    $clase = $self->resolveAttentionTrackedPresentation($daysData)['class'];
                }

                $arrFilter = [SGL_TRA_STATUS_CONCLUIDO, SGL_TRA_STATUS_CANCELADO, SGL_TRA_STATUS_PAGO_GESTOR, SGL_TRA_STATUS_COBRO_CLIENTE];
                if (!in_array($row->tra_status_id, $arrFilter, true)) {
                    if ($daysData['tracked']) {
                        $label = $daysData['bucket'] === 'vencido' ? 'Vencido' : 'Normal';

                        return '<span class="' . $clase . '">' . $label . ' · ' . $diasDiferencia . ' días</span>';
                    }

                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }

                return '<span class="' . $clase . '"></span>';
            });
            $tramite_crud->callbackColumn('cobro_status_id', function ($value, $row) use ($trackedStatusIds, $self) {
                $daysData = $self->classifyAttentionBucket(
                    (int) ($row->tra_status_id ?? 0),
                    isset($row->ent_municipio_id) ? (int) $row->ent_municipio_id : null,
                    $row->started_at ?? null,
                    $row->created_at ?? null,
                    $trackedStatusIds
                );
                $presentation = $self->resolveAttentionPresentation($daysData, (int) ($row->tra_status_id ?? 0));

                return '<span class="' . esc($presentation['class']) . '">' . esc($presentation['label']) . '</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie',
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]);

            $tramite_crud->displayAs('created_at', 'Creación');

            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');
            $tramite_crud->displayAs('cobro_status_id', 'Seguimiento');

            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');

            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');
            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');
            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_salida = $tramite_crud->render();

            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['title'] = $attentionMeta['title'];
            $salida_total['description'] = $attentionMeta['description'];
            $salida_total['header_badge_label'] = $attentionMeta['badge_label'] ?? null;
            $salida_total['header_badge_tone'] = $attentionMeta['badge_tone'] ?? 'normal';
            $salida_total['pre_output_html'] = view('deskapp/extra-pages/tramites_attention_toolbar', array_merge($attentionUrls, [
                'summary' => $attentionSummary,
                'mode' => $currentBucket,
            ]));
            helper(['permissions']);
            [$rolesAcl, $permsAcl] = session_roles_perms($session ?? session());
            $salida_total['insert_button_url'] = can_create_tramite($rolesAcl, $permsAcl) ? '/public/deskapp/tramites/add' : '';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    private function isTramiteLocked(int $tramiteId): bool
    {
        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return false;
        }
        $db = \Config\Database::connect();
        $row = $db->table('tramite')->select('tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        $statusId = (int) ($row['tra_status_id'] ?? 0);
        return $this->isLockedStatusId($statusId);
    }

    private function normalizeRolesPermsFromSession(): array
    {
        helper(['permissions']);
        $session = session();
        return session_roles_perms($session);
    }

    private function denyJson(int $statusCode, string $message)
    {
        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'message' => $message,
            'csrfHash' => csrf_hash(),
        ]);
    }

    protected function getTramiteRowWithStatuses(int $tramiteId): array
    {
        $db = \Config\Database::connect();

        return $db->table('tramite')
            ->select('id, tra_status_id, reembolso_status_id, cobro_status_id')
            ->where('id', $tramiteId)
            ->get(1)
            ->getRowArray() ?? [];
    }

    protected function requireJsonTenantAccess(int $tramiteId, int $userId, array $roles)
    {
        return acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true);
    }

    protected function getTramiteRowWithFolioAndStatuses(int $tramiteId): array
    {
        $db = \Config\Database::connect();

        return $db->table('tramite')
            ->select('id, folio, tra_status_id, reembolso_status_id, cobro_status_id')
            ->where('id', $tramiteId)
            ->get(1)
            ->getRowArray() ?? [];
    }

    private function requireCanEditTramiteJson(array $roles, array $perms)
    {
        if (!can_edit_tramite($roles, $perms)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        return null;
    }

    private function getPaidPagoGestorStatusIds(?array $options = null): array
    {
        $statusOptions = $options;
        if ($statusOptions === null) {
            try {
                $statusOptions = (new PagoGestorStatusModel($this->_getDbData()))->getPagoGestorStatusOptions();
            } catch (\Throwable $e) {
                $statusOptions = [];
            }
        }

        $paidIds = [];
        foreach ($statusOptions as $statusId => $label) {
            if ($this->isPaidLabel((string) $label)) {
                $paidIds[] = (int) $statusId;
            }
        }

        return array_values(array_unique(array_filter($paidIds)));
    }

    private function isReadyForCobroCliente(array $tramiteRow, ?array $pagoGestorStatusOptions = null): bool
    {
        $paidIds = $this->getPaidPagoGestorStatusIds($pagoGestorStatusOptions);
        if (empty($paidIds)) {
            return false;
        }

        return in_array((int) ($tramiteRow['pago_gestor_st_id'] ?? 0), $paidIds, true)
            && (int) ($tramiteRow['cobrar_cliente'] ?? 0) === 1;
    }

    private function syncCobroClienteStatusFromPagoGestor($db, int $tramiteId, ?int $forcedPagoGestorStatusId = null): int
    {
        if ($tramiteId <= 0) {
            return 0;
        }

        $tramiteRow = $db->table('tramite')
            ->select('tra_status_id, pago_gestor_st_id, cobrar_cliente')
            ->where('id', $tramiteId)
            ->get(1)
            ->getRowArray();

        if (empty($tramiteRow)) {
            return 0;
        }

        if ($forcedPagoGestorStatusId !== null) {
            $tramiteRow['pago_gestor_st_id'] = $forcedPagoGestorStatusId;
        }

        $targetStatus = $this->isReadyForCobroCliente($tramiteRow)
            ? SGL_TRA_STATUS_COBRO_CLIENTE
            : SGL_TRA_STATUS_PAGO_GESTOR;
        $currentStatus = (int) ($tramiteRow['tra_status_id'] ?? 0);

        if ($currentStatus !== $targetStatus) {
            $this->updateTramiteStatus($tramiteId, $targetStatus);
        }

        return $targetStatus;
    }

    private function syncCobroClienteStatusAfterPagoGestorResponse($response, int $tramiteId): void
    {
        if ($tramiteId <= 0 || !is_object($response) || !method_exists($response, 'getStatusCode')) {
            return;
        }

        if ((int) $response->getStatusCode() >= 400) {
            return;
        }

        $payload = json_decode((string) $response->getBody(), true);
        if (is_array($payload) && array_key_exists('success', $payload) && !$payload['success']) {
            return;
        }

        $db = \Config\Database::connect();
        $this->updateCobrarClienteFlagTramitesn($db, $tramiteId);
        $this->syncCobroClienteStatusFromPagoGestor($db, $tramiteId);
    }

    /**
     * En pasos 1–3, una vez autorizado (status 23+), queda solo lectura.
     * Override por permiso (Super Admin pasa por bypass dentro de has_permission()).
     */
    private function requireNotApprovedForSteps123Json(int $traStatusId, array $roles, array $perms)
    {
        helper(['tramite_status']);

        if (has_permission('override_tramite_approved_lock', $perms, $roles)) {
            return null;
        }

        if (tramite_is_aprobado_por_status((int) $traStatusId)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        return null;
    }

    private function resolveAdvancedStepView(int $statusId, array $roles, array $perms): ?string
    {
        if (
            $statusId === SGL_TRA_STATUS_PAGO_GESTOR
            && has_permission('section_pago_gestor', $perms, $roles)
        ) {
			return 'deskapp/extra-pages/tramite_update_view_evidencias_finales';
        }

        if (
            $statusId === SGL_TRA_STATUS_COBRO_CLIENTE
            && has_permission('list_cobro_cliente', $perms, $roles)
        ) {
			return 'deskapp/extra-pages/tramite_cobro_cliente_view';
        }

        return null;
    }

    public function search()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }
        $userId = (int) ($session->get('id') ?? 0);

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();
        $canSearch = has_permission('search_tramite', $perms, $roles);
        if (!$canSearch) {
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para buscar trámites.');
        }

        if (strtolower((string) $this->request->getMethod()) !== 'post') {
            return view('deskapp/tramitesn/search', [
                'session' => $session,
                'title' => 'Buscar Trámite',
            ]);
        }

        $tramiteId = (int) ($this->request->getPost('tramite_id') ?? 0);
        $folio = trim((string) ($this->request->getPost('folio') ?? ''));
        $folio = strtoupper($folio);
        $contrato = trim((string) ($this->request->getPost('contrato') ?? ''));
        $contrato = strtoupper($contrato);

        if ($tramiteId <= 0 && $folio === '' && $contrato === '') {
            return redirect()->to('/deskapp/tramitesn/search')->with('error', 'Ingresa el ID del trámite, el folio o el contrato.');
        }

        $db = \Config\Database::connect();
        $tramiteRow = null;

        if ($tramiteId > 0) {
            $tramiteRow = $db->table('tramite')->select('id, folio')->where('id', $tramiteId)->get()->getRowArray();
        } elseif ($folio !== '') {
            $tramiteRow = $db->table('tramite')->select('id, folio')->where('folio', $folio)->get()->getRowArray();
        } else {
            $tramiteRow = $db->table('tramite')->select('id, folio')->where('contrato', $contrato)->get()->getRowArray();
        }

        $resolvedId = (int) ($tramiteRow['id'] ?? 0);
        if ($resolvedId <= 0) {
            return redirect()->to('/deskapp/tramitesn/search')->with('error', 'El trámite no existe.');
        }

        if ($resp = acl_require_tramite_tenant_access($resolvedId, $userId, $roles, 'El ejecutivo no tiene acceso a ese recurso.', '/deskapp/tramitesn/search', 403, false)) {
            log_unauthorized_access_attempt('tramite_search', $resolvedId);
            return $resp;
        }

        return redirect()->to('/deskapp/tramitesn/update/' . $resolvedId . '?from=search');
    }

    public function services($tramiteId)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return acl_deny('ID inválido.', 400, null, true);
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }

        if (!can_edit_tramite($roles, $perms) && !has_permission('read_tramite', $perms, $roles)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $db = \Config\Database::connect();
        $query = $db->table('tra_tramite_asociado')
            ->select('tra_tramite_asociado.id, tra_tramite_asociado.tra_tipos_id, tra_tipos.tipo_tramite')
            ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id')
            ->where('tra_tramite_asociado.tramite_id', $tramiteId)
            ->orderBy('tra_tramite_asociado.id', 'ASC')
            ->get();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $query->getResultArray(),
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function services_add()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard', 'tramite_status']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $traTiposId = (int) $this->request->getPost('tra_tipos_id');
        if ($tramiteId <= 0 || $traTiposId <= 0) {
            return acl_deny('Datos insuficientes.', 400, null, true);
        }

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_datos_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }

        // No permitir ligar el tipo principal como asociado
        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')->select('id, tra_tipos_id, tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        if (!$tramiteRow) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Trámite no encontrado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if ($resp = $this->requireNotApprovedForSteps123Json((int) ($tramiteRow['tra_status_id'] ?? 0), $roles, $perms)) {
            return $resp;
        }

        if ($this->isLockedStatusId((int) ($tramiteRow['tra_status_id'] ?? 0))) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }
        if ((int) ($tramiteRow['tra_tipos_id'] ?? 0) === $traTiposId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No puedes ligar el tipo de trámite principal como asociado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $model = new TraTramiteAsociadoModel();
        $insertId = $model->saveService($tramiteId, $traTiposId);
        if ($insertId === false) {
            return $this->response->setJSON([
                'status' => 'exists',
                'message' => 'Este tipo de trámite ya está asociado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo de trámite agregado correctamente.',
            'asociado_id' => $insertId,
            'tra_tipos_id' => $traTiposId,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function services_update()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard', 'tramite_status']);
        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_datos_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $asociadoId = (int) $this->request->getPost('asociado_id');
        $nuevoTipoId = (int) $this->request->getPost('tra_tipos_id');
        if ($tramiteId <= 0 || $asociadoId <= 0 || $nuevoTipoId <= 0) {
            return $this->denyJson(400, 'Datos insuficientes.');
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }
        if (!has_permission('editar_tramite_asociado', $perms, $roles)) {
            return $this->denyJson(403, 'No tienes permisos para cambiar tipos asociados.');
        }

        $db = \Config\Database::connect();

        // No permitir cambiar un asociado al tipo principal actual
        $tramiteRow = $db->table('tramite')->select('id, tra_tipos_id, tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        if (!$tramiteRow) {
            return $this->denyJson(404, 'Trámite no encontrado.');
        }

        if ($resp = $this->requireNotApprovedForSteps123Json((int) ($tramiteRow['tra_status_id'] ?? 0), $roles, $perms)) {
            return $resp;
        }

        if ($this->isLockedStatusId((int) ($tramiteRow['tra_status_id'] ?? 0))) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }
        if ((int) ($tramiteRow['tra_tipos_id'] ?? 0) === $nuevoTipoId) {
            return $this->denyJson(400, 'No puedes asignar el tipo principal como tipo asociado.');
        }

        $row = $db->table('tra_tramite_asociado')
            ->select('id, tramite_id, tra_tipos_id')
            ->where('id', $asociadoId)
            ->get()
            ->getRowArray();
        if (!$row || (int) $row['tramite_id'] !== $tramiteId) {
            return $this->denyJson(404, 'Asociación no encontrada.');
        }

        $exists = $db->table('tra_tramite_asociado')
            ->where('tramite_id', $tramiteId)
            ->where('tra_tipos_id', $nuevoTipoId)
            ->countAllResults();
        if ($exists > 0) {
            return $this->response->setJSON([
                'status' => 'exists',
                'message' => 'Ese tipo ya está ligado al trámite.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db->table('tra_tramite_asociado')->where('id', $asociadoId)->update([
            'tra_tipos_id' => $nuevoTipoId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $tipoLabelRow = $db->table('tra_tipos')->select('tipo_tramite')->where('id', $nuevoTipoId)->get()->getRowArray();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo asociado actualizado.',
            'asociado_id' => $asociadoId,
            'tra_tipos_id' => $nuevoTipoId,
            'label' => $tipoLabelRow['tipo_tramite'] ?? null,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function services_delete()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard', 'tramite_status']);
        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_datos_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $asociadoId = (int) $this->request->getPost('asociado_id');
        if ($tramiteId <= 0 || $asociadoId <= 0) {
            return $this->denyJson(400, 'Datos insuficientes.');
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }
        if (!has_permission('delete_tramite_asociado', $perms, $roles)) {
            return $this->denyJson(403, 'No tienes permisos para eliminar tipos asociados.');
        }

        if ($this->isTramiteLocked($tramiteId)) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')->select('tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        if ($resp = $this->requireNotApprovedForSteps123Json((int) ($tramiteRow['tra_status_id'] ?? 0), $roles, $perms)) {
            return $resp;
        }
        $row = $db->table('tra_tramite_asociado')
            ->select('id, tramite_id')
            ->where('id', $asociadoId)
            ->get()
            ->getRowArray();
        if (!$row || (int) $row['tramite_id'] !== $tramiteId) {
            return $this->denyJson(404, 'Asociación no encontrada.');
        }

        $db->table('tra_tramite_asociado')->where('id', $asociadoId)->delete();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo asociado eliminado.',
            'asociado_id' => $asociadoId,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function principal_update_tipo()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard', 'tramite_status']);
        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_datos_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $nuevoTipoId = (int) $this->request->getPost('tra_tipos_id');
        if ($tramiteId <= 0 || $nuevoTipoId <= 0) {
            return $this->denyJson(400, 'Datos insuficientes.');
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }
        if (!has_permission('editar_tramite_principal', $perms, $roles)) {
            return $this->denyJson(403, 'No tienes permisos para editar el trámite principal.');
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')->select('id, tra_tipos_id, tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        if (!$tramiteRow) {
            return $this->denyJson(404, 'Trámite no encontrado.');
        }

        if ($resp = $this->requireNotApprovedForSteps123Json((int) ($tramiteRow['tra_status_id'] ?? 0), $roles, $perms)) {
            return $resp;
        }

        if ($this->isLockedStatusId((int) ($tramiteRow['tra_status_id'] ?? 0))) {
            return $this->denyJson(409, 'El trámite está concluido o cancelado.');
        }

        $currentTipoId = (int) ($tramiteRow['tra_tipos_id'] ?? 0);
        if ($currentTipoId === $nuevoTipoId) {
            $tipoLabelRow = $db->table('tra_tipos')->select('tipo_tramite')->where('id', $nuevoTipoId)->get()->getRowArray();
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Sin cambios.',
                'tra_tipos_id' => $nuevoTipoId,
                'old_tipo_id' => $currentTipoId,
                'label' => $tipoLabelRow['tipo_tramite'] ?? null,
                'csrfHash' => csrf_hash(),
            ]);
        }

        $db->table('tramite')->where('id', $tramiteId)->update([
            'tra_tipos_id' => $nuevoTipoId,
        ]);

        $principalAssoc = null;
        if ($currentTipoId > 0) {
            $principalAssoc = $db->table('tra_tramite_asociado')
                ->select('id')
                ->where('tramite_id', $tramiteId)
                ->where('tra_tipos_id', $currentTipoId)
                ->get()
                ->getRowArray();
        }

        $nuevoAssoc = $db->table('tra_tramite_asociado')
            ->select('id')
            ->where('tramite_id', $tramiteId)
            ->where('tra_tipos_id', $nuevoTipoId)
            ->get()
            ->getRowArray();

        $assocAction = 'none';
        $principalAssocId = !empty($nuevoAssoc) ? (int) $nuevoAssoc['id'] : null;

        if (!empty($nuevoAssoc) && !empty($principalAssoc) && (int) $nuevoAssoc['id'] !== (int) $principalAssoc['id']) {
            $db->table('tra_tramite_asociado')->where('id', (int) $principalAssoc['id'])->delete();
            $assocAction = 'deleted_old';
        } elseif (!empty($principalAssoc) && empty($nuevoAssoc)) {
            $db->table('tra_tramite_asociado')->where('id', (int) $principalAssoc['id'])->update([
                'tra_tipos_id' => $nuevoTipoId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $principalAssocId = (int) $principalAssoc['id'];
            $assocAction = 'updated';
        } elseif (empty($principalAssoc) && empty($nuevoAssoc)) {
            $tramiteAsociadoModel = new TraTramiteAsociadoModel();
            $principalAssocId = $tramiteAsociadoModel->saveService($tramiteId, $nuevoTipoId);
            $assocAction = 'inserted';
        } else {
            $assocAction = 'kept_existing';
        }

        $tipoLabelRow = $db->table('tra_tipos')->select('tipo_tramite')->where('id', $nuevoTipoId)->get()->getRowArray();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tipo principal actualizado.',
            'tra_tipos_id' => $nuevoTipoId,
            'old_tipo_id' => $currentTipoId,
            'asociado_id' => $principalAssocId,
            'assoc_action' => $assocAction,
            'label' => $tipoLabelRow['tipo_tramite'] ?? null,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function get_service_costs_by_tramite($tramiteId)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return acl_json_empty(400);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $query = $db->table('tra_tramite_asociado')
            ->select('tra_tramite_asociado.id, tra_tramite_asociado.costo_tramite, tra_tipos.tipo_tramite')
            ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id')
            ->where('tra_tramite_asociado.tramite_id', $tramiteId)
            ->get();

        return $this->response->setJSON($query->getResultArray());
    }

    public function update_service_cost()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $id = $this->request->getPost('id');
        $costo_tramite = $this->request->getPost('costo_tramite');

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID de servicio inválido.'
            ]);
        }

        if ($costo_tramite !== '' && $costo_tramite !== null && !is_numeric($costo_tramite)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El costo debe ser un valor numérico válido.'
            ]);
        }

        if ($costo_tramite === '' || $costo_tramite === null) {
            $costo_tramite = null;
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tra_tramite_asociado');

            $existingRecord = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingRecord) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'El servicio asociado no existe.'
                ]);
            }

            $tramiteId = (int) ($existingRecord['tramite_id'] ?? 0);
            if ($tramiteId <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Servicio inválido.'
                ]);
            }

            // Mutación: requiere permiso de edición del trámite.
            if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
                return $resp;
            }

            if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($this->isTramiteLocked($tramiteId)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'status' => 'error',
                    'message' => 'El trámite está concluido o cancelado.'
                ]);
            }

            $data = [
                'costo_tramite' => $costo_tramite,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $builder->where('id', $id);
            $updateResult = $builder->update($data);
            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el costo del servicio.');
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Costo actualizado correctamente.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_service_cost: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    public function update_save()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard', 'tramite_status']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_datos_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'folio' => 'required',
            'contrato' => 'required',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');

            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El trámite no existe.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            if ($resp = $this->requireNotApprovedForSteps123Json((int) ($existingTramite['tra_status_id'] ?? 0), $roles, $perms)) {
                return $resp;
            }

            if ($this->isLockedStatusId((int) ($existingTramite['tra_status_id'] ?? 0))) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $data = $this->request->getPost();
            $csrfName = csrf_token();
            if (isset($data[$csrfName])) {
                unset($data[$csrfName]);
            }
            $data['user_id'] = $myid;

            $currentStep = isset($data['current_step']) ? (int) $data['current_step'] : 0;
            unset($data['current_step']);

            if (array_key_exists('gestor_id', $data) && ($data['gestor_id'] === '' || $data['gestor_id'] === 'null')) {
                unset($data['gestor_id']);
            }
            if (array_key_exists('empresa_gestora_id', $data) && ($data['empresa_gestora_id'] === '' || $data['empresa_gestora_id'] === 'null')) {
                unset($data['empresa_gestora_id']);
            }

            if ($currentStep > 0 && $currentStep < 3) {
                foreach (['derechos_tramite', 'derechos_pago_sitio', 'derechos_vigencia', 'derechos_revol_cliente', 'derechos_refer_banc'] as $field) {
                    if (array_key_exists($field, $data)) {
                        unset($data[$field]);
                    }
                }
            }

            $duplicateSerie = trim((string) ($data['serie'] ?? ($existingTramite['serie'] ?? '')));
            $existingSerie = trim((string) ($existingTramite['serie'] ?? ''));
            $duplicateTipoId = (int) ($data['tra_tipos_id'] ?? ($existingTramite['tra_tipos_id'] ?? 0));
            $existingTipoId = (int) ($existingTramite['tra_tipos_id'] ?? 0);
            $shouldValidateDuplicates = (
                array_key_exists('serie', $data)
                && $duplicateSerie !== $existingSerie
            ) || (
                array_key_exists('tra_tipos_id', $data)
                && $duplicateTipoId !== $existingTipoId
            );

            if ($shouldValidateDuplicates && $duplicateSerie !== '' && $duplicateTipoId > 0) {
                    $duplicateBuilder = $db->table('tramite');
                    $duplicateBuilder->where('tra_tipos_id', $duplicateTipoId);
                    $duplicateBuilder->where('serie', $duplicateSerie);
                    $duplicateBuilder->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 year')));
                    $duplicateBuilder->where('id !=', $id);

                    $duplicateExists = !empty($duplicateBuilder->get()->getRowArray());
                    if ($duplicateExists) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Ya existe un tramite con el mismo tipo y serie dentro del ultimo ano.',
                            'csrfHash' => csrf_hash(),
                        ]);
                    }
            }

            $changes = [];
            try {
                $changes = compare_tramite_data($existingTramite, $data);
            } catch (\Throwable $e) {
                log_message('error', 'Error en compare_tramite_data (Tramitesn::update_save): ' . $e->getMessage());
            }

            $logFile = WRITEPATH . 'logs/audit_debug.log';
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'tramite_id' => $id,
                'user_id' => $myid,
                'post_fields' => array_keys($data),
                'existing_fields' => array_keys($existingTramite),
                'changes_detected' => count($changes),
                'changes' => $changes,
            ];
            file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

            $builder->where('id', $id);
            $updateResult = $builder->update($data);
            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el trámite.');
            }

            $targetStatus = null;
            $hasGestor = !empty($data['empresa_gestora_id']) && !empty($data['gestor_id']);
            $hasDerechosBase = !empty($data['derechos_tramite'])
                && !empty($data['derechos_pago_sitio'])
                && !empty($data['derechos_vigencia']);
            $hasDerechosBanc = !empty($data['derechos_revol_cliente'])
                && !empty($data['derechos_refer_banc']);

            if ($hasGestor) {
                $targetStatus = SGL_TRA_STATUS_PAGO_DERECHOS_COTIZACION;
            }
            if ($hasDerechosBase) {
                $targetStatus = SGL_TRA_STATUS_PAGO_DERECHOS_LINEA_CAPTURA;
            }
            if ($hasDerechosBanc) {
                $targetStatus = SGL_TRA_STATUS_PAGO_DERECHOS_DOCUMENTOS;
            }

            $statusUpdatedTo = (int) ($existingTramite['tra_status_id'] ?? 0);
            if ($targetStatus !== null) {
                $statusResult = $this->updateTramiteStatus($id, $targetStatus);
                if (!empty($statusResult['success'])) {
                    $statusUpdatedTo = $targetStatus;
                }
            }

            $principalTipoId = (int) ($existingTramite['tra_tipos_id'] ?? 0);
            $asociadoFields = [
                'derechos_tramite',
                'derechos_pago_sitio',
                'derechos_vigencia',
                'derechos_revol_cliente',
                'derechos_refer_banc',
            ];
            $asociadoData = [];
            foreach ($asociadoFields as $field) {
                if (array_key_exists($field, $data)) {
                    $asociadoData[$field] = $data[$field];
                }
            }
            if (!empty($asociadoData)) {
                $asociadoData['updated_at'] = date('Y-m-d H:i:s');
                $asociadoBuilder = $db->table('tra_tramite_asociado');
                $asociadoBuilder->where('tramite_id', (int) $id);
                if ($principalTipoId > 0) {
                    $asociadoBuilder->where('tra_tipos_id !=', $principalTipoId);
                }
                $asociadoBuilder->update($asociadoData);
            }

            $folio = $data['folio'] ?? null;
            $this->recordUpdateSaveSideEffects((int) $id, $folio, (int) $myid, (int) $statusUpdatedTo, $changes);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id,
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_save: ' . $e->getMessage());
            log_message('error', 'Trace Tramitesn::update_save: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function update_gestor_save()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard', 'tramite_status']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_asigna_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'empresa_gestora_id' => 'required',
            'gestor_id' => 'required',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');

            $tramiteBase = $builder->where('id', $id)->get()->getRowArray();
            if (!$tramiteBase) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El trámite no existe.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            if ($resp = $this->requireNotApprovedForSteps123Json((int) ($tramiteBase['tra_status_id'] ?? 0), $roles, $perms)) {
                return $resp;
            }

            if ($this->isLockedStatusId((int) ($tramiteBase['tra_status_id'] ?? 0))) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $this->updateTramiteStatus($id, SGL_TRA_STATUS_PAGO_DERECHOS_COTIZACION);

            $data = $this->request->getPost();
            $csrfName = csrf_token();
            if (isset($data[$csrfName])) {
                unset($data[$csrfName]);
            }
            if (isset($data['current_step'])) {
                unset($data['current_step']);
            }

            if (empty($tramiteBase['started_at'])) {
                $data['started_at'] = date('Y-m-d H:i:s');
            }

            if (isset($data['gestor_name'])) {
                unset($data['gestor_name']);
            }

            $changes = [];
            try {
                $changes = compare_tramite_data($tramiteBase, $data);
            } catch (\Throwable $e) {
                log_message('error', 'Error en compare_tramite_data (Tramitesn::update_gestor_save): ' . $e->getMessage());
            }

            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo asignar el gestor.');
            }

            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->buildBitacoraChanges($changes);
            $insert_bitacora = [
                'id' => null,
                'tipo' => 'update',
                'origen' => 'tramite',
                'tramite_id' => (int) $id,
                'cambios' => json_encode($diferencias),
                'user_id' => (int) $myid,
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                'tramite_id' => (int) $id,
                'user_id' => (int) $myid,
                'tra_status_id' => SGL_TRA_STATUS_DCTOS_COMPLETOS,
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Asignacion de Gestor',
                    'form_step' => 2,
                    'form_section' => 'update_gestor_save',
                ]);

                if (isset($changes['gestor_id'])) {
                    $db = \Config\Database::connect();
                    $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                    $gestor = $db->table('ges_gestor')->select('nombre')->where('id', $data['gestor_id'] ?? 0)->get()->getRowArray();

                    $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                    $gestorNombre = $gestor['nombre'] ?? 'Gestor';
                    notify_gestor_asignado($id, $folio, $gestorNombre, $myid);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El Gestor se asigno correctamente.',
                'redirect' => '/deskapp/tramitesn/update/' . $id,
                'csrfHash' => csrf_hash(),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_gestor_save: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al asignar gestor: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function update_derechos_save()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard', 'tramite_status']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'derechos_tramite' => 'required',
            'derechos_pago_sitio' => 'required',
            'derechos_vigencia' => 'required',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');

            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El trámite no existe.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            if ($resp = $this->requireNotApprovedForSteps123Json((int) ($existingTramite['tra_status_id'] ?? 0), $roles, $perms)) {
                return $resp;
            }

            if ($this->isLockedStatusId((int) ($existingTramite['tra_status_id'] ?? 0))) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            $data = $this->request->getPost();
            $csrfName = csrf_token();
            if (isset($data[$csrfName])) {
                unset($data[$csrfName]);
            }

            $changes = [];

            if (isset($data['current_step'])) {
                unset($data['current_step']);
            }

            try {
                $changes = compare_tramite_data($existingTramite, $data);
            } catch (\Throwable $e) {
                log_message('error', 'Error en compare_tramite_data (Tramitesn::update_derechos_save): ' . $e->getMessage());
            }

            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo guardar los derechos.');
            }

            $hasDerechosBase = !empty($data['derechos_tramite'])
                && !empty($data['derechos_pago_sitio'])
                && !empty($data['derechos_vigencia']);
            $hasDerechosBanc = !empty($data['derechos_revol_cliente'])
                && !empty($data['derechos_refer_banc']);

            if ($hasDerechosBase) {
                $this->updateTramiteStatus($id, SGL_TRA_STATUS_PAGO_DERECHOS_LINEA_CAPTURA);
            }
            if ($hasDerechosBanc) {
                $this->updateTramiteStatus($id, SGL_TRA_STATUS_PAGO_DERECHOS_DOCUMENTOS);
            }

            $principalTipoId = (int) ($existingTramite['tra_tipos_id'] ?? 0);
            $asociadoFields = [
                'derechos_tramite',
                'derechos_pago_sitio',
                'derechos_vigencia',
                'derechos_revol_cliente',
                'derechos_refer_banc',
            ];
            $asociadoData = [];
            foreach ($asociadoFields as $field) {
                if (array_key_exists($field, $data)) {
                    $asociadoData[$field] = $data[$field];
                }
            }
            if (!empty($asociadoData)) {
                $asociadoData['updated_at'] = date('Y-m-d H:i:s');
                $asociadoBuilder = $db->table('tra_tramite_asociado');
                $asociadoBuilder->where('tramite_id', (int) $id);
                if ($principalTipoId > 0) {
                    $asociadoBuilder->where('tra_tipos_id !=', $principalTipoId);
                }
                $asociadoBuilder->update($asociadoData);
            }

            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->buildBitacoraChanges($changes);
            $insert_bitacora = [
                'id' => null,
                'tipo' => 'update',
                'origen' => 'tramite',
                'tramite_id' => (int) $id,
                'cambios' => json_encode($diferencias),
                'user_id' => (int) $myid,
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                'tramite_id' => (int) $id,
                'user_id' => (int) $myid,
                'tra_status_id' => SGL_TRA_STATUS_DCTOS_COMPLETOS,
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Pago de Derechos',
                    'form_step' => 3,
                    'form_section' => 'update_derechos_save',
                ]);

                $db = \Config\Database::connect();
                $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                notify_tramite_actualizado($id, $folio, 'Pago de Derechos actualizado', $myid);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardo correctamente.',
                'redirect' => '/deskapp/tramitesn/update/' . $id,
                'csrfHash' => csrf_hash(),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_derechos_save: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar derechos: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function update_pago_gestor()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireCanEditTramiteJson($roles, $perms)) {
            return $resp;
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $existingTramite = $builder->where('id', $id)->get()->getRowArray();
        if (!$existingTramite) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El trámite no existe.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $traStatusId = (int) ($existingTramite['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($existingTramite['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($existingTramite['cobro_status_id'] ?? 0);
        $canKeepStep4Editable = $this->canKeepStep4Editable(
            $reembolsoStatusId,
            (int) ($existingTramite['pago_gestor_st_id'] ?? 0),
            null,
            (string) ($existingTramite['status_doctos_gestor'] ?? '')
        );
        if ($this->isLockedStatusId($traStatusId)) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'El trámite está en modo de solo lectura.',
                'csrfHash' => csrf_hash(),
            ]);
        }
        if (!$canKeepStep4Editable && !puede_editar_modulo($roles, $traStatusId, 'editar_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'reembolso_status_id' => 'required|integer',
            'status_doctos_gestor' => 'required|in_list[en proceso,entregados]',
        ]);

        if ($validation->withRequest($this->request)->run() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en la validación de datos.',
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        $data = $this->request->getPost();
        $csrfName = csrf_token();
        if (isset($data[$csrfName])) {
            unset($data[$csrfName]);
        }

        $data['user_id'] = $myid;

        $camposAEliminar = [
            'gestor_total_pago_hidden',
            'reembolso_status_id_hidden',
            'impuesto_gestoria_hidden',
            'gestoria_comision_hidden',
            'gestor_name',
            'gestor_id',
        ];
        foreach ($camposAEliminar as $campo) {
            if (isset($data[$campo])) {
                unset($data[$campo]);
            }
        }

        $camposNumericos = [
            'costo_tramite',
            'deposito_gestor',
            'col_a_favor',
            'impuesto_gestoria',
            'gestoria_comision',
            'costo_paqueteria',
            'gestor_total_pago',
        ];
        foreach ($camposNumericos as $campo) {
            if (isset($data[$campo]) && $data[$campo] === '') {
                $data[$campo] = null;
            }
        }

        // Saldo financiero inteligente: Pago total vs Deposito a gestor.
        $toMoney = static function ($value): float {
            if ($value === null || $value === '') {
                return 0.0;
            }
            return round((float) $value, 2);
        };

        $costoTramite = $toMoney($data['costo_tramite'] ?? ($existingTramite['costo_tramite'] ?? 0));
        $impuestoGestoria = $toMoney($data['impuesto_gestoria'] ?? ($existingTramite['impuesto_gestoria'] ?? 0));
        $gestoriaComision = $toMoney($data['gestoria_comision'] ?? ($existingTramite['gestoria_comision'] ?? 0));
        $costoPaqueteria = $toMoney($data['costo_paqueteria'] ?? ($existingTramite['costo_paqueteria'] ?? 0));

        $totalPagoCalculado = round($costoTramite + $impuestoGestoria + $gestoriaComision + $costoPaqueteria, 2);
        $depositoGestor = $toMoney($data['deposito_gestor'] ?? ($existingTramite['deposito_gestor'] ?? 0));
        $saldoCalculado = round($totalPagoCalculado - $depositoGestor, 2);

        $data['gestor_total_pago'] = number_format($totalPagoCalculado, 2, '.', '');
        $data['col_a_favor'] = number_format($saldoCalculado, 2, '.', '');
        $data['reembolso_status_id'] = abs($saldoCalculado) > 0.0001 ? 22 : 24;

        try {
            try {
                $changes = compare_tramite_data($existingTramite, $data);
            } catch (\Throwable $e) {
                $changes = [];
                log_message('error', 'Error en compare_tramite_data (Tramitesn::update_pago_gestor): ' . $e->getMessage());
            }

            $builder->where('id', $id);
            $updateResult = $builder->update($data);
            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el trámite.');
            }

            $this->updateCobrarClienteFlagTramitesn($db, $id);
            $targetStatus = $this->syncCobroClienteStatusFromPagoGestor(
                $db,
                (int) $id,
                isset($data['pago_gestor_st_id']) ? (int) $data['pago_gestor_st_id'] : null
            );

            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $bitacoraModel->insert([
                'id' => null,
                'tipo' => 'update',
                'origen' => 'tramite',
                'tramite_id' => (int) $id,
                'cambios' => json_encode($this->buildBitacoraChanges($changes)),
                'user_id' => (int) $myid,
            ], 'bitacora');

            $traUserLog = new TraUserLogModel($db2);
            $traUserLog->insert([
                'tramite_id' => (int) $id,
                'user_id' => (int) $myid,
                'tra_status_id' => $targetStatus,
            ], 'tra_user_log');

            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Pago a Gestor',
                    'form_step' => 4,
                    'form_section' => 'update_pago_gestor',
                ]);
            }

            $redirectUrl = '/deskapp/tramitesn/update/' . $id;
            if ($targetStatus !== SGL_TRA_STATUS_COBRO_CLIENTE && has_permission('section_pago_gestor', $perms, $roles)) {
                $redirectUrl = '/deskapp/tramitesn/ver_seccion_pago_gestor/' . $id;
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pago a gestor guardado correctamente.',
                'redirect' => $redirectUrl,
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error en Tramitesn::update_pago_gestor: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar el pago a gestor: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }

    public function upload_pago_gestor($id = null)
    {
        $response = parent::upload_pago_gestor();
        $tramiteId = (int) ($id ?? $this->request->getUri()->getSegment(4));
        $this->syncCobroClienteStatusAfterPagoGestorResponse($response, $tramiteId);
        return $response;
    }

    public function delete_pago_gestor()
    {
        $tramiteId = (int) $this->request->getPost('tramite_id');
        $response = parent::delete_pago_gestor();
        $this->syncCobroClienteStatusAfterPagoGestorResponse($response, $tramiteId);
        return $response;
    }

    public function getCobroClienteFiles($id)
    {
        return parent::getCobroClienteFiles($id);
    }

    public function upload_cobro_cliente()
    {
        return parent::upload_cobro_cliente();
    }

    public function delete_cobro_cliente()
    {
        return parent::delete_cobro_cliente();
    }

    public function update_final_save()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        $tramiteId = (int) ($this->request->uri->getSegment(4) ?? 0);

        if ($tramiteId <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }

        if (!can_edit_cobro_cliente_surface($roles, $perms)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $tramiteRow = $this->getTramiteRowWithStatuses($tramiteId);

        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'El trámite no existe.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        if ($this->isLockedStatusId($traStatusId)) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'El trámite está concluido o cancelado.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if (!puede_editar_modulo($roles, $traStatusId, 'botones', $reembolsoStatusId, $cobroStatusId, 5)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        return parent::update_final_save();
    }

    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];
        
        return $this->_example_output($output);
    }
    /**
     * Listado de trámites usando el nuevo flujo, independiente del listado original.
     * Mantiene la misma lógica de filtros/seguridad que Tramites::tramite(),
     * pero los botones apuntan a /deskapp/tramitesn/update.
     */
    public function tramite()
    {
        return $this->renderTramiteList();
    }

    public function prototipo_layout($activeStep = null)
    {
        helper(['permissions', 'acl_guard', 'tramite_status']);
        if (is_string($activeStep)) {
            $activeStep = trim($activeStep);

            if ($activeStep !== '' && preg_match('/^paso-(\d+)$/', $activeStep, $matches)) {
                $activeStep = (int) ($matches[1] ?? 0);
            }
        }

        $activeStep = $activeStep !== null && $activeStep !== ''
            ? (int) $activeStep
            : (int) ($this->request->getGet('step') ?? 2);
        $prototypeTramiteId = (int) ($this->request->getGet('tramite_id') ?? 12454);
        $session = session();
        $myid = (int) ($session->get('id') ?? 0);

        if ($activeStep < 1 || $activeStep > 5) {
            $activeStep = 2;
        }

        $prototypeReadOnlyTramite = null;
        if ($activeStep <= 5 && $prototypeTramiteId > 0) {
            $prototypeReadOnlyTramite = $this->loadPrototypeReadOnlyTramite($prototypeTramiteId);
        }

        // Auto-detect active step for unified layout when no explicit ?step= provided.
        // Reads the tra_status.new_format_step column, which maps each status to its
        // step in the new unified layout (differs from the legacy `step` column).
        if ($this->_unifiedLayoutMode && $this->request->getGet('step') === null && $prototypeReadOnlyTramite !== null) {
            $traStatusId = (int) ($prototypeReadOnlyTramite['tra_status_id'] ?? 0);
            if ($traStatusId > 0) {
                $stepRow = \Config\Database::connect()
                    ->table('tra_status')
                    ->select('new_format_step')
                    ->where('id', $traStatusId)
                    ->get()
                    ->getRowArray();
                $detectedStep = (int) ($stepRow['new_format_step'] ?? 0);
                if ($detectedStep >= 1) {
                    // Terminal states (concluido/cancelado use new_format_step=10)
                    // clamp to 5 so the unified layout shows all phases unblocked.
                    $activeStep = min($detectedStep, 5);
                }
            }
        }

        [$roles, $perms] = $this->normalizeRolesPermsFromSession();
        $prototypeCanApproveStep2 = false;
        $prototypeEvidenceForm = [
            'canView' => false,
            'canAdd' => false,
            'blockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'tramiteId' => $prototypeTramiteId,
            'urls' => [
                'create' => site_url('/deskapp/tramitesn/prototype_evidencias_add/' . $prototypeTramiteId),
            ],
            'items' => !empty($prototypeReadOnlyTramite['process_notes']) && is_array($prototypeReadOnlyTramite['process_notes'])
                ? $prototypeReadOnlyTramite['process_notes']
                : [],
        ];
        $prototypeStep4NotesForm = [
            'canView' => false,
            'canAdd' => false,
            'blockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'tramiteId' => $prototypeTramiteId,
            'urls' => [
                'create' => site_url('/deskapp/tramitesn/prototype_step4_notes_add/' . $prototypeTramiteId),
            ],
            'items' => $this->getPrototypeStep4Notes($prototypeTramiteId),
        ];
        $prototypeStep5NotesForm = [
            'canView' => false,
            'canAdd' => false,
            'blockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'tramiteId' => $prototypeTramiteId,
            'urls' => [
                'create' => site_url('/deskapp/tramitesn/prototype_step5_notes_add/' . $prototypeTramiteId),
            ],
            'items' => $this->getPrototypeStep5Notes($prototypeTramiteId),
        ];
        $prototypeStep1Form = [
            'canEdit' => false,
            'blockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'urls' => [
                'updateSave' => site_url('/deskapp/tramitesn/update_save/' . $prototypeTramiteId),
                'getEjecutivosByClienteIdBase' => site_url('/deskapp/tramites/getEjecutivosByClienteId'),
            ],
            'options' => [
                'cliente' => [],
                'ejecutivo' => [],
                'entidad' => [],
            ],
            'values' => [
                'folio' => (string) ($prototypeReadOnlyTramite['folio'] ?? ''),
                'cli_directo_id' => (int) ($prototypeReadOnlyTramite['cli_directo_id'] ?? 0),
                'cli_directo_ejecutivo_id' => (int) ($prototypeReadOnlyTramite['cli_directo_ejecutivo_id'] ?? 0),
                'contrato' => (string) ($prototypeReadOnlyTramite['contrato'] ?? ''),
                'unidad' => (string) ($prototypeReadOnlyTramite['fields']['unidad'] ?? ''),
                'serie' => (string) ($prototypeReadOnlyTramite['fields']['serie'] ?? ''),
                'placas' => (string) ($prototypeReadOnlyTramite['fields']['placas'] ?? ''),
                'entidad_id' => (int) ($prototypeReadOnlyTramite['entidad_id'] ?? 0),
                'observaciones' => (string) ($prototypeReadOnlyTramite['fields']['observaciones'] ?? ''),
                'current_step' => 1,
            ],
        ];
        $prototypeStep1ServicesForm = [
            'canManageBase' => false,
            'canEditPrincipal' => false,
            'canEditAsociado' => false,
            'canDeleteAsociado' => false,
            'blockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'urls' => [
                'principalUpdate' => site_url('/deskapp/tramitesn/principal/update_tipo'),
                'add' => site_url('/deskapp/tramitesn/services/add'),
                'update' => site_url('/deskapp/tramitesn/services/update'),
                'delete' => site_url('/deskapp/tramitesn/services/delete'),
            ],
            'tramiteId' => $prototypeTramiteId,
            'principalTipoId' => (int) ($prototypeReadOnlyTramite['principal_tipo_id'] ?? 0),
            'options' => [
                'traTipos' => [],
            ],
            'services' => !empty($prototypeReadOnlyTramite['service_rows_raw']) && is_array($prototypeReadOnlyTramite['service_rows_raw'])
                ? $prototypeReadOnlyTramite['service_rows_raw']
                : [],
        ];
        $prototypeStep1DocsForm = [
            'canView' => !empty($prototypeReadOnlyTramite),
            'canUpload' => false,
            'canDelete' => false,
            'blockedReason' => null,
            'deleteBlockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'tramiteId' => $prototypeTramiteId,
            'urls' => [
                'upload' => site_url('/deskapp/tramitesn/upload_step1_doc/' . $prototypeTramiteId),
                'delete' => site_url('/deskapp/tramitesn/delete_step1_doc'),
            ],
            'options' => [
                'documentTypes' => !empty($prototypeReadOnlyTramite['step1_document_options']) && is_array($prototypeReadOnlyTramite['step1_document_options'])
                    ? $prototypeReadOnlyTramite['step1_document_options']
                    : [],
            ],
            'documents' => !empty($prototypeReadOnlyTramite['step1_documents']) && is_array($prototypeReadOnlyTramite['step1_documents'])
                ? $prototypeReadOnlyTramite['step1_documents']
                : [],
            'summary' => !empty($prototypeReadOnlyTramite['step1_doc_summary']) && is_array($prototypeReadOnlyTramite['step1_doc_summary'])
                ? $prototypeReadOnlyTramite['step1_doc_summary']
                : [
                    'requiredTotal' => 0,
                    'uploadedRequired' => 0,
                    'uploadedTotal' => 0,
                ],
        ];
        $prototypeStep2Form = [
            'canEdit' => false,
            'canUploadDocs' => false,
            'canDeleteDocs' => false,
            'currentStatusId' => 0,
            'currentStep' => 0,
            'isApprovedLock' => false,
            'isLockedStatus' => false,
            'blockedReason' => null,
            'docsBlockedReason' => null,
            'deleteBlockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'urls' => [
                'updateGestorSave' => site_url('/deskapp/tramitesn/update_gestor_save/' . $prototypeTramiteId),
                'updateDerechosSave' => site_url('/deskapp/tramitesn/update_derechos_save/' . $prototypeTramiteId),
                'getGestoresByEmpresaIdBase' => site_url('/deskapp/tramites/getGestoresByEmpresaId'),
                'uploadDoc' => site_url('/deskapp/tramites/upload_comprobante/' . $prototypeTramiteId),
                'deleteDoc' => site_url('/deskapp/tramites/delete_comprobante/' . $prototypeTramiteId),
                'authorize' => site_url('/deskapp/tramites/autorizar'),
                'afterApprove' => site_url('/deskapp/tramitesn/prototipo-layout/paso-4?tramite_id=' . $prototypeTramiteId),
            ],
            'approvalStatusId' => 23,
            'tramiteId' => $prototypeTramiteId,
            'options' => [
                'empresaGestora' => [],
                'gestor' => [],
                'derechosPagoSitio' => [
                    'online' => 'En Linea',
                    'ventanilla' => 'En Ventanilla',
                ],
                'derechosRevolCliente' => [
                    'revolvente' => 'Fondo Revolvente',
                    'cliente' => 'Pago Cliente',
                ],
            ],
            'values' => [
                'empresa_gestora_id' => (int) ($prototypeReadOnlyTramite['empresa_gestora_id'] ?? 0),
                'gestor_id' => (int) ($prototypeReadOnlyTramite['gestor_id'] ?? 0),
                'derechos_tramite' => (string) ($prototypeReadOnlyTramite['fields']['derechos_tramite'] ?? ''),
                'derechos_pago_sitio' => (string) ($prototypeReadOnlyTramite['fields']['derechos_pago_sitio'] ?? ''),
                'derechos_vigencia' => (string) ($prototypeReadOnlyTramite['fields']['derechos_vigencia'] ?? ''),
                'derechos_revol_cliente' => (string) ($prototypeReadOnlyTramite['fields']['derechos_revol_cliente'] ?? ''),
                'derechos_refer_banc' => (string) ($prototypeReadOnlyTramite['fields']['derechos_refer_banc'] ?? ''),
            ],
            'docs' => !empty($prototypeReadOnlyTramite['pago_derechos_docs']) && is_array($prototypeReadOnlyTramite['pago_derechos_docs'])
                ? $this->expandDocEntries($prototypeReadOnlyTramite['pago_derechos_docs'], 'pago_derechos', $prototypeTramiteId)
                : [],
        ];
        $prototypeStep3Form = [
            'canUpload' => false,
            'canDelete' => false,
            'blockedReason' => null,
            'deleteBlockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'urls' => [
                'upload' => site_url('/deskapp/tramitesn/upload_pago_gestor/' . $prototypeTramiteId),
                'delete' => site_url('/deskapp/tramitesn/delete_pago_gestor'),
                'openPagoGestor' => site_url('/deskapp/tramitesn/prototipo-layout/paso-4?tramite_id=' . $prototypeTramiteId),
            ],
            'tramiteId' => $prototypeTramiteId,
            'options' => [
                'comprobanteFinal' => [
                    'tramite_recibido' => 'Tramite Entregado por Gestor',
                    'acuse_recibo_cliente' => 'Acuse de Recibo del Cliente',
                ],
            ],
            'docs' => !empty($prototypeReadOnlyTramite['evidence_docs_raw']) && is_array($prototypeReadOnlyTramite['evidence_docs_raw'])
                ? $this->expandDocEntries($prototypeReadOnlyTramite['evidence_docs_raw'], 'pago_gestor', $prototypeTramiteId)
                : [],
            'hasTramiteRecibido' => !empty($prototypeReadOnlyTramite['has_tramite_recibido']),
            'hasAcuseRecibo' => !empty($prototypeReadOnlyTramite['has_acuse_recibo']),
        ];
        $prototypeStep4Form = [
            'canView' => false,
            'canEdit' => false,
            'canUploadDocs' => false,
            'canDeleteDocs' => false,
            'blockedReason' => null,
            'uploadBlockedReason' => null,
            'deleteBlockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'tramiteId' => $prototypeTramiteId,
            'url' => site_url('/deskapp/tramitesn/update_pago_gestor/' . $prototypeTramiteId),
            'urls' => [
                'upload' => site_url('/deskapp/tramitesn/upload_pago_gestor/' . $prototypeTramiteId),
                'delete' => site_url('/deskapp/tramitesn/delete_pago_gestor'),
                'getServiceCosts' => site_url('/deskapp/tramitesn/get_service_costs_by_tramite/' . $prototypeTramiteId),
                'updateServiceCost' => site_url('/deskapp/tramitesn/update_service_cost'),
            ],
            'options' => [
                'pagoGestorStatus' => [],
                'statusDoctosGestor' => [
                    'en proceso' => 'En Proceso',
                    'entregados' => 'Entregados',
                ],
                'reembolsoStatus' => [],
                'comprobanteFinal' => [
                    'factura_gestor' => 'Factura del Gestor',
                    'comprobante_pago' => 'Comprobante de Pago',
                ],
            ],
            'docs' => !empty($prototypeReadOnlyTramite['payment_docs_raw']) && is_array($prototypeReadOnlyTramite['payment_docs_raw'])
                ? $this->expandDocEntries($prototypeReadOnlyTramite['payment_docs_raw'], 'pago_gestor', $prototypeTramiteId)
                : [],
            'values' => [
                'costo_tramite' => (string) ($prototypeReadOnlyTramite['fields']['costo_tramite'] ?? ''),
                'deposito_gestor' => (string) ($prototypeReadOnlyTramite['fields']['deposito_gestor'] ?? ''),
                'col_a_favor' => (string) ($prototypeReadOnlyTramite['fields']['col_a_favor'] ?? ''),
                'num_factura_gestor' => (string) ($prototypeReadOnlyTramite['fields']['num_factura_gestor'] ?? ''),
                'impuesto_gestoria' => (string) ($prototypeReadOnlyTramite['fields']['impuesto_gestoria'] ?? ''),
                'gestoria_comision' => (string) ($prototypeReadOnlyTramite['fields']['gestoria_comision'] ?? ''),
                'costo_paqueteria' => (string) ($prototypeReadOnlyTramite['fields']['costo_paqueteria'] ?? ''),
                'gestor_total_pago' => (string) ($prototypeReadOnlyTramite['fields']['gestor_total_pago'] ?? ''),
                'pago_gestor_st_id' => (int) ($prototypeReadOnlyTramite['pago_gestor_st_id'] ?? 0),
                'status_doctos_gestor' => (string) ($prototypeReadOnlyTramite['status_doctos_gestor'] ?? 'en proceso'),
                'reembolso_status_id' => (int) ($prototypeReadOnlyTramite['reembolso_status_id'] ?? 0),
            ],
        ];
        $prototypeStep5Form = [
            'canView' => false,
            'canEdit' => false,
            'canUploadDocs' => false,
            'canDeleteDocs' => false,
            'blockedReason' => null,
            'uploadBlockedReason' => null,
            'deleteBlockedReason' => null,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
            'tramiteId' => $prototypeTramiteId,
            'url' => site_url('/deskapp/tramitesn/update_final_save/' . $prototypeTramiteId),
            'urls' => [
                'getFiles' => site_url('/deskapp/tramitesn/getCobroClienteFiles/' . $prototypeTramiteId),
                'upload' => site_url('/deskapp/tramitesn/upload_cobro_cliente/' . $prototypeTramiteId),
                'delete' => site_url('/deskapp/tramitesn/delete_cobro_cliente'),
            ],
            'options' => [
                'cobroStatus' => [],
                'cobroCorrecto' => [
                    'parcial' => 'Cobro parcial',
                    'completo' => 'Cobro completo',
                    'otro' => 'Otro soporte',
                ],
            ],
            'docs' => !empty($prototypeReadOnlyTramite['cobro_cliente_docs_raw']) && is_array($prototypeReadOnlyTramite['cobro_cliente_docs_raw'])
                ? $this->expandDocEntries($prototypeReadOnlyTramite['cobro_cliente_docs_raw'], 'cobro_cliente', $prototypeTramiteId)
                : [],
            'values' => [
                'id_give_cliente' => (string) ($prototypeReadOnlyTramite['fields']['id_give_cliente'] ?? ''),
                'numero_factura' => (string) ($prototypeReadOnlyTramite['fields']['numero_factura'] ?? ''),
                'numero_refactura' => (string) ($prototypeReadOnlyTramite['fields']['numero_refactura'] ?? ''),
                'cobro_status_id' => (int) ($prototypeReadOnlyTramite['cobro_status_id'] ?? 0),
                'evidencia_cobro_txt' => (string) ($prototypeReadOnlyTramite['fields']['evidencia_cobro_txt'] ?? ''),
                'costo_gestoria' => (string) ($prototypeReadOnlyTramite['fields']['costo_gestoria'] ?? '0.00'),
                'costo_gestoria_hidden' => (string) ($prototypeReadOnlyTramite['fields']['costo_gestoria'] ?? '0.00'),
                'costo_pago_cliente' => (string) ($prototypeReadOnlyTramite['fields']['costo_pago_cliente'] ?? '0'),
                'comision_derechos' => (string) ($prototypeReadOnlyTramite['fields']['comision_derechos'] ?? '0'),
                'iva' => (string) ($prototypeReadOnlyTramite['fields']['iva'] ?? '0.00'),
                'costo_total' => (string) ($prototypeReadOnlyTramite['fields']['costo_total'] ?? '0.00'),
            ],
        ];
        if (!empty($prototypeReadOnlyTramite)) {
            $arrStatus = [
                11 => 1, 22 => 2, 25 => 3, 26 => 3, 27 => 3,
                23 => 4, 28 => 5, 20 => 6, 21 => 7, 29 => 1,
            ];
            $traStatusId = (int) ($prototypeReadOnlyTramite['tra_status_id'] ?? 0);
            $reembolsoStatusId = (int) ($prototypeReadOnlyTramite['reembolso_status_id'] ?? 0);
            $cobroStatusId = (int) ($prototypeReadOnlyTramite['cobro_status_id'] ?? 0);
            $stepActual = $arrStatus[$traStatusId] ?? 1;

            // Pantalla unificada: no redirigir, se muestran todos los pasos

            $hasTenantAccess = $myid > 0 && acl_has_tramite_tenant_access($prototypeTramiteId, $myid, $roles, $perms);
            $canEditTramite = can_edit_tramite($roles, $perms);
            $canWriteDatosTramite = has_permission('write_tramite_datos_tramite', $perms, $roles);
            $canEditPrincipal = has_permission('editar_tramite_principal', $perms, $roles);
            $canEditAsociado = has_permission('editar_tramite_asociado', $perms, $roles);
            $canDeleteAsociado = has_permission('delete_tramite_asociado', $perms, $roles);
            $canWriteGestor = has_permission('write_tramite_asigna_gestor', $perms, $roles);
            $canWriteDerechos = has_permission('write_tramite_pago_derechos', $perms, $roles);
            $canSectionPagoDerechos = has_permission('section_pago_derechos', $perms, $roles);
            $canSectionFinalCostos = has_permission('section_final_costos', $perms, $roles);
            $canQuickActionBitacora = has_permission('quick_action_bitacora', $perms, $roles);
            $canAddBitacora = $canQuickActionBitacora && has_permission('quick_action_bitacora_add', $perms, $roles);
            $canAccessCobroCliente = can_access_cobro_cliente_surface($roles, $perms);
            $canEditCobroClienteSurface = can_edit_cobro_cliente_surface($roles, $perms);
            $canUploadCobroClienteSurface = can_upload_cobro_cliente_surface($roles, $perms);
            $step5ReadOnly = $this->isLockedStatusId($traStatusId);
            $approvedLock = !has_permission('override_tramite_approved_lock', $perms, $roles)
                && tramite_is_aprobado_por_status($traStatusId);
            $isLocked = in_array($traStatusId, [20, 21], true);
            $prototypeEvidenceForm['canView'] = !empty($prototypeReadOnlyTramite);
            $prototypeEvidenceForm['canAdd'] = $hasTenantAccess
                && $canAddBitacora;
            if (!$prototypeEvidenceForm['canAdd']) {
                if (!$hasTenantAccess) {
                    $prototypeEvidenceForm['blockedReason'] = 'Puedes consultar la bitácora, pero no agregar comentarios sobre un trámite fuera de tu contexto de acceso.';
                } elseif (!$canQuickActionBitacora || !$canAddBitacora) {
                    $prototypeEvidenceForm['blockedReason'] = 'Tu perfil no tiene permiso para registrar comentarios en este carril.';
                }
            }
            $prototypeStep2Form['currentStatusId'] = $traStatusId;
            $prototypeStep2Form['currentStep'] = $stepActual;
            $prototypeStep2Form['isApprovedLock'] = $approvedLock;
            $prototypeStep2Form['isLockedStatus'] = $isLocked;
            $canUploadDerechos = $hasTenantAccess
                && $canEditTramite
                && $canSectionPagoDerechos
                && has_permission('can_upload_dropzone_pago_derechos', $perms, $roles)
                && !$approvedLock
                && !$isLocked
                && puede_editar_modulo($roles, $traStatusId, 'step3_upload', $reembolsoStatusId, $cobroStatusId, 3);
            $canDeleteDerechos = $canUploadDerechos
                && has_permission('quick_action_pagos_derecho_delete', $perms, $roles);

            $prototypeStep1Form['canEdit'] = $hasTenantAccess
                && $canEditTramite
                && $canWriteDatosTramite
                && !$approvedLock
                && !$isLocked;

            if (!$prototypeStep1Form['canEdit']) {
                if (!$hasTenantAccess) {
                    $prototypeStep1Form['blockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no guardar sobre ese expediente.';
                } elseif ($approvedLock) {
                    $prototypeStep1Form['blockedReason'] = 'Este tramite ya fue aprobado. En el flujo real, los pasos 1-3 ya no deberian modificarse.';
                } elseif (!$canEditTramite) {
                    $prototypeStep1Form['blockedReason'] = 'Tu perfil no tiene permiso general para editar tramites.';
                } elseif (!$canWriteDatosTramite) {
                    $prototypeStep1Form['blockedReason'] = 'Tu perfil no tiene permiso para editar los datos base del tramite.';
                } elseif ($isLocked) {
                    $prototypeStep1Form['blockedReason'] = 'El tramite esta concluido o cancelado y no admite cambios en este tramo.';
                }
            }

            $prototypeStep2Form['canEdit'] = $hasTenantAccess
                && $canEditTramite
                && $canWriteGestor
                && $canWriteDerechos
                && !$approvedLock
                && !$isLocked;

            if (!$prototypeStep2Form['canEdit']) {
                if (!$hasTenantAccess) {
                    $prototypeStep2Form['blockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no guardar sobre ese expediente.';
                } elseif ($approvedLock) {
                    $prototypeStep2Form['blockedReason'] = 'Este tramite ya fue aprobado. En el flujo real, los pasos 1-3 ya no deberian modificarse.';
                } elseif (!$canEditTramite) {
                    $prototypeStep2Form['blockedReason'] = 'Tu perfil no tiene permiso general para editar tramites.';
                } elseif (!$canWriteGestor || !$canWriteDerechos) {
                    $prototypeStep2Form['blockedReason'] = 'Tu perfil no tiene permisos completos para asignacion de gestor y pago de derechos.';
                } elseif ($isLocked) {
                    $prototypeStep2Form['blockedReason'] = 'El tramite esta concluido o cancelado y no admite cambios en este tramo.';
                }
            }

            $prototypeStep2Form['canUploadDocs'] = $canUploadDerechos;
            $prototypeStep2Form['canDeleteDocs'] = $canDeleteDerechos;
            if (!$prototypeStep2Form['canUploadDocs']) {
                if (!$hasTenantAccess) {
                    $prototypeStep2Form['docsBlockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no subir comprobantes sobre ese expediente.';
                } elseif (!$canEditTramite) {
                    $prototypeStep2Form['docsBlockedReason'] = 'Tu perfil no tiene permiso general para editar tramites.';
                } elseif (!$canSectionPagoDerechos) {
                    $prototypeStep2Form['docsBlockedReason'] = 'Tu perfil no puede entrar a la seccion de pago de derechos.';
                } elseif (!has_permission('can_upload_dropzone_pago_derechos', $perms, $roles)) {
                    $prototypeStep2Form['docsBlockedReason'] = 'Tu perfil no tiene permiso para subir comprobantes de pago de derechos.';
                } elseif ($approvedLock || $isLocked) {
                    $prototypeStep2Form['docsBlockedReason'] = 'El tramite ya no admite cambios documentales en pago de derechos.';
                } else {
                    $prototypeStep2Form['docsBlockedReason'] = 'El flujo normal no permite subir comprobantes en este estatus.';
                }
            }
            if (!$prototypeStep2Form['canDeleteDocs']) {
                if (!$prototypeStep2Form['canUploadDocs']) {
                    $prototypeStep2Form['deleteBlockedReason'] = $prototypeStep2Form['docsBlockedReason'];
                } elseif (!has_permission('quick_action_pagos_derecho_delete', $perms, $roles)) {
                    $prototypeStep2Form['deleteBlockedReason'] = 'Tu perfil no tiene permiso para eliminar comprobantes de pago de derechos.';
                }
            }

            $prototypeStep1ServicesForm['canManageBase'] = $prototypeStep1Form['canEdit'];
            $prototypeStep1ServicesForm['canEditPrincipal'] = $prototypeStep1Form['canEdit'] && $canEditPrincipal;
            $prototypeStep1ServicesForm['canEditAsociado'] = $prototypeStep1Form['canEdit'] && $canEditAsociado;
            $prototypeStep1ServicesForm['canDeleteAsociado'] = $prototypeStep1Form['canEdit'] && $canDeleteAsociado;
            if (!$prototypeStep1ServicesForm['canManageBase']) {
                $prototypeStep1ServicesForm['blockedReason'] = $prototypeStep1Form['blockedReason'];
            } elseif (!$prototypeStep1ServicesForm['canEditPrincipal'] && !$prototypeStep1ServicesForm['canEditAsociado'] && !$prototypeStep1ServicesForm['canDeleteAsociado']) {
                $prototypeStep1ServicesForm['blockedReason'] = 'Tu perfil solo puede consultar la composicion del servicio en este paso.';
            }

            $canQuickActionDocs = has_permission('quick_action_documentos', $perms, $roles);
            $canOverrideStatus28Docs = has_permission('override_tramite_status_28_readonly', $perms, $roles);
            $docsReadOnly = $this->isLockedStatusId($traStatusId)
                || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideStatus28Docs);
            $prototypeStep1DocsForm['canView'] = !empty($prototypeReadOnlyTramite);
            $prototypeStep1DocsForm['canUpload'] = $hasTenantAccess
                && $canQuickActionDocs
                && has_permission('quick_action_documentos_add', $perms, $roles)
                && !$docsReadOnly;
            $prototypeStep1DocsForm['canDelete'] = $hasTenantAccess
                && $canQuickActionDocs
                && has_permission('quick_action_documentos_delete', $perms, $roles)
                && !$docsReadOnly;
            if (!$prototypeStep1DocsForm['canUpload']) {
                if (!$hasTenantAccess) {
                    $prototypeStep1DocsForm['blockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no cargar documentos sobre ese expediente.';
                } elseif (!$canQuickActionDocs || !has_permission('quick_action_documentos_add', $perms, $roles)) {
                    $prototypeStep1DocsForm['blockedReason'] = 'Tu perfil no tiene permiso para cargar documentos del expediente en este carril.';
                } elseif ($docsReadOnly) {
                    $prototypeStep1DocsForm['blockedReason'] = 'La superficie documental quedó en modo de solo lectura por el estatus actual del tramite.';
                } else {
                    $prototypeStep1DocsForm['blockedReason'] = 'Tu perfil solo puede consultar los documentos requeridos de este expediente.';
                }
            }
            if (!$prototypeStep1DocsForm['canDelete']) {
                if (!$hasTenantAccess) {
                    $prototypeStep1DocsForm['deleteBlockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no eliminar documentos sobre ese expediente.';
                } elseif (!$canQuickActionDocs || !has_permission('quick_action_documentos_delete', $perms, $roles)) {
                    $prototypeStep1DocsForm['deleteBlockedReason'] = 'Tu perfil no tiene permiso para eliminar documentos del expediente en este carril.';
                } elseif ($docsReadOnly) {
                    $prototypeStep1DocsForm['deleteBlockedReason'] = 'La superficie documental quedó en modo de solo lectura por el estatus actual del tramite.';
                } else {
                    $prototypeStep1DocsForm['deleteBlockedReason'] = 'Tu perfil solo puede consultar los documentos requeridos de este expediente.';
                }
            }

            $prototypeCanApproveStep2 = has_permission('important_pasar_a_pagos', $perms, $roles)
                && $stepActual <= 3
                && puede_editar_modulo($roles, $traStatusId, 'boton_aprobar_tramite', $reembolsoStatusId, $cobroStatusId, 3);

            $canSectionPagoGestor = has_permission('section_pago_gestor', $perms, $roles);

            $db2 = $this->_getDbData();
            $traTiposModel = new TraTiposModel($db2);
            $prototypeStep1ServicesForm['options']['traTipos'] = $traTiposModel->getTraTiposOptions();

            $clienteDirectoModel = new ClienteDirectoModel($db2);
            $prototypeStep1Form['options']['cliente'] = $clienteDirectoModel->getClientesDirectosOptions();

            $cliEjecutivoModel = new ClienteDirectoEjecutivoModel($db2);
            if (!empty($prototypeReadOnlyTramite['cli_directo_id'])) {
                $prototypeStep1Form['options']['ejecutivo'] = $cliEjecutivoModel->getEjecutivosOptions((int) $prototypeReadOnlyTramite['cli_directo_id']);
            }

            $entidadesModel = new EntidadesModel($db2);
            $prototypeStep1Form['options']['entidad'] = $entidadesModel->getEntidades();

            $empGestora = new EmpresaGestoraModel($db2);
            $prototypeStep2Form['options']['empresaGestora'] = $empGestora->getEmpresasGestorasOptions();

            $gestorModel = new GestorModel($db2);
            if (!empty($prototypeReadOnlyTramite['empresa_gestora_id'])) {
                $prototypeStep2Form['options']['gestor'] = $gestorModel->getGestoresOptions((int) $prototypeReadOnlyTramite['empresa_gestora_id']);
            }

            $pagoGestorStatusModel = new PagoGestorStatusModel($db2);
            $prototypeStep4Form['options']['pagoGestorStatus'] = $pagoGestorStatusModel->getPagoGestorStatusOptions();

            $reembolsoStatusModel = new ReembolsoStatusModel($db2);
            $prototypeStep4Form['options']['reembolsoStatus'] = $reembolsoStatusModel->getReembolsoStatusOptions();

            $cobroStatusModel = new CobroStatusModel($db2);
            $prototypeStep5Form['options']['cobroStatus'] = $cobroStatusModel->getCobroStatusOptions();

            $canKeepStep4Editable = $this->canKeepStep4Editable(
                $reembolsoStatusId,
                (int) ($prototypeReadOnlyTramite['pago_gestor_st_id'] ?? 0),
                $prototypeStep4Form['options']['pagoGestorStatus'],
                (string) ($prototypeReadOnlyTramite['status_doctos_gestor'] ?? '')
            );
            $canUploadPagoGestor = $hasTenantAccess
                && $canSectionPagoGestor
                && has_permission('editar_pago_gestor', $perms, $roles)
                && ($canKeepStep4Editable || puede_editar_modulo($roles, $traStatusId, 'upload_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4));
            $canUploadDropzoneEvidenciasFinales = $canUploadPagoGestor
                && has_permission('can_upload_dropzone_evidencias_finales', $perms, $roles);
            $canUploadDropzonePagoGestorDocumentos = $canUploadPagoGestor
                && has_permission('can_upload_dropzone_pago_gestor_documentos', $perms, $roles);
            $step4ReadOnly = $this->isLockedStatusId($traStatusId);
            $canEditPagoGestor = $myid > 0
                && acl_has_tramite_tenant_access($prototypeTramiteId, $myid, $roles, $perms)
                && can_edit_tramite($roles, $perms)
                && has_permission('section_pago_gestor', $perms, $roles)
                && has_permission('editar_pago_gestor', $perms, $roles)
                && !$step4ReadOnly
                && ($canKeepStep4Editable || puede_editar_modulo($roles, $traStatusId, 'editar_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4));

            $prototypeStep4Form['canEdit'] = $canEditPagoGestor;
            // canView is read-only: gated by tenant access only so historical
            // tramites are always viewable. Editing still requires section perms.
            $prototypeStep4Form['canView'] = $hasTenantAccess;
            $prototypeStep4Form['canUploadDocs'] = $canUploadDropzonePagoGestorDocumentos;
            $prototypeStep4Form['canDeleteDocs'] = $canUploadDropzonePagoGestorDocumentos
                && has_permission('quick_action_pago_gestor_delete', $perms, $roles);
            $prototypeStep4NotesForm['canView'] = $hasTenantAccess && $canSectionPagoGestor;
            $prototypeStep4NotesForm['canAdd'] = $hasTenantAccess
                && $canSectionPagoGestor
                && $canAddBitacora;
            if (!$canEditPagoGestor) {
                if (!($myid > 0 && acl_has_tramite_tenant_access($prototypeTramiteId, $myid, $roles, $perms))) {
                    $prototypeStep4Form['blockedReason'] = 'Este tramite no pertenece a tu contexto de acceso actual para editar Pago a gestor.';
                } elseif (!can_edit_tramite($roles, $perms)) {
                    $prototypeStep4Form['blockedReason'] = 'Tu perfil no tiene permiso general para editar tramites.';
                } elseif (!has_permission('section_pago_gestor', $perms, $roles) || !has_permission('editar_pago_gestor', $perms, $roles)) {
                    $prototypeStep4Form['blockedReason'] = 'Tu perfil no tiene permisos completos para editar Pago a gestor.';
                } elseif ($step4ReadOnly) {
                    $prototypeStep4Form['blockedReason'] = 'Este tramite esta en modo de solo lectura para Pago a gestor.';
                } else {
                    $prototypeStep4Form['blockedReason'] = 'Pago a gestor no esta editable para este estatus y este perfil.';
                }
            }
            if (!$prototypeStep4Form['canUploadDocs']) {
                if (!$hasTenantAccess) {
                    $prototypeStep4Form['uploadBlockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no subir documentos de pago a gestor.';
                } elseif (!$canSectionPagoGestor || !has_permission('editar_pago_gestor', $perms, $roles)) {
                    $prototypeStep4Form['uploadBlockedReason'] = 'Tu perfil no tiene permisos completos para subir documentos de pago a gestor.';
                } elseif (!$canUploadPagoGestor) {
                    $prototypeStep4Form['uploadBlockedReason'] = 'Los documentos de pago a gestor no estan editables para este estatus y este perfil.';
                } elseif (!has_permission('can_upload_dropzone_pago_gestor_documentos', $perms, $roles)) {
                    $prototypeStep4Form['uploadBlockedReason'] = 'Tu perfil no tiene permiso para usar el dropzone de documentos de pago a gestor.';
                } else {
                    $prototypeStep4Form['uploadBlockedReason'] = 'Tu perfil solo puede consultar los documentos de pago a gestor en este tramo.';
                }
            }
            if (!$prototypeStep4Form['canDeleteDocs']) {
                if (!$hasTenantAccess) {
                    $prototypeStep4Form['deleteBlockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no eliminar documentos de pago a gestor.';
                } elseif (!$canSectionPagoGestor || !has_permission('editar_pago_gestor', $perms, $roles)) {
                    $prototypeStep4Form['deleteBlockedReason'] = 'Tu perfil no tiene permisos completos para eliminar documentos de pago a gestor.';
                } elseif (!$canUploadPagoGestor) {
                    $prototypeStep4Form['deleteBlockedReason'] = 'Los documentos de pago a gestor no estan editables para este estatus y este perfil.';
                } elseif (!has_permission('can_upload_dropzone_pago_gestor_documentos', $perms, $roles)) {
                    $prototypeStep4Form['deleteBlockedReason'] = 'Tu perfil no tiene permiso para administrar documentos de pago a gestor.';
                } elseif (!has_permission('quick_action_pago_gestor_delete', $perms, $roles)) {
                    $prototypeStep4Form['deleteBlockedReason'] = 'Tu perfil no tiene permiso para eliminar documentos de pago a gestor.';
                } else {
                    $prototypeStep4Form['deleteBlockedReason'] = 'Tu perfil solo puede consultar los documentos de pago a gestor en este tramo.';
                }
            }
            if (!$prototypeStep4NotesForm['canAdd']) {
                if (!$hasTenantAccess) {
                    $prototypeStep4NotesForm['blockedReason'] = 'Puedes consultar el seguimiento interno, pero no agregar notas sobre un trámite fuera de tu contexto de acceso.';
                } elseif (!$canSectionPagoGestor) {
                    $prototypeStep4NotesForm['blockedReason'] = 'Tu perfil no tiene acceso a la sección de Pago a gestor.';
                } elseif (!$canQuickActionBitacora || !$canAddBitacora) {
                    $prototypeStep4NotesForm['blockedReason'] = 'Tu perfil no tiene permiso para registrar notas internas en Pago a gestor.';
                }
            }

            // canView is read-only: gated by tenant access only so historical
            // tramites are always viewable. Editing still requires section perms.
            $prototypeStep5Form['canView'] = $hasTenantAccess;

            $prototypeStep5Form['canEdit'] = $hasTenantAccess
                && $canSectionFinalCostos
                && $canAccessCobroCliente
                && $canEditCobroClienteSurface
                && has_permission('editar_final', $perms, $roles)
                && !$step5ReadOnly
                && puede_editar_modulo($roles, $traStatusId, 'botones', $reembolsoStatusId, $cobroStatusId, 5);

            $prototypeStep5Form['canUploadDocs'] = $hasTenantAccess
                && $canSectionFinalCostos
                && $canUploadCobroClienteSurface
                && !$step5ReadOnly
                && puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5);

            $prototypeStep5Form['canDeleteDocs'] = $prototypeStep5Form['canUploadDocs']
                && has_permission('quick_action_cobros_cliente_delete', $perms, $roles);

            if (!$prototypeStep5Form['canEdit']) {
                if (!$hasTenantAccess) {
                    $prototypeStep5Form['blockedReason'] = 'Este tramite no pertenece a tu contexto de acceso actual para editar Cobro a cliente.';
                } elseif (!$canSectionFinalCostos || !$canAccessCobroCliente) {
                    $prototypeStep5Form['blockedReason'] = 'Tu perfil no tiene acceso completo a la sección de Cobro a cliente.';
                } elseif (!$canEditCobroClienteSurface || !has_permission('editar_final', $perms, $roles)) {
                    $prototypeStep5Form['blockedReason'] = 'Tu perfil no tiene permisos completos para editar Cobro a cliente.';
                } elseif ($step5ReadOnly) {
                    $prototypeStep5Form['blockedReason'] = 'Cobro a cliente quedó en modo de solo lectura para este trámite.';
                } else {
                    $prototypeStep5Form['blockedReason'] = 'Cobro a cliente no está editable para este estatus y este perfil.';
                }
            }

            if (!$prototypeStep5Form['canUploadDocs']) {
                if (!$hasTenantAccess) {
                    $prototypeStep5Form['uploadBlockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no subir evidencias de cobro.';
                } elseif (!$canSectionFinalCostos || !$canAccessCobroCliente) {
                    $prototypeStep5Form['uploadBlockedReason'] = 'Tu perfil no tiene acceso completo a la sección de Cobro a cliente.';
                } elseif (!$canUploadCobroClienteSurface) {
                    $prototypeStep5Form['uploadBlockedReason'] = 'Tu perfil no tiene permiso para usar el dropzone de Cobro a cliente.';
                } elseif ($step5ReadOnly) {
                    $prototypeStep5Form['uploadBlockedReason'] = 'Cobro a cliente quedó en modo de solo lectura para este trámite.';
                } else {
                    $prototypeStep5Form['uploadBlockedReason'] = 'Las evidencias de cobro no están editables para este estatus y este perfil.';
                }
            }

            if (!$prototypeStep5Form['canDeleteDocs']) {
                if (!$hasTenantAccess) {
                    $prototypeStep5Form['deleteBlockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no eliminar evidencias de cobro.';
                } elseif (!$canSectionFinalCostos || !$canAccessCobroCliente) {
                    $prototypeStep5Form['deleteBlockedReason'] = 'Tu perfil no tiene acceso completo a la sección de Cobro a cliente.';
                } elseif (!$prototypeStep5Form['canUploadDocs']) {
                    $prototypeStep5Form['deleteBlockedReason'] = $prototypeStep5Form['uploadBlockedReason'];
                } elseif (!has_permission('quick_action_cobros_cliente_delete', $perms, $roles)) {
                    $prototypeStep5Form['deleteBlockedReason'] = 'Tu perfil no tiene permiso para eliminar evidencias de cobro.';
                }
            }

            $prototypeStep5NotesForm['canView'] = $hasTenantAccess && $canAccessCobroCliente;
            $prototypeStep5NotesForm['canAdd'] = $hasTenantAccess
                && $canAccessCobroCliente
                && $canAddBitacora;
            if (!$prototypeStep5NotesForm['canAdd']) {
                if (!$hasTenantAccess) {
                    $prototypeStep5NotesForm['blockedReason'] = 'Puedes consultar el seguimiento interno, pero no agregar notas sobre un trámite fuera de tu contexto de acceso.';
                } elseif (!$canAccessCobroCliente) {
                    $prototypeStep5NotesForm['blockedReason'] = 'Tu perfil no tiene acceso a la sección de Cobro a cliente.';
                } elseif (!$canQuickActionBitacora || !$canAddBitacora) {
                    $prototypeStep5NotesForm['blockedReason'] = 'Tu perfil no tiene permiso para registrar notas internas en Cobro a cliente.';
                } else {
                    $prototypeStep5NotesForm['blockedReason'] = 'Cobro a cliente no está editable para este estatus y este perfil.';
                }
            }

            $prototypeStep3Form['canUpload'] = $canUploadDropzoneEvidenciasFinales;
            $prototypeStep3Form['canDelete'] = $canUploadDropzoneEvidenciasFinales
                && has_permission('quick_action_evidencias_finales_delete', $perms, $roles);
            if (!$prototypeStep3Form['canUpload']) {
                if (!$hasTenantAccess) {
                    $prototypeStep3Form['blockedReason'] = 'Este tramite demo no pertenece a tu contexto de acceso actual. El prototipo puede verse, pero no subir evidencias sobre ese expediente.';
                } elseif (!$canSectionPagoGestor || !has_permission('editar_pago_gestor', $perms, $roles)) {
                    $prototypeStep3Form['blockedReason'] = 'Tu perfil no tiene permisos completos para subir evidencias finales.';
                } elseif (!$canUploadPagoGestor) {
                    $prototypeStep3Form['blockedReason'] = 'Las evidencias finales no estan editables para este estatus y este perfil.';
                } else {
                    $prototypeStep3Form['blockedReason'] = 'Tu perfil solo puede consultar las evidencias finales en este tramo.';
                }
            }
            if (!$prototypeStep3Form['canDelete']) {
                if (!$prototypeStep3Form['canUpload']) {
                    $prototypeStep3Form['deleteBlockedReason'] = $prototypeStep3Form['blockedReason'];
                } elseif (!has_permission('quick_action_evidencias_finales_delete', $perms, $roles)) {
                    $prototypeStep3Form['deleteBlockedReason'] = 'Tu perfil no tiene permiso para eliminar evidencias finales.';
                }
            }
        }

        $viewData = [
            'title' => 'SGL - Detalle de Tramites',
            'activeStep' => $activeStep,
            'prototypeTramiteId' => $prototypeTramiteId,
            'prototypeReadOnlyTramite' => $prototypeReadOnlyTramite,
            'prototypeCanApproveStep2' => $prototypeCanApproveStep2,
            'prototypeStep1Form' => $prototypeStep1Form,
            'prototypeStep1ServicesForm' => $prototypeStep1ServicesForm,
            'prototypeStep1DocsForm' => $prototypeStep1DocsForm,
            'prototypeStep2Form' => $prototypeStep2Form,
            'prototypeStep3Form' => $prototypeStep3Form,
            'prototypeStep4Form' => $prototypeStep4Form,
            'prototypeStep5Form' => $prototypeStep5Form,
            'prototypeStep4NotesForm' => $prototypeStep4NotesForm,
            'prototypeStep5NotesForm' => $prototypeStep5NotesForm,
            'prototypeEvidenceForm' => $prototypeEvidenceForm,
        ];

        if ($this->_unifiedLayoutMode) {
            $this->_unifiedLayoutMode = false;
            return view('deskapp/tramite_unified/index', ['viewData' => $viewData]);
        }

        return view('deskapp/extra-pages/tramites_layout_prototipo', $viewData);
    }

    public function prototipo_layout_paso_1()
    {
        return $this->prototipo_layout(1);
    }

    public function prototipo_layout_paso_2()
    {
        return $this->prototipo_layout(2);
    }

    public function prototipo_layout_paso_3()
    {
        return $this->prototipo_layout(3);
    }

    public function prototipo_layout_paso_4()
    {
        return $this->prototipo_layout(4);
    }

    public function prototipo_layout_paso_5()
    {
        return $this->prototipo_layout(5);
    }

    /**
     * Unified layout view — renders the same tramite data using
     * the new decomposed partial-based layout (5 rows × 3 rails).
     * Reuses all data-loading logic from prototipo_layout().
     */
    public function unified_layout()
    {
        $this->_unifiedLayoutMode = true;
        return $this->prototipo_layout(null);
    }

    public function upload_step1_doc($tramiteId = null)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) ($tramiteId ?? $this->request->uri->getSegment(4) ?? 0);
        $documentoId = (int) $this->request->getPost('documento_id');

        if ($tramiteId <= 0 || $documentoId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Parámetros inválidos.', 'csrfHash' => csrf_hash()]);
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }

        if (!has_permission('quick_action_documentos', $perms, $roles) || !has_permission('quick_action_documentos_add', $perms, $roles)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $db = ConfigDatabase::connect();
        $tramiteRow = $db->table('tramite')
            ->select('id, folio, tra_status_id, tra_tipos_id')
            ->where('id', $tramiteId)
            ->get()
            ->getRowArray();

        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado.', 'csrfHash' => csrf_hash()]);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = $this->isLockedStatusId($traStatusId)
            || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideReadonly);
        if ($isLocked) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.', 'csrfHash' => csrf_hash()]);
        }

        $documentState = $this->buildPrototypeStep1DocumentState(
            $tramiteId,
            (int) ($tramiteRow['tra_tipos_id'] ?? 0)
        );
        if (!in_array($documentoId, $documentState['allowedDocIds'] ?? [], true)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'El documento seleccionado no pertenece al catálogo actual del trámite.', 'csrfHash' => csrf_hash()]);
        }

        if (empty($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo.', 'csrfHash' => csrf_hash()]);
        }

        $tempFile = (string) ($_FILES['file']['tmp_name'] ?? '');
        $originalName = (string) ($_FILES['file']['name'] ?? '');

        // Persist through the storage abstraction (local disk or S3 depending on
        // FILE_STORAGE_DRIVER). "documentostatus" is a flat category (no per-id
        // segment); the legacy tra_doc_status.file column stores the bare filename.
        $key = buildKey('documentostatus', null, $originalName);
        $fileName = basename($key);

        $storage = service('fileStorage');
        if (!$storage->put($key, $tempFile)) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo guardar el archivo.', 'csrfHash' => csrf_hash()]);
        }

        try {
            $existingRows = $db->table('tra_doc_status')
                ->select('id, file')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->where('status', 1)
                ->get()
                ->getResultArray();

            foreach ($existingRows as $existing) {
                $existingFile = trim((string) ($existing['file'] ?? ''));
                if ($existingFile !== '' && strpos($existingFile, '..') === false) {
                    $existingKey = keyFromStored($existingFile, 'documentostatus');
                    if ($existingKey !== '') {
                        $storage->delete($existingKey);
                    }
                }
            }

            $db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->delete();

            $db->table('tra_doc_status')->insert([
                'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                'tramite_id' => $tramiteId,
                'documento_id' => $documentoId,
                'status_documento_id' => defined('SGL_TRA_STATUS_RECOLECCION_DCTOS') ? (int) SGL_TRA_STATUS_RECOLECCION_DCTOS : 11,
                'file' => $fileName,
                'comentario' => 'se sube documento desde prototipo paso 1',
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'status' => 1,
            ]);

            $filePath = file_url($fileName, 'documentostatus');
            if ($filePath === '') {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo resolver la URL del archivo.', 'csrfHash' => csrf_hash()]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento subido correctamente.',
                'fileName' => $fileName,
                'documento_id' => $documentoId,
                'filePath' => $filePath,
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en upload_step1_doc: ' . $e->getMessage());

            // Compensating action: the object was already persisted by put()
            // but the DB write failed. Delete the just-written key exactly once
            // so the store never accumulates an object with no referencing row.
            $compensated = false;
            try {
                $compensated = (bool) $storage->delete($key);
            } catch (\Throwable $deleteError) {
                $compensated = false;
                log_message('error', 'Fallo al ejecutar delete compensatorio en upload_step1_doc para key: ' . $key . ' - ' . $deleteError->getMessage());
            }

            if (!$compensated) {
                // The compensating delete could not remove the object. Record the
                // orphaned key clearly so it can be identified for later cleanup.
                log_message('error', 'ORPHANED_S3_KEY upload_step1_doc: no se pudo eliminar el objeto huérfano tras fallo de DB. key=' . $key);
            }

            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo persistir el documento subido.', 'csrfHash' => csrf_hash()]);
        }
    }

    public function delete_step1_doc()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $documentoId = (int) $this->request->getPost('documento_id');
        $fileName = trim((string) $this->request->getPost('file'));

        if ($tramiteId <= 0 || $documentoId <= 0 || $fileName === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Parámetros inválidos.', 'csrfHash' => csrf_hash()]);
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }

        if (!has_permission('quick_action_documentos', $perms, $roles) || !has_permission('quick_action_documentos_delete', $perms, $roles)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $db = ConfigDatabase::connect();
        $tramiteRow = $db->table('tramite')
            ->select('id, tra_status_id, tra_tipos_id')
            ->where('id', $tramiteId)
            ->get()
            ->getRowArray();

        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado.', 'csrfHash' => csrf_hash()]);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = $this->isLockedStatusId($traStatusId)
            || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideReadonly);
        if ($isLocked) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.', 'csrfHash' => csrf_hash()]);
        }

        $documentState = $this->buildPrototypeStep1DocumentState(
            $tramiteId,
            (int) ($tramiteRow['tra_tipos_id'] ?? 0)
        );
        if (!in_array($documentoId, $documentState['allowedDocIds'] ?? [], true)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'El documento seleccionado no pertenece al catálogo actual del trámite.', 'csrfHash' => csrf_hash()]);
        }

        if ($fileName !== basename($fileName) || strpos($fileName, '..') !== false || strpos($fileName, "\0") !== false) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre de archivo inválido.', 'csrfHash' => csrf_hash()]);
        }

        try {
            $rows = $db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->where('file', $fileName)
                ->get()
                ->getResultArray();
            if (empty($rows)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se encontró documento para eliminar.', 'csrfHash' => csrf_hash()]);
            }

            // Eliminar el/los objeto(s) a través del servicio de almacenamiento (Req 6.4).
            // documentostatus es plano (sin id). Req 6.7: si delete() falla o lanza
            // excepción, se retorna 500 y NO se elimina la fila de BD (la referencia
            // existente se conserva intacta).
            helper('filestorage');
            $storage = service('fileStorage');
            foreach ($rows as $row) {
                $file = trim((string) ($row['file'] ?? ''));
                if ($file !== '' && $file === basename($file) && strpos($file, '..') === false) {
                    $key = keyFromStored($file, 'documentostatus');
                    if (!$storage->delete($key)) {
                        return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor.', 'csrfHash' => csrf_hash()]);
                    }
                }
            }

            $db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->where('file', $fileName)
                ->delete();

            return $this->response->setJSON(['success' => true, 'message' => 'Documento eliminado correctamente.', 'csrfHash' => csrf_hash()]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en delete_step1_doc: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al eliminar documento.', 'csrfHash' => csrf_hash()]);
        }
    }

    /**
     * Expand document rows whose `file` column may hold several comma-separated
     * filenames (GroceryCrud multi-upload stores multiple files in one column).
     *
     * Each filename becomes its own entry with its own resolved `url` and
     * `is_image` flag, preserving all other row metadata (e.g. comprobante_final,
     * cobro_correcto, id). A row with an empty file yields a single entry with
     * url='' so pre-existing "empty row" behavior is retained.
     *
     * @param array<int,mixed> $rawRows   Raw doc rows (arrays with a 'file' key, or bare filename strings).
     * @param string           $category  Storage category (e.g. 'pago_gestor', 'cobro_cliente').
     * @param int              $tramiteId Tramite id used to build per-id keys.
     *
     * @return array<int,array<string,mixed>>
     */
    private function expandDocEntries(array $rawRows, string $category, int $tramiteId): array
    {
        $expanded = [];
        foreach ($rawRows as $rawRow) {
            $row = is_array($rawRow) ? $rawRow : ['file' => (string) $rawRow];
            $fileField = trim((string) ($row['file'] ?? ''));

            if ($fileField === '') {
                $entry = $row;
                $entry['file'] = '';
                $entry['url'] = '';
                $entry['is_image'] = false;
                $expanded[] = $entry;
                continue;
            }

            $fileNames = array_values(array_filter(
                array_map('trim', explode(',', $fileField)),
                static function (string $f): bool {
                    return $f !== '';
                }
            ));
            if ($fileNames === []) {
                $fileNames = [''];
            }

            foreach ($fileNames as $file) {
                $entry = $row;
                $entry['file'] = $file;
                // XML files force a download (Content-Disposition: attachment)
                // because browsers render raw XML inline; other files keep the
                // normal inline URL.
                $isXml = $file !== '' && strtolower((string) pathinfo($file, PATHINFO_EXTENSION)) === 'xml';
                $entry['url'] = $file !== ''
                    ? ($isXml
                        ? file_download_url($file, $category, $tramiteId)
                        : file_url($file, $category, $tramiteId))
                    : '';
                $entry['is_image'] = $file !== '' ? is_image_filename($file) : false;
                $expanded[] = $entry;
            }
        }

        return $expanded;
    }

    private function loadPrototypeReadOnlyTramite(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        try {
            $db = ConfigDatabase::connect();
            $db2 = $this->_getDbData();
            $tramite = $db->table('tramite')
                ->select('id, folio, contrato, tra_status_id, cli_directo_id, cli_directo_ejecutivo_id, entidad_id, tra_tipos_id, unidad, serie, placas, observaciones, empresa_gestora_id, gestor_id, derechos_tramite, derechos_pago_sitio, derechos_vigencia, derechos_revol_cliente, derechos_refer_banc, costo_tramite, deposito_gestor, col_a_favor, num_factura_gestor, pago_gestor_st_id, status_doctos_gestor, impuesto_gestoria, gestoria_comision, costo_paqueteria, gestor_total_pago, reembolso_status_id, cobro_status_id, id_give_cliente, numero_factura, numero_refactura, evidencia_cobro_txt, costo_pago_cliente, comision_derechos')
                ->where('id', $id)
                ->get()
                ->getRowArray();

            if (empty($tramite)) {
                return null;
            }

            $gestorNombre = 'Sin asignar';
            if (!empty($tramite['gestor_id'])) {
                $gestorModel = new GestorModel($db2);
                $gestorNombre = $gestorModel->getGestorNameById((int) $tramite['gestor_id']) ?: 'Sin asignar';
            }

            $empresaGestoraNombre = 'Sin empresa';
            if (!empty($tramite['empresa_gestora_id'])) {
                $empresaGestoraRow = $db->table('ges_empresa_gestora')
                    ->select('id, nombre')
                    ->where('id', (int) $tramite['empresa_gestora_id'])
                    ->get()
                    ->getRowArray();
                $empresaGestoraNombre = (string) ($empresaGestoraRow['nombre'] ?? 'Sin empresa');
            }

            $pagoGestorStatusModel = new PagoGestorStatusModel($db2);
            $reembolsoStatusModel = new ReembolsoStatusModel($db2);
            $cobroStatusModel = new CobroStatusModel($db2);
            $pagoGestorStatuses = $pagoGestorStatusModel->getPagoGestorStatusOptions();
            $reembolsoStatuses = $reembolsoStatusModel->getReembolsoStatusOptions();
            $cobroStatuses = $cobroStatusModel->getCobroStatusOptions();

            $traStatusLabel = 'Sin estatus';
            $traNewFormatStep = 0;
            if (!empty($tramite['tra_status_id'])) {
                $traStatusRow = $db->table('tra_status')
                    ->select('id, tra_status, new_format_step')
                    ->where('id', (int) $tramite['tra_status_id'])
                    ->get()
                    ->getRowArray();
                $traStatusLabel = (string) ($traStatusRow['tra_status'] ?? 'Sin estatus');
                $traNewFormatStep = (int) ($traStatusRow['new_format_step'] ?? 0);
            }

            $clienteNombre = 'Sin cliente';
            if (!empty($tramite['cli_directo_id'])) {
                $clienteRow = $db->table('cli_directo')
                    ->select('id, nombre')
                    ->where('id', (int) $tramite['cli_directo_id'])
                    ->get()
                    ->getRowArray();
                $clienteNombre = (string) ($clienteRow['nombre'] ?? 'Sin cliente');
            }

            $ejecutivoNombre = 'Sin ejecutivo';
            if (!empty($tramite['cli_directo_ejecutivo_id'])) {
                $ejecutivoRow = $db->table('cli_directo_ejecutivo')
                    ->select('id, nombre')
                    ->where('id', (int) $tramite['cli_directo_ejecutivo_id'])
                    ->get()
                    ->getRowArray();
                $ejecutivoNombre = (string) ($ejecutivoRow['nombre'] ?? 'Sin ejecutivo');
            }

            $entidadNombre = 'Sin entidad';
            if (!empty($tramite['entidad_id'])) {
                $entidadRow = $db->table('entidad')
                    ->select('id, entidad')
                    ->where('id', (int) $tramite['entidad_id'])
                    ->get()
                    ->getRowArray();
                $entidadNombre = (string) ($entidadRow['entidad'] ?? 'Sin entidad');
            }

            $tipoPrincipalLabel = 'Sin tipo principal';
            if (!empty($tramite['tra_tipos_id'])) {
                $tipoPrincipalRow = $db->table('tra_tipos')
                    ->select('id, tipo_tramite')
                    ->where('id', (int) $tramite['tra_tipos_id'])
                    ->get()
                    ->getRowArray();
                $tipoPrincipalLabel = (string) ($tipoPrincipalRow['tipo_tramite'] ?? 'Sin tipo principal');
            }

            $linkedServiceBadges = [];
            $associatedServiceRows = [];
            $serviceRowsRaw = $this->getPrototypeServiceRowsRaw($id, (int) ($tramite['tra_tipos_id'] ?? 0));
            foreach ($serviceRowsRaw as $serviceRow) {
                $serviceTipoId = (int) ($serviceRow['tra_tipos_id'] ?? 0);
                $serviceLabel = (string) ($serviceRow['label'] ?? ('Tipo #' . $serviceTipoId));
                $isPrincipal = !empty($serviceRow['is_principal']);
                $linkedServiceBadges[] = [
                    'label' => $serviceLabel,
                    'state' => $isPrincipal ? 'principal' : 'asociado',
                ];

                if (!$isPrincipal) {
                    $associatedServiceRows[] = [
                        'label' => $serviceLabel,
                        'kind' => 'Asociado registrado',
                        'actions' => 'Solo lectura',
                    ];
                }
            }

            $sumDerechos = 0.0;
            foreach ($serviceRowsRaw as $serviceRow) {
                $sumDerechos += is_numeric($serviceRow['costo_tramite'] ?? null)
                    ? (float) $serviceRow['costo_tramite']
                    : 0.0;
            }
            $derechosTramiteFallback = is_numeric($tramite['derechos_tramite'] ?? null)
                ? (float) $tramite['derechos_tramite']
                : 0.0;
            if ($sumDerechos <= 0 && $derechosTramiteFallback > 0) {
                $sumDerechos = $derechosTramiteFallback;
            }

            $baseIva = 0.0;
            $baseIva += is_numeric($tramite['costo_pago_cliente'] ?? null) ? (float) $tramite['costo_pago_cliente'] : 0.0;
            $baseIva += is_numeric($tramite['comision_derechos'] ?? null) ? (float) $tramite['comision_derechos'] : 0.0;
            $ivaCalc = round($baseIva * 0.16, 2);
            $costoTotalCalc = round($sumDerechos + $baseIva + $ivaCalc, 2);

            $step1DocumentState = $this->buildPrototypeStep1DocumentState(
                $id,
                (int) ($tramite['tra_tipos_id'] ?? 0),
                $serviceRowsRaw
            );

            $pagoDerechosModel = new PagoDerechosModel($db2);
            $pagoDerechosRows = $pagoDerechosModel->getImgDerechosByTramiteId($id);
            $pagoDerechosDocs = [];
            if (is_array($pagoDerechosRows)) {
                foreach ($pagoDerechosRows as $docRow) {
                    $fileName = trim((string) ($docRow['file'] ?? ''));
                    if ($fileName !== '') {
                        $pagoDerechosDocs[] = $fileName;
                    }
                }
            }

            $documentRows = $db->table('tra_pago_gestor')
                ->select('file, comprobante_final')
                ->where('tramite_id', $id)
                ->where('status', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();

            $cobroClienteRows = $db->table('tra_cobro_cliente')
                ->select('id, file, cobro_correcto')
                ->where('tramite_id', $id)
                ->where('status', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();

            $paymentDocs = [];
            $paymentDocsRaw = [];
            $evidenceDocs = [];
            $evidenceDocsRaw = [];
            $cobroClienteDocs = [];
            $cobroClienteDocsRaw = [];
            $hasFacturaGestor = false;
            $hasComprobantePago = false;
            $hasTramiteRecibido = false;
            $hasAcuseRecibo = false;

            foreach ($documentRows as $row) {
                $tipo = (string) ($row['comprobante_final'] ?? '');
                $file = (string) ($row['file'] ?? '');
                if ($file === '') {
                    continue;
                }

                if ($tipo === 'factura_gestor') {
                    $hasFacturaGestor = true;
                    $paymentDocs[] = $file;
                    $paymentDocsRaw[] = [
                        'file' => $file,
                        'comprobante_final' => $tipo,
                    ];
                    continue;
                }

                if ($tipo === 'comprobante_pago') {
                    $hasComprobantePago = true;
                    $paymentDocs[] = $file;
                    $paymentDocsRaw[] = [
                        'file' => $file,
                        'comprobante_final' => $tipo,
                    ];
                    continue;
                }

                if ($tipo === 'tramite_recibido' || $tipo === 'acuse_recibo_cliente') {
                    if ($tipo === 'tramite_recibido') {
                        $hasTramiteRecibido = true;
                    }
                    if ($tipo === 'acuse_recibo_cliente') {
                        $hasAcuseRecibo = true;
                    }
                    $evidenceDocs[] = $file;
                    $evidenceDocsRaw[] = [
                        'file' => $file,
                        'comprobante_final' => $tipo,
                    ];
                    continue;
                }

                $paymentDocs[] = $file;
                $paymentDocsRaw[] = [
                    'file' => $file,
                    'comprobante_final' => $tipo,
                ];
            }

            foreach ($cobroClienteRows as $row) {
                $file = trim((string) ($row['file'] ?? ''));
                if ($file === '') {
                    continue;
                }

                $cobroCorrecto = trim((string) ($row['cobro_correcto'] ?? 'otro'));
                if (!in_array($cobroCorrecto, ['parcial', 'completo', 'otro'], true)) {
                    $cobroCorrecto = 'otro';
                }

                $cobroClienteDocs[] = $file;
                $cobroClienteDocsRaw[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'file' => $file,
                    'cobro_correcto' => $cobroCorrecto,
                ];
            }

            $derechosPagoMap = [
                'online' => 'En Linea',
                'ventanilla' => 'En Ventanilla',
            ];
            $derechosFormaMap = [
                'revolvente' => 'Fondo Revolvente',
                'cliente' => 'Pago Cliente',
            ];

            return [
                'id' => (int) ($tramite['id'] ?? 0),
                'folio' => (string) ($tramite['folio'] ?? ''),
                'contrato' => (string) ($tramite['contrato'] ?? ''),
                'tra_status_id' => (int) ($tramite['tra_status_id'] ?? 0),
                'tra_status_label' => $traStatusLabel,
                'new_format_step' => $traNewFormatStep,
                'cli_directo_id' => (int) ($tramite['cli_directo_id'] ?? 0),
                'cli_directo_ejecutivo_id' => (int) ($tramite['cli_directo_ejecutivo_id'] ?? 0),
                'entidad_id' => (int) ($tramite['entidad_id'] ?? 0),
                'reembolso_status_id' => (int) ($tramite['reembolso_status_id'] ?? 0),
                'cobro_status_id' => (int) ($tramite['cobro_status_id'] ?? 0),
                'cliente_name' => $clienteNombre,
                'ejecutivo_name' => $ejecutivoNombre,
                'entidad_name' => $entidadNombre,
                'tipo_principal_label' => $tipoPrincipalLabel,
                'principal_tipo_id' => (int) ($tramite['tra_tipos_id'] ?? 0),
                'step1_complete' => !empty($tramite['contrato']) && !empty($tramite['entidad_id']),
                'linked_service_badges' => $linkedServiceBadges,
                'associated_service_rows' => $associatedServiceRows,
                'service_rows_raw' => $serviceRowsRaw,
                'step1_documents' => $step1DocumentState['documents'] ?? [],
                'step1_document_options' => $step1DocumentState['documentOptions'] ?? [],
                'step1_document_option_meta' => $step1DocumentState['documentOptionMeta'] ?? [],
                'step1_doc_summary' => $step1DocumentState['summary'] ?? [
                    'requiredTotal' => 0,
                    'uploadedRequired' => 0,
                    'uploadedTotal' => 0,
                ],
                'empresa_gestora_id' => (int) ($tramite['empresa_gestora_id'] ?? 0),
                'empresa_gestora_name' => $empresaGestoraNombre,
                'gestor_id' => (int) ($tramite['gestor_id'] ?? 0),
                'gestor_name' => $gestorNombre,
                'step2_complete' => !empty($tramite['empresa_gestora_id']) && !empty($tramite['gestor_id']),
                'step3_complete' => !empty($tramite['derechos_tramite']) && !empty($tramite['derechos_revol_cliente']) && !empty($tramite['derechos_refer_banc']),
                'pago_gestor_st_id' => (int) ($tramite['pago_gestor_st_id'] ?? 0),
                'pago_gestor_status_label' => $pagoGestorStatuses[(int) ($tramite['pago_gestor_st_id'] ?? 0)] ?? 'Sin definir',
                'reembolso_status_label' => $reembolsoStatuses[(int) ($tramite['reembolso_status_id'] ?? 0)] ?? 'Sin definir',
                'cobro_status_label' => $cobroStatuses[(int) ($tramite['cobro_status_id'] ?? 0)] ?? 'Sin definir',
                'status_doctos_gestor' => (string) ($tramite['status_doctos_gestor'] ?? 'en proceso'),
                'status_doctos_gestor_label' => ((string) ($tramite['status_doctos_gestor'] ?? 'en proceso')) === 'entregados' ? 'Entregados' : 'En proceso',
                'fields' => [
                    'unidad' => (string) ($tramite['unidad'] ?? ''),
                    'serie' => (string) ($tramite['serie'] ?? ''),
                    'placas' => (string) ($tramite['placas'] ?? ''),
                    'observaciones' => (string) ($tramite['observaciones'] ?? ''),
                    'derechos_tramite' => (float) ($tramite['derechos_tramite'] ?? 0),
                    'derechos_pago_sitio' => (string) ($tramite['derechos_pago_sitio'] ?? ''),
                    'derechos_pago_sitio_label' => $derechosPagoMap[(string) ($tramite['derechos_pago_sitio'] ?? '')] ?? (string) ($tramite['derechos_pago_sitio'] ?? ''),
                    'derechos_vigencia' => (string) ($tramite['derechos_vigencia'] ?? ''),
                    'derechos_revol_cliente' => (string) ($tramite['derechos_revol_cliente'] ?? ''),
                    'derechos_revol_cliente_label' => $derechosFormaMap[(string) ($tramite['derechos_revol_cliente'] ?? '')] ?? (string) ($tramite['derechos_revol_cliente'] ?? ''),
                    'derechos_refer_banc' => (string) ($tramite['derechos_refer_banc'] ?? ''),
                    'costo_tramite' => (float) ($tramite['costo_tramite'] ?? 0),
                    'deposito_gestor' => (float) ($tramite['deposito_gestor'] ?? 0),
                    'col_a_favor' => (float) ($tramite['col_a_favor'] ?? 0),
                    'num_factura_gestor' => (string) ($tramite['num_factura_gestor'] ?? ''),
                    'impuesto_gestoria' => (float) ($tramite['impuesto_gestoria'] ?? 0),
                    'gestoria_comision' => (float) ($tramite['gestoria_comision'] ?? 0),
                    'costo_paqueteria' => (float) ($tramite['costo_paqueteria'] ?? 0),
                    'gestor_total_pago' => (float) ($tramite['gestor_total_pago'] ?? 0),
                    'id_give_cliente' => (string) ($tramite['id_give_cliente'] ?? ''),
                    'numero_factura' => (string) ($tramite['numero_factura'] ?? ''),
                    'numero_refactura' => (string) ($tramite['numero_refactura'] ?? ''),
                    'evidencia_cobro_txt' => (string) ($tramite['evidencia_cobro_txt'] ?? ''),
                    'costo_gestoria' => number_format($sumDerechos, 2, '.', ''),
                    'costo_pago_cliente' => (float) ($tramite['costo_pago_cliente'] ?? 0),
                    'comision_derechos' => (float) ($tramite['comision_derechos'] ?? 0),
                    'iva' => number_format($ivaCalc, 2, '.', ''),
                    'costo_total' => number_format($costoTotalCalc, 2, '.', ''),
                ],
                'pago_derechos_docs' => $pagoDerechosDocs,
                'payment_docs' => $paymentDocs,
                'payment_docs_raw' => $paymentDocsRaw,
                'evidence_docs' => $evidenceDocs,
                'evidence_docs_raw' => $evidenceDocsRaw,
                'cobro_cliente_docs' => $cobroClienteDocs,
                'cobro_cliente_docs_raw' => $cobroClienteDocsRaw,
                'process_notes' => $this->getPrototypeEvidencias($id),
                'has_tramite_recibido' => $hasTramiteRecibido,
                'has_acuse_recibo' => $hasAcuseRecibo,
                'has_factura_gestor' => $hasFacturaGestor,
                'has_comprobante_pago' => $hasComprobantePago,
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error loading prototype readonly tramite ' . $id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Listado de trámites listos para Cobro a Cliente (Paso 5).
     * Filtra por evidencia en tra_pago_gestor (tramite_recibido + acuse_recibo_cliente).
     */
    public function cobro_cliente()
    {
        $queryString = (string) $this->request->getServer('QUERY_STRING');
        $target = '/deskapp/cobranza';
        if ($queryString !== '') {
            $target .= '?' . $queryString;
        }

        return redirect()->to($target);
    }

    public function cobro_cliente_ver($id)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/deskapp/tramitesn/tramite')
                ->with('error', 'ID de trámite inválido.');
        }

        // Si no tiene acceso por multi-tenancy, no enviarlo al listado de cobro.
        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'No tienes permiso para ver este tramite', '/deskapp/tramitesn/update/' . (int) $id, 403, false)) {
            log_unauthorized_access_attempt('tramite', $id);
            return $resp;
        }

        if (!has_permission('list_cobro_cliente', $perms, $roles)) {
            return redirect()->to('/deskapp/tramitesn/update/' . (int) $id)
            ->with('error', 'No tienes permisos para acceder a Cobranza');
        }

        // Asegurar que solo se muestre esta vista cuando el trámite está en Cobro a Cliente (28)
        $db = \Config\Database::connect();
        $tramite = $db->table('tramite')->select('tra_status_id')->where('id', (int) $id)->get()->getRowArray();
        $statusId = (int) ($tramite['tra_status_id'] ?? 0);
        if ($statusId !== SGL_TRA_STATUS_COBRO_CLIENTE) {
            return redirect()->to('/deskapp/tramitesn/update/' . (int) $id);
        }

        return redirect()->to('/deskapp/tramitesn/ver_seccion_cobro_cliente/' . (int) $id);
    }

    // =====================================================================
    // VISTAS SEPARADAS POR SECCIÓN (para auditoría / requerimientos futuros)
    // =====================================================================
    public function ver_seccion_generales($id)
    {
        return $this->update($id, 'deskapp/extra-pages/tramite_update_view_generales');
    }

    public function single_evidencias()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
        $isApi = (acl_wants_json($request) || $request->getGet('gc_state') !== null);

        if ($resp = acl_require_login('/', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/tramitesn/tramite', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/tramitesn/update/' . $tramite_id, 403, $isApi)) {
            return $resp;
        }

        $canQuickAction = has_permission('quick_action_bitacora', $perms, $roles);

        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id', $folio_tramite);

        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

        $canAdd = $canQuickAction && has_permission('quick_action_bitacora_add', $perms, $roles);
        $canEdit = $canQuickAction && has_permission('quick_action_bitacora_edit', $perms, $roles);
        $canDelete = $canQuickAction && has_permission('quick_action_bitacora_delete', $perms, $roles);

        // Bitácora debe seguir disponible durante el proceso; sólo se bloquea al cerrar/cancelar.
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true);
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($isApi) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Esta sección está en modo de solo lectura.']);
            }
            return redirect()->to('/deskapp/tramitesn/update/' . $tramite_id)->with('error', 'Esta sección está en modo de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramitesn/update/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramitesn/update/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramitesn/update/' . $tramite_id, $isApi);
        }

        if (!$folio_tramite) {
            throw new \Exception('No existe el folio');
        }

        $db = ConfigDatabase::connect();

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $hasTipoEvidenciaField = $db->fieldExists('tipo_evidencia', 'tra_evidencias');

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Bitacora', 'Bitacora');
        $crud->defaultOrdering('tra_evidencias.created_at', 'desc');

        if ($isLocked || !$canAdd) {
            $crud->unsetAdd();
        }
        if ($isLocked || !$canEdit) {
            $crud->unsetEdit();
        }
        if ($isLocked || !$canDelete) {
            $crud->unsetDelete();
        }

        $crud->fields(['folio_tramite', 'tramite_id', 'comentario', 'user_id']);
        $crud->columns(['created_at', 'id', 'comentario', 'user_id']);
        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
        $crud->where(['folio_tramite' => $folio_tramite]);
        if ($hasTipoEvidenciaField) {
            $crud->where(['tipo_evidencia' => 1]);
        }
        $crud->callbackColumn('comentario', function ($value) {
            $shortened_value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
            return '<span title="' . htmlspecialchars($value, ENT_QUOTES) . '">' . $shortened_value . '</span>';
        });

        $crud->callbackAfterInsert(function ($stateParameters) use ($self, $crud) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $db2 = $this->_getDbData();
                $data = $stateParameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);
                $folio_tramite = $session->get('folio_tramite_id');
                $myid = (int) ($session->get('id') ?? 0);

                $bitacoraModel = new BitacoraModel($db2);
                $diferencias = $self->encontrarDiferencias($data, []);
                $bitacoraModel->insert([
                    'id' => null,
                    'tipo' => 'insert',
                    'origen' => 'evidencia',
                    'folio_tramite' => $folio_tramite,
                    'tramite_id' => $tramite_id,
                    'cambios' => json_encode($diferencias),
                    'user_id' => $myid,
                ], 'bitacora');
            }

            return logOperation($stateParameters, $crud->getTable());
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self, $crud) {
            $db2 = $this->_getDbData();
            $session = session();
            $myid = (int) ($session->get('id') ?? 0);

            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $folio_tramite = $session->get('folio_tramite_id');

            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $self->encontrarDiferencias($stateParameters->data, []);
            $bitacoraModel->insert([
                'tipo' => 'update',
                'origen' => 'evidencia',
                'folio_tramite' => $folio_tramite,
                'tramite_id' => $tramite_id,
                'cambios' => json_encode($diferencias),
                'user_id' => $myid,
            ], 'bitacora');

            return logOperation($stateParameters, $crud->getTable());
        });

        $uploadValidations = [
            'maxUploadSize' => '20M',
            'minUploadSize' => '1K',
            'allowedFileTypes' => ['gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'],
        ];

        $crud->setFieldUploadMultiple('file', 'assets/uploads/evidencias/', '/assets/uploads/evidencias/', $uploadValidations);
        $crud->fieldType('user_id', 'hidden');
        $crud->fieldType('folio_tramite', 'hidden');
        $crud->fieldType('tramite_id', 'hidden');

        $crud->callbackBeforeInsert(function ($stateParameters) {
            $session = session();
            $stateParameters->data['folio_tramite'] = $session->get('folio_tramite_id');
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $stateParameters->data['folio_tramite'] = $session->get('folio_tramite_id');
            return $stateParameters;
        });
        $crud->callbackAddForm(function ($data) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);

            $data['user_id'] = (int) ($session->get('id') ?? 0);
            $data['folio_tramite'] = $session->get('folio_tramite_id');
            $data['tramite_id'] = $tramite_id;
            return $data;
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            return logOperation($stateParameters, $crud->getTable());
        });

        $salida = $crud->render();
        $salida2 = array_merge((array) $salida, $data);
        return $this->_example_output($salida2);
    }

    public function ver_seccion_asigna_gestor($id)
    {
        return $this->update($id, 'deskapp/extra-pages/tramite_update_view_asigna_gestor');
    }

    public function ver_seccion_pago_derechos($id)
    {
        return $this->update($id, 'deskapp/extra-pages/tramite_update_view_pago_derechos');
    }

    public function ver_seccion_pago_gestor($id)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/deskapp/tramitesn/tramite')
                ->with('error', 'ID de trámite inválido.');
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'No tienes permiso para ver este tramite', '/deskapp/tramitesn/update/' . (int) $id, 403, false)) {
            log_unauthorized_access_attempt('tramite', $id);
            return $resp;
        }

        $canNavigatePagoGestor = has_permission('section_pago_gestor', $perms, $roles)
            && has_permission('important_ir_pago_gestor', $perms, $roles);
        if (!$canNavigatePagoGestor) {
            return redirect()->to('/deskapp/tramitesn/update/' . (int) $id)
                ->with('error', 'No tienes permisos para acceder a Pago a Gestor');
        }

        return $this->update($id, 'deskapp/extra-pages/tramite_update_view_pago_gestor');
    }

	public function ver_seccion_evidencias_finales($id)
	{
		return $this->update($id, 'deskapp/extra-pages/tramite_update_view_evidencias_finales');
	}

    public function ver_seccion_cobro_cliente($id)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/deskapp/tramitesn/tramite')
                ->with('error', 'ID de trámite inválido.');
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'No tienes permiso para ver este tramite', '/deskapp/tramitesn/update/' . (int) $id, 403, false)) {
            log_unauthorized_access_attempt('tramite', $id);
            return $resp;
        }

        if (!has_permission('list_cobro_cliente', $perms, $roles)) {
            return redirect()->to('/deskapp/tramitesn/update/' . (int) $id)
            ->with('error', 'No tienes permisos para acceder a Cobranza');
        }

        return $this->update($id, 'deskapp/extra-pages/tramite_cobro_cliente_view');
    }

    /**
     * Versión nueva del update del trámite sin Grocery CRUD para el wizard.
     * Mantiene la misma lógica de negocio, pero la vista es 100% custom.
     */
    public function update($id, $viewName = null, ?string $onlySection = null)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

		$session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $myid = (int) ($session->get('id') ?? 0);
		[$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()->to('/deskapp/tramitesn/tramite')
                ->with('error', 'ID de trámite inválido.');
        }

        // ========================================================================
        // VALIDACIÓN DE ACCESO - MULTI-TENANCY
        // ========================================================================
        $from = strtolower((string) $this->request->getGet('from'));
        $tenantRedirect = ($from === 'search') ? '/deskapp/tramitesn/search' : '/deskapp/tramitesn/tramite';
        $tenantMessage = ($from === 'search') ? 'El ejecutivo no tiene acceso a ese recurso.' : '⛔ No tienes permiso para editar este trámite';

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, $tenantMessage, $tenantRedirect, 403, false)) {
            log_unauthorized_access_attempt('tramite', $id);
            return $resp;
        }

        $canEditTramite = can_edit_tramite($roles, $perms);
        $canEditPrincipal = $canEditTramite && has_permission('editar_tramite_principal', $perms, $roles);
        $canEditAsociado = $canEditTramite && has_permission('editar_tramite_asociado', $perms, $roles);
        $canDeleteAsociado = $canEditTramite && has_permission('delete_tramite_asociado', $perms, $roles);
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();

        // 1) Verificar/crear relación en tra_tramite_asociado (incluye tipo principal)
        $tramiteAsociadoModel = new TraTramiteAsociadoModel();
        $tramiteTmp = $builder->getWhere(['id' => $id])->getRowArray();
        $principalTipoId = (int) ($tramiteTmp['tra_tipos_id'] ?? 0);
        if ($principalTipoId > 0) {
            $principalExists = $tramiteAsociadoModel
                ->where('tramite_id', $id)
                ->where('tra_tipos_id', $principalTipoId)
                ->countAllResults();
            if ($principalExists == 0) {
                $tramiteAsociadoModel->saveService($id, $principalTipoId);
            }
        }

        // Recuperar el trámite
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if (!$tramite) {
            return redirect()->to('/deskapp/tramitesn/tramite')
                ->with('error', 'No se encontró el trámite solicitado');
        }

        // Concluido/Cancelado siempre debe mostrarse como solo lectura en el wizard.
        // Esto controla la UI (inputs disabled) además de los bloqueos por endpoint.
        $isLockedByStatus = in_array((int) ($tramite['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true);
        if ($isLockedByStatus) {
            $canEditTramite = false;
            $canEditPrincipal = false;
            $canEditAsociado = false;
            $canDeleteAsociado = false;
        }

        $statusId = (int) ($tramite['tra_status_id'] ?? 0);
        if ($viewName === null && ($onlySection === null || $onlySection === '')) {
            $targetAdvancedView = $this->resolveAdvancedStepView($statusId, $roles, $perms);
			if ($targetAdvancedView === 'deskapp/extra-pages/tramite_update_view_evidencias_finales') {
				return redirect()->to('/deskapp/tramitesn/ver_seccion_evidencias_finales/' . $id);
			}
            if ($targetAdvancedView === 'deskapp/extra-pages/tramite_update_view_pago_gestor') {
                return redirect()->to('/deskapp/tramitesn/ver_seccion_pago_gestor/' . $id);
            }
            if ($targetAdvancedView === 'deskapp/extra-pages/tramite_cobro_cliente_view') {
                return redirect()->to('/deskapp/tramitesn/ver_seccion_cobro_cliente/' . $id);
            }
        }

        // Sumatoria de derechos desde costos del tramite (principal + asociados)
        $sumDerechos = 0.0;

        // Catálogos
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();

        // Servicios asociados (incluye tipo principal)
        $servicesRaw = $tramiteAsociadoModel->getServicesByTramiteId($id);
        if (!empty($principalTipoId) && !empty($servicesRaw)) {
            $principalRows = [];
            $otherRows = [];
            foreach ($servicesRaw as $srv) {
                if ((int) ($srv['tra_tipos_id'] ?? 0) === (int) $principalTipoId) {
                    $principalRows[] = $srv;
                } else {
                    $otherRows[] = $srv;
                }
            }
            $servicesRaw = array_merge($principalRows, $otherRows);
        }
        $servicios_asociados = [];
        $servicios_tipos_ids = [];
        foreach ($servicesRaw as $srv) {
            $tipoId = (int) ($srv['tra_tipos_id'] ?? 0);
            if ($tipoId <= 0) {
                continue;
            }
            $rawCosto = $srv['costo_tramite'] ?? 0;
            $costoNum = is_numeric($rawCosto) ? (float) $rawCosto : 0.0;
            $costo = number_format($costoNum, 2, '.', '');
            $servicios_tipos_ids[] = $tipoId;
            $servicios_asociados[] = [
                'asociado_id' => (int) ($srv['id'] ?? 0),
                'tra_tipos_id' => $tipoId,
                'label' => $tra_tipos_options[$tipoId] ?? ('Tipo #' . $tipoId),
                'costo_tramite' => $costo,
            ];
            $sumDerechos += $costoNum;
        }
        $derechosTramiteFallback = is_numeric($tramite['derechos_tramite'] ?? null)
            ? (float) $tramite['derechos_tramite']
            : 0.0;
        if ($sumDerechos <= 0 && $derechosTramiteFallback > 0) {
            $sumDerechos = $derechosTramiteFallback;
        }
        $tramite['costo_gestoria'] = number_format($sumDerechos, 2, '.', '');

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();

        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();

        // Opciones dependientes (para que el wizard cargue con valores existentes)
        $cliEjecutivoModel = new ClienteDirectoEjecutivoModel($db2);
        $cli_ejecutivo_options = [];
        
        if (!empty($tramite['cli_directo_id'])) {
            $cli_ejecutivo_options = $cliEjecutivoModel->getEjecutivosOptions($tramite['cli_directo_id']);
        }

        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $gestor_model = new GestorModel($db2);
        $gestor_options = [];
        if (!empty($tramite['empresa_gestora_id'])) {
            $gestor_options = $gestor_model->getGestoresOptions($tramite['empresa_gestora_id']);
        }
        $gestor_nombre = $gestor_model->getGestorNameById($tramite['gestor_id']);

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj['tra_status'];
        $tra_status_steps = $tra_status_obj['steps'];

        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();

        $pago_derechos = new PagoDerechosModel($db2);
        $pago_derechos_db = $pago_derechos->getImgDerechosByTramiteId($id);

        $pago_gestor_db = [];
        try {
            $pago_gestor_db = $db->table('tra_pago_gestor')
                ->select('file, comprobante_final')
                ->where('tramite_id', (int) $id)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            $pago_gestor_db = [];
        }

        $hasComprobanteTramiteRecibido = false;
        $hasComprobanteAcuseRecibo = false;
        $hasFacturaGestor = false;
        $hasComprobantePago = false;
        $pagoGestorEvidenciasDb = [];
        $pagoGestorPagoDb = [];
        foreach ($pago_gestor_db as $rowDoc) {
            $tipo = (string) ($rowDoc['comprobante_final'] ?? '');
            if ($tipo === 'tramite_recibido') {
                $hasComprobanteTramiteRecibido = true;
                $pagoGestorEvidenciasDb[] = $rowDoc;
            } elseif ($tipo === 'acuse_recibo_cliente') {
                $hasComprobanteAcuseRecibo = true;
                $pagoGestorEvidenciasDb[] = $rowDoc;
            } elseif ($tipo === 'factura_gestor') {
                $hasFacturaGestor = true;
                $pagoGestorPagoDb[] = $rowDoc;
            } elseif ($tipo === 'comprobante_pago') {
                $hasComprobantePago = true;
                $pagoGestorPagoDb[] = $rowDoc;
            } else {
                $pagoGestorPagoDb[] = $rowDoc;
            }
        }

        $final_docs_db = [
            16 => null,
            17 => null,
        ];
        try {
            $rowsFinalDocs = $db->table('tra_doc_status')
                ->select('id, documento_id, file')
                ->where('tramite_id', (int) $id)
                ->whereIn('documento_id', [16, 17])
                ->where('status', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($rowsFinalDocs as $rowDoc) {
                $docId = (int) ($rowDoc['documento_id'] ?? 0);
                if ($docId > 0 && isset($final_docs_db[$docId]) && $final_docs_db[$docId] === null) {
                    $final_docs_db[$docId] = $rowDoc;
                }
            }
        } catch (\Exception $e) {
            $final_docs_db = [16 => null, 17 => null];
        }

        $pago_gestor_st = new PagoGestorStatusModel($db2);
        $pago_gestor_st_opciones = $pago_gestor_st->getPagoGestorStatusOptions();
        $statusDoctosGestorOptions = [
            'en proceso' => 'En Proceso',
            'entregados' => 'Entregados',
        ];

        $canKeepStep4Editable = $this->canKeepStep4Editable(
            (int) ($tramite['reembolso_status_id'] ?? 0),
            (int) ($tramite['pago_gestor_st_id'] ?? 0),
            $pago_gestor_st_opciones,
            (string) ($tramite['status_doctos_gestor'] ?? '')
        );

        $form = new \stdClass();

        // Campos Paso 1: Datos generales
        $form->fields = [
            'folio' => [
                'label' => 'Folio',
                'type'  => 'hidden',
                'value' => $tramite['folio'],
            ],
            'contrato' => [
                'label'    => 'Contrato',
                'type'     => 'text',
                'value'    => $tramite['contrato'],
                'required' => true,
            ],
            'unidad' => [
                'label' => 'Unidad',
                'type'  => 'text',
                'value' => $tramite['unidad'],
            ],
            'serie' => [
                'label' => 'Serie',
                'type'  => 'text',
                'value' => $tramite['serie'],
            ],
            'placas' => [
                'label' => 'Placas',
                'type'  => 'text',
                'value' => $tramite['placas'],
            ],
            'cli_directo_id' => [
                'label'   => 'Cliente',
                'type'    => 'select',
                'options' => $cli_directo_options,
                'value'   => $tramite['cli_directo_id'],
            ],
            'cli_directo_ejecutivo_id' => [
                'label'   => 'Ejecutivo de Cliente',
                'type'    => 'select',
                'options' => $cli_ejecutivo_options,
                'value'   => $tramite['cli_directo_ejecutivo_id'],
            ],
            'entidad_id' => [
                'label'    => 'Entidad',
                'type'     => 'select',
                'options'  => $entidad_options,
                'value'    => $tramite['entidad_id'],
                'required' => true,
            ],
            'observaciones' => [
                'label' => 'Observaciones',
                'type'  => 'textarea',
                'value' => $tramite['observaciones'],
            ],
        ];

        // Campos Paso 2: Asignación gestor / empresa gestora
        $form->gestor_campos = [
            'empresa_gestora_id' => [
                'label'    => 'Empresa Gestora',
                'type'     => 'select',
                'options'  => $empresa_gestora_options,
                'value'    => $tramite['empresa_gestora_id'],
                'required' => true,
            ],
            'gestor_id' => [
                'label'    => 'Gestor',
                'type'     => 'select',
                'options'  => $gestor_options,
                'value'    => $tramite['gestor_id'],
                'required' => true,
            ],
        ];

        // Campos Paso 3: Derechos base
        $form->derechos_campos = [
            'derechos_tramite' => [
                'label'    => 'Monto pago de derechos',
                'type'     => 'number',
                'value'    => $tramite['derechos_tramite'],
                'required' => true,
            ],
            'derechos_pago_sitio' => [
                'label'   => 'Pago',
                'type'    => 'select',
                'options' => [
                    'online'    => 'En Línea',
                    'ventanilla'=> 'En Ventanilla',
                ],
                'value'   => $tramite['derechos_pago_sitio'],
            ],
            'derechos_vigencia' => [
                'label' => 'Fecha Vigencia',
                'type'  => 'datetime',
                'value' => $tramite['derechos_vigencia'],
            ],
            'derechos_revol_cliente' => [
                'label'    => 'Forma de Pago',
                'type'     => 'select',
                'options'  => [
                    'revolvente' => 'Fondo Revolvente',
                    'cliente'    => 'Pago Cliente',
                ],
                    'documentTypeMeta' => !empty($prototypeReadOnlyTramite['step1_document_option_meta']) && is_array($prototypeReadOnlyTramite['step1_document_option_meta'])
                        ? $prototypeReadOnlyTramite['step1_document_option_meta']
                        : [],
                'value'    => $tramite['derechos_revol_cliente'],
                'required' => true,
            ],
            'derechos_refer_banc' => [
                'label'    => 'Referencia Bancaria',
                'type'     => 'text',
                'value'    => $tramite['derechos_refer_banc'],
                'required' => true,
            ],
        ];

        $labelOrId = static function ($options, $value) {
            if ($value === null || $value === '') {
                return '';
            }
            if (is_array($options) && array_key_exists($value, $options)) {
                return $options[$value];
            }
            return 'ID ' . $value;
        };
        $derechosPagoMap = [
            'online' => 'En Linea',
            'ventanilla' => 'En Ventanilla',
        ];
        $derechosFormaMap = [
            'revolvente' => 'Fondo Revolvente',
            'cliente' => 'Pago Cliente',
        ];

        $readonly_step1 = [
            ['label' => 'Contrato', 'value' => $tramite['contrato']],
            ['label' => 'Unidad', 'value' => $tramite['unidad']],
            ['label' => 'Serie', 'value' => $tramite['serie']],
            ['label' => 'Placas', 'value' => $tramite['placas']],
            ['label' => 'Cliente', 'value' => $labelOrId($cli_directo_options, $tramite['cli_directo_id'])],
            ['label' => 'Ejecutivo de Cliente', 'value' => $labelOrId($cli_ejecutivo_options, $tramite['cli_directo_ejecutivo_id'])],
            ['label' => 'Entidad', 'value' => $labelOrId($entidad_options, $tramite['entidad_id'])],
            ['label' => 'Observaciones', 'value' => $tramite['observaciones']],
        ];
        $readonly_step2 = [
            ['label' => 'Empresa Gestora', 'value' => $labelOrId($empresa_gestora_options, $tramite['empresa_gestora_id'])],
            ['label' => 'Gestor', 'value' => $gestor_nombre],
        ];
        $readonly_step3 = [
            ['label' => 'Monto pago de derechos', 'value' => $tramite['derechos_tramite']],
            ['label' => 'Pago', 'value' => $derechosPagoMap[$tramite['derechos_pago_sitio']] ?? ($tramite['derechos_pago_sitio'] ?? '')],
            ['label' => 'Fecha Vigencia', 'value' => $tramite['derechos_vigencia']],
            ['label' => 'Forma de Pago', 'value' => $derechosFormaMap[$tramite['derechos_revol_cliente']] ?? ($tramite['derechos_revol_cliente'] ?? '')],
            ['label' => 'Referencia Bancaria', 'value' => $tramite['derechos_refer_banc']],
        ];

        // Campos Paso 4: Pago a Gestor (custom, sin Grocery CRUD)
        $form->pago_gestor_campos = [
            'gestor_name' => [
                'label' => 'Gestor',
                'type' => 'text',
                'value' => $gestor_nombre,
                'readonly' => true,
            ],
            'costo_tramite' => [
                'label' => 'Costo del Tramite',
                'type' => 'number',
                'value' => $tramite['costo_tramite'],
            ],
            'deposito_gestor' => [
                'label' => 'Deposito a Gestor',
                'type' => 'number',
                'value' => $tramite['deposito_gestor'],
            ],
            'col_a_favor' => [
                'label' => 'Saldo Pendiente',
                'type' => 'number',
                'value' => $tramite['col_a_favor'],
            ],
            'num_factura_gestor' => [
                'label' => 'Numero de Factura',
                'type' => 'text',
                'value' => $tramite['num_factura_gestor'],
            ],
            'pago_gestor_st_id' => [
                'label' => 'Estatus del Pago',
                'type' => 'select',
                'options' => $pago_gestor_st_opciones,
                'value' => $tramite['pago_gestor_st_id'],
                'required' => true,
            ],
            'status_doctos_gestor' => [
                'label' => 'Estatus de Documentos',
                'type' => 'select',
                'options' => $statusDoctosGestorOptions,
                'value' => !empty($tramite['status_doctos_gestor']) ? $tramite['status_doctos_gestor'] : 'en proceso',
                'required' => true,
            ],
            'impuesto_gestoria' => [
                'label' => 'Honorarios de Gestoria',
                'type' => 'number',
                'value' => $tramite['impuesto_gestoria'],
            ],
            'gestoria_comision' => [
                'label' => 'Gratificacion',
                'type' => 'number',
                'value' => $tramite['gestoria_comision'],
            ],
            'costo_paqueteria' => [
                'label' => 'Costo Paqueteria',
                'type' => 'number',
                'value' => $tramite['costo_paqueteria'] ?? 0,
            ],
            'gestor_total_pago' => [
                'label' => 'Pago Total',
                'type' => 'number',
                'value' => $tramite['gestor_total_pago'],
            ],
            'reembolso_status_id' => [
                'label' => 'Estatus del Reembolso',
                'type' => 'select',
                'options' => $reembolso_status_options,
                'value' => $tramite['reembolso_status_id'],
                'required' => true,
            ],
        ];

        $baseIva = 0.0;
        $baseIva += is_numeric($tramite['costo_pago_cliente']) ? (float) $tramite['costo_pago_cliente'] : 0.0;
        $baseIva += is_numeric($tramite['comision_derechos']) ? (float) $tramite['comision_derechos'] : 0.0;
        $ivaCalc = round($baseIva * 0.16, 2);
        $costoTotalCalc = round($sumDerechos + $baseIva + $ivaCalc, 2);

        $tramite['iva'] = number_format($ivaCalc, 2, '.', '');
        $tramite['costo_total'] = number_format($costoTotalCalc, 2, '.', '');

        $form->final_campos = [
            'id_give_cliente' => [
                'label' => 'ID que da el cliente',
                'type' => 'text',
                'value' => $tramite['id_give_cliente'],
                'required' => 'required',
            ],
            'separador_costos' => [
                'type' => 'hr',
            ],
            'numero_factura' => [
                'label' => 'Numero de Factura',
                'type' => 'text',
                'value' => $tramite['numero_factura'],
                'required' => 'required',
            ],
            'numero_refactura' => [
                'label' => 'Numero de Refactura',
                'type' => 'text',
                'value' => $tramite['numero_refactura'],
            ],
            'cobro_status_id' => [
                'label' => 'Estatus del Cobro',
                'type' => 'select',
                'options' => $cobro_status_options,
                'value' => $tramite['cobro_status_id'],
                'native' => true,
            ],
            'evidencia_cobro_txt' => [
                'label' => 'Evidencia de cobro',
                'type' => 'textarea',
                'value' => $tramite['evidencia_cobro_txt'] ?? '',
            ],
            'separador_costos2' => [
                'type' => 'hr',
            ],
            'costo_gestoria' => [
                'label' => 'Sumatoria de Derechos',
                'type' => 'number',
                'value' => $tramite['costo_gestoria'],
                'disabled' => 'disabled',
            ],
            'costo_gestoria_hidden' => [
                'label' => 'Sumatoria de Derechos',
                'type' => 'hidden',
                'value' => $tramite['costo_gestoria'],
            ],
            'costo_pago_cliente' => [
                'label' => 'Honorarios del Tramite',
                'type' => 'number',
                'value' => $tramite['costo_pago_cliente'],
                'required' => 'required',
            ],
            'comision_derechos' => [
                'label' => 'Comision de Derechos',
                'type' => 'number',
                'value' => $tramite['comision_derechos'],
                'required' => 'required',
            ],
            'iva' => [
                'label' => 'IVA ($)',
                'type' => 'number',
                'value' => $tramite['iva'],
            ],
            'costo_total' => [
                'label' => 'Costo Total',
                'type' => 'number',
                'value' => $tramite['costo_total'],
                'disabled' => 'disabled',
            ],
        ];

        // Flags de completado por paso
        $step1Complete = !empty($tramite['contrato']) && !empty($tramite['entidad_id']);
        $step2Complete = !empty($tramite['empresa_gestora_id']) && !empty($tramite['gestor_id']);
        $step3Complete = !empty($tramite['derechos_tramite']) && !empty($tramite['derechos_revol_cliente']) && !empty($tramite['derechos_refer_banc']);

        $canUploadDerechos = $canEditTramite && puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'step3_upload', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 3);
        $canSectionPagoDerechos = has_permission('section_pago_derechos', $perms, $roles);
        $canSectionPagoGestor = has_permission('section_pago_gestor', $perms, $roles);
        $canSectionFinalCostos = has_permission('section_final_costos', $perms, $roles);
        $canNavigatePagoGestor = $canSectionPagoGestor
            && has_permission('important_ir_pago_gestor', $perms, $roles);
        $canNavigateCobroCliente = has_permission('list_cobro_cliente', $perms, $roles);

        $canEditPagoGestor = $canEditTramite
            && $canSectionPagoGestor
            && has_permission('editar_pago_gestor', $perms, $roles)
            && ($canKeepStep4Editable || puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'editar_pago_gestor', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 4));
        // Uploads (Dropzone/GroceryCRUD) NO dependen de `editar_tramite`.
        // Los endpoints single_* controlan escritura por permisos finos + puede_editar_modulo().
        $canUploadPagoGestor = $canSectionPagoGestor
            && has_permission('editar_pago_gestor', $perms, $roles)
            && ($canKeepStep4Editable || puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'upload_pago_gestor', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 4));
        $canEditFinalForm = $canSectionFinalCostos
            && has_permission('editar_final', $perms, $roles)
            && puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'botones', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 5);
        $canUploadFinalDocs = $canSectionFinalCostos
            && puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'upload_cobro_cliente', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 5);

        // Permisos finos para Dropzones: sin el permiso, el upload queda en solo-lectura
        $canUploadDropzonePagoDerechos = $canUploadDerechos && has_permission('can_upload_dropzone_pago_derechos', $perms, $roles);
        $canUploadDropzoneEvidenciasFinales = $canUploadPagoGestor && has_permission('can_upload_dropzone_evidencias_finales', $perms, $roles);
        $canUploadDropzonePagoGestorDocumentos = $canUploadPagoGestor && has_permission('can_upload_dropzone_pago_gestor_documentos', $perms, $roles);
        $canUploadDropzoneCobroCliente = $canUploadFinalDocs && has_permission('can_upload_dropzone_cobro_cliente', $perms, $roles);

        if (isset($tramite['costo_tramite']) && $tramite['costo_tramite'] > 0) {
            $tramite['costo_tramite'] = number_format($tramite['costo_tramite'], 2, '.', '');
        } else {
            $tramite['costo_tramite'] = 0;
        }

        $data['id'] = $id;
        $data['folio'] = $tramite['folio'];
        $data['contrato'] = $tramite['contrato'];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']] ?? '';
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];
        $data['step'] = $tra_status_steps[$tramite['tra_status_id']] ?? 1;
        $data['started_at'] = $tramite['started_at'];
        $data['derechos_comprobante'] = $tramite['derechos_comprobante'];
        $data['reembolso_status_id'] = $tramite['reembolso_status_id'];
        $data['cobro_status_id'] = $tramite['cobro_status_id'];
        $data['sumatoria_derechos'] = $sumDerechos;

        $data['tipo_tramite'] = $tra_tipos_options[$tramite['tra_tipos_id']] ?? 'N/A';
        $data['cliente'] = $cli_directo_options[$tramite['cli_directo_id']] ?? 'N/A';
        $data['gestor'] = $gestor_nombre ?? 'Sin asignar';
        $data['empresa_gestora'] = $empresa_gestora_options[$tramite['empresa_gestora_id']] ?? 'Sin asignar';

        $form->id = $id;

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        // Grocery CRUD suele traer Bootstrap (v4) dentro de css_files.
        // En este wizard usamos el Bootstrap del layout (v5); cargar otro Bootstrap después
        // descuadra el header/sidebar. Filtramos Bootstrap aquí sin afectar los demás assets.
        $cssFiles = (array) ($crudOutput->css_files ?? []);
        $cssFiles = array_values(array_filter($cssFiles, static function ($file) {
            $file = (string) $file;
            return stripos($file, 'bootstrap') === false;
        }));
        $form->css_files = $cssFiles;
        $form->js_files = $crudOutput->js_files;

        $isLockedByStatus = in_array((int) ($tramite['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true);
        // En el wizard, la selección Tramites vs Concluido depende del estatus (concluido/cancelado).
        // Los permisos finos de escritura se resuelven dentro de cada endpoint single_*.
        $isLocked = $isLockedByStatus;

        $historyCrudBasePath = $isLocked ? '/deskapp/concluido' : '/deskapp/tramites';

        $cruddocstatus = $this->_getGroceryCrudEnterprise();
        $cruddocstatus->setApiUrlPath($historyCrudBasePath . '/single_documentostatus/' . $id);
        $output_docs = $cruddocstatus->render();

        $crudbitacora = $this->_getGroceryCrudEnterprise();
        $crudbitacora->setApiUrlPath('/deskapp/tramitesn/single_evidencias/' . $id);
        $outputbitacora = $crudbitacora->render();

        $crud_derechos = $this->_getGroceryCrudEnterprise();
        $crud_derechos->setApiUrlPath($historyCrudBasePath . '/single_pago_derechos/' . $id);
        $output_derechos = $crud_derechos->render();

        $crud_pago_gestor = $this->_getGroceryCrudEnterprise();
        $canWritePagoGestorCrud = !$isLocked
            && $canUploadPagoGestor;
        if ($canWritePagoGestorCrud) {
            $crud_pago_gestor->setApiUrlPath('/deskapp/tramites/single_pago_gestor/' . $id);
        } else {
            $crud_pago_gestor->setApiUrlPath('/deskapp/concluido/single_pago_gestor/' . $id);
        }
        $output_pago_gestor = $crud_pago_gestor->render();

        $crud_cobro_cliente = $this->_getGroceryCrudEnterprise();
        $canWriteCobroClienteCrud = !$isLockedByStatus
            && $canSectionFinalCostos
            && puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'upload_cobro_cliente', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 5);
        if ($canWriteCobroClienteCrud) {
            $crud_cobro_cliente->setApiUrlPath('/deskapp/tramites/single_cobro_cliente/' . $id);
        } else {
            $crud_cobro_cliente->setApiUrlPath('/deskapp/concluido/single_cobro_cliente/' . $id);
        }
        $output_cobro_cliente = $crud_cobro_cliente->render();

        $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
        $canWriteEvidenciasFinalesCrud = !$isLockedByStatus
            && $canSectionFinalCostos
            && puede_editar_modulo($roles, (int) $tramite['tra_status_id'], 'evidencias_finales_gestor', (int) $tramite['reembolso_status_id'], (int) $tramite['cobro_status_id'], 4);
        if ($canWriteEvidenciasFinalesCrud) {
            $crudevidencias_finales->setApiUrlPath('/deskapp/tramites/single_evidencias_finales/' . $id);
        } else {
            $crudevidencias_finales->setApiUrlPath('/deskapp/concluido/single_evidencias_finales/' . $id);
        }
        $outputevidencias_finales = $crudevidencias_finales->render();

        $form->output_docs = $output_docs->output;
        $form->output_bitacora = $outputbitacora->output;
        $form->outputevidencias_finales = $outputevidencias_finales->output;
        $form->output_derechos = $output_derechos->output;
        $form->output_pago_gestor = $output_pago_gestor->output;
        $form->output_cobro_cliente = $output_cobro_cliente->output;

        // Fusionar datos para la vista nueva (sin Grocery CRUD)
        $viewData = array_merge((array) $form, $data);
        $viewData['tra_tipos_options'] = $tra_tipos_options;
        $viewData['principal_tipo_id'] = (int) ($tramite['tra_tipos_id'] ?? 0);
        $viewData['servicios_asociados'] = $servicios_asociados;
        $viewData['servicios_tipos_ids'] = array_values(array_unique($servicios_tipos_ids));
		$viewData['can_edit_principal'] = $canEditPrincipal;
		$viewData['can_edit_asociado'] = $canEditAsociado;
		$viewData['can_delete_asociado'] = $canDeleteAsociado;
        $viewData['user_roles'] = $roles;
        $viewData['user_permissions'] = $perms;
        $viewData['can_edit_tramite'] = $canEditTramite;
        $viewData['pago_derechos_db'] = $pago_derechos_db;
        $viewData['pago_gestor_db'] = $pago_gestor_db;
        $viewData['pago_gestor_evidencias_db'] = $pagoGestorEvidenciasDb;
        $viewData['pago_gestor_pago_db'] = $pagoGestorPagoDb;
        $viewData['has_comprobante_tramite_recibido'] = $hasComprobanteTramiteRecibido;
        $viewData['has_comprobante_acuse_recibo'] = $hasComprobanteAcuseRecibo;
        $viewData['has_factura_gestor'] = $hasFacturaGestor;
        $viewData['has_comprobante_pago'] = $hasComprobantePago;
        $viewData['final_docs_db'] = $final_docs_db;
        $viewData['pago_gestor_campos'] = $form->pago_gestor_campos;
        $viewData['final_campos'] = $form->final_campos;
        $viewData['readonly_step1'] = $readonly_step1;
        $viewData['readonly_step2'] = $readonly_step2;
        $viewData['readonly_step3'] = $readonly_step3;
        $viewData['step1_complete'] = $step1Complete;
        $viewData['step2_complete'] = $step2Complete;
        $viewData['step3_complete'] = $step3Complete;
        $viewData['can_upload_derechos'] = $canUploadDerechos;
        $viewData['can_upload_dropzone_pago_derechos'] = $canUploadDropzonePagoDerechos;
        $viewData['can_section_pago_derechos'] = $canSectionPagoDerechos;
        $viewData['can_section_pago_gestor'] = $canSectionPagoGestor;
        $viewData['can_section_final_costos'] = $canSectionFinalCostos;
        $viewData['can_navigate_pago_gestor'] = $canNavigatePagoGestor;
        $viewData['can_navigate_cobro_cliente'] = $canNavigateCobroCliente;
        $viewData['can_edit_pago_gestor'] = $canEditPagoGestor;
        $viewData['can_upload_pago_gestor'] = $canUploadPagoGestor;
        $viewData['can_upload_dropzone_evidencias_finales'] = $canUploadDropzoneEvidenciasFinales;
        $viewData['can_upload_dropzone_pago_gestor_documentos'] = $canUploadDropzonePagoGestorDocumentos;
        $viewData['can_edit_final_form'] = $canEditFinalForm;
        $viewData['can_upload_final_docs'] = $canUploadFinalDocs;
        $viewData['can_upload_dropzone_cobro_cliente'] = $canUploadDropzoneCobroCliente;
        $viewData['has_pending_pago_conciliation'] = $this->hasPendingPagoConciliation($id);
        if ($onlySection !== null && $onlySection !== '') {
            $viewData['only_section'] = $onlySection;
        }

        $targetView = $viewName ?: 'deskapp/extra-pages/tramite_update_view_nuevo';
        return view($targetView, $viewData);
    }

    public function upload_final_doc($tramiteId = null, $documentoId = null)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $tramiteId = (int) $tramiteId;
        $documentoId = (int) $documentoId;
        if ($tramiteId <= 0 || !in_array($documentoId, [16, 17], true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Parámetros inválidos.']);
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }

        if ($resp = acl_require_permission('section_final_costos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $tramiteRow = $this->getTramiteRowWithFolioAndStatuses($tramiteId);
        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado.']);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        if ($this->isLockedStatusId($traStatusId)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está concluido o cancelado.']);
        }
        if (!puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        if (empty($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo.']);
        }

        $tempFile = (string) ($_FILES['file']['tmp_name'] ?? '');
        $originalName = (string) ($_FILES['file']['name'] ?? '');

        // Persist through the storage abstraction (local disk or S3 depending on
        // FILE_STORAGE_DRIVER). "documentostatus" is a flat category (no per-id
        // segment); the legacy tra_doc_status.file column stores the bare filename.
        $key = buildKey('documentostatus', null, $originalName);
        $fileName = basename($key);

        $storage = service('fileStorage');
        if (!$storage->put($key, $tempFile)) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo guardar el archivo.']);
        }

        try {
            $existingRows = $db->table('tra_doc_status')
                ->select('id, file')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->where('status', 1)
                ->get()
                ->getResultArray();

            foreach ($existingRows as $existing) {
                $existingFile = trim((string) ($existing['file'] ?? ''));
                if ($existingFile !== '' && strpos($existingFile, '..') === false) {
                    $existingKey = keyFromStored($existingFile, 'documentostatus');
                    if ($existingKey !== '') {
                        $storage->delete($existingKey);
                    }
                }
            }

            $db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId)
                ->delete();

            $comentario = 'se sube documento desde dropzone de paso 4';
            $insertData = [
                'folio_tramite' => (string) ($tramiteRow['folio'] ?? ''),
                'tramite_id' => $tramiteId,
                'documento_id' => $documentoId,
                'status_documento_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
                'file' => $fileName,
                'comentario' => $comentario,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'status' => 1,
            ];
            $db->table('tra_doc_status')->insert($insertData);

            $filePath = file_url($fileName, 'documentostatus');
            if ($filePath === '') {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo resolver la URL del archivo.']);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento subido correctamente.',
                'filePath' => $filePath,
                'fileName' => $fileName,
                'documento_id' => $documentoId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en upload_final_doc: ' . $e->getMessage());

            // Compensating action: the object was already persisted by put()
            // but the DB write failed. Delete the just-written key exactly once
            // so the store never accumulates an object with no referencing row.
            $compensated = false;
            try {
                $compensated = (bool) $storage->delete($key);
            } catch (\Throwable $deleteError) {
                $compensated = false;
                log_message('error', 'Fallo al ejecutar delete compensatorio en upload_final_doc para key: ' . $key . ' - ' . $deleteError->getMessage());
            }

            if (!$compensated) {
                // The compensating delete could not remove the object. Record the
                // orphaned key clearly so it can be identified for later cleanup.
                log_message('error', 'ORPHANED_S3_KEY upload_final_doc: no se pudo eliminar el objeto huérfano tras fallo de DB. key=' . $key);
            }

            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo persistir el documento subido.']);
        }
    }

    public function delete_final_doc()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = $this->normalizeRolesPermsFromSession();

        $request = \Config\Services::request();
        $tramiteId = (int) $request->getPost('tramite_id');
        $documentoId = (int) $request->getPost('documento_id');
        $fileName = trim((string) $request->getPost('file'));

        if ($tramiteId <= 0 || !in_array($documentoId, [16, 17], true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Parámetros inválidos.']);
        }

        if ($resp = $this->requireJsonTenantAccess($tramiteId, $userId, $roles)) {
            return $resp;
        }

        if ($resp = acl_require_permission('section_final_costos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $tramiteRow = $this->getTramiteRowWithStatuses($tramiteId);
        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado.']);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        if ($this->isLockedStatusId($traStatusId)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está concluido o cancelado.']);
        }
        if (!puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        try {
            $builder = $db->table('tra_doc_status');
            $builder->where('tramite_id', $tramiteId);
            $builder->where('documento_id', $documentoId);
            if ($fileName !== '') {
                if ($fileName !== basename($fileName) || strpos($fileName, '..') !== false || strpos($fileName, "\0") !== false) {
                    return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre de archivo inválido.']);
                }
                $builder->where('file', $fileName);
            }

            $rows = $builder->get()->getResultArray();
            if (empty($rows)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se encontró documento para eliminar.']);
            }

            // Eliminar el/los objeto(s) a través del servicio de almacenamiento (Req 6.4).
            // documentostatus es plano (sin id). Req 6.7: si delete() falla o lanza
            // excepción, se retorna 500 y NO se elimina la fila de BD (la referencia
            // existente se conserva intacta).
            helper('filestorage');
            $storage = service('fileStorage');
            foreach ($rows as $row) {
                $file = trim((string) ($row['file'] ?? ''));
                if ($file !== '' && $file === basename($file) && strpos($file, '..') === false) {
                    $key = keyFromStored($file, 'documentostatus');
                    if (!$storage->delete($key)) {
                        return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor.']);
                    }
                }
            }

            $deleteBuilder = $db->table('tra_doc_status')
                ->where('tramite_id', $tramiteId)
                ->where('documento_id', $documentoId);
            if ($fileName !== '') {
                $deleteBuilder->where('file', $fileName);
            }
            $deleteBuilder->delete();

            return $this->response->setJSON(['success' => true, 'message' => 'Documento eliminado correctamente.']);
        } catch (\Throwable $e) {
            log_message('error', 'Error en delete_final_doc: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al eliminar documento.']);
        }
    }

    public function encontrarDiferencias($datos1, $datos2)
    {
        $diferencias = [];
        foreach ($datos1 as $clave => $valor) {
            if (array_key_exists($clave, $datos2) && $datos2[$clave] !== $valor) {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => $datos2[$clave]
                ];
            } else {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => ''
                ];
            }
        }
        return $diferencias;
    }

    private function buildBitacoraChanges(array $changes)
    {
        $diferencias = [];
        foreach ($changes as $field => $values) {
            $diferencias[$field] = [
                'valor_original' => $values['old'] ?? null,
                'valor_nuevo' => $values['new'] ?? null,
            ];
        }
        return $diferencias;
    }

    private function updateCobrarClienteFlagTramitesn($db, int $tramiteId): void
    {
        if ($tramiteId <= 0) {
            return;
        }

        $rows = $db->table('tra_pago_gestor')
            ->select('comprobante_final')
            ->where('tramite_id', $tramiteId)
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $hasFacturaGestor = false;
        $hasComprobantePago = false;
        foreach ($rows as $row) {
            $tipo = (string) ($row['comprobante_final'] ?? '');
            if ($tipo === 'factura_gestor') {
                $hasFacturaGestor = true;
            } elseif ($tipo === 'comprobante_pago') {
                $hasComprobantePago = true;
            }
        }

        $db->table('tramite')
            ->where('id', $tramiteId)
            ->update(['cobrar_cliente' => ($hasFacturaGestor && $hasComprobantePago) ? 1 : 0]);
    }
}

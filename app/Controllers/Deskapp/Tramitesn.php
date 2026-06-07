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

            $shouldValidateDuplicates = $currentStep <= 1
                || array_key_exists('serie', $data)
                || array_key_exists('tra_tipos_id', $data);
            if ($shouldValidateDuplicates) {
                $duplicateSerie = trim((string) ($data['serie'] ?? ($existingTramite['serie'] ?? '')));
                $duplicateTipoId = (int) ($data['tra_tipos_id'] ?? ($existingTramite['tra_tipos_id'] ?? 0));

                if ($duplicateSerie !== '' && $duplicateTipoId > 0) {
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
                'tramite_id' => (int) $id,
                'cambios' => json_encode($diferencias),
                'user_id' => (int) $myid,
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                'tramite_id' => (int) $id,
                'user_id' => (int) $myid,
                'tra_status_id' => $statusUpdatedTo > 0 ? $statusUpdatedTo : SGL_TRA_STATUS_RECOLECCION_DCTOS,
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            if (!empty($changes)) {
                try {
                    $changeCount = log_tramite_bulk_changes($id, $changes, 'tramite', [
                        'form_name' => 'Datos Generales',
                        'form_step' => 1,
                        'form_section' => 'update_save',
                    ]);
                    log_message('info', "[Tramitesn::update_save] Registrados {$changeCount} cambios para trámite ID: {$id}");
                } catch (\Throwable $e) {
                    log_message('error', 'Error en log_tramite_bulk_changes (Tramitesn::update_save): ' . $e->getMessage());
                }

                try {
                    $cambiosTexto = implode(', ', array_keys($changes));
                    notify_tramite_actualizado($id, $folio ?? "Trámite #{$id}", $cambiosTexto, $myid);
                } catch (\Throwable $e) {
                    log_message('error', 'Error en notify_tramite_actualizado (Tramitesn::update_save): ' . $e->getMessage());
                }
            }

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
        $canOverrideStatus28 = has_permission('override_tramite_status_28_readonly', $perms, $roles);

        if ($this->isLockedStatusId($traStatusId) || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideStatus28 && !$canKeepStep4Editable)) {
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

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

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

        if (empty($_FILES['file'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo.']);
        }

        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/documentostatus';
        $targetPath = FCPATH . $storeFolder . $ds;
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        $tempFile = $_FILES['file']['tmp_name'];
        $originalName = (string) ($_FILES['file']['name'] ?? '');
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $baseName);
        $safeBase = trim((string) $safeBase, '_');
        if ($safeBase === '') {
            $safeBase = 'documento';
        }
        try {
            $random = bin2hex(random_bytes(8));
        } catch (\Exception $e) {
            $random = uniqid();
        }
        $fileName = $safeBase . '_' . $tramiteId . '_' . $documentoId . '_' . $random . ($extension !== '' ? '.' . $extension : '');
        $targetFile = $targetPath . $fileName;

        if (!move_uploaded_file($tempFile, $targetFile)) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo.']);
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
                if ($existingFile !== '' && $existingFile === basename($existingFile) && strpos($existingFile, '..') === false) {
                    $existingPath = $targetPath . $existingFile;
                    if (is_file($existingPath)) {
                        @unlink($existingPath);
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

            $filePath = base_url('/assets/uploads/documentostatus/' . $fileName);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento subido correctamente.',
                'filePath' => $filePath,
                'fileName' => $fileName,
                'documento_id' => $documentoId,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en upload_final_doc: ' . $e->getMessage());
            @unlink($targetFile);
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al guardar el documento.']);
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

            $ds = DIRECTORY_SEPARATOR;
            $targetPath = FCPATH . 'assets/uploads/documentostatus' . $ds;

            foreach ($rows as $row) {
                $file = trim((string) ($row['file'] ?? ''));
                if ($file !== '' && $file === basename($file) && strpos($file, '..') === false) {
                    $fullPath = $targetPath . $file;
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
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

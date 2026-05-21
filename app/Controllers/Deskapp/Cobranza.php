<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Services\CobranzaDashboardService;
use App\Services\CobranzaExpedienteService;
use Config\Services;

class Cobranza extends BaseController
{
    private CobranzaDashboardService $dashboardService;

    private CobranzaExpedienteService $expedienteService;

    public function __construct()
    {
        helper(['acl_guard', 'cliente_context', 'cliente_filter', 'permissions', 'url']);
        $this->dashboardService = new CobranzaDashboardService();
        $this->expedienteService = new CobranzaExpedienteService();
    }

    public function index()
    {
        return $this->renderDashboard();
    }

    public function expediente($tramiteId)
    {
        $tramiteId = (int) $tramiteId;

        if ($this->request->isAJAX()) {
            return $this->renderSelectedExpedientePartial($tramiteId);
        }

        return $this->renderDashboard($tramiteId);
    }

    public function abrirExpediente($tramiteId)
    {
        [$session, $roles, $perms, $userId, $accessResponse] = $this->requireCobranzaAccess();
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $tramiteId = (int) $tramiteId;
        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'No tienes permiso para operar este tramite.', site_url('deskapp/cobranza'), 403, false)) {
            return $resp;
        }

        $result = $this->expedienteService->openOrReactivateForTramite($tramiteId, $userId);

        return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
            ->with($result['success'] ? 'success' : 'error', $result['message'] ?? 'No se pudo abrir el expediente de cobranza.');
    }

    public function registrarGestion($tramiteId)
    {
        [$session, $roles, $perms, $userId, $accessResponse] = $this->requireCobranzaAccess();
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $tramiteId = (int) $tramiteId;
        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'No tienes permiso para operar este tramite.', site_url('deskapp/cobranza'), 403, false)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $expediente = $db->table('cobranza_expediente')
            ->select('id')
            ->where('tramite_id', $tramiteId)
            ->where('is_active', 1)
            ->get(1)
            ->getRowArray();

        if (empty($expediente)) {
            return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
                ->with('error', 'Primero debes abrir el expediente de cobranza.');
        }

        $payload = [
            'tipo' => $this->request->getPost('tipo'),
            'canal' => $this->request->getPost('canal'),
            'resultado' => $this->request->getPost('resultado'),
            'comentarios' => $this->request->getPost('comentarios'),
            'siguiente_accion' => $this->request->getPost('siguiente_accion'),
            'fecha_proximo_seguimiento' => $this->request->getPost('fecha_proximo_seguimiento'),
        ];

        $result = $this->expedienteService->registerGestion((int) $expediente['id'], $userId, $payload);

        return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
            ->with($result['success'] ? 'success' : 'error', $result['message'] ?? 'No se pudo registrar la gestion.');
    }

    public function registrarPromesa($tramiteId)
    {
        [$session, $roles, $perms, $userId, $accessResponse] = $this->requireCobranzaAccess();
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $tramiteId = (int) $tramiteId;
        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'No tienes permiso para operar este tramite.', site_url('deskapp/cobranza'), 403, false)) {
            return $resp;
        }

        $expediente = $this->findActiveExpedienteByTramite($tramiteId);
        if (empty($expediente)) {
            return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
                ->with('error', 'Primero debes abrir el expediente de cobranza.');
        }

        $result = $this->expedienteService->registerPromesaPago((int) $expediente['id'], $userId, [
            'monto_prometido' => $this->request->getPost('monto_prometido'),
            'fecha_promesa' => $this->normalizeDateTimeLocal((string) $this->request->getPost('fecha_promesa')),
            'medio_pago' => $this->request->getPost('medio_pago'),
            'observaciones' => $this->request->getPost('observaciones'),
            'canal' => $this->request->getPost('canal'),
        ]);

        return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
            ->with($result['success'] ? 'success' : 'error', $result['message'] ?? 'No se pudo registrar la promesa.');
    }

    public function registrarPago($tramiteId)
    {
        [$session, $roles, $perms, $userId, $accessResponse] = $this->requireCobranzaAccess();
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $tramiteId = (int) $tramiteId;
        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'No tienes permiso para operar este tramite.', site_url('deskapp/cobranza'), 403, false)) {
            return $resp;
        }

        $expediente = $this->findActiveExpedienteByTramite($tramiteId);
        if (empty($expediente)) {
            return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
                ->with('error', 'Primero debes abrir el expediente de cobranza.');
        }

        $result = $this->expedienteService->registerPago((int) $expediente['id'], $userId, [
            'monto' => $this->request->getPost('monto'),
            'tipo_pago' => $this->request->getPost('tipo_pago'),
            'fecha_pago_reportada' => $this->normalizeDateTimeLocal((string) $this->request->getPost('fecha_pago_reportada')),
            'medio_pago' => $this->request->getPost('medio_pago'),
            'referencia_pago' => $this->request->getPost('referencia_pago'),
            'observaciones' => $this->request->getPost('observaciones'),
            'canal' => $this->request->getPost('canal'),
        ]);

        return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
            ->with($result['success'] ? 'success' : 'error', $result['message'] ?? 'No se pudo registrar el pago.');
    }

    public function confirmarPago($tramiteId, $pagoId)
    {
        [$session, $roles, $perms, $userId, $accessResponse] = $this->requireCobranzaAccess();
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $tramiteId = (int) $tramiteId;
        $pagoId = (int) $pagoId;
        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'No tienes permiso para operar este tramite.', site_url('deskapp/cobranza'), 403, false)) {
            return $resp;
        }

        $expediente = $this->findActiveExpedienteByTramite($tramiteId);
        if (empty($expediente)) {
            return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
                ->with('error', 'Primero debes abrir el expediente de cobranza.');
        }

        $result = $this->expedienteService->confirmPago((int) $expediente['id'], $pagoId, $userId, [
            'fecha_pago_confirmada' => $this->normalizeDateTimeLocal((string) $this->request->getPost('fecha_pago_confirmada')),
            'observaciones' => $this->request->getPost('observaciones'),
            'canal' => $this->request->getPost('canal'),
            'fecha_proximo_seguimiento' => $this->normalizeDateTimeLocal((string) $this->request->getPost('fecha_proximo_seguimiento')),
        ]);

        return redirect()->to(site_url('deskapp/cobranza/expediente/' . $tramiteId))
            ->with($result['success'] ? 'success' : 'error', $result['message'] ?? 'No se pudo confirmar el pago.');
    }

    private function renderDashboard(?int $selectedTramiteId = null)
    {
        [$session, $roles, $perms, $userId, $accessResponse] = $this->requireCobranzaAccess();
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $requestedClienteId = $this->request->getGet('cliente_id');
        $activeClienteId = resolve_active_cliente_id($userId, $requestedClienteId);
        $tenantFilterSql = get_tramite_filter_sql($userId, 'tramite', $requestedClienteId);

        $dashboard = $this->dashboardService->buildDashboard($userId, $tenantFilterSql, [
            'q' => $this->request->getGet('q'),
            'bucket' => $this->request->getGet('bucket'),
            'page' => $this->request->getGet('page'),
        ], $selectedTramiteId);

        if ($selectedTramiteId !== null && $selectedTramiteId > 0) {
            $visibleSelectedId = (int) ($dashboard['selected_expediente']['id'] ?? 0);
            if ($visibleSelectedId !== $selectedTramiteId) {
                return redirect()->to(site_url('deskapp/cobranza'))
                    ->with('error', 'El expediente solicitado no esta disponible en tu cartera.');
            }
        }

        return view('deskapp/cobranza/index', array_merge($dashboard, [
            'session' => Services::session(),
            'username' => $session->get('user_name'),
            'clientes' => get_clientes_lista_for_user($userId),
            'active_cliente_id' => $activeClienteId,
            'cobro_status_options' => $this->loadCobroStatusOptions(),
            'can_edit_cobro_cliente_data' => can_edit_cobro_cliente_surface($roles, $perms),
            'can_upload_cobro_cliente_files' => can_upload_cobro_cliente_surface($roles, $perms),
            'can_conclude_tramite' => has_permission('important_concluir_tramite', $perms, $roles),
        ]));
    }

    private function renderSelectedExpedientePartial(int $selectedTramiteId)
    {
        [$session, $roles, $perms, $userId, $accessResponse] = $this->requireCobranzaAccess();
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $requestedClienteId = $this->request->getGet('cliente_id');
        $tenantFilterSql = get_tramite_filter_sql($userId, 'tramite', $requestedClienteId);
        $selectedExpediente = $this->dashboardService->loadSelectedExpediente($userId, $tenantFilterSql, $selectedTramiteId, [
            'q' => $this->request->getGet('q'),
            'bucket' => $this->request->getGet('bucket'),
            'page' => $this->request->getGet('page'),
        ]);

        if ($selectedExpediente === null) {
            return Services::response()
                ->setStatusCode(404)
                ->setBody(view('deskapp/cobranza/_detail', [
                    'selected_expediente' => null,
                    'cobranza_schema_ready' => $this->dashboardService->isCobranzaSchemaReady(),
                ]));
        }

        return view('deskapp/cobranza/_detail', [
            'selected_expediente' => $selectedExpediente,
            'cobranza_schema_ready' => $this->dashboardService->isCobranzaSchemaReady(),
            'cobro_status_options' => $this->loadCobroStatusOptions(),
            'can_edit_cobro_cliente_data' => can_edit_cobro_cliente_surface($roles, $perms),
            'can_upload_cobro_cliente_files' => can_upload_cobro_cliente_surface($roles, $perms),
            'can_conclude_tramite' => has_permission('important_concluir_tramite', $perms, $roles),
        ]);
    }

    private function loadCobroStatusOptions(): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('cobro_statuses')) {
            return [];
        }

        $rows = $db->table('cobro_statuses')
            ->select('id, cobro_status')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $options = [];
        foreach ($rows as $row) {
            $options[(int) ($row['id'] ?? 0)] = (string) ($row['cobro_status'] ?? '');
        }

        return $options;
    }

    private function requireCobranzaAccess(): array
    {
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return [null, [], [], 0, $resp];
        }

        $session = session();
        [$roles, $perms] = session_roles_perms($session);

        if (!has_permission('list_cobro_cliente', $perms, $roles)) {
            $resp = redirect()->to(site_url('deskapp/dashboard'))
                ->with('error', 'No tienes permisos para acceder al centro de cobranza.');
            return [null, [], [], 0, $resp];
        }

        return [$session, $roles, $perms, (int) ($session->get('id') ?? 0), null];
    }

    private function findActiveExpedienteByTramite(int $tramiteId): array
    {
        $db = \Config\Database::connect();

        return $db->table('cobranza_expediente')
            ->select('id')
            ->where('tramite_id', $tramiteId)
            ->where('is_active', 1)
            ->get(1)
            ->getRowArray() ?? [];
    }

    private function normalizeDateTimeLocal(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
    }
}
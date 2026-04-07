<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class DashboardCliente extends BaseController
{
    protected $dashboardModel;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'cliente_filter', 'cliente_context', 'permissions']);
        $this->dashboardModel = new DashboardModel();
        $this->session = session();
    }

    private function requireClienteAccess()
    {
        [$roles, $perms] = session_roles_perms($this->session);

        if (has_permission('menu_dashboard_cliente', $perms, $roles)) {
            return null;
        }

        $accept = (string) $this->request->getHeaderLine('Accept');
        $wantsJson = $this->request->isAJAX() || (strpos($accept, 'application/json') !== false);
        if ($wantsJson) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 403,
                    'error' => 'forbidden',
                    'message' => 'No tiene permisos para acceder a este modulo.',
                ]);
        }

        $data = [
            'session' => \Config\Services::session(),
            'username' => $this->session->get('user_name'),
        ];
        return $this->response
            ->setStatusCode(403)
            ->setBody(view('deskapp/error-pages/403', $data));
    }

    private function getFiltersFromRequest(): array
    {
        $filters = [
            'cli_directo_id' => $this->request->getGet('cli_directo_id'),
            'tra_tipos_id' => $this->request->getGet('tra_tipos_id'),
            'tra_status_id' => $this->request->getGet('tra_status_id'),
            'fecha_inicio' => $this->request->getGet('fecha_inicio'),
            'fecha_fin' => $this->request->getGet('fecha_fin'),
            'pendiente_pago' => $this->request->getGet('pendiente_pago'),
        ];

        foreach (['cli_directo_id', 'tra_tipos_id', 'tra_status_id'] as $key) {
            if ($filters[$key] === '' || $filters[$key] === null) {
                $filters[$key] = null;
                continue;
            }
            $filters[$key] = is_numeric($filters[$key]) ? (int) $filters[$key] : null;
        }

        if (!empty($filters['fecha_inicio']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['fecha_inicio'])) {
            $filters['fecha_inicio'] = null;
        }
        if (!empty($filters['fecha_fin']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['fecha_fin'])) {
            $filters['fecha_fin'] = null;
        }

        $pendiente = $filters['pendiente_pago'];
        if ($pendiente === '1' || $pendiente === 1 || $pendiente === '0' || $pendiente === 0) {
            $filters['pendiente_pago'] = (string) $pendiente;
        } else {
            $filters['pendiente_pago'] = null;
        }

        return $filters;
    }

    private function getCliDirectoList(int $userId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('cli_directo cd');
        $builder->select('cd.id, cd.razon_social, c.razon_social as cliente');
        $builder->join('cliente c', 'c.id = cd.cliente_id', 'inner');

        $clienteIds = get_user_cliente_ids($userId);
        if (is_array($clienteIds)) {
            if (empty($clienteIds)) {
                return [];
            }
            $builder->whereIn('c.id', array_map('intval', $clienteIds));
        }

        $builder->orderBy('cd.razon_social', 'ASC');
        return $builder->get()->getResultArray();
    }

    private function getTiposList(): array
    {
        $db = \Config\Database::connect();
        return $db->table('tra_tipos')
            ->select('id, tipo_tramite')
            ->orderBy('tipo_tramite', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getStatusList(): array
    {
        $db = \Config\Database::connect();
        return $db->table('tra_status')
            ->select('id, tra_status')
            ->orderBy('tra_status', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function index()
    {
        if ($resp = $this->requireClienteAccess()) {
            return $resp;
        }

        $userId = (int) $this->session->get('id');

        $data = [
            'session' => \Config\Services::session(),
            'username' => $this->session->get('user_name'),
            'cli_directo_list' => $this->getCliDirectoList($userId),
            'tipos_list' => $this->getTiposList(),
            'status_list' => $this->getStatusList(),
            'filters' => $this->getFiltersFromRequest(),
        ];

        return view('deskapp/dashboard/dashboard_cliente', $data);
    }

    public function data()
    {
        if ($resp = $this->requireClienteAccess()) {
            return $resp;
        }

        $userId = (int) $this->session->get('id');
        $filters = $this->getFiltersFromRequest();

        $data = [
            'filters' => $filters,
            'semaforo' => $this->dashboardModel->getClienteSemaforoAtencion($filters, $userId),
            'facturas_pendientes' => $this->dashboardModel->getClienteFacturasPendientes($filters, $userId),
            'atorados_por_tipo' => $this->dashboardModel->getClienteAtoradosPorTipoServicio(10, $filters, $userId),
            'atorados_por_estado' => $this->dashboardModel->getClienteAtoradosPorEstado(10, $filters, $userId),
            'atorados_por_cliente' => $this->dashboardModel->getClienteAtoradosPorCliente(10, $filters, $userId),
            'resumen' => $this->dashboardModel->getClienteResumen($filters, $userId),
            'concluidos_periodos' => $this->dashboardModel->getClienteConcluidosPorPeriodos($filters, $userId),
            'tramites_por_tipo' => $this->dashboardModel->getClienteTramitesPorTipoProcesoVsConcluido(10, $filters, $userId),
        ];

        return $this->response->setJSON($data);
    }
}

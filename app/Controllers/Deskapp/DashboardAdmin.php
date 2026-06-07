<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardAdmin extends BaseController
{
    protected $dashboardModel;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'cliente_filter', 'cliente_context', 'permissions']);
        $this->dashboardModel = new DashboardModel();
        $this->session = session();
    }

    /**
     * Requiere permiso para acceder al módulo de Dashboard Admin.
     *
     * - Super Admin/Admin: acceso total
     * - Otros: requiere permiso `menu_dashboard_admin`
     */
    private function requireDashboardAdminAccess()
    {
        [$roles, $perms] = session_roles_perms($this->session);

        if (has_permission('menu_dashboard_admin', $perms, $roles)) {
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
                    'message' => 'No tiene permisos para acceder a este módulo.',
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

    /**
     * Resuelve el filtro cliente_id desde la URL y valida acceso.
     */
    private function resolveClienteIdFilter(int $userId): ?int
    {
        $requestedClienteId = $this->request->getGet('cliente_id');
        return resolve_active_cliente_id($userId, $requestedClienteId);
    }

    private function resolveClienteNombre(?int $clienteId, array $clientesLista): string
    {
        if (empty($clienteId)) {
            return 'Todos los clientes';
        }

        foreach ($clientesLista as $cliente) {
            if ((int) ($cliente['id'] ?? 0) === (int) $clienteId) {
                return (string) ($cliente['nombre'] ?? ('Cliente #' . $clienteId));
            }
        }

        return 'Cliente #' . (int) $clienteId;
    }

    private function buildReportesExportData(int $userId): array
    {
        $clienteIdFiltro = $this->resolveClienteIdFilter($userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);

        $anio = (int) ($this->request->getGet('anio') ?? date('Y'));
        $clientesLista = $this->getClientesLista($userId);

        return [
            'anio' => $anio,
            'cliente_id_filtro' => $clienteIdFiltro,
            'cliente_nombre' => $this->resolveClienteNombre($clienteIdFiltro, $clientesLista),
            'tramites_por_mes' => $this->dashboardModel->getTramitesPorMes($anio, $userId),
            'ingresos_por_mes' => $this->dashboardModel->getIngresosPorMes($anio, $userId),
            'tramites_por_tipo' => $this->dashboardModel->getTramitesPorTipo('anio', $anio, $userId),
            'top_ejecutivos' => $this->dashboardModel->getTopEjecutivos(10, 'anio', $anio, $userId),
            'top_gestores' => $this->dashboardModel->getTopGestores(10, 'anio', $anio, $userId),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function populateReportSheet($sheet, array $metaLines, array $headers, array $rows): void
    {
        $rowIndex = 1;
        foreach ($metaLines as $line) {
            $sheet->setCellValue('A' . $rowIndex, $line);
            $rowIndex++;
        }

        $headerRow = $rowIndex + 1;
        foreach ($headers as $index => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $sheet->getStyle('A1:A' . count($metaLines))->getFont()->setBold(true);
        $lastHeaderColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastHeaderColumn . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $currentRow = $headerRow + 1;
        if (empty($rows)) {
            $sheet->setCellValue('A' . $currentRow, 'Sin datos disponibles');
            $currentRow++;
        } else {
            foreach ($rows as $row) {
                foreach (array_values($row) as $index => $value) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                    $sheet->setCellValue($column . $currentRow, $value);
                }
                $currentRow++;
            }
        }

        for ($index = 1; $index <= count($headers); $index++) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function buildReportesSpreadsheet(array $exportData): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('SGL')
            ->setTitle('Reporte anual ' . $exportData['anio'])
            ->setDescription('Reporte anual de dashboard administrativo');

        $metaLines = [
            'Reporte anual de dashboard administrativo',
            'Anio: ' . $exportData['anio'],
            'Cliente: ' . $exportData['cliente_nombre'],
            'Generado: ' . $exportData['generated_at'],
        ];

        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $tramitesPorMes = [];
        foreach ($exportData['tramites_por_mes'] as $row) {
            $tramitesPorMes[(int) ($row['mes'] ?? 0)] = $row;
        }
        $ingresosPorMes = [];
        foreach ($exportData['ingresos_por_mes'] as $row) {
            $ingresosPorMes[(int) ($row['mes'] ?? 0)] = $row;
        }

        $summaryRows = [];
        foreach ($monthNames as $monthNumber => $monthName) {
            $tramites = $tramitesPorMes[$monthNumber] ?? [];
            $ingresos = $ingresosPorMes[$monthNumber] ?? [];
            $summaryRows[] = [
                $monthName,
                (int) ($tramites['total_ingresados'] ?? 0),
                (int) ($tramites['total_concluidos'] ?? 0),
                (int) ($tramites['total_cobrados'] ?? 0),
                (float) ($ingresos['ingresos'] ?? 0),
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen anual');
        $this->populateReportSheet(
            $sheet,
            $metaLines,
            ['Mes', 'Ingresados', 'Concluidos', 'Cobrados', 'Ingresos'],
            $summaryRows
        );

        $tiposRows = [];
        foreach ($exportData['tramites_por_tipo'] as $row) {
            $cantidad = (int) ($row['cantidad'] ?? 0);
            $concluidos = (int) ($row['concluidos'] ?? 0);
            $tiposRows[] = [
                (string) ($row['tipo_tramite'] ?? 'Sin tipo'),
                $cantidad,
                $concluidos,
                $cantidad > 0 ? round(($concluidos / $cantidad) * 100, 1) . '%': '0%',
                isset($row['tiempo_promedio']) && $row['tiempo_promedio'] !== null ? round((float) $row['tiempo_promedio'], 1) . ' dias' : 'N/A',
            ];
        }
        $sheetTipos = $spreadsheet->createSheet();
        $sheetTipos->setTitle('Tipos de tramite');
        $this->populateReportSheet(
            $sheetTipos,
            $metaLines,
            ['Tipo de tramite', 'Cantidad', 'Concluidos', '% exito', 'Tiempo promedio'],
            $tiposRows
        );

        $ejecutivosRows = [];
        foreach ($exportData['top_ejecutivos'] as $index => $row) {
            $ejecutivosRows[] = [
                $index + 1,
                (string) ($row['ejecutivo'] ?? 'Sin ejecutivo'),
                (int) ($row['total_tramites'] ?? 0),
                (int) ($row['tramites_concluidos'] ?? 0),
                (int) ($row['tramites_cobrados'] ?? 0),
                (float) ($row['monto_cobrado'] ?? 0),
                isset($row['tiempo_promedio_dias']) && $row['tiempo_promedio_dias'] !== null ? round((float) $row['tiempo_promedio_dias'], 1) . ' dias' : 'N/A',
            ];
        }
        $sheetEjecutivos = $spreadsheet->createSheet();
        $sheetEjecutivos->setTitle('Top ejecutivos');
        $this->populateReportSheet(
            $sheetEjecutivos,
            $metaLines,
            ['#', 'Ejecutivo', 'Total', 'Concluidos', 'Cobrados', 'Monto cobrado', 'Tiempo promedio'],
            $ejecutivosRows
        );

        $gestoresRows = [];
        foreach ($exportData['top_gestores'] as $index => $row) {
            $gestoresRows[] = [
                $index + 1,
                (string) ($row['gestor'] ?? 'Sin gestor'),
                (string) ($row['empresa_gestora'] ?? 'Sin empresa'),
                (int) ($row['total_tramites'] ?? 0),
                (int) ($row['tramites_concluidos'] ?? 0),
                isset($row['tiempo_promedio_dias']) && $row['tiempo_promedio_dias'] !== null ? round((float) $row['tiempo_promedio_dias'], 1) . ' dias' : 'N/A',
                (float) ($row['total_pagado_gestor'] ?? 0),
            ];
        }
        $sheetGestores = $spreadsheet->createSheet();
        $sheetGestores->setTitle('Top gestores');
        $this->populateReportSheet(
            $sheetGestores,
            $metaLines,
            ['#', 'Gestor', 'Empresa', 'Total', 'Concluidos', 'Tiempo promedio', 'Pagado'],
            $gestoresRows
        );

        return $spreadsheet;
    }

    /**
     * Lista de clientes visibles para el usuario (tabla cliente).
     */
    private function getClientesLista(int $userId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('cliente');
        $builder->select("id, COALESCE(NULLIF(razon_social,''), NULLIF(nombre,''), CONCAT('Cliente #', id)) as nombre", false);

        $clienteIds = get_user_cliente_ids($userId);
        if (is_array($clienteIds)) {
            if (empty($clienteIds)) {
                return [];
            }
            $builder->whereIn('id', array_map('intval', $clienteIds));
        }

        $builder->orderBy('nombre', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Aplica el filtro en el modelo y agrega variables comunes para vistas.
     */
    private function applyClienteFilterToModelAndData(array &$data, int $userId): void
    {
        $clienteIdFiltro = $this->resolveClienteIdFilter($userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);

        $data['cliente_id_filtro'] = $clienteIdFiltro;
        $data['clientes_lista'] = $this->getClientesLista($userId);
    }

    /**
     * Vista principal del dashboard administrativo
     */
    public function index()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        $this->applyClienteFilterToModelAndData($data, (int) $userId);
        
        // Obtener el año del parámetro GET o usar el año actual
        $anio = $this->request->getGet('anio') ?? date('Y');
        $data['anio_seleccionado'] = $anio;
        
        // Determinar si es el año actual
        $esAnioActual = ($anio == date('Y'));
        $data['es_anio_actual'] = $esAnioActual;
        
        // Obtener todas las métricas necesarias
        if ($esAnioActual) {
            // Para el año actual, mostrar métricas actualizadas
            $data['metricas_hoy'] = $this->dashboardModel->getMetricasHoy($userId);
            $data['metricas_semana'] = $this->dashboardModel->getMetricasSemana($userId);
        } else {
            // Para años anteriores, métricas vacías
            $data['metricas_hoy'] = ['total_ingresados' => 0, 'total_concluidos' => 0, 'total_cobrados' => 0, 'monto_cobrado_hoy' => 0];
            $data['metricas_semana'] = ['total_ingresados' => 0, 'total_concluidos' => 0, 'total_cobrados' => 0, 'monto_cobrado' => 0, 'tiempo_promedio_dias' => 0];
        }
        
        $data['metricas_mes'] = $this->dashboardModel->getMetricasMes($userId);
        $data['metricas_anio'] = $this->dashboardModel->getMetricasAnio($anio, $userId);
        $data['metricas_enero'] = $this->dashboardModel->getMetricasEneroALaFecha($anio, $userId);
        
        // KPIs principales (del año seleccionado)
        $data['kpis'] = $this->dashboardModel->getKPIsPrincipales($anio, $userId);
        
        // Alertas críticas (solo para año actual)
        if ($esAnioActual) {
            $data['tramites_retrasados'] = array_slice($this->dashboardModel->getTramitesRetrasados(30, $userId), 0, 5);
            $data['pendientes_cobro'] = array_slice($this->dashboardModel->getTramitesPendientesCobro(15, $userId), 0, 5);
            $data['tramites_estancados'] = array_slice($this->dashboardModel->getTramitesEstancados(7, $userId), 0, 5);
            
            $data['count_retrasados'] = count($this->dashboardModel->getTramitesRetrasados(30, $userId));
            $data['count_pendientes_cobro'] = count($this->dashboardModel->getTramitesPendientesCobro(15, $userId));
            $data['count_estancados'] = count($this->dashboardModel->getTramitesEstancados(7, $userId));

            $data['semaforo_atencion'] = $this->dashboardModel->getSemaforoAtencion($userId);
            $data['atorados_por_tipo'] = $this->dashboardModel->getAtoradosPorTipoServicio(10, $userId);
            $data['atorados_por_estado'] = $this->dashboardModel->getAtoradosPorEstado(10, $userId);
            $data['atorados_por_cliente'] = $this->dashboardModel->getAtoradosPorCliente(10, $userId);
        } else {
            // Sin alertas para años anteriores
            $data['tramites_retrasados'] = [];
            $data['pendientes_cobro'] = [];
            $data['tramites_estancados'] = [];
            $data['count_retrasados'] = 0;
            $data['count_pendientes_cobro'] = 0;
            $data['count_estancados'] = 0;

            $data['semaforo_atencion'] = [];
            $data['atorados_por_tipo'] = [];
            $data['atorados_por_estado'] = [];
            $data['atorados_por_cliente'] = [];
        }
        
        // Top rankings
        $data['top_ejecutivos'] = $this->dashboardModel->getTopEjecutivos(5, 'anio', $anio, $userId);
        $data['top_gestores'] = $this->dashboardModel->getTopGestores(5, 'anio', $anio, $userId);
        
        // Embudo de conversión del año
        $fechaInicio = $anio . '-01-01';
        $fechaFin = $anio . '-12-31';
        $data['embudo'] = $this->dashboardModel->getEmbudoConversion($fechaInicio, $fechaFin, $userId);
        
        // Distribución por estado (del año)
        $data['distribucion_estados'] = $this->dashboardModel->getDistribucionPorEstado($anio, $userId);
        
        // Comparativa semanal (solo año actual)
        if ($esAnioActual) {
            $data['comparativa_semanal'] = $this->dashboardModel->getComparativaSemanal($userId);
        }
        
        // Records históricos
        $data['records'] = $this->dashboardModel->getRecordsHistoricos($userId);
        
        return view('deskapp/dashboard/dashboard_admin', $data);
    }

    /**
     * Vista de alertas completas
     */
    public function alertas()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        $this->applyClienteFilterToModelAndData($data, (int) $userId);

        $perPage = 50;
        $pageRetrasados = max(1, (int) $this->request->getGet('page_retrasados'));
        $pagePendientes = max(1, (int) $this->request->getGet('page_pendientes'));
        $pageEstancados = max(1, (int) $this->request->getGet('page_estancados'));

        $data['per_page'] = $perPage;
        $data['page_retrasados'] = $pageRetrasados;
        $data['page_pendientes'] = $pagePendientes;
        $data['page_estancados'] = $pageEstancados;

        $data['total_tramites_retrasados'] = $this->dashboardModel->countTramitesRetrasados(30, $userId);
        $data['total_pendientes_cobro'] = $this->dashboardModel->countTramitesPendientesCobro(15, $userId);
        $data['total_tramites_estancados'] = $this->dashboardModel->countTramitesEstancados(7, $userId);

        $data['tramites_retrasados'] = $this->dashboardModel->getTramitesRetrasados(
            30,
            $userId,
            $perPage,
            ($pageRetrasados - 1) * $perPage
        );
        $data['pendientes_cobro'] = $this->dashboardModel->getTramitesPendientesCobro(
            15,
            $userId,
            $perPage,
            ($pagePendientes - 1) * $perPage
        );
        $data['tramites_estancados'] = $this->dashboardModel->getTramitesEstancados(
            7,
            $userId,
            $perPage,
            ($pageEstancados - 1) * $perPage
        );

        $data['semaforo_atencion'] = $this->dashboardModel->getSemaforoAtencion($userId);
        $data['atorados_por_tipo'] = $this->dashboardModel->getAtoradosPorTipoServicio(10, $userId);
        $data['atorados_por_estado'] = $this->dashboardModel->getAtoradosPorEstado(10, $userId);
        $data['atorados_por_cliente'] = $this->dashboardModel->getAtoradosPorCliente(10, $userId);
        
        return view('deskapp/dashboard/alertas', $data);
    }

    /**
     * Vista de análisis financiero
     */
    public function financiero()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        $this->applyClienteFilterToModelAndData($data, (int) $userId);
        
        $perPage = 50;
        $pageAging = max(1, (int) $this->request->getGet('page_aging'));

        $data['per_page'] = $perPage;
        $data['page_aging'] = $pageAging;
        $data['total_aging_report'] = $this->dashboardModel->countAgingReport($userId);

        // Aging report
        $data['aging_report'] = $this->dashboardModel->getAgingReport(
            $userId,
            $perPage,
            ($pageAging - 1) * $perPage
        );
        $data['resumen_rangos'] = $this->dashboardModel->getResumenFinancieroPorRangos($userId);
        $data['proyeccion'] = $this->dashboardModel->getProyeccionIngresos($userId);
        
        // Métricas financieras
        $data['metricas_mes'] = $this->dashboardModel->getMetricasMes($userId);
        $data['metricas_anio'] = $this->dashboardModel->getMetricasAnio(null, $userId);
        
        return view('deskapp/dashboard/financiero', $data);
    }

    /**
     * Vista de reportes y gráficas
     */
    public function reportes()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        $this->applyClienteFilterToModelAndData($data, (int) $userId);
        
        // Obtener año de la URL o usar el actual
        $anio = $this->request->getGet('anio') ?? date('Y');
        $esAnioActual = ($anio == date('Y'));
        
        $data['anio_seleccionado'] = $anio;
        $data['es_anio_actual'] = $esAnioActual;
        
        // Datos para gráficas (del año seleccionado)
        $data['tramites_por_mes'] = $this->dashboardModel->getTramitesPorMes($anio, $userId);
        $data['ingresos_por_mes'] = $this->dashboardModel->getIngresosPorMes($anio, $userId);
        $data['tramites_por_tipo'] = $this->dashboardModel->getTramitesPorTipo('anio', $anio, $userId);
        
        // Rankings (del año seleccionado)
        $data['top_ejecutivos'] = $this->dashboardModel->getTopEjecutivos(10, 'anio', $anio, $userId);
        $data['top_gestores'] = $this->dashboardModel->getTopGestores(10, 'anio', $anio, $userId);
        
        return view('deskapp/dashboard/reportes', $data);
    }

    /**
     * Vista de trámites por cliente
     */
    public function por_cliente()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        $this->applyClienteFilterToModelAndData($data, (int) $userId);
        
        // Obtener año de la URL o usar el actual
        $anio = $this->request->getGet('anio') ?? date('Y');
        $data['anio_seleccionado'] = $anio;
        
        // Obtener resumen por cliente
        $data['tramites_por_cliente'] = $this->dashboardModel->getTramitesPorCliente($anio, $userId);
        
        return view('deskapp/dashboard/por_cliente', $data);
    }

    /**
     * Vista de detalle de trámites por cliente específico
     */
    public function detalle_cliente($clienteId)
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');

        // Validar acceso al cliente solicitado (multi-tenant)
        if (!has_access_to_cliente((int) $clienteId, (int) $userId)) {
            $data403 = [
                'session' => \Config\Services::session(),
                'username' => $this->session->get('user_name'),
            ];
            return $this->response
                ->setStatusCode(403)
                ->setBody(view('deskapp/error-pages/403', $data403));
        }

        // En detalle por cliente, forzar el filtro al cliente del path
        $this->dashboardModel->setClienteIdFilter((int) $clienteId);
        $data['cliente_id_filtro'] = (int) $clienteId;
        
        // Obtener año de la URL o usar el actual
        $anio = $this->request->getGet('anio') ?? date('Y');
        $data['anio_seleccionado'] = $anio;
        $data['cliente_id'] = $clienteId;
        
        // Obtener detalle de trámites
        $tramites = $this->dashboardModel->getDetalleTramitesPorCliente($clienteId, $anio, $userId);
        
        // Aplicar lógica de colores a cada trámite
        foreach ($tramites as &$tramite) {
            $tramite = $this->dashboardModel->aplicarLogicaColores($tramite);
        }
        
        $data['tramites'] = $tramites;
        
        // Obtener nombre del cliente
        if (!empty($tramites)) {
            $data['nombre_cliente'] = $tramites[0]['cliente'];
        } else {
            $data['nombre_cliente'] = 'Cliente';
        }
        
        return view('deskapp/dashboard/detalle_cliente', $data);
    }

    /**
     * API: Obtener métricas en tiempo real (JSON)
     */
    public function api_metricas()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $periodo = $this->request->getGet('periodo') ?? 'hoy';
        $userId = $this->session->get('id');

        // Aplicar filtro por cliente (si viene)
        $clienteIdFiltro = $this->resolveClienteIdFilter((int) $userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);
        
        $data = [];
        switch ($periodo) {
            case 'hoy':
                $data = $this->dashboardModel->getMetricasHoy($userId);
                break;
            case 'semana':
                $data = $this->dashboardModel->getMetricasSemana($userId);
                break;
            case 'mes':
                $data = $this->dashboardModel->getMetricasMes($userId);
                break;
            case 'anio':
                $anio = $this->request->getGet('anio') ?? null;
                $data = $this->dashboardModel->getMetricasAnio($anio, $userId);
                break;
            case 'enero_fecha':
                $anio = $this->request->getGet('anio') ?? null;
                $data = $this->dashboardModel->getMetricasEneroALaFecha($anio, $userId);
                break;
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener alertas (JSON)
     */
    public function api_alertas()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $tipo = $this->request->getGet('tipo') ?? 'todas';
        $userId = $this->session->get('id');

        $clienteIdFiltro = $this->resolveClienteIdFilter((int) $userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);
        
        $data = [];
        switch ($tipo) {
            case 'retrasados':
                $data = $this->dashboardModel->getTramitesRetrasados(30, $userId);
                break;
            case 'pendientes_cobro':
                $data = $this->dashboardModel->getTramitesPendientesCobro(15, $userId);
                break;
            case 'estancados':
                $data = $this->dashboardModel->getTramitesEstancados(7, $userId);
                break;
            default:
                $data = $this->dashboardModel->getAlertasCriticas($userId);
                break;
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener datos para gráficas (JSON)
     */
    public function api_graficas()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $tipo = $this->request->getGet('tipo') ?? 'tramites_mes';
        $anio = $this->request->getGet('anio') ?? date('Y');
        $userId = $this->session->get('id');

        $clienteIdFiltro = $this->resolveClienteIdFilter((int) $userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);
        
        $data = [];
        switch ($tipo) {
            case 'tramites_mes':
                $data = $this->dashboardModel->getTramitesPorMes($anio, $userId);
                break;
            case 'ingresos_mes':
                $data = $this->dashboardModel->getIngresosPorMes($anio, $userId);
                break;
            case 'tramites_tipo':
                $periodo = $this->request->getGet('periodo') ?? 'mes';
                $data = $this->dashboardModel->getTramitesPorTipo($periodo, $anio, $userId);
                break;
            case 'distribucion_estados':
                $data = $this->dashboardModel->getDistribucionPorEstado($anio, $userId);
                break;
            case 'embudo':
                $fechaInicio = $this->request->getGet('fecha_inicio') ?? null;
                $fechaFin = $this->request->getGet('fecha_fin') ?? null;
                $data = $this->dashboardModel->getEmbudoConversion($fechaInicio, $fechaFin, $userId);
                break;
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener KPIs principales (JSON)
     */
    public function api_kpis()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $anio = $this->request->getGet('anio') ?? null;
        $userId = $this->session->get('id');

        $clienteIdFiltro = $this->resolveClienteIdFilter((int) $userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);

        $data = $this->dashboardModel->getKPIsPrincipales($anio, $userId);
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener comparativas (JSON)
     */
    public function api_comparativas()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $tipo = $this->request->getGet('tipo') ?? 'semanal';
        $userId = $this->session->get('id');

        $clienteIdFiltro = $this->resolveClienteIdFilter((int) $userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);
        
        $data = [];
        switch ($tipo) {
            case 'semanal':
                $data = $this->dashboardModel->getComparativaSemanal($userId);
                break;
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener top rankings (JSON)
     */
    public function api_rankings()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $tipo = $this->request->getGet('tipo') ?? 'ejecutivos';
        $limite = $this->request->getGet('limite') ?? 5;
        $periodo = $this->request->getGet('periodo') ?? 'mes';
        $anio = $this->request->getGet('anio') ?? null;
        $userId = $this->session->get('id');

        $clienteIdFiltro = $this->resolveClienteIdFilter((int) $userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);
        
        $data = [];
        switch ($tipo) {
            case 'ejecutivos':
                $data = $this->dashboardModel->getTopEjecutivos($limite, $periodo, $anio, $userId);
                break;
            case 'gestores':
                $data = $this->dashboardModel->getTopGestores($limite, $periodo, $anio, $userId);
                break;
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener resumen financiero (JSON)
     */
    public function api_financiero()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $userId = $this->session->get('id');

        $clienteIdFiltro = $this->resolveClienteIdFilter((int) $userId);
        $this->dashboardModel->setClienteIdFilter($clienteIdFiltro);

        $data = [
            'aging_report' => $this->dashboardModel->getAgingReport($userId),
            'resumen_rangos' => $this->dashboardModel->getResumenFinancieroPorRangos($userId),
            'proyeccion' => $this->dashboardModel->getProyeccionIngresos($userId),
        ];
        
        return $this->response->setJSON($data);
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportar_excel()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $userId = (int) $this->session->get('id');
        $tipo = $this->request->getGet('tipo') ?? 'metricas';

        if ($tipo !== 'reportes') {
            return $this->response->setStatusCode(400)->setBody('Tipo de exportacion no soportado.');
        }

        $exportData = $this->buildReportesExportData($userId);
        $spreadsheet = $this->buildReportesSpreadsheet($exportData);
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'sgl-reportes-xlsx-');
        $writer->save($tempFile);
        $contents = file_get_contents($tempFile) ?: '';
        @unlink($tempFile);

        $fileName = 'reporte_reportes_' . $exportData['anio'] . '_' . date('Y-m-d') . '.xlsx';
        return $this->response->download($fileName, $contents);
    }

    /**
     * Imprimir reporte PDF
     */
    public function exportar_pdf()
    {
        if ($resp = $this->requireDashboardAdminAccess()) {
            return $resp;
        }
        $userId = (int) $this->session->get('id');
        $tipo = $this->request->getGet('tipo') ?? 'metricas';

        if ($tipo !== 'reportes') {
            return $this->response->setStatusCode(400)->setBody('Tipo de exportacion no soportado.');
        }

        $data = $this->buildReportesExportData($userId);
        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody(view('deskapp/dashboard/reportes_print', $data));
    }
}

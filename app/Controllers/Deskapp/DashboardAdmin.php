<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class DashboardAdmin extends BaseController
{
    protected $dashboardModel;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'cliente_filter']);
        $this->dashboardModel = new DashboardModel();
        $this->session = session();
    }

    /**
     * Vista principal del dashboard administrativo
     */
    public function index()
    {
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');
        
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
        } else {
            // Sin alertas para años anteriores
            $data['tramites_retrasados'] = [];
            $data['pendientes_cobro'] = [];
            $data['tramites_estancados'] = [];
            $data['count_retrasados'] = 0;
            $data['count_pendientes_cobro'] = 0;
            $data['count_estancados'] = 0;
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
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');
        
        // Obtener todas las alertas sin límite
        $data['tramites_retrasados'] = $this->dashboardModel->getTramitesRetrasados(30, $userId);
        $data['pendientes_cobro'] = $this->dashboardModel->getTramitesPendientesCobro(15, $userId);
        $data['tramites_estancados'] = $this->dashboardModel->getTramitesEstancados(7, $userId);
        
        return view('deskapp/dashboard/alertas', $data);
    }

    /**
     * Vista de análisis financiero
     */
    public function financiero()
    {
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');
        
        // Aging report
        $data['aging_report'] = $this->dashboardModel->getAgingReport($userId);
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
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');
        
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
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');
        
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
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        $userId = $this->session->get('id');
        
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
        $periodo = $this->request->getGet('periodo') ?? 'hoy';
        $userId = $this->session->get('id');
        
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
        $tipo = $this->request->getGet('tipo') ?? 'todas';
        $userId = $this->session->get('id');
        
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
        $tipo = $this->request->getGet('tipo') ?? 'tramites_mes';
        $anio = $this->request->getGet('anio') ?? date('Y');
        $userId = $this->session->get('id');
        
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
        $anio = $this->request->getGet('anio') ?? null;
        $userId = $this->session->get('id');
        $data = $this->dashboardModel->getKPIsPrincipales($anio, $userId);
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener comparativas (JSON)
     */
    public function api_comparativas()
    {
        $tipo = $this->request->getGet('tipo') ?? 'semanal';
        $userId = $this->session->get('id');
        
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
        $tipo = $this->request->getGet('tipo') ?? 'ejecutivos';
        $limite = $this->request->getGet('limite') ?? 5;
        $periodo = $this->request->getGet('periodo') ?? 'mes';
        $anio = $this->request->getGet('anio') ?? null;
        $userId = $this->session->get('id');
        
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
        $userId = $this->session->get('id');
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
        $tipo = $this->request->getGet('tipo') ?? 'metricas';
        
        // Aquí se puede implementar la exportación a Excel
        // usando PhpSpreadsheet u otra librería similar
        
        return $this->response->download('reporte_' . $tipo . '_' . date('Y-m-d') . '.xlsx', null);
    }

    /**
     * Imprimir reporte PDF
     */
    public function exportar_pdf()
    {
        $tipo = $this->request->getGet('tipo') ?? 'metricas';
        
        // Aquí se puede implementar la exportación a PDF
        // usando TCPDF, DOMPDF u otra librería similar
        
        return $this->response->download('reporte_' . $tipo . '_' . date('Y-m-d') . '.pdf', null);
    }
}

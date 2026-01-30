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
        helper(['form', 'url']);
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
        
        // Obtener el año del parámetro GET o usar el año actual
        $anio = $this->request->getGet('anio') ?? date('Y');
        $data['anio_seleccionado'] = $anio;
        
        // Determinar si es el año actual
        $esAnioActual = ($anio == date('Y'));
        $data['es_anio_actual'] = $esAnioActual;
        
        // Obtener todas las métricas necesarias
        if ($esAnioActual) {
            // Para el año actual, mostrar métricas actualizadas
            $data['metricas_hoy'] = $this->dashboardModel->getMetricasHoy();
            $data['metricas_semana'] = $this->dashboardModel->getMetricasSemana();
        } else {
            // Para años anteriores, métricas vacías
            $data['metricas_hoy'] = ['total_ingresados' => 0, 'total_concluidos' => 0, 'total_cobrados' => 0, 'monto_cobrado_hoy' => 0];
            $data['metricas_semana'] = ['total_ingresados' => 0, 'total_concluidos' => 0, 'total_cobrados' => 0, 'monto_cobrado' => 0, 'tiempo_promedio_dias' => 0];
        }
        
        $data['metricas_mes'] = $this->dashboardModel->getMetricasMes();
        $data['metricas_anio'] = $this->dashboardModel->getMetricasAnio($anio);
        $data['metricas_enero'] = $this->dashboardModel->getMetricasEneroALaFecha($anio);
        
        // KPIs principales (del año seleccionado)
        $data['kpis'] = $this->dashboardModel->getKPIsPrincipales($anio);
        
        // Alertas críticas (solo para año actual)
        if ($esAnioActual) {
            $data['tramites_retrasados'] = array_slice($this->dashboardModel->getTramitesRetrasados(30), 0, 5);
            $data['pendientes_cobro'] = array_slice($this->dashboardModel->getTramitesPendientesCobro(15), 0, 5);
            $data['tramites_estancados'] = array_slice($this->dashboardModel->getTramitesEstancados(7), 0, 5);
            
            $data['count_retrasados'] = count($this->dashboardModel->getTramitesRetrasados(30));
            $data['count_pendientes_cobro'] = count($this->dashboardModel->getTramitesPendientesCobro(15));
            $data['count_estancados'] = count($this->dashboardModel->getTramitesEstancados(7));
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
        $data['top_ejecutivos'] = $this->dashboardModel->getTopEjecutivos(5, 'anio', $anio);
        $data['top_gestores'] = $this->dashboardModel->getTopGestores(5, 'anio', $anio);
        
        // Embudo de conversión del año
        $fechaInicio = $anio . '-01-01';
        $fechaFin = $anio . '-12-31';
        $data['embudo'] = $this->dashboardModel->getEmbudoConversion($fechaInicio, $fechaFin);
        
        // Distribución por estado (del año)
        $data['distribucion_estados'] = $this->dashboardModel->getDistribucionPorEstado($anio);
        
        // Comparativa semanal (solo año actual)
        if ($esAnioActual) {
            $data['comparativa_semanal'] = $this->dashboardModel->getComparativaSemanal();
        }
        
        // Records históricos
        $data['records'] = $this->dashboardModel->getRecordsHistoricos();
        
        return view('deskapp/dashboard/dashboard_admin', $data);
    }

    /**
     * Vista de alertas completas
     */
    public function alertas()
    {
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        
        // Obtener todas las alertas sin límite
        $data['tramites_retrasados'] = $this->dashboardModel->getTramitesRetrasados(30);
        $data['pendientes_cobro'] = $this->dashboardModel->getTramitesPendientesCobro(15);
        $data['tramites_estancados'] = $this->dashboardModel->getTramitesEstancados(7);
        
        return view('deskapp/dashboard/alertas', $data);
    }

    /**
     * Vista de análisis financiero
     */
    public function financiero()
    {
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        
        // Aging report
        $data['aging_report'] = $this->dashboardModel->getAgingReport();
        $data['resumen_rangos'] = $this->dashboardModel->getResumenFinancieroPorRangos();
        $data['proyeccion'] = $this->dashboardModel->getProyeccionIngresos();
        
        // Métricas financieras
        $data['metricas_mes'] = $this->dashboardModel->getMetricasMes();
        $data['metricas_anio'] = $this->dashboardModel->getMetricasAnio();
        
        return view('deskapp/dashboard/financiero', $data);
    }

    /**
     * Vista de reportes y gráficas
     */
    public function reportes()
    {
        $data['session'] = \Config\Services::session();
        $data['username'] = $this->session->get('user_name');
        
        // Obtener año de la URL o usar el actual
        $anio = $this->request->getGet('anio') ?? date('Y');
        $esAnioActual = ($anio == date('Y'));
        
        $data['anio_seleccionado'] = $anio;
        $data['es_anio_actual'] = $esAnioActual;
        
        // Datos para gráficas (del año seleccionado)
        $data['tramites_por_mes'] = $this->dashboardModel->getTramitesPorMes($anio);
        $data['ingresos_por_mes'] = $this->dashboardModel->getIngresosPorMes($anio);
        $data['tramites_por_tipo'] = $this->dashboardModel->getTramitesPorTipo('anio', $anio);
        
        // Rankings (del año seleccionado)
        $data['top_ejecutivos'] = $this->dashboardModel->getTopEjecutivos(10, 'anio', $anio);
        $data['top_gestores'] = $this->dashboardModel->getTopGestores(10, 'anio', $anio);
        
        return view('deskapp/dashboard/reportes', $data);
    }

    /**
     * API: Obtener métricas en tiempo real (JSON)
     */
    public function api_metricas()
    {
        $periodo = $this->request->getGet('periodo') ?? 'hoy';
        
        $data = [];
        switch ($periodo) {
            case 'hoy':
                $data = $this->dashboardModel->getMetricasHoy();
                break;
            case 'semana':
                $data = $this->dashboardModel->getMetricasSemana();
                break;
            case 'mes':
                $data = $this->dashboardModel->getMetricasMes();
                break;
            case 'anio':
                $data = $this->dashboardModel->getMetricasAnio();
                break;
            case 'enero_fecha':
                $data = $this->dashboardModel->getMetricasEneroALaFecha();
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
        
        $data = [];
        switch ($tipo) {
            case 'retrasados':
                $data = $this->dashboardModel->getTramitesRetrasados(30);
                break;
            case 'pendientes_cobro':
                $data = $this->dashboardModel->getTramitesPendientesCobro(15);
                break;
            case 'estancados':
                $data = $this->dashboardModel->getTramitesEstancados(7);
                break;
            default:
                $data = $this->dashboardModel->getAlertasCriticas();
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
        
        $data = [];
        switch ($tipo) {
            case 'tramites_mes':
                $data = $this->dashboardModel->getTramitesPorMes($anio);
                break;
            case 'ingresos_mes':
                $data = $this->dashboardModel->getIngresosPorMes($anio);
                break;
            case 'tramites_tipo':
                $periodo = $this->request->getGet('periodo') ?? 'mes';
                $data = $this->dashboardModel->getTramitesPorTipo($periodo);
                break;
            case 'distribucion_estados':
                $data = $this->dashboardModel->getDistribucionPorEstado();
                break;
            case 'embudo':
                $data = $this->dashboardModel->getEmbudoConversion();
                break;
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener KPIs principales (JSON)
     */
    public function api_kpis()
    {
        $data = $this->dashboardModel->getKPIsPrincipales();
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener comparativas (JSON)
     */
    public function api_comparativas()
    {
        $tipo = $this->request->getGet('tipo') ?? 'semanal';
        
        $data = [];
        switch ($tipo) {
            case 'semanal':
                $data = $this->dashboardModel->getComparativaSemanal();
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
        
        $data = [];
        switch ($tipo) {
            case 'ejecutivos':
                $data = $this->dashboardModel->getTopEjecutivos($limite, $periodo);
                break;
            case 'gestores':
                $data = $this->dashboardModel->getTopGestores($limite, $periodo);
                break;
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * API: Obtener resumen financiero (JSON)
     */
    public function api_financiero()
    {
        $data = [
            'aging_report' => $this->dashboardModel->getAgingReport(),
            'resumen_rangos' => $this->dashboardModel->getResumenFinancieroPorRangos(),
            'proyeccion' => $this->dashboardModel->getProyeccionIngresos(),
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

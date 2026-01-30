<?php
namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * ========================================
     * MÉTRICAS GENERALES POR PERÍODO
     * ========================================
     */

    /**
     * Obtener métricas del día actual
     */
    public function getMetricasHoy()
    {
        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
                SUM(CASE WHEN (numero_factura IS NOT NULL AND numero_factura != '' OR numero_refactura IS NOT NULL AND numero_refactura != '') 
                    AND cobro_status_id = 22 THEN 1 ELSE 0 END) as pendientes_cobro,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado_hoy
            FROM tramite
            WHERE DATE(created_at) = CURDATE()
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * Obtener métricas de la semana actual
     */
    public function getMetricasSemana()
    {
        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado,
                AVG(CASE WHEN finished_at IS NOT NULL THEN DATEDIFF(finished_at, started_at) END) as tiempo_promedio_dias
            FROM tramite
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * Obtener métricas del mes actual
     */
    public function getMetricasMes()
    {
        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado,
                SUM(CASE WHEN (numero_factura IS NOT NULL AND numero_factura != '' OR numero_refactura IS NOT NULL AND numero_refactura != '') 
                    AND cobro_status_id = 22 THEN costo_total ELSE 0 END) as monto_por_cobrar
            FROM tramite
            WHERE YEAR(created_at) = YEAR(CURDATE()) 
            AND MONTH(created_at) = MONTH(CURDATE())
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * Obtener métricas del año actual o año específico
     */
    public function getMetricasAnio($anio = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado_anio,
                AVG(CASE WHEN finished_at IS NOT NULL THEN DATEDIFF(finished_at, started_at) END) as tiempo_promedio_dias
            FROM tramite
            WHERE YEAR(created_at) = ?
        ";
        
        return $this->db->query($query, [$anio])->getRowArray();
    }

    /**
     * Obtener métricas desde enero a la fecha (o año específico)
     */
    public function getMetricasEneroALaFecha($anio = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $esAnioActual = ($anio == date('Y'));
        $fechaFin = $esAnioActual ? 'CURDATE() + INTERVAL 1 DAY' : "'$anio-12-31'";
        
        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN tra_status_id = 21 THEN 1 ELSE 0 END) as total_cancelados,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado,
                SUM(CASE WHEN (numero_factura IS NOT NULL AND numero_factura != '' OR numero_refactura IS NOT NULL AND numero_refactura != '') 
                    AND cobro_status_id = 22 THEN 1 ELSE 0 END) as pendientes_cobro,
                SUM(CASE WHEN (numero_factura IS NOT NULL AND numero_factura != '' OR numero_refactura IS NOT NULL AND numero_refactura != '') 
                    AND cobro_status_id = 22 THEN costo_total ELSE 0 END) as monto_por_cobrar
            FROM tramite
            WHERE created_at >= '$anio-01-01'
            AND created_at < $fechaFin
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * ========================================
     * ALERTAS Y SITUACIONES CRÍTICAS
     * ========================================
     */

    /**
     * Obtener trámites con tiempos excedidos
     */
    public function getTramitesRetrasados($diasLimite = 30)
    {
        $query = "
            SELECT 
                t.id,
                t.folio,
                t.contrato,
                t.unidad,
                t.created_at,
                t.started_at,
                DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at)) as dias_transcurridos,
                ts.tra_status,
                tt.tipo_tramite,
                CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo,
                cd.razon_social AS cliente
            FROM tramite t
            LEFT JOIN tra_status ts ON t.tra_status_id = ts.id
            LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN cli_directo cd ON t.cli_directo_id = cd.id
            WHERE t.tra_status_id NOT IN (20, 21)  -- No concluidos ni cancelados
            AND DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at)) > ?
            ORDER BY dias_transcurridos DESC
        ";
        
        return $this->db->query($query, [$diasLimite])->getResultArray();
    }

    /**
     * Obtener trámites facturados sin cobrar
     */
    public function getTramitesPendientesCobro($diasLimite = 15)
    {
        $query = "
            SELECT 
                t.id,
                t.folio,
                t.contrato,
                t.unidad,
                t.numero_factura,
                t.numero_refactura,
                t.created_at,
                t.finished_at,
                t.costo_total,
                DATEDIFF(CURDATE(), t.finished_at) as dias_sin_cobrar,
                cs.cobro_status,
                cd.razon_social AS cliente,
                CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo
            FROM tramite t
            LEFT JOIN cobro_statuses cs ON t.cobro_status_id = cs.id
            LEFT JOIN cli_directo cd ON t.cli_directo_id = cd.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND (
                (t.numero_factura IS NOT NULL AND t.numero_factura != '')
                OR 
                (t.numero_refactura IS NOT NULL AND t.numero_refactura != '')
            )
            AND t.cobro_status_id = 22
            AND t.finished_at IS NOT NULL
            AND DATEDIFF(CURDATE(), t.finished_at) > ?
            ORDER BY dias_sin_cobrar DESC
        ";
        
        return $this->db->query($query, [$diasLimite])->getResultArray();
    }

    /**
     * Obtener trámites sin movimiento (estancados)
     */
    public function getTramitesEstancados($diasLimite = 7)
    {
        $query = "
            SELECT 
                t.id,
                t.folio,
                t.contrato,
                t.unidad,
                t.created_at,
                DATEDIFF(CURDATE(), t.created_at) as dias_sin_movimiento,
                ts.tra_status,
                tt.tipo_tramite,
                cd.razon_social AS cliente,
                CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo
            FROM tramite t
            LEFT JOIN tra_status ts ON t.tra_status_id = ts.id
            LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
            LEFT JOIN cli_directo cd ON t.cli_directo_id = cd.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND t.started_at IS NULL
            AND DATEDIFF(CURDATE(), t.created_at) > ?
            ORDER BY dias_sin_movimiento DESC
        ";
        
        return $this->db->query($query, [$diasLimite])->getResultArray();
    }

    /**
     * Obtener todas las alertas críticas
     */
    public function getAlertasCriticas()
    {
        $alertas = [
            'tramites_retrasados' => $this->getTramitesRetrasados(30),
            'pendientes_cobro' => $this->getTramitesPendientesCobro(15),
            'tramites_estancados' => $this->getTramitesEstancados(7),
        ];

        return $alertas;
    }

    /**
     * ========================================
     * EMBUDO DE CONVERSIÓN
     * ========================================
     */

    /**
     * Obtener datos del embudo de conversión
     */
    public function getEmbudoConversion($fechaInicio = null, $fechaFin = null)
    {
        $whereDate = "";
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $whereDate = "WHERE created_at >= ? AND created_at <= ?";
            $params = [$fechaInicio, $fechaFin];
        }

        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as concluidos,
                SUM(CASE WHEN (numero_factura IS NOT NULL AND numero_factura != '' 
                    OR numero_refactura IS NOT NULL AND numero_refactura != '') THEN 1 ELSE 0 END) as facturados,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as cobrados
            FROM tramite
            $whereDate
        ";
        
        $result = $this->db->query($query, $params)->getRowArray();
        
        // Calcular porcentajes
        $total = $result['total_ingresados'];
        if ($total > 0) {
            $result['pct_en_proceso'] = round(($result['en_proceso'] / $total) * 100, 2);
            $result['pct_concluidos'] = round(($result['concluidos'] / $total) * 100, 2);
            $result['pct_facturados'] = round(($result['facturados'] / $total) * 100, 2);
            $result['pct_cobrados'] = round(($result['cobrados'] / $total) * 100, 2);
        }
        
        return $result;
    }

    /**
     * ========================================
     * RANKINGS Y TOP
     * ========================================
     */

    /**
     * Obtener top 5 ejecutivos por trámites concluidos
     */
    public function getTopEjecutivos($limite = 5, $periodo = 'mes', $anio = null)
    {
        if ($periodo == 'anio' && $anio) {
            $whereDate = "YEAR(t.created_at) = $anio";
        } else {
            $whereDate = $this->getWherePeriodo($periodo);
        }

        $query = "
            SELECT 
                CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo,
                COUNT(*) as total_tramites,
                SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as tramites_concluidos,
                SUM(CASE WHEN t.cobro_status_id = 23 THEN 1 ELSE 0 END) as tramites_cobrados,
                SUM(CASE WHEN t.cobro_status_id = 23 THEN t.costo_total ELSE 0 END) as monto_cobrado,
                AVG(CASE WHEN t.finished_at IS NOT NULL AND t.started_at IS NOT NULL 
                    THEN DATEDIFF(t.finished_at, t.started_at) END) as tiempo_promedio_dias
            FROM tramite t
            INNER JOIN users u ON t.user_id = u.id
            WHERE $whereDate
            GROUP BY u.id, u.firstname, u.lastname
            ORDER BY tramites_concluidos DESC, monto_cobrado DESC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$limite])->getResultArray();
    }

    /**
     * Obtener top gestores por eficiencia
     */
    public function getTopGestores($limite = 5, $periodo = 'mes', $anio = null)
    {
        if ($periodo == 'anio' && $anio) {
            $whereDate = "YEAR(t.created_at) = $anio";
        } else {
            $whereDate = $this->getWherePeriodo($periodo);
        }

        $query = "
            SELECT 
                g.nombre AS gestor,
                eg.nombre AS empresa_gestora,
                COUNT(*) as total_tramites,
                SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as tramites_concluidos,
                AVG(CASE WHEN t.finished_at IS NOT NULL AND t.started_at IS NOT NULL 
                    THEN DATEDIFF(t.finished_at, t.started_at) END) as tiempo_promedio_dias,
                SUM(t.gestor_total_pago) as total_pagado_gestor
            FROM tramite t
            LEFT JOIN ges_gestor g ON t.gestor_id = g.id
            LEFT JOIN ges_empresa_gestora eg ON t.empresa_gestora_id = eg.id
            WHERE $whereDate
            AND t.gestor_id IS NOT NULL
            GROUP BY g.id, g.nombre, eg.nombre
            ORDER BY tramites_concluidos DESC, tiempo_promedio_dias ASC
            LIMIT ?
        ";
        
        return $this->db->query($query, [$limite])->getResultArray();
    }

    /**
     * ========================================
     * ANÁLISIS FINANCIERO
     * ========================================
     */

    /**
     * Obtener reporte de cuentas por cobrar (Aging Report)
     */
    public function getAgingReport()
    {
        $query = "
            SELECT 
                t.id,
                t.folio,
                t.contrato,
                t.numero_factura,
                t.numero_refactura,
                t.finished_at,
                t.costo_total,
                DATEDIFF(CURDATE(), t.finished_at) as dias_vencidos,
                CASE 
                    WHEN DATEDIFF(CURDATE(), t.finished_at) <= 15 THEN '0-15 días'
                    WHEN DATEDIFF(CURDATE(), t.finished_at) <= 30 THEN '16-30 días'
                    WHEN DATEDIFF(CURDATE(), t.finished_at) <= 60 THEN '31-60 días'
                    WHEN DATEDIFF(CURDATE(), t.finished_at) <= 90 THEN '61-90 días'
                    ELSE 'Más de 90 días'
                END as rango_dias,
                cd.razon_social AS cliente,
                CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo
            FROM tramite t
            LEFT JOIN cli_directo cd ON t.cli_directo_id = cd.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE (t.numero_factura IS NOT NULL AND t.numero_factura != '' 
                OR t.numero_refactura IS NOT NULL AND t.numero_refactura != '')
            AND t.cobro_status_id = 22
            AND t.finished_at IS NOT NULL
            ORDER BY dias_vencidos DESC
        ";
        
        return $this->db->query($query)->getResultArray();
    }

    /**
     * Obtener resumen financiero por rangos
     */
    public function getResumenFinancieroPorRangos()
    {
        $query = "
            SELECT 
                CASE 
                    WHEN DATEDIFF(CURDATE(), finished_at) <= 15 THEN '0-15 días'
                    WHEN DATEDIFF(CURDATE(), finished_at) <= 30 THEN '16-30 días'
                    WHEN DATEDIFF(CURDATE(), finished_at) <= 60 THEN '31-60 días'
                    WHEN DATEDIFF(CURDATE(), finished_at) <= 90 THEN '61-90 días'
                    ELSE 'Más de 90 días'
                END as rango,
                COUNT(*) as cantidad_tramites,
                SUM(costo_total) as monto_total
            FROM tramite
            WHERE (numero_factura IS NOT NULL AND numero_factura != '' 
                OR numero_refactura IS NOT NULL AND numero_refactura != '')
            AND cobro_status_id = 22
            AND finished_at IS NOT NULL
            GROUP BY rango
            ORDER BY FIELD(rango, '0-15 días', '16-30 días', '31-60 días', '61-90 días', 'Más de 90 días')
        ";
        
        return $this->db->query($query)->getResultArray();
    }

    /**
     * Obtener proyección de ingresos
     */
    public function getProyeccionIngresos()
    {
        $query = "
            SELECT 
                SUM(CASE WHEN cobro_status_id = 22 AND (numero_factura IS NOT NULL AND numero_factura != '' 
                    OR numero_refactura IS NOT NULL AND numero_refactura != '') THEN costo_total ELSE 0 END) as pendiente_cobro,
                SUM(CASE WHEN cobro_status_id = 23 AND MONTH(created_at) = MONTH(CURDATE()) 
                    THEN costo_total ELSE 0 END) as cobrado_mes_actual,
                SUM(CASE WHEN tra_status_id NOT IN (20, 21) THEN costo_total ELSE 0 END) as en_proceso_estimado,
                COUNT(CASE WHEN tra_status_id NOT IN (20, 21) THEN 1 END) as tramites_en_proceso
            FROM tramite
            WHERE YEAR(created_at) = YEAR(CURDATE())
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * ========================================
     * GRÁFICAS Y TENDENCIAS
     * ========================================
     */

    /**
     * Obtener datos para gráfica de trámites por mes
     */
    public function getTramitesPorMes($anio = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }

        $query = "
            SELECT 
                MONTH(created_at) as mes,
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados
            FROM tramite
            WHERE YEAR(created_at) = ?
            GROUP BY MONTH(created_at)
            ORDER BY mes
        ";
        
        return $this->db->query($query, [$anio])->getResultArray();
    }

    /**
     * Obtener datos para gráfica de ingresos por mes
     */
    public function getIngresosPorMes($anio = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }

        $query = "
            SELECT 
                MONTH(created_at) as mes,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as ingresos
            FROM tramite
            WHERE YEAR(created_at) = ?
            GROUP BY MONTH(created_at)
            ORDER BY mes
        ";
        
        return $this->db->query($query, [$anio])->getResultArray();
    }

    /**
     * Obtener trámites por tipo
     */
    public function getTramitesPorTipo($periodo = 'mes', $anio = null)
    {
        // Si se especifica año y el periodo es 'anio', filtrar por ese año
        if ($anio && $periodo == 'anio') {
            $whereDate = "YEAR(t.created_at) = " . (int)$anio;
        } else {
            $whereDate = $this->getWherePeriodo($periodo);
        }

        $query = "
            SELECT 
                tt.tipo_tramite,
                COUNT(*) as cantidad,
                SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as concluidos,
                AVG(CASE WHEN t.finished_at IS NOT NULL AND t.started_at IS NOT NULL 
                    THEN DATEDIFF(t.finished_at, t.started_at) END) as tiempo_promedio
            FROM tramite t
            LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
            WHERE $whereDate
            GROUP BY tt.id, tt.tipo_tramite
            ORDER BY cantidad DESC
        ";
        
        return $this->db->query($query)->getResultArray();
    }

    /**
     * Obtener distribución de trámites por estado
     */
    public function getDistribucionPorEstado($anio = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $query = "
            SELECT 
                ts.tra_status,
                COUNT(*) as cantidad,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tramite WHERE tra_status_id NOT IN (20, 21) AND YEAR(created_at) = ?)), 2) as porcentaje
            FROM tramite t
            LEFT JOIN tra_status ts ON t.tra_status_id = ts.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND YEAR(t.created_at) = ?
            GROUP BY ts.id, ts.tra_status
            ORDER BY cantidad DESC
        ";
        
        return $this->db->query($query, [$anio, $anio])->getResultArray();
    }

    /**
     * ========================================
     * INDICADORES DE RENDIMIENTO (KPIs)
     * ========================================
     */

    /**
     * Obtener KPIs principales
     */
    public function getKPIsPrincipales($anio = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $query = "
            SELECT 
                -- Trámites activos
                COUNT(CASE WHEN tra_status_id NOT IN (20, 21) THEN 1 END) as tramites_activos,
                
                -- Tasa de conversión (concluidos / ingresados) del mes
                ROUND(
                    (SUM(CASE WHEN tra_status_id = 20 AND MONTH(finished_at) = MONTH(CURDATE()) AND YEAR(finished_at) = ? THEN 1 ELSE 0 END) * 100.0) /
                    NULLIF(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = ? THEN 1 ELSE 0 END), 0)
                , 2) as tasa_conversion_mes,
                
                -- Tiempo promedio de gestión (días)
                ROUND(AVG(CASE WHEN finished_at IS NOT NULL AND started_at IS NOT NULL 
                    AND MONTH(finished_at) = MONTH(CURDATE()) AND YEAR(finished_at) = ?
                    THEN DATEDIFF(finished_at, started_at) END), 1) as tiempo_promedio_gestion,
                
                -- Eficiencia de cobro (cobrados / facturados)
                ROUND(
                    (SUM(CASE WHEN cobro_status_id = 23 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = ? THEN 1 ELSE 0 END) * 100.0) /
                    NULLIF(SUM(CASE WHEN (numero_factura IS NOT NULL AND numero_factura != '' 
                        OR numero_refactura IS NOT NULL AND numero_refactura != '') 
                        AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = ? THEN 1 ELSE 0 END), 0)
                , 2) as eficiencia_cobro,
                
                -- Promedio de días para cobrar
                ROUND(AVG(CASE WHEN cobro_status_id = 23 AND finished_at IS NOT NULL 
                    AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = ?
                    THEN DATEDIFF(CURDATE(), finished_at) END), 1) as dias_promedio_cobro,
                
                -- Total pendiente de cobro
                SUM(CASE WHEN cobro_status_id = 22 AND (numero_factura IS NOT NULL AND numero_factura != '' 
                    OR numero_refactura IS NOT NULL AND numero_refactura != '') 
                    THEN costo_total ELSE 0 END) as monto_pendiente_cobro
                
            FROM tramite
            WHERE YEAR(created_at) = ?
        ";
        
        return $this->db->query($query, [$anio, $anio, $anio, $anio, $anio, $anio, $anio])->getRowArray();
    }

    /**
     * ========================================
     * COMPARATIVAS
     * ========================================
     */

    /**
     * Comparar semana actual vs semana anterior
     */
    public function getComparativaSemanal()
    {
        $query = "
            SELECT 
                'Semana Actual' as periodo,
                COUNT(*) as tramites_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as tramites_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado
            FROM tramite
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            
            UNION ALL
            
            SELECT 
                'Semana Anterior' as periodo,
                COUNT(*) as tramites_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as tramites_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado
            FROM tramite
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) - 1
        ";
        
        $result = $this->db->query($query)->getResultArray();
        
        // Calcular variación porcentual
        if (count($result) == 2) {
            $actual = $result[0];
            $anterior = $result[1];
            
            $variacion = [
                'tramites_ingresados' => $this->calcularVariacion($actual['tramites_ingresados'], $anterior['tramites_ingresados']),
                'tramites_concluidos' => $this->calcularVariacion($actual['tramites_concluidos'], $anterior['tramites_concluidos']),
                'monto_cobrado' => $this->calcularVariacion($actual['monto_cobrado'], $anterior['monto_cobrado']),
            ];
            
            return [
                'actual' => $actual,
                'anterior' => $anterior,
                'variacion' => $variacion
            ];
        }
        
        return $result;
    }

    /**
     * ========================================
     * FUNCIONES AUXILIARES
     * ========================================
     */

    /**
     * Obtener cláusula WHERE según el período
     */
    private function getWherePeriodo($periodo)
    {
        switch ($periodo) {
            case 'hoy':
                return "DATE(t.created_at) = CURDATE()";
            case 'semana':
                return "YEARWEEK(t.created_at, 1) = YEARWEEK(CURDATE(), 1)";
            case 'mes':
                return "YEAR(t.created_at) = YEAR(CURDATE()) AND MONTH(t.created_at) = MONTH(CURDATE())";
            case 'anio':
                return "YEAR(t.created_at) = YEAR(CURDATE())";
            case 'enero_fecha':
                return "t.created_at >= CONCAT(YEAR(CURDATE()), '-01-01') AND t.created_at < CURDATE() + INTERVAL 1 DAY";
            default:
                return "1=1"; // Sin filtro
        }
    }

    /**
     * Calcular variación porcentual
     */
    private function calcularVariacion($actual, $anterior)
    {
        if ($anterior == 0) {
            return $actual > 0 ? 100 : 0;
        }
        return round((($actual - $anterior) / $anterior) * 100, 2);
    }

    /**
     * Obtener récords históricos
     */
    public function getRecordsHistoricos()
    {
        $query = "
            SELECT 
                'Mayor cantidad de trámites en un día' as record,
                MAX(tramites) as valor,
                fecha
            FROM (
                SELECT DATE(created_at) as fecha, COUNT(*) as tramites
                FROM tramite
                GROUP BY DATE(created_at)
            ) as daily
            WHERE tramites = (SELECT MAX(tramites) FROM (
                SELECT COUNT(*) as tramites FROM tramite GROUP BY DATE(created_at)
            ) as t)
            
            UNION ALL
            
            SELECT 
                'Mayor cantidad de trámites concluidos en una semana' as record,
                MAX(tramites) as valor,
                CONCAT(YEAR(semana), '-W', LPAD(WEEK(semana, 1), 2, '0')) as fecha
            FROM (
                SELECT DATE(finished_at) as semana, COUNT(*) as tramites
                FROM tramite
                WHERE tra_status_id = 20
                GROUP BY YEARWEEK(finished_at, 1)
            ) as weekly
            WHERE tramites = (SELECT MAX(tramites) FROM (
                SELECT COUNT(*) as tramites FROM tramite WHERE tra_status_id = 20 GROUP BY YEARWEEK(finished_at, 1)
            ) as t)
            
            UNION ALL
            
            SELECT 
                'Mayor monto cobrado en un mes' as record,
                MAX(monto) as valor,
                CONCAT(YEAR(mes), '-', LPAD(MONTH(mes), 2, '0')) as fecha
            FROM (
                SELECT DATE(created_at) as mes, SUM(costo_total) as monto
                FROM tramite
                WHERE cobro_status_id = 23
                GROUP BY YEAR(created_at), MONTH(created_at)
            ) as monthly
            WHERE monto = (SELECT MAX(monto) FROM (
                SELECT SUM(costo_total) as monto FROM tramite WHERE cobro_status_id = 23 
                GROUP BY YEAR(created_at), MONTH(created_at)
            ) as t)
        ";
        
        return $this->db->query($query)->getResultArray();
    }
}

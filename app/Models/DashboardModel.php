<?php
namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;
    protected $clienteIdFilter = null;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Establece un filtro opcional por cliente (tabla cliente.id)
     * para restringir TODAS las consultas del dashboard en esta instancia.
     */
    public function setClienteIdFilter($clienteId): void
    {
        if ($clienteId === null || $clienteId === '' || !is_numeric($clienteId)) {
            $this->clienteIdFilter = null;
            return;
        }

        $clienteId = (int) $clienteId;
        $this->clienteIdFilter = $clienteId > 0 ? $clienteId : null;
    }

    /**
     * Generar filtro SQL por cliente (multi-tenancy)
     * 
     * @param int|null $userId ID del usuario (null = sin filtro)
     * @param string $tramiteAlias Alias de la tabla tramite en la consulta (default: 'tramite')
     * @return string Condición SQL para agregar al WHERE
     */
    private function getClienteFilterSQL($userId = null, $tramiteAlias = 'tramite')
    {
        // Filtro específico por cliente (si se estableció)
        if ($this->clienteIdFilter !== null) {
            $clienteId = (int) $this->clienteIdFilter;

            helper('cliente_filter');

            // Defensa en profundidad: si no tiene acceso global y no tiene acceso, no retornar datos
            if ($userId !== null && !user_has_global_cliente_access($userId) && !has_access_to_cliente($clienteId, $userId)) {
                return '1 = 0';
            }

            return "{$tramiteAlias}.id IN (
                SELECT t.id
                FROM tramite t
                INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
                WHERE cd.cliente_id = {$clienteId}
            )";
        }

        if ($userId === null) {
            return '1 = 1'; // Sin filtro
        }
        
        helper('cliente_filter');
        
        // Verificar si tiene acceso global (sin restricciones)
        if (user_has_global_cliente_access($userId)) {
            return '1 = 1'; // Admin global ve todo
        }
        
        // Obtener clientes del usuario
        $clienteIds = get_user_cliente_ids($userId);
        
        if (empty($clienteIds)) {
            return '1 = 0'; // Sin acceso
        }
        
        // Generar IN clause usando el alias correcto
        $clienteIdsStr = implode(',', array_map('intval', $clienteIds));
        return "{$tramiteAlias}.id IN (
            SELECT t.id 
            FROM tramite t
            INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
            WHERE cd.cliente_id IN ($clienteIdsStr)
        )";
    }

    /**
     * ========================================
     * MÉTRICAS GENERALES POR PERÍODO
     * ========================================
     */

    /**
     * Obtener métricas del día actual
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Métricas del día
     */
    public function getMetricasHoy($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
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
            AND ($filtroCliente)
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * Obtener métricas de la semana actual
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Métricas de la semana
     */
    public function getMetricasSemana($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado,
                AVG(CASE WHEN finished_at IS NOT NULL THEN DATEDIFF(finished_at, started_at) END) as tiempo_promedio_dias
            FROM tramite
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            AND ($filtroCliente)
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * Obtener métricas del mes actual
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Métricas del mes
     */
    public function getMetricasMes($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
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
            AND ($filtroCliente)
        ";
        
        return $this->db->query($query)->getRowArray();
    }

    /**
     * Obtener métricas del año actual o año específico
     * 
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Métricas del año
     */
    public function getMetricasAnio($anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
        $query = "
            SELECT 
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado_anio,
                AVG(CASE WHEN finished_at IS NOT NULL THEN DATEDIFF(finished_at, started_at) END) as tiempo_promedio_dias
            FROM tramite
            WHERE YEAR(created_at) = ?
            AND ($filtroCliente)
        ";
        
        return $this->db->query($query, [$anio])->getRowArray();
    }

    /**
     * Obtener métricas desde enero a la fecha (o año específico)
     * 
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Métricas desde enero
     */
    public function getMetricasEneroALaFecha($anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
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
            AND ($filtroCliente)
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
     * 
     * @param int $diasLimite Días límite para considerar retrasado
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Lista de trámites retrasados
     */
    public function getTramitesRetrasados($diasLimite = 30, $userId = null, $limit = null, $offset = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $limitSql = '';

        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $limitSql = " LIMIT {$limit} OFFSET {$offset}";
        }
        
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
            AND ($filtroCliente)
            ORDER BY dias_transcurridos DESC
            $limitSql
        ";
        
        return $this->db->query($query, [$diasLimite])->getResultArray();
    }

    /**
     * Contar trámites con tiempos excedidos
     */
    public function countTramitesRetrasados($diasLimite = 30, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');

        $query = "
            SELECT COUNT(*) as total
            FROM tramite t
            WHERE t.tra_status_id NOT IN (20, 21)
            AND DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at)) > ?
            AND ($filtroCliente)
        ";

        $row = $this->db->query($query, [$diasLimite])->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtener trámites facturados sin cobrar
     * 
     * @param int $diasLimite Días límite para considerar pendiente
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Lista de trámites pendientes de cobro
     */
    public function getTramitesPendientesCobro($diasLimite = 15, $userId = null, $limit = null, $offset = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $limitSql = '';

        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $limitSql = " LIMIT {$limit} OFFSET {$offset}";
        }
        
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
            AND ($filtroCliente)
            ORDER BY dias_sin_cobrar DESC
            $limitSql
        ";
        
        return $this->db->query($query, [$diasLimite])->getResultArray();
    }

    /**
     * Contar trámites facturados sin cobrar
     */
    public function countTramitesPendientesCobro($diasLimite = 15, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');

        $query = "
            SELECT COUNT(*) as total
            FROM tramite t
            WHERE t.tra_status_id NOT IN (20, 21)
            AND (
                (t.numero_factura IS NOT NULL AND t.numero_factura != '')
                OR
                (t.numero_refactura IS NOT NULL AND t.numero_refactura != '')
            )
            AND t.cobro_status_id = 22
            AND t.finished_at IS NOT NULL
            AND DATEDIFF(CURDATE(), t.finished_at) > ?
            AND ($filtroCliente)
        ";

        $row = $this->db->query($query, [$diasLimite])->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtener trámites sin movimiento (estancados)
     * 
     * @param int $diasLimite Días límite para considerar estancado
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Lista de trámites estancados
     */
    public function getTramitesEstancados($diasLimite = 7, $userId = null, $limit = null, $offset = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $limitSql = '';

        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $limitSql = " LIMIT {$limit} OFFSET {$offset}";
        }
        
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
            AND ($filtroCliente)
            ORDER BY dias_sin_movimiento DESC
            $limitSql
        ";
        
        return $this->db->query($query, [$diasLimite])->getResultArray();
    }

    /**
     * Contar trámites sin movimiento (estancados)
     */
    public function countTramitesEstancados($diasLimite = 7, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');

        $query = "
            SELECT COUNT(*) as total
            FROM tramite t
            WHERE t.tra_status_id NOT IN (20, 21)
            AND t.started_at IS NULL
            AND DATEDIFF(CURDATE(), t.created_at) > ?
            AND ($filtroCliente)
        ";

        $row = $this->db->query($query, [$diasLimite])->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtener todas las alertas críticas
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Alertas críticas agrupadas
     */
    public function getAlertasCriticas($userId = null)
    {
        $alertas = [
            'tramites_retrasados' => $this->getTramitesRetrasados(30, $userId),
            'pendientes_cobro' => $this->getTramitesPendientesCobro(15, $userId),
            'tramites_estancados' => $this->getTramitesEstancados(7, $userId),
        ];

        return $alertas;
    }

    /**
     * Conteo por semaforo (local vs foraneo) basado en dias transcurridos.
     */
    public function getSemaforoAtencion($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";
        $local = "UPPER(COALESCE(e.entidad, '')) IN ('CIUDAD DE MEXICO', 'CIUDAD DE MÉXICO', 'ESTADO DE MEXICO', 'ESTADO DE MÉXICO', 'CDMX', 'EDOMEX', 'EDO MEX', 'EDO. MEX')";
        $foraneo = "NOT ($local)";

        $query = "
            SELECT
                SUM(CASE WHEN $local AND $dias < 5 THEN 1 ELSE 0 END) as local_verde,
                SUM(CASE WHEN $local AND $dias BETWEEN 5 AND 7 THEN 1 ELSE 0 END) as local_amarillo,
                SUM(CASE WHEN $local AND $dias BETWEEN 8 AND 11 THEN 1 ELSE 0 END) as local_rojo,
                SUM(CASE WHEN $local AND $dias >= 12 THEN 1 ELSE 0 END) as local_violeta,
                SUM(CASE WHEN $foraneo AND $dias < 10 THEN 1 ELSE 0 END) as foraneo_verde,
                SUM(CASE WHEN $foraneo AND $dias BETWEEN 10 AND 12 THEN 1 ELSE 0 END) as foraneo_amarillo,
                SUM(CASE WHEN $foraneo AND $dias BETWEEN 13 AND 15 THEN 1 ELSE 0 END) as foraneo_rojo,
                SUM(CASE WHEN $foraneo AND $dias >= 16 THEN 1 ELSE 0 END) as foraneo_violeta
            FROM tramite t
            LEFT JOIN entidad e ON t.entidad_id = e.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND ($filtroCliente)
        ";

        $row = $this->db->query($query)->getRowArray();
        return $row ?: [];
    }

    /**
     * Construye filtro SQL para el dashboard cliente con parametros.
     */
    private function buildClienteDashboardFilter(array $filters, $userId = null, $alias = 't'): array
    {
        $conditions = [];
        $params = [];

        $conditions[] = '(' . $this->getClienteFilterSQL($userId, $alias) . ')';

        if (!empty($filters['cli_directo_id'])) {
            $conditions[] = "{$alias}.cli_directo_id = ?";
            $params[] = (int) $filters['cli_directo_id'];
        }

        if (!empty($filters['tra_tipos_id'])) {
            $conditions[] = "{$alias}.tra_tipos_id = ?";
            $params[] = (int) $filters['tra_tipos_id'];
        }

        if (!empty($filters['tra_status_id'])) {
            $conditions[] = "{$alias}.tra_status_id = ?";
            $params[] = (int) $filters['tra_status_id'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $conditions[] = "{$alias}.created_at >= ?";
            $params[] = $filters['fecha_inicio'] . ' 00:00:00';
        }

        if (!empty($filters['fecha_fin'])) {
            $conditions[] = "{$alias}.created_at <= ?";
            $params[] = $filters['fecha_fin'] . ' 23:59:59';
        }

        if (isset($filters['pendiente_pago'])) {
            $pendienteSql = "(({$alias}.numero_factura IS NOT NULL AND {$alias}.numero_factura != '') OR ({$alias}.numero_refactura IS NOT NULL AND {$alias}.numero_refactura != '')) AND {$alias}.cobro_status_id = 22";
            if ($filters['pendiente_pago'] === '1') {
                $conditions[] = $pendienteSql;
            } elseif ($filters['pendiente_pago'] === '0') {
                $conditions[] = "NOT ($pendienteSql)";
            }
        }

        $where = implode(' AND ', $conditions);
        return [$where !== '' ? $where : '1 = 1', $params];
    }

    /**
     * Semaforo cliente con filtros adicionales.
     */
    public function getClienteSemaforoAtencion(array $filters, $userId = null)
    {
        [$where, $params] = $this->buildClienteDashboardFilter($filters, $userId, 't');
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";
        $local = "UPPER(COALESCE(e.entidad, '')) IN ('CIUDAD DE MEXICO', 'CIUDAD DE MÉXICO', 'ESTADO DE MEXICO', 'ESTADO DE MÉXICO', 'CDMX', 'EDOMEX', 'EDO MEX', 'EDO. MEX')";
        $foraneo = "NOT ($local)";

        $query = "
            SELECT
                SUM(CASE WHEN $local AND $dias < 5 THEN 1 ELSE 0 END) as local_verde,
                SUM(CASE WHEN $local AND $dias BETWEEN 5 AND 7 THEN 1 ELSE 0 END) as local_amarillo,
                SUM(CASE WHEN $local AND $dias BETWEEN 8 AND 11 THEN 1 ELSE 0 END) as local_rojo,
                SUM(CASE WHEN $local AND $dias >= 12 THEN 1 ELSE 0 END) as local_violeta,
                SUM(CASE WHEN $foraneo AND $dias < 10 THEN 1 ELSE 0 END) as foraneo_verde,
                SUM(CASE WHEN $foraneo AND $dias BETWEEN 10 AND 12 THEN 1 ELSE 0 END) as foraneo_amarillo,
                SUM(CASE WHEN $foraneo AND $dias BETWEEN 13 AND 15 THEN 1 ELSE 0 END) as foraneo_rojo,
                SUM(CASE WHEN $foraneo AND $dias >= 16 THEN 1 ELSE 0 END) as foraneo_violeta
            FROM tramite t
            LEFT JOIN entidad e ON t.entidad_id = e.id
            WHERE $where
            AND t.tra_status_id NOT IN (20, 21)
        ";

        $row = $this->db->query($query, $params)->getRowArray();
        return $row ?: [];
    }

    /**
     * Facturas entregadas y pendientes de pago.
     */
    public function getClienteFacturasPendientes(array $filters, $userId = null)
    {
        [$where, $params] = $this->buildClienteDashboardFilter($filters, $userId, 't');
        $pendienteSql = "((t.numero_factura IS NOT NULL AND t.numero_factura != '') OR (t.numero_refactura IS NOT NULL AND t.numero_refactura != '')) AND t.cobro_status_id = 22";

        $query = "
            SELECT
                COUNT(*) as total,
                SUM(t.costo_total) as monto_total
            FROM tramite t
            WHERE $where
            AND $pendienteSql
        ";

        $row = $this->db->query($query, $params)->getRowArray();
        return $row ?: ['total' => 0, 'monto_total' => 0];
    }

    /**
     * Resumen de tramites para cliente.
     */
    public function getClienteResumen(array $filters, $userId = null)
    {
        [$where, $params] = $this->buildClienteDashboardFilter($filters, $userId, 't');

        $query = "
            SELECT
                COUNT(*) as total_tramites,
                SUM(CASE WHEN t.tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as concluidos,
                SUM(CASE WHEN t.tra_status_id = 21 THEN 1 ELSE 0 END) as cancelados
            FROM tramite t
            WHERE $where
        ";

        $row = $this->db->query($query, $params)->getRowArray();
        return $row ?: [];
    }

    /**
     * Conteo de trámites concluidos por período para dashboard cliente.
     *
     * Importante:
     * - Se basa en la fecha de cierre (t.finished_at) para reflejar "cuántos se concluyeron".
     * - Aplica filtros de contexto (cliente directo / tipo) y multi-tenancy por usuario.
     * - No aplica filtros por estatus (siempre concluidos) ni rango de fechas (el período define la ventana).
     */
    public function getClienteConcluidosPorPeriodos(array $filters, $userId = null): array
    {
        $conditions = [];
        $params = [];

        $conditions[] = '(' . $this->getClienteFilterSQL($userId, 't') . ')';

        if (!empty($filters['cli_directo_id'])) {
            $conditions[] = 't.cli_directo_id = ?';
            $params[] = (int) $filters['cli_directo_id'];
        }

        if (!empty($filters['tra_tipos_id'])) {
            $conditions[] = 't.tra_tipos_id = ?';
            $params[] = (int) $filters['tra_tipos_id'];
        }

        $where = implode(' AND ', $conditions);
        if ($where === '') {
            $where = '1 = 1';
        }

        $query = "
            SELECT
                SUM(CASE WHEN DATE(t.finished_at) = CURDATE() THEN 1 ELSE 0 END) as hoy,
                SUM(CASE WHEN YEARWEEK(t.finished_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) as semana,
                SUM(CASE WHEN YEAR(t.finished_at) = YEAR(CURDATE()) AND MONTH(t.finished_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as mes,
                SUM(CASE WHEN YEAR(t.finished_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as anio
            FROM tramite t
            WHERE $where
            AND t.tra_status_id = 20
            AND t.finished_at IS NOT NULL
        ";

        $row = $this->db->query($query, $params)->getRowArray();
        return $row ?: ['hoy' => 0, 'semana' => 0, 'mes' => 0, 'anio' => 0];
    }

    /**
     * Trámites por tipo con separación en proceso vs concluidos (para gráficas de dashboard cliente).
     *
     * Respeta filtros del dashboard (cliente directo, tipo, rango de fechas, pendiente de pago) y multi-tenancy.
     * No respeta filtro de estatus para poder comparar en proceso vs concluidos en la misma gráfica.
     */
    public function getClienteTramitesPorTipoProcesoVsConcluido($limit = 10, array $filters = [], $userId = null): array
    {
        $limit = max(1, (int) $limit);

        $conditions = [];
        $params = [];

        $conditions[] = '(' . $this->getClienteFilterSQL($userId, 't') . ')';

        if (!empty($filters['cli_directo_id'])) {
            $conditions[] = 't.cli_directo_id = ?';
            $params[] = (int) $filters['cli_directo_id'];
        }

        if (!empty($filters['tra_tipos_id'])) {
            $conditions[] = 't.tra_tipos_id = ?';
            $params[] = (int) $filters['tra_tipos_id'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $conditions[] = 't.created_at >= ?';
            $params[] = $filters['fecha_inicio'] . ' 00:00:00';
        }

        if (!empty($filters['fecha_fin'])) {
            $conditions[] = 't.created_at <= ?';
            $params[] = $filters['fecha_fin'] . ' 23:59:59';
        }

        if (isset($filters['pendiente_pago'])) {
            $pendienteSql = "((t.numero_factura IS NOT NULL AND t.numero_factura != '') OR (t.numero_refactura IS NOT NULL AND t.numero_refactura != '')) AND t.cobro_status_id = 22";
            if ($filters['pendiente_pago'] === '1') {
                $conditions[] = $pendienteSql;
            } elseif ($filters['pendiente_pago'] === '0') {
                $conditions[] = "NOT ($pendienteSql)";
            }
        }

        $where = implode(' AND ', $conditions);
        if ($where === '') {
            $where = '1 = 1';
        }

        $query = "
            SELECT
                COALESCE(tt.tipo_tramite, 'Sin tipo') as tipo,
                SUM(CASE WHEN t.tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as concluidos,
                COUNT(*) as total
            FROM tramite t
            LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
            WHERE $where
            GROUP BY t.tra_tipos_id, tt.tipo_tramite
            ORDER BY total DESC
            LIMIT $limit
        ";

        return $this->db->query($query, $params)->getResultArray();
    }

    /**
     * Tipos de servicio atorados (sin movimiento > 7 dias) con filtros.
     */
    public function getClienteAtoradosPorTipoServicio($limit = 10, array $filters = [], $userId = null)
    {
        [$where, $params] = $this->buildClienteDashboardFilter($filters, $userId, 't');
        $limit = max(1, (int) $limit);
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";

        $query = "
            SELECT
                COALESCE(tt.tipo_tramite, 'Sin tipo') as tipo,
                COUNT(*) as total
            FROM tramite t
            LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
            WHERE $where
            AND t.tra_status_id NOT IN (20, 21)
            AND $dias > 7
            GROUP BY tt.id, tt.tipo_tramite
            ORDER BY total DESC
            LIMIT $limit
        ";

        return $this->db->query($query, $params)->getResultArray();
    }

    /**
     * Estados con mayor atraso (sin movimiento > 7 dias) con filtros.
     */
    public function getClienteAtoradosPorEstado($limit = 10, array $filters = [], $userId = null)
    {
        [$where, $params] = $this->buildClienteDashboardFilter($filters, $userId, 't');
        $limit = max(1, (int) $limit);
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";

        $query = "
            SELECT
                COALESCE(e.entidad, 'Sin entidad') as estado,
                COUNT(*) as total
            FROM tramite t
            LEFT JOIN entidad e ON t.entidad_id = e.id
            WHERE $where
            AND t.tra_status_id NOT IN (20, 21)
            AND $dias > 7
            GROUP BY e.id, e.entidad
            ORDER BY total DESC
            LIMIT $limit
        ";

        return $this->db->query($query, $params)->getResultArray();
    }

    /**
     * Clientes con mayor atraso (sin movimiento > 7 dias) con filtros.
     */
    public function getClienteAtoradosPorCliente($limit = 10, array $filters = [], $userId = null)
    {
        [$where, $params] = $this->buildClienteDashboardFilter($filters, $userId, 't');
        $limit = max(1, (int) $limit);
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";

        $query = "
            SELECT
                COALESCE(NULLIF(c.razon_social, ''), NULLIF(c.nombre, ''), NULLIF(cd.razon_social, ''), CONCAT('Cliente #', c.id)) as cliente,
                COUNT(*) as total
            FROM tramite t
            INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
            INNER JOIN cliente c ON cd.cliente_id = c.id
            WHERE $where
            AND t.tra_status_id NOT IN (20, 21)
            AND $dias > 7
            GROUP BY c.id, cliente
            ORDER BY total DESC
            LIMIT $limit
        ";

        return $this->db->query($query, $params)->getResultArray();
    }

    /**
     * Tipos de servicio atorados (sin movimiento > 7 dias).
     */
    public function getAtoradosPorTipoServicio($limit = 10, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $limit = max(1, (int) $limit);
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";

        $query = "
            SELECT
                COALESCE(tt.tipo_tramite, 'Sin tipo') as tipo,
                COUNT(*) as total
            FROM tramite t
            LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND $dias > 7
            AND ($filtroCliente)
            GROUP BY tt.id, tt.tipo_tramite
            ORDER BY total DESC
            LIMIT $limit
        ";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Estados con mayor atraso (sin movimiento > 7 dias).
     */
    public function getAtoradosPorEstado($limit = 10, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $limit = max(1, (int) $limit);
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";

        $query = "
            SELECT
                COALESCE(e.entidad, 'Sin entidad') as estado,
                COUNT(*) as total
            FROM tramite t
            LEFT JOIN entidad e ON t.entidad_id = e.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND $dias > 7
            AND ($filtroCliente)
            GROUP BY e.id, e.entidad
            ORDER BY total DESC
            LIMIT $limit
        ";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Clientes con mayor atraso (sin movimiento > 7 dias).
     */
    public function getAtoradosPorCliente($limit = 10, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $limit = max(1, (int) $limit);
        $dias = "DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at))";

        $query = "
            SELECT
                COALESCE(NULLIF(c.razon_social, ''), NULLIF(c.nombre, ''), NULLIF(cd.razon_social, ''), CONCAT('Cliente #', c.id)) as cliente,
                COUNT(*) as total
            FROM tramite t
            INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
            INNER JOIN cliente c ON cd.cliente_id = c.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND $dias > 7
            AND ($filtroCliente)
            GROUP BY c.id, cliente
            ORDER BY total DESC
            LIMIT $limit
        ";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * ========================================
     * EMBUDO DE CONVERSIÓN
     * ========================================
     */

    /**
     * Obtener datos del embudo de conversión
     * 
     * @param string|null $fechaInicio Fecha de inicio
     * @param string|null $fechaFin Fecha de fin
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Datos del embudo
     */
    public function getEmbudoConversion($fechaInicio = null, $fechaFin = null, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        $whereDate = "WHERE ($filtroCliente)";
        $params = [];
        
        if ($fechaInicio && $fechaFin) {
            $whereDate = "WHERE created_at >= ? AND created_at <= ? AND ($filtroCliente)";
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
     * 
     * @param int $limite Número de resultados
     * @param string $periodo Periodo a consultar
     * @param int|null $anio Año específico
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Top ejecutivos
     */
    public function getTopEjecutivos($limite = 5, $periodo = 'mes', $anio = null, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        
        if ($periodo == 'anio' && $anio) {
            $whereDate = "YEAR(t.created_at) = $anio AND ($filtroCliente)";
        } else {
            $whereDate = $this->getWherePeriodo($periodo) . " AND ($filtroCliente)";
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
     * 
     * @param int $limite Número de resultados
     * @param string $periodo Periodo a consultar
     * @param int|null $anio Año específico
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Top gestores
     */
    public function getTopGestores($limite = 5, $periodo = 'mes', $anio = null, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        
        if ($periodo == 'anio' && $anio) {
            $whereDate = "YEAR(t.created_at) = $anio AND ($filtroCliente)";
        } else {
            $whereDate = $this->getWherePeriodo($periodo) . " AND ($filtroCliente)";
        }

        $query = "
            SELECT 
                g.nombre AS gestor,
                eg.nombre AS empresa_gestora,
                COUNT(*) as total_tramites,
                SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as tramites_concluidos,
                COALESCE(AVG(CASE WHEN t.finished_at IS NOT NULL AND t.started_at IS NOT NULL 
                    THEN DATEDIFF(t.finished_at, t.started_at) END), 0) as tiempo_promedio_dias,
                COALESCE(SUM(t.gestor_total_pago), 0) as total_pagado_gestor
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
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Reporte de cuentas por cobrar
     */
    public function getAgingReport($userId = null, $limit = null, $offset = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $limitSql = '';

        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $limitSql = " LIMIT {$limit} OFFSET {$offset}";
        }
        
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
            AND ($filtroCliente)
            ORDER BY dias_vencidos DESC
            $limitSql
        ";
        
        return $this->db->query($query)->getResultArray();
    }

    /**
     * Contar registros del aging report
     */
    public function countAgingReport($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');

        $query = "
            SELECT COUNT(*) as total
            FROM tramite t
            WHERE (t.numero_factura IS NOT NULL AND t.numero_factura != ''
                OR t.numero_refactura IS NOT NULL AND t.numero_refactura != '')
            AND t.cobro_status_id = 22
            AND t.finished_at IS NOT NULL
            AND ($filtroCliente)
        ";

        $row = $this->db->query($query)->getRowArray();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtener resumen financiero por rangos
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Resumen por rangos de días
     */
    public function getResumenFinancieroPorRangos($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
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
            AND ($filtroCliente)
            GROUP BY rango
            ORDER BY FIELD(rango, '0-15 días', '16-30 días', '31-60 días', '61-90 días', 'Más de 90 días')
        ";
        
        return $this->db->query($query)->getResultArray();
    }

    /**
     * Obtener proyección de ingresos
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Proyección de ingresos
     */
    public function getProyeccionIngresos($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
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
            AND ($filtroCliente)
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
     * 
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Datos por mes
     */
    public function getTramitesPorMes($anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');

        $query = "
            SELECT 
                MONTH(created_at) as mes,
                COUNT(*) as total_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados
            FROM tramite
            WHERE YEAR(created_at) = ?
            AND ($filtroCliente)
            GROUP BY MONTH(created_at)
            ORDER BY mes
        ";
        
        return $this->db->query($query, [$anio])->getResultArray();
    }

    /**
     * Obtener datos para gráfica de ingresos por mes
     * 
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Ingresos por mes
     */
    public function getIngresosPorMes($anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');

        $query = "
            SELECT 
                MONTH(created_at) as mes,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as ingresos
            FROM tramite
            WHERE YEAR(created_at) = ?
            AND ($filtroCliente)
            GROUP BY MONTH(created_at)
            ORDER BY mes
        ";
        
        return $this->db->query($query, [$anio])->getResultArray();
    }

    /**
     * Obtener trámites por tipo
     * 
     * @param string $periodo Periodo a consultar
     * @param int|null $anio Año específico
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Trámites por tipo
     */
    public function getTramitesPorTipo($periodo = 'mes', $anio = null, $userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        
        // Si se especifica año y el periodo es 'anio', filtrar por ese año
        if ($anio && $periodo == 'anio') {
            $whereDate = "YEAR(t.created_at) = " . (int)$anio . " AND ($filtroCliente)";
        } else {
            $whereDate = $this->getWherePeriodo($periodo) . " AND ($filtroCliente)";
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
     * 
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Distribución por estado
     */
    public function getDistribucionPorEstado($anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        $filtroClienteSubconsulta = $this->getClienteFilterSQL($userId, 'tramite');
        
        $query = "
            SELECT 
                ts.tra_status,
                COUNT(*) as cantidad,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tramite WHERE tra_status_id NOT IN (20, 21) AND YEAR(created_at) = ? AND ($filtroClienteSubconsulta))), 2) as porcentaje
            FROM tramite t
            LEFT JOIN tra_status ts ON t.tra_status_id = ts.id
            WHERE t.tra_status_id NOT IN (20, 21)
            AND YEAR(t.created_at) = ?
            AND ($filtroCliente)
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
     * 
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array KPIs principales
     */
    public function getKPIsPrincipales($anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
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
            AND ($filtroCliente)
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
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Comparativa semanal con variaciones
     */
    public function getComparativaSemanal($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
        $query = "
            SELECT 
                'Semana Actual' as periodo,
                COUNT(*) as tramites_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as tramites_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado
            FROM tramite
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            AND ($filtroCliente)
            
            UNION ALL
            
            SELECT 
                'Semana Anterior' as periodo,
                COUNT(*) as tramites_ingresados,
                SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as tramites_concluidos,
                SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado
            FROM tramite
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) - 1
            AND ($filtroCliente)
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
     * 
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Récords históricos
     */
    public function getRecordsHistoricos($userId = null)
    {
        $filtroCliente = $this->getClienteFilterSQL($userId, 'tramite');
        
        $query = "
            SELECT 
                'Mayor cantidad de trámites en un día' as record,
                MAX(tramites) as valor,
                fecha
            FROM (
                SELECT DATE(created_at) as fecha, COUNT(*) as tramites
                FROM tramite
                WHERE ($filtroCliente)
                GROUP BY DATE(created_at)
            ) as daily
            WHERE tramites = (SELECT MAX(tramites) FROM (
                SELECT COUNT(*) as tramites FROM tramite WHERE ($filtroCliente) GROUP BY DATE(created_at)
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
                AND ($filtroCliente)
                GROUP BY YEARWEEK(finished_at, 1)
            ) as weekly
            WHERE tramites = (SELECT MAX(tramites) FROM (
                SELECT COUNT(*) as tramites FROM tramite WHERE tra_status_id = 20 AND ($filtroCliente) GROUP BY YEARWEEK(finished_at, 1)
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
                AND ($filtroCliente)
                GROUP BY YEAR(created_at), MONTH(created_at)
            ) as monthly
            WHERE monto = (SELECT MAX(monto) FROM (
                SELECT SUM(costo_total) as monto FROM tramite WHERE cobro_status_id = 23 AND ($filtroCliente) 
                GROUP BY YEAR(created_at), MONTH(created_at)
            ) as t)
        ";
        
        return $this->db->query($query)->getResultArray();
    }

    /**
     * ========================================
     * REPORTES DETALLADOS POR CLIENTE
     * ========================================
     */

    /**
     * Obtener resumen de trámites por cliente
     * 
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Resumen por cliente
     */
    public function getTramitesPorCliente($anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        
        $query = "
            SELECT 
                c.id as cliente_id,
                c.nombre as cliente,
                COUNT(t.id) as total_tramites,
                SUM(CASE WHEN t.tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as concluidos,
                SUM(CASE WHEN t.tra_status_id = 21 THEN 1 ELSE 0 END) as cancelados,
                SUM(CASE WHEN t.cobro_status_id = 23 THEN 1 ELSE 0 END) as cobrados,
                SUM(CASE WHEN t.cobro_status_id = 23 THEN t.costo_total ELSE 0 END) as monto_cobrado,
                SUM(CASE WHEN t.cobro_status_id = 22 AND (t.numero_factura IS NOT NULL AND t.numero_factura != '' 
                    OR t.numero_refactura IS NOT NULL AND t.numero_refactura != '') 
                    THEN t.costo_total ELSE 0 END) as monto_pendiente,
                AVG(CASE WHEN t.finished_at IS NOT NULL AND t.started_at IS NOT NULL 
                    THEN DATEDIFF(t.finished_at, t.started_at) END) as tiempo_promedio_dias
            FROM tramite t
            INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
            INNER JOIN cliente c ON cd.cliente_id = c.id
            WHERE YEAR(t.created_at) = ?
            AND ($filtroCliente)
            GROUP BY c.id, c.nombre
            ORDER BY total_tramites DESC, monto_cobrado DESC
        ";
        
        return $this->db->query($query, [$anio])->getResultArray();
    }

    /**
     * Obtener detalle de trámites por cliente con indicadores de tiempo
     * 
     * @param int $clienteId ID del cliente
     * @param int|null $anio Año a consultar (null = año actual)
     * @param int|null $userId ID del usuario para filtrado (null = sin filtro)
     * @return array Detalle de trámites con colores
     */
    public function getDetalleTramitesPorCliente($clienteId, $anio = null, $userId = null)
    {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $filtroCliente = $this->getClienteFilterSQL($userId, 't');
        
        $query = "
            SELECT 
                t.id,
                t.folio,
                t.contrato,
                t.unidad,
                t.serie,
                t.placas,
                t.created_at,
                t.started_at,
                t.finished_at,
                t.tra_status_id,
                t.cobro_status_id,
                t.costo_total,
                t.ent_municipio_id,
                ts.tra_status,
                tt.tipo_tramite,
                cs.cobro_status,
                cd.razon_social as cliente_directo,
                c.nombre as cliente,
                CONCAT(u.firstname, ' ', u.lastname) as ejecutivo,
                DATEDIFF(CURDATE(), COALESCE(t.started_at, t.created_at)) as dias_transcurridos
            FROM tramite t
            INNER JOIN cli_directo cd ON t.cli_directo_id = cd.id
            INNER JOIN cliente c ON cd.cliente_id = c.id
            LEFT JOIN tra_status ts ON t.tra_status_id = ts.id
            LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
            LEFT JOIN cobro_statuses cs ON t.cobro_status_id = cs.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE c.id = ?
            AND YEAR(t.created_at) = ?
            AND ($filtroCliente)
            ORDER BY t.created_at DESC
        ";
        
        return $this->db->query($query, [$clienteId, $anio])->getResultArray();
    }

    /**
     * Aplicar lógica de colores según días transcurridos y ubicación
     * 
     * @param array $tramite Datos del trámite
     * @return array Tramite con clase CSS y etiqueta
     */
    public function aplicarLogicaColores($tramite)
    {
        $diasDiferencia = $tramite['dias_transcurridos'];
        $traStatusId = $tramite['tra_status_id'];
        $entMunicipioId = $tramite['ent_municipio_id'];
        
        // Definir clases CSS según los días
        $claseVerde = 'background-verde';
        $claseAmarillo = 'background-amarillo';
        $claseRojo = 'background-rojo';
        $claseVioleta = 'background-violeta';
        $claseGris = 'background-gris';
        $claseAzulClaro = 'background-azul-claro';
        $claseAzul = 'background-azul';
        
        // Verificar tra_status_id para colores especiales
        if ($traStatusId == 23 || $traStatusId == 28) {
            $clase = $claseAzulClaro;
            $etiqueta = '';
        } elseif ($traStatusId == 21) {
            $clase = $claseGris;
            $etiqueta = 'Cancelado';
        } elseif ($traStatusId == 20) {
            $clase = $claseAzul;
            $etiqueta = 'Concluido';
        } else {
            // Determinar si es Local o Foráneo
            $local = ($entMunicipioId >= 266 && $entMunicipioId <= 281) || 
                     ($entMunicipioId >= 657 && $entMunicipioId <= 781);
            
            // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
            if ($local) {
                if ($diasDiferencia < 5) {
                    $clase = $claseVerde;
                    $etiqueta = $diasDiferencia . ' días';
                } elseif ($diasDiferencia < 8) {
                    $clase = $claseAmarillo;
                    $etiqueta = $diasDiferencia . ' días';
                } elseif ($diasDiferencia < 12) {
                    $clase = $claseRojo;
                    $etiqueta = $diasDiferencia . ' días';
                } else {
                    $clase = $claseVioleta;
                    $etiqueta = $diasDiferencia . ' días';
                }
            } else {
                if ($diasDiferencia < 10) {
                    $clase = $claseVerde;
                    $etiqueta = $diasDiferencia . ' días';
                } elseif ($diasDiferencia < 13) {
                    $clase = $claseAmarillo;
                    $etiqueta = $diasDiferencia . ' días';
                } elseif ($diasDiferencia < 16) {
                    $clase = $claseRojo;
                    $etiqueta = $diasDiferencia . ' días';
                } else {
                    $clase = $claseVioleta;
                    $etiqueta = $diasDiferencia . ' días';
                }
            }
        }
        
        $arrFilter = [20, 21, 23, 28];
        if (in_array($traStatusId, $arrFilter)) {
            $etiqueta = '';
        }
        
        $tramite['clase_css'] = $clase;
        $tramite['etiqueta_dias'] = $etiqueta;
        
        return $tramite;
    }
}


<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Expression as PredicateExpression;

class TramitesModel extends Model
{
    protected $table = 'tramite';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'folio', 'contrato', 'unidad', 'serie', 'placas',
        'tra_tipos_id', 'ent_municipio_id', 'cli_directo_id',
        'cli_directo_ejecutivo_id', 'empresa_gestora_id',
        'gestor_id', 'fecha_asignacion', 'fecha_conclusion',
        'costo_gestoria', 'impuesto_gestoria', 'derechos_tramite',
        'comision_derechos', 'numero_factura', 'numero_refactura',
        'costo_total', 'reembolso_status_id', 'tra_status_id',
        'cobro_status_id', 'observaciones', 'user_id', 'status'
    ];

    // Función para obtener un folio dado el ID del trámite
    public function getFolioById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }
    
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->where(['id' => $id]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute()->current();
        if ($result && isset($result['folio'])) {
            return $result['folio'];
        } 
        return false; // Valor predeterminado
    }

    // Función para obtener todos los trámites
    public function getAllTramites()
    {
        return $this->findAll();
    }

    // Función para obtener un trámite específico
    public function getTramiteById($id)
    {
        return $this->find($id);
    }

    // Función para actualizar un trámite
    public function updateTramite($id, $data)
    {
        return $this->update($id, $data);
    }

    // Función para eliminar un trámite
    public function deleteTramite($id)
    {
        return $this->delete($id);
    }


    public function getTramitesWithClase()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->columns([
            'id',
            'ent_municipio_id',
            'dias_diferencia' => new Expression('DATEDIFF(fecha_conclusion, created_at)')
        ]);

        // Ejecutar la consulta
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

        $tramites = [];

        foreach ($results as $row) {
            $diasDiferencia = $row['dias_diferencia'];
            $local = ($row['ent_municipio_id'] >= 266 && $row['ent_municipio_id'] <= 281) || 
                    ($row['ent_municipio_id'] >= 657 && $row['ent_municipio_id'] <= 781);

            // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
            if ($local) {
                if ($diasDiferencia < 5) {
                    $clase = 'claseVerde'; // Cambiar por el valor real de $claseVerde
                } elseif ($diasDiferencia < 8) {
                    $clase = 'claseAmarillo'; // Cambiar por el valor real de $claseAmarillo
                } elseif ($diasDiferencia < 12) {
                    $clase = 'claseRojo'; // Cambiar por el valor real de $claseRojo
                } else {
                    $clase = 'claseVioleta'; // Cambiar por el valor real de $claseVioleta
                }
            } else { // Foráneo
                if ($diasDiferencia < 10) {
                    $clase = 'claseVerde'; // Cambiar por el valor real de $claseVerde
                } elseif ($diasDiferencia < 13) {
                    $clase = 'claseAmarillo'; // Cambiar por el valor real de $claseAmarillo
                } elseif ($diasDiferencia < 16) {
                    $clase = 'claseRojo'; // Cambiar por el valor real de $claseRojo
                } else {
                    $clase = 'claseVioleta'; // Cambiar por el valor real de $claseVioleta
                }
            }

            $tramites[] = [
                'id' => $row['id'],
                'ent_municipio_id' => $row['ent_municipio_id'],
                'dias_diferencia' => $diasDiferencia,
                'tipo' => $local ? 'Local' : 'Foráneo',
                'clase' => $clase
            ];
        }

        return $tramites;
    }

    public function getTramiteCounts()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->columns([
            'local_count' => new Expression('SUM(CASE WHEN (ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781) THEN 1 ELSE 0 END)'),
            'foraneo_count' => new Expression('SUM(CASE WHEN (ent_municipio_id < 266 OR ent_municipio_id > 281) AND (ent_municipio_id < 657 OR ent_municipio_id > 781) THEN 1 ELSE 0 END)')
        ]);

        // Ejecutar la consulta
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute()->current();

        return [
            'local_count' => $result['local_count'],
            'foraneo_count' => $result['foraneo_count']
        ];
    }

    public function getTramiteCountsByClase()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->columns([
            'local_verde' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) < 5 THEN 1 ELSE 0 END)"),
            'local_amarillo' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 5 AND 7 THEN 1 ELSE 0 END)"),
            'local_rojo' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 8 AND 11 THEN 1 ELSE 0 END)"),
            'local_violeta' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) >= 12 THEN 1 ELSE 0 END)"),
            'foraneo_verde' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) < 10 THEN 1 ELSE 0 END)"),
            'foraneo_amarillo' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 10 AND 12 THEN 1 ELSE 0 END)"),
            'foraneo_rojo' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 13 AND 15 THEN 1 ELSE 0 END)"),
            'foraneo_violeta' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) >= 16 THEN 1 ELSE 0 END)")
        ]);

        // Ejecutar la consulta
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute()->current();

        return [
            'local' => [
                'verde' => $result['local_verde'],
                'amarillo' => $result['local_amarillo'],
                'rojo' => $result['local_rojo'],
                'violeta' => $result['local_violeta'],
            ],
            'foraneo' => [
                'verde' => $result['foraneo_verde'],
                'amarillo' => $result['foraneo_amarillo'],
                'rojo' => $result['foraneo_rojo'],
                'violeta' => $result['foraneo_violeta'],
            ]
        ];
    }

    /**
     * Obtiene conteos de trámites por clase con filtros de cliente y tipo
     */
    public function getTramiteCountsByClaseConFiltros($clientesAsignados = null, $clienteId = null, $tipoTramiteId = null)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->columns([
            'local_verde' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) < 5 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)"),
            'local_amarillo' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 5 AND 7 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)"),
            'local_rojo' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 8 AND 11 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)"),
            'local_violeta' => new Expression("SUM(CASE WHEN ((ent_municipio_id BETWEEN 266 AND 281) OR (ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) >= 12 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)"),
            'foraneo_verde' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) < 10 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)"),
            'foraneo_amarillo' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 10 AND 12 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)"),
            'foraneo_rojo' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) BETWEEN 13 AND 15 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)"),
            'foraneo_violeta' => new Expression("SUM(CASE WHEN ((ent_municipio_id NOT BETWEEN 266 AND 281) AND (ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), created_at) >= 16 AND tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)")
        ]);

        // Filtro por clientes asignados
        if ($clientesAsignados !== null && !empty($clientesAsignados)) {
            $select->where->in('cli_directo_id', $clientesAsignados);
        }

        // Filtro por cliente específico
        if ($clienteId) {
            $select->where(['cli_directo_id' => $clienteId]);
        }

        // Filtro por tipo de trámite
        if ($tipoTramiteId) {
            $select->where(['tra_tipos_id' => $tipoTramiteId]);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute()->current();

        return [
            'local' => [
                'verde' => $result['local_verde'] ?: 0,
                'amarillo' => $result['local_amarillo'] ?: 0,
                'rojo' => $result['local_rojo'] ?: 0,
                'violeta' => $result['local_violeta'] ?: 0,
            ],
            'foraneo' => [
                'verde' => $result['foraneo_verde'] ?: 0,
                'amarillo' => $result['foraneo_amarillo'] ?: 0,
                'rojo' => $result['foraneo_rojo'] ?: 0,
                'violeta' => $result['foraneo_violeta'] ?: 0,
            ]
        ];
    }

    /**
     * Obtiene resumen de trámites por cliente
     */
    public function getResumenPorCliente($clientesAsignados = null)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->join('cli_directo', 'tramite.cli_directo_id = cli_directo.id', ['cliente_nombre' => 'nombre'], 'left');
        $select->columns([
            'cli_directo_id',
            'total' => new Expression('COUNT(*)'),
            'en_proceso' => new Expression('SUM(CASE WHEN tramite.tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)'),
            'concluidos' => new Expression('SUM(CASE WHEN tramite.tra_status_id = 20 THEN 1 ELSE 0 END)'),
            'cancelados' => new Expression('SUM(CASE WHEN tramite.tra_status_id = 21 THEN 1 ELSE 0 END)'),
            'retrasados' => new Expression('SUM(CASE WHEN tramite.tra_status_id NOT IN (20, 21) AND (
                (((tramite.ent_municipio_id BETWEEN 266 AND 281) OR (tramite.ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), tramite.created_at) >= 8)
                OR
                (((tramite.ent_municipio_id NOT BETWEEN 266 AND 281) AND (tramite.ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), tramite.created_at) >= 13)
            ) THEN 1 ELSE 0 END)')
        ]);
        $select->group('tramite.cli_directo_id');

        if ($clientesAsignados !== null && !empty($clientesAsignados)) {
            $select->where->in('tramite.cli_directo_id', $clientesAsignados);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

        $resumen = [];
        foreach ($results as $row) {
            $resumen[] = [
                'cliente_id' => $row['cli_directo_id'],
                'cliente_nombre' => $row['cliente_nombre'] ?: 'Sin Cliente',
                'total' => $row['total'],
                'en_proceso' => $row['en_proceso'],
                'concluidos' => $row['concluidos'],
                'cancelados' => $row['cancelados'],
                'retrasados' => $row['retrasados']
            ];
        }

        return $resumen;
    }

    /**
     * Obtiene resumen de trámites por tipo de servicio
     */
    public function getResumenPorTipoServicio($clientesAsignados = null, $clienteId = null)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->join('tra_tipos', 'tramite.tra_tipos_id = tra_tipos.id', ['tipo_nombre' => 'tipo_tramite'], 'left');
        $select->columns([
            'tra_tipos_id',
            'total' => new Expression('COUNT(*)'),
            'en_proceso' => new Expression('SUM(CASE WHEN tramite.tra_status_id NOT IN (20, 21) THEN 1 ELSE 0 END)'),
            'concluidos' => new Expression('SUM(CASE WHEN tramite.tra_status_id = 20 THEN 1 ELSE 0 END)'),
            'retrasados' => new Expression('SUM(CASE WHEN tramite.tra_status_id NOT IN (20, 21) AND (
                (((tramite.ent_municipio_id BETWEEN 266 AND 281) OR (tramite.ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), tramite.created_at) >= 8)
                OR
                (((tramite.ent_municipio_id NOT BETWEEN 266 AND 281) AND (tramite.ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), tramite.created_at) >= 13)
            ) THEN 1 ELSE 0 END)')
        ]);
        $select->group('tramite.tra_tipos_id');

        if ($clientesAsignados !== null && !empty($clientesAsignados)) {
            $select->where->in('tramite.cli_directo_id', $clientesAsignados);
        }

        if ($clienteId) {
            $select->where(['tramite.cli_directo_id' => $clienteId]);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

        $resumen = [];
        foreach ($results as $row) {
            $resumen[] = [
                'tipo_id' => $row['tra_tipos_id'],
                'tipo_nombre' => $row['tipo_nombre'] ?: 'Sin Tipo',
                'total' => $row['total'],
                'en_proceso' => $row['en_proceso'],
                'concluidos' => $row['concluidos'],
                'retrasados' => $row['retrasados']
            ];
        }

        return $resumen;
    }

    /**
     * Obtiene trámites retrasados con detalles
     */
    public function getTramitesRetrasados($clientesAsignados = null, $clienteId = null, $tipoTramiteId = null)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->join('cli_directo', 'tramite.cli_directo_id = cli_directo.id', ['cliente_nombre' => 'nombre'], 'left');
        $select->join('tra_tipos', 'tramite.tra_tipos_id = tra_tipos.id', ['tipo_nombre' => 'tipo_tramite'], 'left');
        $select->join('tra_status', 'tramite.tra_status_id = tra_status.id', ['status_nombre' => 'tra_status'], 'left');
        $select->columns([
            'id',
            'folio',
            'created_at',
            'tra_status_id',
            'ent_municipio_id',
            'dias_transcurridos' => new Expression('DATEDIFF(CURDATE(), tramite.created_at)'),
            'es_local' => new Expression('CASE WHEN ((tramite.ent_municipio_id BETWEEN 266 AND 281) OR (tramite.ent_municipio_id BETWEEN 657 AND 781)) THEN 1 ELSE 0 END')
        ]);

        // Solo trámites en proceso (no concluidos ni cancelados)
        $select->where->notIn('tramite.tra_status_id', [20, 21]);

        // Filtro de retraso
        $select->where(new PredicateExpression('(
            (((tramite.ent_municipio_id BETWEEN 266 AND 281) OR (tramite.ent_municipio_id BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), tramite.created_at) >= 8)
            OR
            (((tramite.ent_municipio_id NOT BETWEEN 266 AND 281) AND (tramite.ent_municipio_id NOT BETWEEN 657 AND 781)) AND DATEDIFF(CURDATE(), tramite.created_at) >= 13)
        )'));

        if ($clientesAsignados !== null && !empty($clientesAsignados)) {
            $select->where->in('tramite.cli_directo_id', $clientesAsignados);
        }

        if ($clienteId) {
            $select->where(['tramite.cli_directo_id' => $clienteId]);
        }

        if ($tipoTramiteId) {
            $select->where(['tramite.tra_tipos_id' => $tipoTramiteId]);
        }

        $select->order('dias_transcurridos DESC');
        $select->limit(20);

        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

        $tramites = [];
        foreach ($results as $row) {
            $esLocal = $row['es_local'];
            $dias = $row['dias_transcurridos'];
            
            // Determinar nivel de alerta
            if ($esLocal) {
                if ($dias >= 12) $nivel = 'critico';
                elseif ($dias >= 8) $nivel = 'alto';
                else $nivel = 'medio';
            } else {
                if ($dias >= 16) $nivel = 'critico';
                elseif ($dias >= 13) $nivel = 'alto';
                else $nivel = 'medio';
            }

            $tramites[] = [
                'id' => $row['id'],
                'folio' => $row['folio'],
                'cliente' => $row['cliente_nombre'] ?: 'Sin Cliente',
                'tipo' => $row['tipo_nombre'] ?: 'Sin Tipo',
                'status' => $row['status_nombre'] ?: 'Sin Status',
                'dias_transcurridos' => $dias,
                'es_local' => $esLocal,
                'nivel_alerta' => $nivel,
                'created_at' => $row['created_at']
            ];
        }

        return $tramites;
    }
    public function getTramitesGroupedByStatusPerMonth($clientesAsignados = null, $clienteId = null)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->columns([
            'mes' => new Expression('MONTH(tramite.created_at)'),
            'anio' => new Expression('YEAR(tramite.created_at)'),
            'recoleccion' => new Expression("SUM(CASE WHEN tramite.tra_status_id IN (11, 22, 23) THEN 1 ELSE 0 END)"),
            'concluidos' => new Expression("SUM(CASE WHEN tramite.tra_status_id = 20 THEN 1 ELSE 0 END)")
        ]);
        $select->where(new PredicateExpression('tramite.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)'));

        if ($clientesAsignados !== null && !empty($clientesAsignados)) {
            $select->where->in('tramite.cli_directo_id', $clientesAsignados);
        }

        if ($clienteId) {
            $select->where(['tramite.cli_directo_id' => $clienteId]);
        }

        $select->group([
            new Expression('YEAR(tramite.created_at)'),
            new Expression('MONTH(tramite.created_at)')
        ]);
        $select->order(['anio', 'mes']);
    
        // Ejecutar la consulta
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
    
        // Preparar un arreglo para almacenar los resultados
        $tramitesPorMes = [];
        foreach ($results as $row) {
            $mes = $row['mes'];
            $anio = $row['anio'];
            $tramitesPorMes["$anio-$mes"] = [
                'recoleccion' => $row['recoleccion'],
                'concluidos' => $row['concluidos']
            ];
        }
    
        return $tramitesPorMes;
    }




    // Función para insertar un nuevo trámite
    public function insertTramite($data)
    {
        return $this->insert($data);
    }
}
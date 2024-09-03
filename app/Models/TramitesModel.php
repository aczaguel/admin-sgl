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
    public function getTramitesGroupedByStatusPerMonth()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->columns([
            'mes' => new Expression('MONTH(created_at)'),
            'anio' => new Expression('YEAR(created_at)'),
            'en_proceso' => new Expression("SUM(CASE WHEN tra_status_id IN (11, 22, 23) THEN 1 ELSE 0 END)"),
            'concluidos' => new Expression("SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END)")
        ]);
        $select->where(new PredicateExpression('created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)'));
        $select->group([
            new Expression('YEAR(created_at)'),
            new Expression('MONTH(created_at)')
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
                'en_proceso' => $row['en_proceso'],
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
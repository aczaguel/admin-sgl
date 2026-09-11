<?php
namespace App\Models;
use CodeIgniter\Model;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Expression as PredicateExpression;
use Laminas\Db\Adapter\Adapter;

class TraTramiteAsociadoModel extends Model
{
    protected $table = 'tra_tramite_asociado';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tramite_id', 'tra_tipos_id', 'created_at', 'updated_at'];

    protected $adapter;

    public function __construct()
    {
        parent::__construct();

        // Conectar a la base de datos con Laminas Adapter
        $db = \Config\Database::connect();
        $this->adapter = new Adapter([
            'driver'   => 'Pdo_Mysql',
            'database' => $db->database,
            'username' => $db->username,
            'password' => $db->password,
            'hostname' => $db->hostname,
            'charset'  => 'utf8mb4',
        ]);
    }

    // Obtener los servicios asociados a un trámite
    public function getServicesByTramiteId($tramiteId)
    {
        return $this->where('tramite_id', $tramiteId)
        ->orderBy('id', 'ASC')->findAll();
    }

    // Guardar un nuevo servicio asociado evitando duplicados
    public function saveService($tramiteId, $traTiposId)
    {
        // Verificar si ya existe el servicio en este trámite
        $exists = $this->where([
            'tramite_id' => $tramiteId,
            'tra_tipos_id' => $traTiposId
        ])->countAllResults();

        if ($exists > 0) {
            return false; // Ya existe, no lo insertamos
        }

        // Si no existe, lo insertamos
        $data = [
            'tramite_id' => $tramiteId,
            'tra_tipos_id' => $traTiposId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }


    // Eliminar un servicio asociado
    public function deleteService($asociado_id)
    {
        return $this->where(['id' => $asociado_id])->delete();
    }

    public function syncTramitesWithoutAsociados()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->columns([
            'tramite_id' => new Expression('id'),
            'tra_tipos_id' => new Expression('tra_tipos_id')
        ]);
        $select->where(new PredicateExpression('NOT EXISTS (
            SELECT 1 FROM tra_tramite_asociado ta WHERE ta.tramite_id = tramite.id
        )'));

        // Ejecutar la consulta
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();

        // Insertar los registros faltantes
        $insertedCount = 0;
        foreach ($results as $row) {
            if ($this->saveService($row['tramite_id'], $row['tra_tipos_id'])) {
                $insertedCount++;
            }
        }

        return "✅ Se han sincronizado {$insertedCount} trámites sin relación.";
    }

}
?>

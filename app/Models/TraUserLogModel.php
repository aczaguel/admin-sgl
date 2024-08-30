<?php
namespace App\Models;

use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class TraUserLogModel extends Model
{
    protected $table = 'tra_user_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tramite_id', 'user_id', 'tra_status_id',
        'created_at', 'updated_at', 'status'
    ];

    // Función para obtener todos los logs de un trámite específico
    public function getLogsByTramiteId($tramiteId)
    {
        if (!is_numeric($tramiteId)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        $select->where(['tramite_id' => $tramiteId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }

    // Función para obtener todos los logs de un usuario específico
    public function getLogsByUserId($userId)
    {
        if (!is_numeric($userId)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        $select->where(['user_id' => $userId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }

    // Función para insertar un nuevo log
    public function insertLog($data)
    {
        return $this->insert($data);
    }

    // Función para actualizar un log
    public function updateLog($id, $data)
    {
        return $this->update($id, $data);
    }

    // Función para eliminar un log
    public function deleteLog($id)
    {
        return $this->delete($id);
    }

    // Función para obtener un log específico
    public function getLogById($id)
    {
        return $this->find($id);
    }

    // Función para obtener todos los logs
    public function getAllLogs()
    {
        return $this->findAll();
    }
}

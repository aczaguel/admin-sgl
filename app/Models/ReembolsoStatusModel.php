<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class ReembolsoStatusModel extends Model
{
    protected $table = 'reembolso_status';
    protected $primaryKey = 'id';
    protected $allowedFields = ['reembolso_status', 'descripcion', 'status', 'created_at', 'updated_at', 'user_id'];

    // Función para obtener opciones de reembolso_status
    public function getReembolsoStatusOptions()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $options = [];

        foreach ($result as $row) { 
            $options[$row['id']] = $row['reembolso_status'];
        }

        return $options;
    }

    public function getReembolsoStatusById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->where(['id = ?' => $id]);
        $select->from($this->table);

        $row = $this->getRowFromSelect($select, $sql);
        return $row ? $row[0] : false;
    }
}
?>

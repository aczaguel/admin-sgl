<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class TraStatusModel extends Model
{
    protected $table = 'tra_status';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tra_status', 'descripcion', 'status', 'created_at', 'updated_at', 'user_id'
    ];

    // Función para obtener opciones de tra_status
    public function getTraStatusOptions()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tra_status');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        $tra_status = [];
        $steps = [];
        foreach ($result as $row) {
            $tra_status[$row['id']] = $row['tra_status'];
            $steps[$row['id']] = $row['step'];
        }
        $options['tra_status'] = $tra_status;
        $options['steps'] = $steps;
        return $options;
    }
}
?>
<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class CobroStatusesModel extends Model
{
    protected $table = 'cobro_statuses';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'cobro_status', 'descripcion', 'status', 'created_at', 'updated_at', 'user_id'
    ];

    // Función para obtener opciones de cobro_statuses
    public function getCobroStatusesOptions()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('cobro_statuses');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        
        foreach ($result as $row) {
            $options[$row['id']] = $row['cobro_status'];
        }

        return $options;
    }
}
?>
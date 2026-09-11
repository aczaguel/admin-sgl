<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use App\Models\Traits\SharedAdapter;
use Laminas\Db\Sql\Sql;

class EntidadesModel extends Model
{
    use SharedAdapter;
    protected $table = 'entidad';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'entidad'];

    // Función para obtener opciones de la tabla entidad
    public function getEntidades()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('entidad');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        
        foreach ($result as $row) { 
            $options[$row['id']] = $row['entidad'];
        }

        return $options;
    }
}
?>

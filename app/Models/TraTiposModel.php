<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class TraTiposModel extends Model
{
    protected $table = 'tra_tipos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tipo_tramite', 'descripcion', 'created_at', 'updated_at', 'user_id'];

    // Función para obtener opciones de tra_tipos
    public function getTraTiposOptions()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tra_tipos');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        
        foreach ($result as $row) { 
            $options[$row['id']] = $row['tipo_tramite'];
        }

        return $options;
    }

    public function getFolioById($id)
    {
        return $this->where('id', $id)->select('folio')->first();
    }


}

?>
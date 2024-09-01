<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class EntMunicipioModel extends Model
{
    protected $table = 'rel_ent_municipio';
    protected $primaryKey = 'ent_municipality_id';
    protected $allowedFields = ['ent_municipality', 'id_entity', 'id_municipality', 'ent_municipality_id'];

    // Función para obtener opciones de tra_tipos
    public function getEntMunicipios()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('rel_ent_municipio');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        
        foreach ($result as $row) { 
            $options[$row['ent_municipality_id']] = $row['ent_municipality'];
        }

        return $options;
    }
}

?>
<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class EmpresaGestoraModel extends Model
{
    protected $table = 'ges_empresa_gestora';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre', 'razon_social', 'rfc', 'telefono', 'correo_electronico',
        'calle', 'numero_interior', 'numero_exterior', 'codigo_postal',
        'colonia', 'ciudad', 'estado', 'pais', 'user_id', 'created_at',
        'updated_at', 'status'
    ];

    public function getEmpresasGestorasOptions()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('ges_empresa_gestora');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        
        foreach ($result as $row) {
            $options[$row['id']] = $row['nombre'];
        }

        return $options;
    }
}
?>
<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class GestorModel extends Model
{
    protected $table = 'ges_gestor';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre', 'razon_social', 'rfc', 'telefono', 'correo_electronico',
        'calle', 'numero_interior', 'numero_exterior', 'codigo_postal',
        'colonia', 'ciudad', 'estado', 'pais', 'user_id', 'empresa_gestora_id',
        'created_at', 'updated_at', 'status'
    ];

    public function getGestoresOptions($empresaGestoraId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('ges_gestor');
        $select->where(['empresa_gestora_id' => $empresaGestoraId]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        
        foreach ($result as $row) {
            $options[$row['id']] = $row['nombre'];
        }

        return $options;
    }

    public function getGestorNameById($gestorId){
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('ges_gestor');
        $select->where(['id' => $gestorId]); // Condición para el ID del gestor

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        // Retornar el nombre si se encuentra, o false si no
        foreach ($result as $row) {
            return $row['nombre'];
        }

        return false; // Si no se encuentra el gestor
    }
}
?>
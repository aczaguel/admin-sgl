<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class ClienteDirectoModel extends Model
{
    protected $table = 'cli_directo';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre', 'razon_social', 'rfc', 'telefono', 'correo_electronico',
        'calle', 'numero_interior', 'numero_exterior', 'codigo_postal',
        'colonia', 'ciudad', 'estado', 'pais', 'cliente_id', 'user_id', 
        'created_at', 'updated_at', 'status'
    ];

    // Función para obtener opciones de cli_directo
    // $conditions puede ser NULL (sin filtro) o un array estilo CI/GroceryCrud
    // Ej: ['cliente_id' => [1,2,3]] o ['id' => 0]
    public function getClientesDirectosOptions(?array $conditions = null)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('cli_directo');

        if (is_array($conditions) && $conditions !== []) {
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    // WHERE field IN (...)
                    $select->where->in($field, $value);
                } else {
                    // WHERE field = value
                    $select->where([$field => $value]);
                }
            }
        }

        $select->order('nombre ASC');
        
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
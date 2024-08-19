<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class ClienteDirectoEjecutivoModel extends Model
{
    protected $table = 'cli_directo_ejecutivo';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre', 'telefono', 'correo_electronico', 'user_id', 
        'cli_directo_id', 'created_at', 'updated_at', 'status'
    ];

    // Función para obtener opciones de cli_directo_ejecutivo basado en cli_directo_id
    public function getEjecutivosOptions($clienteDirectoId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('cli_directo_ejecutivo');
        $select->where(['cli_directo_id' => $clienteDirectoId]);
        
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
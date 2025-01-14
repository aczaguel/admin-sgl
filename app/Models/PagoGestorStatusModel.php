<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class PagoGestorStatusModel extends Model
{
    protected $table = 'pago_gestor_status';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pago_status', 'descripcion', 'status', 'created_at', 'updated_at', 'user_id'
    ];

    // Función para obtener opciones de pago_gestor_status
    public function getPagoGestorStatusOptions()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('pago_gestor_status');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $options = [];
        // $pago_status = [];
        // $descriptions = [];
        // foreach ($result as $row) {
        //     $pago_status[$row['id']] = $row['pago_status'];
        //     // $descriptions[$row['id']] = $row['descripcion'];
        // }
        // $options['pago_status'] = $pago_status;
        // $options['descriptions'] = $descriptions;
        // return $options;

        $options = [];

        foreach ($result as $row) { 
            $options[$row['id']] = $row['pago_status'];
        }

        return $options;
    }
}
?>

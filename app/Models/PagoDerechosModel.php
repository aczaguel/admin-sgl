<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class PagoDerechosModel extends Model
{
    protected $table = 'tra_pago_derechos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tramite_id', 'file', 'comentario', 'costo', 'user_id',
        'created_at', 'updated_at', 'status'
    ];

    /**
     * Obtiene las imágenes asociadas a un trámite específico.
     *
     * @param int $tramiteId El ID del trámite.
     * @return array Lista de imágenes asociadas al trámite.
     */
    public function getImgDerechosByTramiteId($tramiteId)
    {
        if (!is_numeric($tramiteId)) {
            return []; // Retorna un arreglo vacío si el ID no es válido
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        $select->where(['tramite_id' => $tramiteId, 'status' => 1]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $images = [];
        foreach ($result as $row) {
            $images[] = [
                'id' => $row['id'],
                'file' => $row['file'],
                'comentario' => $row['comentario'],
                'costo' => $row['costo'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }

        return $images;
    }
}
?>

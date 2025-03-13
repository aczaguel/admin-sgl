<?php
namespace App\Models;
use CodeIgniter\Model;
use Laminas\Db\Sql\Sql;

class TraTramiteAsociadoModel extends Model
{
    protected $table = 'tra_tramite_asociado';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tramite_id', 'tra_tipos_id', 'created_at', 'updated_at'];

    // Obtener los servicios asociados a un trámite
    public function getServicesByTramiteId($tramiteId)
    {
        return $this->where('tramite_id', $tramiteId)->findAll();
    }

    // Guardar un nuevo servicio asociado evitando duplicados
    public function saveService($tramiteId, $traTiposId)
    {
        // Verificar si ya existe el servicio en este trámite
        $exists = $this->where([
            'tramite_id' => $tramiteId,
            'tra_tipos_id' => $traTiposId
        ])->countAllResults();

        if ($exists > 0) {
            return false; // Ya existe, no lo insertamos
        }

        // Si no existe, lo insertamos
        $data = [
            'tramite_id' => $tramiteId,
            'tra_tipos_id' => $traTiposId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }


    // Eliminar un servicio asociado
    public function deleteService($asociado_id)
    {
        return $this->where(['id' => $asociado_id])->delete();
    }
}
?>

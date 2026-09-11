<?php
namespace App\Models;

use CodeIgniter\Model;

class ApiLogModel extends Model
{
    protected $table = 'api_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'method', 'endpoint', 'controller', 'action', 'action_ids',
        'body', 'response', 'user_id', 'ip_address', 'user_agent', 'created_at'
    ];

    public function insert($data = null, bool $returnID = true)
    {
        $builder = $this->db->table($this->table);
        $builder->set($data);

        // Generar y mostrar la consulta SQL para depuración
        // echo $builder->getCompiledInsert(); // Imprime la consulta generada

        // Realizar el insert
        return $builder->insert($data, $returnID);
    }

}

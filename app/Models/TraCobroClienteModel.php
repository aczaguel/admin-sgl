<?php
namespace App\Models;

use CodeIgniter\Model;

class TraCobroClienteModel extends Model
{
    protected $table = 'tra_cobro_cliente';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'tramite_id', 'file', 'comentario', 'costo', 'user_id', 'status'
    ];

    /**
     * Obtener todos los registros de cobro por tramite_id
     */
    public function getByTramiteId(int $tramiteId): array
    {
        return $this->where('tramite_id', $tramiteId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtener un registro por su id
     */
    public function getById(int $id): ?array
    {
        $row = $this->find($id);
        return $row ?: null;
    }

    /**
     * Inserta un nuevo registro (espera $data con keys permitidos)
     */
    public function insertRecord(array $data): int
    {
        $this->insert($data);
        return (int) $this->getInsertID();
    }

    /**
     * Elimina registro y su archivo físico si existe.
     * Retorna true si todo OK, false si fallo.
     */
    public function deleteRecordWithFile(int $id): bool
    {
        $record = $this->getById($id);
        if (!$record) {
            return false;
        }

        // Intentar borrar archivo físico si existe
        if (!empty($record['file'])) {
            $path = FCPATH . 'assets/uploads/cobro_cliente/' . $record['tramite_id'] . '/' . $record['file'];
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        return (bool) $this->delete($id);
    }

    /**
     * Contador de archivos por tramite
     */
    public function countByTramite(int $tramiteId): int
    {
        return $this->where('tramite_id', $tramiteId)->countAllResults();
    }

    /**
     * Actualiza campos permitidos de un registro
     */
    public function updateRecord(int $id, array $data): bool
    {
        return (bool) $this->update($id, $data);
    }
}
<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class TramitesModel extends Model
{
    protected $table = 'tramite';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'folio', 'contrato', 'unidad', 'serie', 'placas',
        'tra_tipos_id', 'ent_municipio_id', 'cli_directo_id',
        'cli_directo_ejecutivo_id', 'empresa_gestora_id',
        'gestor_id', 'fecha_asignacion', 'fecha_conclusion',
        'costo_gestoria', 'impuesto_gestoria', 'derechos_tramite',
        'comision_derechos', 'numero_factura', 'numero_refactura',
        'costo_total', 'reembolso_status_id', 'tra_status_id',
        'cobro_status_id', 'observaciones', 'user_id', 'status'
    ];

    // Función para obtener un folio dado el ID del trámite
    public function getFolioById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }
    
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('tramite');
        $select->where(['id' => $id]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute()->current();
        if ($result && isset($result['folio'])) {
            return $result['folio'];
        } 
        return false; // Valor predeterminado
    }

    // Función para obtener todos los trámites
    public function getAllTramites()
    {
        return $this->findAll();
    }

    // Función para obtener un trámite específico
    public function getTramiteById($id)
    {
        return $this->find($id);
    }

    // Función para actualizar un trámite
    public function updateTramite($id, $data)
    {
        return $this->update($id, $data);
    }

    // Función para eliminar un trámite
    public function deleteTramite($id)
    {
        return $this->delete($id);
    }

    // Función para insertar un nuevo trámite
    public function insertTramite($data)
    {
        return $this->insert($data);
    }
}
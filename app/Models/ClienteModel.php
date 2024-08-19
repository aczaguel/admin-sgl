<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class ClienteModel extends Model
{
    protected $table = 'cliente';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre', 'razon_social', 'rfc', 'telefono', 'correo_electronico', 'calle',
        'numero_interior', 'numero_exterior', 'codigo_postal', 'colonia', 'ciudad',
        'estado', 'pais', 'user_id', 'status', 'prefijo'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all cliente data
     *
     * @return array
     */
    public function getAllClientes()
    {
        return $this->findAll();
    }

    /**
     * Get the prefijo by cliente ID
     *
     * @param int $id
     * @return string|null
     */
    public function getPrefijoById($id)
    {
        $cliente = $this->find($id);
        return $cliente ? $cliente['prefijo'] : null;
    }

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

    public function getPrefijoByCliDirectoId($cliDirectoId)
    {
        // Conectar a la base de datos
        $db = \Config\Database::connect();

        // Obtener el cliente_id de la tabla cli_directo
        $builder = $db->table('cli_directo');
        $builder->select('cliente_id');
        $builder->where('id', $cliDirectoId);
        $query = $builder->get();
        $result = $query->getRow();

        // Si no se encuentra el registro, retornar null
        if (!$result) {
            return null;
        }

        $clienteId = $result->cliente_id;

        // Ahora obtener el prefijo de la tabla cliente usando el cliente_id
        $builder = $db->table('cliente');
        $builder->select('prefijo');
        $builder->where('id', $clienteId);
        $query = $builder->get();
        $result = $query->getRow();

        // Si no se encuentra el registro, retornar null
        if (!$result) {
            return null;
        }

        return $result->prefijo;
    }

    public function ultimos_seis_digitos()
    {
        // Obtener el valor de time()
        $tiempo = time();
        
        // Convertir el tiempo a cadena
        $tiempo_str = (string) $tiempo;
        
        // Tomar los últimos 6 caracteres
        $ultimos_seis = substr($tiempo_str, -6);
        
        return $ultimos_seis;
    }

    public function getPrefijoConUltimosSeisDigitos($cliDirectoId)
    {
        // Obtener el prefijo
        $prefijo = $this->getPrefijoByCliDirectoId($cliDirectoId);
        
        // Obtener los últimos seis dígitos del tiempo actual
        $ultimos_seis_digitos = $this->ultimos_seis_digitos();
        
        // Concatenar el prefijo con los últimos seis dígitos
        $resultado = $prefijo . $ultimos_seis_digitos;
        
        return $resultado;
    }
}


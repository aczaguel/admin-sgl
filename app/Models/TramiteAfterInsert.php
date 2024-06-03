<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class TramiteAfterInsert extends Model {
    public function updateFolioTramite($tramiteId, $cli_directo_id) {
       
        if (!is_numeric($tramiteId)) {
            return false;
        }
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->where(['id = ?' => $tramiteId]);
        $select->from('tramite');
        $row = $this->getRowFromSelect($select, $sql);
        if ($row === null) {
            return false;
        }
        $rfolio = $row[0]['folio'];
        $sql2 = new Sql($this->adapter);
        $select2 = $sql->select();
       
        $select2->from('cli_directo');
        $select2->join('cliente', 'cli_directo.cliente_id = cliente.id', ['id', 'prefijo']);
        $select2->where(['cli_directo.id = ?' => $cli_directo_id]);
        
        // $sqlString = $sql->getSqlStringForSqlObject($select2);
        // echo $sqlString;
        $row2 = $this->getRowFromSelect($select2, $sql2);
        if ($row2 === null) {
            return false;
        }
        
        $sql = new Sql($this->adapter);
        $update = $sql->update('tramite');
        $update->where(['id = ?' => $tramiteId]);
        $prefolio = $this->ultimos_seis_digitos();
        $newfolio = $row2[0]['prefijo'] . $prefolio;
        $update->set([
            'folio' => ($newfolio)
        ]);

        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
        return $newfolio;
    }
    public function ultimos_seis_digitos() {
        // Obtener el valor de time()
        $tiempo = time();
        
        // Convertir el tiempo a cadena
        $tiempo_str = (string) $tiempo;
        
        // Tomar los últimos 6 caracteres
        $ultimos_seis = substr($tiempo_str, -6);
        
        return $ultimos_seis;
    }
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
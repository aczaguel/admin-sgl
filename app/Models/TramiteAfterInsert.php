<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class TramiteAfterInsert extends Model {
    public function updateFolioTramite($tramiteId) {

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

        $sql = new Sql($this->adapter);
        $update = $sql->update('tramite');
        $update->where(['id = ?' => $tramiteId]);

        $timestamp = time();
        $sixd = str_pad(substr($timestamp, -6), 6, '0', STR_PAD_LEFT);

        $update->set([
            'customerName' => ('[NEW] ' . $row[0]['customerName'])
        ]);

        $statement = $sql->prepareStatementForSqlObject($update);
        return $statement->execute();
    }
}
<?php

namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class UsRolesModel extends Model
{
    protected $table = 'us_roles';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'description', 'created_at', 'updated_at'
    ];

    public function getRoleById($roleId)
    {
        if (!is_numeric($roleId)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->where(['id = ?' => $roleId]);
        $select->from('us_roles');

        $row = $this->getRowFromSelect($select, $sql);

        return $row ? $row[0] : false;
    }
}
?>

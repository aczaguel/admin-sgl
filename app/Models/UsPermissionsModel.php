<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class UsPermissionsModel extends Model
{
    protected $table = 'us_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'description', 'created_at', 'updated_at'
    ];

    public function getPermissionById($permissionId)
    {
        if (!is_numeric($permissionId)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->where(['id = ?' => $permissionId]);
        $select->from('us_permissions');

        $row = $this->getRowFromSelect($select, $sql);

        return $row ? $row[0] : false;
    }
}
?>

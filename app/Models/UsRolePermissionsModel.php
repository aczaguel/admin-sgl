<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class UsRolePermissionsModel extends Model
{
    protected $table = 'us_role_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'role_id', 'permission_id', 'created_at'
    ];

    public function getPermissionsByRoleId($roleId)
    {
        if (!is_numeric($roleId)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->where(['role_id = ?' => $roleId]);
        $select->from('us_role_permissions');

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $permissions = [];
        foreach ($result as $row) {
            $permissions[] = $row['permission_id'];
        }

        return $permissions;
    }
}
?>

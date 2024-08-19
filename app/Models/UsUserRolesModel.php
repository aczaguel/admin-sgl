<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;

class UserRolesModel extends Model
{
    protected $table = 'us_user_roles';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'role_id', 'created_at'
    ];

    public function getRolesByUserId($userId)
    {
        if (!is_numeric($userId)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->where(['user_id = ?' => $userId]);
        $select->from('us_user_roles');

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $roles = [];
        foreach ($result as $row) {
            $roles[] = $row['role_id'];
        }

        return $roles;
    }
}
?>

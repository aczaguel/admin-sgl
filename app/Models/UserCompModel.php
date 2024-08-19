<?php
namespace App\Models;
use GroceryCrud\Core\Model;
use Laminas\Db\Sql\Sql;
use Laminas\Db\ResultSet\ResultSet;
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'username', 'firstname', 'lastname', 'email', 'phone', 
        'avatar', 'password', 'created_at', 'updated_at', 'status'
    ];

    public function getUserById($userId)
    {
        if (!is_numeric($userId)) {
            return false;
        }

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->where(['id = ?' => $userId]);
        $select->from('us_users');

        $row = $this->getRowFromSelect($select, $sql);

        return $row ? $row[0] : false;
    }
    public function getuser()
    {
        return $this->findAll();
    }
    public function getRolesAndPermissions($userId)
    {
        $db = db_connect(); // Obtiene la conexión a la base de datos

        // Obtener los roles del usuario
        $rolesQuery = $db->table('user_roles')
                         ->join('roles', 'user_roles.role_id = roles.id')
                         ->where('user_roles.user_id', $userId)
                         ->get();

        $roles = $rolesQuery->getResult();

        // Preparar la estructura de datos para almacenar roles y permisos
        $rolesAndPermissions = [];

        foreach ($roles as $role) {
            // Obtener los permisos para cada rol
            $permissionsQuery = $db->table('role_permissions')
                                    ->join('permissions', 'role_permissions.permission_id = permissions.id')
                                    ->where('role_permissions.role_id', $role->role_id)
                                    ->get();

            $permissions = $permissionsQuery->getResult();

            // Agregar el rol y sus permisos al resultado final
            $rolesAndPermissions[] = [
                'role' => $role,
                'permissions' => $permissions
            ];
        }

        return $rolesAndPermissions;
    }
    public function obtenerPermisosPorUsuarioId($userId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(['u' => 'users']) // 'users' es la tabla de usuarios
            ->join(['ur' => 'us_user_roles'], 'u.id = ur.user_id', []) // 'user_roles' es la tabla que relaciona usuarios con roles
            ->join(['r' => 'us_roles'], 'ur.role_id = r.id', []) // 'us_roles' es la tabla de roles
            ->join(['rp' => 'us_role_permissions'], 'r.id = rp.role_id', []) // 'role_permissions' es la tabla que relaciona roles con permisos
            ->join(['p' => 'us_permissions'], 'rp.permission_id = p.id', ['permission_name' => 'permission_name']) // 'permissions' es la tabla de permisos
            ->where->equalTo('u.id', $userId);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $nombresDePermisos = $this->obtenerNombresDePermisos($resultSet->toArray());
        return $nombresDePermisos;
    }  

    function obtenerNombresDePermisos($arreglo)
    {
        return array_map(function($item) {
            return $item['permission_name'];
        }, $arreglo);
    }

    public function obtenerRolesPorUsuarioId($userId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['ur' => 'user_roles']) // Asume que 'user_roles' es la tabla que relaciona usuarios con roles
            ->join(['r' => 'us_roles'], 'ur.role_id = r.id', ['role_name' => 'name']) // Asume que 'us_roles' contiene los roles y 'name' es el nombre del rol
            ->where(['ur.user_id = ?' => $userId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet->toArray();
    }

    // Obtener permisos para un conjunto de roles
    public function obtenerPermisosPorRoles(array $roleIds)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['rp' => 'role_permissions']) // Asume que 'role_permissions' es la tabla que relaciona roles con permisos
            ->join(['p' => 'permissions'], 'rp.permission_id = p.id', ['permission_name' => 'name']) // Asume que 'permissions' contiene los permisos y 'name' es el nombre del permiso
            ->where(['rp.role_id' => $roleIds]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet->toArray();
    }
}
?>


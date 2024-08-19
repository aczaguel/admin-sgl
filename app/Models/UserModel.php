<?php 
namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model{
    protected $table = 'users';
    protected $allowedFields = ['username','firstname','lastname','email','phone','avatar','password'];
    public function getuser()
    {
        return $this->findAll();
    }
    public function getUserPermissions($userId)
    {
        $builder = $this->db->table('users as u');
        $builder->select('p.permission_name');
        $builder->join('us_user_roles as ur', 'u.id = ur.user_id', 'inner');
        $builder->join('us_roles as r', 'ur.role_id = r.id', 'inner');
        $builder->join('us_role_permissions as rp', 'r.id = rp.role_id', 'inner');
        $builder->join('us_permissions as p', 'rp.permission_id = p.id', 'inner');
        $builder->where('u.id', $userId);
        $query = $builder->get();
        $permissionNames = $this->extractPermissionNames($query->getResultArray());
        return $permissionNames;
    }
    function extractPermissionNames($permissionsArray)
    {
        return array_map(function($item) {
            return$item['permission_name'];
        }, $permissionsArray);
    }
    public function getUserRoles($userId)
    {
        $builder = $this->db->table('us_user_roles as ur');
        $builder->select('r.role_name');
        $builder->join('us_roles as r', 'ur.role_id = r.id', 'inner');
        $builder->where('ur.user_id', $userId);
        $query = $builder->get();
        return $this->extractRoleNames($query->getResultArray());
    }

    /**
     * Extrae los nombres de los roles de los resultados de la consulta.
     * 
     * @param array $results Resultados de la consulta.
     * @return array Arreglo de nombres de roles.
     */
    protected function extractRoleNames(array $results)
    {
        $roleNames = [];
        foreach ($results as $row) {
            $roleNames[] = $row['role_name'];
        }
        return $roleNames;
    }

}           

<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use Config\Database;



class Roles extends BaseController
{
    public function __construct() {
        // parent::__construct();
        helper(['form', 'url']);
    }

    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];
        
        return $this->_example_output($output);
    }

    public function roles()
    {
        try {
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $roles_crud = $this->_getGroceryCrudEnterprise();
            $roles_crud->setTable('us_roles');
            $roles_crud->setSubject('Rol', 'Roles');
            $roles_crud->defaultOrdering('us_roles.id', 'desc');
            
            $roles_crud->columns(['id', 'role_name', 'permisos', 'description']);
            $roles_crud->fields(['role_name', 'permisos', 'description']);
            $roles_crud->unsetDeleteMultiple();

            $roles_crud->callbackBeforeInsert(function ($stateParameters) {
                $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
                return $stateParameters;
            });

            // Configuración de la relación N to N para permisos
            $roles_crud->setRelationNtoN(
                'permisos', // Nombre del campo en el formulario
                'us_role_permissions', // Tabla de unión
                'us_permissions', // Tabla de destino
                'role_id', // Llave foránea en la tabla de unión que apunta a la tabla de roles
                'permission_id', // Llave foránea en la tabla de unión que apunta a la tabla de permisos
                'permission_name' // Campo que se desea mostrar en el multiselect
            );
            
            $roles_output = $roles_crud->render();
            $final_output = array_merge((array)$roles_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function role_permissions()
    {
        try {
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $role_permissions_crud = $this->_getGroceryCrudEnterprise();
            $role_permissions_crud->setTable('us_role_permissions');
            $role_permissions_crud->setSubject('Rol-Permiso', 'Roles-Permisos');
            $role_permissions_crud->defaultOrdering('us_role_permissions.id', 'desc');
            
            $role_permissions_crud->columns(['id', 'role_id', 'permission_id']);
            $role_permissions_crud->fields(['role_id', 'permission_id']);
            $role_permissions_crud->unsetDeleteMultiple();

            // Relaciones
            $role_permissions_crud->setRelation('role_id', 'us_roles', 'role_name');
            $role_permissions_crud->setRelation('permission_id', 'us_permissions', 'permission_name');

            $role_permissions_output = $role_permissions_crud->render();
            $final_output = array_merge((array)$role_permissions_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        return $groceryCrud;
    }

    private function _getDbData() {
        $db = (new ConfigDatabase())->default;
        return [
            'adapter' => [
                'driver' => 'mysqli',
                'host'     => $db['hostname'],
                'database' => $db['database'],
                'username' => $db['username'],
                'password' => $db['password'],
                'charset' => 'utf8'
            ]
        ];
    }

    private function _example_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');

            echo $salida->output;
            exit;
        }
        // echo "<br> bbbbbbb";
        return view('/deskapp/extra-pages/grocery_page', (array)$salida);
    }

}

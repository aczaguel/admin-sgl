<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use Config\Database;



class Permisos extends BaseController
{
    public function __construct() {
        // parent::__construct();
        helper(['form', 'url', 'permissions', 'acl_guard']);
    }

    private function guardManagementAccess()
    {
        $session = session();
        $userId = $session->get('id');
        $isApi = $this->request->isAJAX() || $this->request->getGet('gc_state') !== null;

        if (!$userId) {
            if ($isApi) {
                return acl_deny('Sesión expirada', 401, null, true);
            }
            return redirect()->to('/deskapp/auth/login');
        }

        [$roles, $perms] = session_roles_perms($session);
        $canManage = has_permission('menu_roles', $perms, $roles) || has_permission('menu_permisos', $perms, $roles);
        if (!$canManage) {
            if ($isApi) {
                return acl_deny('Acceso denegado', 403, null, true);
            }
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para administrar permisos.');
        }

        return null;
    }

    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];
        
        return $this->_example_output($output);
    }

    public function permisos()
    {
        try {
            if ($resp = $this->guardManagementAccess()) {
                return $resp;
            }

            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');

            helper('acl_version');
            
            $permissions_crud = $this->_getGroceryCrudEnterprise();
            $permissions_crud->setTable('us_permissions');
            $permissions_crud->setSubject('Permission', 'Permissions');
            $permissions_crud->defaultOrdering('us_permissions.id', 'desc');
            
            $permissions_crud->columns(['id', 'permission_name', 'description']);
            $permissions_crud->fields(['permission_name', 'description']);
            $permissions_crud->unsetDeleteMultiple();

            $permissions_crud->callbackColumn('permission_name', static function ($value) {
                $value = trim((string) $value);
                if ($value === '') {
                    return '';
                }

                $label = function_exists('permission_ui_label') ? permission_ui_label($value) : $value;
                if ($label === $value) {
                    return esc($label);
                }

                return esc($label) . '<br><small class="text-muted">' . esc($value) . '</small>';
            });

            $permissions_crud->callbackAfterInsert(function ($stateParameters) use ($permissions_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $permissions_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $permissions_crud->callbackAfterUpdate(function ($stateParameters) use ($permissions_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $permissions_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $permissions_crud->callbackAfterDelete(function ($stateParameters) use ($permissions_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $permissions_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });


            $permissions_output = $permissions_crud->render();
            $data['title'] = 'Gestión de Permisos';
            $data['description'] = 'Administra los permisos del sistema y sus descripciones';
            $final_output = array_merge((array)$permissions_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }



    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
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

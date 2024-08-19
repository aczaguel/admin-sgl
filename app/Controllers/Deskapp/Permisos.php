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

    public function permisos()
    {
        try {
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $permissions_crud = $this->_getGroceryCrudEnterprise();
            $permissions_crud->setTable('us_permissions');
            $permissions_crud->setSubject('Permission', 'Permissions');
            $permissions_crud->defaultOrdering('us_permissions.id', 'desc');
            
            $permissions_crud->columns(['id', 'permission_name', 'description']);
            $permissions_crud->fields(['permission_name', 'description', 'created_at', 'updated_at']);
            $permissions_crud->unsetDeleteMultiple();

            $permissions_output = $permissions_crud->render();
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

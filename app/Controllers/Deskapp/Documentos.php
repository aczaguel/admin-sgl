<?php
// namespace App\Controllers;
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

class Documentos extends BaseController
{
    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];

        return $this->_example_output($output);
    }

    public function documento()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('documento');
        $crud->setSubject('Documento', 'Documentos');

        $crud->columns(["documento", "descripcion"]); 
        $crud->fields(["documento", "descripcion", "user_id"]);

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf'
            ]
        ];
        
        $crud->callbackAddForm(function ($data) {
            $session = session();
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            return $data;
        });
        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/documentos/', 
            '/assets/uploads/documentos/', 
            $uploadValidations
        );

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function status()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('doc_statuses');
        $crud->setSubject('Estatus', 'Estatuses');

        $crud->columns(["st_documento", "descripcion"]); 
        $crud->fields(["st_documento", "descripcion", "user_id"]);

        $crud->fields(["st_documento", "descripcion", "user_id"]);

        $crud->displayAs('st_documento','Estatus del Documento');

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');


        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    private function _example_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $salida->output;
            exit;
        }
        // return view('example.php', (array)$salida);
        return view('/deskapp/extra-pages/grocery_page.php', (array)$salida);
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
    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        return $groceryCrud;
    }
}
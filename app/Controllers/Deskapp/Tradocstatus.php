<?php
// namespace App\Controllers;
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

class Tradocstatus extends BaseController
{
    private function guardDocStatusAccess()
    {
        helper(['permissions', 'acl_guard']);

        $session = session();
        $userId = $session->get('id');
        $isGroceryApi = $this->request->getGet('gc_state') !== null;

        if (!$userId) {
            if ($this->request->isAJAX() || $isGroceryApi) {
                return acl_deny('Sesión expirada', 401, null, true);
            }
            return redirect()->to('/deskapp/auth/login');
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('menu_documentos', $perms, $roles)) {
            if ($this->request->isAJAX() || $isGroceryApi) {
                return acl_deny('Acceso denegado', 403, null, true);
            }
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para acceder a Documentos.');
        }

        return null;
    }

    public function index()
    {
        if ($resp = $this->guardDocStatusAccess()) {
            return $resp;
        }

        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];

        return $this->_example_output($output);
    }

    public function documento()
    {
        if ($resp = $this->guardDocStatusAccess()) {
            return $resp;
        }

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_doc_status');
        $crud->setSubject('Documento', 'Documentos');

        /* SELECT Se configura el documento */
        $crud->setRelation('documento_id', 'documento', 'documento');
        $crud->displayAs('documento_id','Documento');

        /* SELECT Se configura el doc_status */
        $crud->setRelation('status_documento_id', 'doc_statuses', 'st_documento');
        $crud->displayAs('status_documento_id','Status del Documento');

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/docstatus/', 
            '/assets/uploads/docstatus/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');

        $crud->callbackAddForm(function ($data) {
            $session = session();
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            return $data;
        });

        $crud->callbackBeforeInsert(function ($stateParameters) {
            $session = session();
            $myid = $session->get('id');
            $stateParameters->data['user_id'] = $myid;
            $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
            $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
            return $stateParameters;
        });

        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $myid = $session->get('id');
            $stateParameters->data['user_id'] = $myid;
            $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
            return $stateParameters;
        });

        $salida = $crud->render();
        $data['title'] = 'Gestión de Documentos de Trámites';
        $data['description'] = 'Administra los documentos y evidencias asociadas a los trámites';
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
                'charset' => 'utf8',
            // FR-01: Sync MySQL session timezone with PHP (America/Mexico_City)
            'driver_options' => [
                MYSQLI_INIT_COMMAND => "SET time_zone = '-06:00'",
            ],
            ]
        ];
    }
    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
        return $groceryCrud;
    }
}
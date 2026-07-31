<?php
// namespace App\Controllers;
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

class Example extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'cliente_filter', 'cliente_context']);

        $session = session();
        $userId = $session->get('id');
        $requested = $this->request ? $this->request->getGet('cliente_id') : null;

        // Persistir cliente activo (si viene en GET) para que el filtro aplique en ESTA misma request
        resolve_active_cliente_id($userId, $requested);
    }

    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];

        return $this->_example_output($output);
    }

    public function customers()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tramite');
        $crud->setSubject('tramite', 'Tramites');

        $filterSql = get_tramite_filter_sql($myid);
        $crud->where($filterSql);

        $salida = $crud->render();
        $data['title'] = 'Gestión de Trámites';
        $data['description'] = 'Administra todos los trámites registrados en el sistema';

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
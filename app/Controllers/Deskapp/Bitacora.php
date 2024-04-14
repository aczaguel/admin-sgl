<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;
use Config\Database as ConfigDatabase;

use App\Models\BitacoraModel;
use CodeIgniter\Controller;

class Bitacora extends BaseController
{
    public function index()
    {
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $folio_tramite = (int) $uri->getSegment(4);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        // Carga del modelo
        $db_config = $this->_getDbData();
        // $model = new BitacoraModel($db_config);

        $model = new \CodeIgniter\Model();
        $model->setTable('documento');
        
        $documentos = $model->select('documento_id, documento')
        ->findAll();
        $doctos = [];

        foreach ($documentos as $item) {
            $doctos[$item['documento_id']] = $item['documento'];
        }

        $model = new \CodeIgniter\Model();
        $model->setTable('bitacora');
        
        $bitacoras = $model->select('bitacora.folio_tramite, bitacora.tramite_id, bitacora.cambios, bitacora.tipo, bitacora.origen, bitacora.created_at, users.firstname, users.lastname')
        ->join('users', 'users.id = bitacora.user_id')
        ->where('bitacora.folio_tramite =', $folio_tramite) 
        ->orderBy('bitacora.created_at', 'DESC')
        ->findAll();

        $model = new \CodeIgniter\Model();
        $model->setTable('cobro_statuses');
        
        $cobro_statuses = $model->select('cobro_statuses.id, cobro_statuses.cobro_status')
        ->findAll();

        // Llamada a la función para convertir el arreglo $array
        $arrayConvertido = $this->convertirCambiosToArray($bitacoras);

        // Preparar los datos para pasar a la vista
        $salida['bitacoras'] = $arrayConvertido;
        $salida['documentos'] = $doctos;
        $cobro_statuses = $this->convertir_a_asociativo($cobro_statuses);
        $salida['cobro_statuses'] = $cobro_statuses;

        $salida2 = array_merge((array)$salida, $data);

        // Cargar la vista
        return $this->_example_output($salida2);
    }

    private function _example_output($salida = null) {
    //   $salida = (object)esc($salida, 'raw');
    //   if ($salida->isJSONResponse) {
    //       header('Content-Type: application/json; charset=utf-8');
    //       echo $salida->output;
    //       exit;
    //   }
      // return view('example.php', (array)$salida);
        return view('/deskapp/ui/grocery_timeline.php', (array)$salida);
    }
    function convertirCambiosToArray($array)
    {
        foreach ($array as &$fila) {
            $fila['cambios'] = json_decode($fila['cambios'], true);
        }
        return $array;
    }
    function convertir_a_asociativo($arreglo) {
        $asociativo = array();
        foreach ($arreglo as $elemento) {
            $asociativo[$elemento['id']] = $elemento['cobro_status'];
        }
        return $asociativo;
    }
  

  private function _getDbData() {
    $db = (new ConfigDatabase())->default;
        return [
            'adapter' => [
                'driver' => 'Pdo_Mysql',
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

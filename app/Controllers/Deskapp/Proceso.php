<?php
// namespace App\Controllers;
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use Config\Database;

use App\Models\TraDocStatusModel;
use App\Models\BitacoraModel;

class Proceso extends BaseController
{

    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];

        return $this->_example_output($output);
    }

    public function final()
    {
        try {
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            $crud = $this->_getGroceryCrudEnterprise();

            $crud->setCsrfTokenName(csrf_token());
            $crud->setCsrfTokenValue(csrf_hash());

            $crud->setTable('tramite');
            $crud->setSubject('tramite', 'Tramites');
            $crud->unsetAdd();
            $crud->columns(["folio",
            "contrato",
            "unidad",
            "serie",
            "placas",
            "tra_tipos_id",
            // "ent_municipio_id",
            "cli_directo_id",
            "cli_directo_ejecutivo_id",
            "empresa_gestora_id",
            "gestor_id",
            "fecha_asignacion",
            "fecha_conclusion",
            "costo_gestoria",
            "impuesto_gestoria",
            "derechos_tramite",
            "comision_derechos",
            "numero_factura",
            "numero_refactura",
            "costo_total",
            "reembolso_status_id",
            "tra_status_id",
            "cobro_status_id",
            "observaciones",
            "status"]);
            $crud->fields(["folio",
            "contrato",
            "unidad",
            "serie",
            "placas",
            "tra_tipos_id",
            // "ent_municipio_id",
            "cli_directo_id",
            "cli_directo_ejecutivo_id",
            "empresa_gestora_id",
            "gestor_id",
            "fecha_asignacion",
            "fecha_conclusion",
            "costo_gestoria",
            "impuesto_gestoria",
            "derechos_tramite",
            "comision_derechos",
            "numero_factura",
            "numero_refactura",
            "costo_total",
            "reembolso_status_id",
            "tra_status_id",
            "cobro_status_id",
            "observaciones",
            "status"]);

            $crud->readOnlyFields(["folio",
            "contrato",
            "unidad",
            "serie",
            "placas",
            "tra_tipos_id",
            // "ent_municipio_id",
            "cli_directo_id",
            "cli_directo_ejecutivo_id",
            "empresa_gestora_id",
            "gestor_id",
            "fecha_asignacion",
            "fecha_conclusion",
            "costo_gestoria",
            "impuesto_gestoria",
            "derechos_tramite",
            "comision_derechos",
            "numero_factura",
            "numero_refactura",
            "costo_total",
            "reembolso_status_id",
            "tra_status_id",
            "observaciones",
            "status"]);

            $crud->where([
                'tra_status_id' => 20
            ]);

            /* SELECT Se configura el tipo de tramite */
            $crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el estatus del tramite */
            $crud->setRelation('cobro_status_id', 'cobro_statuses', 'cobro_status');
            $crud->displayAs('cobro_status_id','Estatus del Cobro');

            /* SELECT Se configura el cliente final o cliente directo */
            $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el municipio */
            $crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $crud->displayAs('gestor_id','Gestor');

            $crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');
            
            /* SELECT Se configura el gestor*/
            $crud->setRelation('reembolso_status_id', 'reembolso_status', 'reembolso_status');
            $crud->displayAs('reembolso_status_id','Estatus de Reembolso');

            // $crud->setRule('folio', 'integer');

            $crud->setActionButton('Documentos', 'fa fa-file', function ($row) {
                return '/deskapp/tramites/documentostatus/' . $row->folio . '/' . $row->id;
            }, true);
            $crud->setActionButton('Bitacora', 'icon-copy dw dw-open-book-2', function ($row) {
                return '/deskapp//bitacora/index/' . $row->folio ;
            }, true);
            $crud->unsetDelete();
            $crud->unsetDeleteMultiple();

            $crud->callbackEditForm(function ($data) use ($self){
                $session = session();
                $data2 = $data;
                $data3 = $data2->getArrayCopy();
                $flatArray = $self->flattenObject($data3);
                $session->set('data_tramite_final_before_update',  $flatArray);
                $session->set('tramite_final_id',  $flatArray["id"]);
                $session->set('tramite_final_folio',  $flatArray["folio"]);
                return $data;
            });

            $crud->callbackBeforeUpdate(function ($stateParameters) {
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
                return $stateParameters;
            });

            $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
                $db = Database::connect();
                $db2 = $this->_getDbData();
                $session = session();
                $data = $stateParameters->data;
                $myid = $session->get('id');

                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;                
                $data_prev = $session->get('data_tramite_final_before_update');
                $tramite_id = $session->get('tramite_final_id');
                $folio = $session->get('tramite_final_folio');
                $diferencias = $self->encontrarDiferencias($data_prev, $data_bitacora);
                
                $insert_bitacora = [
                    "tipo" => "update",
                    "origen"=>"final",
                    "folio_tramite" => $folio,
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "status" => 1
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
            });

            $salida = $crud->render();
            $salida2 = array_merge((array)$salida, $data);
            return $this->_example_output($salida2);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
    public function encontrarDiferencias($datos1, $datos2) {
        $diferencias = [];
        foreach ($datos1 as $clave => $valor) {
            // Verificar si la clave existe en el segundo conjunto de datos y si los valores son diferentes
            if (array_key_exists($clave, $datos2) && $datos2[$clave] !== $valor) {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => $datos2[$clave]
                ];
            }
        }
        return $diferencias;
    }
    public function flattenObject($object, &$result = [], $prefix = '') {
        foreach ($object as $key => $value) {
            if (is_object($value)) {
                // $this->flattenObject($value, $result, $prefix . $key . '_');
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function tipo()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_tipos');
        $crud->setSubject('Tipo de Tramite', 'Tipos de Tramite');

        $crud->setRelationNtoN(
            "Documentos",
            "tra_tipo_documentos",
            "documento",
            "tra_tipos_id",
            "documento_id", 
            "documento"
        );
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
            $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
            return $stateParameters;
        });
        $crud->callbackAddForm(function ($data) {
            $session = session();
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            return $data;
        });
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

        $crud->setTable('tra_status');
        $crud->setSubject('Etatus de Tramite', 'Estatuses de Tramite');

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function documentostatus()
    {
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $folio_tramite = (int) $uri->getSegment(4);
        $tramite_id = (int) $uri->getSegment(5);

        $db = Database::connect();
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();

        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_doc_status');
        $crud->setSubject('Documento', 'Documentos');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);        
        
        /* SELECT Se configura el documento */
        $crud->setRelation('documento_id', 'documento', 'documento');
        $crud->displayAs('documento_id','Documento');

        /* SELECT Se configura el doc_status */
        $crud->setRelation('status_documento_id', 'doc_statuses', 'st_documento');
        $crud->displayAs('status_documento_id','Status del Documento');

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_documents_before_update',  $flatArray);
            $session->set('doc_tramite_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db = Database::connect();
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');

            ($db2);
            $data_bitacora = $data;            

            $data_prev = $session->get('data_documents_before_update');
            $tramite_id = $session->get('doc_tramite_id');
            $diferencias = $self->encontrarDiferencias($data_prev, $data_bitacora);
            $diferencias["documento_id"] = $data["documento_id"];
            $insert_bitacora = [
                "tipo" => "update",
                "origen"=>"documentos",
                "folio_tramite" => $data['folio_tramite'],
                "tramite_id" => (int)$tramite_id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
                "status" => 1
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf'
            ]
        ];

        $crud->setFieldUpload(
            'file', 
            'assets/uploads/', 
            '/assets/uploads/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $folio_tramite = (int) $uri->getSegment(4);
            $tramite_id = (int) $uri->getSegment(5);

            $session = session();
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['folio_tramite'] = $folio_tramite;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
            $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
            return $stateParameters;
        });

        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
            return $stateParameters;
        });

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


/*
array(3) {
  [0]=>
  array(7) {
    ["id"]=>
    string(1) "1"
    ["tra_tipos_id"]=>
    string(1) "1"
    ["documento_id"]=>
    string(1) "1"
    ["created_at"]=>
    string(19) "2024-02-02 02:06:19"
    ["updated_at"]=>
    string(19) "2024-02-02 02:06:19"
    ["user_id"]=>
    NULL
    ["status"]=>
    string(1) "1"
  }
  [1]=>
  array(7) {
    ["id"]=>
    string(1) "2"
    ["tra_tipos_id"]=>
    string(1) "1"
    ["documento_id"]=>
    string(1) "2"
    ["created_at"]=>
    string(19) "2024-02-02 02:06:19"
    ["updated_at"]=>
    string(19) "2024-02-02 02:06:19"
    ["user_id"]=>
    NULL
    ["status"]=>
    string(1) "1"
  }
  [2]=>
  array(7) {
    ["id"]=>
    string(1) "3"
    ["tra_tipos_id"]=>
    string(1) "1"
    ["documento_id"]=>
    string(1) "3"
    ["created_at"]=>
    string(19) "2024-02-02 02:06:19"
    ["updated_at"]=>
    string(19) "2024-02-02 02:06:19"
    ["user_id"]=>
    NULL
    ["status"]=>
    string(1) "1"
  }
}
*/
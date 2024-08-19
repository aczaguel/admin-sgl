<?php
// namespace App\Controllers;
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use Config\Database;

use App\Models\TraDocStatusModel;
use App\Models\TramiteAfterInsert;
use App\Models\BitacoraModel;
use App\Models\TraTiposModel;
use App\Models\EntMunicipioModel;
use App\Models\ClienteModel;
use App\Models\ClienteDirectoModel;
use App\Models\ClienteDirectoEjecutivoModel;
use App\Models\EmpresaGestoraModel;
use App\Models\GestorModel;
use App\Models\TraStatusModel;
use App\Models\TramitesModel;
use App\Models\CobroStatusesModel;

class Tramites extends BaseController
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

    public function tramite()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->unsetAdd();    
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetDelete();
            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id','fecha_asignacion',
                'tra_status_id','cobro_status_id',
                'observaciones', 'status'
            ]);

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id','fecha_asignacion',
                'tra_status_id','cobro_status_id',
                'observaciones', 'status', 'user_id', 'created_at', 'updated_at'
            ]); 
            // $tramite_crud->readOnlyFields(["folio"]);
            $tramite_crud->unsetDeleteMultiple();
            
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');


            $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, true);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    private function _example_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $salida->output;
            exit;
        }
        return view('/deskapp/extra-pages/grocery_page', (array)$salida);
    }

    private function _simple_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $salida->output;
            exit;
        }
    }

    // Function to handle adding a new product
    public function add() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db2 = $this->_getDbData();

        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        $entMunicipios = new EntMunicipioModel($db2);
        $ent_municipio_options = $entMunicipios->getEntMunicipios();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_options = $traStatus->getTraStatusOptions();

        $cobroStatuses = new CobroStatusesModel($db2);
        $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();

        $gestor_options = [];
        $output = new \stdClass();
        
        // Fields to be displayed in the add form
        $output->fields = [
            "folio" => ["label" => "Folio", "type" => "text", "readonly"=>"readonly"],
            "contrato" => ["label" => "Contrato", "type" => "text", "required"=>"required"],
            "unidad" => ["label" => "Unidad", "type" => "text"],
            "serie" => ["label" => "Serie", "type" => "text"],
            "placas" => ["label" => "Placas", "type" => "text"],
            "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options], // Asumiendo que tienes un array $tra_tipos_options
            "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options], // Asumiendo que tienes un array $cli_directo_options
            "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => []], // Asumiendo que tienes un array $cli_directo_ejecutivo_options
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options], // Asumiendo que tienes un array $empresa_gestora_options
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => $gestor_options], // Asumiendo que tienes un array $gestor_options
            "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options], // Asumiendo que tienes un array $ent_municipio_options
            "fecha_asignacion" => ["label" => "Fecha Asignacion", "type" => "datetime"],
            "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options], // Asumiendo que tienes un array $tra_status_options
            // "cobro_status_id" => ["label" => "Cobro Status Id", "type" => "select", "options" => $cobro_status_options], // Asumiendo que tienes un array $cobro_status_options
            "observaciones" => ["label" => "Observaciones", "type" => "textarea"],
            "status" => ["label" => "Status", "type" => "radio", "options" => ["1" => "Activo", "0" => "Inactivo"]],
            "user_id" => ["label" => "User Id", "type" => "hidden", "value" => "$myid"]
        ];

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $output->css_files = $crudOutput->css_files;
        $output->js_files = $crudOutput->js_files;
        // Load the add form view
        $output = array_merge((array)$output, $data);
        return $this->_example_output_2($output, 'add');
    }

    public function getEjecutivosByClienteId($clienteDirectoId) {
        $db2 = $this->_getDbData();
        $ejecutivoModel = new ClienteDirectoEjecutivoModel($db2);
        $options = $ejecutivoModel->getEjecutivosOptions($clienteDirectoId);
        
        return $this->response->setJSON($options);
    }

    // Function to handle inserting a new product into the database
    public function insert() {
        $validation = \Config\Services::validation();

        // Set validation rules
        $validation->setRules([
            "contrato" => "required",
            // Añade más reglas de validación según sea necesario
        ]);
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Si la solicitud es AJAX, devuelve los errores en formato JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $validation->getErrors()
                ]);
            } else {
                // Si no es una solicitud AJAX, carga la vista del formulario nuevamente con los errores de validación
                return $this->add();
            }
        } else {
            try {
                // Insertar los datos en la base de datos
                $data = $this->request->getPost();
                $db = \Config\Database::connect();
                $builder = $db->table('tramite');

                $clienteModel = new ClienteModel($this->_getDbData());
                $newFolio = $clienteModel->getPrefijoConUltimosSeisDigitos($data["cli_directo_id"]);
                $data["folio"] = $newFolio;

                $builder->insert($data);
                // Get the last insert ID
                $lastInsertID = $db->insertID();

                #Espacio para guardar la relación DosStatus 

                // $db = Database::connect();
                $db2 = $this->_getDbData();
                $tra_tipos_id = $data["tra_tipos_id"];
                $condition = ['tra_tipos_id' => $tra_tipos_id];
                $query = $db->table('tra_tipo_documentos')->where($condition)->get();
                $resultados = $query->getResultArray();
                // var_dump($resultados);
                $session = session();
                $myid = $session->get('id');
                
                $traDocStatusModel = new TraDocStatusModel($db2);

                foreach ($resultados as $elemento) {
                    // Inserta cada elemento en la tabla tra_doc_status
                    $insert_data = [
                        "id"=>null,
                        "folio_tramite" => $newFolio,
                        "tramite_id" => (int)$lastInsertID,
                        "documento_id" => (int)$elemento['documento_id'],
                        "status_documento_id" => 11,
                        "file" => null,
                        "comentario" => null,
                        "user_id" => (int)$myid,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s'),
                        "status" => 1
                    ];
                    // Inserta los datos en la base de datos utilizando el modelo apropiado (ejemplo: usando CodeIgniter Model)
                    // var_dump($insert_data);
                    $result = $traDocStatusModel->insert($insert_data, 'tra_doc_status');
                }

                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $this->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"tramite",
                    "folio_tramite" => $newFolio,
                    "tramite_id" => (int)$lastInsertID,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "status" => 1
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

                // Si la solicitud es AJAX, devuelve una respuesta JSON indicando éxito
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'redirect' => '/deskapp/tramites/update/'.$lastInsertID
                    ]);
                } else {
                    // Si no es una solicitud AJAX, redirige a la página de lista
                    return redirect()->to('/deskapp/tramites/update/'.$lastInsertID);
                }
            } catch (\Exception $e) {
                // Manejo de excepciones de la base de datos
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Ocurrió un error al guardar el trámite: ' . $e->getMessage()
                    ]);
                } else {
                    // Si no es una solicitud AJAX, muestra el error de alguna otra forma
                    return redirect()->back()->withInput()->with('error', 'Ocurrió un error al guardar el trámite: ' . $e->getMessage());
                }
            }
        }
    }

    public function update($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();

        // var_dump($tramite);
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        $entMunicipios = new EntMunicipioModel($db2);
        $ent_municipio_options = $entMunicipios->getEntMunicipios();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_options = $traStatus->getTraStatusOptions();

        $cobroStatuses = new CobroStatusesModel($db2);
        $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form
        $form->fields = [
            "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "readonly"=>"readonly"],
            "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"],
            "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']],
            "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie']],
            "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas']],
            "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']],
            "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']],
            "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']],
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id']],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id']],
            "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id']],
            "fecha_asignacion" => ["label" => "Fecha Asignacion", "type" => "datetime", "value" => $tramite['fecha_asignacion']],
            "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
            "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']],
            "status" => ["label" => "Status", "type" => "radio", "options" => ["1" => "Activo", "0" => "Inactivo"], "value" => $tramite['status']],
            // "user_id" => ["label" => "User Id", "type" => "hidden", "value" => "$myid"]
        ];
    
        // $data['fields'] = $fields;
        $data['id'] = $id;
        $form->id = $id;
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        // Load the view with the fields and current data

        $cruddocstatus = $this->_getGroceryCrudEnterprise();
        $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/221');
        $output = $cruddocstatus->render();
        // single_evidencias
        // $crudtramites = $this->_getGroceryCrudEnterprise();
        // $crud2->setApiUrlPath('/deskapp/tramites/tipo');
        // $output2 = $crud2->render();

        $crudevidencias = $this->_getGroceryCrudEnterprise();
        $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/221');
        $outputevidencias = $crudevidencias->render();
        
        $output->output .= "<hr>".$outputevidencias->output;

        $form->output = $output->output;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'add');
    }

    public function getDependentData($type, $parentId) {
        $db = \Config\Database::connect();
    
        switch ($type) {
            case 'gestor':
                $builder = $db->table('ges_gestor');
                $builder->where('empresa_gestora_id', $parentId);
                $result = $builder->get()->getResultArray();
                break;
            case 'ejecutivo':
                $builder = $db->table('cli_directo_ejecutivo');
                $builder->where('cli_directo_id', $parentId);
                $result = $builder->get()->getResultArray();
                break;
            default:
                $result = [];
                break;
        }
    
        return $this->response->setJSON($result);
    }
    
    public function update_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        // Set validation rules
        $validation->setRules([
            "folio" => "required",
            "contrato" => "required"
        ]);
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            // Validation failed, return errors as JSON
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        } else {
            // Update the data in the database
            $data = $this->request->getPost();
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            $builder->where('id', $id);
            $builder->update($data);
            $folio = $data["folio"];
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "folio_tramite" => $folio,
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
                "status" => 1
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    private function _example_output_2($output = null, $page = 'index') {
        return view('/deskapp/extra-pages/tramite_' . $page . '_view', (array)$output);
        // $this->load->view('tramite_' . $page . '_view', (array)$output);
    }

    public function demo_multigrid() {
        $crud = $this->_getGroceryCrudEnterprise();
        #ancla
        $crud->setApiUrlPath('/tramites/mios');
        $output = $crud->render();

        $crud2 = $this->_getGroceryCrudEnterprise();
        $crud2->setApiUrlPath('/tramites/tipo');
        $output2 = $crud2->render();
        
        $output->output .= "<hr>".$output2->output;
        // echo "<br>". $output->output;

        return $this->_example_output_test($output);
    }

    public function _example_output_test($output = null) {
        // echo $version = GroceryCrud::VERSION;
        try{
            if (isset($output->isJSONResponse) && $output->isJSONResponse) {
                header('Content-Type: application/json; charset=utf-8');
                echo $output->output;
                exit;
            }
        }catch (\Exception $e) {
            var_dump($e->getMessage());
            // exit($e->getMessage());
            throw new \Exception('Wrong data');
        }
        return view('/deskapp/extra-pages/grocery_page', (array)$output);
        // $this->load->view('grocery_simple_page', $output); 
     }

    public function mios()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->unsetAdd();    
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetDelete();
            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Mis Tramites');
            $tramite_crud->where([
                'tramite.user_id' => $myid
            ]);     

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id','fecha_asignacion',
                'tra_status_id','cobro_status_id',
                'observaciones', 'status'
            ]);

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id','fecha_asignacion',
                'tra_status_id','cobro_status_id',
                'observaciones', 'status', 'user_id', 'created_at', 'updated_at'
            ]); 
            // $tramite_crud->readOnlyFields(["folio"]);
            $tramite_crud->unsetDeleteMultiple();
            
            /* SELECT Se configura el tipo de tramite */
            $tramite_crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramite_crud->displayAs('tra_tipos_id','Tipo de Tramite');

            /* SELECT Se configura el estatus del tramite */
            $tramite_crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramite_crud->displayAs('tra_status_id','Estatus del Tramite');

            /* SELECT Se configura el cliente final o cliente directo */
            $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $tramite_crud->displayAs('cli_directo_id','Cliente Directo');
            
            /* SELECT Se configura el ejecutivo del cliente */
            $tramite_crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramite_crud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');

            $tramite_crud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            /* SELECT Se configura el municipio */
            $tramite_crud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramite_crud->displayAs('ent_municipio_id','Municipio');

            /* SELECT Se configura la empresa gestora */
            $tramite_crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramite_crud->displayAs('empresa_gestora_id','Empresa Gestora');

            /* SELECT Se configura el gestor*/
            $tramite_crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramite_crud->displayAs('gestor_id','Gestor');

            $tramite_crud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, true);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function getGestoresByEmpresaId($empresaGestoraId)
    {
        try {
            $db2 = $this->_getDbData();
            $gestorModel = new GestorModel($db2);
            $options = $gestorModel->getGestoresOptions($empresaGestoraId);

            return $this->response->setJSON($options);
        } catch (\Exception $e) {
            // Logging the error for debugging purposes
            log_message('error', $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'An error occurred']);
        }
    }

    public function ultimos_seis_digitos() {
        $tiempo = time();
        $tiempo_str = (string) $tiempo;
        $ultimos_seis = substr($tiempo_str, -6);
        return $ultimos_seis;
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
            } else {
                // Si la clave no existe en el segundo conjunto de datos, agregarla con valores vacíos
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => ''
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
        $folio_tramite = $uri->getSegment(4);
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

        $crud->fieldType('folio_tramite','hidden');
        $crud->fieldType('tramite_id','hidden');
        
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

            $bitacoraModel = new BitacoraModel($db2);
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

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/documentostatus/', 
            '/assets/uploads/documentostatus/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $folio_tramite = $uri->getSegment(4);
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

    public function single_documentostatus()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db = Database::connect();
        $db2 = $this->_getDbData();
        $self = $this;

        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        // Verificar si se encontró un folio
        if (!$folio_tramite) {
            throw new \Exception('No existe el folio');
        } 

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->setTable('tra_doc_status');
        $crud->setSubject('Documento', 'Documentos');
        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);        

        $crud->fieldType('folio_tramite','hidden');
        
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

            $bitacoraModel = new BitacoraModel($db2);
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

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/documentostatus/', 
            '/assets/uploads/documentostatus/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = $uri->getSegment(4);
            // $tramite_id = (int) $uri->getSegment(5);

            $session = session();
            $myid = $session->get('id');
            $folio_tramite = $session->set('folio_tramite_id');
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

    public function evidencias()
    {
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $folio_tramite = $uri->getSegment(4);
        $tramite_id = (int) $uri->getSegment(5);

        $db = Database::connect();
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Evidencia', 'Evidencias');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);   

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $parameters = $stateParameters;
                $db = Database::connect();
                $db2 = $this->_getDbData();
                $data = $parameters->data;

                $request = \Config\Services::request();
                $uri = $request->getUri();
                $folio_tramite = $uri->getSegment(4);
                $tramite_id = (int) $uri->getSegment(5);

                $session = session();
                $myid = $session->get('id');
                                
                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"evidencia",
                    "folio_tramite" => $folio_tramite,
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "status" => 1
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_evidencias_before_update',  $flatArray);
            $session->set('doc_tramite_evidencia_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db = Database::connect();
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;            

            $data_prev = $session->get('data_evidencias_before_update');
            $tramite_id = $session->get('doc_tramite_evidencia_id');
            $diferencias = $self->encontrarDiferencias($data_prev, $data_bitacora);
            $insert_bitacora = [
                "tipo" => "update",
                "origen"=>"evidencia",
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

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');

        $crud->fieldType('folio_tramite','hidden');
        $crud->fieldType('tramite_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $folio_tramite = $uri->getSegment(4);
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

    public function single_evidencias(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        // Verificar si se encontró un folio
        if (!$folio_tramite) {
            throw new \Exception('No existe el folio');
        } 

        $db = Database::connect();
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Evidencia', 'Evidencias');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);   

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $db = Database::connect();
                $db2 = $this->_getDbData();
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);
                $folio_tramite = $session->get('folio_tramite_id');

                $myid = $session->get('id');
                                
                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"evidencia",
                    "folio_tramite" => $folio_tramite,
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "status" => 1
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackEditForm(function ($data) use ($self){
            $session = session();
            $data2 = $data;
            $data3 = $data2->getArrayCopy();
            $flatArray = $self->flattenObject($data3);
            $session->set('data_evidencias_before_update',  $flatArray);
            $session->set('doc_tramite_evidencia_id',  $flatArray["id"]);
            return $data;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db = Database::connect();
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');
            
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $folio_tramite = $session->get('folio_tramite_id');

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $self->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "tipo"=>"update",
                "origen"=>"evidencia",
                "folio_tramite" => $folio_tramite,
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

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('created_at','hidden');
        $crud->fieldType('updated_at','hidden');

        $crud->fieldType('folio_tramite','hidden');
        $crud->fieldType('tramite_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $session = session();

            $request = \Config\Services::request();
            $uri = $request->getUri();

            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $folio_tramite = $session->get('folio_tramite_id');

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

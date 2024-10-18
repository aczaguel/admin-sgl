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
use App\Models\EntidadesModel;
use App\Models\ClienteModel;
use App\Models\ClienteDirectoModel;
use App\Models\ClienteDirectoEjecutivoModel;
use App\Models\EmpresaGestoraModel;
use App\Models\GestorModel;
use App\Models\TraStatusModel;
use App\Models\TramitesModel;
use App\Models\CobroStatusesModel;
use App\Models\TraUserLogModel;
use App\Models\ReembolsoStatusModel;
use App\Models\CobroStatusModel;
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
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetPrint();
            }

            if (has_permission('read_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones'
            ]);
            $tramite_crud->displayAs("started_at", "Desde Asignación");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at); 
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 
            
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

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();

        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        // $tra_status_options = array_slice($tra_status_options, 0, 1, true); // Se deja unicamente la opción en proceso
        $cobroStatuses = new CobroStatusesModel($db2);
        $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();

        $gestor_options = [];
        $output = new \stdClass();
        
        // Fields to be displayed in the add form
        $output->fields = [
            "folio" => ["label" => "Folio", "type" => "hidden", "readonly"=>"readonly"],
            "contrato" => ["label" => "Contrato", "type" => "text", "required"=>"required"],
            "unidad" => ["label" => "Unidad", "type" => "text"],
            "serie" => ["label" => "Serie", "type" => "text", "required"=>"required"],
            "placas" => ["label" => "Placas", "type" => "text"],
            "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "required"=>"required"], // Asumiendo que tienes un array $tra_tipos_options
            "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "required"=>"required"], // Asumiendo que tienes un array $cli_directo_options
            "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "required"=>"required"], // Asumiendo que tienes un array $cli_directo_ejecutivo_options
            // "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options], // Asumiendo que tienes un array $empresa_gestora_options
            // "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => $gestor_options], // Asumiendo que tienes un array $gestor_options
            "entidad_id" => ["label" => "Entidad", "type" => "select", "options" => $entidad_options, "required"=>"required"],
            "ent_municipio_id" => ["label" => "Entidad - Municipio", "type" => "select", "options" => $ent_municipio_options, "disabled"=>"disabled"], // Asumiendo que tienes un array $ent_municipio_options
            // "cobro_status_id" => ["label" => "Cobro Status Id", "type" => "select", "options" => $cobro_status_options], // Asumiendo que tienes un array $cobro_status_options
            "observaciones" => ["label" => "Observaciones", "type" => "textarea"],
            "user_id" => ["label" => "User Id", "type" => "hidden", "value" => "$myid"]
        ];
        $output->gestor_campos = [];
        $output->derechos_campos = [];
        $output->bancario_campos = [];
        $output->final_campos = [];

        // if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
        //     unset($output->fields['empresa_gestora_id']);
        //     unset($output->fields['gestor_id']);
        // }

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
                        "user_id" => (int)$myid
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
                    "user_id" => (int)$myid
                ];
                $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

                $tra_user_log = new TraUserLogModel($db2);
                $log = [
                    "tramite_id"    => $lastInsertID,
                    "user_id"       => $myid,
                    "tra_status_id" => 11
                ];
                $tra_user_log->insert($log, 'tra_user_log');

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
        // if($tramite['tra_status_id'] == 23){
        //     return redirect()->to('/deskapp/proceso/update_final/'. $id);
        // }
        // var_dump($tramite);
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        $entMunicipios = new EntMunicipioModel($db2);
        $ent_municipio_options = $entMunicipios->getEntMunicipios();
        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_options = $traStatus->getTraStatusOptions();
        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();


        // $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        // if (is_read_only(esc($session->get('user_roles')))){
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "hidden", "value" => $tramite['folio'], "disabled"=>"disabled"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
                // "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
                // "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"],
                "entidad_id" => ["label" => "Entidad", "type" => "select", "options" => $entidad_options, "required"=>"required"],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
                // "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "disabled"=>"disabled"],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
            ];
        // }else{
        //     $form->fields = [
        //         "folio" => ["label" => "Folio", "type" => "hidden", "value" => $tramite['folio'], "readonly"=>"readonly"],
        //         "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"],
        //         "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']],
        //         "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie']],
        //         "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas']],
        //         "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']],
        //         "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']],
        //         "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']],
        //         // "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id']],
        //         // "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id']],
        //         "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id']],
        //         // "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
        //         "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']]
        //     ];
        // }


        // if (is_read_only(esc($session->get('user_roles')))){
        //     $form->gestor_fields = [
        //         "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
        //         "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"]
        //     ];
        // }else{
        $form->gestor_campos = [
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "required" => "required"],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "required" => "required"]
        ];
        // }
        
        $form->derechos_campos = [
            "derechos_tramite" => ["label" => "Monto pago de derechos", "type" => "number", "value" => $tramite['derechos_tramite'], "required" => "required"],
            "derechos_pago_sitio" => ["label" => "Pago", "type" => "select", "options" => ["online"=>"En Linea", "ventanilla"=>"En Ventanilla"], "value" => $tramite['derechos_pago_sitio']],
            "derechos_vigencia" => ["label" => "Fecha Vigencia", "type" => "datetime", "value" => $tramite['derechos_vigencia']]
        ];
        
        $form->bancario_campos = [
            "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required"],
            "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required"],
        ];

        $form->final_campos = [
            "numero_factura" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['numero_factura'], "required" => "required"],
            "numero_refactura" => ["label" => "Número de Refactura", "type" => "text", "value" => $tramite['numero_refactura']],
            "reembolso_status_id" => ["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']],
            "cobro_status_id" => ["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id']],
            "costo_gestoria" => ["label" => "Costo de Gestoría", "type" => "number", "value" => $tramite['costo_gestoria'], "required" => "required"],
            "impuesto_gestoria" => ["label" => "Impuesto de Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria'], "required" => "required"],
            "comision_derechos" => ["label" => "Comisión de Derechos", "type" => "number", "value" => $tramite['comision_derechos'], "required" => "required"],
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "required" => "required"],
        ];
        
        // if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
        //     unset($output->fields['empresa_gestora_id']);
        //     unset($output->fields['gestor_id']);
        // }

        $data['id'] = $id;
        $data['folio'] = $tramite['folio'];
        $data['tra_tipo'] = $tra_tipos_options[$tramite['tra_tipos_id']];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']];
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];
        $data['started_at'] = $tramite['started_at'];
        $data['derechos_comprobante'] = $tramite['derechos_comprobante'];
        
        $form->id = $id;

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        // if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output = $cruddocstatus->render();
            $form->output_docs = $output->output;
            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();

            $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
            $crudevidencias_finales->setApiUrlPath('/deskapp/proceso/single_evidencias_finales/' . $id);
            $outputevidencias_finales = $crudevidencias_finales->render();
            
            $output->output .= "<hr>".$outputevidencias->output;
            // $form->output_docs = $output->output;
            $form->output_bitacora = $outputevidencias->output;
            $form->outputevidencias_finales = $outputevidencias_finales->output;

            $form->output = $output->output;
        // }

        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'update');
    }

    public function update_solicitud($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if($tramite['tra_status_id'] == 23){
            return redirect()->to('/deskapp/proceso/update_final/'. $id);
        }
        
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
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (is_read_only(esc($session->get('user_roles')))){
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "disabled"=>"disabled"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required", "disabled"=>"disabled"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "disabled"=>"disabled"],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "disabled"=>"disabled"],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
            ];
        }else{
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
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']]
            ];
        }

        if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
            unset($output->fields['empresa_gestora_id']);
            unset($output->fields['gestor_id']);
        }

        $data['id'] = $id;
        $form->id = $id;

        
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output = $cruddocstatus->render();

            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();
            
            $output->output .= "<hr>".$outputevidencias->output;

            $form->output = $output->output;
        }
        $data['target_title'] = 'Asignarme este trámite';
        $data['target_id'] = 11;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'add');
    }

    public function update_recoleccion($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if($tramite['tra_status_id'] == 23){
            return redirect()->to('/deskapp/proceso/update_final/'. $id);
        }
        
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
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (is_read_only(esc($session->get('user_roles')))){
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "disabled"=>"disabled"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required", "disabled"=>"disabled"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "disabled"=>"disabled"],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "disabled"=>"disabled"],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
            ];
        }else{
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
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']]
            ];
        }

        if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
            unset($output->fields['empresa_gestora_id']);
            unset($output->fields['gestor_id']);
        }

        $data['id'] = $id;
        $form->id = $id;

        
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output = $cruddocstatus->render();

            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();
            
            $output->output .= "<hr>".$outputevidencias->output;

            $form->output = $output->output;
        }
        $data['target_title'] = 'Documentos Completos';
        $data['target_id'] = 22;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'add');
    }

    public function update_en_tramite($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if($tramite['tra_status_id'] == 23){
            return redirect()->to('/deskapp/proceso/update_final/'. $id);
        }
        
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
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (is_read_only(esc($session->get('user_roles')))){
            $form->fields = [
                "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "disabled"=>"disabled"],
                "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required", "disabled"=>"disabled"],
                "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
                "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
                "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
                "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "disabled"=>"disabled"],
                "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
                "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
                "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "disabled"=>"disabled"],
                "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "disabled"=>"disabled"],
                "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "disabled"=>"disabled"],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
            ];
        }else{
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
                "tra_status_id" => ["label" => "Estatus", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id']],
                "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']]
            ];
        }

        if (!has_permission('tramite_view_gestor', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
            unset($output->fields['empresa_gestora_id']);
            unset($output->fields['gestor_id']);
        }

        $data['id'] = $id;
        $form->id = $id;

        
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
        if (!is_read_only(esc($session->get('user_roles')))){
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output = $cruddocstatus->render();

            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();
            
            $output->output .= "<hr>".$outputevidencias->output;

            $form->output = $output->output;
        }
        $data['target_title'] = 'Enviar a Fase Final';
        $data['target_id'] = 23;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'add');
    }

    public function upload_comprobante()
    {   
        $db2 = $this->_getDbData();
        if ($this->request->isAJAX()) {
            $validation = \Config\Services::validation();
    
            // Definir reglas de validación para el archivo
            $validation->setRules([
                'image' => [
                    'rules' => 'uploaded[image]|max_size[image,2048]|ext_in[image,jpg,jpeg,png]',
                    'errors' => [
                        'uploaded' => 'No se seleccionó ningún archivo.',
                        'max_size' => 'El tamaño máximo del archivo es de 2MB.',
                        'ext_in' => 'Solo se permiten archivos con extensiones jpg, jpeg, png.',
                    ]
                ]
            ]);
    
            if (!$validation->withRequest($this->request)->run()) {
                // Retornar los errores de validación
                return $this->response->setJSON(['success' => false, 'errors' => $validation->getErrors()]);
            }
    
            // Obtener el archivo subido
            $file = $this->request->getFile('image');
    
            // Mover el archivo a la carpeta deseada
            if ($file->isValid() && !$file->hasMoved()) {
                $fileName = $file->getRandomName();
                $file->move('assets/uploads/tramites/', $fileName);
    
                // Actualizar la base de datos con el nombre del archivo
                $data = [
                    'derechos_comprobante' => $fileName,
                ];
    
                $tramiteModel = new TramitesModel($db2);
                $tramiteModel->setTableName('tramite');
                $tramiteModel->update($this->request->getPost('tramite_id'), $data);
    
                // Retornar respuesta JSON de éxito
                return $this->response->setJSON(['success' => true, 'message' => 'Archivo subido correctamente']);
            } else {
                // Error al mover el archivo
                return $this->response->setJSON(['success' => false, 'message' => 'Error al subir el archivo']);
            }
        }
    
        return $this->response->setJSON(['success' => false, 'message' => 'Solicitud no válida']);
    
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
            $data["user_id"] = $myid;
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
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 11
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    public function update_gestor_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        $validation->setRules([
            "empresa_gestora_id" => "required",
            "gestor_id" => "required"
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
            $data["user_id"] = $myid;
            $data["tra_status_id"] = 22;
            $data["started_at"] = date('Y-m-d H:i:s');
            $builder->update($data);
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El Gestor se asignó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }
    public function update_derechos_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        $validation->setRules([
            "derechos_tramite"=> "required",
            "derechos_pago_sitio" => "required",
            "derechos_vigencia" => "required"
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
            $data["user_id"] = $myid;
            $builder->update($data);
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    public function update_bancario_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        $validation->setRules([
            "derechos_revol_cliente"=> "required",
            "derechos_refer_banc" => "required"
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
            $data["user_id"] = $myid;
            $builder->update($data);
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);
        }
    }

    public function update_final_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        $validation->setRules([
            "numero_factura"=> "required",
            "costo_gestoria" => "required"
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
            $data["user_id"] = $myid;
            $builder->update($data);
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

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

            if(is_starter($session->get('user_roles'))){
                $tramite_crud->where([
                    '(tramite.user_id = ? AND tramite.tra_status_id = ?)' => [$myid, 11]
                ]);
            }elseif(is_executer($session->get('user_roles'))){
                    $tramite_crud->where([
                        '(tramite.user_id = ? AND tramite.tra_status_id = ?)' => [$myid, 22]
                    ]);
            }else{
                $tramite_crud->where([
                    'tramite.user_id' => $myid
                ]); 
            }

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignacion");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
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
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function solicitudes()
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
                'tramite.tra_status_id = ?' => [24]
            ]);   

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
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
                return '/deskapp/tramites/update_solicitud/' . $row->id;
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            $salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function recoleccion()
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
                'tramite.tra_status_id = ?' => [11]
            ]);   

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
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
                return '/deskapp/tramites/update_recoleccion/' . $row->id;
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);
            //$salida_total['insert_button_url'] = '/public/deskapp/tramites/add';

            echo $this->_example_output($salida_total);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function en_tramite()
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
                'tramite.tra_status_id = ?' => [22]
            ]);   

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';  // Clase CSS para verde
                $claseAmarillo = 'background-amarillo';  // Clase CSS para amarillo
                $claseRojo = 'background-rojo';  // Clase CSS para rojo
                $claseVioleta = 'background-violeta';  // Clase CSS para violeta
            
                // Determinar si es Local o Foráneo
                $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) || 
                ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);
                
                // Determinar la clase CSS basada en los días de diferencia y si es Local o Foráneo
                if ($local) {
                    if ($diasDiferencia < 5) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 8) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 12) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                } else {
                    if ($diasDiferencia < 10) {
                        $clase = $claseVerde;
                    } elseif ($diasDiferencia < 13) {
                        $clase = $claseAmarillo;
                    } elseif ($diasDiferencia < 16) {
                        $clase = $claseRojo;
                    } else {
                        $clase = $claseVioleta;
                    }
                }
            
                return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
            });

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
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
                return '/deskapp/tramites/update_en_tramite/' . $row->id;
            }, false);

            $tramite_salida = $tramite_crud->render();
            
            $salida_total = array_merge((array)$tramite_salida, $data);

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

        // $crud->columns(["tipo_tramite", "descripcion"]); 
        // $crud->fields(["tipo_tramite", "descripcion"]);

        $crud->setRelationNtoN(
            "Documentos",
            "tra_tipo_documentos",
            "documento",
            "tra_tipos_id",
            "documento_id", 
            "documento"
        );
        $crud->unsetFields(["created_at", "updated_at", "user_id"]);
        $crud->unsetColumns(["created_at", "updated_at", "user_id"]);

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
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/documentostatus/', 
            '/assets/uploads/documentostatus/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');

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
        $crud->defaultOrdering('tra_doc_status.created_at', 'desc');

        $crud->fields([
            "tramite_id", "folio_tramite", "documento_id", "file", "comentario", "status_documento_id"
        ]); 

        $crud->columns([
            "created_at", "documento_id", "file", "comentario"
        ]);

        $crud->displayAs('created_at','Creación');

        $crud->fieldType('tramite_id','hidden');
        $crud->fieldType('status_documento_id','hidden');
        
        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);        
        $crud->readOnlyFields(['folio_tramite']);
        
        $crud->fieldType('user_id','hidden');
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });    
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });    
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
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/documentostatus/', 
            '/assets/uploads/documentostatus/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = $uri->getSegment(4);

            $session = session();
            $myid = $session->get('id');
            $folio_tramite = $session->get('folio_tramite_id');
            $data['user_id'] = $myid;
            $data['folio_tramite'] = $folio_tramite;
            $data['tramite_id'] = $tramite_id;
            $data['status_documento_id'] = 3;
            return $data;
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
        $crud->setSubject('Bitacora', 'Bitacora');

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
                    "user_id" => (int)$myid
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
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');

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
        $crud->setSubject('Bitacora', 'Bitacora');
        $crud->defaultOrdering('tra_evidencias.created_at', 'desc');

        $crud->fields([
            "folio_tramite", "tramite_id", "comentario", "user_id"
        ]); 

        $crud->columns([
            "created_at", "id", "comentario", "user_id"
        ]);

        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');

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
                    "user_id" => (int)$myid
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
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('folio_tramite','hidden');
        $crud->fieldType('tramite_id','hidden');
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });    
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            return $stateParameters;
        });  
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

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_evidencias_finales(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias_finales');
        $crud->setSubject('Evidencia Final', 'Evidencias Finales');

        $crud->where([
            'tramite_id' => $tramite_id
        ]);   

        $crud->fields([
            'file', 'tramite_id',
            'costo', 'comentario', 'user_id'
        ]); 

        $crud->columns([
            'id', 'tramite_id','file', 
            'costo', 'created_at'
        ]); 

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $db2 = $this->_getDbData();
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);

                $myid = $session->get('id');
                                
                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"final",
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db2 = $this->_getDbData();
            $session = session();
            $data = $stateParameters->data;
            $myid = $session->get('id');
            
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);

            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $self->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "tipo"=>"update",
                "origen"=>"final",
                "tramite_id" => (int)$tramite_id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/evidencias/', 
            '/assets/uploads/evidencias/', 
            $uploadValidations
        );

        $crud->fieldType('user_id','hidden');
        $crud->fieldType('tramite_id','hidden');

        $crud->callbackAddForm(function ($data) {
            $session = session();

            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);

            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['tramite_id'] = $tramite_id;
            // var_dump($data);
            return $data;
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

    public function autorizar(){
        $tramiteId = $this->request->getPost('tramite_id');
        $statusId = $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            // Actualizar el estatus del trámite
            $builder->where('id', $tramiteId);
            $builder->update(['tra_status_id' => $statusId]);

            // Opcional: Insertar un registro en tra_user_log
            $session = session();
            $myid = $session->get('id');
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => 23
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function change_status(){
        $tramiteId = $this->request->getPost('tramite_id');
        $statusId = $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            // Actualizar el estatus del trámite

            $builder->where('id', $tramiteId);
            $builder->update([
                'tra_status_id' => $statusId
            ]);
            
            // Opcional: Insertar un registro en tra_user_log
            $session = session();
            $myid = $session->get('id');
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => $statusId
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
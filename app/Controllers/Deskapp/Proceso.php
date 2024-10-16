<?php
// namespace App\Controllers;
namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use Config\Database;

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
use App\Models\CobroStatusModel;
use App\Models\BitacoraModel;
use App\Models\ReembolsoStatusModel;
class Proceso extends BaseController
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

    public function final()
    {
        try {
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            $crud = $this->_getGroceryCrudEnterprise();

            $crud->unsetAdd();
            $crud->unsetEdit();
            $crud->unsetDelete();
            $crud->unsetDeleteMultiple();
            $crud->unsetClone();

            $crud->setCsrfTokenName(csrf_token());
            $crud->setCsrfTokenValue(csrf_hash());

            $crud->setTable('tramite');
            $crud->setSubject('tramite', 'Tramites');
            $crud->columns(["started_at", "id", "folio", "contrato", "unidad", "serie", "placas", "tra_tipos_id",'ent_municipio_id', "cli_directo_id", "cli_directo_ejecutivo_id", "empresa_gestora_id", "gestor_id", 
            "fecha_asignacion", "fecha_conclusion", "costo_gestoria", "impuesto_gestoria", "derechos_tramite", "comision_derechos", "costo_total", "numero_factura", "numero_refactura",
            "reembolso_status_id", "tra_status_id", "cobro_status_id", "observaciones"]); 

            $crud->displayAs("started_at", "Desde Creación");

            $crud->callbackColumn('started_at', function ($value, $row) {
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

            $crud->fields(["folio", "contrato", "unidad", "serie", "placas", "tra_tipos_id", 
            "cli_directo_id", "cli_directo_ejecutivo_id", "empresa_gestora_id", "gestor_id", "fecha_asignacion", "fecha_conclusion", "costo_gestoria", "impuesto_gestoria", "derechos_tramite",
            "comision_derechos", "costo_total", "numero_factura", "numero_refactura", "reembolso_status_id", "tra_status_id", "cobro_status_id", "observaciones"]);

            $crud->readOnlyFields(["folio", "contrato", "unidad", "serie", "placas", "tra_tipos_id", "cli_directo_id", "cli_directo_ejecutivo_id", "empresa_gestora_id",
            "gestor_id", "fecha_asignacion", "fecha_conclusion", "numero_factura", "numero_refactura", "reembolso_status_id", "tra_status_id", "observaciones"]);

            $crud->where([
                'tra_status_id' => 20                                                                                                                                                                                                                                                                                                                                  
            ]);
            
            if (!has_permission('export_final_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $crud->unsetExport();
            }
            if (!has_permission('print_final_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $crud->unsetPrint();
            }
            if (!has_permission('read_final_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $crud->unsetRead();
            }
            

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

            $crud->callbackBeforeUpdate(function ($stateParameters) {
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
                return $stateParameters;
            });

            $crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                return '/deskapp/proceso/update_final/' . $row->id;
            }, false);
            $salida = $crud->render();
            $salida2 = array_merge((array)$salida, $data);
            return $this->_example_output($salida2);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function update_final($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        $tra_status_id = $tramite['tra_status_id'];
        $ro_numero_factura = [];
        $ro_numero_refactura = [];
        $ro_reembolso_status_id = [];
        $ro_cobro_status_id = [];
        $ro_costo_gestoria = [];
        $ro_impuesto_gestoria = [];
        $ro_derechos_tramite = [];
        $ro_comision_derechos = [];

        if($tramite['tra_status_id'] != 23){
            $ro_numero_factura = ["disabled"=>"disabled"];
            $ro_numero_refactura = ["disabled"=>"disabled"];
            $ro_reembolso_status_id = ["disabled"=>"disabled"];
            $ro_cobro_status_id = ["disabled"=>"disabled"];
            $ro_costo_gestoria = ["disabled"=>"disabled"];
            $ro_impuesto_gestoria = ["disabled"=>"disabled"];
            $ro_derechos_tramite = ["disabled"=>"disabled"];
            $ro_comision_derechos = ["disabled"=>"disabled"];
        }    
        
        // var_dump($tramite);
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        $entMunicipios = new EntMunicipioModel($db2);
        $ent_municipio_options = $entMunicipios->getEntMunicipios();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_options = $traStatus->getTraStatusOptions();

        // $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        $form->fields = [
            "folio" => ["label" => "Folio", "type" => "text", "value" => $tramite['folio'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "disabled"=>"disabled"],
            "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "fecha_asignacion" => ["label" => "Fecha Asignacion", "type" => "datetime", "value" => $tramite['fecha_asignacion'], "readonly"=>"readonly", "disabled"=>"disabled"],
            
            "fecha_conclusion" => ["label" => "Fecha de Conclusión", "type" => "datetime", "value" => $tramite['fecha_conclusion'], "readonly"=>"readonly", "disabled"=>"disabled"],
            
            "numero_factura" => array_merge(["label" => "Número de Factura", "type" => "number", "value" => $tramite['costo_total']], $ro_numero_factura),
            "numero_refactura" => array_merge(["label" => "Número de Refactura", "type" => "number", "value" => $tramite['costo_total']], $ro_numero_refactura),
            "reembolso_status_id" => array_merge(["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']], $ro_reembolso_status_id),

            "cobro_status_id" => array_merge(["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id']], $ro_cobro_status_id),

            "costo_gestoria" => array_merge(["label" => "Costo Gestoría", "type" => "number", "value" => $tramite['costo_gestoria']], $ro_costo_gestoria),
            "impuesto_gestoria" => array_merge(["label" => "Impuesto Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria']], $ro_impuesto_gestoria), 
            "derechos_tramite" => array_merge(["label" => "Derechos del Trámite", "type" => "number", "value" => $tramite['derechos_tramite']], $ro_derechos_tramite),
            "comision_derechos" => array_merge(["label" => "Comisión Derechos", "type" => "number", "value" => $tramite['comision_derechos']], $ro_comision_derechos),
            
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "readonly"=>"readonly"],

            "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "tra_status_id" => ["label" => "Estatus del Trámite", "type" => "select", "options" => $tra_status_options, "value" => $tramite['tra_status_id'], "readonly"=>"readonly", "disabled"=>"disabled"],
            "user_id" => ["label" => "User Id", "type" => "hidden", "value" => "$myid"],
        ];
        // echo "<pre>";

        // print_r($form->fields);
        // echo "</pre>";die();
        $data['id'] = $id;
        $form->id = $id;
        $form->tra_status_id = $tra_status_id;
        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;

        $cruddocstatus = $this->_getGroceryCrudEnterprise();
        $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/' . $id);
        $output = $cruddocstatus->render();

        $crudevidencias = $this->_getGroceryCrudEnterprise();
        $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'. $id);
        $outputevidencias = $crudevidencias->render();

        $crud_finalevidencias = $this->_getGroceryCrudEnterprise();
        $crud_finalevidencias->setApiUrlPath('/deskapp/proceso/single_evidencias_finales/' . $id);
        $output_finalevidencias = $crud_finalevidencias->render();

        $output_finalevidencias->output .= "<hr>".$output->output . $outputevidencias->output;

        $form->output = $output_finalevidencias->output;
        $form = array_merge((array)$form, $data);
        return $this->_example_output_final($form);
    }

    private function _example_output_final($output = null) {
        return view('/deskapp/extra-pages/tramite_final_view', (array)$output);
        // $this->load->view('tramite_' . $page . '_view', (array)$output);
    }

    public function update_final_save() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();    
        // Set validation rules
        $validation->setRules([
            "costo_gestoria" => "required",
            "costo_total" => "required"
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
            // $folio = $data["folio"];
            #adding bitacora
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $data;
            $diferencias = $this->encontrarDiferencias($data_bitacora, []);
            $insert_bitacora = [
                "id"=>null,
                "tipo"=>"update",
                "origen"=>"final",
                // "folio_tramite" => $folio,
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $result = $bitacoraModel->insert($insert_bitacora, 'bitacora');

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/proceso/update_final/'.$id
            ]);
        }
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
                "updated_at" => date('Y-m-d H:i:s')
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
            'assets/uploads/proceso/', 
            '/assets/uploads/proceso/', 
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

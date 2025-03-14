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
use App\Models\PagoDerechosModel;
use App\Models\PagoGestorStatusModel;

class Concluido extends BaseController
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
            $tramite_crud->unsetDelete();
            $tramite_crud->unsetDeleteMultiple();

            if (!has_permission('export_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->unsetPrint();
            }

            if (has_permission('read_tramite', esc($session->get('user_permissions')),esc($session->get('user_roles')))){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/concluido/ver/' . $row->id;
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
                 
            $tramite_crud->where("tra_status_id IN (20)");

            $tramite_crud->columns([
                'id', 'reembolso_status_id', 'created_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->callbackColumn('reembolso_status_id', function ($value, $row) {
                // Definir clases CSS
                $claseVerde = 'background-verde';          // Clase para terminado completamente
                $claseNaranjaCalido = 'background-naranja-calido';  // Clase para pendientes (21 o 22)
                $claseRoja = 'background-roja';            // Clase para pendiente (Cliente)

                // Verificar el valor de reembolso_status_id
                // if (in_array($row->reembolso_status_id, [21, 22])) {
                //     // Pendiente, falta proceso
                //     $clase = $claseNaranjaCalido;
                //     $mensaje = 'Pendiente (Gestor)';
                // } else {
                //     // Completamente terminado
                //     $clase = $claseVerde;
                //     $mensaje = 'Finalizado';
                // }

                

                // Si el cobro no está pendiente, evaluar reembolso_status_id
                if (in_array($row->reembolso_status_id, [21, 22])) {
                    // Pendiente, falta proceso por parte del gestor
                    $clase = $claseNaranjaCalido;
                    $mensaje = 'Pago Pendiente (Gestor)';
                } else {
                    if ($row->cobro_status_id == 22) {
                        // Si el cobro del cliente está pendiente, mostrar "Pendiente (Cliente)"
                        $clase = $claseRoja;
                        $mensaje = 'Cobro Pendiente (Cliente)';
                    } else {
                        // Completamente terminado
                        $clase = $claseVerde;  // Corregido: $clase en lugar de $clase
                        $mensaje = 'Finalizado';
                    }
                }


            
                // Devolver el HTML con la clase y el mensaje
                return '<span class="' . $clase . '">' . $mensaje . '</span>';
            });

            $tramite_crud->displayAs("diferencia_status", "Finalizado");
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->fields([
                'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]); 

            $tramite_crud->displayAs("created_at", "Creación");
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

            /* SELECT Se configura el la entidad */
            $tramite_crud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramite_crud->displayAs('entidad_id','Entidad');

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

    public function ver($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();

        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();

        // $entMunicipios = new EntMunicipioModel($db2);
        // $ent_municipio_options = $entMunicipios->getEntMunicipios();

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions();
        $empGestora = new EmpresaGestoraModel($db2);
        $empresa_gestora_options = $empGestora->getEmpresasGestorasOptions();

        $traStatus = new TraStatusModel($db2);
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        $tra_status_steps = $tra_status_obj["steps"];

        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();

        $pago_derechos = new PagoDerechosModel($db2);
        $pago_derechos_db = $pago_derechos->getImgDerechosByTramiteId($id);

        $pago_gestor_st = new PagoGestorStatusModel($db2);
        $pago_gestor_st_opciones = $pago_gestor_st->getPagoGestorStatusOptions();

        // $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        $puede_modificar = ["disabled"=>"disabled"];
        // if(is_admin($session->get('user_roles'))){
        //     $puede_modificar = [];
        // }
        $form->fields = [
            "folio" => array_merge(["label" => "Folio", "type" => "hidden", "value" => $tramite['folio']], $puede_modificar),
            "contrato" => array_merge(["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"], $puede_modificar),
            "unidad" => array_merge(["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']], $puede_modificar),
            "serie" => array_merge(["label" => "Serie", "type" => "text", "value" => $tramite['serie']], $puede_modificar),
            "placas" => array_merge(["label" => "Placas", "type" => "text", "value" => $tramite['placas']], $puede_modificar),
            "tra_tipos_id" => array_merge(["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id']], $puede_modificar),
            "cli_directo_id" => array_merge(["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']], $puede_modificar),
            "cli_directo_ejecutivo_id" => array_merge(["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']], $puede_modificar),
            "entidad_id" => array_merge(["label" => "Entidad", "type" => "select", "options" => $entidad_options, "value" => $tramite['entidad_id'], "required"=>"required"], $puede_modificar),
            "observaciones" => array_merge(["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']], $puede_modificar)
        ];
        

        $form->gestor_campos = [
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "required" => "required", "disabled"=>"disabled"],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "required" => "required", "disabled"=>"disabled"]
        ];
        // }
        
        $form->derechos_campos = [
            "derechos_tramite" => ["label" => "Monto pago de derechos", "type" => "number", "value" => $tramite['derechos_tramite'], "required" => "required", "disabled"=>"disabled"],
            "derechos_pago_sitio" => ["label" => "Pago", "type" => "select", "options" => ["online"=>"En Linea", "ventanilla"=>"En Ventanilla"], "value" => $tramite['derechos_pago_sitio'], "disabled"=>"disabled"],
            "derechos_vigencia" => ["label" => "Fecha Vigencia", "type" => "datetime", "value" => $tramite['derechos_vigencia'], "disabled"=>"disabled"]
        ];
        
        $form->bancario_campos = [
            "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required", "disabled"=>"disabled"],
            "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required", "disabled"=>"disabled"],
        ];

        $gestor_model = new GestorModel($db2);
        $gestor_nombre = $gestor_model->getGestorNameById($tramite['gestor_id']);

        $puede_modificar_pendiente = ["disabled"=>"disabled"];
        $reembolso_pendiente = false;
        if(in_array($tramite['reembolso_status_id'], [21, 22])){
            $reembolso_pendiente = true;
            $puede_modificar_pendiente = [];
        }
        
        $form->pago_gestor = [
            "gestor_id" => ["label" => "Gestor", "type" => "text", "value" => $gestor_nombre, "disabled"=>"disabled"],
            "costo_tramite" => array_merge(["label" => "Costo del Trámite", "type" => "number", "value" => $tramite['costo_tramite'], "required" => "required"], $puede_modificar_pendiente),
            "deposito_gestor" => array_merge(["label" => "Deposito a Gestor", "type" => "number", "value" => $tramite['deposito_gestor'], "required" => "required"], $puede_modificar_pendiente),
            "col_a_favor" => ["label" => "Saldo Pendiente", "type" => "number", "value" => $tramite['col_a_favor'], "required" => "required", "disabled"=>"disabled"], 
            "num_factura_gestor" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['num_factura_gestor'], "disabled"=>"disabled"],    
            "pago_gestor_st_id" => ["label" => "Estatus del Pago", "type" => "select", "options" => $pago_gestor_st_opciones, "value" => $tramite['pago_gestor_st_id'], "disabled"=>"disabled"],
            "impuesto_gestoria" => ["label" => "Honorarios de Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria'], "required" => "required", "disabled"=>"disabled"],
            "gestoria_comision" => ["label" => "Gratificación", "type" => "number", "value" => $tramite['gestoria_comision'], "required" => "required", "disabled"=>"disabled"],
            "gestor_total_pago" => ["label" => "Pago Total", "type" => "number", "value" => $tramite['gestor_total_pago'], "required" => "required", "disabled"=>"disabled"],
            "reembolso_status_id" => array_merge(["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']], $puede_modificar_pendiente),
        ];

        $form->final_campos = [
            "id_give_cliente" => ["label" => "ID del cliente", "type" => "text", "value" => $tramite['id_give_cliente'], "required" => "required", "disabled"=>"disabled"],
            "numero_factura" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['numero_factura'], "required" => "required", "disabled"=>"disabled"],
            "numero_refactura" => ["label" => "Número de Refactura", "type" => "text", "value" => $tramite['numero_refactura'], "disabled"=>"disabled"],
            "cobro_status_id" => ["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id'], "disabled"=>"disabled"],
            "costo_gestoria" => ["label" => "Costo de Gestoría", "type" => "number", "value" => $tramite['costo_gestoria'], "required" => "required", "disabled"=>"disabled"],
            "costo_pago_cliente"=> ["label" => "Honorarios del Trámite", "type" => "number", "value" => $tramite['costo_pago_cliente'], "required" => "required", "disabled"=>"disabled"],
            "comision_derechos" => ["label" => "Comisión de Derechos", "type" => "number", "value" => $tramite['comision_derechos'], "required" => "required", "disabled"=>"disabled"],
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "disabled"=>"disabled"],
        ];
        
        $data['id'] = $id;
        $data['folio'] = $tramite['folio'];
        $data['tra_tipo'] = $tra_tipos_options[$tramite['tra_tipos_id']];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']];
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];
        $data['reembolso_pendiente'] = $reembolso_pendiente;

        $data['step'] = $tra_status_steps[$tramite['tra_status_id']];
        $data['started_at'] = $tramite['started_at'];
        $data['derechos_comprobante'] = $tramite['derechos_comprobante'];
        // $data['images_derechos_comprobante'] = $images_derechos;
        // $data['images_pago_gestor'] = $images_gestor;
        // $data['images_cobro_cliente'] = $images_cobro_cliente;
        $form->id = $id;

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();
        
        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        $cruddocstatus = $this->_getGroceryCrudEnterprise();
        $cruddocstatus->setApiUrlPath('/deskapp/concluido/single_documentostatus/'.$id);
        $output_docs = $cruddocstatus->render();            
        
        $crudevidencias = $this->_getGroceryCrudEnterprise();
        $crudevidencias->setApiUrlPath('/deskapp/concluido/single_evidencias/'.$id);
        $outputevidencias = $crudevidencias->render();

        $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
        $crudevidencias_finales->setApiUrlPath('/deskapp/concluido/single_evidencias_finales/' . $id);
        $outputevidencias_finales = $crudevidencias_finales->render();

        $crud_derechos = $this->_getGroceryCrudEnterprise();
        $crud_derechos->setApiUrlPath('/deskapp/concluido/single_pago_derechos/' . $id);
        $output_derechos = $crud_derechos->render();

        $crud_pago_gestor = $this->_getGroceryCrudEnterprise();
        $crud_pago_gestor->setApiUrlPath('/deskapp/concluido/single_pago_gestor/' . $id);
        $output_pago_gestor = $crud_pago_gestor->render();

        $crud_cobro_cliente = $this->_getGroceryCrudEnterprise();
        $crud_cobro_cliente->setApiUrlPath('/deskapp/concluido/single_cobro_cliente/' . $id);
        $output_cobro_cliente = $crud_cobro_cliente->render();

        $form->output_docs = $output_docs->output;
        $form->output_bitacora = $outputevidencias->output;
        $form->outputevidencias_finales = $outputevidencias_finales->output;
        $form->output_derechos = $output_derechos->output;
        $form->output_pago_gestor = $output_pago_gestor->output;
        $form->output_cobro_cliente = $output_cobro_cliente->output;

        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'finalizados');
    }

    public function update_gestor_costos() {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);  // Obtener el ID del trámite desde la URL
    
        // Validación de los campos
        $validation = \Config\Services::validation();
        $validation->setRules([
            "costo_tramite" => "required|numeric",
            "deposito_gestor" => "required|numeric",
            "reembolso_status_id" => "required|integer"
        ]);
    
        // Validación fallida
        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }
    
        // Obtener los datos enviados
        $data = $this->request->getPost();
    
        try {
            // Conexión a la base de datos y actualización del trámite
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
    
            // Actualizar los campos en la tabla tramite
            $builder->where('id', $id);
            $builder->update([
                'costo_tramite' => $data['costo_tramite'],
                'deposito_gestor' => $data['deposito_gestor'],
                'reembolso_status_id' => $data['reembolso_status_id']
            ]);
    
            // Registro en la bitácora
            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->encontrarDiferencias($data, []);
            $insert_bitacora = [
                "id" => null,
                "tipo" => "update",
                "origen" => "tramite",
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');
    
            // Registro en tra_user_log
            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id" => (int)$id,
                "user_id" => (int)$myid,
                "tra_status_id" => 22  // Estado ejemplo
            ];
            $tra_user_log->insert($log, 'tra_user_log');
    
            // Retornar éxito como respuesta JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Los costos del trámite se actualizaron correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id
            ]);
        } catch (\Exception $e) {
            // Manejo de errores
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar los costos: ' . $e->getMessage()
            ]);
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

    public function getEjecutivosByClienteId($clienteDirectoId) {
        $db2 = $this->_getDbData();
        $ejecutivoModel = new ClienteDirectoEjecutivoModel($db2);
        $options = $ejecutivoModel->getEjecutivosOptions($clienteDirectoId);
        
        return $this->response->setJSON($options);
    }

    public function getPagoGestorFiles($id)
    {
        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        // Ruta de la carpeta donde se almacenan los archivos
        $storeFolderSpecific = 'assets/uploads/pago_gestor/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_pago_gestor')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Función para asignar íconos personalizados según el tipo de archivo
        $assignIcon = function ($extension, $filePath) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            // Si el archivo es una imagen, devolver la ruta del archivo
            if (in_array(strtolower($extension), $imageExtensions)) {
                return base_url($filePath);
            }

            // Si no es una imagen, usar un ícono específico por extensión
            $icons = [
                'xml'  => '/public/assets/src/images/xml-icon.png',
                'pdf'  => '/public/assets/src/images/pdf-icon.png',
                'doc'  => '/public/assets/src/images/doc-icon.png',
                'docx' => '/public/assets/src/images/docx-icon.png',
                'xls'  => '/public/assets/src/images/xls-icon.png',
                'xlsx' => '/public/assets/src/images/xlsx-icon.png',
                'txt'  => '/public/assets/src/images/txt-icon.png',
                'zip'  => '/public/assets/src/images/zip-icon.png',
                'rar'  => '/public/assets/src/images/rar-icon.png',
            ];

            return $icons[strtolower($extension)] ?? '/public/assets/src/images/file-icon.png';
        };

        // Validar archivos de la base de datos
        foreach ($cobro_cliente_db as $dbFile) {
            $filePath = $storeFolderSpecific . $dbFile->file;
            $absoluteFilePath = FCPATH . $filePath;

            // Verificar que el archivo exista físicamente
            if (file_exists($absoluteFilePath)) {
                $extension = strtolower(pathinfo($dbFile->file, PATHINFO_EXTENSION));
                $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
                $obj['name'] = $dbFile->file; // Nombre del archivo
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
                $obj['existing_path'] = base_url($filePath); // Ruta para la vista
                $obj['icon'] = $assignIcon($extension, $filePath); // Ícono o imagen real según el tipo de archivo
                $result[] = $obj;
            }
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }
    public function getPagoDerechosFiles($id)
    {
        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        // Ruta de la carpeta donde se almacenan los archivos
        $storeFolderSpecific = 'assets/uploads/pago_derechos/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_pago_derechos')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Función para asignar íconos personalizados según el tipo de archivo
        $assignIcon = function ($extension, $filePath) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            // Si el archivo es una imagen, devolver la ruta del archivo
            if (in_array(strtolower($extension), $imageExtensions)) {
                return base_url($filePath);
            }

            // Si no es una imagen, usar un ícono específico por extensión
            $icons = [
                'xml'  => '/public/assets/src/images/xml-icon.png',
                'pdf'  => '/public/assets/src/images/pdf-icon.png',
                'doc'  => '/public/assets/src/images/doc-icon.png',
                'docx' => '/public/assets/src/images/docx-icon.png',
                'xls'  => '/public/assets/src/images/xls-icon.png',
                'xlsx' => '/public/assets/src/images/xlsx-icon.png',
                'txt'  => '/public/assets/src/images/txt-icon.png',
                'zip'  => '/public/assets/src/images/zip-icon.png',
                'rar'  => '/public/assets/src/images/rar-icon.png',
            ];

            return $icons[strtolower($extension)] ?? '/public/assets/src/images/file-icon.png';
        };

        // Validar archivos de la base de datos
        foreach ($cobro_cliente_db as $dbFile) {
            $filePath = $storeFolderSpecific . $dbFile->file;
            $absoluteFilePath = FCPATH . $filePath;

            // Verificar que el archivo exista físicamente
            if (file_exists($absoluteFilePath)) {
                $extension = strtolower(pathinfo($dbFile->file, PATHINFO_EXTENSION));
                $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
                $obj['name'] = $dbFile->file; // Nombre del archivo
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
                $obj['existing_path'] = base_url($filePath); // Ruta para la vista
                $obj['icon'] = $assignIcon($extension, $filePath); // Ícono o imagen real según el tipo de archivo
                $result[] = $obj;
            }
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }

    public function getCobroClienteFiles($id)
    {
        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        // Ruta de la carpeta donde se almacenan los archivos
        $storeFolderSpecific = 'assets/uploads/cobro_cliente/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_cobro_cliente')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Función para asignar íconos personalizados según el tipo de archivo
        $assignIcon = function ($extension, $filePath) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            // Si el archivo es una imagen, devolver la ruta del archivo
            if (in_array(strtolower($extension), $imageExtensions)) {
                return base_url($filePath);
            }

            // Si no es una imagen, usar un ícono específico por extensión
            $icons = [
                'xml'  => '/public/assets/src/images/xml-icon.png',
                'pdf'  => '/public/assets/src/images/pdf-icon.png',
                'doc'  => '/public/assets/src/images/doc-icon.png',
                'docx' => '/public/assets/src/images/docx-icon.png',
                'xls'  => '/public/assets/src/images/xls-icon.png',
                'xlsx' => '/public/assets/src/images/xlsx-icon.png',
                'txt'  => '/public/assets/src/images/txt-icon.png',
                'zip'  => '/public/assets/src/images/zip-icon.png',
                'rar'  => '/public/assets/src/images/rar-icon.png',
            ];

            return $icons[strtolower($extension)] ?? '/public/assets/src/images/file-icon.png';
        };

        // Validar archivos de la base de datos
        foreach ($cobro_cliente_db as $dbFile) {
            $filePath = $storeFolderSpecific . $dbFile->file;
            $absoluteFilePath = FCPATH . $filePath;

            // Verificar que el archivo exista físicamente
            if (file_exists($absoluteFilePath)) {
                $extension = strtolower(pathinfo($dbFile->file, PATHINFO_EXTENSION));
                $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
                $obj['name'] = $dbFile->file; // Nombre del archivo
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
                $obj['existing_path'] = base_url($filePath); // Ruta para la vista
                $obj['icon'] = $assignIcon($extension, $filePath); // Ícono o imagen real según el tipo de archivo
                $result[] = $obj;
            }
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
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

    private function _example_output_2($output = null, $page = 'index') {
        return view('/deskapp/extra-pages/tramite_' . $page . '_view', (array)$output);
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
            // log_message('error', $e->getMessage());
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
         // Callbacks para registrar el log
         $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
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

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

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
        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
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

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
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

        $crud->callbackColumn('comentario', function($value, $row) {
            return '<div style="white-space: normal;">' . htmlspecialchars($value, ENT_QUOTES) . '</div>';
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
        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
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
        $crud->callbackColumn('comentario', function($value, $row) {
            // Recortar el texto si es muy largo
            $shortened_value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        
            // Retornar el texto con un tooltip para ver el contenido completo
            return '<span title="' . htmlspecialchars($value, ENT_QUOTES) . '">' . $shortened_value . '</span>';
        });
        // $crud->callbackColumn('comentario', function($value, $row) {
        //     return '<div style="white-space: normal;">' . htmlspecialchars($value, ENT_QUOTES) . '</div>';
        // });


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

            $tramite_id = (int) $uri->getSegment(4);
            $folio_tramite = $session->get('folio_tramite_id');

            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['folio_tramite'] = $folio_tramite;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });
        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_pago_derechos(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
    
        // Paso 1: Definir directorios
        $sourceDir = FCPATH . 'assets/uploads/pago_derechos/';
        $targetDir = FCPATH . 'assets/uploads/pago_derechos/' . $tramite_id . '/';

        // Verificar si el directorio de destino existe
        if (!is_dir($targetDir)) {
            // Intentar crear el directorio con permisos 777
            if (!mkdir($targetDir, 0777, true)) {
                return json_encode([
                    'status' => 'error',
                    'message' => 'No se pudo crear el directorio de destino. Verifica permisos.'
                ]);
            }
        }
        chmod($targetDir, 0777); // Asegurar permisos
        // Paso 2: Consultar las imágenes vinculadas en la tabla tra_pago_derechos
        $db = \Config\Database::connect();
        $builder = $db->table('tra_pago_derechos');
        $builder->select('file');
        $builder->where('tramite_id', $tramite_id);
        $query = $builder->get();
        $files = $query->getResultArray();

        foreach ($files as $file) {
            $fileName = $file['file'];
            $sourceFilePath = $sourceDir . $fileName;
            $targetFilePath = $targetDir . $fileName;

            // Verificar si el archivo existe en el directorio fuente
            if (file_exists($sourceFilePath)) {
                // Copiar archivo solo si no existe en el directorio destino
                if (!file_exists($targetFilePath)) {
                    if (!copy($sourceFilePath, $targetFilePath)) {
                        return json_encode([
                            'status' => 'error',
                            'message' => "Error al copiar el archivo $fileName a $targetDir."
                        ]);
                    }
                }
            } 

        }

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setSkin('bootstrap');
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetRead();

        // $tramite_crud->setTheme('bootstrap-v5');
        $crud->unsetDeleteMultiple();
        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
        $crud->unsetExport();
        $crud->unsetPrint();
        // $crud->unsetFilters();
        $crud->unsetClone();
        $crud->setTable('tra_pago_derechos');
        $crud->setSubject('Pago', 'Pagos de Derechos');
        $crud->defaultOrdering('tra_pago_derechos.created_at', 'desc');

        $crud->fields([
            "file", "costo", "comentario", "tramite_id", "user_id"
        ]); 

        $crud->columns([
            "id", "created_at", "file", "costo", "comentario", "user_id"
        ]);

        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');

        $crud->where([
            'tramite_id' => $tramite_id
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
                    "origen"=>"derechos",
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

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self) {
            $db = \Config\Database::connect();
            $session = session();
            $request = \Config\Services::request();
        
            // Datos generales
            $userId = $session->get('id'); // ID del usuario actual
            $uri = $request->getUri(); // URI completa
            $method = $request->getMethod(); // Método HTTP
            $endpoint = $uri->getPath(); // Ruta completa como endpoint
            $primaryKeyValue = $stateParameters->primaryKeyValue; // ID afectado
            $data = $stateParameters->data; // Datos enviados desde Grocery CRUD
        
            // Extraer controlador y acción de la URL
            $pathParts = explode('/', $endpoint);
            $controller = $pathParts[1] ?? null; // El segundo segmento como controlador
            $action = $pathParts[2] ?? null; // El tercer segmento como acción
        
            // Buscar números en la URL
            preg_match_all('/\d+/', $endpoint, $matches);
            $numbers = $matches[0] ?? [];
        
            // Obtener ID principal y números adicionales
            $sent_id = $numbers[0] ?? 0; // Primer número
            $additional_ids = array_slice($numbers, 1); // Números adicionales
        
            // Determinar las diferencias de datos
            $oldData = []; // Aquí puedes incluir lógica para obtener datos anteriores si es necesario
            $diferencias = $self->encontrarDiferencias($data, $oldData);
        
            // Obtener la respuesta del estado de Grocery CRUD
            $response = [
                'success' => $stateParameters->success,
                'message' => $stateParameters->message,
                'data'    => $stateParameters->data,
            ];
        
            // Registrar el log
            $logModel = new \App\Models\ApiLogModel($db);
            $logData = [
                'method'     => $method,
                'endpoint'   => $endpoint,
                'controller' => $controller,
                'action'     => $action,
                'sent_id'    => $sent_id,
                'vista'      => implode(',', $additional_ids), // Números adicionales concatenados
                'body'       => json_encode($data), // Datos enviados a Grocery CRUD
                'response'   => json_encode($response), // Respuesta generada por Grocery CRUD
                'user_id'    => $userId,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent()->getAgentString(),
            ];
        
            $logModel->insert($logData);
        
            return $stateParameters; // Continuar con el flujo normal
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUpload(
            'file', 
            'assets/uploads/pago_derechos/'.$tramite_id.'/', 
            '/assets/uploads/pago_derechos/'.$tramite_id.'/', 
            $uploadValidations
        );

        $crud->callbackBeforeDelete(function ($stateParameters) {
            // Access the primary key value directly from the $stateParameters object
            // var_dump($stateParameters->primaryKeyValue);die();

            $primaryKeyValue = (int)$stateParameters->primaryKeyValue;
        
            // Log the primary key value for debugging purposes
            // log_message('debug', "Primary Key Value: var_dump($primaryKeyValue)");
        
            // Database connection
            $db = \Config\Database::connect();
        
            // Query to retrieve file and tramite_id using the primary key
            $builder = $db->table('tra_pago_derechos');
            $builder->select('file, tramite_id');
            $builder->where('id', $primaryKeyValue);
        
            $query = $builder->get();
            $row = $query->getRowArray();
        
            if ($row) {
                $tramite_id = $row['tramite_id'];
                $fileName = $row['file'];
        
                // Define the base image path
                $baseImagePath = FCPATH . 'assets/uploads/pago_derechos/';
        
                // Ensure tramite_id and fileName are available
                if ($tramite_id && $fileName) {
                    // Construct the full file path
                    $filePath = $baseImagePath . $tramite_id . '/' . $fileName;
        
                    // Check if the file exists
                    if (file_exists($filePath)) {
                        // Attempt to delete the file
                        if (unlink($filePath)) {
                            // log_message('info', "File successfully deleted: $filePath");
                        } else {
                            // log_message('error', "Failed to delete file: $filePath");
                        }
                    } else {
                        // log_message('warning', "File does not exist: $filePath");
                    }
                } else {
                    // log_message('error', "Incomplete data: Tramite ID: $tramite_id, File: $fileName");
                }
            } else {
                // log_message('error', "No record found for Primary Key: $primaryKeyValue");
            }
        
            // Continue with the delete operation
            return $stateParameters;
        });
        
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
            return $data;
        });

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_pago_gestor(){
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();
    
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);
    
        // Paso 1: Definir directorios
        $sourceDir = FCPATH . 'assets/uploads/pago_gestor/';
        $targetDir = FCPATH . 'assets/uploads/pago_gestor/' . $tramite_id . '/';
    
        // Verificar si el directorio de destino existe
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                return json_encode([
                    'status' => 'error',
                    'message' => 'No se pudo crear el directorio de destino. Verifica permisos.'
                ]);
            }
            chmod($targetDir, 0777); // Asegurar permisos
        }
    
        // Paso 2: Consultar las imágenes vinculadas en la tabla tra_pago_gestor
        $db = \Config\Database::connect();
        $builder = $db->table('tra_pago_gestor');
        $builder->select('file');
        $builder->where('tramite_id', $tramite_id);
        $query = $builder->get();
        $files = $query->getResultArray();
    
        foreach ($files as $file) {
            $fileName = $file['file'];
            $sourceFilePath = $sourceDir . $fileName;
            $targetFilePath = $targetDir . $fileName;
    
            // Verificar si el archivo existe en el directorio fuente
            if (file_exists($sourceFilePath)) {
                // Copiar archivo solo si no existe en el directorio destino
                if (!file_exists($targetFilePath)) {
                    if (!copy($sourceFilePath, $targetFilePath)) {
                        return json_encode([
                            'status' => 'error',
                            'message' => "Error al copiar el archivo $fileName a $targetDir."
                        ]);
                    }
                }
            }
        }
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setSkin('bootstrap');
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetRead();
    
        $crud->unsetDeleteMultiple();
        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetClone();
        $crud->setTable('tra_pago_gestor');
        $crud->setSubject('Pago', 'Pagos de Gestor');
        $crud->defaultOrdering('tra_pago_gestor.created_at', 'desc');
    
        $crud->fields([
            "file", "costo", "comentario", "tramite_id", "user_id"
        ]);
    
        $crud->columns([
            "id", "created_at", "file", "costo", "comentario", "user_id"
        ]);
    
        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
    
        $crud->where([
            'tramite_id' => $tramite_id
        ]);
    
        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);
    
                $myid = $session->get('id');
    
                $bitacoraModel = new BitacoraModel($self->_getDbData());
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"gestor",
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });
    
        $crud->callbackAddForm(function ($data) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', // 20 Mega Bytes
            'minUploadSize' => '1K', // 1 Kilo Byte
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUpload(
            'file', 
            'assets/uploads/pago_gestor/'.$tramite_id.'/', 
            '/assets/uploads/pago_gestor/'.$tramite_id.'/', 
            $uploadValidations
        );

        $crud->callbackBeforeDelete(function ($stateParameters) {
            // Access the primary key value directly from the $stateParameters object
            // var_dump($stateParameters->primaryKeyValue);die();

            $primaryKeyValue = (int)$stateParameters->primaryKeyValue;
        
            // Log the primary key value for debugging purposes
            // log_message('debug', "Primary Key Value: var_dump($primaryKeyValue)");
        
            // Database connection
            $db = \Config\Database::connect();
        
            // Query to retrieve file and tramite_id using the primary key
            $builder = $db->table('tra_pago_gestor');
            $builder->select('file, tramite_id');
            $builder->where('id', $primaryKeyValue);
        
            $query = $builder->get();
            $row = $query->getRowArray();
        
            if ($row) {
                $tramite_id = $row['tramite_id'];
                $fileName = $row['file'];
        
                // Define the base image path
                $baseImagePath = FCPATH . 'assets/uploads/pago_gestor/';
        
                // Ensure tramite_id and fileName are available
                if ($tramite_id && $fileName) {
                    // Construct the full file path
                    $filePath = $baseImagePath . $tramite_id . '/' . $fileName;
        
                    // Check if the file exists
                    if (file_exists($filePath)) {
                        // Attempt to delete the file
                        if (unlink($filePath)) {
                            // log_message('info', "File successfully deleted: $filePath");
                        } else {
                            // log_message('error', "Failed to delete file: $filePath");
                        }
                    } else {
                        // log_message('warning', "File does not exist: $filePath");
                    }
                } else {
                    // log_message('error', "Incomplete data: Tramite ID: $tramite_id, File: $fileName");
                }
            } else {
                // log_message('error', "No record found for Primary Key: $primaryKeyValue");
            }
        
            // Continue with the delete operation
            return $stateParameters;
        });
    
        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_cobro_cliente()
    {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        // Paso 1: Definir directorios
        $sourceDir = FCPATH . 'assets/uploads/cobro_cliente/';
        $targetDir = FCPATH . 'assets/uploads/cobro_cliente/' . $tramite_id . '/';

        // Verificar si el directorio de destino existe
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                return json_encode([
                    'status' => 'error',
                    'message' => 'No se pudo crear el directorio de destino. Verifica permisos.'
                ]);
            }
            chmod($targetDir, 0777); // Asegurar permisos
        }

        // Paso 2: Consultar las imágenes vinculadas en la tabla tra_cobro_cliente
        $db = \Config\Database::connect();
        $builder = $db->table('tra_cobro_cliente');
        $builder->select('file');
        $builder->where('tramite_id', $tramite_id);
        $query = $builder->get();
        $files = $query->getResultArray();

        foreach ($files as $file) {
            $fileName = $file['file'];
            $sourceFilePath = $sourceDir . $fileName;
            $targetFilePath = $targetDir . $fileName;

            // Verificar si el archivo existe en el directorio fuente
            if (file_exists($sourceFilePath)) {
                // Copiar archivo solo si no existe en el directorio destino
                if (!file_exists($targetFilePath)) {
                    if (!copy($sourceFilePath, $targetFilePath)) {
                        return json_encode([
                            'status' => 'error',
                            'message' => "Error al copiar el archivo $fileName a $targetDir."
                        ]);
                    }
                }
            }
        }

        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setSkin('bootstrap');
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetRead();
        $crud->unsetDeleteMultiple();
        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetClone();
        $crud->setTable('tra_cobro_cliente');
        $crud->setSubject('Cobro', 'Cobros a Cliente');
        $crud->defaultOrdering('tra_cobro_cliente.created_at', 'desc');

        $crud->fields([
            "file", "costo", "comentario", "tramite_id", "user_id"
        ]);

        $crud->columns([
            "id", "created_at", "file", "costo", "comentario", "user_id"
        ]);

        $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');

        $crud->where([
            'tramite_id' => $tramite_id
        ]);

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $parameters = $stateParameters;
                $data = $parameters->data;
                $request = \Config\Services::request();
                $uri = $request->getUri();
                $tramite_id = (int) $uri->getSegment(4);

                $myid = $session->get('id');

                $bitacoraModel = new BitacoraModel($self->_getDbData());
                $data_bitacora = $data;
                $diferencias = $self->encontrarDiferencias($data_bitacora, []);
                $insert_bitacora = [
                    "id"=>null,
                    "tipo"=>"insert",
                    "origen"=>"cliente",
                    "tramite_id" => (int)$tramite_id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ];
                $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }
            return $stateParameters;
        });

        $crud->callbackAddForm(function ($data) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $myid = $session->get('id');
            $data['user_id'] = $myid;
            $data['tramite_id'] = $tramite_id;
            return $data;
        });

        $uploadValidations = [
            'maxUploadSize' => '20M', 
            'minUploadSize' => '1K', 
            'allowedFileTypes' => [
                'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
            ]
        ];

        $crud->setFieldUploadMultiple(
            'file', 
            'assets/uploads/cobro_cliente/'.$tramite_id.'/', 
            '/assets/uploads/cobro_cliente/'.$tramite_id.'/', 
            $uploadValidations
        );

        $crud->callbackBeforeDelete(function ($stateParameters) {
            // Access the primary key value directly from the $stateParameters object
            // var_dump($stateParameters->primaryKeyValue);die();

            $primaryKeyValue = (int)$stateParameters->primaryKeyValue;
        
            // Database connection
            $db = \Config\Database::connect();
        
            // Query to retrieve file and tramite_id using the primary key
            $builder = $db->table('tra_cobro_cliente');
            $builder->select('file, tramite_id');
            $builder->where('id', $primaryKeyValue);
        
            $query = $builder->get();
            $row = $query->getRowArray();
        
            if ($row) {
                $tramite_id = $row['tramite_id'];
                $fileName = $row['file'];
        
                // Define the base image path
                $baseImagePath = FCPATH . 'assets/uploads/cobro_cliente/';
        
                // Ensure tramite_id and fileName are available
                if ($tramite_id && $fileName) {
                    // Construct the full file path
                    $filePath = $baseImagePath . $tramite_id . '/' . $fileName;
        
                    // Check if the file exists
                    if (file_exists($filePath)) {
                        // Attempt to delete the file
                        if (unlink($filePath)) {
                            // log_message('info', "File successfully deleted: $filePath");
                        } else {
                            // log_message('error', "Failed to delete file: $filePath");
                        }
                    } else {
                        // log_message('warning', "File does not exist: $filePath");
                    }
                } else {
                    // log_message('error', "Incomplete data: Tramite ID: $tramite_id, File: $fileName");
                }
            } else {
                // log_message('error', "No record found for Primary Key: $primaryKeyValue");
            }
        
            // Continue with the delete operation
            return $stateParameters;
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }


    private function logDeletedImage($primaryKey, $row)
    {
        // Ruta base de las imágenes
        $imageField = 'file'; // Cambia según el nombre de tu campo de imagen
        $imagePath = '/assets/uploads/pago_derechos/'.$primaryKey.'/'; // Cambia según tu configuración

        // Construir la ruta completa de la imagen
        $imageFile = isset($row[$imageField]) ? $imagePath . $row[$imageField] : 'No definido';

        // Registrar en el log el ID y la ruta de la imagen
        // log_message('info', "Registro eliminado: ID = $primaryKey, Imagen = $imageFile");
        // echo "Registro eliminado: ID = $primaryKey, Imagen = $imageFile";die();
        // Retornar true para continuar el flujo normal
        return true;
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
        $crud->unsetAdd();
        $crud->unsetDelete();
        $crud->unsetEdit();
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

            return $data;
        });

        // Callbacks para registrar el log
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
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
            if($statusId == 20){
                $builder->update([
                    'finished_at' => date('Y-m-d H:i:s'),
                    'tra_status_id' => $statusId
                ]);
            }else{
                $builder->update([
                    'tra_status_id' => $statusId
                ]);
            }
                

            
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

    public function cancelar_tramite(){
        $tramiteId = $this->request->getPost('tramite_id');
        $motivo = $this->request->getPost('motivo');
        $statusId = $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            // Actualizar el estatus del trámite

            $builder->where('id', $tramiteId);
            $builder->update([
                'tra_status_id' => $statusId,
                'cancelacion_motivo' => $motivo
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
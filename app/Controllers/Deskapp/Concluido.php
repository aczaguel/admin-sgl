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
        helper(['form', 'url', 'cliente_filter', 'cliente_context']);

        $session = session();
        $userId = $session->get('id');
        $requested = service('request')->getGet('cliente_id');

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

    public function final()
    {
        try {
            helper(['permissions']);

            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            # fin del manejo de session

            if (!$myid) {
                return redirect()->to('/deskapp/auth/login');
            }

            [$roles, $perms] = session_roles_perms($session);
            $canRead = has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles);
            if (!$canRead) {
                return redirect()->to('/deskapp/dashboard')->with('error', 'Acceso denegado.');
            }

            $tramite_crud = $this->_getGroceryCrudEnterprise();

            // Filtro multi-tenancy + cliente activo
            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            $tramite_crud->unsetDelete();
            $tramite_crud->unsetDeleteMultiple();

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
            }

            if (has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('clone_tramite', $perms, $roles)){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');
                 
            $tramite_crud->where('tra_status_id IN (' . SGL_TRA_STATUS_CONCLUIDO . ')');

            $tramite_crud->columns([
                'id', 'reembolso_status_id', 'created_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("reembolso_status_id", "Estatus de Reembolso");

            $tramite_crud->callbackColumn('reembolso_status_id', function ($value, $row) {
                // Definir clases CSS
                $claseVerde = 'background-azul-cobro-cliente';          // Clase para terminado completamente
                $claseNaranjaCalido = 'background-naranja-calido';  // Clase para pendientes (21 o 22)
                $claseNaranjaFuerte = 'background-naranja-fuerte';            // Clase para pendiente (Cliente)
                $claseRojo = 'background-rojo'; // Clase para ambos pendientes

                // Verificar si ambos procedimientos están pendientes
                if (in_array($row->reembolso_status_id, SGL_REEMBOLSO_STATUS_PENDING_IDS, true) && (int) $row->cobro_status_id === SGL_COBRO_STATUS_PENDIENTE) {
                    // Ambos están pendientes
                    $clase = $claseRojo;
                    $mensaje = 'Ambos Pendientes (Gestor y Cliente)';
                } else {
                    // Si el cobro no está pendiente, evaluar reembolso_status_id
                    if (in_array($row->reembolso_status_id, SGL_REEMBOLSO_STATUS_PENDING_IDS, true)) {
                        // Pendiente, falta proceso por parte del gestor
                        $clase = $claseNaranjaCalido;
                        $mensaje = 'Pago Pendiente (Gestor)';
                    } else {
                        if ((int) $row->cobro_status_id === SGL_COBRO_STATUS_PENDIENTE) {
                            // Si el cobro del cliente está pendiente, mostrar "Pendiente (Cliente)"
                            $clase = $claseNaranjaFuerte;
                            $mensaje = 'Cobro Pendiente (Cliente)';
                        } else {
                            // Completamente terminado
                            $clase = $claseVerde;
                            $mensaje = 'Finalizado';
                        }
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
            helper(['permissions']);
            [$rolesAcl, $permsAcl] = session_roles_perms($session ?? session());
            $salida_total['insert_button_url'] = can_create_tramite($rolesAcl, $permsAcl) ? '/public/deskapp/tramites/add' : '';

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
        if (in_array($tramite['reembolso_status_id'], SGL_REEMBOLSO_STATUS_PENDING_IDS, true)) {
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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $myid = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $id = (int) $this->request->uri->getSegment(4);  // Obtener el ID del trámite desde la URL

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        if (!has_permission('editar_pago_gestor', $perms, $roles)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
    
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

            $tramiteRow = $builder->select('id, tra_status_id')->where('id', $id)->get(1)->getRowArray();
            if (empty($tramiteRow)) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'El trámite no existe.']);
            }
    
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
                "tra_status_id" => (int) ($tramiteRow['tra_status_id'] ?? 0)
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
        return view('/deskapp/extra-pages/grocery_page', (array) $salida);
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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $clienteDirectoId = (int) $clienteDirectoId;
        if ($clienteDirectoId <= 0) {
            return acl_json_empty(400);
        }

        // Validación de acceso: si no es Super Admin, el cli_directo debe pertenecer a un cliente asignado
        if (!has_permission('bypass_cliente_filter', $perms, $roles)) {
            $db = \Config\Database::connect();
            $cliRow = $db->table('cli_directo')->select('cliente_id')->where('id', $clienteDirectoId)->get(1)->getRowArray();
            $clienteId = (int) ($cliRow['cliente_id'] ?? 0);
            if ($clienteId <= 0 || !has_access_to_cliente($clienteId, $userId)) {
                return acl_deny('Acceso denegado.', 403, null, true);
            }
        }

        $db2 = $this->_getDbData();
        $ejecutivoModel = new ClienteDirectoEjecutivoModel($db2);
        $options = $ejecutivoModel->getEjecutivosOptions($clienteDirectoId);
        return $this->response->setJSON($options);
    }

    public function getPagoGestorFiles($id)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        [$roles, $perms] = session_roles_perms($session);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);

        $id = (int) $id;
        if ($id <= 0) {
            return acl_json_empty(400);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        $category = 'pago_gestor';
        // Leer el driver de almacenamiento una sola vez por llamada.
        $driver = config('FileStorage')->driver;

        // Ruta de la carpeta donde se almacenan los archivos (solo aplica al driver local)
        $storeFolderSpecific = 'assets/uploads/' . $category . '/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_pago_gestor')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Ícono estático para archivos que no son imágenes (se conserva el mapa original)
        $staticIconFor = function ($extension) {
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
            $name = (string) $dbFile->file;

            // Omitir filas con valor vacío o compuesto solo por espacios.
            if (trim($name) === '') {
                continue;
            }

            // Bajo el driver local se conserva la verificación de existencia física
            // (file_exists) y el filesize(). Bajo s3 el objeto no está en disco local,
            // así que se omite el gate local, se omite 'size' y nunca se hace un
            // exists() por archivo contra S3.
            $absoluteFilePath = null;
            if ($driver === 'local') {
                $absoluteFilePath = FCPATH . $storeFolderSpecific . $name;
                if (!file_exists($absoluteFilePath)) {
                    continue;
                }
            }

            $existingPath = file_url($name, $category, $id);

            $obj = [];
            $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
            $obj['name'] = $name; // Nombre del archivo
            if ($driver === 'local') {
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
            }
            $obj['existing_path'] = $existingPath; // URL resuelta a través de file_url
            $obj['icon'] = is_image_filename($name)
                ? $existingPath
                : $staticIconFor(pathinfo($name, PATHINFO_EXTENSION)); // Imagen real o ícono estático
            $result[] = $obj;
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }
    public function getPagoDerechosFiles($id)
    {
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $id = (int) $id;
        if ($id <= 0) {
            return acl_json_empty(400);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('section_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        $category = 'pago_derechos';
        // Leer el driver de almacenamiento una sola vez por llamada.
        $driver = config('FileStorage')->driver;

        // Ruta de la carpeta donde se almacenan los archivos (solo aplica al driver local)
        $storeFolderSpecific = 'assets/uploads/' . $category . '/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_pago_derechos')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Ícono estático para archivos que no son imágenes (se conserva el mapa original)
        $staticIconFor = function ($extension) {
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
            $name = (string) $dbFile->file;

            // Omitir filas con valor vacío o compuesto solo por espacios.
            if (trim($name) === '') {
                continue;
            }

            // Bajo el driver local se conserva la verificación de existencia física
            // (file_exists) y el filesize(). Bajo s3 el objeto no está en disco local,
            // así que se omite el gate local, se omite 'size' y nunca se hace un
            // exists() por archivo contra S3.
            $absoluteFilePath = null;
            if ($driver === 'local') {
                $absoluteFilePath = FCPATH . $storeFolderSpecific . $name;
                if (!file_exists($absoluteFilePath)) {
                    continue;
                }
            }

            $existingPath = file_url($name, $category, $id);

            $obj = [];
            $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
            $obj['name'] = $name; // Nombre del archivo
            if ($driver === 'local') {
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
            }
            $obj['existing_path'] = $existingPath; // URL resuelta a través de file_url
            $obj['icon'] = is_image_filename($name)
                ? $existingPath
                : $staticIconFor(pathinfo($name, PATHINFO_EXTENSION)); // Imagen real o ícono estático
            $result[] = $obj;
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }

    public function getCobroClienteFiles($id)
    {
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $id = (int) $id;
        if ($id <= 0) {
            return acl_json_empty(400);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('section_final_costos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect(); // Conexión a la base de datos
        $result = [];
        $ds = DIRECTORY_SEPARATOR;

        $category = 'cobro_cliente';
        // Leer el driver de almacenamiento una sola vez por llamada.
        $driver = config('FileStorage')->driver;

        // Ruta de la carpeta donde se almacenan los archivos (solo aplica al driver local)
        $storeFolderSpecific = 'assets/uploads/' . $category . '/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_cobro_cliente')
                                ->select('id, file')
                                ->where('tramite_id', $id)
                                ->get()
                                ->getResultObject();

        // Ícono estático para archivos que no son imágenes (se conserva el mapa original)
        $staticIconFor = function ($extension) {
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
            $name = (string) $dbFile->file;

            // Omitir filas con valor vacío o compuesto solo por espacios.
            if (trim($name) === '') {
                continue;
            }

            // Bajo el driver local se conserva la verificación de existencia física
            // (file_exists) y el filesize(). Bajo s3 el objeto no está en disco local,
            // así que se omite el gate local, se omite 'size' y nunca se hace un
            // exists() por archivo contra S3.
            $absoluteFilePath = null;
            if ($driver === 'local') {
                $absoluteFilePath = FCPATH . $storeFolderSpecific . $name;
                if (!file_exists($absoluteFilePath)) {
                    continue;
                }
            }

            $existingPath = file_url($name, $category, $id);

            $obj = [];
            $obj['id'] = $dbFile->id; // ID del archivo en la base de datos
            $obj['name'] = $name; // Nombre del archivo
            if ($driver === 'local') {
                $obj['size'] = filesize($absoluteFilePath); // Tamaño del archivo físico
            }
            $obj['existing_path'] = $existingPath; // URL resuelta a través de file_url
            $obj['icon'] = is_image_filename($name)
                ? $existingPath
                : $staticIconFor(pathinfo($name, PATHINFO_EXTENSION)); // Imagen real o ícono estático
            $result[] = $obj;
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }

    public function getDependentData($type, $parentId) {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $canRead = has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles);
        if (!$canRead) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $db = \Config\Database::connect();

        switch ($type) {
            case 'gestor':
                $builder = $db->table('ges_gestor');
                if (!is_numeric($parentId)) {
                    return acl_json_empty(400);
                }
                $builder->where('empresa_gestora_id', (int) $parentId);
                $result = $builder->get()->getResultArray();
                break;
            case 'ejecutivo':
                $parentId = (int) $parentId;
                if ($parentId <= 0) {
                    return acl_json_empty(400);
                }

                // Validación de acceso: si no es Super Admin, el cli_directo debe pertenecer a un cliente asignado
                if (!has_permission('bypass_cliente_filter', $perms, $roles)) {
                    $cliRow = $db->table('cli_directo')->select('cliente_id')->where('id', $parentId)->get(1)->getRowArray();
                    $clienteId = (int) ($cliRow['cliente_id'] ?? 0);
                    if ($clienteId <= 0 || !has_access_to_cliente($clienteId, $userId)) {
                        return acl_json_empty(403);
                    }
                }

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
        helper(['permissions', 'acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $canRead = has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles);
        if (!$canRead) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        if (!is_numeric($empresaGestoraId) || (int) $empresaGestoraId <= 0) {
            return acl_json_empty(400);
        }

        try {
            $db2 = $this->_getDbData();
            $gestorModel = new GestorModel($db2);
            $options = $gestorModel->getGestoresOptions((int) $empresaGestoraId);

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
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db = Database::connect();
        $db2 = $this->_getDbData();
        $self = $this;

        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/dashboard', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        if (!(has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }
        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

        // Importante: estas quick actions pueden ser independientes de `editar_tramite`.
        $canQuickAction = has_permission('quick_action_documentos', $perms, $roles);
        $canAdd = $canQuickAction && has_permission('quick_action_documentos_add', $perms, $roles);
        $canEdit = $canQuickAction && has_permission('quick_action_documentos_edit', $perms, $roles);
        $canDelete = $canQuickAction && has_permission('quick_action_documentos_delete', $perms, $roles);

        // En concluidos/cancelados, el bloqueo es por estatus.
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true);
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'El trámite está Concluido/Cancelado y es de solo lectura.']);
            }
            return redirect()->to('/deskapp/concluido/single_documentostatus/' . $tramite_id)->with('error', 'El trámite está Concluido/Cancelado y es de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_documentostatus/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_documentostatus/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_documentostatus/' . $tramite_id, $isApi);
        }

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

        if ($isLocked || !$canAdd) {
            $crud->unsetAdd();
        }
        if ($isLocked || !$canEdit) {
            $crud->unsetEdit();
        }
        if ($isLocked || !$canDelete) {
            $crud->unsetDelete();
        }
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
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = (int) $session->get('id');
            return $stateParameters;
        });    
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = (int) $session->get('id');
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

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self, $crud){
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

            // GroceryCrud solo conserva UN callback por evento; consolidamos Bitácora + ApiLog.
            return logOperation($stateParameters, $crud->getTable());
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

        $hasTipoEvidenciaField = $db->fieldExists('tipo_evidencia', 'tra_evidencias');

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Bitacora', 'Bitacora');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);   

        if ($hasTipoEvidenciaField) {
            $crud->where([
                'tipo_evidencia' => 1,
            ]);
        }

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
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/dashboard', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        if (!(has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }
        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

        // Importante: Bitácora puede ser independiente de `editar_tramite`.
        $canQuickAction = has_permission('quick_action_bitacora', $perms, $roles);
        $canAdd = $canQuickAction && has_permission('quick_action_bitacora_add', $perms, $roles);
        $canEdit = $canQuickAction && has_permission('quick_action_bitacora_edit', $perms, $roles);
        $canDelete = $canQuickAction && has_permission('quick_action_bitacora_delete', $perms, $roles);

        // En concluidos/cancelados, el bloqueo es por estatus.
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true);
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'El trámite está Concluido/Cancelado y es de solo lectura.']);
            }
            return redirect()->to('/deskapp/concluido/single_evidencias/' . $tramite_id)->with('error', 'El trámite está Concluido/Cancelado y es de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_evidencias/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_evidencias/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_evidencias/' . $tramite_id, $isApi);
        }

        // Verificar si se encontró un folio
        if (!$folio_tramite) {
            throw new \Exception('No existe el folio');
        } 

        $db = Database::connect();
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $hasTipoEvidenciaField = $db->fieldExists('tipo_evidencia', 'tra_evidencias');

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Bitacora', 'Bitacora');
        $crud->defaultOrdering('tra_evidencias.created_at', 'desc');

        if ($isLocked || !$canAdd) {
            $crud->unsetAdd();
        }
        if ($isLocked || !$canEdit) {
            $crud->unsetEdit();
        }
        if ($isLocked || !$canDelete) {
            $crud->unsetDelete();
        }
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

        if ($hasTipoEvidenciaField) {
            $crud->where([
                'tipo_evidencia' => 1,
            ]);
        }
        $crud->callbackColumn('comentario', function($value, $row) {
            // Recortar el texto si es muy largo
            $shortened_value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        
            // Retornar el texto con un tooltip para ver el contenido completo
            return '<span title="' . htmlspecialchars($value, ENT_QUOTES) . '">' . $shortened_value . '</span>';
        });
        // $crud->callbackColumn('comentario', function($value, $row) {
        //     return '<div style="white-space: normal;">' . htmlspecialchars($value, ENT_QUOTES) . '</div>';
        // });


        $crud->callbackAfterInsert(function ($stateParameters)  use ($self, $crud) {
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
            // GroceryCrud solo conserva UN callback por evento; consolidamos Bitácora + ApiLog.
            return logOperation($stateParameters, $crud->getTable());
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

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self, $crud){
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

            // GroceryCrud solo conserva UN callback por evento; consolidamos Bitácora + ApiLog.
            return logOperation($stateParameters, $crud->getTable());
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
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = (int) $session->get('id');
            return $stateParameters;
        });    
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $folio_tramite = $session->get('folio_tramite_id');
            $stateParameters->data['folio_tramite'] = $folio_tramite;
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = (int) $session->get('id');
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
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_pago_derechos(){
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/dashboard', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        if (!(has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }

        $tramiteModel = new TramitesModel($db2);
        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

        $canWrite = has_permission('write_tramite_pago_derechos', $perms, $roles);

        $canQuickAction = has_permission('quick_action_pagos_derecho', $perms, $roles);
        $canAdd = $canWrite
            && $canQuickAction
            && has_permission('quick_action_pagos_derecho_add', $perms, $roles)
            && has_permission('can_upload_dropzone_pago_derechos', $perms, $roles);
        $canEdit = $canWrite && $canQuickAction && has_permission('quick_action_pagos_derecho_edit', $perms, $roles);
        $canDelete = $canWrite
            && $canQuickAction
            && has_permission('quick_action_pagos_derecho_delete', $perms, $roles)
            && has_permission('can_upload_dropzone_pago_derechos', $perms, $roles);

        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'El trámite está Concluido/Cancelado y es de solo lectura.']);
            }
            return redirect()->to('/deskapp/concluido/single_pago_derechos/' . $tramite_id)->with('error', 'El trámite está Concluido/Cancelado y es de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_pago_derechos/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_pago_derechos/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_pago_derechos/' . $tramite_id, $isApi);
        }
    
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
            $fileName = trim((string) ($file['file'] ?? ''));
            if ($fileName === '') {
                continue;
            }
            if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                continue;
            }
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
        $crud->unsetExport();
        $crud->unsetPrint();
        // $crud->unsetFilters();
        $crud->unsetClone();
        $crud->setTable('tra_pago_derechos');
        $crud->setSubject('Pago', 'Pagos de Derechos');
        $crud->defaultOrdering('tra_pago_derechos.created_at', 'desc');

        if ($isLocked || !$canAdd) {
            $crud->unsetAdd();
        }
        if ($isLocked || !$canEdit) {
            $crud->unsetEdit();
        }
        if ($isLocked || !$canDelete) {
            $crud->unsetDelete();
        }

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

        $crud->callbackBeforeInsert(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_pago_derechos', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });

        $crud->callbackBeforeUpdate(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_pago_derechos', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);

            $primaryKeyValue = (int) ($stateParameters->primaryKeyValue ?? 0);
            if ($primaryKeyValue > 0) {
                $db = \Config\Database::connect();
                $row = $db->table('tra_pago_derechos')->select('tramite_id')->where('id', $primaryKeyValue)->get()->getRowArray();
                $rowTramiteId = (int) ($row['tramite_id'] ?? 0);
                acl_throw_if_tramite_id_mismatch((int) $rowTramiteId, (int) $tramite_id, 'Acceso denegado.', \RuntimeException::class);
            }

            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });

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

        $crud->callbackBeforeDelete(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_pago_derechos', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);

            $primaryKeyValue = (int) ($stateParameters->primaryKeyValue ?? 0);
            if ($primaryKeyValue <= 0) {
                return $stateParameters;
            }

            $db = \Config\Database::connect();
            $row = $db->table('tra_pago_derechos')->select('file, tramite_id')->where('id', $primaryKeyValue)->get()->getRowArray();
            $rowTramiteId = (int) ($row['tramite_id'] ?? 0);
            acl_throw_if_tramite_id_mismatch((int) $rowTramiteId, (int) $tramite_id, 'Acceso denegado.', \RuntimeException::class);

            // Borrado físico de archivo: solo con permiso de gestión de uploads
            if (!has_permission('can_upload_dropzone_pago_derechos', $perms, $roles)) {
                return $stateParameters;
            }

            $fileName = trim((string) ($row['file'] ?? ''));
            if ($fileName === '') {
                return $stateParameters;
            }
            if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                return $stateParameters;
            }

            $filePath = FCPATH . 'assets/uploads/pago_derechos/' . $tramite_id . '/' . $fileName;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
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
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();
    
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/dashboard', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        $tramiteModel = new TramitesModel($db2);
        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        $canWrite = has_permission('section_pago_gestor', $perms, $roles)
            && has_permission('editar_pago_gestor', $perms, $roles)
            && puede_editar_modulo($roles, $statusId, 'upload_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4);

        $canQuickAction = has_permission('quick_action_pago_gestor', $perms, $roles);
        $canAdd = $canWrite
            && $canQuickAction
            && has_permission('quick_action_pago_gestor_add', $perms, $roles)
            && has_permission('can_upload_dropzone_pago_gestor', $perms, $roles);
        $canEdit = $canWrite && $canQuickAction && has_permission('quick_action_pago_gestor_edit', $perms, $roles);
        $canDelete = $canWrite
            && $canQuickAction
            && has_permission('quick_action_pago_gestor_delete', $perms, $roles)
            && has_permission('can_upload_dropzone_pago_gestor', $perms, $roles);

        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'El trámite está Concluido/Cancelado y es de solo lectura.']);
            }
            return redirect()->to('/deskapp/concluido/single_pago_gestor/' . $tramite_id)->with('error', 'El trámite está Concluido/Cancelado y es de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_pago_gestor/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_pago_gestor/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_pago_gestor/' . $tramite_id, $isApi);
        }
    
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
            $fileName = trim((string) ($file['file'] ?? ''));
            if ($fileName === '') {
                continue;
            }
            if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                continue;
            }
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
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetClone();
        $crud->setTable('tra_pago_gestor');
        $crud->setSubject('Pago', 'Pagos de Gestor');
        $crud->defaultOrdering('tra_pago_gestor.created_at', 'desc');

        if ($isLocked || !$canAdd) {
            $crud->unsetAdd();
        }
        if ($isLocked || !$canEdit) {
            $crud->unsetEdit();
        }
        if ($isLocked || !$canDelete) {
            $crud->unsetDelete();
        }
    
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

        $crud->callbackBeforeInsert(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });

        $crud->callbackBeforeUpdate(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);

            $primaryKeyValue = (int) ($stateParameters->primaryKeyValue ?? 0);
            if ($primaryKeyValue > 0) {
                $db = \Config\Database::connect();
                $row = $db->table('tra_pago_gestor')->select('tramite_id')->where('id', $primaryKeyValue)->get()->getRowArray();
                $rowTramiteId = (int) ($row['tramite_id'] ?? 0);
                acl_throw_if_tramite_id_mismatch((int) $rowTramiteId, (int) $tramite_id, 'Acceso denegado.', \RuntimeException::class);
            }

            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });
    
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

        $crud->callbackBeforeDelete(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);

            $primaryKeyValue = (int) ($stateParameters->primaryKeyValue ?? 0);
            if ($primaryKeyValue <= 0) {
                return $stateParameters;
            }

            $db = \Config\Database::connect();
            $row = $db->table('tra_pago_gestor')->select('file, tramite_id')->where('id', $primaryKeyValue)->get()->getRowArray();
            $rowTramiteId = (int) ($row['tramite_id'] ?? 0);
            acl_throw_if_tramite_id_mismatch((int) $rowTramiteId, (int) $tramite_id, 'Acceso denegado.', \RuntimeException::class);

            // Borrado físico de archivo: solo con permiso de gestión de uploads
            if (!has_permission('can_upload_dropzone_pago_gestor', $perms, $roles)) {
                return $stateParameters;
            }

            $fileName = trim((string) ($row['file'] ?? ''));
            if ($fileName === '') {
                return $stateParameters;
            }
            if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                return $stateParameters;
            }

            $filePath = FCPATH . 'assets/uploads/pago_gestor/' . $tramite_id . '/' . $fileName;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            return $stateParameters;
        });
    
        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_cobro_cliente()
    {
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/dashboard', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        if (!can_access_cobro_cliente_surface($roles, $perms)) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }

        $tramiteModel = new TramitesModel($db2);
        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        $canWrite = can_access_cobro_cliente_surface($roles, $perms)
            && puede_editar_modulo($roles, $statusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5);

        $canQuickAction = has_permission('quick_action_cobros_cliente', $perms, $roles);
        $canAdd = $canWrite
            && $canQuickAction
            && has_permission('quick_action_cobros_cliente_add', $perms, $roles)
            && has_permission('can_upload_dropzone_cobro_cliente', $perms, $roles);
        $canEdit = $canWrite && $canQuickAction && has_permission('quick_action_cobros_cliente_edit', $perms, $roles);
        $canDelete = $canWrite
            && $canQuickAction
            && has_permission('quick_action_cobros_cliente_delete', $perms, $roles)
            && has_permission('can_upload_dropzone_cobro_cliente', $perms, $roles);

        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'El trámite está Concluido/Cancelado y es de solo lectura.']);
            }
            return redirect()->to('/deskapp/concluido/single_cobro_cliente/' . $tramite_id)->with('error', 'El trámite está Concluido/Cancelado y es de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_cobro_cliente/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_cobro_cliente/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_cobro_cliente/' . $tramite_id, $isApi);
        }

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
            $fileName = trim((string) ($file['file'] ?? ''));
            if ($fileName === '') {
                continue;
            }
            if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                continue;
            }
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
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetClone();
        $crud->setTable('tra_cobro_cliente');
        $crud->setSubject('Cobro', 'Cobros a Cliente');
        $crud->defaultOrdering('tra_cobro_cliente.created_at', 'desc');

        if ($isLocked || !$canAdd) {
            $crud->unsetAdd();
        }
        if ($isLocked || !$canEdit) {
            $crud->unsetEdit();
        }
        if ($isLocked || !$canDelete) {
            $crud->unsetDelete();
        }

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

        $crud->callbackBeforeInsert(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_final_costos', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });

        $crud->callbackBeforeUpdate(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_final_costos', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);

            $primaryKeyValue = (int) ($stateParameters->primaryKeyValue ?? 0);
            if ($primaryKeyValue > 0) {
                $db = \Config\Database::connect();
                $row = $db->table('tra_cobro_cliente')->select('tramite_id')->where('id', $primaryKeyValue)->get()->getRowArray();
                $rowTramiteId = (int) ($row['tramite_id'] ?? 0);
                acl_throw_if_tramite_id_mismatch((int) $rowTramiteId, (int) $tramite_id, 'Acceso denegado.', \RuntimeException::class);
            }

            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });

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

        $crud->callbackBeforeDelete(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_permission('section_final_costos', $roles, $perms, 'Acceso denegado.', \RuntimeException::class);

            $primaryKeyValue = (int) ($stateParameters->primaryKeyValue ?? 0);
            if ($primaryKeyValue <= 0) {
                return $stateParameters;
            }

            $db = \Config\Database::connect();
            $row = $db->table('tra_cobro_cliente')->select('file, tramite_id')->where('id', $primaryKeyValue)->get()->getRowArray();
            $rowTramiteId = (int) ($row['tramite_id'] ?? 0);
            acl_throw_if_tramite_id_mismatch((int) $rowTramiteId, (int) $tramite_id, 'Acceso denegado.', \RuntimeException::class);

            // Borrado físico de archivo: solo con permiso de gestión de uploads
            if (!has_permission('can_upload_dropzone_cobro_cliente', $perms, $roles)) {
                return $stateParameters;
            }

            $fileName = trim((string) ($row['file'] ?? ''));
            if ($fileName === '') {
                return $stateParameters;
            }
            if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                return $stateParameters;
            }

            $filePath = FCPATH . 'assets/uploads/cobro_cliente/' . $tramite_id . '/' . $fileName;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
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
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/dashboard', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        if (!(has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }

        $tramiteModel = new TramitesModel($this->_getDbData());
        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        $canWrite = has_permission('section_final_costos', $perms, $roles)
            && puede_editar_modulo($roles, $statusId, 'evidencias_finales_gestor', $reembolsoStatusId, $cobroStatusId, 4);

        $canQuickAction = has_permission('quick_action_evidencias_finales', $perms, $roles);
        $canAdd = $canWrite && $canQuickAction && has_permission('quick_action_evidencias_finales_add', $perms, $roles);
        $canEdit = $canWrite && $canQuickAction && has_permission('quick_action_evidencias_finales_edit', $perms, $roles);
        $canDelete = $canWrite && $canQuickAction && has_permission('quick_action_evidencias_finales_delete', $perms, $roles);

        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'El trámite está Concluido/Cancelado y es de solo lectura.']);
            }
            return redirect()->to('/deskapp/concluido/single_evidencias_finales/' . $tramite_id)->with('error', 'El trámite está Concluido/Cancelado y es de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_evidencias_finales/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_evidencias_finales/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/concluido/single_evidencias_finales/' . $tramite_id, $isApi);
        }
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias_finales');
        $crud->setSubject('Evidencia Final', 'Evidencias Finales');

        if ($isLocked || !$canAdd) {
            $crud->unsetAdd();
        }
        if ($isLocked || !$canEdit) {
            $crud->unsetEdit();
        }
        if ($isLocked || !$canDelete) {
            $crud->unsetDelete();
        }
        $crud->where([
            'tramite_id' => $tramite_id
        ]);   

        $crud->callbackBeforeInsert(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_any_permission(['read_tramite', 'read_final_tramite'], $roles, $perms, 'Acceso denegado.', \RuntimeException::class);
            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });

        $crud->callbackBeforeUpdate(function ($stateParameters) use ($tramite_id) {
            helper(['permissions', 'cliente_filter']);
            $session = session();
            [$roles, $perms] = session_roles_perms($session);
            $userId = (int) ($session->get('id') ?? 0);
            if ($userId <= 0) {
                acl_throw_if_not_logged_in($session, 'Sesión expirada.', \RuntimeException::class);
            }
            acl_throw_if_no_tramite_tenant_access((int) $tramite_id, (int) $userId, $roles, 'Acceso denegado.', \RuntimeException::class);
            acl_throw_if_no_any_permission(['read_tramite', 'read_final_tramite'], $roles, $perms, 'Acceso denegado.', \RuntimeException::class);

            $primaryKeyValue = (int) ($stateParameters->primaryKeyValue ?? 0);
            if ($primaryKeyValue > 0) {
                $db = \Config\Database::connect();
                $row = $db->table('tra_evidencias_finales')->select('tramite_id')->where('id', $primaryKeyValue)->get()->getRowArray();
                $rowTramiteId = (int) ($row['tramite_id'] ?? 0);
                acl_throw_if_tramite_id_mismatch((int) $rowTramiteId, (int) $tramite_id, 'Acceso denegado.', \RuntimeException::class);
            }

            $stateParameters->data['tramite_id'] = $tramite_id;
            $stateParameters->data['user_id'] = $userId;
            return $stateParameters;
        });

        $crud->fields([
            'file', 'tramite_id',
            'costo', 'comentario', 'user_id'
        ]); 

        $crud->columns([
            'id', 'tramite_id','file', 
            'costo', 'created_at'
        ]); 

        // Nota: GroceryCrud solo conserva UN callback por evento; consolidamos Bitácora + ApiLog en uno solo.

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
        $crud->callbackAfterInsert(function ($stateParameters) use ($crud, $self, $tramite_id) {
            if (is_object($stateParameters) && property_exists($stateParameters, 'insertId')) {
                $session = session();
                $myid = (int) ($session->get('id') ?? 0);
                $data = (array) ($stateParameters->data ?? []);

                $bitacoraModel = new BitacoraModel($self->_getDbData());
                $diferencias = $self->encontrarDiferencias($data, []);
                $insert_bitacora = [
                    'id' => null,
                    'tipo' => 'insert',
                    'origen' => 'final',
                    'tramite_id' => (int) $tramite_id,
                    'cambios' => json_encode($diferencias),
                    'user_id' => $myid,
                ];
                $bitacoraModel->insert($insert_bitacora, 'bitacora');
            }

            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });
        $crud->callbackAfterUpdate(function ($stateParameters) use ($crud, $self, $tramite_id) {
            $session = session();
            $myid = (int) ($session->get('id') ?? 0);
            $data = (array) ($stateParameters->data ?? []);

            $bitacoraModel = new BitacoraModel($self->_getDbData());
            $diferencias = $self->encontrarDiferencias($data, []);
            $insert_bitacora = [
                'tipo' => 'update',
                'origen' => 'final',
                'tramite_id' => (int) $tramite_id,
                'cambios' => json_encode($diferencias),
                'user_id' => $myid,
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

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

    public function autorizar(){
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $statusId = (int) $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            if ($tramiteId <= 0 || $statusId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Datos inválidos.']);
            }

            $requiredPermission = null;
            switch ($statusId) {
                case SGL_TRA_STATUS_PAGO_GESTOR:
                    $requiredPermission = 'important_pasar_a_pagos';
                    break;
                case SGL_TRA_STATUS_CONCLUIDO:
                    $requiredPermission = 'important_concluir_tramite';
                    break;
                case SGL_TRA_STATUS_COTIZACION:
                case SGL_TRA_STATUS_RECOLECCION_DCTOS:
                    $requiredPermission = 'important_cancelar_tramite';
                    break;
                default:
                    $requiredPermission = 'editar_tramite';
                    break;
            }

            if (!has_permission($requiredPermission, $perms, $roles)) {
                return acl_deny('Acceso denegado.', 403, null, true);
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            $tramiteRow = $db->table('tramite')->select('tra_status_id')->where('id', $tramiteId)->get(1)->getRowArray();
            $oldStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

            // Actualizar el estatus del trámite
            $builder->where('id', $tramiteId);
            $builder->update(['tra_status_id' => $statusId]);

            // Opcional: Insertar un registro en tra_user_log
            $myid = $userId;
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => $statusId
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            if ($oldStatusId > 0 && $oldStatusId !== $statusId) {
                log_tramite_status_change($tramiteId, $oldStatusId, $statusId);
            }

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function change_status(){
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $statusId = (int) $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            if ($tramiteId <= 0 || $statusId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Datos inválidos.']);
            }

            $requiredPermission = null;
            switch ($statusId) {
                case SGL_TRA_STATUS_PAGO_GESTOR:
                    $requiredPermission = 'important_pasar_a_pagos';
                    break;
                case SGL_TRA_STATUS_CONCLUIDO:
                    $requiredPermission = 'important_concluir_tramite';
                    break;
                case SGL_TRA_STATUS_COTIZACION:
                case SGL_TRA_STATUS_RECOLECCION_DCTOS:
                    $requiredPermission = 'important_cancelar_tramite';
                    break;
                default:
                    $requiredPermission = 'editar_tramite';
                    break;
            }

            if (!has_permission($requiredPermission, $perms, $roles)) {
                return acl_deny('Acceso denegado.', 403, null, true);
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            $tramiteRow = $db->table('tramite')->select('tra_status_id')->where('id', $tramiteId)->get(1)->getRowArray();
            $oldStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

            // Actualizar el estatus del trámite

            $builder->where('id', $tramiteId);
            if ($statusId == SGL_TRA_STATUS_CONCLUIDO) {
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
            $myid = $userId;
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => $statusId
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            if ($oldStatusId > 0 && $oldStatusId !== $statusId) {
                log_tramite_status_change($tramiteId, $oldStatusId, $statusId);
            }

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancelar_tramite(){
        helper(['permissions', 'cliente_filter']);
        helper(['acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        [$roles, $perms] = session_roles_perms($session);

        if ($userId <= 0) {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }
        }

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $motivo = (string) $this->request->getPost('motivo');
        $statusId = (int) $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            if ($tramiteId <= 0 || $statusId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Datos inválidos.']);
            }

            $requiredPermission = null;
            switch ($statusId) {
                case SGL_TRA_STATUS_PAGO_GESTOR:
                    $requiredPermission = 'important_pasar_a_pagos';
                    break;
                case SGL_TRA_STATUS_CONCLUIDO:
                    $requiredPermission = 'important_concluir_tramite';
                    break;
                case SGL_TRA_STATUS_COTIZACION:
                case SGL_TRA_STATUS_RECOLECCION_DCTOS:
                default:
                    $requiredPermission = 'important_cancelar_tramite';
                    break;
            }

            if (!has_permission($requiredPermission, $perms, $roles)) {
                return acl_deny('Acceso denegado.', 403, null, true);
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            $tramiteRow = $db->table('tramite')->select('tra_status_id')->where('id', $tramiteId)->get(1)->getRowArray();
            $oldStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

            // Actualizar el estatus del trámite

            $builder->where('id', $tramiteId);
            $builder->update([
                'tra_status_id' => $statusId,
                'cancelacion_motivo' => $motivo
            ]);
            
            // Opcional: Insertar un registro en tra_user_log
            $myid = $userId;
            $tra_user_log = new TraUserLogModel($db2);
            $logData = [
                'tramite_id' => $tramiteId,
                'user_id' => $myid,
                'tra_status_id' => $statusId
            ];

            $tra_user_log->insert($logData, 'tra_user_log');

            if ($oldStatusId > 0 && $oldStatusId !== $statusId) {
                log_tramite_status_change($tramiteId, $oldStatusId, $statusId);
            }

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
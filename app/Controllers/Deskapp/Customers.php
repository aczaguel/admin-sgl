<?php

/**
 * ============================================================================
 * CONTROLADOR DE VISTA DE CLIENTES - ARQUITECTURA MULTI-TENANCY
 * ============================================================================
 * 
 * Este controlador proporciona vistas de trámites FILTRADAS por cliente.
 * Es utilizado cuando los usuarios son clientes externos que solo deben
 * ver SUS propios trámites.
 * 
 * PROPÓSITO:
 * - Permitir que clientes externos accedan al sistema
 * - Mostrar únicamente trámites relacionados a sus clientes asignados
 * - Implementar filtrado automático basado en cliente_user
 * 
 * SEGURIDAD CRÍTICA:
 * - TODOS los métodos aplican filtrado por cliente_user
 * - Se valida acceso antes de mostrar cualquier dato
 * - Se previene acceso a información de otros clientes
 * 
 * ARQUITECTURA:
 * - Consulta tabla cliente_user para obtener clientes del usuario
 * - Filtra trámites mediante relación cliente -> cli_directo -> tramite
 * - Solo muestra datos de trámites asociados a clientes autorizados
 * 
 * ============================================================================
 */

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
use App\Models\EntidadesModel;

class Customers extends BaseController
{
    public function __construct() {
        // parent::__construct();
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

    private function _example_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $salida->output;
            exit;
        }
        // return view('example.php', (array)$salida);
        return view('/deskapp/extra-pages/grocery_page_cliente.php', (array)$salida);
    }

    /**
     * Lista de trámites filtrados por clientes del usuario
     * 
     * FILTRADO DE SEGURIDAD MULTI-TENANCY:
     * Esta función implementa el filtrado crítico que asegura que los usuarios
     * solo vean trámites de sus clientes asignados.
     * 
     * FLUJO:
     * 1. Obtiene el ID del usuario en sesión
     * 2. Consulta cliente_user para obtener clientes asignados
     * 3. Filtra trámites mediante la cadena: cliente -> cli_directo -> tramite
     * 4. Solo muestra trámites que pertenecen a los clientes autorizados
     * 
     * CONSULTA SQL:
     * WHERE tramite.id IN (
     *     SELECT t.id 
     *     FROM cliente_user cu
     *     JOIN cliente c ON cu.cliente_id = c.id
     *     JOIN cli_directo cd ON cd.cliente_id = c.id
     *     JOIN tramite t ON cd.id = t.cli_directo_id
     *     WHERE cu.user_id = $myid
     * )
     * 
     * PERMISOS:
     * - Solo lectura (unsetAdd, unsetEdit, unsetDelete)
     * - Verifica permisos para export, print y read
     */
    public function list()
    {
        try {
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');

            if (!$myid) {
                return redirect()->to('/deskapp/auth/login');
            }

            $perms = $session->get('user_permissions');
            $roles = $session->get('user_roles');
              if (!has_permission('read_final_tramite', $perms, $roles)) {
                 return redirect()->to('/deskapp/dashboard')->with('error', 'Acceso denegado.');
            }
            
            $crud = $this->_getGroceryCrudEnterprise();
            // $crud->where([
            //     'tra_status_id' => 23                                                                                                                                                                                                                                                                                                                                  
            // ]);

            $crud->unsetAdd();
            $crud->unsetEdit();
            $crud->unsetDelete();
            $crud->unsetDeleteMultiple();
            $crud->unsetClone();
            $crud->setCsrfTokenName(csrf_token());
            $crud->setCsrfTokenValue(csrf_hash());
            $crud->setTable('tramite');
            $crud->setSubject('tramite', 'Tramites');
            
            // ========================================================================
            // FILTRADO CRÍTICO POR CLIENTE - ARQUITECTURA MULTI-TENANCY
            // ========================================================================
            // 
            // Esta condición WHERE es FUNDAMENTAL para la seguridad del sistema.
            // Asegura que el usuario solo pueda ver trámites de sus clientes asignados.
            // 
            // RELACIÓN:
            // user -> cliente_user -> cliente -> cli_directo -> tramite
            // 
            // Si se elimina o modifica este filtro, se rompe la segregación de datos
            // y los usuarios podrían ver información de otros clientes.
            // ========================================================================
            
            $filterSql = get_tramite_filter_sql($myid);
            $crud->where($filterSql);
            
            $crud->columns(['created_at', "started_at", "id", "folio", "contrato", "unidad", "serie", "placas", "tra_tipos_id",'ent_municipio_id', "cli_directo_id", "cli_directo_ejecutivo_id", "empresa_gestora_id", "gestor_id", 
            "fecha_asignacion", "fecha_conclusion", "costo_gestoria", "impuesto_gestoria", "derechos_tramite", "comision_derechos", "costo_total", "numero_factura", "numero_refactura",
            "reembolso_status_id", "tra_status_id", "cobro_status_id", "user_id", "observaciones"]); 
            $crud->displayAs("started_at", "Desde Creación");
            $crud->displayAs('created_at','Creación'); 
            $crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $crud->displayAs("user_id", "Ejecutivo");
            $crud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at); 
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;
            
                // Definir clases CSS según los días
                $claseVerde = 'background-verde';
                $claseAmarillo = 'background-amarillo';
                $claseRojo = 'background-rojo';
                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';  // Clase CSS para gris claro
                $claseAzulClaro = 'background-azul-claro';  // Clase CSS para azul claro
                $claseAzul = 'background-azul';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == 23) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == 21) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == 20) {
                    $clase = $claseAzul;
                } else {
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
                }
                $arrFilter = [20, 21, 23];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

            $crud->fields(["folio", "contrato", "unidad", "serie", "placas", "tra_tipos_id", 
            "cli_directo_id", "cli_directo_ejecutivo_id", "empresa_gestora_id", "gestor_id", "fecha_asignacion", "fecha_conclusion", "costo_gestoria", "impuesto_gestoria", "derechos_tramite",
            "comision_derechos", "costo_total", "numero_factura", "numero_refactura", "reembolso_status_id", "tra_status_id", "cobro_status_id", "observaciones"]);
            $crud->readOnlyFields(["folio", "contrato", "unidad", "serie", "placas", "tra_tipos_id", "cli_directo_id", "cli_directo_ejecutivo_id", "empresa_gestora_id",
            "gestor_id", "fecha_asignacion", "fecha_conclusion", "numero_factura", "numero_refactura", "reembolso_status_id", "tra_status_id", "observaciones"]);
            
            if (!has_permission('export_final_tramite', $perms, $roles)){
                $crud->unsetExport();
            }
            if (!has_permission('print_final_tramite', $perms, $roles)){
                $crud->unsetPrint();
            }
            if (!has_permission('read_final_tramite', $perms, $roles)){
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
                return '/deskapp/customers/tramite/' . $row->id;
            }, false);
            $salida = $crud->render();
            $salida2 = array_merge((array)$salida, $data);
            return $this->_example_output($salida2);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    private function _example_output_2($output = null, $page = 'index') {
        return view('/deskapp/extra-pages/tramite_' . $page . '_view', (array)$output);
    }

    public function tramite($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');

        if (!$myid) {
            return redirect()->to('/deskapp/auth/login');
        }

        $perms = $session->get('user_permissions');
        $roles = $session->get('user_roles');
           if (!has_permission('read_final_tramite', $perms, $roles)) {
             throw new \Exception('Acceso denegado');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();
        // Retrieve the record

        $builder->where(get_tramite_filter_sql($myid), null, false);
        $tramite = $builder->getWhere(['id' => (int) $id])->getRowArray();
        if (!$tramite) {
            throw new \Exception('Trámite no autorizado o no encontrado');
        }



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
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        // $tra_status_steps = $tra_status_obj["steps"];

        $reembolso_status = new ReembolsoStatusModel($db2);
        $reembolso_status_options = $reembolso_status->getReembolsoStatusOptions();

        $cobro_status = new CobroStatusModel($db2);
        $cobro_status_options = $cobro_status->getCobroStatusOptions();


        // $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        $form->fields = [
            "folio" => ["label" => "Folio", "type" => "hidden", "value" => $tramite['folio'], "disabled"=>"disabled"],
            "contrato" => ["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required", "disabled"=>"disabled"],
            "unidad" => ["label" => "Unidad", "type" => "text", "value" => $tramite['unidad'], "disabled"=>"disabled"],
            "serie" => ["label" => "Serie", "type" => "text", "value" => $tramite['serie'], "disabled"=>"disabled"],
            "placas" => ["label" => "Placas", "type" => "text", "value" => $tramite['placas'], "disabled"=>"disabled"],
            "tra_tipos_id" => ["label" => "Tipo de Trámite", "type" => "select", "options" => $tra_tipos_options, "value" => $tramite['tra_tipos_id'], "disabled"=>"disabled"],
            "cli_directo_id" => ["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id'], "disabled"=>"disabled"],
            "cli_directo_ejecutivo_id" => ["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id'], "disabled"=>"disabled"],
            "entidad_id" => ["label" => "Entidad", "type" => "select", "options" => $entidad_options, "value" => $tramite['entidad_id'], "required"=>"required", "disabled"=>"disabled"],
            "ent_municipio_id" => ["label" => "Municipio", "type" => "select", "options" => $ent_municipio_options, "value" => $tramite['ent_municipio_id'], "disabled"=>"disabled"],
            "observaciones" => ["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones'], "disabled"=>"disabled"]
        ];

        $form->gestor_campos = [
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "required" => "required", "disabled"=>"disabled"],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "required" => "required", "disabled"=>"disabled"]
        ];
        
        $form->derechos_campos = [
            "derechos_tramite" => ["label" => "Monto pago de derechos", "type" => "number", "value" => $tramite['derechos_tramite'], "required" => "required", "disabled"=>"disabled"],
            "derechos_pago_sitio" => ["label" => "Pago", "type" => "select", "options" => ["online"=>"En Linea", "ventanilla"=>"En Ventanilla"], "value" => $tramite['derechos_pago_sitio'], "disabled"=>"disabled"],
            "derechos_vigencia" => ["label" => "Fecha Vigencia", "type" => "datetime", "value" => $tramite['derechos_vigencia'], "disabled"=>"disabled"]
        ];
        
        $form->bancario_campos = [
            "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required", "disabled"=>"disabled"],
            "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required", "disabled"=>"disabled"],
        ];

        $form->final_campos = [
            "id_give_cliente" => ["label" => "ID del cliente", "type" => "text", "value" => $tramite['id_give_cliente'], "required" => "required", "disabled"=>"disabled"],
            "numero_factura" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['numero_factura'], "required" => "required", "disabled"=>"disabled"],
            "numero_refactura" => ["label" => "Número de Refactura", "type" => "text", "value" => $tramite['numero_refactura'], "disabled"=>"disabled"],
            "reembolso_status_id" => ["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id'], "disabled"=>"disabled"],
            "cobro_status_id" => ["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id'], "disabled"=>"disabled"],
            "costo_gestoria" => ["label" => "Costo de Gestoría", "type" => "number", "value" => $tramite['costo_gestoria'], "required" => "required", "disabled"=>"disabled"],
            "impuesto_gestoria" => ["label" => "Impuesto de Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria'], "required" => "required", "disabled"=>"disabled"],
            "comision_derechos" => ["label" => "Comisión de Derechos", "type" => "number", "value" => $tramite['comision_derechos'], "required" => "required", "disabled"=>"disabled"],
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "required" => "required", "disabled"=>"disabled"],
            "costos_factura_pdf" => ["label" => "PDF", "type" => "file", "value" => $tramite['costos_factura_pdf'], "disabled"=>"disabled"],
            "costos_factura_xml" => ["label" => "XML", "type" => "file", "value" => $tramite['costos_factura_xml'], "disabled"=>"disabled"],

        ];

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
        $cruddocstatus = $this->_getGroceryCrudEnterprise();
        $cruddocstatus->setApiUrlPath('/deskapp/customers/proceso_documentostatus/'.$id);
        $output = $cruddocstatus->render();
        $form->output_docs = $output->output;
        $crudevidencias = $this->_getGroceryCrudEnterprise();
        $crudevidencias->setApiUrlPath('/deskapp/customers/proceso_evidencias/'.$id);
        $outputevidencias = $crudevidencias->render();

        $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
        $crudevidencias_finales->setApiUrlPath('/deskapp/customers/proceso_evidencias_finales/' . $id);
        $outputevidencias_finales = $crudevidencias_finales->render();
        
        $crud_derechos = $this->_getGroceryCrudEnterprise();
        $crud_derechos->setApiUrlPath('/deskapp/customers/proceso_pago_derechos/' . $id);
        $output_derechos = $crud_derechos->render();
        
        $output->output .= "<hr>".$outputevidencias->output;
        // $form->output_docs = $output->output;
        $form->output_bitacora = $outputevidencias->output;
        $form->outputevidencias_finales = $outputevidencias_finales->output;
        $form->output_derechos = $output_derechos->output;

        $form->output = $output->output;


        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'clientes');
    }

    public function encontrarDiferencias($datos1, $datos2) {
        $diferencias = [];
        foreach ($datos1 as $clave => $valor) {
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
                // No-op (misma semántica que otros controladores)
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function proceso_documentostatus()
    {
        $session = session();
        helper(['permissions', 'acl_guard']);
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db = Database::connect();
        $db2 = $this->_getDbData();
        $self = $this;

        $myid = $session->get('id');
        if (!$myid) {
            return acl_deny('Sesión expirada', 401, null, true);
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('read_final_tramite', $perms, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        if (!$tramite_id || !acl_has_tramite_tenant_access($tramite_id, (int) $myid, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

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
        $crud->unsetEdit();
        $crud->unsetDelete();
        $crud->unsetClone();
        $crud->unsetRead();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetFilters();
        $crud->unsetSettings();
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

    public function proceso_evidencias(){
        $session = session();
        helper(['permissions', 'acl_guard']);
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;

        $myid = $session->get('id');
        if (!$myid) {
            return acl_deny('Sesión expirada', 401, null, true);
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('read_final_tramite', $perms, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        if (!$tramite_id || !acl_has_tramite_tenant_access($tramite_id, (int) $myid, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

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
        $crud->unsetAdd();
        $crud->unsetEdit();
        $crud->unsetDelete();
        $crud->unsetClone();
        $crud->unsetRead();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetFilters();
        $crud->unsetSettings();
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

    public function proceso_pago_derechos(){
        $session = session();
        helper(['permissions', 'acl_guard']);
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;

        $myid = $session->get('id');
        if (!$myid) {
            return acl_deny('Sesión expirada', 401, null, true);
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('read_final_tramite', $perms, $roles) || !has_permission('section_pago_derechos', $perms, $roles)) {
              return acl_deny('Acceso denegado', 403, null, true);
        }

        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        if (!$tramite_id || !acl_has_tramite_tenant_access($tramite_id, (int) $myid, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetRead();
        $crud->unsetAdd();
        $crud->unsetEdit();
        $crud->unsetDelete();
        $crud->unsetClone();
        $crud->unsetRead();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetFilters();
        $crud->unsetSettings();
        // $tramite_crud->setTheme('bootstrap-v5');
        $crud->unsetDeleteMultiple();
        $crud->unsetDelete();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetFilters();
        $crud->unsetClone();
        $crud->setTable('tra_pago_derechos');
        $crud->setSubject('Pago', 'Pagos de Derechos');
        $crud->defaultOrdering('tra_pago_derechos.created_at', 'desc');

        $crud->fields([
            "file", "costo", "comentario", "tramite_id", "user_id"
        ]); 

        $crud->columns([
            "created_at", "file", "costo", "comentario", "user_id"
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

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self){
            $db = Database::connect();
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
                "origen"=>"derechos",
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
            'assets/uploads/pago_derechos/', 
            '/assets/uploads/pago_derechos/', 
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

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function proceso_evidencias_finales(){
        $session = session();
        helper(['permissions', 'acl_guard']);
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $self = $this;

        $myid = $session->get('id');
        if (!$myid) {
            return acl_deny('Sesión expirada', 401, null, true);
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('read_final_tramite', $perms, $roles) || !has_permission('section_final_costos', $perms, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        if (!$tramite_id || !acl_has_tramite_tenant_access($tramite_id, (int) $myid, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());
        $crud->unsetAdd();
        $crud->unsetEdit();
        $crud->unsetDelete();
        $crud->unsetClone();
        $crud->unsetRead();
        $crud->unsetExport();
        $crud->unsetPrint();
        $crud->unsetFilters();
        $crud->unsetSettings();
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
}

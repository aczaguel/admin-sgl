<?php

/**
 * ============================================================================
 * CONTROLADOR DE TRÁMITES - CON VALIDACIÓN MULTI-TENANCY
 * ============================================================================
 * 
 * Este controlador gestiona todos los trámites del sistema.
 * 
 * IMPORTANTE - SEGURIDAD MULTI-TENANCY:
 * Este controlador debe implementar filtrado por cliente_user para usuarios
 * con restricciones de acceso. Los usuarios asignados a clientes específicos
 * solo deben ver trámites de esos clientes.
 * 
 * RECOMENDACIONES:
 * 1. Cargar el helper 'cliente_filter' en el constructor
 * 2. Verificar si el usuario tiene restricción por cliente
 * 3. Aplicar filtro get_cliente_filter_sql() si corresponde
 * 4. Validar acceso en métodos update/view/delete
 * 
 * TODO: Implementar filtrado automático basado en cliente_user
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
use App\Models\UserModel;
use App\Models\PagoDerechosModel;
use App\Models\PagoGestorStatusModel;
use App\Models\TraTramiteAsociadoModel;
use App\Models\TraCobroClienteModel;
use App\Models\TraEvidenciasFinalesModel;

class Tramites extends BaseController
{
    protected function isPendingOrInProcessLabel(?string $label): bool
    {
        $label = trim((string) $label);
        if ($label === '') {
            return false;
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
        return strpos($normalized, 'pendiente') !== false || strpos($normalized, 'proceso') !== false;
    }

    protected function isPaidLabel(?string $label): bool
    {
        $label = trim((string) $label);
        if ($label === '') {
            return false;
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
        return strpos($normalized, 'pagado') !== false;
    }

    protected function isDeliveredDocumentsStatus(?string $status): bool
    {
        $status = trim((string) $status);
        if ($status === '') {
            return false;
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($status, 'UTF-8') : strtolower($status);
        return $normalized === 'entregados';
    }

    protected function canKeepStep4Editable($reembolsoStatusId = null, $pagoGestorStatusId = null, ?array $pagoGestorOptions = null, ?string $statusDoctosGestor = null): bool
    {
        $pagoGestorStatusId = (int) $pagoGestorStatusId;
        $options = $pagoGestorOptions;
        if ($options === null && $pagoGestorStatusId > 0) {
            try {
                $db2 = $this->_getDbData();
                $model = new PagoGestorStatusModel($db2);
                $options = $model->getPagoGestorStatusOptions();
            } catch (\Throwable $e) {
                $options = [];
            }
        }

        $label = isset($options[$pagoGestorStatusId]) ? (string) $options[$pagoGestorStatusId] : '';
        $paymentComplete = $this->isPaidLabel($label);
        $documentsComplete = $this->isDeliveredDocumentsStatus($statusDoctosGestor);

        return !($paymentComplete && $documentsComplete);
    }

    public function __construct() {
        // parent::__construct();
        helper(['form', 'url', 'cliente_filter', 'cliente_context', 'audit', 'notification', 'permissions']);

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

    public function tramite()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            // ========================================================================
            // FILTRADO POR CLIENTE - ARQUITECTURA MULTI-TENANCY
            // ========================================================================
            // 
            // Aplicar filtro por clientes asignados al usuario.
            // - Si el usuario es ADMIN: verá TODOS los trámites (sin filtro)
            // - Si el usuario NO es admin: solo verá trámites de sus clientes asignados
            // 
            // La función get_cliente_filter_sql() automáticamente:
            // 1. Detecta si el usuario es admin (retorna "1 = 1" sin filtro)
            // 2. Obtiene los clientes del usuario desde cliente_user
            // 3. Genera SQL que filtra por esos clientes
            // 
            // IMPORTANTE: Este filtro es CRÍTICO para la seguridad del sistema.
            // No eliminar o modificar sin entender las implicaciones.
            // ========================================================================
            
            $filterSql = get_tramite_filter_sql($myid);
            
            $tramite_crud->where($filterSql);
            
            // Filtro adicional por status
            $tramite_crud->where('tra_status_id NOT IN (' . SGL_TRA_STATUS_CONCLUIDO . ', ' . SGL_TRA_STATUS_CANCELADO . ')');
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', $perms, $roles) || has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    // Mantener flujo original de detalle de Tramites
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
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
            
            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2026-01-01 00:00:00']
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
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
                $claseAzulCobroCliente = 'background-azul-cobro-cliente';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == SGL_TRA_STATUS_PAGO_GESTOR || $row->tra_status_id == SGL_TRA_STATUS_COBRO_CLIENTE) {
                    if ($row->tra_status_id == SGL_TRA_STATUS_PAGO_GESTOR) {
                        $clase = $claseAzulClaro;
                    }
                    $txt_generar_factura = '';

                    // agrega validacion para cobro cliente y para evidencias finales dado el tramite_id, si existe alguno entonces se debe usar otra clase
                     $traCobroClienteModel = new TraCobroClienteModel();
                     $registrosCobroCliente = $traCobroClienteModel->getByTramiteId($row->id);

                     $traEvidenciasFinalesModel = new TraEvidenciasFinalesModel();
                     $registrosEvidenciasFinales = $traEvidenciasFinalesModel->getByTramiteId($row->id);
                     // si alguna de las dos tiene registros entonces txt_generar_factura debe decir "Generar Factura" de lo contrario queda vacio
                    if (count($registrosCobroCliente) > 0 || count($registrosEvidenciasFinales) > 0) {
                        $txt_generar_factura = 'Facturar';
                    }

                    if ($row->tra_status_id == SGL_TRA_STATUS_COBRO_CLIENTE) {
                        $clase = $claseAzulCobroCliente;
                        return '<span class="' . $clase . '">' . $txt_generar_factura . '</span>';
                    }
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CANCELADO) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CONCLUIDO) {
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


                $arrFilter = [SGL_TRA_STATUS_CONCLUIDO, SGL_TRA_STATUS_CANCELADO, SGL_TRA_STATUS_PAGO_GESTOR, SGL_TRA_STATUS_COBRO_CLIENTE];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

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
            // FILTRO DE CONFIDENCIALIDAD: Aplica filtro para mostrar solo clientes asignados
            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
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
    public function tramite_2024()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            // FILTRADO POR CLIENTE (Multi-tenancy)
            $filterSql = get_tramite_filter_sql($myid);

            $tramite_crud->where($filterSql);
            
            //$tramite_crud->where('tra_status_id NOT IN (20, 21)');
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', $perms, $roles) || has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramitesn/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
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
            $tramite_crud->where([
                'tramite.created_at < ?' => ['2025-01-01']
            ]);
            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
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
                if ($row->tra_status_id == SGL_TRA_STATUS_PAGO_GESTOR || $row->tra_status_id == SGL_TRA_STATUS_COBRO_CLIENTE) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CANCELADO) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CONCLUIDO) {
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
                $arrFilter = [SGL_TRA_STATUS_CONCLUIDO, SGL_TRA_STATUS_CANCELADO, SGL_TRA_STATUS_PAGO_GESTOR, SGL_TRA_STATUS_COBRO_CLIENTE];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

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
            // FILTRO DE CONFIDENCIALIDAD: Aplica filtro para mostrar solo clientes asignados
            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
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

    public function tramite_2025()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            // FILTRADO POR CLIENTE (Multi-tenancy)
            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);
            
            //$tramite_crud->where('tra_status_id NOT IN (20, 21)');
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', $perms, $roles) || has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramitesn/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
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
            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2025-01-01 00:00:00'],
                'tramite.created_at < ?' => ['2026-01-01 00:00:00']
                
            ]);
            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
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
                if ($row->tra_status_id == SGL_TRA_STATUS_PAGO_GESTOR || $row->tra_status_id == SGL_TRA_STATUS_COBRO_CLIENTE) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CANCELADO) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CONCLUIDO) {
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
                $arrFilter = [SGL_TRA_STATUS_CONCLUIDO, SGL_TRA_STATUS_CANCELADO, SGL_TRA_STATUS_PAGO_GESTOR, SGL_TRA_STATUS_COBRO_CLIENTE];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

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
            // FILTRO DE CONFIDENCIALIDAD: Aplica filtro para mostrar solo clientes asignados
            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
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

    public function finalizados()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            // FILTRADO POR CLIENTE (Multi-tenancy)
            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', $perms, $roles) || has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
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

            $tramite_crud->where([
                'tramite.finished_at >= ?' => ['2025-01-01'],
                  'tramite.tra_status_id IN (' . SGL_TRA_STATUS_CONCLUIDO . ', ' . SGL_TRA_STATUS_CANCELADO . ')'
            ]);

            
            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
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
                if ($row->tra_status_id == SGL_TRA_STATUS_PAGO_GESTOR || $row->tra_status_id == SGL_TRA_STATUS_COBRO_CLIENTE) {
                    $clase = $claseAzulClaro;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CANCELADO) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == SGL_TRA_STATUS_CONCLUIDO) {
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
                $arrFilter = [SGL_TRA_STATUS_CONCLUIDO, SGL_TRA_STATUS_CANCELADO, SGL_TRA_STATUS_PAGO_GESTOR, SGL_TRA_STATUS_COBRO_CLIENTE];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

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
            // FILTRO DE CONFIDENCIALIDAD: Aplica filtro para mostrar solo clientes asignados
            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
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

    public function tenencias()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            // FILTRADO POR CLIENTE (Multi-tenancy)
            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', $perms, $roles) || has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramitesn/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
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

            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2025-01-01'],
                'tra_tipos_id IN (31, 32, 33)'
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
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
                if ($row->tra_status_id == 23 || $row->tra_status_id == 28) {
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
                $arrFilter = [20, 21, 23, 28];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

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
            // FILTRO DE CONFIDENCIALIDAD: Aplica filtro para mostrar solo clientes asignados
            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
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

    public function cotizaciones()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            
            // FILTRADO POR CLIENTE (Multi-tenancy)
            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Editar', 'fas fa-pencil-alt', function ($row) {
                    return '/deskapp/tramites/update_cotizacion/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
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

            $tramite_crud->where([
                 'tramite.tra_status_id IN (29)'
            ]);
            
            $tramite_crud->columns([
                'id', 'created_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

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
            // FILTRO DE CONFIDENCIALIDAD: Aplica filtro para mostrar solo clientes asignados
            $clienteRelationFilter = get_cliente_relation_filter($myid);
            if ($clienteRelationFilter !== null) {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramite_crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
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

    protected function _example_output($salida = null) {
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

    // Function to handle adding a new product
    public function add() {
        helper(['permissions']);

        $session = session();
        $roles = $session->get('user_roles') ?? [];
        $perms = $session->get('user_permissions') ?? [];
        $canCreate = has_permission('create_tramite', $perms, $roles);
        if (!$canCreate) {
            return redirect()->to('/deskapp/tramitesn/tramite')->with('error', '⛔ No tienes permisos para crear trámites.');
        }

        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db2 = $this->_getDbData();

        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        // $entMunicipios = new EntMunicipioModel($db2);
        // $ent_municipio_options = $entMunicipios->getEntMunicipios();

        $entidades = new EntidadesModel($db2);
        $entidad_options = $entidades->getEntidades();

        $clienteDirecto = new ClienteDirectoModel($db2);
        // Multi-tenancy: filtra clientes directos por asignación en cliente_user (si no es admin)
        $clienteRelationFilter = get_cliente_relation_filter($myid);
        $cli_directo_options = $clienteDirecto->getClientesDirectosOptions($clienteRelationFilter);
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
            "entidad_id" => ["label" => "Entidad", "type" => "select", "options" => $entidad_options, "required"=>"required"],
            "observaciones" => ["label" => "Observaciones", "type" => "textarea"],
            "user_id" => ["label" => "User Id", "type" => "hidden", "value" => "$myid"]
        ];
        $output->gestor_campos = [];
        $output->derechos_campos = [];
        $output->bancario_campos = [];
        $output->final_campos = [];

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();

        $output->css_files = $crudOutput->css_files;
        $output->js_files = $crudOutput->js_files;
        // Load the add form view
        $output = array_merge((array)$output, $data);

        return $this->_example_output_2($output, 'add');
    }

    public function getEjecutivosByClienteId($clienteDirectoId) {
        helper(['cliente_filter', 'acl_guard']);

        $session = session();
        $myid = (int) $session->get('id');

        $clienteDirectoId = (int) $clienteDirectoId;
        if ($clienteDirectoId <= 0) {
            return $this->response->setJSON([]);
        }

        // Validación de acceso: si no es admin, el cli_directo debe pertenecer a un cliente asignado
        if (!user_has_global_cliente_access($myid)) {
            $clienteIds = get_user_cliente_ids($myid);
            if (empty($clienteIds)) {
                log_unauthorized_access_attempt('cli_directo', $clienteDirectoId, $myid);
                return acl_json_empty(403);
            }
            $db = \Config\Database::connect();
            $row = $db->table('cli_directo')->select('cliente_id')->where('id', $clienteDirectoId)->get(1)->getRowArray();
            $tenantId = $row['cliente_id'] ?? null;
            if (!$tenantId || !in_array((int) $tenantId, array_map('intval', $clienteIds), true)) {
                log_unauthorized_access_attempt('cli_directo', $clienteDirectoId, $myid);
                return acl_json_empty(403);
            }
        }

        $db2 = $this->_getDbData();
        $ejecutivoModel = new ClienteDirectoEjecutivoModel($db2);
        $options = $ejecutivoModel->getEjecutivosOptions($clienteDirectoId);
        
        return $this->response->setJSON($options);
    }

    // Function to handle inserting a new product into the database
    public function insert() {
        $validation = \Config\Services::validation();

        $session = session();
        $myid = (int) $session->get('id');

        helper(['permissions', 'acl_guard']);
        $roles = $session->get('user_roles') ?? [];
        $perms = $session->get('user_permissions') ?? [];
        $canCreate = has_permission('create_tramite', $perms, $roles);
        if (!$canCreate) {
            if ($this->request->isAJAX()) {
                return acl_deny('Acceso denegado.', 403, null, true);
            }
            return redirect()->to('/deskapp/tramitesn/tramite')->with('error', '⛔ No tienes permisos para crear trámites.');
        }

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

                // Forzar user_id desde sesión para evitar manipulación del POST
                $data['user_id'] = $myid;

                // Validación multi-tenant del cliente directo
                $cliDirectoId = (int) ($data['cli_directo_id'] ?? 0);
                if ($cliDirectoId <= 0) {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'errors' => ['cli_directo_id' => 'Cliente es requerido.']]);
                    }
                    return $this->add();
                }

                if (!user_has_global_cliente_access($myid)) {
                    $clienteIds = get_user_cliente_ids($myid);
                    $row = $db->table('cli_directo')->select('cliente_id')->where('id', $cliDirectoId)->get(1)->getRowArray();
                    $tenantId = $row['cliente_id'] ?? null;
                    if (empty($clienteIds) || !$tenantId || !in_array((int) $tenantId, array_map('intval', $clienteIds), true)) {
                        log_unauthorized_access_attempt('cli_directo', $cliDirectoId, $myid);
                        if ($this->request->isAJAX()) {
                            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'errors' => ['cli_directo_id' => 'Acceso denegado al cliente seleccionado.']]);
                        }
                        return redirect()->back()->with('error', '⛔ Acceso denegado al cliente seleccionado.');
                    }
                }
                
                $button_action = $this->request->getPost('accion');
                $tra_tipos_id = $data["tra_tipos_id"];
                $forceConfirm = (int) ($this->request->getPost('force_duplicate_confirm') ?? 0) === 1;

                // Valida duplicados de tipo y serie dentro de la ventana del ultimo ano.
                // Si force_duplicate_confirm=1, salta esta validación (usuario ya confirmó)
                if (!$forceConfirm) {
                    $builder = $db->table('tramite');
                    $builder->where('tra_tipos_id', $tra_tipos_id);
                    $builder->where('serie', $data['serie']);
                    $builder->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 year')));
                    $query = $builder->get();   

                    $resultados = $query->getResultArray();
                    $existen = !empty($resultados);
                    
                    if ($existen) {
                        helper('datetime_es');

                        $data_existete = $resultados[0];
                        $id_existente = $data_existete['id'];
                        $user_id_existente = $data_existete['user_id'];
                        $tra_tipos_id_existente = $data_existete['tra_tipos_id'];
                        $created_at_existente = format_datetime_es($data_existete['created_at'], true, 'N/A');
                        $contratoExistente = $data_existete['contrato'] ?? '';
                        $serieExistente = $data_existete['serie'];

                        // Obtener el Nombre del usuario que creó el trámite existente
                        $userModel = new UserModel($db);
                        $user_id_existente = (int)$user_id_existente;

                        if ($user_id_existente <= 0) {
                            $user_id_existente = 1;
                        }

                        $nombreUsuarioExistente = $userModel->getFullNameById($user_id_existente);      
                        // Obtener el tipo de trámite existente
                        $traTiposModel = new TraTiposModel($this->_getDbData());
                        $tipoTramiteExistente = $traTiposModel->getTipoTramiteById($tra_tipos_id_existente);

                        $mensajeError = [];
                        $mensajeError['contrato_existente'] = $contratoExistente;
                        $mensajeError['serie_existente'] = $serieExistente;
                        $mensajeError['tipo_tramite_existente'] = $tipoTramiteExistente;
                        $mensajeError['nombre_usuario_existente'] = $nombreUsuarioExistente;
                        $mensajeError['created_at_existente'] = $created_at_existente;
                        $mensajeError['id_existente'] = $id_existente;

                        if ($this->request->isAJAX()) {
                            // Devolver respuesta especial para mostrar modal de confirmación
                            return $this->response->setJSON([
                                'success' => false,
                                'confirmable' => true,
                                'message' => $mensajeError
                            ]);
                        } else {
                            return redirect()->back()->withInput()->with('error', $mensajeError);
                        }
                    }
                }
                // Si no existe, continúa con la inserción
                $builder = $db->table('tramite');
                
                $clienteModel = new ClienteModel($this->_getDbData());
                $newFolio = $clienteModel->getPrefijoConUltimosSeisDigitos($data["cli_directo_id"]);
                $data["folio"] = $newFolio;
                
                if($button_action == 'quotation'){
                    $data["tra_status_id"] = 29;
                    $data["quoted_at"] = date('Y-m-d H:i:s');
                } else { // tramite
                    $data["tra_status_id"] = 11;
                }


                // Espacio para guardar la relación DosStatus
                unset($data["accion"]);
                unset($data['force_duplicate_confirm']);
                $builder->insert($data);

                $lastInsertID = $db->insertID();

                # Insertar relación en tra_tramite_asociado
                $traTramiteAsociadoModel = new TraTramiteAsociadoModel();
                $insert_tramite_asociado = [
                "tramite_id" => (int)$lastInsertID,
                "tra_tipos_id" => (int)$tra_tipos_id,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
                ];
                $traTramiteAsociadoModel->insert($insert_tramite_asociado);

                $tra_tipos_id = $data["tra_tipos_id"];

                
                // Espacio para guardar la relación DosStatus 

                // $db = Database::connect();
                $db2 = $this->_getDbData();
                $condition = ['tra_tipos_id' => $tra_tipos_id];
                $queryBuilder = $db->table('tra_tipo_documentos')->where($condition);
                if (in_array('es_obligatorio', $db->getFieldNames('tra_tipo_documentos'), true)) {
                    $queryBuilder->where('es_obligatorio', 1);
                }
                $query = $queryBuilder->get();
                $resultados = $query->getResultArray();

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

                    $result = $traDocStatusModel->insert($insert_data, 'tra_doc_status');
                }

                $bitacoraModel = new BitacoraModel($db2);
                $data_bitacora = $data;
                $diferencias = $this->encontrarDiferencias([], $data_bitacora);
                if ($forceConfirm) {
                    $diferencias['confirmacion_modal_duplicado'] = [
                        'valor_original' => '',
                        'valor_nuevo' => 'Si'
                    ];
                }
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

                // AUDITORÍA: Registrar creación del trámite
                $auditDescription = $forceConfirm
                    ? "Trámite creado con folio {$newFolio} tras confirmar duplicado en modal"
                    : "Trámite creado con folio {$newFolio}";

                log_tramite_change(
                    $lastInsertID,
                    'insert',
                    'tramite',
                    $auditDescription,
                    null,
                    null,
                    null,
                    [
                        'folio' => $newFolio,
                        'tipo_tramite_id' => $tra_tipos_id,
                        'contrato' => $data['contrato'] ?? null,
                        'serie' => $data['serie'] ?? null,
                        'confirmacion_modal_duplicado' => $forceConfirm
                    ]
                );

                // NOTIFICACIÓN: Enviar notificación de trámite creado
                notify_tramite_creado($lastInsertID, $newFolio, $myid);

                // Si la solicitud es AJAX, devuelve una respuesta JSON indicando éxito
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'from' => 'insert',
                        'success' => true,
                        'redirect' => '/deskapp/tramitesn/update/'.$lastInsertID
                    ]);
                } else {
                    // Si no es una solicitud AJAX, redirige a la página de lista
                    return redirect()->to('/deskapp/tramites/update/'.$lastInsertID);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error en Tramites::insert: ' . $e->getMessage());
                log_message('error', 'Trace Tramites::insert: ' . $e->getTraceAsString());
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
        // Alias/redirect del flujo legacy al flujo nuevo.
        // Esto asegura que cualquier link viejo a /tramites/update/{id} termine usando el wizard de tramitesn.
        if (strtolower($this->request->getMethod()) === 'get') {
            return redirect()->to('/deskapp/tramitesn/update/' . (int) $id);
        }

        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $id = (int) $id;
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        // Permiso de edición: requerido para cualquier modificación en POST.
        if (!can_edit_tramite($roles, $perms)) {
            if ($this->request->isAJAX()) {
                return acl_deny('Acceso denegado.', 403, null, true);
            }
            return redirect()->back()->with('error', '⛔ No tienes permiso para editar trámites.');
        }

        // ========================================================================
        // VALIDACIÓN DE ACCESO - MULTI-TENANCY
        // ========================================================================
        // Verificar que el usuario tenga acceso a este trámite
        // - Si es admin: siempre tiene acceso
        // - Si no es admin: solo si el trámite pertenece a sus clientes
        // ========================================================================
        
        if ($resp = acl_require_tramite_tenant_access($id, $userId, $roles, '⛔ No tienes permiso para editar este trámite', '/deskapp/tramites/tramite', 403, false)) {
            log_unauthorized_access_attempt('tramite', $id);
            return $resp;
        }

        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $db2 = $this->_getDbData();


        // 🔹 1️⃣ Verificar si el trámite tiene relación en `tra_tramite_asociado`
        $tramiteAsociadoModel = new TraTramiteAsociadoModel();
        $asociadoExists = $tramiteAsociadoModel->where('tramite_id', $id)->countAllResults();

        if ($asociadoExists == 0) {
            // 🔹 2️⃣ Obtener `tra_tipos_id` del trámite
            $tramite = $builder->getWhere(['id' => $id])->getRowArray();

            if (!empty($tramite['tra_tipos_id'])) {
                // 🔹 3️⃣ Crear la relación en `tra_tramite_asociado`
                $tramiteAsociadoModel->saveService($id, $tramite['tra_tipos_id']);
            }
        }

        // Retrieve the record
        $tramite = $builder->getWhere(['id' => $id])->getRowArray();
        if (!$tramite) {
            return redirect()->to('/deskapp/tramites/tramite')
                ->with('error', 'No se encontró el trámite solicitado');
        }

        // Sumatoria de pagos de derechos (tra_pago_derechos.costo) -> costo_gestoria
        // Esto mantiene el campo siempre cargado y consistente con los pagos capturados.
        try {
            $sumRow = $db->table('tra_pago_derechos')
                ->selectSum('costo', 'total')
                ->where('tramite_id', (int)$id)
                ->get()
                ->getRowArray();
            $sumDerechos = (float)($sumRow['total'] ?? 0);
        } catch (\Exception $e) {
            $sumDerechos = 0;
        }
        $tramite['costo_gestoria'] = number_format($sumDerechos, 2, '.', '');

        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        
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

        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        $form->fields = [
            "folio" => array_merge(["label" => "Folio", "type" => "hidden", "value" => $tramite['folio']]),
            "contrato" => array_merge(["label" => "Contrato", "type" => "text", "value" => $tramite['contrato'], "required" => "required"]),
            "unidad" => array_merge(["label" => "Unidad", "type" => "text", "value" => $tramite['unidad']]),
            "serie" => array_merge(["label" => "Serie", "type" => "text", "value" => $tramite['serie']]),
            "placas" => array_merge(["label" => "Placas", "type" => "text", "value" => $tramite['placas']]),
            "cli_directo_id" => array_merge(["label" => "Cliente", "type" => "select", "options" => $cli_directo_options, "value" => $tramite['cli_directo_id']]),
            "cli_directo_ejecutivo_id" => array_merge(["label" => "Ejecutivo de Cliente", "type" => "select", "options" => [], "value" => $tramite['cli_directo_ejecutivo_id']]),
            "entidad_id" => array_merge(["label" => "Entidad", "type" => "select", "options" => $entidad_options, "value" => $tramite['entidad_id'], "required"=>"required"]),
            "observaciones" => array_merge(["label" => "Observaciones", "type" => "textarea", "value" => $tramite['observaciones']])
        ];
        

        $form->gestor_campos = [
            "empresa_gestora_id" => ["label" => "Empresa Gestora", "type" => "select", "options" => $empresa_gestora_options, "value" => $tramite['empresa_gestora_id'], "required" => "required"],
            "gestor_id" => ["label" => "Gestor", "type" => "select", "options" => [], "value" => $tramite['gestor_id'], "required" => "required"]
        ];

        
        $form->derechos_campos = [
            "derechos_tramite" => ["label" => "Monto pago de derechos", "type" => "number", "value" => $tramite['derechos_tramite'], "required" => "required"],
            "derechos_pago_sitio" => ["label" => "Pago", "type" => "select", "options" => ["online"=>"En Linea", "ventanilla"=>"En Ventanilla"], "value" => $tramite['derechos_pago_sitio']],
            "derechos_vigencia" => ["label" => "Fecha Vigencia", "type" => "datetime", "value" => $tramite['derechos_vigencia']],
            "separador_1" => ["type" => "hr"],
            "separador_2" => ["type" => "hr"],
            "titulo_seccion" => ["type" => "divider", "id" => "seccion_datos_generales"],
            "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required"],
            "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required"],
        ];
        
        $form->bancario_campos = [
            // "derechos_revol_cliente" => ["label" => "Forma de Pago", "type" => "select", "options" => ["revolvente"=>"Fondo Revolvente", "cliente"=>"Pago Cliente"], "value" => $tramite['derechos_revol_cliente'], "required" => "required"],
            // "derechos_refer_banc" => ["label" => "Referencia Bancaria", "type" => "text", "value" => $tramite['derechos_refer_banc'], "required" => "required"],
        ];

        $gestor_model = new GestorModel($db2);
        $gestor_nombre = $gestor_model->getGestorNameById($tramite['gestor_id']);

        // echo "<pre>";
        // print_r($tramite);
        // echo "</pre>";die();
        // Preparar los campos de pago del gestor
        if(isset($tramite['costo_tramite']) && $tramite['costo_tramite'] > 0){
            $tramite['costo_tramite'] = number_format($tramite['costo_tramite'], 2, '.', '');
        } else {
            $tramite['costo_tramite'] = 0;
        }


        $form->pago_gestor = [
            // "gestor_total_pago_hidden" => ["label" => "Pago Total", "type" => "hidden", "value" => $tramite['gestor_total_pago']],
            // "gestoria_comision_hidden" => ["label" => "", "type" => "hidden", "value" => $tramite['gestoria_comision']],
            "gestor_id" => ["label" => "", "type" => "hidden", "value" => $tramite['gestor_id']],
            "gestor_name" => ["label" => "Gestor", "type" => "text", "value" => $gestor_nombre, "disabled"=>"disabled"],
            "costo_tramite" => ["label" => "Costos de los Trámites", "type" => "number", "value" => $tramite['costo_tramite'], "disabled" => "disabled"],
            "deposito_gestor" => ["label" => "Deposito a Gestor", "type" => "number", "value" => $tramite['deposito_gestor'], "required" => "required"],
            "col_a_favor" => ["label" => "Saldo a Favor SGL", "type" => "number", "value" => $tramite['col_a_favor'], "required" => "required"], 
            "col_a_favor_gestor" => ["label" => "Saldo a Favor del Gestor", "type" => "number", "value" => $tramite['col_a_favor_gestor'], "required" => "required"],
            
            "num_factura_gestor" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['num_factura_gestor']],    
            
            // "reembolso_status_id_hidden" => ["label" => "Estatus del Reembolso", "type" => "hidden", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']],
            "separador_gestor" => ["type" => "hr"],

            "impuesto_gestoria" => ["label" => "Honorarios de Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria'], "required" => "required"],
            // "impuesto_gestoria_hidden" => ["label" => "", "type" => "hidden", "value" => $tramite['impuesto_gestoria']],

            "gestoria_comision" => ["label" => "Gratificación", "type" => "number", "value" => $tramite['gestoria_comision']],
            "costo_paqueteria" => ["label" => "Costo de Paquetería", "type" => "number", "value" => $tramite['costo_paqueteria']],
            "gestor_total_pago" => ["label" => "Gasto Total", "type" => "number", "value" => $tramite['gestor_total_pago'], "disabled"=>"disabled"],
            "reembolso_status_id" => ["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']],
        ];

        $form->final_campos = [
            "id_give_cliente" => ["label" => "ID que da el cliente", "type" => "text", "value" => $tramite['id_give_cliente'], "required" => "required"],
            "separador_costos" => ["type" => "hr"],
            "numero_factura" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['numero_factura'], "required" => "required"],
            "numero_refactura" => ["label" => "Número de Refactura", "type" => "text", "value" => $tramite['numero_refactura']],
            "cobro_status_id" => ["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id']],
            "separador_costos2" => ["type" => "hr"],
            "costo_gestoria" => ["label" => "Sumatoria de Derechos", "type" => "number", "value" => $tramite['costo_gestoria'], "disabled"=>"disabled"],
            "costo_gestoria_hidden" => ["label" => "Sumatoria de Derechos", "type" => "hidden", "value" => $tramite['costo_gestoria']],
            "costo_pago_cliente"=> ["label" => "Honorarios del Trámite", "type" => "number", "value" => $tramite['costo_pago_cliente'], "required" => "required"],
            "comision_derechos" => ["label" => "Comisión de Derechos", "type" => "number", "value" => $tramite['comision_derechos'], "required" => "required"],
            "iva" => ["label" => "IVA ($)", "type" => "number", "value" => $tramite['iva']],
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "disabled"=>"disabled"],
        ];
        
        $data['id'] = $id;
        $data['folio'] = $tramite[ 'folio'];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']];
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];

        $data['step'] = $tra_status_steps[$tramite['tra_status_id']];
        $data['started_at'] = $tramite['started_at'];
        $data['derechos_comprobante'] = $tramite['derechos_comprobante'];
        $data['reembolso_status_id'] = $tramite['reembolso_status_id'];
        $data['cobro_status_id'] = $tramite['cobro_status_id'];
		$data['sumatoria_derechos'] = $sumDerechos;
        
        // Obtener nombres para el header
        $data['tipo_tramite'] = isset($tra_tipos_options[$tramite['tra_tipos_id']]) ? $tra_tipos_options[$tramite['tra_tipos_id']] : 'N/A';
        $data['cliente'] = isset($cli_directo_options[$tramite['cli_directo_id']]) ? $cli_directo_options[$tramite['cli_directo_id']] : 'N/A';
        $data['gestor'] = $gestor_nombre ?? 'Sin asignar';
        $data['empresa_gestora'] = isset($empresa_gestora_options[$tramite['empresa_gestora_id']]) ? $empresa_gestora_options[$tramite['empresa_gestora_id']] : 'Sin asignar';
        $data['has_pending_pago_conciliation'] = $this->hasPendingPagoConciliation($id);

        $form->id = $id;

        $crud = $this->_getGroceryCrudEnterprise();
        $crudOutput = $crud->render();
        
        $form->css_files = $crudOutput->css_files;
        $form->js_files = $crudOutput->js_files;
        
        // Load the view with the fields and current data
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output_docs = $cruddocstatus->render();            
            
            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();

            $crud_derechos = $this->_getGroceryCrudEnterprise();
            $crud_derechos->setApiUrlPath('/deskapp/tramites/single_pago_derechos/' . $id);
            $output_derechos = $crud_derechos->render();

            $crud_pago_gestor = $this->_getGroceryCrudEnterprise();
            if(puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_gestor', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])){
                $crud_pago_gestor->setApiUrlPath('/deskapp/tramites/single_pago_gestor/' . $id);
            } else {
                $crud_pago_gestor->setApiUrlPath('/deskapp/concluido/single_pago_gestor/' . $id);
            
            }
            $output_pago_gestor = $crud_pago_gestor->render();

            $crud_cobro_cliente = $this->_getGroceryCrudEnterprise();
            if(puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_cliente', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])){
                $crud_cobro_cliente->setApiUrlPath('/deskapp/tramites/single_cobro_cliente/' . $id);
            } else {
                $crud_cobro_cliente->setApiUrlPath('/deskapp/concluido/single_cobro_cliente/' . $id);
            }
            $output_cobro_cliente = $crud_cobro_cliente->render();

            $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
            if(puede_editar_modulo($session->get('user_roles'), $tramite['tra_status_id'], 'evidencias_finales_gestor', $tramite['reembolso_status_id'], $tramite['cobro_status_id'], $tramite['tra_status_id'])){
                $crudevidencias_finales->setApiUrlPath('/deskapp/tramites/single_evidencias_finales/' . $id);
            } else {
                $crudevidencias_finales->setApiUrlPath('/deskapp/concluido/single_evidencias_finales/' . $id);
            }
            $outputevidencias_finales = $crudevidencias_finales->render();
            
            $form->output_docs = $output_docs->output;
            $form->output_bitacora = $outputevidencias->output;
            $form->outputevidencias_finales = $outputevidencias_finales->output;
            $form->output_derechos = $output_derechos->output;
            $form->output_pago_gestor = $output_pago_gestor->output;
            $form->output_cobro_cliente = $output_cobro_cliente->output;
            
            // $form->output = $output_docs->output;
        // }

        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'update');
    }

    protected function hasPendingPagoConciliation(int $tramiteId): bool
    {
        if ($tramiteId <= 0) {
            return false;
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('cobranza_expediente') || !$db->tableExists('cobranza_pago')) {
            return false;
        }

        $expediente = $db->table('cobranza_expediente')
            ->select('id')
            ->where('tramite_id', $tramiteId)
            ->where('is_active', 1)
            ->get(1)
            ->getRowArray();

        $expedienteId = (int) ($expediente['id'] ?? 0);
        if ($expedienteId <= 0) {
            return false;
        }

        $rows = $db->table('cobranza_pago')
            ->select('status_code')
            ->where('expediente_id', $expedienteId)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            if (($row['status_code'] ?? '') !== 'confirmado') {
                return true;
            }
        }

        return false;
    }

    public function update_cotizacion($id) {
        helper(['permissions']);
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

        [$roles, $perms] = session_roles_perms($session);
        $puede_modificar = ["disabled"=>"disabled"];
        if (has_permission('editar_tramite', $perms, $roles)) {
            $puede_modificar = [];
        }
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

        $gestor_model = new GestorModel($db2);
        $gestor_nombre = $gestor_model->getGestorNameById($tramite['gestor_id']);

        $form->pago_gestor = [
            // nombre del gestor
            "gestor_id" => ["label" => "Gestor", "type" => "text", "value" => $gestor_nombre, "disabled"=>"disabled"],
            "costo_tramite" => ["label" => "Costo del Trámite", "type" => "number", "value" => $tramite['costo_tramite']],
            "deposito_gestor" => ["label" => "Deposito a Gestor", "type" => "number", "value" => $tramite['deposito_gestor']],
            "col_a_favor" => ["label" => "Saldo Pendiente", "type" => "number", "value" => $tramite['col_a_favor']], 
            "num_factura_gestor" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['num_factura_gestor']],    
            "pago_gestor_st_id" => ["label" => "Estatus del Pago", "type" => "select", "options" => $pago_gestor_st_opciones, "value" => $tramite['pago_gestor_st_id']],
            "impuesto_gestoria" => ["label" => "Honorarios de Gestoría", "type" => "number", "value" => $tramite['impuesto_gestoria']],
            "gestoria_comision" => ["label" => "Gratificación", "type" => "number", "value" => $tramite['gestoria_comision']],
            "gestor_total_pago" => ["label" => "Pago Total", "type" => "number", "value" => $tramite['gestor_total_pago']],
            "reembolso_status_id" => ["label" => "Estatus del Reembolso", "type" => "select", "options" => $reembolso_status_options, "value" => $tramite['reembolso_status_id']]
        ];

        $form->final_campos = [
            "id_give_cliente" => ["label" => "ID del cliente", "type" => "text", "value" => $tramite['id_give_cliente'], "required" => "required"],
            "numero_factura" => ["label" => "Número de Factura", "type" => "text", "value" => $tramite['numero_factura'], "required" => "required"],
            "numero_refactura" => ["label" => "Número de Refactura", "type" => "text", "value" => $tramite['numero_refactura']],
            "cobro_status_id" => ["label" => "Estatus del Cobro", "type" => "select", "options" => $cobro_status_options, "value" => $tramite['cobro_status_id']],
            "costo_gestoria" => ["label" => "Costo de Gestoría", "type" => "number", "value" => $tramite['costo_gestoria'], "required" => "required"],
            "costo_pago_cliente"=> ["label" => "Honorarios del Trámite", "type" => "number", "value" => $tramite['costo_pago_cliente'], "required" => "required"],
            "comision_derechos" => ["label" => "Comisión de Derechos", "type" => "number", "value" => $tramite['comision_derechos'], "required" => "required"],
            "costo_total" => ["label" => "Costo Total", "type" => "number", "value" => $tramite['costo_total'], "disabled"=>"disabled"],
        ];
        
        $data['id'] = $id;
        $data['folio'] = $tramite[      'folio'];
        $data['tra_tipo'] = $tra_tipos_options[$tramite['tra_tipos_id']];
        $data['tra_status'] = $tra_status_options[$tramite['tra_status_id']];
        $data['tra_status_id'] = $tramite['tra_status_id'];
        $data['created_at'] = $tramite['created_at'];

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
        
        // Load the view with the fields and current data
            $cruddocstatus = $this->_getGroceryCrudEnterprise();
            $cruddocstatus->setApiUrlPath('/deskapp/tramites/single_documentostatus/'.$id);
            $output_docs = $cruddocstatus->render();            
            
            $crudevidencias = $this->_getGroceryCrudEnterprise();
            $crudevidencias->setApiUrlPath('/deskapp/tramites/single_evidencias/'.$id);
            $outputevidencias = $crudevidencias->render();

            $crudevidencias_finales = $this->_getGroceryCrudEnterprise();
            $crudevidencias_finales->setApiUrlPath('/deskapp/tramites/single_evidencias_finales/' . $id);
            $outputevidencias_finales = $crudevidencias_finales->render();

            $crud_derechos = $this->_getGroceryCrudEnterprise();
            $crud_derechos->setApiUrlPath('/deskapp/tramites/single_pago_derechos/' . $id);
            $output_derechos = $crud_derechos->render();

            $crud_pago_gestor = $this->_getGroceryCrudEnterprise();
            $crud_pago_gestor->setApiUrlPath('/deskapp/tramites/single_pago_gestor/' . $id);
            $output_pago_gestor = $crud_pago_gestor->render();

            $crud_cobro_cliente = $this->_getGroceryCrudEnterprise();
            $crud_cobro_cliente->setApiUrlPath('/deskapp/tramites/single_cobro_cliente/' . $id);
            $output_cobro_cliente = $crud_cobro_cliente->render();

            // $output_docs->output .= "<hr>".$outputevidencias->output;
            // $form->output_docs = $output->output;
            
            $form->output_docs = $output_docs->output;
            $form->output_bitacora = $outputevidencias->output;
            $form->outputevidencias_finales = $outputevidencias_finales->output;
            $form->output_derechos = $output_derechos->output;
            $form->output_pago_gestor = $output_pago_gestor->output;
            $form->output_cobro_cliente = $output_cobro_cliente->output;
            
            // $form->output = $output_docs->output;
        // }

        $form = array_merge((array)$form, $data);
        return $this->_example_output_2($form, 'cotizacion');
    }

    public function getPagoGestorFiles($id)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

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

        // Ruta de la carpeta donde se almacenan los archivos
        $storeFolderSpecific = 'assets/uploads/cobro_cliente/' . $id . $ds;

        // Consulta a la base de datos
        $cobro_cliente_db = $db->table('tra_cobro_cliente')
                    ->select('id, file, cobro_correcto')
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
                $obj['cobro_correcto'] = $dbFile->cobro_correcto ?? null;
                $result[] = $obj;
            }
        }

        // Devolver los resultados en formato JSON
        return $this->response->setJSON($result);
    }




    public function update_solicitud($id) {
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        [$roles, $perms] = session_roles_perms($session);
        $canEdit = can_edit_tramite($roles, $perms) && can_write_tramite_step(1, $perms, $roles);
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
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        // $tra_status_steps = $tra_status_obj["steps"];

        $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (!$canEdit){
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

        if (!has_permission('tramite_view_gestor', $perms, $roles)){
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
        if ($canEdit){
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
        [$roles, $perms] = session_roles_perms($session);
        $canEdit = can_edit_tramite($roles, $perms) && can_write_tramite_step(1, $perms, $roles);
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
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        // $tra_status_steps = $tra_status_obj["steps"];

        $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (!$canEdit){
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

        if (!has_permission('tramite_view_gestor', $perms, $roles)){
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
        if ($canEdit){
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
        [$roles, $perms] = session_roles_perms($session);
        $canEdit = can_edit_tramite($roles, $perms) && can_write_tramite_step(1, $perms, $roles);
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
        $tra_status_obj = $traStatus->getTraStatusOptions();
        $tra_status_options = $tra_status_obj["tra_status"];
        // $tra_status_steps = $tra_status_obj["steps"];
        $cobroStatuses = new CobroStatusesModel($db2);
        // $cobro_status_options = $cobroStatuses->getCobroStatusesOptions();
        $form = new \stdClass();
        
        // Fields to be displayed in the add form

        if (!$canEdit){
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

        if (!has_permission('tramite_view_gestor', $perms, $roles)){
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
        if ($canEdit){
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

    public function delete_comprobante()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $request = \Config\Services::request();
        $tramiteId = (int) $request->getPost('tramite_id');
        $fileName = $request->getPost('file');

        // Validar ID del trámite
        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID de trámite inválido.']);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $tramiteStatusRow = $db->table('tramite')
            ->select('tra_status_id, reembolso_status_id, cobro_status_id, pago_gestor_st_id, status_doctos_gestor')
            ->where('id', (int) $tramiteId)
            ->get(1)
            ->getRowArray();
        $traStatusId = (int) ($tramiteStatusRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteStatusRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteStatusRow['cobro_status_id'] ?? 0);
        $canOverrideStatus28 = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideStatus28)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.']);
        }

        if ($resp = acl_require_permission('section_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('can_upload_dropzone_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        // Validar nombre del archivo
        $fileName = trim((string) $fileName);
        if ($fileName === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre del archivo no proporcionado.']);
        }
        if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre de archivo inválido.']);
        }

        try {
            // Verificar que el registro existe en la base de datos
            $db = \Config\Database::connect();
            $builder = $db->table('tra_pago_derechos');
            $existingRecord = $builder->where('tramite_id', $tramiteId)
                                      ->where('file', $fileName)
                                      ->get()->getRowArray();
            
            if (!$existingRecord) {
                return $this->response->setJSON(['success' => false, 'message' => 'El registro no existe en la base de datos.']);
            }

            // Ruta del archivo
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'assets/uploads/pago_derechos/' . $tramiteId;
            $filePath = FCPATH . $storeFolder . $ds . $fileName;

            // Eliminar archivo físico
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor.']);
                }
            }

            // Eliminar registro de la base de datos con WHERE obligatorio
            $builder->where('tramite_id', $tramiteId);
            $builder->where('file', $fileName);

            if (!$builder->delete()) {
                throw new \Exception('No se pudo eliminar el registro de la base de datos.');
            }

            $db2 = $this->_getDbData();
            $bitacoraModel = new BitacoraModel($db2);
            $data_bitacora = $existingRecord;
            $diferencias = $this->encontrarDiferencias([], $data_bitacora);
            $insert_bitacora = [
                'id' => null,
                'tipo' => 'delete',
                'origen' => 'tramite',
                'tramite_id' => (int) $tramiteId,
                'cambios' => json_encode($diferencias),
                'user_id' => (int) $userId
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            $this->updateCobrarClienteFlag($db, (int) $tramiteId);

            return $this->response->setJSON(['success' => true, 'message' => 'Archivo eliminado correctamente.']);

        } catch (\Exception $e) {
            log_message('error', 'Error en delete_comprobante: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al eliminar.']);
        }
    }

    public function upload_comprobante()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramiteId = (int) $uri->getSegment(4);

        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')
            ->select('tra_status_id, reembolso_status_id, cobro_status_id, pago_gestor_st_id, status_doctos_gestor')
            ->where('id', (int) $tramiteId)
            ->get(1)
            ->getRowArray();
        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado']);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('section_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);
        $pagoGestorStatusId = (int) ($tramiteRow['pago_gestor_st_id'] ?? 0);
        $pagoGestorStatusId = (int) ($tramiteRow['pago_gestor_st_id'] ?? 0);

        // Permiso fino Dropzone: sin el permiso, el upload queda en solo-lectura (defensa en profundidad)
        if ($resp = acl_require_permission('can_upload_dropzone_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $canOverrideStatus28 = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideStatus28)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.']);
        }
        if (!puede_editar_modulo($roles, $traStatusId, 'step3_upload', $reembolsoStatusId, $cobroStatusId, 3)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $ds = DIRECTORY_SEPARATOR; 
        $storeFolder = 'assets/uploads/pago_derechos/' . $tramiteId; // Carpeta destino para los archivos
        $targetPath = FCPATH . $storeFolder . $ds;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true); // Crear carpeta si no existe
        }

        if (!empty($_FILES['file'])) {
            // Subir archivo
            $tempFile = $_FILES['file']['tmp_name'];
            $originalName = (string) ($_FILES['file']['name'] ?? '');
            $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $baseName);
            $safeBase = trim((string) $safeBase, '_');
            if ($safeBase === '') {
                $safeBase = 'archivo';
            }
            try {
                $random = bin2hex(random_bytes(8));
            } catch (\Exception $e) {
                $random = uniqid();
            }
            $fileName = $safeBase . '_' . $random . ($extension !== '' ? '.' . $extension : '');
            $targetFile = $targetPath . $fileName;

            if (move_uploaded_file($tempFile, $targetFile)) {
                // Guardar el registro en la tabla tra_pago_derechos
                $db = \Config\Database::connect();
                $builder = $db->table('tra_pago_derechos');
                $data = [
                    'tramite_id' => $tramiteId,
                    'file' => $fileName,
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'status' => 1
                ];
                $builder->insert($data);
                $filePath = $ds . $storeFolder . $ds . $fileName;
                return $this->response->setJSON(['success' => true, 'message' => 'Archivo subido y registro creado correctamente', 'filePath'=>$filePath]);
            } else {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo']);
            }
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo']);
    }

    public function delete_pago_gestor()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $request = \Config\Services::request();
        $tramiteId = (int) $request->getPost('tramite_id');
        $fileName = $request->getPost('file');

        // Validar ID del trámite
        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID de trámite inválido.']);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $tramiteStatusRow = $db->table('tramite')
            ->select('tra_status_id, reembolso_status_id, cobro_status_id, pago_gestor_st_id')
            ->where('id', (int) $tramiteId)
            ->get(1)
            ->getRowArray();
        $traStatusId = (int) ($tramiteStatusRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteStatusRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteStatusRow['cobro_status_id'] ?? 0);
        $pagoGestorStatusId = (int) ($tramiteStatusRow['pago_gestor_st_id'] ?? 0);
        $statusDoctosGestor = (string) ($tramiteStatusRow['status_doctos_gestor'] ?? '');
        $canKeepStep4Editable = $this->canKeepStep4Editable($reembolsoStatusId, $pagoGestorStatusId, null, $statusDoctosGestor);
        $canOverrideStatus28 = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideStatus28 && !$canKeepStep4Editable)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.']);
        }

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        $existingComprobanteFinal = (string) ($existingRecord['comprobante_final'] ?? '');
        if (in_array($existingComprobanteFinal, ['factura_gestor', 'comprobante_pago'], true)) {
            if ($resp = acl_require_permission('can_upload_dropzone_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }
        } else {
            if ($resp = acl_require_permission('can_upload_dropzone_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }
        }
        if (!$canKeepStep4Editable && !puede_editar_modulo($roles, $traStatusId, 'can_upload_dropzone_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        // Validar nombre del archivo
        $fileName = trim((string) $fileName);
        if ($fileName === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre del archivo no proporcionado.']);
        }
        if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre de archivo inválido.']);
        }

        try {
            // Verificar que el registro existe
            $db = \Config\Database::connect();
            $builder = $db->table('tra_pago_gestor');
            $existingRecord = $builder->where('tramite_id', $tramiteId)
                                      ->where('file', $fileName)
                                      ->get()->getRowArray();
            
            if (!$existingRecord) {
                return $this->response->setJSON(['success' => false, 'message' => 'El registro no existe en la base de datos.']);
            }

            // Ruta del archivo
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'assets/uploads/pago_gestor/' . $tramiteId;
            $filePath = FCPATH . $storeFolder . $ds . $fileName;

            // Eliminar archivo físico
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor.']);
                }
            }

            // Eliminar registro de la base de datos con WHERE obligatorio
            $builder->where('tramite_id', $tramiteId);
            $builder->where('file', $fileName);

            if (!$builder->delete()) {
                throw new \Exception('No se pudo eliminar el registro de la base de datos.');
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Archivo eliminado correctamente.']);

        } catch (\Exception $e) {
            log_message('error', 'Error en delete_pago_gestor: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al eliminar.']);
        }
    }

    public function upload_pago_gestor()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramiteId = (int) $uri->getSegment(4);

        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')
            ->select('tra_status_id, reembolso_status_id, cobro_status_id, pago_gestor_st_id')
            ->where('id', (int) $tramiteId)
            ->get(1)
            ->getRowArray();
        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado']);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);
        $pagoGestorStatusId = (int) ($tramiteRow['pago_gestor_st_id'] ?? 0);

        $comprobanteFinal = (string) $request->getPost('comprobante_final');
        $allowedComprobanteFinal = [
            'tramite_recibido',
            'acuse_recibo_cliente',
            'factura_gestor',
            'comprobante_pago',
            'otro',
        ];
        if (!in_array($comprobanteFinal, $allowedComprobanteFinal, true)) {
            $comprobanteFinal = null;
        }

        // Permiso fino Dropzone segun el tipo de archivo que se sube.
        if (in_array($comprobanteFinal, ['factura_gestor', 'comprobante_pago'], true)) {
            if ($resp = acl_require_permission('can_upload_dropzone_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }
        } else {
            if ($resp = acl_require_permission('can_upload_dropzone_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }
        }

        $canKeepStep4Editable = $this->canKeepStep4Editable(
            $reembolsoStatusId,
            $pagoGestorStatusId,
            null,
            (string) ($tramiteRow['status_doctos_gestor'] ?? '')
        );
        $canOverrideStatus28 = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideStatus28 && !$canKeepStep4Editable)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.']);
        }
        if (!$canKeepStep4Editable && !puede_editar_modulo($roles, $traStatusId, 'upload_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/pago_gestor/' . $tramiteId; // Carpeta destino para los archivos
        $targetPath = FCPATH . $storeFolder . $ds;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true); // Crear carpeta si no existe
        }

        if (!empty($_FILES['file'])) {
            // Subir archivo
            $tempFile = $_FILES['file']['tmp_name'];
            $originalName = (string) ($_FILES['file']['name'] ?? '');
            $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $baseName);
            $safeBase = trim((string) $safeBase, '_');
            if ($safeBase === '') {
                $safeBase = 'archivo';
            }
            try {
                $random = bin2hex(random_bytes(8));
            } catch (\Exception $e) {
                $random = uniqid();
            }
            $fileName = $safeBase . '_' . $random . ($extension !== '' ? '.' . $extension : '');
            $targetFile = $targetPath . $fileName;

            if (move_uploaded_file($tempFile, $targetFile)) {
                // Guardar el registro en la tabla tra_pago_gestor
                $db = \Config\Database::connect();
                $builder = $db->table('tra_pago_gestor'); // Cambiado a la tabla 'tra_pago_gestor'
                $data = [
                    'tramite_id' => $tramiteId,
                    'file' => $fileName,
                    'user_id' => $userId,
                    'comprobante_final' => $comprobanteFinal,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'status' => 1
                ];
                $builder->insert($data);

                $this->updateCobrarClienteFlag($db, (int) $tramiteId);

                $filePath = base_url('/assets/uploads/pago_gestor/' . $tramiteId . '/' . $fileName);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Archivo subido y registro creado correctamente',
                    'filePath' => $filePath,
                    'fileName' => $fileName,
                    'originalName' => $originalName,
                    'comprobanteFinal' => $comprobanteFinal,
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo']);
            }
        }

        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo']);
    }

    public function delete_cobro_cliente()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $request = \Config\Services::request();
        $tramiteId = (int) $request->getPost('tramite_id');
        $fileName = $request->getPost('file');

        // Validar ID del trámite
        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID de trámite inválido.']);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        // En Cobros Cliente (Paso 5) el acceso es 100% por permisos.

        // Concluido/Cancelado siempre es solo lectura
        $db = \Config\Database::connect();
        $tramiteStatusRow = $db->table('tramite')
            ->select('tra_status_id, reembolso_status_id, cobro_status_id')
            ->where('id', (int) $tramiteId)
            ->get(1)
            ->getRowArray();
        $traStatusId = (int) ($tramiteStatusRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteStatusRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteStatusRow['cobro_status_id'] ?? 0);
        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.']);
        }

        if (!can_upload_cobro_cliente_surface($roles, $perms)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        if (!puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        // Validar nombre del archivo
        $fileName = trim((string) $fileName);
        if ($fileName === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre del archivo no proporcionado.']);
        }
        if ($fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nombre de archivo inválido.']);
        }

        try {
            // Verificar que el registro existe
            $db = \Config\Database::connect();
            $builder = $db->table('tra_cobro_cliente');
            $existingRecord = $builder->where('tramite_id', $tramiteId)
                                      ->where('file', $fileName)
                                      ->get()->getRowArray();
            
            if (!$existingRecord) {
                return $this->response->setJSON(['success' => false, 'message' => 'El registro no existe en la base de datos.']);
            }

            // Ruta del archivo
            $ds = DIRECTORY_SEPARATOR;
            $storeFolder = 'assets/uploads/cobro_cliente/' . $tramiteId;
            $filePath = FCPATH . $storeFolder . $ds . $fileName;

            // Eliminar archivo físico
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'No se pudo eliminar el archivo del servidor.']);
                }
            }

            // Eliminar registro de la base de datos con WHERE obligatorio
            $builder->where('tramite_id', $tramiteId);
            $builder->where('file', $fileName);

            if (!$builder->delete()) {
                throw new \Exception('No se pudo eliminar el registro de la base de datos.');
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Archivo eliminado correctamente.']);

        } catch (\Exception $e) {
            log_message('error', 'Error en delete_cobro_cliente: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al eliminar.']);
        }
    }

    public function upload_cobro_cliente()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $request = \Config\Services::request();
    
        $uri = $request->getUri();
        $tramiteId = (int) $uri->getSegment(4);
    
        if ($tramiteId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID de trámite no proporcionado']);
        }

        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')
            ->select('tra_status_id, reembolso_status_id, cobro_status_id')
            ->where('id', (int) $tramiteId)
            ->get(1)
            ->getRowArray();
        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado']);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if (!can_upload_cobro_cliente_surface($roles, $perms)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }
        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);
        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está en modo de solo lectura.']);
        }
        if (!puede_editar_modulo($roles, $traStatusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }
    
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = 'assets/uploads/cobro_cliente/' . $tramiteId; // Carpeta destino para los archivos
        $targetPath = FCPATH . $storeFolder . $ds;
    
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true); // Crear carpeta si no existe
        }
    
        if (!empty($_FILES['file'])) {
            // Subir archivo
            $tempFile = $_FILES['file']['tmp_name'];
            $originalName = (string) ($_FILES['file']['name'] ?? '');
            $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
            $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $baseName);
            $safeBase = trim((string) $safeBase, '_');
            if ($safeBase === '') {
                $safeBase = 'archivo';
            }
            try {
                $random = bin2hex(random_bytes(8));
            } catch (\Exception $e) {
                $random = uniqid();
            }
            $fileName = $safeBase . '_' . $random . ($extension !== '' ? '.' . $extension : '');
            $targetFile = $targetPath . $fileName;
    
            if ($this->moveCobroClienteUploadedFile($tempFile, $targetFile)) {
                // Guardar el registro en la tabla tra_cobro_cliente
                $db = \Config\Database::connect();
                $builder = $db->table('tra_cobro_cliente'); // Cambiado a la tabla 'tra_cobro_cliente'
                $cobroCorrecto = $request->getPost('cobro_correcto');
                $cobroCorrecto = is_string($cobroCorrecto) ? trim($cobroCorrecto) : '';
                if (!in_array($cobroCorrecto, ['parcial', 'completo', 'otro'], true)) {
                    $cobroCorrecto = 'otro';
                }
                $data = [
                    'tramite_id' => $tramiteId,
                    'file' => $fileName,
                    'cobro_correcto' => $cobroCorrecto,
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'status' => 1
                ];
                $builder->insert($data);
    
                return $this->response->setJSON(['success' => true, 'message' => 'Archivo subido y registro creado correctamente']);
            } else {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo mover el archivo']);
            }
        }
    
        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo']);
    }

        protected function moveCobroClienteUploadedFile(string $tempFile, string $targetFile): bool
        {
            return move_uploaded_file($tempFile, $targetFile);
        }
    

    public function getDependentData($type, $parentId) {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return acl_json_empty(401);
        }
        [$roles, $perms] = session_roles_perms($session);

        if (!(has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_json_empty(403);
        }

        $parentId = (int) $parentId;
        if ($parentId <= 0) {
            return acl_json_empty(400);
        }

        $db = \Config\Database::connect();
        $result = [];
        switch ($type) {
            case 'gestor':
                $builder = $db->table('ges_gestor');
                $builder->where('empresa_gestora_id', $parentId);
                $result = $builder->get()->getResultArray();
                break;
            case 'ejecutivo':
                $cliDirecto = $db->table('cli_directo')
                    ->select('cliente_id')
                    ->where('id', $parentId)
                    ->get(1)
                    ->getRowArray();
                $clienteId = (int) ($cliDirecto['cliente_id'] ?? 0);
                if ($clienteId <= 0) {
                    return acl_json_empty(404);
                }
                if (!has_permission('bypass_cliente_filter', $perms, $roles) && !has_access_to_cliente($clienteId, $userId)) {
                    return acl_json_empty(403);
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
    
    public function update_save() {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        [$roles, $perms] = session_roles_perms($session);

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_datos_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        // Validación de ID ya cubierta por guard

        $validation = \Config\Services::validation();
        $validation->setRules([
            "folio" => "required",
            "contrato" => "required"
        ]);
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors(),
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            
            // Verificar que el trámite existe
            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON(['success' => false, 'message' => 'El trámite no existe.', 'csrfHash' => csrf_hash()]);
            }
            if (in_array((int) ($existingTramite['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            // Actualizar datos
            $data = $this->request->getPost();
            $csrfName = csrf_token();
            if (isset($data[$csrfName])) {
                unset($data[$csrfName]);
            }
            $data["user_id"] = $myid;
            
            // AUDITORÍA: Comparar datos antes de actualizar
            $changes = compare_tramite_data($existingTramite, $data);
            
            // Log temporal para debug - guardar en archivo separado
            $logFile = WRITEPATH . 'logs/audit_debug.log';
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'tramite_id' => $id,
                'user_id' => $myid,
                'post_fields' => array_keys($data),
                'existing_fields' => array_keys($existingTramite),
                'changes_detected' => count($changes),
                'changes' => $changes
            ];
            file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
            
            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el trámite.');
            }

            // Propagar derechos_* a asociados (excluye tipo principal)
            $principalTipoId = (int) ($existingTramite['tra_tipos_id'] ?? 0);
            $asociadoData = [];
            $asociadoFields = [
                'derechos_tramite',
                'derechos_pago_sitio',
                'derechos_vigencia',
                'derechos_revol_cliente',
                'derechos_refer_banc',
            ];
            foreach ($asociadoFields as $field) {
                if (array_key_exists($field, $data)) {
                    $asociadoData[$field] = $data[$field];
                }
            }
            if (!empty($asociadoData)) {
                $asociadoData['updated_at'] = date('Y-m-d H:i:s');
                $asociadoBuilder = $db->table('tra_tramite_asociado');
                $asociadoBuilder->where('tramite_id', (int) $id);
                if ($principalTipoId > 0) {
                    $asociadoBuilder->where('tra_tipos_id !=', $principalTipoId);
                }
                $asociadoBuilder->update($asociadoData);
            }

            $folio = $data["folio"];
            $db2 = $this->_getDbData();

            // Bitácora
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = $this->encontrarDiferencias($data, []);
            $insert_bitacora = [
                "id" => null,
                "tipo" => "update",
                "origen" => "tramite",
                "folio_tramite" => $folio,
                "tramite_id" => (int)$id,
                "cambios" => json_encode($diferencias),
                "user_id" => (int)$myid
            ];
            $bitacoraModel->insert($insert_bitacora, 'bitacora');

            // Log de usuario
            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id" => (int)$id,
                "user_id" => (int)$myid,
                "tra_status_id" => 11
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // AUDITORÍA: Registrar cambios detectados
            if (!empty($changes)) {
                $changeCount = log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Datos Generales',
                    'form_step' => 1,
                    'form_section' => 'update_save'
                ]);
                log_message('info', "[Tramites::update_save] Registrados {$changeCount} cambios para trámite ID: {$id}");
                
                // NOTIFICACIÓN: Enviar notificación de trámite actualizado
                $cambiosTexto = implode(', ', array_keys($changes));
                notify_tramite_actualizado($id, $folio ?? "Trámite #{$id}", $cambiosTexto, $myid);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id,
                'csrfHash' => csrf_hash(),
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en update_save: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage(), 'csrfHash' => csrf_hash()]);
        }
    }

    public function update_gestor_save() {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        [$roles, $perms] = session_roles_perms($session);

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_asigna_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        // Validación de ID ya cubierta por guard

        $validation = \Config\Services::validation();
        $validation->setRules([
            "empresa_gestora_id" => "required",
            "gestor_id" => "required"
        ]);

        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            
            // Verificar que el trámite existe
            $tramite_base = $builder->where('id', $id)->get()->getRowArray();
            if (!$tramite_base) {
                return $this->response->setJSON(['success' => false, 'message' => 'El trámite no existe.']);
            }
            if (in_array((int) ($tramite_base['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                ]);
            }

            $this->updateTramiteStatus($id, 25);

            // Preparar datos
            $data = $this->request->getPost();
            
            if (empty($tramite_base['started_at'])) {
                $data["started_at"] = date('Y-m-d H:i:s');
            }

            if (isset($data['gestor_name'])) {
                unset($data['gestor_name']);
            }

            // AUDITORÍA: Comparar datos antes de actualizar
            $changes = compare_tramite_data($tramite_base, $data);

            // Actualizar con WHERE obligatorio
            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo asignar el gestor.');
            }

            $db2 = $this->_getDbData();

            // Bitácora
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

            // Log de usuario
            $tra_user_log = new TraUserLogModel($db2);
            $log = [
                "tramite_id" => (int)$id,
                "user_id" => (int)$myid,
                "tra_status_id" => 22
            ];
            $tra_user_log->insert($log, 'tra_user_log');

            // AUDITORÍA: Registrar cambios detectados
            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Asignación de Gestor',
                    'form_step' => 2,
                    'form_section' => 'update_gestor_save'
                ]);
                
                // NOTIFICACIÓN: Enviar notificación de gestor asignado
                if (isset($changes['gestor_id'])) {
                    // Obtener folio y nombre del gestor
                    $db = \Config\Database::connect();
                    $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                    $gestor = $db->table('ges_gestor')->select('nombre')->where('id', $data['gestor_id'])->get()->getRowArray();
                    
                    $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                    $gestorNombre = $gestor['nombre'] ?? 'Gestor';
                    
                    notify_gestor_asignado($id, $folio, $gestorNombre, $myid);
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'El Gestor se asignó correctamente.',
                'redirect' => '/deskapp/tramites/update/' . $id
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en update_gestor_save: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error al asignar gestor: ' . $e->getMessage()]);
        }
    }

    public function update_gestor_costos() {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        // Validar ID
        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        // Mutación: requiere permiso de edición del trámite.
        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
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
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }
    
        $data = $this->request->getPost();
    
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');

            // Verificar que el trámite existe
            $existingTramite = $builder->select('id, tra_status_id')->where('id', $id)->get(1)->getRowArray();
            if (empty($existingTramite)) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'El trámite no existe.']);
            }
            if ($resp = $this->denyReadonlyStep4Mutation($id, $roles, $perms)) {
                return $resp;
            }
    
            // Actualizar con WHERE obligatorio
            $builder->where('id', $id);
            $updateResult = $builder->update([
                'costo_tramite' => $data['costo_tramite'],
                'deposito_gestor' => $data['deposito_gestor'],
                'reembolso_status_id' => $data['reembolso_status_id']
            ]);

            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar los costos.');
            }
    
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
                "tra_status_id" => (int) ($existingTramite['tra_status_id'] ?? 0)
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

    public function update_derechos_save() {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);

        [$roles, $perms] = session_roles_perms($session);

        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        // Validación de ID ya cubierta por guard

        $validation = \Config\Services::validation();
        $validation->setRules([
            "derechos_tramite" => "required",
            "derechos_pago_sitio" => "required",
            "derechos_vigencia" => "required"
        ]);

        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            
            // Verificar que el trámite existe
            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON(['success' => false, 'message' => 'El trámite no existe.']);
            }
            if (in_array((int) ($existingTramite['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                ]);
            }

            $this->updateTramiteStatus($id, 26);

            $data = $this->request->getPost();
            
            // AUDITORÍA: Comparar datos antes de actualizar
            $changes = compare_tramite_data($existingTramite, $data);
            
            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo guardar los derechos.');
            }

            $db2 = $this->_getDbData();
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

            // AUDITORÍA: Registrar cambios detectados
            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Pago de Derechos',
                    'form_step' => 3,
                    'form_section' => 'update_derechos_save'
                ]);
                
                // NOTIFICACIÓN: Enviar notificación de trámite actualizado
                $db = \Config\Database::connect();
                $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                
                notify_tramite_actualizado($id, $folio, 'Pago de Derechos actualizado', $myid);
            }

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en update_derechos_save: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar derechos: ' . $e->getMessage()]);
        }
    }

    public function update_bancario_save() {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $myid = (int) ($session->get('id') ?? 0);
        $id = (int) ($this->request->uri->getSegment(4) ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        // Validar ID
        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        if ($resp = acl_require_tramite_tenant_access($id, $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('write_tramite_pago_derechos', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            "derechos_revol_cliente" => "required",
            "derechos_refer_banc" => "required"
        ]);

        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            
            // Verificar que el trámite existe
            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON(['success' => false, 'message' => 'El trámite no existe.']);
            }
            if (in_array((int) ($existingTramite['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                ]);
            }

            $this->updateTramiteStatus($id, 27);

            $data = $this->request->getPost();
            
            // AUDITORÍA: Comparar datos antes de actualizar
            $changes = compare_tramite_data($existingTramite, $data);
            
            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo guardar los datos bancarios.');
            }

            $db2 = $this->_getDbData();
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

            // AUDITORÍA: Registrar cambios detectados
            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Datos Bancarios',
                    'form_step' => 3,
                    'form_section' => 'update_bancario_save'
                ]);
                
                // NOTIFICACIÓN: Enviar notificación de trámite actualizado
                $db = \Config\Database::connect();
                $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                
                notify_tramite_actualizado($id, $folio, 'Datos Bancarios actualizados', $myid);
            }

            // Return success message as JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.',
                'redirect' => '/deskapp/tramites/update/'.$id
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en update_bancario_save: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar datos bancarios: ' . $e->getMessage()]);
        }
    }

    public function update_pago_gestor() {
        helper(['permissions', 'cliente_filter', 'acl_guard']);
        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $myid = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);


        $id = (int) ($this->request->uri->getSegment(4) ?? 0);
        $validation = \Config\Services::validation();   
        $db2 = $this->_getDbData();
    
        // Validar que el ID del trámite sea válido
        if ($id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, null, true);
        }

        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_tramite_tenant_access((int) $id, (int) $myid, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
    
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        $builder->where('id', $id);
        $existingData = $builder->get()->getRowArray();
        
        // Verificar que el trámite existe
        if (!$existingData) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El trámite no existe.'
            ]);
        }

        $traStatusId = (int) ($existingData['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($existingData['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($existingData['cobro_status_id'] ?? 0);

        $canOverrideStatus28 = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($traStatusId === 28 && !$canOverrideStatus28)) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'El trámite está en modo de solo lectura.'
            ]);
        }

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }
        if (!puede_editar_modulo($roles, $traStatusId, 'editar_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }
    
        // Reglas de validación - solo campos que realmente son obligatorios
        $validation->setRules([
            "reembolso_status_id" => "required|integer",
        ], [
            "reembolso_status_id" => [
                "required" => "El estatus del reembolso es obligatorio.", 
                "integer" => "Debe ser un número entero válido."
            ]
        ]);
    
        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en la validación de datos.',
                'errors' => $validation->getErrors()
            ]);
        }
        
        // Obtener datos del formulario
        $data = $this->request->getPost();
        $data["user_id"] = $myid;
        // Remover CSRF del payload para evitar guardar en la tabla
        foreach (array_keys($data) as $key) {
            if (strpos($key, 'csrf') === 0) {
                unset($data[$key]);
            }
        }
        
        // AUDITORÍA: Comparar datos antes de actualizar
        $changes = compare_tramite_data($existingData, $data);
        
        // Limpiar campos que no deben guardarse
        $camposAEliminar = [
            'gestor_total_pago_hidden', 
            'reembolso_status_id_hidden', 
            'impuesto_gestoria_hidden', 
            'gestoria_comision_hidden',
            'gestor_name',
            'gestor_id' // Este campo es readonly, no debe actualizarse
        ];
        
        foreach ($camposAEliminar as $campo) {
            if (isset($data[$campo])) {
                unset($data[$campo]);
            }
        }
        
        // Convertir campos numéricos vacíos a NULL
        $camposNumericos = [
            'costo_tramite', 
            'deposito_gestor', 
            'col_a_favor', 
            'impuesto_gestoria', 
            'gestoria_comision', 
            'gestor_total_pago'
        ];
        
        foreach ($camposNumericos as $campo) {
            if (isset($data[$campo]) && $data[$campo] === '') {
                $data[$campo] = null;
            }
        }
        
        try {
            // Actualizar en la base de datos
            $builder->where('id', $id);
            $updateResult = $builder->update($data);
            
            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el trámite.');
            }

            // Actualizar estatus del trámite a 28 (Pago a Gestor completado)
            $this->updateTramiteStatus($id, 28);

            $this->updateCobrarClienteFlag($db, (int) $id);
            
            // Bitácora
            $bitacoraModel = new BitacoraModel($db2);
            $diferencias = [];
            foreach ($changes as $field => $values) {
                $diferencias[$field] = [
                    'valor_original' => $values['old'] ?? null,
                    'valor_nuevo' => $values['new'] ?? null,
                ];
            }
            
            if (!empty($diferencias)) {
                $bitacoraModel->insert([
                    "id" => null,
                    "tipo" => "update",
                    "origen" => "tramite",
                    "tramite_id" => (int)$id,
                    "cambios" => json_encode($diferencias),
                    "user_id" => (int)$myid
                ], 'bitacora');
            }
    
            // Registrar log de usuario
            $tra_user_log = new TraUserLogModel($db2);
            $tra_user_log->insert([
                "tramite_id"    => (int)$id,
                "user_id"       => (int)$myid,
                "tra_status_id" => 28
            ], 'tra_user_log');
    
            // AUDITORÍA: Registrar cambios detectados
            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Pago a Gestor',
                    'form_step' => 5,
                    'form_section' => 'update_pago_gestor'
                ]);
                
                // NOTIFICACIÓN: Enviar notificación de pago a gestor
                if (isset($changes['pago_gestor_monto'])) {
                    $db = \Config\Database::connect();
                    $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                    $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                    $monto = $data['pago_gestor_monto'] ?? 0;
                    
                    notify_pago_gestor($id, $folio, $monto, $myid);
                }
            }
    
            $redirectUrl = '/deskapp/tramites/update/' . $id;
            if (has_permission('list_cobro_cliente', $perms, $roles) || has_permission('section_final_costos', $perms, $roles)) {
                $redirectUrl = '/deskapp/tramitesn/ver_seccion_cobro_cliente/' . $id;
            } elseif (has_permission('section_pago_gestor', $perms, $roles)) {
                $redirectUrl = '/deskapp/tramitesn/ver_seccion_pago_gestor/' . $id;
            }

            // Respuesta de éxito
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pago a gestor guardado correctamente.',
                'redirect' => $redirectUrl
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en update_pago_gestor: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar el pago a gestor: ' . $e->getMessage()
            ]);
        }
    }    

    private function updateCobrarClienteFlag($db, $tramiteId)
    {
        if (!$tramiteId) {
            return;
        }
        $rows = $db->table('tra_pago_gestor')
            ->select('comprobante_final')
            ->where('tramite_id', (int) $tramiteId)
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $hasFacturaGestor = false;
        $hasComprobantePago = false;
        foreach ($rows as $row) {
            $tipo = (string) ($row['comprobante_final'] ?? '');
            if ($tipo === 'factura_gestor') {
                $hasFacturaGestor = true;
            } elseif ($tipo === 'comprobante_pago') {
                $hasComprobantePago = true;
            }
        }
        $canCobrar = ($hasFacturaGestor && $hasComprobantePago) ? 1 : 0;
        $db->table('tramite')
            ->where('id', (int) $tramiteId)
            ->update(['cobrar_cliente' => $canCobrar]);
    }

    public function update_final_save()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $myid = $session->get('id');
        $id = $this->request->uri->getSegment(4);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        [$roles, $perms] = session_roles_perms($session);

        // Validar ID
        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de trámite inválido.']);
        }

        if ($resp = acl_require_tramite_tenant_access((int) $id, (int) $myid, $roles, 'Acceso denegado.', null, 403, true, $perms)) {
            return $resp;
        }

        if (!can_edit_cobro_cliente_surface($roles, $perms)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            "id_give_cliente" => "required",
            "numero_factura" => "required",
            "numero_refactura" => "permit_empty",
            "cobro_status_id" => "required|integer",
            "evidencia_cobro_txt" => "permit_empty",
            "costo_pago_cliente" => "required|decimal",
            "comision_derechos" => "required|decimal",
            "costo_total" => "permit_empty|decimal"
        ]);

        if ($validation->withRequest($this->request)->run() === FALSE) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tramite');
            
            // Verificar que el trámite existe
            $existingTramite = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingTramite) {
                return $this->response->setJSON(['success' => false, 'message' => 'El trámite no existe.']);
            }

            if (in_array((int) ($existingTramite['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                    'csrfHash' => csrf_hash(),
                ]);
            }

            // Preparar datos
            $data = $this->request->getPost([
                "id_give_cliente",
                "numero_factura",
                "numero_refactura",
                "cobro_status_id",
                "evidencia_cobro_txt",
                "costo_pago_cliente",
                "comision_derechos",
                "costo_gestoria",
                "costo_gestoria_hidden",
                "iva"
            ]);
            $data["user_id"] = $myid;

            // Fallback: si no viene el hidden, calcular desde pagos de derechos
            $costoGestoriaHidden = $data["costo_gestoria_hidden"] ?? null;
            if ($costoGestoriaHidden === null || $costoGestoriaHidden === '' || (float)$costoGestoriaHidden === 0.0) {
                try {
                    $sumRow = $db->table('tra_pago_derechos')
                        ->selectSum('costo', 'total')
                        ->where('tramite_id', (int)$id)
                        ->get()
                        ->getRowArray();
                    $costoGestoriaHidden = (string)((float)($sumRow['total'] ?? 0));
                } catch (\Exception $e) {
                    $costoGestoriaHidden = (string)0;
                }
            }

            $data["costo_gestoria"] = $costoGestoriaHidden;
            unset($data["costo_gestoria_hidden"]);
            
            // Calcular el costo total
            $data["costo_total"] = (float)$data["costo_gestoria"] + (float)$data["costo_pago_cliente"] + (float)$data["comision_derechos"] + (float)($data["iva"] ?? 0);

            // AUDITORÍA: Comparar datos antes de actualizar
            $changes = compare_tramite_data($existingTramite, $data);

            // Actualizar con WHERE obligatorio
            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo guardar el trámite final.');
            }

            $this->updateTramiteStatus($id, 28);

            $this->recordFinalSaveBitacora($changes, (int) $id, (int) $myid);
            $this->recordFinalSaveUserLog((int) $id, (int) $myid);
    
            // AUDITORÍA: Registrar cambios detectados
            if (!empty($changes)) {
                log_tramite_bulk_changes($id, $changes, 'tramite', [
                    'form_name' => 'Cobro a Cliente',
                    'form_step' => 6,
                    'form_section' => 'update_final_save'
                ]);
                
                // NOTIFICACIÓN: Enviar notificación de factura cobrada
                if (isset($changes['cobro_monto_final'])) {
                    $db = \Config\Database::connect();
                    $tramiteData = $db->table('tramite')->select('folio')->where('id', $id)->get()->getRowArray();
                    $folio = $tramiteData['folio'] ?? "Trámite #{$id}";
                    $monto = $data['cobro_monto_final'] ?? 0;
                    
                    notify_factura_cobrada($id, $folio, $monto, $myid);
                }
            }
    
            // Retornar mensaje de éxito como JSON
            return $this->response->setJSON([
                'success' => true,
                'message' => 'El trámite se guardó correctamente.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en update_final_save: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar trámite final: ' . $e->getMessage()]);
        }
    }

    protected function recordFinalSaveBitacora(array $changes, int $tramiteId, int $userId): void
    {
        $db2 = $this->_getDbData();
        $bitacoraModel = new BitacoraModel($db2);
        $diferencias = [];
        foreach ($changes as $field => $values) {
            $diferencias[$field] = [
                'valor_original' => $values['old'] ?? null,
                'valor_nuevo' => $values['new'] ?? null,
            ];
        }

        $insertBitacora = [
            'id' => null,
            'tipo' => 'update',
            'origen' => 'tramite',
            'tramite_id' => $tramiteId,
            'cambios' => json_encode($diferencias),
            'user_id' => $userId,
        ];

        $bitacoraModel->insert($insertBitacora, 'bitacora');
    }

    protected function recordFinalSaveUserLog(int $tramiteId, int $userId): void
    {
        $db2 = $this->_getDbData();
        $traUserLog = new TraUserLogModel($db2);
        $log = [
            'tramite_id' => $tramiteId,
            'user_id' => $userId,
            'tra_status_id' => 22,
        ];

        $traUserLog->insert($log, 'tra_user_log');
    }

    private function _example_output_2($output = null, $page = 'index') {
        return view('/deskapp/extra-pages/tramite_' . $page . '_view', (array)$output);
    }

    public function updateTramiteStatus($id, $newStatus){
        $db = \Config\Database::connect();
        $builder = $db->table('tramite');
        // $builder = $this->db->table('tramite'); // Cambia 'tramite' por el nombre real de tu tabla
        $tramite_base = $builder->getWhere(['id' => $id])->getRowArray();

        if (!$tramite_base) {
            return ['success' => false, 'message' => 'Trámite no encontrado'];
        }
        if (in_array((int) ($tramite_base['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
            return ['success' => false, 'message' => 'El trámite está concluido o cancelado.'];
        }

        // Define el flujo de estados válidos
        $arr_status = [22, 25, 26, 27, 23, 28, 20, 21];

        // Obtener la posición del estado actual y el nuevo estado en el flujo
        $currentStatusIndex = array_search($tramite_base['tra_status_id'], $arr_status);
        $newStatusIndex = array_search($newStatus, $arr_status);

        if ($newStatusIndex !== false && $newStatusIndex >= $currentStatusIndex) {
            $oldStatus = $tramite_base['tra_status_id'];
            $data = ['tra_status_id' => $newStatus];
            $builder->where('id', $id);
            $builder->update($data);

            // AUDITORÍA: Registrar cambio de estatus
            log_tramite_status_change($id, $oldStatus, $newStatus);

            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        }

        // Si el nuevo estado es anterior, no hacer nada
        return null; // Opcionalmente puedes omitir esta línea si no necesitas retorno
    }

    private function denyReadonlyStep4Mutation(int $tramiteId, array $roles, array $perms)
    {
        $db = \Config\Database::connect();
        $tramiteRow = $db->table('tramite')
            ->select('tra_status_id, reembolso_status_id, cobro_status_id, pago_gestor_st_id, status_doctos_gestor')
            ->where('id', $tramiteId)
            ->get(1)
            ->getRowArray();

        if (empty($tramiteRow)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'El trámite no existe.',
            ]);
        }

        $traStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);
        $pagoGestorStatusId = (int) ($tramiteRow['pago_gestor_st_id'] ?? 0);
        $statusDoctosGestor = (string) ($tramiteRow['status_doctos_gestor'] ?? '');

        $canKeepStep4Editable = $this->canKeepStep4Editable(
            $reembolsoStatusId,
            $pagoGestorStatusId,
            null,
            $statusDoctosGestor
        );
        $canOverrideStatus28 = has_permission('override_tramite_status_28_readonly', $perms, $roles);

        if (in_array($traStatusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($traStatusId === SGL_TRA_STATUS_COBRO_CLIENTE && !$canOverrideStatus28 && !$canKeepStep4Editable)) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'El trámite está en modo de solo lectura.',
            ]);
        }

        if (!$canKeepStep4Editable && !puede_editar_modulo($roles, $traStatusId, 'editar_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4)) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        return null;
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
            helper(['permissions']);
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->unsetAdd();    
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetDelete();
            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Mis Tramites');

            // Filtros especiales de "Mis Trámites" ahora son por permisos (no por rol).
            if (has_permission('mios_filter_status_11', $perms, $roles)) {
                $tramite_crud->where(['(tramite.user_id = ? AND tramite.tra_status_id = ?)' => [$myid, 11]])
                    ->where('tramite.tra_status_id NOT IN (20, 21)');
            } elseif (has_permission('mios_filter_status_22', $perms, $roles)) {
                $tramite_crud->where([
                    '(tramite.user_id = ? AND tramite.tra_status_id = ?)' => [$myid, 22]
                ]);
            } else {
                $tramite_crud->where([
                    'tramite.user_id' => $myid
                ]);
            }

            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->columns([
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);
            $tramite_crud->displayAs('created_at','Creación');  
            $tramite_crud->displayAs("started_at", "Desde Asignacion");
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

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

            $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, false);

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
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");
            $tramite_crud->displayAs('created_at','Creación'); 
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");
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

            $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
            }, false);

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
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");
            $tramite_crud->displayAs('created_at','Creación'); 
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");
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

            $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
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
                'created_at', 'started_at', 'id', 'folio','contrato','unidad','serie', 
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");
            $tramite_crud->displayAs('created_at','Creación'); 
            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");
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

            $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                return '/deskapp/tramites/update/' . $row->id;
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
        if (empty($datos1) && !empty($datos2)) {
            foreach ($datos2 as $clave => $valor) {
                $diferencias[$clave] = [
                    'valor_original' => '',
                    'valor_nuevo' => $valor
                ];
            }
            return $diferencias;
        }
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
        $data['title'] = 'Tipos de Tramite';
        $data['description'] = 'Consulta rapido que documentos base requiere cada tipo de tramite y administra la relacion oficial del catalogo.';

        $tiposDocumentosResumen = [];
        $documentosClasificacionActiva = false;
        try {
            $db = \Config\Database::connect();
            $documentosClasificacionActiva = in_array('es_obligatorio', $db->getFieldNames('tra_tipo_documentos'), true);
            $rows = $db->table('tra_tipos tt')
                ->select(
                    'tt.id, tt.tipo_tramite, tt.descripcion, d.documento_id, d.documento, '
                    . ($documentosClasificacionActiva ? 'COALESCE(ttd.es_obligatorio, 1)' : '1')
                    . ' AS es_obligatorio'
                )
                ->join('tra_tipo_documentos ttd', 'ttd.tra_tipos_id = tt.id', 'left')
                ->join('documento d', 'd.documento_id = ttd.documento_id', 'left')
                ->orderBy('tt.tipo_tramite', 'asc')
                ->orderBy('d.documento', 'asc')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $tipoId = (int) ($row['id'] ?? 0);
                if ($tipoId <= 0) {
                    continue;
                }

                if (!isset($tiposDocumentosResumen[$tipoId])) {
                    $tiposDocumentosResumen[$tipoId] = [
                        'id' => $tipoId,
                        'tipo_tramite' => trim((string) ($row['tipo_tramite'] ?? 'Tipo sin nombre')),
                        'descripcion' => trim((string) ($row['descripcion'] ?? '')),
                        'documentos_obligatorios' => [],
                        'documentos_opcionales' => [],
                    ];
                }

                $documentoId = (int) ($row['documento_id'] ?? 0);
                $documentoLabel = trim((string) ($row['documento'] ?? ''));
                if ($documentoId > 0 && $documentoLabel !== '') {
                    $bucket = ((int) ($row['es_obligatorio'] ?? 1) === 1)
                        ? 'documentos_obligatorios'
                        : 'documentos_opcionales';
                    $tiposDocumentosResumen[$tipoId][$bucket][$documentoId] = [
                        'id' => $documentoId,
                        'documento' => $documentoLabel,
                    ];
                }
            }

            foreach ($tiposDocumentosResumen as &$tipoResumen) {
                $tipoResumen['documentos_obligatorios'] = array_values($tipoResumen['documentos_obligatorios']);
                $tipoResumen['documentos_opcionales'] = array_values($tipoResumen['documentos_opcionales']);
            }
            unset($tipoResumen);
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo construir el resumen visual de tipos de tramite: ' . $e->getMessage());
        }

        $data['pre_output_html'] = view('deskapp/extra-pages/tramites_tipo_visual', [
            'tipos_documentos_resumen' => array_values($tiposDocumentosResumen),
            'documentos_clasificacion_activa' => $documentosClasificacionActiva,
        ]);
    
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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

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
        if ($resp = acl_require_login('/', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/tramites', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        $canFinalCostos = has_permission('section_final_costos', $perms, $roles);
        $canQuickAction = has_permission('quick_action_documentos', $perms, $roles);
        if (!($canFinalCostos || $canQuickAction || has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }

        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

        // Importante: estas quick actions pueden ser independientes de `editar_tramite`.
        // Ej: un rol puede NO editar datos del trámite, pero SÍ subir documentos.
        $canAdd = $canQuickAction && has_permission('quick_action_documentos_add', $perms, $roles);
        $canEdit = $canQuickAction && has_permission('quick_action_documentos_edit', $perms, $roles);
        $canDelete = $canQuickAction && has_permission('quick_action_documentos_delete', $perms, $roles);

        // Bloqueo por estatus (no por rol). El override aplica solo a status 28.
        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === 28 && !$canOverrideReadonly);
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Esta sección está en modo de solo lectura.']);
            }
            return redirect()->to('deskapp/tramites/single_documentostatus/' . $tramite_id)->with('error', 'Esta sección está en modo de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_documentostatus/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_documentostatus/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_documentostatus/' . $tramite_id, $isApi);
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

            // AUDITORÍA: Registrar cambios en documentos
            if (!empty($diferencias) && $tramite_id) {
                log_tramite_change(
                    $tramite_id,
                    'update',
                    'tra_doc_status',
                    'Actualización de documento',
                    null,
                    null,
                    null,
                    $diferencias
                );
            }

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

        $crud->setTable('tra_evidencias');
        $crud->setSubject('Bitacora', 'Bitacora');

        $crud->where([
            'folio_tramite' => $folio_tramite
        ]);   

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self, $crud) {
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
                
                // AUDITORÍA: Registrar inserción de evidencia
                if ($tramite_id) {
                    log_tramite_upload(
                        $tramite_id,
                        'tra_evidencias',
                        $data['file'] ?? 'archivo',
                        "Nueva evidencia agregada"
                    );
                }
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
            
            // AUDITORÍA: Registrar actualización de evidencia
            if (!empty($diferencias) && $tramite_id) {
                log_tramite_change(
                    $tramite_id,
                    'update',
                    'tra_evidencias',
                    'Actualización de evidencia',
                    null,
                    null,
                    null,
                    $diferencias
                );
            }

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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/tramites', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        $canQuickAction = has_permission('quick_action_bitacora', $perms, $roles);
        if (!($canQuickAction || has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }
        $tramiteModel = new TramitesModel($db2);
        $folio_tramite = $tramiteModel->getFolioById($tramite_id);
        $session->set('folio_tramite_id',  $folio_tramite);

        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

        // Importante: Bitácora puede ser independiente de `editar_tramite`.
        $canAdd = $canQuickAction && has_permission('quick_action_bitacora_add', $perms, $roles);
        $canEdit = $canQuickAction && has_permission('quick_action_bitacora_edit', $perms, $roles);
        $canDelete = $canQuickAction && has_permission('quick_action_bitacora_delete', $perms, $roles);

        // Bloqueo por estatus (no por rol). Override por permiso.
        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === 28 && !$canOverrideReadonly);
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($isApi) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Esta sección está en modo de solo lectura.']);
            }
            return redirect()->to('deskapp/tramites/single_evidencias/' . $tramite_id)->with('error', 'Esta sección está en modo de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_evidencias/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_evidencias/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_evidencias/' . $tramite_id, $isApi);
        }

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
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    public function single_pago_derechos(){
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/tramites', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }
        if ($resp = acl_require_permission('section_pago_derechos', $roles, $perms, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        $tramiteModel = new TramitesModel($db2);
        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);

        // Independiente de `editar_tramite`: permite subir/gestionar pagos derechos sin editar datos generales del trámite.
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
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === 28 && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Esta sección está en modo de solo lectura.']);
            }
            return redirect()->to('deskapp/tramites/single_pago_derechos/' . $tramite_id)->with('error', 'Esta sección está en modo de solo lectura.');
        }

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_pago_derechos/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_pago_derechos/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_pago_derechos/' . $tramite_id, $isApi);
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
        // $crud->unsetDelete();
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

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self, $crud) {
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
            helper(['permissions', 'cliente_filter', 'acl_guard']);

            $session = session();
            $userId = acl_throw_if_not_logged_in($session, 'Sesión expirada.');
            [$roles, $perms] = session_roles_perms($session);

            acl_throw_if_no_permission('editar_tramite', $roles, $perms, 'Acceso denegado.');
            acl_throw_if_no_permission('write_tramite_pago_derechos', $roles, $perms, 'Acceso denegado.');
            acl_throw_if_no_permission('can_upload_dropzone_pago_derechos', $roles, $perms, 'Acceso denegado.');

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
                $tramite_id = (int) ($row['tramite_id'] ?? 0);
                $fileName = trim((string) ($row['file'] ?? ''));

                if ($tramite_id <= 0) {
                    throw new \Exception('Trámite inválido.');
                }

                acl_throw_if_no_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.');

                if ($fileName === '' || $fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                    throw new \Exception('Nombre de archivo inválido.');
                }
        
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

        $crud->callbackAfterDelete(function ($stateParameters) use ($self) {
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            if ($tramite_id > 0) {
                $db = \Config\Database::connect();
                $self->updateCobrarClienteFlag($db, $tramite_id);
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

        $crud->callbackBeforeInsert(function ($stateParameters) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $myid = (int) $session->get('id');
            $stateParameters->data['user_id'] = $myid;
            $stateParameters->data['tramite_id'] = $tramite_id;
            return $stateParameters;
        });

        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $session = session();
            $request = \Config\Services::request();
            $uri = $request->getUri();
            $tramite_id = (int) $uri->getSegment(4);
            $myid = (int) $session->get('id');
            $stateParameters->data['user_id'] = $myid;
            $stateParameters->data['tramite_id'] = $tramite_id;
            return $stateParameters;
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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/tramites', $isApi);
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

        // Independiente de `editar_tramite`: permite subir/gestionar pagos a gestor sin editar datos generales del trámite.
        $canKeepStep4Editable = $this->canKeepStep4Editable(
            $reembolsoStatusId,
            (int) ($tramiteRow['pago_gestor_st_id'] ?? 0),
            null,
            (string) ($tramiteRow['status_doctos_gestor'] ?? '')
        );
        $canWrite = has_permission('section_pago_gestor', $perms, $roles)
            && has_permission('editar_pago_gestor', $perms, $roles)
            && ($canKeepStep4Editable || puede_editar_modulo($roles, $statusId, 'upload_pago_gestor', $reembolsoStatusId, $cobroStatusId, 4));

        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === 28 && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Esta sección está en modo de solo lectura.']);
            }
            return redirect()->to('deskapp/tramites/single_pago_gestor/' . $tramite_id)->with('error', 'Esta sección está en modo de solo lectura.');
        }


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

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_pago_gestor/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_pago_gestor/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_pago_gestor/' . $tramite_id, $isApi);
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
            helper(['permissions', 'cliente_filter', 'acl_guard']);

            $session = session();
            $userId = acl_throw_if_not_logged_in($session, 'Sesión expirada.');
            [$roles, $perms] = session_roles_perms($session);

            acl_throw_if_no_permission('can_upload_dropzone_pago_gestor', $roles, $perms, 'Acceso denegado.');

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
                $tramite_id = (int) ($row['tramite_id'] ?? 0);
                $fileName = trim((string) ($row['file'] ?? ''));

                if ($tramite_id <= 0) {
                    throw new \Exception('Trámite inválido.');
                }

                acl_throw_if_no_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.');

                if ($fileName === '' || $fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                    throw new \Exception('Nombre de archivo inválido.');
                }
        
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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $db2 = $this->_getDbData();
        $self = $this;
        $request = \Config\Services::request();

        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/tramites', $isApi);
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

        // Independiente de `editar_tramite`: permite subir/gestionar cobro a cliente sin editar datos generales del trámite.
        $canWrite = can_access_cobro_cliente_surface($roles, $perms)
            && puede_editar_modulo($roles, $statusId, 'upload_cobro_cliente', $reembolsoStatusId, $cobroStatusId, 5);

        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === 28 && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Esta sección está en modo de solo lectura.']);
            }
            return redirect()->to('deskapp/tramites/single_cobro_cliente/' . $tramite_id)->with('error', 'Esta sección está en modo de solo lectura.');
        }


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

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_cobro_cliente/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_cobro_cliente/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_cobro_cliente/' . $tramite_id, $isApi);
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
            helper(['permissions', 'cliente_filter', 'acl_guard']);

            $session = session();
            $userId = acl_throw_if_not_logged_in($session, 'Sesión expirada.');
            [$roles, $perms] = session_roles_perms($session);

            acl_throw_if_no_permission('can_upload_dropzone_cobro_cliente', $roles, $perms, 'Acceso denegado.');

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
                $tramite_id = (int) ($row['tramite_id'] ?? 0);
                $fileName = trim((string) ($row['file'] ?? ''));

                if ($tramite_id <= 0) {
                    throw new \Exception('Trámite inválido.');
                }

                acl_throw_if_no_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.');

                if ($fileName === '' || $fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                    throw new \Exception('Nombre de archivo inválido.');
                }
        
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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $self = $this;
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $tramite_id = (int) $uri->getSegment(4);

        $isApi = ($request->isAJAX() || $request->getGet('gc_state') !== null);
        if ($resp = acl_require_login('/', 'Sesión expirada.', $isApi)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($tramite_id <= 0) {
            return acl_deny('ID de trámite inválido.', 400, '/deskapp/tramites', $isApi);
        }

        if ($resp = acl_require_tramite_tenant_access($tramite_id, $userId, $roles, 'Acceso denegado.', '/deskapp/dashboard', 403, $isApi)) {
            return $resp;
        }

        $canQuickAction = has_permission('quick_action_evidencias_finales', $perms, $roles);
        if (!($canQuickAction || has_permission('section_final_costos', $perms, $roles) || has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/dashboard', $isApi);
        }

        $tramiteModel = new TramitesModel($this->_getDbData());
        $tramiteRow = $tramiteModel->getTramiteById($tramite_id);
        $statusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
        $reembolsoStatusId = (int) ($tramiteRow['reembolso_status_id'] ?? 0);
        $cobroStatusId = (int) ($tramiteRow['cobro_status_id'] ?? 0);

        // Independiente de `editar_tramite`: permite subir evidencias finales sin editar datos generales del trámite.
        $canWrite = has_permission('section_final_costos', $perms, $roles)
            && puede_editar_modulo($roles, $statusId, 'evidencias_finales_gestor', $reembolsoStatusId, $cobroStatusId, 4);

        $canOverrideReadonly = has_permission('override_tramite_status_28_readonly', $perms, $roles);
        $isLocked = in_array($statusId, SGL_TRA_STATUS_LOCKED_IDS, true) || ($statusId === 28 && !$canOverrideReadonly) || !$canWrite;
        $gcState = (string) ($request->getGet('gc_state') ?? '');
        if ($isLocked && in_array($gcState, ['add', 'edit', 'insert', 'update', 'delete', 'ajax_insert', 'ajax_update', 'ajax_delete'], true)) {
            if ($request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Esta sección está en modo de solo lectura.']);
            }
            return redirect()->to('deskapp/tramites/single_evidencias_finales/' . $tramite_id)->with('error', 'Esta sección está en modo de solo lectura.');
        }
    
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setCsrfTokenName(csrf_token());
        $crud->setCsrfTokenValue(csrf_hash());

        $crud->setTable('tra_evidencias_finales');
        $crud->setSubject('Evidencia Final', 'Evidencias Finales');

        $canAdd = $canWrite
            && $canQuickAction
            && has_permission('quick_action_evidencias_finales_add', $perms, $roles);
        $canEdit = $canWrite && $canQuickAction && has_permission('quick_action_evidencias_finales_edit', $perms, $roles);
        $canDelete = $canWrite
            && $canQuickAction
            && has_permission('quick_action_evidencias_finales_delete', $perms, $roles);

        if (in_array($gcState, ['add', 'insert', 'ajax_insert'], true) && !$canAdd) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_evidencias_finales/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['edit', 'update', 'ajax_update'], true) && !$canEdit) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_evidencias_finales/' . $tramite_id, $isApi);
        }
        if (in_array($gcState, ['delete', 'ajax_delete'], true) && !$canDelete) {
            return acl_deny('Acceso denegado.', 403, '/deskapp/tramites/single_evidencias_finales/' . $tramite_id, $isApi);
        }

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

        $crud->fields([
            'file', 'tramite_id',
            'costo', 'comentario', 'user_id'
        ]); 

        $crud->columns([
            'id', 'tramite_id','file', 
            'costo', 'created_at'
        ]); 

        $crud->callbackAfterInsert(function ($stateParameters)  use ($self, $crud) {
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
            // GroceryCrud solo conserva UN callback por evento; consolidamos Bitácora + ApiLog.
            return logOperation($stateParameters, $crud->getTable());
        });

        $crud->callbackAfterUpdate(function ($stateParameters) use ($self, $crud){
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
        $crud->callbackAfterDelete(function ($stateParameters) use ($crud) {
            $tableName = $crud->getTable();
            return logOperation($stateParameters, $tableName);
        });

        $salida = $crud->render();
        $salida2 = array_merge((array)$salida, $data);
        return $this->_example_output($salida2);
    }

    protected function _getDbData() {
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
    protected function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
        return $groceryCrud;
    }

    public function autorizar(){
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

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
                case 23:
                    $requiredPermission = 'important_pasar_a_pagos';
                    break;
                case 20:
                    $requiredPermission = 'important_concluir_tramite';
                    break;
                case 29:
                case 11:
                    $requiredPermission = 'important_cancelar_tramite';
                    break;
                default:
                    $requiredPermission = 'editar_tramite';
                    break;
            }

            if ($resp = acl_require_permission($requiredPermission, $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            // Obtener datos actuales para auditoría
            $tramiteActual = $builder->where('id', $tramiteId)->get()->getRowArray();
            $oldStatusId = $tramiteActual['tra_status_id'] ?? null;
            if (in_array((int) $oldStatusId, SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                ]);
            }
            
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
            
            // AUDITORÍA: Registrar autorización con cambio de estatus
            if ($oldStatusId) {
                log_tramite_status_change($tramiteId, $oldStatusId, $statusId);
            }
            log_tramite_change(
                $tramiteId,
                'update',
                'tramite',
                'Trámite autorizado',
                'autorizado',
                '0',
                '1'
            );

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function check_reembolso_status() {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $db = \Config\Database::connect();
    
        try {
            if ($tramiteId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Datos inválidos.']);
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            // Endpoint auxiliar para concluir trámite: exige el mismo permiso de la transición
            if ($resp = acl_require_permission('important_concluir_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            // Consultar el reembolso_status_id del trámite
            $builder = $db->table('tramite');
            $builder->select('reembolso_status_id');
            $builder->where('id', $tramiteId);
            $tramite = $builder->get()->getRow();
    
            // Verificar si el reembolso está pendiente (21 o 22)
            $reembolsoPendiente = false;
            if ($tramite && in_array($tramite->reembolso_status_id, [21, 22])) {
                $reembolsoPendiente = true;
            }
    
            // Devolver la respuesta JSON
            return $this->response->setJSON(['reembolso_pendiente' => $reembolsoPendiente]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function change_status(){
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $statusId = (int) $this->request->getPost('status_id');
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $builder = $db->table('tramite');

        try {
            if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
                return $resp;
            }

            $session = session();
            $userId = (int) ($session->get('id') ?? 0);
            [$roles, $perms] = session_roles_perms($session);

            if ($tramiteId <= 0 || $statusId <= 0) {
                return acl_deny('Datos inválidos.', 400, null, true);
            }

            // Permiso requerido según transición (alineado con los botones en vistas)
            $requiredPermission = null;
            switch ($statusId) {
                case 23: // Aprobar Trámite
                    $requiredPermission = 'important_pasar_a_pagos';
                    break;
                case 20: // Concluir Trámite
                    $requiredPermission = 'important_concluir_tramite';
                    break;
                case 29: // Es solo Cotización
                case 11: // Reactivar como trámite (desde cotización)
                    $requiredPermission = 'important_cancelar_tramite';
                    break;
                default:
                    $requiredPermission = 'editar_tramite';
                    break;
            }

            if ($resp = acl_require_permission($requiredPermission, $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            // Multi-tenancy: validar acceso al cliente del trámite
            $tramiteRow = $db->table('tramite')->select('cli_directo_id, tra_status_id')->where('id', $tramiteId)->get(1)->getRowArray();
            if (empty($tramiteRow)) {
                return acl_deny('Trámite no encontrado.', 404, null, true);
            }
            $oldStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
            if (in_array((int) ($tramiteRow['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'El trámite está concluido o cancelado.',
                ]);
            }
            $cliDirectoId = (int) ($tramiteRow['cli_directo_id'] ?? 0);
            if ($cliDirectoId > 0) {
                $cliRow = $db->table('cli_directo')->select('cliente_id')->where('id', $cliDirectoId)->get(1)->getRowArray();
                $clienteId = (int) ($cliRow['cliente_id'] ?? 0);
                if ($clienteId > 0 && !has_permission('bypass_cliente_filter', $perms, $roles) && !has_access_to_cliente($clienteId, $userId)) {
                    return acl_deny('Acceso denegado al cliente del trámite.', 403, null, true);
                }
            }

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
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

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

            // Permiso requerido según transición (alineado con change_status)
            $requiredPermission = null;
            switch ($statusId) {
                case 23:
                    $requiredPermission = 'important_pasar_a_pagos';
                    break;
                case 20:
                    $requiredPermission = 'important_concluir_tramite';
                    break;
                case 29:
                case 11:
                default:
                    $requiredPermission = 'important_cancelar_tramite';
                    break;
            }

            if ($resp = acl_require_permission($requiredPermission, $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            $tramiteRow = $db->table('tramite')->select('tra_status_id')->where('id', $tramiteId)->get(1)->getRowArray();
            if (empty($tramiteRow)) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Trámite no encontrado.']);
            }
            $oldStatusId = (int) ($tramiteRow['tra_status_id'] ?? 0);
            if (in_array((int) ($tramiteRow['tra_status_id'] ?? 0), SGL_TRA_STATUS_LOCKED_IDS, true)) {
                return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'El trámite está concluido o cancelado.']);
            }

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
    public function get_service_types()
    {
        helper(['permissions', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        [$roles, $perms] = session_roles_perms($session);

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db2 = $this->_getDbData();
        $TraTiposModel = new TraTiposModel($db2);
        $tra_tipos_options = $TraTiposModel->getTraTiposOptions();
        return $this->response->setJSON($tra_tipos_options);
    }

    public function get_services_by_tramite($tramiteId)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return acl_json_empty(400);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $model = new TraTramiteAsociadoModel();
        return $this->response->setJSON($model->getServicesByTramiteId($tramiteId));
    }
    public function save_services()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $tramiteId = (int) $this->request->getPost('tramite_id');
        $services = $this->request->getPost('services');

        if ($tramiteId <= 0 || empty($services)) {
            return acl_deny('Datos insuficientes', 400, null, true);
        }

        // Endurecer: mutaciones requieren permiso de edición general
        if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = $this->denyReadonlyStep4Mutation($tramiteId, $roles, $perms)) {
            return $resp;
        }

        $model = new TraTramiteAsociadoModel();
        foreach ($services as $serviceId) {
            $model->saveService($tramiteId, $serviceId);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Servicios guardados exitosamente']);
    }

    // Eliminar un servicio asociado al trámite
    public function delete_service()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $serviceId = $this->request->getPost('asociado_id');
        
        // Validar ID del servicio
        if (!$serviceId || !is_numeric($serviceId)) {
            return acl_deny('ID de servicio inválido.', 400, null, true);
        }

        try {
            $model = new TraTramiteAsociadoModel();
            
            // Verificar que el servicio existe antes de eliminar
            $existingService = $model->find($serviceId);
            if (!$existingService) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'El servicio no existe.']);
            }

            if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            $tramiteId = (int) ($existingService['tramite_id'] ?? 0);
            if ($tramiteId <= 0) {
                return acl_deny('Servicio inválido.', 400, null, true);
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = $this->denyReadonlyStep4Mutation($tramiteId, $roles, $perms)) {
                return $resp;
            }

            $model->deleteService($serviceId);

            return $this->response->setJSON(['status' => 'deleted', 'message' => 'Servicio eliminado correctamente.']);

        } catch (\Exception $e) {
            log_message('error', 'Error en delete_service: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Error al eliminar.']);
        }
    }

    public function get_service_costs_by_tramite($tramiteId)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $tramiteId = (int) $tramiteId;
        if ($tramiteId <= 0) {
            return acl_json_empty(400);
        }

        if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        if ($resp = acl_require_permission('section_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $query = $db->table('tra_tramite_asociado')
                    ->select('tra_tramite_asociado.id, tra_tramite_asociado.costo_tramite, tra_tipos.tipo_tramite')
                    ->join('tra_tipos', 'tra_tipos.id = tra_tramite_asociado.tra_tipos_id')
                    ->where('tra_tramite_asociado.tramite_id', $tramiteId)
                    ->get();

        return $this->response->setJSON($query->getResultArray());
    }

    public function update_service_cost(){
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        if ($resp = acl_require_login(null, 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $id = $this->request->getPost('id');
        $costo_tramite = $this->request->getPost('costo_tramite');

        // Validar ID
        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID de servicio inválido.'
            ]);
        }

        // Validar costo_tramite (debe ser numérico si no está vacío)
        if ($costo_tramite !== '' && $costo_tramite !== null && !is_numeric($costo_tramite)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El costo debe ser un valor numérico válido.'
            ]);
        }

        // Convertir vacío a NULL
        if ($costo_tramite === '' || $costo_tramite === null) {
            $costo_tramite = null;
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tra_tramite_asociado');
            
            // Verificar que el registro existe
            $existingRecord = $builder->where('id', $id)->get()->getRowArray();
            if (!$existingRecord) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'El servicio asociado no existe.'
                ]);
            }

            $tramiteId = (int) ($existingRecord['tramite_id'] ?? 0);
            if ($tramiteId <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Servicio inválido.'
                ]);
            }

            if ($resp = acl_require_permission('editar_tramite', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = acl_require_permission('editar_pago_gestor', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = acl_require_tramite_tenant_access($tramiteId, $userId, $roles, 'Acceso denegado.', null, 403, true)) {
                return $resp;
            }

            if ($resp = $this->denyReadonlyStep4Mutation($tramiteId, $roles, $perms)) {
                return $resp;
            }

            // Actualizar
            $data = [
                'costo_tramite' => $costo_tramite,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $builder->where('id', $id);
            $updateResult = $builder->update($data);

            if (!$updateResult) {
                throw new \Exception('No se pudo actualizar el costo del servicio.');
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Costo actualizado correctamente.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en update_service_cost: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }
    public function sincronizarTramites()
    {
        helper(['permissions', 'acl_guard']);

        if ($resp = acl_require_login('/', 'Sesión expirada.', true)) {
            return $resp;
        }

        $session = session();
        [$roles, $perms] = session_roles_perms($session);

        if ($resp = acl_require_permission('sincronizar_tramites', $roles, $perms, 'Acceso denegado.', null, 403, true)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $traTramiteAsociadoModel = new TraTramiteAsociadoModel();
        $resultado = $traTramiteAsociadoModel->syncTramitesWithoutAsociados();

        return $this->response->setJSON(['message' => $resultado]);
    }

    public function list_cobro_clientes()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();
            $tramite_crud->where('tra_status_id NOT IN (20, 21)');
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite', $perms, $roles) || has_permission('read_tramite', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramites/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite', $perms, $roles)){
                $tramite_crud->unsetPrint();
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
            
            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2025-01-01 00:00:00']
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
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
                $claseAzulCobroCliente = 'background-azul-cobro-cliente';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == 23 || $row->tra_status_id == 28) {
                    if($row->tra_status_id == 23){
                        $clase = $claseAzulClaro;
                    }
                    $txt_generar_factura = '';

                    // agrega validacion para cobro cliente y para evidencias finales dado el tramite_id, si existe alguno entonces se debe usar otra clase
                     $traCobroClienteModel = new TraCobroClienteModel();
                     $registrosCobroCliente = $traCobroClienteModel->getByTramiteId($row->id);

                     $traEvidenciasFinalesModel = new TraEvidenciasFinalesModel();
                     $registrosEvidenciasFinales = $traEvidenciasFinalesModel->getByTramiteId($row->id);
                     // si alguna de las dos tiene registros entonces txt_generar_factura debe decir "Generar Factura" de lo contrario queda vacio
                    if (count($registrosCobroCliente) > 0 || count($registrosEvidenciasFinales) > 0) {
                        $txt_generar_factura = 'Facturar';
                    }

                    if($row->tra_status_id == 28){
                        $clase = $claseAzulCobroCliente;
                        return '<span class="' . $clase . '">' . $txt_generar_factura . '</span>';
                    }
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


                $arrFilter = [20, 21, 23, 28];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

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

    public function cancelados()
    {
        try {
            # Manejo de session de action
            $self = $this;
            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            [$roles, $perms] = session_roles_perms($session);

            // Auditoría de permisos (se muestra en el div de audit cuando Debug ON)
            $data['perm_audit_context'] = 'tramites/cancelados';
            $data['perm_audit_requirements'] = [
                'Botón "Editar"' => 'editar_tramite_cancelado',
                'Botón "Ver" (si aparece)' => 'read_tramite_cancelado',
                'En "Más": Eliminar' => 'delete_tramite_cancelado',
                'En "Más": Exportar' => 'export_tramite_cancelado',
                'En "Más": Imprimir' => 'print_tramite_cancelado',
                'En "Más": Clonar' => 'clone_tramite_cancelado',
            ];
            # fin del manejo de session

            $tramite_crud = $this->_getGroceryCrudEnterprise();

            // FILTRADO POR CLIENTE / MULTI-TENANCY (mismo criterio que el módulo Cancelado)
            $filterSql = get_tramite_filter_sql($myid);
            $tramite_crud->where($filterSql);

            $tramite_crud->where('tra_status_id = 21');
            
            $tramite_crud->unsetAdd();
            $tramite_crud->unsetEdit();
            $tramite_crud->unsetRead();
            // $tramite_crud->setTheme('bootstrap-v5');
            $tramite_crud->unsetDeleteMultiple();
            if (has_permission('editar_tramite_cancelado', $perms, $roles) || has_permission('read_tramite_cancelado', $perms, $roles)){
                $tramite_crud->setActionButton('Abrir', 'fas fa-eye', function ($row) {
                    return '/deskapp/tramitesn/update/' . $row->id;
                }, false);
            }

            if (!has_permission('delete_tramite_cancelado', $perms, $roles)){
                $tramite_crud->unsetDelete();
            }

            if (!has_permission('export_tramite_cancelado', $perms, $roles)){
                $tramite_crud->unsetExport();
            }

            if (!has_permission('print_tramite_cancelado', $perms, $roles)){
                $tramite_crud->unsetPrint();
            }

            if (!has_permission('clone_tramite_cancelado', $perms, $roles)){
                $tramite_crud->unsetClone();
            }

            $tramite_crud->setCsrfTokenName(csrf_token());
            $tramite_crud->setCsrfTokenValue(csrf_hash());

            //lista todos los unset de grocery crud
            
            $tramite_crud->setTable('tramite');
            $tramite_crud->setSubject('tramite', 'Tramites');
            $tramite_crud->defaultOrdering('tramite.id', 'desc');
            
            $tramite_crud->where([
                'tramite.created_at >= ?' => ['2025-01-01 00:00:00']
            ]);

            $tramite_crud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie', 
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramite_crud->displayAs("started_at", "Desde Asignación");


            $tramite_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramite_crud->displayAs("user_id", "Ejecutivo");

            $tramite_crud->callbackColumn('started_at', function ($value, $row) {
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
                $claseAzulCobroCliente = 'background-azul-cobro-cliente';  // Clase CSS para azul
            
                // Verificar tra_status_id para colores especiales
                if ($row->tra_status_id == 23 || $row->tra_status_id == 28) {
                    if($row->tra_status_id == 23){
                        $clase = $claseAzulClaro;
                    }
                    $txt_generar_factura = '';

                    // agrega validacion para cobro cliente y para evidencias finales dado el tramite_id, si existe alguno entonces se debe usar otra clase
                     $traCobroClienteModel = new TraCobroClienteModel();
                     $registrosCobroCliente = $traCobroClienteModel->getByTramiteId($row->id);

                     $traEvidenciasFinalesModel = new TraEvidenciasFinalesModel();
                     $registrosEvidenciasFinales = $traEvidenciasFinalesModel->getByTramiteId($row->id);
                     // si alguna de las dos tiene registros entonces txt_generar_factura debe decir "Generar Factura" de lo contrario queda vacio
                    if (count($registrosCobroCliente) > 0 || count($registrosEvidenciasFinales) > 0) {
                        $txt_generar_factura = 'Facturar';
                    }

                    if($row->tra_status_id == 28){
                        $clase = $claseAzulCobroCliente;
                        return '<span class="' . $clase . '">' . $txt_generar_factura . '</span>';
                    }
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


                $arrFilter = [20, 21, 23, 28];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' días</span>';
                }
            
                return '<span class="' . $clase . '"></span>';
            });

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

    /**
     * ========================================================================
     * TIMELINE DE AUDITORÍA DEL TRÁMITE
     * ========================================================================
     * Muestra todos los cambios realizados en el trámite con detalles completos
     */
    public function audit_timeline($tramiteId = null)
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        $canViewAuditTimeline = has_permission('monitoreo_auditoria_tramite', $perms, $roles)
            || has_permission('tramite_detalle_quick_actions_historial_actividad_ver', $perms, $roles);

        if (!$canViewAuditTimeline) {
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para acceder a esta función');
        }
        
        if (!$tramiteId || !is_numeric($tramiteId) || (int) $tramiteId <= 0) {
            return redirect()->to(site_url('/deskapp/tramites/audit_search'))->with('error', 'ID de trámite no proporcionado');
        }
        
        // Verificar que el trámite existe
        $db = Database::connect();
        $tramite = $db->table('tramite')->select('id, folio, tra_status_id')->where('id', $tramiteId)->get()->getRowArray();
        
        if (!$tramite) {
            return redirect()->to(site_url('/deskapp/tramites/audit_search'))->with('error', 'Trámite no encontrado');
        }

        if ($resp = acl_require_tramite_tenant_access((int) $tramiteId, $userId, $roles, 'No tienes permisos para ver este trámite', site_url('/deskapp/tramites/audit_search'), 403, false)) {
            log_unauthorized_access_attempt('tramite_audit', (int) $tramiteId);
            return $resp;
        }
        
        // Obtener datos de auditoría
        $auditLog = get_tramite_audit_log($tramiteId);
        
        $db2 = $this->_getDbData();
        $traStatusModel = new \App\Models\TraStatusModel($db2);
        $traStatusOptions = $traStatusModel->getTraStatusOptions();
        $traStatusSteps = $traStatusOptions['steps'] ?? [];
        $stepActualDb = $traStatusSteps[$tramite['tra_status_id']] ?? 1;
        $stepActualDisplay = $stepActualDb + 1;

        $data = [
            'session' => $session,
            'tramite_id' => $tramiteId,
            'folio' => $tramite['folio'],
            'audit_log' => $auditLog,
            'last_modifier' => get_tramite_last_modifier($tramiteId),
            'summary' => get_tramite_audit_summary($tramiteId),
            'total_changes' => count($auditLog),
            'step_actual_db' => $stepActualDb,
            'step_actual_display' => $stepActualDisplay,
        ];
        
        // Log temporal para debug
        log_message('info', "[audit_timeline] Trámite ID: {$tramiteId}, Total registros: " . count($auditLog));
        
        return view('deskapp/tramites/audit_timeline', $data);
    }

    /**
     * Vista de búsqueda de auditoría
     * Solo accesible por admin y super_admin
     */
    public function audit_search()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();

        if ($resp = acl_require_login('/deskapp/auth/login', 'Sesión expirada.', false)) {
            return $resp;
        }
        
        [$roles, $perms] = session_roles_perms($session);

        if ($resp = acl_require_permission('monitoreo_auditoria_tramite', $roles, $perms, 'No tienes permisos para acceder a esta función', '/deskapp/dashboard', 403, false)) {
            return $resp;
        }
        
        $data = [
            'session' => $session,
            'title' => 'Buscar Auditoría de Trámite'
        ];
        
        return view('deskapp/tramites/audit_search', $data);
    }

    /**
     * Buscar trámite por folio para auditoría
     * Solo accesible por admin y super_admin
     */
    public function buscar_por_folio()
    {
        helper(['permissions', 'cliente_filter', 'acl_guard']);

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        
        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('monitoreo_auditoria_tramite', $perms, $roles)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para acceder a esta función'
            ]);
        }
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Petición inválida'
            ]);
        }

        $json = $this->request->getJSON();
        $folio = $json->folio ?? '';
        
        if (empty($folio)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El folio es requerido'
            ]);
        }
        
        try {
            $db = Database::connect();
            $tramite = $db->table('tramite')
                ->select('id, folio')
                ->where('folio', $folio)
                ->get()
                ->getRowArray();
            
            if (!$tramite) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontró ningún trámite con el folio: ' . $folio
                ]);
            }

            if (!acl_has_tramite_tenant_access((int) $tramite['id'], $userId, $roles)) {
                log_unauthorized_access_attempt('tramite_audit', (int) $tramite['id']);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este trámite'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'tramite_id' => $tramite['id'],
                'folio' => $tramite['folio']
            ]);
            
        } catch (\Exception $e) {
            log_message('error', '[Tramites::buscar_por_folio] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al buscar el trámite: ' . $e->getMessage()
            ]);
        }
    }

}

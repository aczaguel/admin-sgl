<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Models\TramitesModel;
use Config\Database;
use Config\GroceryCrud as ConfigGroceryCrud;
use Config\Database as ConfigDatabase;
use GroceryCrud\Core\GroceryCrud;

class CorrecionTramites extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
        helper(['url', 'form', 'session', 'cliente_filter', 'cliente_context', 'permissions', 'acl_guard']);

        $session = session();
        $userId = $session->get('id');
        $requested = $this->request ? $this->request->getGet('cliente_id') : null;

        // Persistir cliente activo (si viene en GET) para que el filtro aplique en ESTA misma request
        resolve_active_cliente_id($userId, $requested);
    }

    private function requireCorreccionTramitesPermission(?bool $forceJson = null)
    {
        $session = session();
        [$roles, $perms] = session_roles_perms($session);
        return acl_require_permission(
            'monitoreo_correccion_tramites',
            $roles,
            $perms,
            'No tienes permisos para acceder a esta función',
            '/deskapp/dashboard',
            403,
            $forceJson
        );
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

    private function _getGroceryCrudEnterprise()
    {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();
        $groceryCrud = new GroceryCrud($config, $db);
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
        return $groceryCrud;
    }

    private function _example_output($output = null)
    {
        $output = (object)$output;
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
        }
        
        $data = (array)$output;
        $data['title'] = is_string($data['title'] ?? null) ? $data['title'] : 'Corrección de Trámites';
        $data['session'] = session();
        
        return view('deskapp/correccion_tramites/index', $data);
    }

    public function index()
    {
        $session = session();

        if ($resp = $this->requireCorreccionTramitesPermission(false)) {
            return $resp;
        }

        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $data['title'] = 'Corrección de Trámites';

        try {
            $crud = $this->_getGroceryCrudEnterprise();
            $crud->setCsrfTokenName(csrf_token());
            $crud->setCsrfTokenValue(csrf_hash());
            
            // Establecer la ruta de la API
            $crud->setApiUrlPath('/deskapp/correccion-tramites/crud_api');

            $crud->setTable('tramite');
            $filterSql = get_tramite_filter_sql($session->get('id'));
            $crud->where($filterSql);
            $crud->setSubject('Trámite', 'Trámites para Corrección');
            $crud->defaultOrdering('tramite.id', 'desc');

            // Solo mostrar columnas necesarias para corrección
            $crud->columns([
                'id',
                'folio',
                'created_at',
                'contrato',
                'unidad',
                'serie',
                'placas',
                'tra_tipos_id',
                'tra_status_id',
                'cli_directo_id',
                'cli_directo_ejecutivo_id',
                'user_id'
            ]);

            // Campos editables del modulo de correccion
            $crud->fields([
                'folio',
                'contrato',
                'unidad',
                'serie',
                'placas',
                'tra_tipos_id',
                'tra_status_id',
                'cli_directo_id',
                'cli_directo_ejecutivo_id',
                'empresa_gestora_id',
                'gestor_id'
            ]);

            // Campos de solo lectura
            $crud->readOnlyFields([
                'folio',
                'contrato',
                'unidad',
                'serie',
                'placas'
            ]);

            // Deshabilitar agregar y eliminar
            $crud->unsetAdd();
            $crud->unsetDelete();
            $crud->unsetDeleteMultiple();
            $crud->unsetExport();
            $crud->unsetPrint();

            // Botón de auditoría de trámite
            $crud->setActionButton('Auditar', 'fas fa-clipboard-list', function ($row) {
                return '/deskapp/tramites/audit_timeline/' . $row->id;
            }, false);

            // Display names
            $crud->displayAs('id', 'ID');
            $crud->displayAs('folio', 'Folio');
            $crud->displayAs('created_at', 'Fecha Creación');
            $crud->displayAs('contrato', 'Contrato');
            $crud->displayAs('unidad', 'Unidad');
            $crud->displayAs('serie', 'Serie');
            $crud->displayAs('placas', 'Placas');
            $crud->displayAs('tra_tipos_id', 'Tipo de Trámite');
            $crud->displayAs('tra_status_id', 'Estatus del Trámite');
            $crud->displayAs('empresa_gestora_id', 'Empresa Gestora');
            $crud->displayAs('gestor_id', 'Gestor');
            $crud->displayAs('cli_directo_id', 'Cliente Directo');
            $crud->displayAs('cli_directo_ejecutivo_id', 'Ejecutivo');
            $crud->displayAs('user_id', 'Asignado a');

            // Relaciones
            $crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $crud->setDependentRelation('gestor_id', 'empresa_gestora_id', 'empresa_gestora_id');
            $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $crud->setDependentRelation('cli_directo_ejecutivo_id', 'cli_directo_id', 'cli_directo_id');
            $crud->setRelation('user_id', 'users', '{firstname} {lastname}');

            // Callback ANTES de actualizar para capturar datos viejos
            $controller = $this;
            $crud->callbackBeforeUpdate(function ($stateParameters) use ($controller) {
                log_message('debug', '========== callbackBeforeUpdate INICIANDO ==========');
                $tramiteId = $stateParameters->primaryKeyValue;
                log_message('debug', 'Tramite ID a actualizar: ' . $tramiteId);
                
                // Guardar los datos viejos en sesión temporalmente
                $db = Database::connect();
                $oldData = $db->table('tramite')->where('id', $tramiteId)->get()->getRowArray();
                if ($oldData) {
                    session()->setTempdata('old_tramite_' . $tramiteId, $oldData, 60);
                    log_message('debug', 'Datos viejos guardados: ' . json_encode($oldData));
                }
                
                return $stateParameters;
            });

            // Callback después de actualizar para registrar el cambio
            $crud->callbackAfterUpdate(function ($stateParameters) use ($controller) {
                log_message('debug', '========== callbackAfterUpdate INICIANDO ==========');
                $tramiteId = $stateParameters->primaryKeyValue;
                log_message('debug', 'Tramite ID actualizado: ' . $tramiteId);
                
                $newData = (array) $stateParameters->data;
                log_message('debug', 'Datos nuevos: ' . json_encode($newData));
                
                // Obtener datos viejos de la sesión
                $oldData = session()->getTempdata('old_tramite_' . $tramiteId);
                if ($oldData) {
                    log_message('debug', 'Datos viejos recuperados de sesión');
                    $controller->registrarCambioConDatos($tramiteId, $oldData, $newData);
                } else {
                    log_message('debug', 'No se encontraron datos viejos, usando método alternativo');
                    $controller->registrarCambioPublic($tramiteId, $newData);
                }
                
                return $stateParameters;
            });

            $output = $crud->render();
            
            return $this->_example_output($output);

        } catch (\Exception $e) {
            log_message('error', 'Error en CorrecionTramites: ' . $e->getMessage() . ' | Línea: ' . $e->getLine() . ' | Archivo: ' . $e->getFile());
            
            // Si es petición AJAX, devolver JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => $e->getMessage(),
                    'trace' => ENVIRONMENT === 'development' ? $e->getTraceAsString() : null
                ]);
            }
            
            // Si es petición normal, relanzar la excepción para que CodeIgniter la maneje
            throw $e;
        }
    }

    // Método específico para las llamadas API de Grocery CRUD
    public function crud_api()
    {
        log_message('debug', '========== crud_api() INICIANDO ==========');
        log_message('debug', 'Request URI: ' . $this->request->getUri());
        log_message('debug', 'Request Method: ' . $this->request->getMethod());
        log_message('debug', 'Request POST: ' . json_encode($this->request->getPost()));
        
        $session = session();

        if ($resp = $this->requireCorreccionTramitesPermission(true)) {
            return $resp;
        }

        try {
            $crud = $this->_getGroceryCrudEnterprise();
            $crud->setCsrfTokenName(csrf_token());
            $crud->setCsrfTokenValue(csrf_hash());
            $crud->setApiUrlPath('/deskapp/correccion-tramites/crud_api');

            $crud->setTable('tramite');
            $crud->setSubject('Trámite', 'Trámites para Corrección');
            $crud->defaultOrdering('tramite.id', 'desc');

            $filterSql = get_tramite_filter_sql($session->get('id'));
            $crud->where($filterSql);

            $crud->columns([
                'id', 'folio', 'created_at', 'contrato', 'unidad', 'serie', 'placas',
                'tra_tipos_id', 'tra_status_id', 'cli_directo_id', 'cli_directo_ejecutivo_id', 'user_id'
            ]);

            $crud->fields(['folio', 'contrato', 'unidad', 'serie', 'placas', 'tra_tipos_id', 'tra_status_id', 'cli_directo_id', 'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id']);
            $crud->readOnlyFields(['folio', 'contrato', 'unidad', 'serie', 'placas']);
            $crud->unsetAdd();
            $crud->unsetDelete();
            $crud->unsetDeleteMultiple();
            $crud->unsetExport();
            $crud->unsetPrint();

            // Botón de auditoría de trámite
            $crud->setActionButton('Auditar', 'fas fa-clipboard-list', function ($row) {
                return '/deskapp/tramites/audit_timeline/' . $row->id;
            }, false);

            $crud->displayAs('id', 'ID');
            $crud->displayAs('folio', 'Folio');
            $crud->displayAs('created_at', 'Fecha Creación');
            $crud->displayAs('contrato', 'Contrato');
            $crud->displayAs('unidad', 'Unidad');
            $crud->displayAs('serie', 'Serie');
            $crud->displayAs('placas', 'Placas');
            $crud->displayAs('tra_tipos_id', 'Tipo de Trámite');
            $crud->displayAs('tra_status_id', 'Estatus del Trámite');
            $crud->displayAs('empresa_gestora_id', 'Empresa Gestora');
            $crud->displayAs('gestor_id', 'Gestor');
            $crud->displayAs('cli_directo_id', 'Cliente Directo');
            $crud->displayAs('cli_directo_ejecutivo_id', 'Ejecutivo');
            $crud->displayAs('user_id', 'Asignado a');

            $crud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $crud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $crud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $crud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $crud->setDependentRelation('gestor_id', 'empresa_gestora_id', 'empresa_gestora_id');
            $crud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            $crud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $crud->setDependentRelation('cli_directo_ejecutivo_id', 'cli_directo_id', 'cli_directo_id');
            $crud->setRelation('user_id', 'users', '{firstname} {lastname}');

            // Callback ANTES de actualizar
            $controller = $this;
            $crud->callbackBeforeUpdate(function ($stateParameters) use ($controller) {
                log_message('debug', '========== crud_api callbackBeforeUpdate INICIANDO ==========');
                $tramiteId = $stateParameters->primaryKeyValue;
                $db = Database::connect();
                $oldData = $db->table('tramite')->where('id', $tramiteId)->get()->getRowArray();
                if ($oldData) {
                    session()->setTempdata('old_tramite_' . $tramiteId, $oldData, 60);
                }
                return $stateParameters;
            });

            $crud->callbackAfterUpdate(function ($stateParameters) use ($controller) {
                log_message('debug', '========== crud_api callbackAfterUpdate INICIANDO ==========');
                $tramiteId = $stateParameters->primaryKeyValue;
                log_message('debug', 'Tramite ID: ' . $tramiteId);
                
                $newData = (array) $stateParameters->data;
                log_message('debug', 'Datos recibidos: ' . json_encode($newData));
                
                $oldData = session()->getTempdata('old_tramite_' . $tramiteId);
                if ($oldData) {
                    $controller->registrarCambioConDatos($tramiteId, $oldData, $newData);
                } else {
                    $controller->registrarCambioPublic($tramiteId, $newData);
                }
                log_message('debug', 'Registro de cambio completado');
                
                return $stateParameters;
            });

            $output = $crud->render();
            
            if (isset($output->isJSONResponse) && $output->isJSONResponse) {
                header('Content-Type: application/json; charset=utf-8');
                echo $output->output;
                exit;
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en crud_api: ' . $e->getMessage());
            return $this->response->setJSON(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    // Método público para registrar cambios (llamado desde callback)
    public function registrarCambioPublic($tramiteId, $newData)
    {
        $this->registrarCambio($tramiteId, $newData);
    }

    // Método público para registrar cambios con datos viejos ya capturados
    public function registrarCambioConDatos($tramiteId, $oldData, $newData)
    {
        log_message('debug', '=== INICIANDO registrarCambioConDatos ===');
        log_message('debug', 'Tramite ID: ' . $tramiteId);
        log_message('debug', 'Old Data: ' . json_encode($oldData));
        log_message('debug', 'New Data: ' . json_encode($newData));
        
        $session = session();
        $userId = $session->get('id');
        
        // Construir nombre completo del usuario
        $firstname = $session->get('firstname') ?? '';
        $midname = $session->get('midname') ?? '';
        $lastname = $session->get('lastname') ?? '';
        $username = trim($firstname . ' ' . $midname . ' ' . $lastname);
        if (empty($username)) {
            $username = 'Usuario Desconocido';
        }

        $cambios = [];
        
        if (isset($newData['tra_tipos_id']) && $oldData['tra_tipos_id'] != $newData['tra_tipos_id']) {
            // Obtener nombres de tipos
            $oldTipo = $this->db->table('tra_tipos')->where('id', $oldData['tra_tipos_id'])->get()->getRowArray();
            $newTipo = $this->db->table('tra_tipos')->where('id', $newData['tra_tipos_id'])->get()->getRowArray();
            
            if ($oldTipo && $newTipo) {
                $cambios[] = "Tipo de Trámite: '{$oldTipo['tipo_tramite']}' → '{$newTipo['tipo_tramite']}'";
                log_message('debug', 'Cambio detectado en tipo de trámite');
            }
        }

        if (isset($newData['tra_status_id']) && $oldData['tra_status_id'] != $newData['tra_status_id']) {
            // Obtener nombres de estatus
            $oldStatus = $this->db->table('tra_status')->where('id', $oldData['tra_status_id'])->get()->getRowArray();
            $newStatus = $this->db->table('tra_status')->where('id', $newData['tra_status_id'])->get()->getRowArray();
            
            if ($oldStatus && $newStatus) {
                $cambios[] = "Estatus: '{$oldStatus['tra_status']}' → '{$newStatus['tra_status']}'";
                log_message('debug', 'Cambio detectado en estatus');
            }
        }

        if (array_key_exists('empresa_gestora_id', $newData) && (int) ($oldData['empresa_gestora_id'] ?? 0) !== (int) $newData['empresa_gestora_id']) {
            $oldEmpresa = $this->db->table('ges_empresa_gestora')->where('id', $oldData['empresa_gestora_id'] ?? 0)->get()->getRowArray();
            $newEmpresa = $this->db->table('ges_empresa_gestora')->where('id', $newData['empresa_gestora_id'])->get()->getRowArray();

            $oldEmpresaNombre = $oldEmpresa['razon_social'] ?? 'Sin empresa gestora';
            $newEmpresaNombre = $newEmpresa['razon_social'] ?? 'Sin empresa gestora';
            $cambios[] = "Empresa Gestora: '{$oldEmpresaNombre}' → '{$newEmpresaNombre}'";
            log_message('debug', 'Cambio detectado en empresa gestora');
        }

        if (array_key_exists('gestor_id', $newData) && (int) ($oldData['gestor_id'] ?? 0) !== (int) $newData['gestor_id']) {
            $oldGestor = $this->db->table('ges_gestor')->where('id', $oldData['gestor_id'] ?? 0)->get()->getRowArray();
            $newGestor = $this->db->table('ges_gestor')->where('id', $newData['gestor_id'])->get()->getRowArray();

            $oldGestorNombre = $oldGestor['nombre'] ?? 'Sin gestor';
            $newGestorNombre = $newGestor['nombre'] ?? 'Sin gestor';
            $cambios[] = "Gestor: '{$oldGestorNombre}' → '{$newGestorNombre}'";
            log_message('debug', 'Cambio detectado en gestor');
        }

        if (array_key_exists('cli_directo_id', $newData) && (int) ($oldData['cli_directo_id'] ?? 0) !== (int) $newData['cli_directo_id']) {
            $oldClienteDirecto = $this->db->table('cli_directo')->where('id', $oldData['cli_directo_id'] ?? 0)->get()->getRowArray();
            $newClienteDirecto = $this->db->table('cli_directo')->where('id', $newData['cli_directo_id'])->get()->getRowArray();

            $oldClienteDirectoNombre = $oldClienteDirecto['razon_social'] ?? 'Sin cliente directo';
            $newClienteDirectoNombre = $newClienteDirecto['razon_social'] ?? 'Sin cliente directo';
            $cambios[] = "Cliente Directo: '{$oldClienteDirectoNombre}' → '{$newClienteDirectoNombre}'";
            log_message('debug', 'Cambio detectado en cliente directo');
        }

        if (array_key_exists('cli_directo_ejecutivo_id', $newData) && (int) ($oldData['cli_directo_ejecutivo_id'] ?? 0) !== (int) $newData['cli_directo_ejecutivo_id']) {
            $oldEjecutivoCliente = $this->db->table('cli_directo_ejecutivo')->where('id', $oldData['cli_directo_ejecutivo_id'] ?? 0)->get()->getRowArray();
            $newEjecutivoCliente = $this->db->table('cli_directo_ejecutivo')->where('id', $newData['cli_directo_ejecutivo_id'])->get()->getRowArray();

            $oldEjecutivoClienteNombre = $oldEjecutivoCliente['nombre'] ?? 'Sin ejecutivo';
            $newEjecutivoClienteNombre = $newEjecutivoCliente['nombre'] ?? 'Sin ejecutivo';
            $cambios[] = "Ejecutivo del Cliente: '{$oldEjecutivoClienteNombre}' → '{$newEjecutivoClienteNombre}'";
            log_message('debug', 'Cambio detectado en ejecutivo del cliente');
        }

        if (!empty($cambios)) {
            log_message('debug', 'Total cambios detectados: ' . count($cambios));
            
            // Crear tabla si no existe
            $this->crearTablaLog();
            
            $logData = [
                'tramite_id' => $tramiteId,
                'folio' => $oldData['folio'] ?? 'N/A',
                'user_id' => $userId ?? 0,
                'username' => $username ?? 'Sistema',
                'cambios' => implode(' | ', $cambios),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            log_message('debug', 'Log Data a insertar: ' . json_encode($logData));

            // Insertar en tabla de log
            $result = $this->db->table('tramite_correccion_log')->insert($logData);
            
            if ($result) {
                log_message('debug', '✓ Registro insertado correctamente ID: ' . $this->db->insertID());
            } else {
                log_message('error', '✗ Error al insertar en tramite_correccion_log');
            }
        } else {
            log_message('debug', 'No se detectaron cambios entre old y new data');
        }
        
        log_message('debug', '=== FIN registrarCambioConDatos ===');
    }

    // Método para registrar cambios en una tabla de auditoría
    private function registrarCambio($tramiteId, $newData)
    {
        log_message('debug', '=== INICIANDO registrarCambio ===');
        log_message('debug', 'Tramite ID: ' . $tramiteId);
        log_message('debug', 'New Data: ' . json_encode($newData));
        
        $session = session();
        $userId = $session->get('id');
        
        // Construir nombre completo del usuario
        $firstname = $session->get('firstname') ?? '';
        $midname = $session->get('midname') ?? '';
        $lastname = $session->get('lastname') ?? '';
        $username = trim($firstname . ' ' . $midname . ' ' . $lastname);
        if (empty($username)) {
            $username = 'Usuario Desconocido';
        }
        
        log_message('debug', 'User ID: ' . $userId . ', Username: ' . $username);

        // Obtener datos anteriores
        $builder = $this->db->table('tramite');
        $oldData = $builder->where('id', $tramiteId)->get()->getRowArray();
        
        log_message('debug', 'Old Data: ' . json_encode($oldData));

        if (!$oldData) {
            log_message('error', 'Trámite no encontrado: ' . $tramiteId);
            return; // Trámite no encontrado
        }

        $cambios = [];
        
        if (isset($newData['tra_tipos_id']) && $oldData['tra_tipos_id'] != $newData['tra_tipos_id']) {
            // Obtener nombres de tipos
            $oldTipo = $this->db->table('tra_tipos')->where('id', $oldData['tra_tipos_id'])->get()->getRowArray();
            $newTipo = $this->db->table('tra_tipos')->where('id', $newData['tra_tipos_id'])->get()->getRowArray();
            
            if ($oldTipo && $newTipo) {
                $cambios[] = "Tipo de Trámite: '{$oldTipo['tipo_tramite']}' → '{$newTipo['tipo_tramite']}'";
            }
        }

        if (isset($newData['tra_status_id']) && $oldData['tra_status_id'] != $newData['tra_status_id']) {
            // Obtener nombres de estatus
            $oldStatus = $this->db->table('tra_status')->where('id', $oldData['tra_status_id'])->get()->getRowArray();
            $newStatus = $this->db->table('tra_status')->where('id', $newData['tra_status_id'])->get()->getRowArray();
            
            if ($oldStatus && $newStatus) {
                $cambios[] = "Estatus: '{$oldStatus['tra_status']}' → '{$newStatus['tra_status']}'";
            }
        }

        if (!empty($cambios)) {
            log_message('debug', 'Cambios detectados: ' . json_encode($cambios));
            
            // Crear tabla si no existe
            $this->crearTablaLog();
            
            $logData = [
                'tramite_id' => $tramiteId,
                'folio' => $oldData['folio'] ?? 'N/A',
                'user_id' => $userId ?? 0,
                'username' => $username ?? 'Sistema',
                'cambios' => implode(' | ', $cambios),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            log_message('debug', 'Log Data a insertar: ' . json_encode($logData));

            // Insertar en tabla de log
            $result = $this->db->table('tramite_correccion_log')->insert($logData);
            
            if ($result) {
                log_message('debug', '✓ Registro insertado correctamente en tramite_correccion_log');
            } else {
                log_message('error', '✗ Error al insertar en tramite_correccion_log');
            }
        } else {
            log_message('debug', 'No se detectaron cambios');
        }
        
        log_message('debug', '=== FIN registrarCambio ===');
    }

    // API para buscar trámites
    public function buscar()
    {
        $session = session();

        if ($resp = $this->requireCorreccionTramitesPermission(true)) {
            return $resp;
        }

        $search = $this->request->getGet('q');
        
        $builder = $this->db->table('tramite t');
        $builder->select('t.id, t.folio, t.contrato, t.unidad, t.serie, t.placas, 
                         tt.tipo_tramite, ts.tra_status, cd.razon_social as cliente');
        $builder->join('tra_tipos tt', 't.tra_tipos_id = tt.id', 'left');
        $builder->join('tra_status ts', 't.tra_status_id = ts.id', 'left');
        $builder->join('cli_directo cd', 't.cli_directo_id = cd.id', 'left');

        $filterSql = get_tramite_filter_sql($session->get('id'), 't');
        $builder->where($filterSql, null, false);
        
        if ($search) {
            $builder->groupStart()
                    ->like('t.folio', $search)
                    ->orLike('t.contrato', $search)
                    ->orLike('t.unidad', $search)
                    ->orLike('t.serie', $search)
                    ->orLike('t.placas', $search)
                    ->groupEnd();
        }
        
        $builder->orderBy('t.id', 'DESC');
        $builder->limit(50);
        
        $results = $builder->get()->getResultArray();
        
        return $this->response->setJSON($results);
    }

    // Vista de historial de cambios
    public function historial()
    {
        $session = session();

        if ($resp = $this->requireCorreccionTramitesPermission(false)) {
            return $resp;
        }
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $data['title'] = 'Historial de Correcciones';

        // Crear tabla si no existe
        $this->crearTablaLog();

        $builder = $this->db->table('tramite_correccion_log');
        $builder->orderBy('created_at', 'DESC');
        $builder->limit(500);
        
        $data['logs'] = $builder->get()->getResultArray();

        return view('deskapp/correccion_tramites/historial', $data);
    }

    // Método para crear la tabla de log si no existe
    private function crearTablaLog()
    {
        $forge = \Config\Database::forge();
        
        if (!$this->db->tableExists('tramite_correccion_log')) {
            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'tramite_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'folio' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'username' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'cambios' => [
                    'type' => 'TEXT',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('tramite_id');
            $forge->addKey('created_at');
            $forge->createTable('tramite_correccion_log', true);
        }
    }
}

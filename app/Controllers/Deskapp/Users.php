<?php

/**
 * ============================================================================
 * CONTROLADOR DE USUARIOS - CON SOPORTE MULTI-TENANCY
 * ============================================================================
 * 
 * Este controlador gestiona la administración de usuarios del sistema,
 * implementando la arquitectura de segregación de datos mediante la
 * tabla pivote cliente_user.
 * 
 * CARACTERÍSTICAS PRINCIPALES:
 * - CRUD completo de usuarios mediante GroceryCrud Enterprise
 * - Gestión de relación N:N usuarios-clientes (cliente_user)
 * - Gestión de roles y permisos
 * - Cifrado seguro de contraseñas
 * - Upload de avatares
 * 
 * ARQUITECTURA DE SEGURIDAD:
 * - Cada usuario puede estar asignado a múltiples clientes
 * - La relación N:N se gestiona mediante la tabla cliente_user
 * - Los usuarios solo pueden acceder a trámites de sus clientes asignados
 * 
 * PROPÓSITO EMPRESARIAL:
 * - Permitir que la dueña del negocio otorgue acceso a sus clientes
 * - Cada cliente tiene ejecutivos dedicados operando exclusivamente sus trámites
 * - Proteger la confidencialidad entre clientes competidores
 * 
 * ============================================================================
 */

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;
use \App\Models\UserModel;

class Users extends BaseController
{
    public function __construct() {
        // parent::__construct();
        helper(['form', 'url', 'cliente_filter', 'permissions', 'acl_guard']);
    }

    private function guardManagementAccess()
    {
        $session = session();
        $userId = $session->get('id');
        $isApi = $this->request->isAJAX() || $this->request->getGet('gc_state') !== null;

        if (!$userId) {
            if ($isApi) {
                return acl_deny('Sesión expirada', 401, null, true);
            }
            return redirect()->to('/deskapp/auth/login');
        }

        [$roles, $perms] = session_roles_perms($session);
        $canManage = has_permission('menu_roles', $perms, $roles) || has_permission('menu_permisos', $perms, $roles);

        if (!$canManage) {
            if ($isApi) {
                return acl_deny('Acceso denegado', 403, null, true);
            }
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para administrar usuarios.');
        }

        return null;
    }

    private function guardSessionOnly(bool $json = false)
    {
        $session = session();
        $userId = $session->get('id');
        if (!$userId) {
            if ($json) {
                return acl_deny('Sesión expirada', 401, null, true);
            }
            return redirect()->to('/deskapp/auth/login');
        }
        return null;
    }

    private function hasDebugRoleAccess(array $roles, array $perms): bool
    {
        if (in_array('debug_perm_audit_tags', normalize_permission_list($perms), true)) {
            return true;
        }

        foreach (normalize_permission_list($roles) as $roleName) {
            if (normalize_role_key($roleName) === 'debug') {
                return true;
            }
        }

        return false;
    }

    private function getDebugMarkerRoleName(array $roles): string
    {
        foreach (normalize_permission_list($roles) as $roleName) {
            if (normalize_role_key($roleName) === 'debug') {
                return (string) $roleName;
            }
        }

        return 'Debug';
    }

    private function applyDebugRoleToSession($session, UserModel $userModel, int $roleId, string $debugRoleName): bool
    {
        $role = $userModel->findRoleById($roleId);
        if ($role === null) {
            return false;
        }

        $selectedRoleName = trim((string) ($role['role_name'] ?? ''));
        if ($selectedRoleName === '' || normalize_role_key($selectedRoleName) === 'debug') {
            return false;
        }

        $effectivePermissions = $userModel->getRolePermissionsById($roleId);
        if (!in_array('debug_perm_audit_tags', $effectivePermissions, true)) {
            $effectivePermissions[] = 'debug_perm_audit_tags';
        }
        $effectivePermissions = array_values(array_unique($effectivePermissions));
        sort($effectivePermissions, SORT_STRING);

        $effectiveRoles = [$selectedRoleName];
        if (normalize_role_key($debugRoleName) !== normalize_role_key($selectedRoleName)) {
            $effectiveRoles[] = $debugRoleName;
        }

        $session->set([
            'user_roles' => $effectiveRoles,
            'user_permissions' => $effectivePermissions,
            'debug_selected_role_id' => $roleId,
            'debug_selected_role_name' => $selectedRoleName,
            'debug_can_switch_roles' => true,
            'auth_is_debug' => true,
            'auth_debug_role_name' => $debugRoleName,
        ]);

        return true;
    }

    private function ensureDebugRoleContext($session, UserModel $userModel): array
    {
        $userId = (int) ($session->get('id') ?? 0);
        if ($userId <= 0) {
            return [false, [], 0, ''];
        }

        $authRoles = $userModel->getUserRoles($userId);
        $authPerms = $userModel->getUserPermissions($userId);
        $session->set('auth_user_roles', $authRoles);
        $session->set('auth_user_permissions', $authPerms);

        $sessionDebugFlag = (bool) ($session->get('auth_is_debug') || $session->get('debug_can_switch_roles'));
        $isDebugUser = $sessionDebugFlag || $this->hasDebugRoleAccess($authRoles, $authPerms);
        $session->set('auth_is_debug', $isDebugUser);

        if (!$isDebugUser) {
            $session->set('debug_can_switch_roles', false);
            return [false, [], 0, ''];
        }

        $debugRoleName = $this->getDebugMarkerRoleName($authRoles);
        $session->set('auth_debug_role_name', $debugRoleName);
        $session->set('debug_can_switch_roles', true);

        $roleOptions = $userModel->getAvailableRoles(true);
        $selectedRoleId = (int) ($session->get('debug_selected_role_id') ?? 0);
        $selectedRoleName = (string) ($session->get('debug_selected_role_name') ?? '');
        $selectedRole = $selectedRoleId > 0 ? $userModel->findRoleById($selectedRoleId) : null;

        if ($selectedRole === null || normalize_role_key((string) ($selectedRole['role_name'] ?? '')) === 'debug') {
            $selectedRole = $userModel->findRoleByNormalizedKey('admin');
            if ($selectedRole === null) {
                foreach ($roleOptions as $availableRole) {
                    if (normalize_role_key((string) ($availableRole['role_name'] ?? '')) !== 'debug') {
                        $selectedRole = $availableRole;
                        break;
                    }
                }
            }

            if ($selectedRole !== null) {
                $this->applyDebugRoleToSession($session, $userModel, (int) ($selectedRole['id'] ?? 0), $debugRoleName);
                $selectedRoleId = (int) ($selectedRole['id'] ?? 0);
                $selectedRoleName = (string) ($selectedRole['role_name'] ?? '');
            }
        }

        $roleOptions = array_values(array_filter($roleOptions, static function (array $role): bool {
            return normalize_role_key((string) ($role['role_name'] ?? '')) !== 'debug';
        }));

        return [true, $roleOptions, $selectedRoleId, $selectedRoleName];
    }

    public function index()
    {
        if ($resp = $this->guardManagementAccess()) {
            return $resp;
        }

        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];
        
        return $this->_example_output($output);
    }

    /**
     * Método principal para gestión de usuarios
     * 
     * IMPORTANTE - RELACIÓN CLIENTE_USER:
     * Este método configura la relación N:N entre usuarios y clientes mediante
     * la tabla pivote cliente_user. Esta relación es CRÍTICA para la arquitectura
     * de seguridad del sistema.
     * 
     * FLUJO:
     * 1. Configura GroceryCrud para tabla users
     * 2. Establece relación N:N con roles (us_user_roles)
     * 3. Establece relación N:N con clientes (cliente_user) ← CRÍTICO
     * 4. Configura callbacks de cifrado de contraseñas
     * 5. Configura callbacks de auditoría (logs)
     * 
     * SEGREGACIÓN DE DATOS:
     * Al asignar clientes a un usuario mediante el campo "clientes", se crean
     * registros en cliente_user que determinan qué trámites puede ver el usuario.
     */
    public function users(){
        try {
            if ($resp = $this->guardManagementAccess()) {
                return $resp;
            }

            helper('acl_version');

            $db2 = $this->_getDbData();
            $session = session();
            $session->get('user_permissions');

            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $users_crud = $this->_getGroceryCrudEnterprise();
            // JS extra para toggle AJAX de status en el grid (click directo en Activo/Inactivo)
            // Nota: En esta versión de GroceryCRUD hay un mismatch: GroceryCrud::setJsFile() llama Layout::setJsFile() (inexistente).
            // Por eso lo cargamos directo desde el layout.
            $layout = $users_crud->getLayout();
            if (is_object($layout) && method_exists($layout, 'setJavaScriptFile')) {
                // En este proyecto los assets se sirven como /public/assets/...
                $layout->setJavaScriptFile(base_url('/public/assets/js/users_status_grid_ajax.js'));
            }
            $users_crud->setTable('users');
            $users_crud->setSubject('Usuario', 'Usuarios');
            $users_crud->defaultOrdering('users.id', 'desc');
            
            // Callback para cifrar la contraseña antes de insertar
            $users_crud->columns(['id', 'status', 'username', 'firstname', 'midname', 'lastname', 'email', 'avatar', 'roles', 'clientes']);
            // Quitar status del form: ahora se cambia directo en el grid con AJAX
            $users_crud->fields(['username', 'firstname', 'midname', 'lastname', 'email', 'phone', 'avatar', 'password', 'roles', 'clientes']);
            $users_crud->fieldType('password', 'password'); // Indica que el campo password es de tipo password
            $users_crud->unsetDeleteMultiple();
            $users_crud->setActionButton('Clonar', 'fas fa-clone', function ($row) {
                return '/users/users/clone/' . $row->id;
            }, false);

            $users_crud->setActionButton('Mapa', 'fas fa-map', function ($row) {
                return '/deskapp/users/users_mapa/' . $row->id;
            }, false);
            $users_crud->displayAs('username','Username');
            $users_crud->displayAs('firstname','Nombre');
            $users_crud->displayAs('midname','Apellido Paterno');
            $users_crud->displayAs('lastname','Apellido Materno');
            $users_crud->displayAs('email','Correo');
            $users_crud->displayAs('phone','Teléfono');
            $users_crud->displayAs('password','Contraseña');
            $users_crud->displayAs('avatar','Foto');

            $uploadValidations = [
                'maxUploadSize' => '20M',
                'minUploadSize' => '1K',
                'allowedFileTypes' => [
                    'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf', 'xml'
                ]
            ];
    
            $users_crud->setFieldUpload(
                'avatar', 
                'assets/uploads/avatars/', 
                '/assets/uploads/avatars/', 
                $uploadValidations
            );

            // Configura la relación N to N
            $users_crud->setRelationNtoN(
                'roles',              // El nombre del campo que se usará en el formulario
                'us_user_roles',      // Tabla de unión
                'us_roles',           // Tabla de destino
                'user_id',            // Llave foránea en la tabla de unión que apunta a la tabla principal ('users')
                'role_id',            // Llave foránea en la tabla de unión que apunta a la tabla relacionada ('us_roles')
                'role_name'           // Campo en la tabla relacionada que se desea mostrar en el multiselect
            );

            // Encriptar la contraseña antes de guardarla en la base de datos al insertar un nuevo registro
            $users_crud->callbackAddForm(function ($data){
                $session = session();
                $myid = $session->get('id');
                $data['user_id'] = $myid;
                // $data['username'] = 'test';
                // $data['firstname'] = 'test';
                // $data['midname'] = 'test';
                // $data['lastname'] = "test";
                // $data['email'] = "test@test.com";
                // $data['phone'] = "12345678";
                // $data['password'] = "12345678";

                return $data;
            });
            
            // Configurar campos para edición (sin password)
            // status se cambia desde el grid
            $users_crud->editFields(['username', 'firstname', 'midname', 'lastname', 'email', 'phone', 'avatar', 'roles', 'clientes']);

            // Columna status como badge clickeable
            $users_crud->callbackColumn('status', function ($value, $row) {
                $userId = (int)($row->id ?? 0);
                $username = (string)($row->username ?? '');
                $status = (int)$value === 1;
                $cls = $status ? 'badge badge-success' : 'badge badge-secondary';
                $txt = $status ? 'Activo' : 'Inactivo';
                $title = $status ? 'Click para desactivar' : 'Click para activar';

                return '<a href="#"'
                    . ' class="js-toggle-user-status ' . $cls . '"'
                    . ' data-user-id="' . $userId . '"'
                    . ' data-username="' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '"'
                    . ' data-status="' . ($status ? '1' : '0') . '"'
                    . ' title="' . $title . '"'
                    . ' style="cursor:pointer;">'
                    . $txt
                    . '</a>';
            });
            
            $users_crud->callbackBeforeInsert(function ($stateParameters) {
                $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
                // Si no viene status (porque ya no está en el form), por defecto activo
                if (!isset($stateParameters->data['status'])) {
                    $stateParameters->data['status'] = 1;
                }
                // Comprueba y encripta la contraseña
                if (isset($stateParameters->data['password']) && !empty($stateParameters->data['password'])) {
                    $stateParameters->data['password'] = password_hash($stateParameters->data['password'], PASSWORD_DEFAULT);
                }
                
                return $stateParameters;
            });

            // ========================================================================
            // CONFIGURACIÓN DE RELACIÓN N:N CON CLIENTES (MULTI-TENANCY)
            // ========================================================================
            // 
            // Esta relación es la BASE de la arquitectura de segregación de datos.
            // 
            // FLUJO:
            // 1. Usuario selecciona uno o más clientes en el formulario
            // 2. Se crean/actualizan registros en la tabla pivote cliente_user
            // 3. Estos registros determinan qué trámites puede ver el usuario
            // 
            // EJEMPLO:
            // Si usuario ID=5 se asigna a clientes [10, 15, 20]:
            //   - cliente_user: (5, 10), (5, 15), (5, 20)
            //   - El usuario solo verá trámites de cli_directo vinculados a clientes 10, 15, 20
            // 
            // SEGURIDAD:
            // - Previene que usuarios vean información de otros clientes
            // - Protege confidencialidad empresarial
            // - Permite modelo de negocio multi-cliente
            // ========================================================================
            
            $users_crud->setRelationNtoN(
                'clientes',        // Nombre del campo que se usará en el formulario
                'cliente_user',    // Tabla de unión (pivote) ← TABLA CRÍTICA
                'cliente',         // Tabla de destino (clientes "maestros")
                'user_id',         // Llave foránea en la tabla de unión que apunta a la tabla principal ('users')
                'cliente_id',      // Llave foránea en la tabla de unión que apunta a la tabla relacionada ('cliente')
                'razon_social'     // Campo en la tabla relacionada que se desea mostrar en el multiselect
            );

            // Callback column para mostrar los clientes asociados con un usuario
            $users_crud->callbackColumn('clientes', function ($value, $row) {
                $db = \Config\Database::connect();
                $builder = $db->table('cliente_user');
                $builder->select('cliente.razon_social');
                $builder->join('cliente', 'cliente_user.cliente_id = cliente.id');
                $builder->where('cliente_user.user_id', $row->id);
                $clientes = $builder->get()->getResult();

                $clienteNombres = array_map(function($cliente) {
                    return $cliente->razon_social;
                }, $clientes);

                return implode(', ', $clienteNombres); // Mostrar los nombres de los clientes separados por comas
            });

            // Encriptar la contraseña antes de guardarla en la base de datos al actualizar un registro existente
            $users_crud->callbackBeforeUpdate(function ($stateParameters) {
                // Leer directamente el campo status (POST o JSON)
                $request = \Config\Services::request();
                $postStatus = $request->getPost('status');
                $hasStatus = $postStatus !== null;
                if (!$hasStatus) {
                    $postStatus = $request->getVar('status');
                    $hasStatus = $postStatus !== null;
                }
                if (!$hasStatus) {
                    $contentType = strtolower($request->getHeaderLine('Content-Type'));
                    $rawBody = (string) $request->getBody();

                    if (strpos($contentType, 'application/json') !== false && $rawBody !== '') {
                        $jsonBody = json_decode($rawBody, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonBody) && array_key_exists('status', $jsonBody)) {
                            $postStatus = $jsonBody['status'];
                            $hasStatus = true;
                        }
                    }
                }
                
                log_message('error', '========== CALLBACK BEFORE UPDATE ==========');
                log_message('error', 'POST status directo: ' . ($postStatus ?? 'NULL'));
                log_message('error', 'Data status de Grocery CRUD: ' . ($stateParameters->data['status'] ?? 'NULL'));
                
                // Si existe status en el POST, usarlo (tiene prioridad)
                if ($hasStatus) {
                    $stateParameters->data['status'] = (int)$postStatus;
                    log_message('error', '✅ Status actualizado desde POST: ' . $stateParameters->data['status']);
                } else {
                    log_message('error', '⚠️ No se encontró status en POST directo, usando valor de Grocery CRUD');
                }
                
                // Actualizar timestamp
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
                
                // Encriptar contraseña si se proporciona
                if (isset($stateParameters->data['password']) && !empty($stateParameters->data['password'])) {
                    $stateParameters->data['password'] = password_hash($stateParameters->data['password'], PASSWORD_DEFAULT);
                }
                
                log_message('error', 'Status FINAL que se guardará: ' . ($stateParameters->data['status'] ?? 'NULL'));
                
                return $stateParameters;
            });
             
            $users_crud->callbackAfterUpdate(function ($stateParameters) use ($users_crud) {
                // En este CRUD se pueden modificar relaciones N:N (roles/clientes).
                // GroceryCRUD puede enviar las relaciones con nombres/formatos distintos, así que
                // bumpeamos SIEMPRE en update para que el usuario afectado refresque con recarga.
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }

                // Verificar qué se guardó realmente en la base de datos
                $db = \Config\Database::connect();
                $lastQuery = $db->getLastQuery();
                
                // Leer directamente de la base de datos
                $userId = $stateParameters->primaryKeyValue;
                $query = $db->query("SELECT id, username, status, updated_at FROM users WHERE id = ?", [$userId]);
                $userFromDb = $query->getRow();
                
                log_message('error', '========== AFTER UPDATE ==========');
                log_message('error', 'Query ejecutada: ' . $lastQuery);
                log_message('error', 'Usuario en DB después del update:');
                log_message('error', json_encode($userFromDb));
                log_message('error', '==========================================');
                
                // Guardar en sesión para mostrar
                session()->set('last_query', (string)$lastQuery);
                session()->set('last_operation', 'UPDATE');
                session()->set('last_primary_key', $userId);
                session()->set('db_status', $userFromDb->status ?? 'NULL');
                
                $tableName = $users_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            
            $users_crud->callbackAfterInsert(function ($stateParameters) use ($users_crud) {
                // Crear usuario también puede incluir N:N (roles/clientes) y/o afectar permisos.
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }

                // Log de la query ejecutada
                $db = \Config\Database::connect();
                $lastQuery = $db->getLastQuery();
                log_message('debug', '========== AFTER INSERT ==========');
                log_message('debug', 'SQL Query ejecutada: ' . $lastQuery);
                log_message('debug', '==========================================');
                
                $tableName = $users_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            
            $users_crud->callbackAfterDelete(function ($stateParameters) use ($users_crud) {
				// Si se borra un usuario, su relación cliente_user/roles/permisos se afecta.
				if (function_exists('acl_bump_version')) {
					acl_bump_version();
				}
                $tableName = $users_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });

            $users_output = $users_crud->render();
            $data['title'] = 'Gestión de Usuarios';
            $data['description'] = 'Administra los usuarios del sistema, sus roles y permisos de acceso';
            $final_output = array_merge((array)$users_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Toggle AJAX para status del usuario.
     * POST: user_id, status (0|1)
     */
    public function toggle_status()
    {
        if ($resp = $this->guardManagementAccess()) {
            return $resp;
        }

        if (!$this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['ok' => false, 'message' => 'Solicitud inválida.']);
        }

        $userId = (int) $this->request->getPost('user_id');
        $statusRaw = $this->request->getPost('status');
        $statusInt = is_numeric($statusRaw) ? (int) $statusRaw : -1;

        if ($userId <= 0 || !in_array($statusInt, [0, 1], true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'message' => 'Parámetros inválidos.']);
        }

        $db = \Config\Database::connect();

        $exists = $db->table('users')->select('id')->where('id', $userId)->get()->getRowArray();
        if (empty($exists)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['ok' => false, 'message' => 'Usuario no encontrado.']);
        }

        $data = ['status' => $statusInt];
        if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('updated_at', 'users')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $db->table('users')->where('id', $userId)->update($data);

        return $this->response->setJSON([
            'ok' => true,
            'user_id' => $userId,
            'status' => $statusInt,
        ]);
    }

    public function users_mapa($userId = null)
    {
        if ($resp = $this->guardManagementAccess()) {
            return $resp;
        }

        $userId = (int) ($userId ?? 0);
        if ($userId <= 0) {
            return redirect()->to('/deskapp/users/users')->with('error', 'Usuario inválido.');
        }

        $db = \Config\Database::connect();
        $session = session();
        $userModel = new UserModel($db);

        $user = $userModel->find($userId);
        if (empty($user)) {
            return redirect()->to('/deskapp/users/users')->with('error', 'Usuario no encontrado.');
        }

        $userRoles = $userModel->getUserRoles($userId);
        // Permisos efectivos (roles + overrides por usuario).
        $userPerms = $userModel->getUserPermissions($userId);
        $userRoles = normalize_permission_list($userRoles);
        $userPerms = normalize_permission_list($userPerms);

        // Roles tal cual están en DB (para comparar exacto vs mapeos/reglas)
        $userRolesDb = [];
        try {
            $userRolesDb = $db->table('us_user_roles as ur')
                ->select('r.id as role_id, r.role_name, r.description')
                ->join('us_roles as r', 'ur.role_id = r.id', 'inner')
                ->where('ur.user_id', $userId)
                ->orderBy('r.role_name', 'asc')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            $userRolesDb = [];
        }

        // Permisos por rol (tal cual DB): us_role_permissions -> us_permissions
        $rolePermissionsDb = [];
        try {
            $roleIds = [];
            foreach ($userRolesDb as $r) {
                $rid = (int)($r['role_id'] ?? 0);
                if ($rid > 0) {
                    $roleIds[$rid] = $rid;
                }
            }
            $roleIds = array_values($roleIds);

            if (!empty($roleIds)) {
                $rpBuilder = $db->table('us_role_permissions as rp')
                    ->select('r.id as role_id, r.role_name, p.permission_name, p.description')
                    ->join('us_roles as r', 'rp.role_id = r.id', 'inner')
                    ->join('us_permissions as p', 'rp.permission_id = p.id', 'inner')
                    ->whereIn('rp.role_id', $roleIds);

                // Si existe la columna status, solo muestra permisos activos.
                if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
                    $rpBuilder->where('p.status', 1);
                }

                $rows = $rpBuilder
                    ->orderBy('r.role_name', 'asc')
                    ->orderBy('p.permission_name', 'asc')
                    ->get()
                    ->getResultArray();

                foreach ($rows as $row) {
                    $rid = (int)($row['role_id'] ?? 0);
                    if ($rid <= 0) {
                        continue;
                    }

                    if (!isset($rolePermissionsDb[$rid])) {
                        $rolePermissionsDb[$rid] = [
                            'role_id' => $rid,
                            'role_name' => (string)($row['role_name'] ?? ''),
                            'permissions' => [],
                        ];
                    }

                    $permName = trim((string)($row['permission_name'] ?? ''));
                    if ($permName === '') {
                        continue;
                    }

                    $rolePermissionsDb[$rid]['permissions'][] = [
                        'permission_name' => $permName,
                        'description' => (string)($row['description'] ?? ''),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $rolePermissionsDb = [];
        }

        // Set rápido: permisos base por rol (para distinguir “Rol” vs “Usuario”).
        $rolePermSet = [];
        foreach (($rolePermissionsDb ?? []) as $ri) {
            foreach (($ri['permissions'] ?? []) as $p) {
                $pn = trim((string)($p['permission_name'] ?? ''));
                if ($pn !== '') {
                    $rolePermSet[$pn] = true;
                }
            }
        }

        // Overrides por usuario (para mostrar origen + permitir toggles)
        $userPermissionOverrides = [];
        try {
            $ovBuilder = $db->table('us_user_permissions as up')
                ->select('p.permission_name, up.granted')
                ->join('us_permissions as p', 'p.id = up.permission_id', 'inner')
                ->where('up.user_id', $userId);

            if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
                $ovBuilder->where('p.status', 1);
            }

            $rows = $ovBuilder->orderBy('p.permission_name', 'asc')->get()->getResultArray();
            foreach ($rows as $row) {
                $pn = trim((string)($row['permission_name'] ?? ''));
                if ($pn === '') {
                    continue;
                }
                $userPermissionOverrides[$pn] = ((int)($row['granted'] ?? 1) === 1) ? 1 : 0;
            }
        } catch (\Throwable $e) {
            $userPermissionOverrides = [];
        }

        // --------------------------------------------------------------------
        // Catálogo completo de permisos + descripciones desde DB
        // --------------------------------------------------------------------
        $permissionDescription = [];
        $permissionUiArea = [];
        try {
            $permBuilder = $db->table('us_permissions')
                ->select('permission_name, description');

            // Si existe la columna status, el mapa solo lista permisos activos.
            if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
                $permBuilder->where('status', 1);
            }

            $permRows = $permBuilder
                ->orderBy('permission_name', 'asc')
                ->get()
                ->getResultArray();

            foreach ($permRows as $row) {
                $permName = trim((string)($row['permission_name'] ?? ''));
                if ($permName === '') {
                    continue;
                }
                $permissionDescription[$permName] = (string)($row['description'] ?? '');

                $p = strtolower($permName);
                if (strpos($p, 'menu_') === 0) {
                    $permissionUiArea[$permName] = 'Menú';
                } elseif (strpos($p, 'header_') === 0 || $p === 'header_buttons') {
                    $permissionUiArea[$permName] = 'Header';
                } elseif (strpos($p, 'important_') === 0) {
                    $permissionUiArea[$permName] = 'Acceso rápido';
                } elseif (strpos($p, 'section_') === 0) {
                    $permissionUiArea[$permName] = 'Sección';
                } else {
                    $permissionUiArea[$permName] = 'Acción';
                }
            }
        } catch (\Throwable $e) {
            // Si falla el catálogo, seguimos con el mapeo estático (fallback).
            $permissionDescription = [];
            $permissionUiArea = [];
        }

        // --------------------------------------------------------------------
        // Config base por paso: roles y permisos semilla (fallback)
        // --------------------------------------------------------------------
        $steps = [
            1 => [
                'name' => 'Paso 1: Datos del trámite',
                // Nota UI: Starter “mueve” Paso 1 en el sentido de iniciar/crear el trámite.
                'roles_can_move' => ['Starter', 'Executer', 'Admin', 'Super Admin'],
                'permissions' => [
                    'read_tramite', 'listar_tramite', 'create_tramite', 'editar_tramite', 'delete_tramite',
                    'export_tramite', 'print_tramite', 'clone_tramite',
                    'section_inicial_datos',
                    'editar_tramite_principal', 'editar_tramite_asociado', 'delete_tramite_asociado',
                ],
            ],
            2 => [
                'name' => 'Paso 2: Gestor y Empresa',
                'roles_can_move' => ['Executer', 'Admin', 'Super Admin'],
                'permissions' => ['section_asigna_gestor', 'tramite_view_gestor', 'editar_tramite'],
            ],
            3 => [
                'name' => 'Paso 3: Pago de derechos',
                'roles_can_move' => ['Executer', 'Admin', 'Super Admin'],
                'permissions' => ['section_pago_derechos', 'section_linea_captura', 'section_documentos_pago', 'editar_tramite'],
            ],
            4 => [
                'name' => 'Paso 4: Pago a gestor',
                'roles_can_move' => ['Authorizer Editor', 'Authorizer Simple', 'Admin', 'Super Admin'],
                'permissions' => ['section_pago_gestor', 'editar_pago_gestor', 'important_pasar_a_pagos', 'editar_tramite'],
            ],
            5 => [
                'name' => 'Paso 5: Cobro a cliente',
                'roles_can_move' => ['Closer', 'Admin', 'Super Admin'],
                'permissions' => [
                    'section_final_costos',
                    'read_final_tramite', 'listar_final_tramite',
                    'export_final_tramite', 'print_final_tramite',
                    'important_concluir_tramite', 'important_cancelar_tramite',
                    'editar_tramite',
                ],
            ],
        ];

        // --------------------------------------------------------------------
        // “Mapear todo”: incorporar permisos desde catálogo DB a pasos 1-5.
        // Esto incluye permisos usados por secciones/detalles de GroceryCRUD.
        // --------------------------------------------------------------------
        $adminPermissions = [];
        if (!empty($permissionDescription)) {
            $candidatePerms = array_keys($permissionDescription);

            // Índices para evitar duplicados
            $stepPermSet = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
            foreach ($steps as $sNum => $cfg) {
                foreach (($cfg['permissions'] ?? []) as $p) {
                    $stepPermSet[$sNum][(string)$p] = true;
                }
            }

            $assignStepForPerm = static function (string $permName): ?int {
                $p = strtolower($permName);

                $contains = static function (string $haystack, string $needle): bool {
                    return $needle !== '' && strpos($haystack, $needle) !== false;
                };

                // ----------------------------------------------------------------
                // Más específico → menos específico
                // ----------------------------------------------------------------

                // Paso 5: proceso final / cobro / concluido
                if (
                    $contains($p, 'section_final_costos') ||
                    $contains($p, 'final_tramite') ||
                    $contains($p, 'cobro_cliente') ||
                    $contains($p, 'final_costos') ||
                    $contains($p, 'concluido') ||
                    $contains($p, 'concluir') ||
                    $contains($p, 'cancelar')
                ) {
                    return 5;
                }

                // Paso 4: pago gestor / autorizaciones
                if (
                    $contains($p, 'section_pago_gestor') ||
                    $contains($p, 'pago_gestor') ||
                    $contains($p, 'editar_pago_gestor') ||
                    $contains($p, 'pasar_a_pagos')
                ) {
                    return 4;
                }

                // Paso 3: pago derechos / línea / docs de pago
                if (
                    $contains($p, 'section_pago_derechos') ||
                    $contains($p, 'pago_derechos') ||
                    $contains($p, 'linea_captura') ||
                    $contains($p, 'documentos_pago')
                ) {
                    return 3;
                }

                // Paso 2: gestor / asignaciones
                if (
                    $contains($p, 'section_asigna_gestor') ||
                    $contains($p, 'tramite_view_gestor')
                ) {
                    return 2;
                }

                // Paso 1: base del trámite
                if (
                    $contains($p, 'read_tramite') ||
                    $contains($p, 'listar_tramite') ||
                    $contains($p, 'create_tramite') ||
                    $contains($p, 'editar_tramite') ||
                    $contains($p, 'delete_tramite') ||
                    $contains($p, 'export_tramite') ||
                    $contains($p, 'print_tramite') ||
                    $contains($p, 'clone_tramite') ||
                    $contains($p, 'section_inicial_datos') ||
                    $contains($p, 'editar_tramite_principal') ||
                    $contains($p, 'editar_tramite_asociado')
                ) {
                    return 1;
                }

                // ----------------------------------------------------------------
                // Prefijos típicos del sistema
                // ----------------------------------------------------------------

                // Menús: intentar ponerlos en el paso más cercano
                if (strpos($p, 'menu_') === 0) {
                    // Solo menús del flujo de trámites viven en pasos.
                    if ($contains($p, 'proceso_final')) {
                        return 5;
                    }
                    if ($contains($p, 'tramites')) {
                        return 1;
                    }
                    return null;
                }

                // Listados: final vs normal
                if (strpos($p, 'listar_') === 0) {
                    if ($contains($p, 'final_tramite') || $contains($p, 'concluido')) {
                        return 5;
                    }
                    if ($contains($p, 'tramite')) {
                        return 1;
                    }
                    return null;
                }

                // Export/print: final vs normal
                if (strpos($p, 'export_') === 0 || strpos($p, 'print_') === 0) {
                    if ($contains($p, 'final_tramite')) {
                        return 5;
                    }
                    if ($contains($p, 'tramite')) {
                        return 1;
                    }
                    return null;
                }

                // Default: no pertenece a pasos 1-5 (se mostrará en Admin permisos)
                return null;
            };

            foreach ($candidatePerms as $permName) {
                if ($permName === '') {
                    continue;
                }
                $stepNum = $assignStepForPerm($permName);
                if ($stepNum === null) {
                    $adminPermissions[$permName] = true;
                    continue;
                }
                $stepPermSet[$stepNum][$permName] = true;
            }

            // Rehidratar en el arreglo final, ordenado
            foreach ($steps as $sNum => &$cfg) {
                $perms = array_keys($stepPermSet[$sNum]);
                sort($perms, SORT_STRING);
                $cfg['permissions'] = $perms;
            }
            unset($cfg);
        }

        $adminPermissions = array_keys($adminPermissions);
        sort($adminPermissions, SORT_STRING);

        // UI-only: “Puede mover este paso” se define por permisos efectivos (no por rol).
        // Super Admin queda cubierto por el bypass dentro de has_permission().
        $canMoveStep = [];
        foreach ($steps as $stepNum => $_cfg) {
            $stepNum = (int) $stepNum;
            switch ($stepNum) {
                case 1:
                    $canMoveStep[$stepNum] = has_permission('create_tramite', $userPerms, $userRoles)
                        || can_write_tramite_step(1, $userPerms, $userRoles);
                    break;
                case 2:
                    $canMoveStep[$stepNum] = can_write_tramite_step(2, $userPerms, $userRoles);
                    break;
                case 3:
                    $canMoveStep[$stepNum] = can_write_tramite_step(3, $userPerms, $userRoles);
                    break;
                case 4:
                    $canMoveStep[$stepNum] = has_permission('section_pago_gestor', $userPerms, $userRoles)
                        && has_permission('editar_pago_gestor', $userPerms, $userRoles);
                    break;
                case 5:
                    $canMoveStep[$stepNum] = has_permission('section_final_costos', $userPerms, $userRoles);
                    break;
                default:
                    $canMoveStep[$stepNum] = false;
                    break;
            }
        }

        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'title' => 'Mapa de permisos',
            'description' => 'Mapa por zonas (pasos 1 a 5) y sección de permisos administrativos, con permisos en verde/gris.',
            'target_user' => $user,
            'target_roles' => $userRoles,
            'target_roles_db' => $userRolesDb,
            'target_role_permissions_db' => $rolePermissionsDb,
            'target_permissions' => $userPerms,
            'target_role_permission_set' => $rolePermSet,
            'target_user_permission_overrides' => $userPermissionOverrides,
            'steps' => $steps,
            'admin_permissions' => $adminPermissions,
            'permission_descriptions' => $permissionDescription,
            'permission_ui_area' => $permissionUiArea,
            'can_move_step' => $canMoveStep,
            'can_authorize_target' => can_authorize_tramite($userRoles, $userPerms),
            'toggle_user_permission_url' => (string) base_url('deskapp/users/toggle_user_permission'),
        ];

        return view('deskapp/users/users_mapa', $data);
    }

    /**
     * Toggle de override user-permiso (extras y denegaciones) sin modificar roles.
     * Regla:
     * - BaseGranted (por rol) + Override (user): Effective
     * - Si deseas activar y ya viene por rol, se elimina override (si existía).
     * - Si deseas desactivar y NO viene por rol, se elimina override (si existía).
     * - En los demás casos, se upsertea override granted=1/0.
     */
    public function toggle_user_permission()
    {
        if ($resp = $this->guardManagementAccess()) {
            return $resp;
        }

        helper('acl_version');

        if (!$this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['ok' => false, 'message' => 'Solicitud inválida.']);
        }

        $userId = (int) $this->request->getPost('user_id');
        $permissionName = trim((string) $this->request->getPost('permission_name'));
        $desiredRaw = $this->request->getPost('granted');
        $desired = is_numeric($desiredRaw) ? (int) $desiredRaw : -1;

        if ($userId <= 0 || $permissionName === '' || !in_array($desired, [0, 1], true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'message' => 'Parámetros inválidos.']);
        }

        $db = \Config\Database::connect();

        $userExists = $db->table('users')->select('id')->where('id', $userId)->get()->getRowArray();
        if (empty($userExists)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['ok' => false, 'message' => 'Usuario no encontrado.']);
        }

        // Resolver permission_id
        $permBuilder = $db->table('us_permissions')->select('id, permission_name');
        if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
            $permBuilder->where('status', 1);
        }
        $permRow = $permBuilder->where('permission_name', $permissionName)->get()->getRowArray();
        if (empty($permRow)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['ok' => false, 'message' => 'Permiso no encontrado o inactivo.']);
        }
        $permissionId = (int) ($permRow['id'] ?? 0);
        if ($permissionId <= 0) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'message' => 'Permiso inválido.']);
        }

        // BaseGranted por rol (DB)
        $baseGranted = false;
        try {
            $baseQ = $db->table('us_user_roles as ur')
                ->select('rp.permission_id')
                ->join('us_role_permissions as rp', 'rp.role_id = ur.role_id', 'inner')
                ->where('ur.user_id', $userId)
                ->where('rp.permission_id', $permissionId)
                ->limit(1)
                ->get()
                ->getRowArray();
            $baseGranted = !empty($baseQ);
        } catch (\Throwable $e) {
            $baseGranted = false;
        }

        // Override actual
        $overrideRow = null;
        try {
            $overrideRow = $db->table('us_user_permissions')
                ->select('id, granted')
                ->where('user_id', $userId)
                ->where('permission_id', $permissionId)
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            $overrideRow = null;
        }

        $overrideExists = !empty($overrideRow);
        $overrideId = (int) ($overrideRow['id'] ?? 0);
        $currentOverrideGranted = $overrideExists ? (int) ($overrideRow['granted'] ?? 1) : null;

        $action = 'none';
        $finalOverride = null; // null|0|1

        // Regla de minimización de overrides (mantener consistencia con roles)
        if ($desired === 1) {
            if ($baseGranted) {
                // Ya viene por rol: quitar override si existía (especialmente si era deny)
                if ($overrideExists) {
                    $db->table('us_user_permissions')->where('id', $overrideId)->delete();
                    $action = 'deleted';
                }
                $finalOverride = null;
            } else {
                // No viene por rol: necesitamos override allow
                $data = [
                    'user_id' => $userId,
                    'permission_id' => $permissionId,
                    'granted' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if (!$overrideExists) {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $db->table('us_user_permissions')->insert($data);
                    $action = 'inserted';
                } else {
                    $db->table('us_user_permissions')->where('id', $overrideId)->update($data);
                    $action = ($currentOverrideGranted === 1) ? 'kept' : 'updated';
                }
                $finalOverride = 1;
            }
        } else {
            // desired === 0
            if (!$baseGranted) {
                // No viene por rol: quitar override si existía (especialmente si era allow)
                if ($overrideExists) {
                    $db->table('us_user_permissions')->where('id', $overrideId)->delete();
                    $action = 'deleted';
                }
                $finalOverride = null;
            } else {
                // Viene por rol: necesitamos override deny
                $data = [
                    'user_id' => $userId,
                    'permission_id' => $permissionId,
                    'granted' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if (!$overrideExists) {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $db->table('us_user_permissions')->insert($data);
                    $action = 'inserted';
                } else {
                    $db->table('us_user_permissions')->where('id', $overrideId)->update($data);
                    $action = ($currentOverrideGranted === 0) ? 'kept' : 'updated';
                }
                $finalOverride = 0;
            }
        }

        $effectiveGranted = ($finalOverride !== null) ? ($finalOverride === 1) : $baseGranted;
        $source = 'none';
        if ($finalOverride !== null) {
            $source = ($finalOverride === 1) ? 'user_allow' : 'user_deny';
        } else {
            $source = $baseGranted ? 'role' : 'none';
        }

        // Invalidar cache ACL en sesión (solo si hubo cambio real en BD)
        if (in_array($action, ['inserted', 'updated', 'deleted'], true)) {
            if (function_exists('acl_bump_version')) {
                acl_bump_version();
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'user_id' => $userId,
            'permission_name' => $permissionName,
            'base_granted' => $baseGranted,
            'override' => $finalOverride,
            'granted' => $effectiveGranted ? 1 : 0,
            'source' => $source,
            'action' => $action,
        ]);
    }
    
    public function get_debug_info()
    {
        if ($resp = $this->guardManagementAccess()) {
            return $resp;
        }

        // Endpoint para obtener información de debug
        $session = session();
        $debugInfo = [
            'query' => $session->get('last_query'),
            'operation' => $session->get('last_operation'),
            'primary_key' => $session->get('last_primary_key')
        ];
        
        // Limpiar después de leer
        $session->remove('last_query');
        $session->remove('last_operation');
        $session->remove('last_primary_key');
        
        return $this->response->setJSON($debugInfo);
    }

    public function user_roles()
    {
        try {
            if ($resp = $this->guardManagementAccess()) {
                return $resp;
            }

            helper('acl_version');

            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $user_roles_crud = $this->_getGroceryCrudEnterprise();
            $user_roles_crud->setTable('us_user_roles');
            $user_roles_crud->setSubject('User-Rol', 'User-Roles');
            $user_roles_crud->defaultOrdering('us_user_roles.id', 'desc');
            
            $user_roles_crud->columns(['id', 'user_id', 'role_id']);
            $user_roles_crud->fields(['user_id', 'role_id']);
            $user_roles_crud->unsetDeleteMultiple();

            // Relaciones$user_roles_crud->setRelation('user_id', 'us_users', 'username');
            $user_roles_crud->setRelation('role_id', 'us_roles', 'role_name');
            $user_roles_crud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');

            $user_roles_crud->callbackAfterInsert(function ($stateParameters) use ($user_roles_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $user_roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $user_roles_crud->callbackAfterUpdate(function ($stateParameters) use ($user_roles_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $user_roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $user_roles_crud->callbackAfterDelete(function ($stateParameters) use ($user_roles_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $user_roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });

            $user_roles_output = $user_roles_crud->render();
            $data['title'] = 'Gestión de Roles';
            $data['description'] = 'Administra los roles del sistema y sus permisos asociados';
            $final_output = array_merge((array)$user_roles_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception$e) {
            exit($e->getMessage());
        }
    }


    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
        return $groceryCrud;
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

    private function _example_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');

            echo $salida->output;
            exit;
        }
        // echo "<br> bbbbbbb";
        return view('/deskapp/extra-pages/grocery_page', (array)$salida);
    }
    public function profile()
    {
        if ($resp = $this->guardSessionOnly(false)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $session = session();
        $userModel = new UserModel($db);
        
        // Obtener datos del usuario actual
        $user = $userModel->find($session->get('id'));
        [$isDebugUser, $debugRoleOptions, $debugSelectedRoleId, $debugSelectedRoleName] = $this->ensureDebugRoleContext($session, $userModel);

        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'user' => $user,
            'debugRoleSwitcherEnabled' => $isDebugUser,
            'debugRoleOptions' => $debugRoleOptions,
            'debugSelectedRoleId' => $debugSelectedRoleId,
            'debugSelectedRoleName' => $debugSelectedRoleName,
            'title' => 'Mi Perfil'
        ];
        
        return view('deskapp/users/profile', $data);
    }

    public function switch_debug_role()
    {
        if ($resp = $this->guardSessionOnly(false)) {
            return $resp;
        }

        $session = session();
        $db = \Config\Database::connect();
        $userModel = new UserModel($db);
        [$isDebugUser] = $this->ensureDebugRoleContext($session, $userModel);

        if (!$isDebugUser) {
            return redirect()->to('/users/profile')->with('error', 'No tienes acceso al cambio de rol Debug.');
        }

        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $debugRoleName = (string) ($session->get('auth_debug_role_name') ?? 'Debug');

        if ($roleId <= 0 || !$this->applyDebugRoleToSession($session, $userModel, $roleId, $debugRoleName)) {
            return redirect()->to('/users/profile')->with('error', 'Rol Debug inválido.');
        }

        $selectedRoleName = (string) ($session->get('debug_selected_role_name') ?? '');
        return redirect()->to('/users/profile')->with('success', '✓ Rol Debug actualizado a ' . $selectedRoleName);
    }

    public function update_profile()
    {
        if ($resp = $this->guardSessionOnly(false)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $session = session();
        $userModel = new UserModel($db);
        
        $rules = [
            'firstname' => 'required|min_length[2]|max_length[40]',
            'midname' => 'max_length[40]',
            'lastname' => 'required|min_length[2]|max_length[40]',
            'email' => 'required|valid_email|max_length[100]',
            'phone' => 'max_length[15]'
        ];
        
        // Validar imagen si se proporciona
        if ($this->request->getFile('avatar')->isValid()) {
            $rules['avatar'] = 'uploaded[avatar]|is_image[avatar]|max_size[avatar,2048]|ext_in[avatar,jpg,jpeg,png,gif]';
        }
        
        if ($this->validate($rules)) {
            $userId = $session->get('id');
            $oldUser = $userModel->find($userId);
            
            $data = [
                'firstname' => $this->request->getPost('firstname'),
                'midname' => $this->request->getPost('midname'),
                'lastname' => $this->request->getPost('lastname'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone')
            ];
            
            // Manejar la subida de avatar a través del Storage_Service (S3 File Storage).
            // El avatar es opcional en este formulario: solo entramos al flujo de subida
            // cuando el usuario efectivamente adjuntó un archivo válido.
            $avatar     = $this->request->getFile('avatar');
            $newKey     = null; // Clave recién escrita (para compensar en fallo de DB).
            $avatarProvided = $avatar && $avatar->isValid() && !$avatar->hasMoved();

            if ($avatarProvided) {
                // Req 6.3: sin archivo / ruta temporal vacía => 400 y NO llamar a put().
                $tempName = (string) $avatar->getTempName();
                if ($tempName === '' || !is_file($tempName)) {
                    return $this->response
                        ->setStatusCode(400)
                        ->setJSON(['success' => false, 'message' => 'No se recibió ningún archivo válido.']);
                }

                // Nombre original provisto por el cliente para derivar la clave canónica.
                $originalName = (string) ($avatar->getClientName() ?: $avatar->getName());
                $key          = buildKey('avatars', null, $originalName);

                $storage = service('fileStorage');

                // Req 6.2 / 6.6: persistir con put(); si falla, 500 y NO registrar referencia en DB.
                if (!$storage->put($key, $tempName)) {
                    log_message('error', '[AVATAR] put() falló para la clave: ' . $key);
                    return redirect()->back()->withInput()->with('error', 'Error al subir la imagen de perfil');
                }
                $newKey = $key;

                // Req 6.4 / 6.7: eliminar el avatar anterior (reemplazo) vía delete() del servicio,
                // no con unlink(). Si delete() falla => error y se conserva la referencia existente;
                // además se compensa el objeto recién escrito para no dejar huérfanos.
                $oldAvatar = (string) ($oldUser['avatar'] ?? '');
                if ($oldAvatar !== '' && $oldAvatar !== 'uploads/avatars/default.png') {
                    $oldKey = keyFromStored($oldAvatar, 'avatars');
                    if ($oldKey !== '' && !$storage->delete($oldKey)) {
                        log_message('error', '[AVATAR] delete() del avatar anterior falló: ' . $oldKey);
                        $storage->delete($newKey); // compensa el objeto recién subido
                        return redirect()->back()->withInput()->with('error', 'No se pudo eliminar el avatar anterior. Se conservó el actual.');
                    }
                }

                // Req 6.5 / 5.x: almacenar el valor canónico (nombre base), nunca una URL absoluta.
                $data['avatar'] = basename($key);
            }

            // Actualizar base de datos (con compensación ante fallo posterior al put()).
            try {
                $builder = $db->table('users');
                $builder->where('id', $userId);
                $builder->update($data);
            } catch (\Throwable $e) {
                log_message('error', 'Error al actualizar perfil: ' . $e->getMessage());
                // Req 7.1: si la escritura en DB falla tras un put() exitoso, se elimina
                // exactamente una vez el objeto recién escrito para no dejar huérfanos.
                if ($newKey !== null) {
                    service('fileStorage')->delete($newKey);
                }
                return redirect()->back()->withInput()->with('error', 'No se pudieron guardar los datos del perfil.');
            }

            // Actualizar datos de sesión
            $session->set('firstname', $data['firstname']);
            $session->set('midname', $data['midname'] ?? '');
            $session->set('lastname', $data['lastname']);
            if (isset($data['avatar'])) {
                $session->set('avatar', $data['avatar']);
            }
            
            return redirect()->to('/users/profile')->with('success', '✓ Datos del perfil actualizados correctamente');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
    }

    public function update_password()
    {
        if ($resp = $this->guardSessionOnly(false)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $session = session();
        $userModel = new UserModel($db);
        
        // Reglas de validación
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/]',
            'confirm_password' => 'required|matches[new_password]'
        ];
        
        $messages = [
            'new_password' => [
                'regex_match' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número'
            ]
        ];
        
        if ($this->validate($rules, $messages)) {
            // Obtener datos del usuario
            $user = $userModel->find($session->get('id'));
            
            // Verificar contraseña actual
            if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
                return redirect()->back()->with('error', '✗ La contraseña actual es incorrecta');
            }
            
            // Actualizar contraseña
            $data = [
                'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)
            ];
            
            $userModel->update($session->get('id'), $data);
            
            return redirect()->to('/users/profile')->with('success', '✓ Contraseña actualizada correctamente. Por seguridad, considera cerrar sesión y volver a iniciar');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
    }
    
    /**
     * Eliminar avatar (AJAX)
     */
    public function delete_avatar()
    {
        if ($resp = $this->guardSessionOnly(true)) {
            return $resp;
        }

        $session = session();
        $userId = $session->get('id');
        
        $db = \Config\Database::connect();
        $userModel = new UserModel($db);
        $user = $userModel->find($userId);
        
        // Req 6.4 / 6.7: eliminar el objeto subyacente vía delete() del Storage_Service
        // en lugar de unlink(). Si delete() falla, se devuelve error y se conserva la
        // referencia existente sin cambios.
        $currentAvatar = (string) ($user['avatar'] ?? '');
        if ($currentAvatar !== '' && $currentAvatar !== 'uploads/avatars/default.png') {
            $key = keyFromStored($currentAvatar, 'avatars');
            if ($key !== '' && !service('fileStorage')->delete($key)) {
                log_message('error', '[AVATAR] delete() falló al eliminar avatar: ' . $key);
                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'success' => false,
                        'message' => 'No se pudo eliminar el avatar. Se conservó el actual.',
                    ]);
            }
        }
        
        // Establecer avatar por defecto
        $defaultAvatar = 'uploads/avatars/default.png';
        $userModel->update($userId, ['avatar' => $defaultAvatar]);
        $session->set('avatar', $defaultAvatar);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Avatar eliminado correctamente',
            'avatar' => $defaultAvatar
        ]);
    }
}

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
        helper(['form', 'url', 'cliente_filter']);
    }

    private function guardManagementAccess()
    {
        $session = session();
        $userId = $session->get('id');
        $isApi = $this->request->isAJAX() || $this->request->getGet('gc_state') !== null;

        if (!$userId) {
            if ($isApi) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesión expirada']);
            }
            return redirect()->to('/deskapp/auth/login');
        }

        $perms = $session->get('user_permissions');
        $roles = $session->get('user_roles');
        $canManage = has_permission('menu_roles', $perms, $roles) || has_permission('menu_permisos', $perms, $roles);

        if (!(is_super_admin($roles) || is_admin($roles)) && !$canManage) {
            if ($isApi) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado']);
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
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesión expirada']);
            }
            return redirect()->to('/deskapp/auth/login');
        }
        return null;
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

            $db2 = $this->_getDbData();
            $session = session();
            $session->get('user_permissions');

            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $users_crud = $this->_getGroceryCrudEnterprise();
            $users_crud->setTable('users');
            $users_crud->setSubject('Usuario', 'Usuarios');
            $users_crud->defaultOrdering('users.id', 'desc');
            
            // Callback para cifrar la contraseña antes de insertar
            $users_crud->columns(['id', 'username', 'firstname', 'midname', 'lastname', 'email', 'avatar', 'roles', 'clientes', 'status']);
            $users_crud->fields(['username', 'firstname', 'midname', 'lastname', 'email', 'phone', 'avatar', 'password', 'roles', 'clientes', 'status']);
            $users_crud->fieldType('password', 'password'); // Indica que el campo password es de tipo password
            $users_crud->unsetDeleteMultiple();
            $users_crud->setActionButton('Clonar', 'fas fa-clone', function ($row) {
                return '/users/users/clone/' . $row->id;
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
            $users_crud->editFields(['username', 'firstname', 'midname', 'lastname', 'email', 'phone', 'avatar', 'roles', 'clientes', 'status']);
            
            $users_crud->callbackBeforeInsert(function ($stateParameters) {
                $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
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
                    $jsonBody = $request->getJSON(true);
                    if (is_array($jsonBody) && array_key_exists('status', $jsonBody)) {
                        $postStatus = $jsonBody['status'];
                        $hasStatus = true;
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
                $tableName = $user_roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $user_roles_crud->callbackAfterUpdate(function ($stateParameters) use ($user_roles_crud) {
                $tableName = $user_roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $user_roles_crud->callbackAfterDelete(function ($stateParameters) use ($user_roles_crud) {
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
        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'user' => $user,
            'title' => 'Mi Perfil'
        ];
        
        return view('deskapp/users/profile', $data);
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
            
            // Manejar la subida de avatar
            $avatar = $this->request->getFile('avatar');
            if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
                // Crear directorio si no existe
                $uploadPath = ROOTPATH . 'public/uploads/avatars';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Debug de permisos/rutas en producción
                log_message('error', '[AVATAR DEBUG] uploadPath=' . $uploadPath
                    . ' is_dir=' . (is_dir($uploadPath) ? 'yes' : 'no')
                    . ' is_writable=' . (is_writable($uploadPath) ? 'yes' : 'no')
                    . ' tmp=' . $avatar->getTempName()
                    . ' tmp_exists=' . (file_exists($avatar->getTempName()) ? 'yes' : 'no'));
                
                // Eliminar avatar anterior si existe y no es el default
                if (!empty($oldUser['avatar']) && $oldUser['avatar'] !== 'uploads/avatars/default.png') {
                    $oldPath = ROOTPATH . 'public/' . $oldUser['avatar'];
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                
                // Generar nombre único para la imagen
                $newName = 'avatar_' . $userId . '_' . time() . '.' . $avatar->getExtension();
                
                // Mover archivo
                if ($avatar->move($uploadPath, $newName)) {
                    $data['avatar'] = 'uploads/avatars/' . $newName;
                } else {
                    return redirect()->back()->withInput()->with('error', 'Error al subir la imagen de perfil');
                }
            }
            
            // Actualizar base de datos
            $builder = $db->table('users');
            $builder->where('id', $userId);
            $builder->update($data);
            
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
        
        if (!empty($user['avatar']) && $user['avatar'] !== 'uploads/avatars/default.png') {
            $oldPath = ROOTPATH . 'public/' . $user['avatar'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
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

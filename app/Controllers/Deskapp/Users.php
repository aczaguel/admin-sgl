<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

class Users extends BaseController
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

    public function users(){
        try {
            $db2 = $this->_getDbData();
            $session = session();
            $session->get('user_permissions');
            // echo "<pre>";
            // print_r($session->get('user_permissions'));
            // echo "</pre>";die();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $users_crud = $this->_getGroceryCrudEnterprise();
            $users_crud->setTable('users');
            $users_crud->setSubject('Usuario', 'Usuarios');
            $users_crud->defaultOrdering('users.id', 'desc');
            
            // Callback para cifrar la contraseña antes de insertar
            $users_crud->columns(['id', 'username', 'firstname', 'midname', 'lastname', 'email', 'roles', 'status']);
            $users_crud->fields(['username', 'firstname', 'midname', 'lastname', 'email', 'phone', 'avatar', 'password', 'roles', 'status']);
            $users_crud->fieldType('password', 'password'); // Indica que el campo password es de tipo password
            $users_crud->unsetDeleteMultiple();
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
                    'gif', 'jpeg', 'jpg', 'png', 'tiff', 'pdf'
                ]
            ];
    
            $users_crud->setFieldUpload(
                'avatar', 
                '/assets/uploads/documentos/', 
                '/assets/uploads/documentos/', 
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
                $data['username'] = 'test';
                $data['firstname'] = 'test';
                $data['midname'] = 'test';
                $data['lastname'] = "test";
                $data['email'] = "test@test.com";
                $data['phone'] = "12345678";
                $data['password'] = "12345678";

                return $data;
            });
            $users_crud->unsetEditFields(['password']);
            $users_crud->callbackBeforeInsert(function ($stateParameters) {
                $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
                // Comprueba y encripta la contraseña
                if (isset($stateParameters->data['password']) && !empty($stateParameters->data['password'])) {
                    log_message('error', 'Original Password: ' . $stateParameters->data['password']); // Log the original password
                    $stateParameters->data['password'] = password_hash($stateParameters->data['password'], PASSWORD_DEFAULT);
                    log_message('error', 'Hashed Password: ' . $stateParameters->data['password']); // Log the hashed password
                }else{
                    log_message('error', 'Hashed Password: No existe el campo'); // Log the hashed password
                }
                
                return $stateParameters;
            });

            // Encriptar la contraseña antes de guardarla en la base de datos al actualizar un registro existente
            $users_crud->callbackBeforeUpdate(function ($stateParameters) {
                if (isset($stateParameters->data['password']) && !empty($stateParameters->data['password'])) {
                    // Utiliza password_hash() para un cifrado seguro
                    $stateParameters->data['password'] = password_hash($stateParameters->data['password'], PASSWORD_DEFAULT);
                }
                return $stateParameters;
            });

            $users_output = $users_crud->render();
            $final_output = array_merge((array)$users_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function user_roles()
    {
        try {
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

            $user_roles_output = $user_roles_crud->render();
            $final_output = array_merge((array)$user_roles_output, $data);
            echo$this->_example_output($final_output);

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

}

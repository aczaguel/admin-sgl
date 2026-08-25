<?php 
	namespace App\Controllers\Deskapp;
	use App\Controllers\BaseController;
    use App\Models\UserModel;

    /**
     * 
     */
    class Login extends BaseController
    {
    	
	    	public function index()
	    {
					helper(['form']);
	        echo view('deskapp/auth/login');
	    } 

	    public function auth()
	    {
	        $session = session();
	        $model = new UserModel();
	        helper(['acl_version', 'permissions']);
	        $username = $this->request->getPost('username');
	        $password = $this->request->getPost('password');
	        $data = $model->where('username', $username)->where('status', 1)->first();
	        if($data){
	            $pass = $data['password'];
	            $verify_pass = password_verify($password, $pass);
	            if($verify_pass){
	                $ses_data = [
	                    'id'       => $data['id'],
	                    'username' => $data['username'],
	                    'email'    => $data['email'],
	                    'firstname' => $data['firstname'],
						'midname' => $data['midname'],
	                    'lastname' => $data['lastname'],
						'avatar' => $data['avatar'],
	                    'logged_in'     => TRUE
	                ];
	                $session->set($ses_data);
					$user_permissions = $model->getUserPermissions($data['id']);
					$user_roles = $model->getUserRoles($data['id']);

					$session->set('auth_user_permissions', $user_permissions);
					$session->set('auth_user_roles', $user_roles);

					$isDebugUser = in_array('debug_perm_audit_tags', normalize_permission_list($user_permissions), true);
					foreach (normalize_permission_list($user_roles) as $roleName) {
						if (normalize_role_key($roleName) === 'debug') {
							$isDebugUser = true;
							break;
						}
					}

					$debugRoleName = 'Debug';
					foreach (normalize_permission_list($user_roles) as $roleName) {
						if (normalize_role_key($roleName) === 'debug') {
							$debugRoleName = $roleName;
							break;
						}
					}

					if ($isDebugUser) {
						$defaultRole = $model->findRoleByNormalizedKey('admin');
						if ($defaultRole === null) {
							$availableRoles = $model->getAvailableRoles(true);
							$defaultRole = $availableRoles[0] ?? null;
						}

						if ($defaultRole !== null) {
							$effectiveRoleName = (string) ($defaultRole['role_name'] ?? 'Admin');
							$effectivePermissions = $model->getRolePermissionsById((int) ($defaultRole['id'] ?? 0));
							if (!in_array('debug_perm_audit_tags', $effectivePermissions, true)) {
								$effectivePermissions[] = 'debug_perm_audit_tags';
							}
							sort($effectivePermissions, SORT_STRING);

							$effectiveRoles = [$effectiveRoleName];
							if (normalize_role_key($debugRoleName) !== normalize_role_key($effectiveRoleName)) {
								$effectiveRoles[] = $debugRoleName;
							}

							$session->set('user_roles', $effectiveRoles);
							$session->set('user_permissions', $effectivePermissions);
							$session->set('debug_selected_role_id', (int) ($defaultRole['id'] ?? 0));
							$session->set('debug_selected_role_name', $effectiveRoleName);
						} else {
							$session->set('user_permissions', $user_permissions);
							$session->set('user_roles', $user_roles);
						}

						$session->set('auth_is_debug', true);
						$session->set('debug_can_switch_roles', true);
						$session->set('auth_debug_role_name', $debugRoleName);
					} else {
						$session->set('user_permissions', $user_permissions);
						$session->set('user_roles', $user_roles);
						$session->remove(['auth_is_debug', 'debug_can_switch_roles', 'auth_debug_role_name', 'debug_selected_role_id', 'debug_selected_role_name']);
					}
					$aclVer = function_exists('acl_get_version') ? acl_get_version() : null;
					if ($aclVer !== null) {
						$session->set('acl_version', (int)$aclVer);
					}
					$user_client = $model->isUserClient($data['id']);
					if($user_client["is_client"]){
						$session->set('user_client', $user_client);
					}

					$clients_by_user = $model->obtenerClientesPorUsuario($data['id']);
					$session->set('clients_by_user', $clients_by_user);

					// Redirigir a dashboard según rol:
					// Solo va al dashboard de cliente si is_client=true Y no tiene otros roles
					// que impliquen acceso interno (Admin, Ejecutivo, etc.)
					$effectiveRoles = $session->get('user_roles') ?? [];
					$isOnlyClient = !empty($user_client['is_client']) && $this->_isClientOnlyUser($effectiveRoles);
					if ($isOnlyClient) {
						return redirect()->to(site_url('deskapp/clientes/cdashboard'));
					}
	                return redirect()->to('./deskapp/dashboard');

	            }else{
	                $session->setFlashdata('msg', 'Wrong Password');
	                return redirect()->to('./deskapp/login');
	            }
	        }else{
	            $session->setFlashdata('msg', 'Username not Found');
	            return redirect()->to('./deskapp/login');
	        }
	    }

	   
	/**
	 * Devuelve true si los roles del usuario son exclusivamente de cliente
	 * (sin roles internos como Admin, Super Admin, Ejecutivo, etc.)
	 *
	 * Un usuario es "solo cliente" cuando NO tiene ningún rol que implique
	 * acceso al sistema interno. Se considera cliente exclusivo si no tiene
	 * ningún rol reconocido como interno.
	 */
	private function _isClientOnlyUser(array $roles): bool
	{
		if (empty($roles)) {
			return false;
		}

		// Roles que indican acceso interno — cualquiera de estos excluye el redirect a cdashboard
		$internalRolePatterns = [
			'admin', 'super admin', 'superadmin', 'ejecutivo', 'gestor',
			'gerente', 'manager', 'operador', 'supervisor', 'debug',
			'staff', 'empleado', 'interno',
		];

		foreach ($roles as $role) {
			$roleLower = strtolower(trim((string) $role));
			foreach ($internalRolePatterns as $pattern) {
				if (strpos($roleLower, $pattern) !== false) {
					return false; // Tiene al menos un rol interno → dashboard normal
				}
			}
		}

		return true; // Solo tiene roles que no son internos → dashboard cliente
	}
    }
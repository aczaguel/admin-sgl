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
	        $username = $this->request->getPost('username');
	        $password = $this->request->getPost('password');
	        $data = $model->where('username', $username)->first();
	        if($data){
	            $pass = $data['password'];
	            $verify_pass = password_verify($password, $pass);
	            if($verify_pass){
	                $ses_data = [
	                    'id'       => $data['id'],
	                    'username' => $data['username'],
	                    'email'    => $data['email'],
	                    'firstname' => $data['firstname'],
	                    'lastname' => $data['lastname'],
	                    'logged_in'     => TRUE
	                ];
	                $session->set($ses_data);
					$user_permissions = $model->getUserPermissions($data['id']);
					$session->set('user_permissions', $user_permissions);
					// var_dump($session->get('user_permissions'));
					$user_roles = $model->getUserRoles($data['id']);
					$session->set('user_roles', $user_roles);
					// var_dump($session->get('user_roles'));die();
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

	   
    }
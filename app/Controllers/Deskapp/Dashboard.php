<?php
 namespace App\Controllers\Deskapp;
 use App\Controllers\BaseController;
 use App\Models\UserModel;

 use Config\Database as ConfigDatabase;
 use App\Models\TramitesModel;
 /**
  * 
  */
 class Dashboard extends BaseController
 {
 	
 	public function index()
 	{
 		$db = \Config\Database::connect();
 		$model = new UserModel();
 		$session = session();
 		$data['username'] = $session->get('user_name');
 		$data['session'] = \Config\Services::session();

		$db2 = $this->_getDbData();
		$tramiteModel = new TramitesModel($db2);
		$tramiteCountsByClase = $tramiteModel->getTramiteCountsByClase();
		 
		$data["graph"] = $tramiteCountsByClase;

		
		// $perMonth = $tramiteModel->getTramitesGroupedByStatusPerMonth();
		$tramitesPorMes = $tramiteModel->getTramitesGroupedByStatusPerMonth();

		$data["perMonth"] = $tramitesPorMes;
 		echo view('deskapp/dashboard/index_sgl',$data);
 	}
 	public function one()
 	{
 		$session = session();
 		$data['session'] = \Config\Services::session();
 		$data['username'] = $session->get('user_name');
 		echo view('deskapp/dashboard/index',$data);
 	}

 	public function two()
 	{
 		$session = session();
 		$data['session'] = \Config\Services::session();
 		$data['username'] = $session->get('user_name');
 		echo view('deskapp/dashboard/index2',$data);
 	}
 	public function three()
 	{
 		$session = session();
 		$data['session'] = \Config\Services::session();
 		$data['username'] = $session->get('user_name');
 		echo view('deskapp/dashboard/index3',$data);
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
 	
 }
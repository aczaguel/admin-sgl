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
		$userId = $session->get('id');
		$userRoles = $session->get('user_roles');

		$db2 = $this->_getDbData();
		$tramiteModel = new TramitesModel($db2);

		// Obtener clientes asignados al usuario
		$clientesAsignados = $this->_getClientesAsignados($userId, $userRoles, $db);
		$data['clientes_asignados'] = $clientesAsignados;

		// Obtener filtros de la solicitud
		$clienteId = $this->request->getGet('cliente_id');
		$tipoTramiteId = $this->request->getGet('tipo_tramite_id');

		// Obtener conteos con filtros
		$tramiteCountsByClase = $tramiteModel->getTramiteCountsByClaseConFiltros($clientesAsignados, $clienteId, $tipoTramiteId);
		$data["graph"] = $tramiteCountsByClase;

		// Obtener resumen por cliente
		$resumenPorCliente = $tramiteModel->getResumenPorCliente($clientesAsignados);
		$data["resumen_clientes"] = $resumenPorCliente;

		// Obtener resumen por tipo de servicio
		$resumenPorTipo = $tramiteModel->getResumenPorTipoServicio($clientesAsignados, $clienteId);
		$data["resumen_tipos"] = $resumenPorTipo;

		// Obtener trámites con retraso
		$tramitesRetrasados = $tramiteModel->getTramitesRetrasados($clientesAsignados, $clienteId, $tipoTramiteId);
		$data["tramites_retrasados"] = $tramitesRetrasados;

		// Gráfica por mes
		$tramitesPorMes = $tramiteModel->getTramitesGroupedByStatusPerMonth($clientesAsignados, $clienteId);
		$data["perMonth"] = $tramitesPorMes;

		// Obtener tipos de trámite para el filtro
		$tiposTramite = $db->table('tra_tipos')->select('id, tipo_tramite as nombre')->get()->getResultArray();
		$data['tipos_tramite'] = $tiposTramite;

		// Obtener clientes para el filtro
		if ($clientesAsignados === null) {
			// SuperAdmin - todos los clientes
			$clientesLista = $db->table('cli_directo')->select('id, nombre')->orderBy('nombre', 'ASC')->get()->getResultArray();
		} else {
			// Usuario normal - solo clientes asignados
			$clientesLista = $db->table('cli_directo')->select('id, nombre')->whereIn('id', $clientesAsignados)->orderBy('nombre', 'ASC')->get()->getResultArray();
		}
		$data['clientes_lista'] = $clientesLista;

		// DEBUG - Registrar información
		log_message('debug', 'Clientes asignados: ' . json_encode($clientesAsignados));
		log_message('debug', 'Clientes lista: ' . json_encode($clientesLista));
		log_message('debug', 'Resumen clientes: ' . json_encode($resumenPorCliente));
		log_message('debug', 'Resumen tipos: ' . json_encode($resumenPorTipo));
		log_message('debug', 'Trámites retrasados: ' . json_encode($tramitesRetrasados));

		// Pasar filtros actuales a la vista
		$data['cliente_id_filtro'] = $clienteId;
		$data['tipo_tramite_id_filtro'] = $tipoTramiteId;

 		echo view('deskapp/dashboard/index_sgl',$data);
 	}

	/**
	 * Obtiene los clientes asignados al usuario según su rol
	 * Si es admin, retorna null (acceso a todos)
	 * Si no, retorna array de IDs de clientes asignados
	 */
	private function _getClientesAsignados($userId, $userRoles, $db)
	{
		// Verificar si es Super Admin o Admin
		if (is_array($userRoles) && (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles))) {
			return null; // null significa acceso a todos los clientes
		}

		// Obtener clientes asignados desde cliente_user
		$query = $db->table('cliente_user')
			->select('cliente_id')
			->where('user_id', $userId)
			->get();

		$clientesAsignados = array_column($query->getResultArray(), 'cliente_id');

		return !empty($clientesAsignados) ? $clientesAsignados : [0]; // [0] significa sin acceso
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
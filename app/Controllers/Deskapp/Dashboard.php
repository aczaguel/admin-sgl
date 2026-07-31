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
		// Nota: este método devuelve los IDs de cli_directo (clientes directos)
		// que pertenecen a los clientes (tabla cliente) asignados al usuario
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

		// Obtener clientes directos para el filtro
		if ($clientesAsignados === null) {
			// Admin/SuperAdmin - todos los clientes directos
			$clientesLista = $db->table('cli_directo')
				->select('id, nombre')
				->orderBy('nombre', 'ASC')
				->get()
				->getResultArray();
		} else {
			// Usuario normal - solo cli_directo ligados a sus clientes permitidos
			$clientesLista = $db->table('cli_directo')
				->select('id, nombre')
				->whereIn('id', $clientesAsignados)
				->orderBy('nombre', 'ASC')
				->get()
				->getResultArray();
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

		$excludedTeamIds = [11, 13, 25, 26, 27];
		$roleSevenUserIds = $db->table('us_user_roles')
			->select('user_id')
			->where('role_id', 7)
			->get()
			->getResultArray();
		$excludedTeamIds = array_values(array_unique(array_merge(
			$excludedTeamIds,
			array_map(static fn(array $row): int => (int) ($row['user_id'] ?? 0), $roleSevenUserIds)
		)));

		$teamMembers = $db->table('users')
			->select('id, username, firstname, lastname, avatar, status')
			->where('status', 1)
			->groupStart()
				->where('id >=', 6)
				->orWhere('id', 4)
			->groupEnd()
			->whereNotIn('id', $excludedTeamIds)
			->orderBy('CASE WHEN id = 4 THEN 1 ELSE 0 END', 'ASC', false)
			->orderBy('id', 'ASC')
			->get()
			->getResultArray();

		$data['team_members'] = array_map(static function (array $member): array {
			$firstName = trim((string) ($member['firstname'] ?? ''));
			$lastName = trim((string) ($member['lastname'] ?? ''));
			$fullName = trim($firstName . ' ' . $lastName);

			if ($fullName === '') {
				$fullName = (string) ($member['username'] ?? ('Usuario ' . ($member['id'] ?? '')));
			}

			$avatar = trim((string) ($member['avatar'] ?? ''));
			if ($avatar === '') {
				$avatar = 'uploads/avatars/default.png';
			}

			$member['display_name'] = $fullName;
			$member['avatar'] = $avatar;

			return $member;
		}, $teamMembers);

 		echo view('deskapp/dashboard/index_sgl',$data);
 	}

	/**
	 * Obtiene los clientes asignados al usuario según su rol.
	 *
	 * Si es Admin/Super Admin: retorna null (acceso a todos los clientes directos).
	 * Si no: retorna array de IDs de cli_directo ligados a los clientes (tabla cliente)
	 *        que están asignados al usuario vía cliente_user.
	 */
	private function _getClientesAsignados($userId, $userRoles, $db)
	{
		// Verificar si es Super Admin o Admin
		if (is_array($userRoles) && (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles))) {
			return null; // null = sin filtro por cliente (acceso completo)
		}

		// 1) Obtener IDs de clientes (tabla cliente) asignados al usuario
		$clienteIdsQuery = $db->table('cliente_user')
			->select('cliente_id')
			->where('user_id', $userId)
			->get();

		$clienteIds = array_column($clienteIdsQuery->getResultArray(), 'cliente_id');

		if (empty($clienteIds)) {
			// Sin clientes asignados: devolver un array que no matchee ningún cli_directo
			return [0];
		}

		// 2) Traducir esos clientes a sus cli_directo asociados
		$cliDirectoQuery = $db->table('cli_directo')
			->select('id')
			->whereIn('cliente_id', $clienteIds)
			->get();

		$cliDirectoIds = array_column($cliDirectoQuery->getResultArray(), 'id');

		return !empty($cliDirectoIds) ? $cliDirectoIds : [0];
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
                'charset' => 'utf8',
            // FR-01: Sync MySQL session timezone with PHP (America/Mexico_City)
            'driver_options' => [
                MYSQLI_INIT_COMMAND => "SET time_zone = '-06:00'",
            ],
            ]
        ];
    }
 	
 }
<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;
use Config\Database as ConfigDatabase;

use App\Models\BitacoraModel;
use CodeIgniter\Controller;

class Bitacora extends BaseController
{
    public function __construct()
    {
        helper(['cliente_filter']);
    }

    public function index()
    {
        $request = \Config\Services::request();
        $uri = $request->getUri();
        $folio_tramite = (int) $uri->getSegment(4);

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');
        $myid = $session->get('id');
        // Carga del modelo
        $db_config = $this->_getDbData();
        // $model = new BitacoraModel($db_config);

        $model = new \CodeIgniter\Model();
        $model->setTable('documento');
        
        $documentos = $model->select('documento_id, documento')
        ->findAll();
        $doctos = [];

        foreach ($documentos as $item) {
            $doctos[$item['documento_id']] = $item['documento'];
        }

        $model = new \CodeIgniter\Model();
        $model->setTable('bitacora');
        
        $bitacoras = $model->select('bitacora.folio_tramite, bitacora.tramite_id, bitacora.cambios, bitacora.tipo, bitacora.origen, bitacora.created_at, users.firstname, users.lastname')
        ->join('users', 'users.id = bitacora.user_id')
        ->where('bitacora.folio_tramite =', $folio_tramite) 
        ->orderBy('bitacora.created_at', 'DESC')
        ->findAll();

        $model = new \CodeIgniter\Model();
        $model->setTable('cobro_statuses');
        
        $cobro_statuses = $model->select('cobro_statuses.id, cobro_statuses.cobro_status')
        ->findAll();

        // Llamada a la función para convertir el arreglo $array
        $arrayConvertido = $this->convertirCambiosToArray($bitacoras);

        // Preparar los datos para pasar a la vista
        $salida['bitacoras'] = $arrayConvertido;
        $salida['documentos'] = $doctos;
        $cobro_statuses = $this->convertir_a_asociativo($cobro_statuses);
        $salida['cobro_statuses'] = $cobro_statuses;

        $salida2 = array_merge((array)$salida, $data);

        // Cargar la vista
        return $this->_example_output($salida2);
    }

    public function timeline()
    {
        $session = session();
        $folio = trim((string) $this->request->getGet('folio'));
        $tramiteId = trim((string) $this->request->getGet('tramite_id'));

        $validatedTramiteId = null;

        if ($folio === '' && $tramiteId === '') {
            return redirect()->to(site_url('/bitacora/search'))->with('error', 'Selecciona un tramite para ver la bitacora.');
        }

        if ($tramiteId !== '') {
            $validatedTramiteId = (int) $tramiteId;
        } elseif ($folio !== '') {
            $db = ConfigDatabase::connect();
            $tramiteRow = $db->table('tramite')
                ->select('id')
                ->where('folio', $folio)
                ->get()
                ->getRowArray();

            if (!$tramiteRow) {
                return redirect()->to(site_url('/bitacora/search'))->with('error', 'Trámite no encontrado');
            }

            $validatedTramiteId = (int) $tramiteRow['id'];
        }

        if ($validatedTramiteId && !validate_tramite_access($validatedTramiteId)) {
            log_unauthorized_access_attempt('bitacora', $validatedTramiteId);
            return redirect()->to(site_url('/bitacora/search'))->with('error', 'No tienes permisos para ver este trámite');
        }

        $model = new \CodeIgniter\Model();
        $model->setTable('bitacora');
        $builder = $model
            ->select('bitacora.*, users.firstname, users.lastname, users.username, users.email')
            ->join('users', 'users.id = bitacora.user_id', 'left')
            ->orderBy('bitacora.created_at', 'DESC');

        if ($folio !== '') {
            $builder->where('bitacora.folio_tramite', $folio);
        }

        if ($tramiteId !== '') {
            $builder->where('bitacora.tramite_id', $tramiteId);
        }

        if ($validatedTramiteId && $tramiteId === '') {
            $builder->where('bitacora.tramite_id', $validatedTramiteId);
        }

        $bitacoras = $builder->findAll();
        $bitacoras = $this->decodeCambiosRows($bitacoras);

        $data = [
            'session' => $session,
            'bitacora_log' => $bitacoras,
            'summary' => $this->buildSummary($bitacoras),
            'total_changes' => count($bitacoras),
            'last_modifier' => $this->getLastModifier($bitacoras),
            'filters' => [
                'folio' => $folio,
                'tramite_id' => $tramiteId,
            ],
        ];

        return view('deskapp/bitacora/bitacora_timeline', $data);
    }

    public function search()
    {
        $session = session();
        $model = new \CodeIgniter\Model();
        $model->setTable('bitacora');

        $userId = $session->get('id');

        $builder = $model
            ->select('bitacora.tramite_id, bitacora.folio_tramite, MAX(bitacora.created_at) AS last_change, COUNT(*) AS total_changes')
            ->groupBy('bitacora.tramite_id, bitacora.folio_tramite')
            ->orderBy('last_change', 'DESC')
            ->limit(100);

        if (!user_has_global_cliente_access($userId)) {
            $builder->join('tramite t', 't.id = bitacora.tramite_id', 'inner');
            $builder->where(get_cliente_filter_sql($userId, 't'), null, false);
        }

        $bitacoraList = $builder->findAll();

        $data = [
            'session' => $session,
            'bitacora_list' => $bitacoraList,
        ];

        return view('deskapp/bitacora/bitacora_search', $data);
    }

    private function _example_output($salida = null) {
    //   $salida = (object)esc($salida, 'raw');
    //   if ($salida->isJSONResponse) {
    //       header('Content-Type: application/json; charset=utf-8');
    //       echo $salida->output;
    //       exit;
    //   }
      // return view('example.php', (array)$salida);
        return view('/deskapp/ui/grocery_timeline.php', (array)$salida);
    }
    function convertirCambiosToArray($array)
    {
        foreach ($array as &$fila) {
            $fila['cambios'] = json_decode($fila['cambios'], true);
        }
        return $array;
    }
    function convertir_a_asociativo($arreglo) {
        $asociativo = array();
        foreach ($arreglo as $elemento) {
            $asociativo[$elemento['id']] = $elemento['cobro_status'];
        }
        return $asociativo;
    }

    private function decodeCambiosRows(array $rows)
    {
        foreach ($rows as &$row) {
            $row['cambios'] = $this->decodeCambios($row['cambios'] ?? '');
        }
        unset($row);
        return $rows;
    }

    private function decodeCambios($cambios)
    {
        if ($cambios === null || $cambios === '') {
            return [];
        }

        $decoded = json_decode($cambios, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function buildSummary(array $bitacoras)
    {
        $summary = [];
        foreach ($bitacoras as $item) {
            $action = $item['tipo'] ?? 'unknown';
            if (!isset($summary[$action])) {
                $summary[$action] = [
                    'action' => $action,
                    'count' => 0,
                    'last_occurrence' => $item['created_at'] ?? null,
                ];
            }

            $summary[$action]['count']++;
            $currentLast = $summary[$action]['last_occurrence'];
            if (!$currentLast || strtotime($item['created_at'] ?? '') > strtotime($currentLast)) {
                $summary[$action]['last_occurrence'] = $item['created_at'] ?? null;
            }
        }

        return array_values($summary);
    }

    private function getLastModifier(array $bitacoras)
    {
        if (empty($bitacoras)) {
            return null;
        }

        $last = $bitacoras[0];
        $nameParts = array_filter([
            $last['firstname'] ?? '',
            $last['lastname'] ?? '',
        ]);

        $displayName = trim(implode(' ', $nameParts));
        if ($displayName === '') {
            $displayName = $last['username'] ?? 'N/A';
        }

        return [
            'username' => $displayName,
            'modified_at' => $last['created_at'] ?? null,
        ];
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

    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        return $groceryCrud;
    }
}

<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;
use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use App\Models\BitacoraModel;
use CodeIgniter\Controller;

class Bitacora extends BaseController
{
    private const SEARCH_PAGE_SIZE = 10;
    private const SEARCH_SCAN_CHUNK_SIZE = 25;
    private const SEARCH_PAGER_SESSION_KEY = 'bitacora_search_pager';

    public function __construct()
    {
        helper(['cliente_filter', 'permissions', 'acl_guard']);
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
        $contrato = trim((string) $this->request->getGet('contrato'));

        $validatedTramiteId = null;

        if ($folio === '' && $tramiteId === '' && $contrato === '') {
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
        } elseif ($contrato !== '') {
            $db = ConfigDatabase::connect();
            $tramiteRow = $db->table('tramite')
                ->select('id')
                ->where('contrato', $contrato)
                ->get()
                ->getRowArray();

            if (!$tramiteRow) {
                return redirect()->to(site_url('/bitacora/search'))->with('error', 'Trámite no encontrado');
            }

            $validatedTramiteId = (int) $tramiteRow['id'];
        }

        $userId = (int) ($session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($session);

        if ($validatedTramiteId && !acl_has_tramite_tenant_access((int) $validatedTramiteId, $userId, $roles)) {
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
                'contrato' => $contrato,
            ],
        ];

        return view('deskapp/bitacora/bitacora_timeline', $data);
    }

    public function search()
    {
        $session = session();

        [$roles, $perms] = session_roles_perms($session);
        if ($resp = acl_require_permission('monitoreo_bitacora_search', $roles, $perms, 'No tienes permisos para acceder a esta función', '/deskapp/dashboard', 403, null)) {
            return $resp;
        }

        $db = ConfigDatabase::connect();
        $userId = (int) ($session->get('id') ?? 0);
        $requestedPage = max(1, (int) $this->request->getGet('page'));
        $token = trim((string) $this->request->getGet('token'));

        if ($token === '') {
            $token = bin2hex(random_bytes(16));
            $requestedPage = 1;
        }

        $pagerState = $this->getSearchPagerState($session, $token);
        [$pagerState, $pageData] = $this->resolveSearchPage($db, $userId, $pagerState, $requestedPage);
        $this->storeSearchPagerState($session, $token, $pagerState);

        $resolvedPage = $requestedPage;
        if (!isset($pagerState['pages'][$resolvedPage])) {
            $resolvedPage = empty($pagerState['pages'])
                ? 1
                : max(array_map('intval', array_keys($pagerState['pages'])));
        }

        $pageLinks = array_map('intval', array_keys($pagerState['pages']));
        sort($pageLinks);
        if (!empty($pageData['has_next'])) {
            $pageLinks[] = $resolvedPage + 1;
            $pageLinks = array_values(array_unique($pageLinks));
        }

        $data = [
            'session' => $session,
            'bitacora_list' => $pageData['items'],
            'pager' => [
                'token' => $token,
                'page' => $resolvedPage,
                'page_size' => self::SEARCH_PAGE_SIZE,
                'has_prev' => $resolvedPage > 1,
                'has_next' => $pageData['has_next'],
                'prev_page' => $resolvedPage > 1 ? $resolvedPage - 1 : null,
                'next_page' => $pageData['has_next'] ? $resolvedPage + 1 : null,
                'page_links' => $pageLinks,
            ],
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

    private function getSearchPagerState($session, string $token): array
    {
        $allStates = $session->get(self::SEARCH_PAGER_SESSION_KEY);
        if (!is_array($allStates)) {
            return [
                'pages' => [],
                'seen_keys' => [],
                'next_cursor' => null,
                'exhausted' => false,
            ];
        }

        $state = $allStates[$token] ?? null;
        if (!is_array($state)) {
            return [
                'pages' => [],
                'seen_keys' => [],
                'next_cursor' => null,
                'exhausted' => false,
            ];
        }

        $state['pages'] = isset($state['pages']) && is_array($state['pages']) ? $state['pages'] : [];
        $state['seen_keys'] = isset($state['seen_keys']) && is_array($state['seen_keys']) ? $state['seen_keys'] : [];
        $state['next_cursor'] = array_key_exists('next_cursor', $state) ? $state['next_cursor'] : null;
        $state['exhausted'] = !empty($state['exhausted']);

        return $state;
    }

    private function storeSearchPagerState($session, string $token, array $pagerState): void
    {
        $allStates = $session->get(self::SEARCH_PAGER_SESSION_KEY);
        if (!is_array($allStates)) {
            $allStates = [];
        }

        $allStates[$token] = $pagerState;

        if (count($allStates) > 5) {
            $allStates = array_slice($allStates, -5, null, true);
        }

        $session->set(self::SEARCH_PAGER_SESSION_KEY, $allStates);
    }

    private function resolveSearchPage($db, int $userId, array $pagerState, int $requestedPage): array
    {
        if ($requestedPage < 1) {
            $requestedPage = 1;
        }

        if (isset($pagerState['pages'][$requestedPage])) {
            return [$pagerState, $pagerState['pages'][$requestedPage]];
        }

        $maxCachedPage = empty($pagerState['pages']) ? 0 : max(array_map('intval', array_keys($pagerState['pages'])));
        if ($requestedPage > ($maxCachedPage + 1)) {
            $requestedPage = $maxCachedPage + 1;
        }

        while ($maxCachedPage < $requestedPage) {
            [$pagerState, $pageData] = $this->generateNextSearchPage($db, $userId, $pagerState);
            $maxCachedPage++;
            $pagerState['pages'][$maxCachedPage] = $pageData;

            if (empty($pageData['has_next']) && $maxCachedPage < $requestedPage) {
                break;
            }
        }

        if (!isset($pagerState['pages'][$requestedPage])) {
            $requestedPage = max(1, $maxCachedPage);
        }

        return [$pagerState, $pagerState['pages'][$requestedPage] ?? ['items' => [], 'has_next' => false]];
    }

    private function generateNextSearchPage($db, int $userId, array $pagerState): array
    {
        if (!empty($pagerState['exhausted'])) {
            return [$pagerState, ['items' => [], 'has_next' => false]];
        }

        $selected = [];
        $seenKeys = array_fill_keys(array_values($pagerState['seen_keys']), true);
        $cursor = $pagerState['next_cursor'] ?? null;
        $scannedAnyRow = false;
        $displayCursor = $cursor;
        $requiredItems = self::SEARCH_PAGE_SIZE + 1;

        while (count($selected) < $requiredItems) {
            $rows = $this->fetchSearchChunk($db, $userId, $cursor, self::SEARCH_SCAN_CHUNK_SIZE);
            if (empty($rows)) {
                $pagerState['exhausted'] = true;
                break;
            }

            foreach ($rows as $row) {
                $scannedAnyRow = true;
                $cursor = (int) $row['id'];
                $key = $this->buildSearchRowKey($row);
                if ($key === null || isset($seenKeys[$key]) || isset($selected[$key])) {
                    continue;
                }

                $selected[$key] = [
                    '_row_id' => (int) $row['id'],
                    'tramite_id' => !empty($row['tramite_id']) ? (int) $row['tramite_id'] : null,
                    'folio_tramite' => $row['folio_tramite'] ?? null,
                    'contrato' => $row['contrato'] ?? null,
                    'last_change' => $row['last_change'] ?? null,
                    'total_changes' => 0,
                ];

                if (count($selected) <= self::SEARCH_PAGE_SIZE) {
                    $displayCursor = (int) $row['id'];
                }

                if (count($selected) >= $requiredItems) {
                    break;
                }
            }

            if (count($rows) < self::SEARCH_SCAN_CHUNK_SIZE) {
                $pagerState['exhausted'] = true;
                break;
            }
        }

        if (empty($selected)) {
            $pagerState['next_cursor'] = $cursor;
            if (!$scannedAnyRow) {
                $pagerState['exhausted'] = true;
            }
            return [$pagerState, ['items' => [], 'has_next' => false]];
        }

        $hasNext = count($selected) > self::SEARCH_PAGE_SIZE;
        if ($hasNext) {
            array_pop($selected);
        }

        $selected = $this->attachSearchTotals($db, $selected);
        foreach (array_keys($selected) as $key) {
            $pagerState['seen_keys'][] = $key;
        }
        $pagerState['seen_keys'] = array_values(array_unique($pagerState['seen_keys']));
        $pagerState['next_cursor'] = $displayCursor;

        if (!$hasNext) {
            $pagerState['exhausted'] = true;
        }

        return [$pagerState, ['items' => array_values($selected), 'has_next' => $hasNext]];
    }

    private function fetchSearchChunk($db, int $userId, ?int $cursor, int $limit): array
    {
        $builder = $db->table('bitacora')
            ->select('bitacora.id, bitacora.tramite_id, bitacora.folio_tramite, bitacora.created_at AS last_change, t.contrato')
            ->join('tramite t', 't.id = bitacora.tramite_id', 'left')
            ->orderBy('bitacora.id', 'DESC')
            ->limit($limit);

        if ($cursor !== null && $cursor > 0) {
            $builder->where('bitacora.id <', $cursor);
        }

        if (!user_has_global_cliente_access($userId)) {
            $builder->where(get_cliente_filter_sql($userId, 't'), null, false);
        }

        return $builder->get()->getResultArray();
    }

    private function attachSearchTotals($db, array $selected): array
    {
        if (empty($selected)) {
            return [];
        }

        $tramiteIds = [];
        $folios = [];
        foreach ($selected as $item) {
            if (!empty($item['tramite_id'])) {
                $tramiteIds[] = (int) $item['tramite_id'];
                continue;
            }

            if (!empty($item['folio_tramite'])) {
                $folios[] = (string) $item['folio_tramite'];
            }
        }

        $countsByKey = [];
        if (!empty($tramiteIds)) {
            $countRows = $db->table('bitacora')
                ->select('tramite_id, COUNT(*) AS total_changes')
                ->whereIn('tramite_id', array_values(array_unique($tramiteIds)))
                ->groupBy('tramite_id')
                ->get()
                ->getResultArray();

            foreach ($countRows as $countRow) {
                $countsByKey['id:' . (int) $countRow['tramite_id']] = (int) $countRow['total_changes'];
            }
        }

        if (!empty($folios)) {
            $countRows = $db->table('bitacora')
                ->select('folio_tramite, COUNT(*) AS total_changes')
                ->whereIn('folio_tramite', array_values(array_unique($folios)))
                ->groupBy('folio_tramite')
                ->get()
                ->getResultArray();

            foreach ($countRows as $countRow) {
                $countsByKey['folio:' . (string) $countRow['folio_tramite']] = (int) $countRow['total_changes'];
            }
        }

        foreach ($selected as $key => &$item) {
            $item['total_changes'] = $countsByKey[$key] ?? 0;
            unset($item['_row_id']);
        }
        unset($item);

        return $selected;
    }

    private function buildSearchRowKey(array $row): ?string
    {
        if (!empty($row['tramite_id'])) {
            return 'id:' . (int) $row['tramite_id'];
        }

        if (!empty($row['folio_tramite'])) {
            return 'folio:' . (string) $row['folio_tramite'];
        }

        return null;
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
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
        return $groceryCrud;
    }
}

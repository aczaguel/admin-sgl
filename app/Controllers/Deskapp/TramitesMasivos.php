<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Models\BitacoraModel;
use App\Models\ClienteModel;
use App\Models\ClienteDirectoEjecutivoModel;
use App\Models\EntidadesModel;
use App\Models\TraDocStatusModel;
use App\Models\TraTiposModel;
use App\Models\TraTramiteAsociadoModel;
use App\Models\TraUserLogModel;
use Config\Database as ConfigDatabase;

class TramitesMasivos extends BaseController
{
    private function _getDbData()
    {
        $db = (new ConfigDatabase())->default;
        return [
            'adapter' => [
                'driver' => 'mysqli',
                'host' => $db['hostname'],
                'database' => $db['database'],
                'username' => $db['username'],
                'password' => $db['password'],
                'charset' => 'utf8',
            ],
        ];
    }

    private function guardImportAccess(bool $json = true)
    {
        helper(['permissions', 'acl_guard', 'cliente_filter']);

        $session = session();
        [$roles, $perms] = session_roles_perms($session);

        $hasMenu = has_permission('menu_tramites', $perms, $roles);
        $canCreate = can_create_tramite($roles, $perms);

        if ($hasMenu && $canCreate) {
            return null;
        }

        if ($json) {
            return acl_deny('Acceso denegado.', 403, null, true);
        }

        return redirect()->to('/deskapp/dashboard')->with('error', 'Acceso denegado.');
    }

    public function import()
    {
        if ($resp = $this->guardImportAccess(false)) {
            return $resp;
        }

        $session = session();
        $catalogs = $this->loadCatalogs();

        return view('deskapp/extra-pages/tramites_masivos_import', [
            'session' => \Config\Services::session(),
            'username' => $session->get('user_name'),
            'title' => 'Importación Masiva de Trámites',
            'tipo_options' => $catalogs['tra_tipos'],
            'cliente_options' => $catalogs['clientes'],
            'entidad_options' => $catalogs['entidades'],
        ]);
    }

    public function preview()
    {
        if ($resp = $this->guardImportAccess(true)) {
            return $resp;
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Archivo CSV inválido.',
            ]);
        }

        return $this->response->setJSON($this->parseCsvFile($file->getTempName()));
    }

    public function get_ejecutivos($clienteDirectoId)
    {
        if ($resp = $this->guardImportAccess(true)) {
            return $resp;
        }

        helper(['cliente_filter', 'acl_guard']);

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        $clienteDirectoId = (int) $clienteDirectoId;

        if ($clienteDirectoId <= 0) {
            return $this->response->setJSON([]);
        }

        if (!user_has_global_cliente_access($userId)) {
            $clienteIds = get_user_cliente_ids($userId);
            if (empty($clienteIds)) {
                return acl_json_empty(403);
            }

            $db = \Config\Database::connect();
            $row = $db->table('cli_directo')
                ->select('cliente_id')
                ->where('id', $clienteDirectoId)
                ->get(1)
                ->getRowArray();

            $tenantId = $row['cliente_id'] ?? null;
            if (!$tenantId || !in_array((int) $tenantId, array_map('intval', $clienteIds), true)) {
                return acl_json_empty(403);
            }
        }

        $ejecutivoModel = new ClienteDirectoEjecutivoModel($this->_getDbData());
        return $this->response->setJSON($ejecutivoModel->getEjecutivosOptions($clienteDirectoId));
    }

    public function save_row()
    {
        if ($resp = $this->guardImportAccess(true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) ($session->get('id') ?? 0);
        if ($userId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión expirada.',
            ]);
        }

        $payload = $this->request->getJSON(true);
        $row = is_array($payload['row'] ?? null) ? $payload['row'] : [];
        if ($row === []) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibió información de la fila.',
            ]);
        }

        $catalogs = $this->loadCatalogs();
        $seenRows = [];
        $validated = $this->validateCsvRow($row, (int) ($row['linea'] ?? 0), $catalogs, $seenRows);

        if (!empty($validated['errors'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La fila no pasó validación.',
                'errors' => $validated['errors'],
                'existing_tramite_id' => $validated['existing_tramite_id'] ?? null,
                'existing_tramite_folio' => $validated['existing_tramite_folio'] ?? null,
                'existing_tramite_url' => $validated['existing_tramite_url'] ?? null,
            ]);
        }

        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();

        $clienteModel = new ClienteModel($db2);
        $tramiteAsociadoModel = new TraTramiteAsociadoModel();
        $traDocStatusModel = new TraDocStatusModel($db2);
        $bitacoraModel = new BitacoraModel($db2);
        $traUserLogModel = new TraUserLogModel($db2);

        $db->transStart();

        try {
            $folio = $this->generateUniqueFolio($clienteModel, (int) $validated['cli_directo_id'], $db);

            $tramiteData = [
                'folio' => $folio,
                'contrato' => $validated['contrato'],
                'unidad' => $validated['unidad'],
                'serie' => $validated['serie'],
                'placas' => $validated['placas'],
                'tra_tipos_id' => (int) $validated['tra_tipos_id'],
                'cli_directo_id' => (int) $validated['cli_directo_id'],
                'cli_directo_ejecutivo_id' => (int) $validated['cli_directo_ejecutivo_id'],
                'entidad_id' => (int) $validated['entidad_id'],
                'observaciones' => $validated['observaciones'],
                'tra_status_id' => 11,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('tramite')->insert($tramiteData);
            $tramiteId = (int) $db->insertID();

            if ($tramiteId <= 0) {
                throw new \RuntimeException('No se pudo crear el trámite.');
            }

            $tramiteAsociadoModel->insert([
                'tramite_id' => $tramiteId,
                'tra_tipos_id' => (int) $validated['tra_tipos_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $docsQuery = $db->table('tra_tipo_documentos')
                ->where(['tra_tipos_id' => (int) $validated['tra_tipos_id']]);
            if (in_array('es_obligatorio', $db->getFieldNames('tra_tipo_documentos'), true)) {
                $docsQuery->where('es_obligatorio', 1);
            }
            $docs = $docsQuery->get()->getResultArray();

            foreach ($docs as $doc) {
                $traDocStatusModel->insert([
                    'id' => null,
                    'folio_tramite' => $folio,
                    'tramite_id' => $tramiteId,
                    'documento_id' => (int) ($doc['documento_id'] ?? 0),
                    'status_documento_id' => 11,
                    'file' => null,
                    'comentario' => null,
                    'user_id' => $userId,
                ], 'tra_doc_status');
            }

            $bitacoraModel->insert([
                'id' => null,
                'tipo' => 'insert',
                'origen' => 'tramite',
                'folio_tramite' => $folio,
                'tramite_id' => $tramiteId,
                'cambios' => json_encode($this->encontrarDiferencias([], $tramiteData)),
                'user_id' => $userId,
            ], 'bitacora');

            $traUserLogModel->insert([
                'tramite_id' => $tramiteId,
                'user_id' => $userId,
                'tra_status_id' => 11,
            ], 'tra_user_log');
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo guardar la fila.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Fila guardada correctamente.',
            'tramite_id' => $tramiteId,
            'folio' => $folio,
            'tramite_url' => site_url('/deskapp/tramitesn/update/' . $tramiteId),
        ]);
    }

    private function parseCsvFile(string $path): array
    {
        $catalogs = $this->loadCatalogs();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [
                'success' => false,
                'message' => 'No se pudo leer el archivo.',
            ];
        }

        $header = null;
        $rows = [];
        $line = 0;
        $seenRows = [];

        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            $data = $this->normalizeCsvRowEncoding($data);

            if ($header === null) {
                $candidate = array_map([$this, 'canonicalizeHeader'], $data);
                if ($this->hasExpectedHeaders($candidate)) {
                    $header = $candidate;
                }
                continue;
            }

            $nonEmpty = array_filter($data, static function ($item) {
                return trim((string) $item) !== '';
            });
            if ($nonEmpty === []) {
                continue;
            }

            $row = $this->combineRow($header, $data);
            $rows[] = $this->validateCsvRow($row, $line, $catalogs, $seenRows);
        }

        fclose($handle);

        if ($header === null) {
            return [
                'success' => false,
                'message' => 'No se encontró el encabezado esperado en el CSV.',
            ];
        }

        $savableCount = 0;
        foreach ($rows as $row) {
            if (empty($row['errors'])) {
                $savableCount++;
            }
        }

        return [
            'success' => true,
            'total' => count($rows),
            'savable' => $savableCount,
            'rows' => $rows,
        ];
    }

    private function loadCatalogs(): array
    {
        helper('cliente_filter');

        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();
        $session = session();
        $userId = (int) ($session->get('id') ?? 0);

        $traTiposModel = new TraTiposModel($db2);
        $traTipos = $traTiposModel->getTraTiposOptions();

        $entidadesModel = new EntidadesModel($db2);
        $entidades = $entidadesModel->getEntidades();

        $clientesBuilder = $db->table('cli_directo')
            ->select('cli_directo.id, cli_directo.nombre, cli_directo.razon_social, cli_directo.cliente_id');

        $clienteIds = get_user_cliente_ids($userId);
        if ($clienteIds !== null) {
            if (empty($clienteIds)) {
                $clientesBuilder->where('1 = 0');
            } else {
                $clientesBuilder->whereIn('cli_directo.cliente_id', array_map('intval', $clienteIds));
            }
        }

        $clientesRows = $clientesBuilder->get()->getResultArray();

        $cliDirectoIds = array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id'] ?? 0);
        }, $clientesRows)));

        $ejecutivosBuilder = $db->table('cli_directo_ejecutivo')
            ->select('id, nombre, cli_directo_id');
        if (empty($cliDirectoIds)) {
            $ejecutivosBuilder->where('1 = 0');
        } else {
            $ejecutivosBuilder->whereIn('cli_directo_id', $cliDirectoIds);
        }
        $ejecutivosRows = $ejecutivosBuilder->get()->getResultArray();

        $clientesLookup = [];
        $clientesLabels = [];
        foreach ($clientesRows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $label = trim((string) ($row['nombre'] ?: $row['razon_social'] ?: 'Cliente #' . $id));
            $clientesLabels[$id] = $label;

            foreach ([(string) ($row['nombre'] ?? ''), (string) ($row['razon_social'] ?? '')] as $candidate) {
                $key = $this->normalizeKey($candidate);
                if ($key === '') {
                    continue;
                }
                $clientesLookup[$key] = $clientesLookup[$key] ?? [];
                if (!in_array($id, $clientesLookup[$key], true)) {
                    $clientesLookup[$key][] = $id;
                }
            }
        }

        $ejecutivosLookup = [];
        $ejecutivosLabels = [];
        $ejecutivosByCliente = [];
        foreach ($ejecutivosRows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $clienteId = (int) ($row['cli_directo_id'] ?? 0);
            if ($id <= 0 || $clienteId <= 0) {
                continue;
            }

            $name = trim((string) ($row['nombre'] ?? ''));
            if ($name === '') {
                continue;
            }

            $ejecutivosLabels[$id] = $name;
            $ejecutivosByCliente[$clienteId] = $ejecutivosByCliente[$clienteId] ?? [];
            $ejecutivosByCliente[$clienteId][$id] = $name;
            $key = $this->normalizeKey($name);
            if ($key === '') {
                continue;
            }

            $ejecutivosLookup[$clienteId] = $ejecutivosLookup[$clienteId] ?? [];
            $ejecutivosLookup[$clienteId][$key] = $ejecutivosLookup[$clienteId][$key] ?? [];
            if (!in_array($id, $ejecutivosLookup[$clienteId][$key], true)) {
                $ejecutivosLookup[$clienteId][$key][] = $id;
            }
        }

        return [
            'tra_tipos' => $traTipos,
            'tra_tipos_lookup' => $this->buildLookup($traTipos),
            'entidades' => $entidades,
            'entidades_lookup' => $this->buildLookup($entidades, ['CDMX' => 'CIUDAD DE MEXICO']),
            'clientes' => $clientesLabels,
            'clientes_lookup' => $clientesLookup,
            'ejecutivos' => $ejecutivosLabels,
            'ejecutivos_lookup' => $ejecutivosLookup,
            'ejecutivos_by_cliente' => $ejecutivosByCliente,
        ];
    }

    private function validateCsvRow(array $row, int $line, array $catalogs, array &$seenRows): array
    {
        $db = \Config\Database::connect();

        $normalized = [
            'linea' => $line,
            'contrato' => $this->getRowStringValue($row, ['Contrato', 'contrato']),
            'unidad' => $this->getRowStringValue($row, ['Unidad', 'unidad']),
            'serie' => $this->getRowStringValue($row, ['Serie', 'serie']),
            'placas' => $this->getRowStringValue($row, ['Placas', 'placas']),
            'tipo_tramite' => $this->getRowStringValue($row, ['Tipo de Trámite', 'tipo_tramite', 'tipo_tramite_label']),
            'cliente' => $this->getRowStringValue($row, ['Cliente', 'cliente', 'cliente_label']),
            'ejecutivo_cliente' => $this->getRowStringValue($row, ['Ejecutivo de Cliente', 'ejecutivo_cliente', 'ejecutivo_cliente_label']),
            'entidad' => $this->getRowStringValue($row, ['Entidad', 'entidad', 'entidad_label']),
            'observaciones' => $this->getRowStringValue($row, ['Observaciones', 'observaciones']),
            'tra_tipos_id' => $this->getRowIntValue($row, ['tra_tipos_id']),
            'cli_directo_id' => $this->getRowIntValue($row, ['cli_directo_id']),
            'cli_directo_ejecutivo_id' => $this->getRowIntValue($row, ['cli_directo_ejecutivo_id']),
            'entidad_id' => $this->getRowIntValue($row, ['entidad_id']),
            'existing_tramite_id' => $this->getRowIntValue($row, ['existing_tramite_id']),
            'existing_tramite_folio' => $this->getRowStringValue($row, ['existing_tramite_folio']),
            'existing_tramite_url' => $this->getRowStringValue($row, ['existing_tramite_url']),
            'ejecutivo_options' => [],
            'errors' => [],
        ];

        if ($normalized['contrato'] === '') {
            $normalized['errors'][] = 'Contrato requerido.';
        }
        if ($normalized['serie'] === '') {
            $normalized['errors'][] = 'Serie requerida.';
        }
        if ($normalized['tipo_tramite'] === '') {
            $normalized['errors'][] = 'Tipo de Trámite requerido.';
        }
        if ($normalized['cliente'] === '') {
            $normalized['errors'][] = 'Cliente requerido.';
        }
        if ($normalized['ejecutivo_cliente'] === '') {
            $normalized['errors'][] = 'Ejecutivo de Cliente requerido.';
        }
        if ($normalized['entidad'] === '') {
            $normalized['errors'][] = 'Entidad requerida.';
        }

        if ($normalized['tra_tipos_id'] > 0 && isset($catalogs['tra_tipos'][$normalized['tra_tipos_id']])) {
            $normalized['tipo_tramite_label'] = $catalogs['tra_tipos'][$normalized['tra_tipos_id']];
            $normalized['tipo_tramite'] = $normalized['tipo_tramite_label'];
        } else {
            $tipoResolution = $this->resolveLookupValue($catalogs['tra_tipos_lookup'], $normalized['tipo_tramite'], 'Tipo de Trámite', true);
            if ($tipoResolution['id'] !== null) {
                $normalized['tra_tipos_id'] = $tipoResolution['id'];
                $normalized['tipo_tramite_label'] = $catalogs['tra_tipos'][$tipoResolution['id']] ?? $normalized['tipo_tramite'];
                $normalized['tipo_tramite'] = $normalized['tipo_tramite_label'];
            }
            if ($tipoResolution['error'] !== null) {
                $normalized['errors'][] = $tipoResolution['error'];
            }
        }

        $duplicateSerie = trim((string) $normalized['serie']);
        $duplicateTipoId = (int) $normalized['tra_tipos_id'];
        if ($duplicateSerie !== '' && $duplicateTipoId > 0) {
            $duplicateKey = $duplicateTipoId . '|' . $this->normalizeKey($duplicateSerie);
            if (isset($seenRows[$duplicateKey])) {
                $normalized['errors'][] = 'Serie repetida dentro del archivo para el mismo tipo de trámite.';
            } else {
                $seenRows[$duplicateKey] = true;
            }

            $existingTramite = $db->table('tramite')
                ->select('id, folio')
                ->where('tra_tipos_id', $duplicateTipoId)
                ->where('serie', $duplicateSerie)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 year')))
                ->get()
                ->getRowArray();

            if (!empty($existingTramite)) {
                $normalized['existing_tramite_id'] = (int) ($existingTramite['id'] ?? 0);
                $normalized['existing_tramite_folio'] = (string) ($existingTramite['folio'] ?? '');
                $normalized['existing_tramite_url'] = $normalized['existing_tramite_id'] > 0
                    ? site_url('/deskapp/tramitesn/update/' . $normalized['existing_tramite_id'])
                    : '';
                $normalized['errors'][] = 'Ya existe un trámite con la misma serie y el mismo tipo dentro del último año.';
            }
        }

        if ($normalized['cli_directo_id'] > 0 && isset($catalogs['clientes'][$normalized['cli_directo_id']])) {
            $normalized['cliente_label'] = $catalogs['clientes'][$normalized['cli_directo_id']];
            $normalized['cliente'] = $normalized['cliente_label'];
        } else {
            $clienteResolution = $this->resolveLookupValue($catalogs['clientes_lookup'], $normalized['cliente'], 'Cliente');
            if ($clienteResolution['id'] !== null) {
                $normalized['cli_directo_id'] = $clienteResolution['id'];
                $normalized['cliente_label'] = $catalogs['clientes'][$clienteResolution['id']] ?? $normalized['cliente'];
                $normalized['cliente'] = $normalized['cliente_label'];
            }
            if ($clienteResolution['error'] !== null) {
                $normalized['errors'][] = $clienteResolution['error'];
            }
        }

        if ($normalized['entidad_id'] > 0 && isset($catalogs['entidades'][$normalized['entidad_id']])) {
            $normalized['entidad_label'] = $catalogs['entidades'][$normalized['entidad_id']];
            $normalized['entidad'] = $normalized['entidad_label'];
        } else {
            $entidadResolution = $this->resolveLookupValue($catalogs['entidades_lookup'], $normalized['entidad'], 'Entidad');
            if ($entidadResolution['id'] !== null) {
                $normalized['entidad_id'] = $entidadResolution['id'];
                $normalized['entidad_label'] = $catalogs['entidades'][$entidadResolution['id']] ?? $normalized['entidad'];
                $normalized['entidad'] = $normalized['entidad_label'];
            }
            if ($entidadResolution['error'] !== null) {
                $normalized['errors'][] = $entidadResolution['error'];
            }
        }

        if (!empty($normalized['cli_directo_id'])) {
            $normalized['ejecutivo_options'] = $catalogs['ejecutivos_by_cliente'][(int) $normalized['cli_directo_id']] ?? [];

            if ($normalized['cli_directo_ejecutivo_id'] > 0 && isset($normalized['ejecutivo_options'][(int) $normalized['cli_directo_ejecutivo_id']])) {
                $normalized['ejecutivo_cliente_label'] = $normalized['ejecutivo_options'][(int) $normalized['cli_directo_ejecutivo_id']];
                $normalized['ejecutivo_cliente'] = $normalized['ejecutivo_cliente_label'];
            } else {
                $ejecutivosLookup = $catalogs['ejecutivos_lookup'][(int) $normalized['cli_directo_id']] ?? [];
                $ejecutivoResolution = $this->resolveLookupValue($ejecutivosLookup, $normalized['ejecutivo_cliente'], 'Ejecutivo de Cliente');

                if ($ejecutivoResolution['id'] !== null) {
                    $normalized['cli_directo_ejecutivo_id'] = $ejecutivoResolution['id'];
                    $normalized['ejecutivo_cliente_label'] = $catalogs['ejecutivos'][$ejecutivoResolution['id']] ?? $normalized['ejecutivo_cliente'];
                    $normalized['ejecutivo_cliente'] = $normalized['ejecutivo_cliente_label'];
                }
                if ($ejecutivoResolution['error'] !== null) {
                    $normalized['errors'][] = $ejecutivoResolution['error'];
                }
            }
        } elseif ($normalized['ejecutivo_cliente'] !== '') {
            $normalized['errors'][] = 'No se pudo validar el Ejecutivo de Cliente sin resolver primero el Cliente.';
        }

        return $normalized;
    }

    private function getRowStringValue(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return trim((string) $row[$key]);
            }
        }

        return '';
    }

    private function getRowIntValue(array $row, array $keys): int
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return (int) $row[$key];
            }
        }

        return 0;
    }

    private function generateUniqueFolio(ClienteModel $clienteModel, int $cliDirectoId, $db): string
    {
        $prefijo = (string) $clienteModel->getPrefijoByCliDirectoId($cliDirectoId);
        if ($prefijo === '') {
            throw new \RuntimeException('No se pudo resolver el prefijo del cliente para generar el folio.');
        }

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $suffix = substr(preg_replace('/\D+/', '', sprintf('%.6f', microtime(true))), -6);
            if ($suffix === '') {
                $suffix = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            }

            $folio = $prefijo . str_pad($suffix, 6, '0', STR_PAD_LEFT);
            $exists = $db->table('tramite')->select('id')->where('folio', $folio)->get()->getRowArray();
            if (empty($exists)) {
                return $folio;
            }
            usleep(20000);
        }

        throw new \RuntimeException('No se pudo generar un folio único para el trámite.');
    }

    private function resolveLookupValue(array $lookup, string $value, string $label, bool $allowSoundex = false): array
    {
        $key = $this->normalizeKey($value);
        if ($key === '') {
            return ['id' => null, 'error' => null];
        }

        $ids = $lookup[$key] ?? [];
        if ($ids === [] && $allowSoundex) {
            $ids = $this->findSoundexMatches($lookup, $key);
        }

        if ($ids === []) {
            return ['id' => null, 'error' => $label . ' no válido.'];
        }

        if (count($ids) > 1) {
            return ['id' => null, 'error' => $label . ' ambiguo; usa un nombre más específico.'];
        }

        return ['id' => (int) $ids[0], 'error' => null];
    }

    private function findSoundexMatches(array $lookup, string $key): array
    {
        $targetSoundex = $this->buildSoundexKey($key);
        if ($targetSoundex === '') {
            return [];
        }

        $matches = [];
        foreach ($lookup as $candidateKey => $candidateIds) {
            if ($this->buildSoundexKey((string) $candidateKey) !== $targetSoundex) {
                continue;
            }

            foreach ((array) $candidateIds as $candidateId) {
                $candidateId = (int) $candidateId;
                if ($candidateId > 0 && !in_array($candidateId, $matches, true)) {
                    $matches[] = $candidateId;
                }
            }
        }

        return $matches;
    }

    private function buildSoundexKey(string $value): string
    {
        $normalized = $this->normalizeKey($value);
        if ($normalized === '') {
            return '';
        }

        $tokens = array_values(array_filter(explode(' ', $normalized), static function ($token) {
            return $token !== '' && !in_array($token, ['DE', 'DEL', 'LA', 'LAS', 'LOS', 'EL', 'Y'], true);
        }));

        if ($tokens === []) {
            $tokens = array_values(array_filter(explode(' ', $normalized)));
        }

        $codes = [];
        foreach ($tokens as $token) {
            if (ctype_digit($token)) {
                $codes[] = 'N' . $token;
                continue;
            }

            $codes[] = soundex($token);
        }

        return implode('-', $codes);
    }

    private function buildLookup(array $options, array $aliases = []): array
    {
        $map = [];
        foreach ($options as $id => $label) {
            $key = $this->normalizeKey((string) $label);
            if ($key === '') {
                continue;
            }
            $map[$key] = $map[$key] ?? [];
            if (!in_array((int) $id, $map[$key], true)) {
                $map[$key][] = (int) $id;
            }
        }

        foreach ($aliases as $alias => $target) {
            $aliasKey = $this->normalizeKey($alias);
            $targetKey = $this->normalizeKey($target);
            if ($aliasKey === '' || $targetKey === '' || empty($map[$targetKey])) {
                continue;
            }
            $map[$aliasKey] = $map[$targetKey];
        }

        return $map;
    }

    private function hasExpectedHeaders(array $headerCandidate): bool
    {
        $expected = $this->expectedHeaders();
        foreach ($expected as $column) {
            if (!in_array($column, $headerCandidate, true)) {
                return false;
            }
        }
        return true;
    }

    private function expectedHeaders(): array
    {
        return [
            'Contrato',
            'Unidad',
            'Serie',
            'Placas',
            'Tipo de Trámite',
            'Cliente',
            'Ejecutivo de Cliente',
            'Entidad',
            'Observaciones',
        ];
    }

    private function canonicalizeHeader(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $value = trim((string) $value);
        $value = rtrim($value, " *\t\n\r\0\x0B");

        $normalized = $this->normalizeKey($value);
        $map = [
            'CONTRATO' => 'Contrato',
            'UNIDAD' => 'Unidad',
            'SERIE' => 'Serie',
            'PLACAS' => 'Placas',
            'TIPO DE TRAMITE' => 'Tipo de Trámite',
            'TIPO TRAMITE' => 'Tipo de Trámite',
            'TIPO DE SERVICIO' => 'Tipo de Trámite',
            'TIPO SERVICIO' => 'Tipo de Trámite',
            'SERVICIO' => 'Tipo de Trámite',
            'CLIENTE' => 'Cliente',
            'EJECUTIVO DE CLIENTE' => 'Ejecutivo de Cliente',
            'ENTIDAD' => 'Entidad',
            'OBSERVACIONES' => 'Observaciones',
        ];

        return $map[$normalized] ?? $value;
    }

    private function normalizeCsvRowEncoding(array $data): array
    {
        foreach ($data as $index => $value) {
            $data[$index] = $this->normalizeCsvValueEncoding((string) $value, $index === 0);
        }

        return $data;
    }

    private function normalizeCsvValueEncoding(string $value, bool $stripBom = false): string
    {
        if ($stripBom) {
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        }

        $value = str_replace(["\xC2\xA0", "\xA0"], ' ', $value);

        if ($value === '') {
            return $value;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            $value = $this->repairUtf8Mojibake($value);
            return $stripBom ? preg_replace('/^\xEF\xBB\xBF/', '', $value) : $value;
        }

        $detectedEncoding = mb_detect_encoding($value, ['Windows-1252', 'ISO-8859-1', 'UTF-8'], true);
        if ($detectedEncoding === false || $detectedEncoding === 'UTF-8') {
            $detectedEncoding = 'Windows-1252';
        }

        $converted = mb_convert_encoding($value, 'UTF-8', $detectedEncoding);
        $converted = $this->repairUtf8Mojibake($converted);

        return $stripBom ? preg_replace('/^\xEF\xBB\xBF/', '', $converted) : $converted;
    }

    private function repairUtf8Mojibake(string $value): string
    {
        if (!$this->looksLikeUtf8Mojibake($value)) {
            return $value;
        }

        $candidate = @mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
        if (!is_string($candidate) || $candidate === '' || !mb_check_encoding($candidate, 'UTF-8')) {
            return $value;
        }

        if ($this->mojibakeScore($candidate) >= $this->mojibakeScore($value)) {
            return $value;
        }

        return $candidate;
    }

    private function looksLikeUtf8Mojibake(string $value): bool
    {
        return preg_match('/(?:Ã.|Â.|â.|ð.|Ð.|ï¿½|�)/u', $value) === 1;
    }

    private function mojibakeScore(string $value): int
    {
        preg_match_all('/(?:Ã.|Â.|â.|ð.|Ð.|ï¿½|�)/u', $value, $matches);
        return count($matches[0]);
    }

    private function normalizeKey(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = mb_strtoupper($value, 'UTF-8');
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        if ($normalized !== false) {
            $value = $normalized;
        }

        $value = preg_replace('/[^A-Z0-9 ]+/', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim((string) $value);
    }

    private function combineRow(array $header, array $data): array
    {
        $row = [];
        foreach ($header as $idx => $column) {
            $row[$column] = trim((string) ($data[$idx] ?? ''));
        }
        return $row;
    }

    private function encontrarDiferencias(array $datosOriginales, array $datosNuevos): array
    {
        $diferencias = [];
        foreach ($datosNuevos as $clave => $valor) {
            $diferencias[$clave] = [
                'valor_original' => $datosOriginales[$clave] ?? '',
                'valor_nuevo' => $valor,
            ];
        }
        return $diferencias;
    }
}
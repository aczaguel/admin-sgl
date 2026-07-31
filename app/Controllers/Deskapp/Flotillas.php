<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use Config\Database as ConfigDatabase;
use App\Models\ClienteDirectoModel;
use App\Models\TraTiposModel;
use App\Models\EntidadesModel;
use App\Models\ClienteModel;
use App\Models\TraTramiteAsociadoModel;
use App\Models\TraDocStatusModel;
use App\Models\BitacoraModel;
use App\Models\TraUserLogModel;

class Flotillas extends BaseController
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
            // FR-01: Sync MySQL session timezone with PHP (America/Mexico_City)
            'driver_options' => [
                MYSQLI_INIT_COMMAND => "SET time_zone = '-06:00'",
            ],
            ]
        ];
    }
    private function guardImportAccess(bool $json = true)
    {
        helper(['permissions', 'acl_guard']);
        $session = session();
        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('menu_tramites', $perms, $roles)) {
            if ($json) {
                return acl_deny('Acceso denegado.', 403, null, true);
            }
            return redirect()->to('/deskapp/dashboard')->with('error', 'Acceso denegado.');
        }
        return null;
    }

    public function import()
    {
        if ($resp = $this->guardImportAccess(false)) {
            return $resp;
        }

        $session = session();
        $data['session'] = \Config\Services::session();
        $data['username'] = $session->get('user_name');

        $db2 = $this->_getDbData();
        $clienteDirecto = new ClienteDirectoModel($db2);
        $data['cli_directo_options'] = $clienteDirecto->getClientesDirectosOptions();

        return view('deskapp/extra-pages/flotillas_import', $data);
    }

    public function preview()
    {
        if ($resp = $this->guardImportAccess(true)) {
            return $resp;
        }

        $cliDirectoId = (int) $this->request->getPost('cli_directo_id');
        if ($cliDirectoId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente es requerido.'
            ]);
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Archivo CSV inválido.'
            ]);
        }

        $parseResult = $this->parseCsvFile($file->getTempName(), $cliDirectoId);
        if (!empty($parseResult['preview_rows'])) {
            $parseResult['rows'] = $parseResult['preview_rows'];
            unset($parseResult['preview_rows']);
        }
        return $this->response->setJSON($parseResult);
    }

    public function store()
    {
        if ($resp = $this->guardImportAccess(true)) {
            return $resp;
        }

        $session = session();
        $userId = (int) $session->get('id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión expirada.'
            ]);
        }

        $cliDirectoId = (int) $this->request->getPost('cli_directo_id');
        if ($cliDirectoId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente es requerido.'
            ]);
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Archivo CSV inválido.'
            ]);
        }

        $parseResult = $this->parseCsvFile($file->getTempName(), $cliDirectoId);
        if (empty($parseResult['success'])) {
            return $this->response->setJSON($parseResult);
        }

        if (!empty($parseResult['errors'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Existen errores de validación.',
                'errors' => $parseResult['errors']
            ]);
        }

        $rows = $parseResult['rows'] ?? [];
        if (empty($rows)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay filas válidas para importar.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $flotillaName = (string) $this->request->getPost('flotilla_nombre');
        $flotillaName = trim($flotillaName);
        if ($flotillaName === '') {
            $flotillaName = 'Flotilla ' . date('Y-m-d H:i');
        }

        $flotillaData = [
            'nombre' => $flotillaName,
            'cliente_id' => $cliDirectoId,
            'archivo_origen' => $file->getName(),
            'total_registros' => count($rows),
            'total_importados' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $userId
        ];

        $db->table('flotilla')->insert($flotillaData);
        $flotillaId = (int) $db->insertID();

        $db2 = $this->_getDbData();
        $clienteModel = new ClienteModel($db2);
        $tramiteAsociadoModel = new TraTramiteAsociadoModel();
        $traDocStatusModel = new TraDocStatusModel($db2);
        $bitacoraModel = new BitacoraModel($db2);
        $traUserLogModel = new TraUserLogModel($db2);

        $imported = 0;
        foreach ($rows as $row) {
            $folio = $clienteModel->getPrefijoConUltimosSeisDigitos($cliDirectoId);

            $tramiteData = [
                'folio' => $folio,
                'contrato' => $row['contrato'],
                'placas' => $row['placas'],
                'serie' => $row['serie'],
                'unidad' => $row['vehiculo'],
                'entidad_id' => $row['entidad_id'],
                'cli_directo_id' => $cliDirectoId,
                'tra_tipos_id' => $row['tra_tipos_id'],
                'tra_status_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('tramite')->insert($tramiteData);
            $tramiteId = (int) $db->insertID();

            $db->table('flotilla_tramite')->insert([
                'flotilla_id' => $flotillaId,
                'tramite_id' => $tramiteId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $tramiteAsociadoModel->insert([
                'tramite_id' => $tramiteId,
                'tra_tipos_id' => $row['tra_tipos_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $docsQuery = $db->table('tra_tipo_documentos')
                ->where(['tra_tipos_id' => $row['tra_tipos_id']]);
            if (in_array('es_obligatorio', $db->getFieldNames('tra_tipo_documentos'), true)) {
                $docsQuery->where('es_obligatorio', 1);
            }
            $docs = $docsQuery->get()->getResultArray();

            foreach ($docs as $doc) {
                $traDocStatusModel->insert([
                    'id' => null,
                    'folio_tramite' => $folio,
                    'tramite_id' => $tramiteId,
                    'documento_id' => (int) $doc['documento_id'],
                    'status_documento_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS,
                    'file' => null,
                    'comentario' => null,
                    'user_id' => $userId
                ]);
            }

            $diferencias = $this->encontrarDiferencias($tramiteData, []);
            $bitacoraModel->insert([
                'id' => null,
                'tipo' => 'insert',
                'origen' => 'tramite',
                'folio_tramite' => $folio,
                'tramite_id' => $tramiteId,
                'cambios' => json_encode($diferencias),
                'user_id' => $userId
            ], 'bitacora');

            $traUserLogModel->insert([
                'tramite_id' => $tramiteId,
                'user_id' => $userId,
                'tra_status_id' => SGL_TRA_STATUS_RECOLECCION_DCTOS
            ], 'tra_user_log');

            $imported++;
        }

        $db->table('flotilla')
            ->where('id', $flotillaId)
            ->update(['total_importados' => $imported]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al importar la flotilla.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Flotilla importada correctamente.',
            'flotilla_id' => $flotillaId,
            'importados' => $imported
        ]);
    }

    private function parseCsvFile(string $path, int $cliDirectoId): array
    {
        $db = \Config\Database::connect();
        $db2 = $this->_getDbData();

        $traTiposModel = new TraTiposModel($db2);
        $traTiposOptions = $traTiposModel->getTraTiposOptions();

        $entidadesModel = new EntidadesModel($db2);
        $entidades = $entidadesModel->getEntidades();

        $tipoMap = $this->buildLookup($traTiposOptions);
        $entidadMap = $this->buildLookup($entidades);

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [
                'success' => false,
                'message' => 'No se pudo leer el archivo.'
            ];
        }

        $header = null;
        $rows = [];
        $errors = [];
        $line = 0;
        $headerFound = false;

        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            if ($header === null) {
                $headerCandidate = array_map('trim', $data);
                if (in_array('Contrato', $headerCandidate, true) && in_array('Concepto', $headerCandidate, true)) {
                    $header = $headerCandidate;
                    $headerFound = true;
                }
                continue;
            }

            if (count(array_filter($data)) === 0) {
                continue;
            }

            $row = $this->combineRow($header, $data);
            $tipoPago = strtoupper(trim((string) ($row['Tipo de Pago'] ?? '')));
            if ($tipoPago !== 'HONORARIO') {
                continue;
            }

            $contrato = trim((string) ($row['Contrato'] ?? ''));
            $placas = trim((string) ($row['PLACA'] ?? ''));
            $serie = trim((string) ($row['SERIE'] ?? ''));
            $entidadRaw = trim((string) ($row['Entidad'] ?? ''));
            $vehiculo = trim((string) ($row['VEHICULO'] ?? ''));
            $tipoRaw = trim((string) ($row['Concepto'] ?? ''));

            $rowErrors = [];
            if ($contrato === '') {
                $rowErrors[] = 'Contrato requerido';
            }
            if ($placas === '') {
                $rowErrors[] = 'Placa requerida';
            }
            if ($serie === '') {
                $rowErrors[] = 'Serie requerida';
            }

            $entidadKey = $this->normalizeKey($entidadRaw);
            if ($entidadKey === 'CDMX') {
                $entidadKey = $this->normalizeKey('CIUDAD DE MEXICO');
            }
            $entidadId = $entidadMap[$entidadKey] ?? null;
            if (!$entidadId) {
                $rowErrors[] = 'Entidad no valida';
            }

            $tipoKey = $this->normalizeKey($tipoRaw);
            if ($tipoKey === 'RENOV DE TC') {
                $tipoKey = $this->normalizeKey('TARJETA DE CIRCULACION');
            }
            $traTiposId = $tipoMap[$tipoKey] ?? null;
            if (!$traTiposId) {
                $rowErrors[] = 'Tipo de tramite no valido';
            }

            $existingTramite = null;
            if ($contrato !== '') {
                $existingTramite = $db->table('tramite')
                    ->select('id, tra_tipos_id, contrato')
                    ->where('contrato', $contrato)
                    ->get()
                    ->getRowArray();
                if (!empty($existingTramite)) {
                    $rowErrors[] = 'Contrato ya existe';
                }
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'linea' => $line,
                    'contrato' => $contrato,
                    'errores' => $rowErrors,
                    'existing_tramite_id' => $existingTramite['id'] ?? null,
                    'existing_tipo_id' => $existingTramite['tra_tipos_id'] ?? null,
                    'existing_tipo_label' => $existingTramite ? ($traTiposOptions[$existingTramite['tra_tipos_id']] ?? '') : '',
                    'existing_contrato' => $existingTramite['contrato'] ?? null
                ];
                continue;
            }

            $rows[] = [
                'contrato' => $contrato,
                'placas' => $placas,
                'serie' => $serie,
                'vehiculo' => $vehiculo,
                'entidad_id' => (int) $entidadId,
                'entidad_label' => $entidades[$entidadId] ?? '',
                'tra_tipos_id' => (int) $traTiposId,
                'tipo_label' => $traTiposOptions[$traTiposId] ?? ''
            ];
        }

        fclose($handle);

        if (!$headerFound) {
            return [
                'success' => false,
                'message' => 'No se encontró el encabezado esperado en el CSV.'
            ];
        }

        return [
            'success' => true,
            'total' => count($rows),
            'errors' => $errors,
            'rows' => $rows,
            'preview_rows' => array_slice($rows, 0, 50)
        ];
    }

    private function buildLookup(array $options): array
    {
        $map = [];
        foreach ($options as $id => $label) {
            $key = $this->normalizeKey((string) $label);
            if ($key !== '') {
                $map[$key] = (int) $id;
            }
        }
        return $map;
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
        return trim($value);
    }

    private function combineRow(array $header, array $data): array
    {
        $row = [];
        foreach ($header as $idx => $col) {
            $row[$col] = $data[$idx] ?? '';
        }
        return $row;
    }

    private function encontrarDiferencias($datos1, $datos2)
    {
        $diferencias = [];
        foreach ($datos1 as $clave => $valor) {
            if (array_key_exists($clave, $datos2) && $datos2[$clave] !== $valor) {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => $datos2[$clave]
                ];
            } else {
                $diferencias[$clave] = [
                    'valor_original' => $valor,
                    'valor_nuevo' => ''
                ];
            }
        }
        return $diferencias;
    }
}

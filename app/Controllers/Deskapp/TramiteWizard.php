<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use App\Services\ExternalTramiteService;
use App\Models\TramitesModel;
use App\Models\TraTiposModel;
use App\Models\EntidadesModel;
use App\Models\EntMunicipioModel;
use App\Models\ClienteModel;
use App\Models\ClienteDirectoModel;
use App\Models\ClienteDirectoEjecutivoModel;
use App\Models\EmpresaGestoraModel;
use App\Models\GestorModel;
use App\Models\TraStatusModel;
use App\Models\UserModel;
use CodeIgniter\Database\Config;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TramiteWizard extends BaseController
{
    protected $db;
    
    public function __construct()
    {
        helper(['form', 'url', 'cliente_filter', 'cliente_context', 'permissions', 'acl_guard', 'filestorage']);
        $this->db = \Config\Database::connect();
    }

    /**
     * Vista principal del wizard
     */
    public function index()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            return redirect()->to('/deskapp/auth/login');
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('create_tramite', $perms, $roles)) {
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para crear trámites.');
        }

        $requested = $this->request->getGet('cliente_id');
        $clienteIdFiltro = resolve_active_cliente_id($userId, $requested);

        // Cargar datos para los selectores
        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'tra_tipos' => $this->getTraTipos(),
            'entidades' => $this->getEntidades(),
            'clientes' => $this->getClientesFiltrados($userId, $clienteIdFiltro),
            'empresas_gestoras' => $this->getEmpresasGestoras(),
            'usuarios' => $this->getUsuarios(),
            'folio_sugerido' => $this->generarFolioSugerido(),
        ];

        return view('deskapp/tramite_wizard/index', $data);
    }

    /**
     * Listado de trámites creados con wizard
     */
    public function listado()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            return redirect()->to('/deskapp/auth/login');
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!(has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para ver trámites.');
        }

        $requested = $this->request->getGet('cliente_id');
        $clienteIdFiltro = resolve_active_cliente_id($userId, $requested);

        // Aplicar filtro multi-tenancy
        $builder = $this->db->table('tramite t');
        $builder->select('t.*, 
                         tt.tipo_tramite,
                         cd.razon_social as cliente,
                         eg.razon_social as empresa_gestora,
                         g.nombre as gestor,
                         ts.tra_status as status,
                         u.firstname, u.lastname,
                         DATEDIFF(NOW(), t.created_at) as dias_desde_creacion');
        $builder->join('tra_tipos tt', 'tt.id = t.tra_tipos_id', 'left');
        $builder->join('cli_directo cd', 'cd.id = t.cli_directo_id', 'left');
        $builder->join('ges_empresa_gestora eg', 'eg.id = t.empresa_gestora_id', 'left');
        $builder->join('ges_gestor g', 'g.id = t.gestor_id', 'left');
        $builder->join('tra_status ts', 'ts.id = t.tra_status_id', 'left');
        $builder->join('users u', 'u.id = t.user_id', 'left');

        // Restricción opcional por permisos: ver únicamente lo que el usuario generó.
        if (has_permission('wizard_list_only_own', $perms, $roles)) {
            $builder->where('t.user_id', (int) $userId);
        }

        // Filtro multi-tenancy + cliente activo
        if (!empty($clienteIdFiltro)) {
            $builder->where('cd.cliente_id', (int) $clienteIdFiltro);
        } elseif (!user_has_global_cliente_access($userId)) {
            $clienteIds = get_user_cliente_ids($userId);
            if (empty($clienteIds)) {
                $builder->where('1 = 0'); // Usuario sin clientes asignados
            } else {
                $builder->whereIn('cd.cliente_id', array_map('intval', $clienteIds));
            }
        }

        $builder->orderBy('t.id', 'DESC');
        $builder->limit(100);

        $tramites = $builder->get()->getResultArray();

        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'tramites' => $tramites,
        ];

        return view('deskapp/tramite_wizard/listado', $data);
    }

    /**
     * Guardar trámite completo
     */
    public function guardar()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            // Mantener mensaje; forzar JSON para fetch.
            return acl_deny('Sesión expirada', 200, null, true);
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!can_create_tramite($roles, $perms)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        // Validar datos del trámite
        $validacion = $this->validarDatosTramite();
        if (!$validacion['success']) {
            return $this->response->setJSON($validacion);
        }

        $service = new ExternalTramiteService($this->db);
        $result = $service->createFromWizardPayload($this->request->getPost(), $this->request->getFiles(), $userId);
        $statusCode = (int) ($result['statusCode'] ?? 200);
        unset($result['statusCode']);

        return $this->response->setStatusCode($statusCode)->setJSON($result);
    }

    /**
     * Guardar borrador del trámite
     */
    public function guardar_borrador()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            // Mantener mensaje; forzar JSON para fetch.
            return acl_deny('Sesión expirada', 200, null, true);
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!can_create_tramite($roles, $perms)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        try {
            $datosBorrador = [
                'user_id' => $userId,
                'datos' => json_encode($this->request->getPost()),
                'paso_actual' => $this->request->getPost('paso_actual') ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Verificar si existe un borrador previo
            $builder = $this->db->table('tramite_borrador');
            $builder->where('user_id', $userId);
            $borradorExistente = $builder->get()->getRow();

            if ($borradorExistente) {
                // Crear nuevo builder para update
                $builderUpdate = $this->db->table('tramite_borrador');
                $builderUpdate->where('user_id', $userId);
                $builderUpdate->update($datosBorrador);
            } else {
                $datosBorrador['created_at'] = date('Y-m-d H:i:s');
                // Crear nuevo builder para insert
                $builderInsert = $this->db->table('tramite_borrador');
                $builderInsert->insert($datosBorrador);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Borrador guardado'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar borrador: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Recuperar borrador guardado
     */
    public function recuperar_borrador()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            // Mantener mensaje; forzar JSON para fetch.
            return acl_deny('Sesión expirada', 200, null, true);
        }

        [$roles, $perms] = session_roles_perms($session);
        if (!(has_permission('read_tramite', $perms, $roles) || has_permission('read_final_tramite', $perms, $roles))) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        $builder = $this->db->table('tramite_borrador');
        $builder->where('user_id', $userId);
        $borrador = $builder->get()->getRow();

        if ($borrador) {
            return $this->response->setJSON([
                'success' => true,
                'borrador' => json_decode($borrador->datos, true),
                'paso_actual' => $borrador->paso_actual
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'No hay borrador guardado'
        ]);
    }

    /**
     * Exportar listado a Excel
     */
    public function exportar_excel()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            return redirect()->to('/deskapp/auth/login');
        }

        [$roles, $perms] = session_roles_perms($session);
        $canExport = has_permission('export_tramite', $perms, $roles) || has_permission('export_final_tramite', $perms, $roles);
        if (!$canExport) {
            return acl_deny_text('Acceso denegado', 403);
        }

        // Obtener filtros
        $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-01-01');
        $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d');
        $statusId = $this->request->getGet('status_id');

        // Consultar trámites
        $builder = $this->db->table('tramite t');
        $builder->select('t.id, t.folio, t.contrato, t.unidad, t.serie, t.placas,
                         t.created_at, t.started_at,
                         tt.tipo_tramite,
                         rm.ent_municipality as municipio,
                         cd.razon_social as cliente,
                         cde.nombre as ejecutivo_cliente,
                         eg.razon_social as empresa_gestora,
                         g.nombre as gestor,
                         ts.tra_status as status,
                         CONCAT(u.firstname, " ", u.lastname) as responsable,
                         t.observaciones');
        $builder->join('tra_tipos tt', 'tt.id = t.tra_tipos_id', 'left');
        $builder->join('rel_ent_municipio rm', 'rm.id = t.ent_municipio_id', 'left');
        $builder->join('cli_directo cd', 'cd.id = t.cli_directo_id', 'left');
        $builder->join('cli_directo_ejecutivo cde', 'cde.id = t.cli_directo_ejecutivo_id', 'left');
        $builder->join('ges_empresa_gestora eg', 'eg.id = t.empresa_gestora_id', 'left');
        $builder->join('ges_gestor g', 'g.id = t.gestor_id', 'left');
		$builder->join('users u', 'u.id = t.user_id', 'left');

        $builder->where('t.created_at >=', $fechaInicio);
        $builder->where('t.created_at <=', $fechaFin . ' 23:59:59');

        if ($statusId) {
            $builder->where('t.tra_status_id', $statusId);
        }

        // Filtro multi-tenancy
        if (!user_has_global_cliente_access($userId)) {
            $clienteIds = get_user_cliente_ids($userId);
            if (empty($clienteIds)) {
                $builder->where('1 = 0');
            } else {
                $builder->whereIn('cd.cliente_id', array_map('intval', $clienteIds));
            }
        }

        $builder->orderBy('t.id', 'DESC');
        $tramites = $builder->get()->getResultArray();

        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados
        $headers = [
            'A1' => 'ID',
            'B1' => 'Folio',
            'C1' => 'Contrato',
            'D1' => 'Unidad',
            'E1' => 'Serie',
            'F1' => 'Placas',
            'G1' => 'Tipo Trámite',
            'H1' => 'Municipio',
            'I1' => 'Cliente',
            'J1' => 'Ejecutivo Cliente',
            'K1' => 'Empresa Gestora',
            'L1' => 'Gestor',
            'M1' => 'Status',
            'N1' => 'Responsable',
            'O1' => 'Fecha Creación',
            'P1' => 'Fecha Inicio',
            'Q1' => 'Observaciones'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Estilo para encabezados
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Agregar datos
        $row = 2;
        foreach ($tramites as $tramite) {
            $sheet->setCellValue('A' . $row, $tramite['id']);
            $sheet->setCellValue('B' . $row, $tramite['folio']);
            $sheet->setCellValue('C' . $row, $tramite['contrato']);
            $sheet->setCellValue('D' . $row, $tramite['unidad']);
            $sheet->setCellValue('E' . $row, $tramite['serie']);
            $sheet->setCellValue('F' . $row, $tramite['placas']);
            $sheet->setCellValue('G' . $row, $tramite['tipo_tramite']);
            $sheet->setCellValue('H' . $row, $tramite['municipio']);
            $sheet->setCellValue('I' . $row, $tramite['cliente']);
            $sheet->setCellValue('J' . $row, $tramite['ejecutivo_cliente']);
            $sheet->setCellValue('K' . $row, $tramite['empresa_gestora']);
            $sheet->setCellValue('L' . $row, $tramite['gestor']);
            $sheet->setCellValue('M' . $row, $tramite['status']);
            $sheet->setCellValue('N' . $row, $tramite['responsable']);
            $sheet->setCellValue('O' . $row, $tramite['created_at']);
            $sheet->setCellValue('P' . $row, $tramite['started_at']);
            $sheet->setCellValue('Q' . $row, $tramite['observaciones']);
            $row++;
        }

        // Autoajustar columnas
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'tramites_' . date('Y-m-d_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Obtener municipios por entidad (AJAX)
     */
    public function get_municipios()
    {
        helper(['acl_guard', 'permissions']);
        $session = session();
        if ($resp = acl_require_login(null, 'Sesión expirada', true)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('read_tramite', $perms, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        $entidadId = $this->request->getPost('entidad_id');
        
        $builder = $this->db->table('rel_ent_municipio');
        $builder->select('ent_municipality_id as id, ent_municipality as municipio');
        
        if ($entidadId && is_numeric($entidadId)) {
            $builder->where('id_entity', (int) $entidadId);
        }
        
        $builder->orderBy('ent_municipality', 'ASC');
        $municipios = $builder->get()->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'municipios' => $municipios
        ]);
    }

    /**
     * Obtener ejecutivos por cliente (AJAX)
     */
    public function get_ejecutivos_cliente()
    {
        helper(['acl_guard', 'permissions']);
        $session = session();
        if ($resp = acl_require_login(null, 'Sesión expirada', true)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('read_tramite', $perms, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        $clienteId = $this->request->getPost('cliente_id');

        if (!is_numeric($clienteId)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Cliente inválido']);
        }

        // Validar multi-tenancy: el usuario debe tener acceso al cliente dueño del cli_directo
        if (!user_has_global_cliente_access($userId)) {
            $row = $this->db->table('cli_directo')
                ->select('cliente_id')
                ->where('id', (int) $clienteId)
                ->get()
                ->getRowArray();

            if (empty($row['cliente_id']) || !has_access_to_cliente((int) $row['cliente_id'], $userId)) {
                return acl_deny('Acceso denegado', 403, null, true);
            }
        }
        
        $builder = $this->db->table('cli_directo_ejecutivo');
        $builder->select('id, nombre');
        $builder->where('cli_directo_id', (int) $clienteId);
        $builder->orderBy('nombre', 'ASC');
        
        $ejecutivos = $builder->get()->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'ejecutivos' => $ejecutivos
        ]);
    }

    /**
     * Obtener gestores por empresa (AJAX)
     */
    public function get_gestores()
    {
        helper(['acl_guard', 'permissions']);
        $session = session();
        if ($resp = acl_require_login(null, 'Sesión expirada', true)) {
            return $resp;
        }

        $userId = (int) ($session->get('id') ?? 0);

        [$roles, $perms] = session_roles_perms($session);
        if (!has_permission('read_tramite', $perms, $roles)) {
            return acl_deny('Acceso denegado', 403, null, true);
        }

        $empresaId = $this->request->getPost('empresa_id');
        if (!is_numeric($empresaId)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Empresa inválida']);
        }
        
        $builder = $this->db->table('ges_gestor');
        $builder->select('id, nombre');
        $builder->where('empresa_gestora_id', (int) $empresaId);
        $builder->orderBy('nombre', 'ASC');
        
        $gestores = $builder->get()->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'gestores' => $gestores
        ]);
    }

    // ========================================================================
    // MÉTODOS PRIVADOS
    // ========================================================================

    private function getTraTipos()
    {
        $builder = $this->db->table('tra_tipos');
        $builder->select('id, tipo_tramite');
        $builder->orderBy('tipo_tramite', 'ASC');
        return $builder->get()->getResultArray();
    }

    private function getEntidades()
    {
        $builder = $this->db->table('entidad');
        $builder->select('id, entidad');
        $builder->orderBy('entidad', 'ASC');
        return $builder->get()->getResultArray();
    }

    private function getClientesFiltrados($userId, $clienteIdFiltro = null)
    {
        $builder = $this->db->table('cli_directo');
        $builder->select('id, razon_social, cliente_id');

        if (!empty($clienteIdFiltro)) {
            // Cliente activo (tabla cliente.id)
            $builder->where('cliente_id', (int) $clienteIdFiltro);
        }
        
        // Aplicar filtro multi-tenancy
        if (!user_has_global_cliente_access($userId)) {
            $clienteIds = get_user_cliente_ids($userId);
            if (empty($clienteIds)) {
                $builder->where('1 = 0');
            } else {
                $builder->whereIn('cliente_id', array_map('intval', $clienteIds));
            }
        }
        
        $builder->orderBy('razon_social', 'ASC');
        return $builder->get()->getResultArray();
    }

    private function getEmpresasGestoras()
    {
        $builder = $this->db->table('ges_empresa_gestora');
        $builder->select('id, razon_social');
        $builder->orderBy('razon_social', 'ASC');
        return $builder->get()->getResultArray();
    }

    private function getUsuarios()
    {
        $builder = $this->db->table('users');
        $builder->select('id, CONCAT(firstname, " ", lastname) as nombre');
        $builder->orderBy('firstname', 'ASC');
        return $builder->get()->getResultArray();
    }

    private function generarFolioSugerido()
    {
        $builder = $this->db->table('tramite');
        $builder->selectMax('id');
        $result = $builder->get()->getRow();
        
        $nextId = ($result->id ?? 0) + 1;
        return 'TR-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    private function validarDatosTramite()
    {
        $rules = [
            'folio' => 'required',
            'contrato' => 'required',
            'serie' => 'required',
            'tra_tipos_id' => 'required|numeric',
            'ent_municipio_id' => 'required|numeric',
            'cli_directo_id' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return [
                'success' => false,
                'message' => 'Datos incompletos',
                'errors' => $this->validator->getErrors()
            ];
        }

        return ['success' => true];
    }

    private function validarAccesoCliente($userId, $clienteId)
    {
        if (user_has_global_cliente_access($userId)) {
            return true;
        }

        // $clienteId aquí es cli_directo_id; validar por su cliente_id
        $row = $this->db->table('cli_directo')
            ->select('cliente_id')
            ->where('id', (int) $clienteId)
            ->get()
            ->getRowArray();

        if (empty($row['cliente_id'])) {
            return false;
        }

        $clienteIds = get_user_cliente_ids($userId);
        return is_array($clienteIds) && in_array((int) $row['cliente_id'], array_map('intval', $clienteIds), true);
    }

    private function guardarArchivos($tramiteId, $archivos)
    {
        helper('filestorage');

        $storage    = service('fileStorage');
        $documentos = [];

        foreach ($archivos as $archivo) {
            // Defensive: an already-moved handle cannot be persisted again.
            if ($archivo->hasMoved()) {
                continue;
            }

            // Req 6.3: missing/invalid file or empty temporary path => 400, do NOT call put.
            $tempName = $archivo->getTempName();
            if (!$archivo->isValid() || $tempName === '' || $tempName === null) {
                return [
                    'success'    => false,
                    'statusCode' => 400,
                    'message'    => 'No se recibió un archivo válido',
                    'documentos' => $documentos,
                ];
            }

            // Build a traversal-safe relative key under tramites/<id>/... (canonical layout).
            $key = buildKey('tramites', (int) $tramiteId, $archivo->getClientName());

            // Req 6.2 / 6.6: persist through the storage service using the uploaded temp path.
            // A failed put returns 500 and records NO database reference.
            if (!$storage->put($key, $tempName)) {
                log_message('error', 'TramiteWizard::guardarArchivos put failed for key {key}', ['key' => $key]);

                return [
                    'success'    => false,
                    'statusCode' => 500,
                    'message'    => 'No se pudo guardar el archivo',
                    'documentos' => $documentos,
                ];
            }

            // Req 6.5: store the canonical value (bare filename); never an absolute URL.
            $storedValue = basename($key);

            try {
                // Registrar en tabla de documentos. The relative key is stored in
                // `ruta` so the reference is driver-agnostic (resolvable by file_url()).
                $this->db->table('tra_doc_status')->insert([
                    'tramite_id'      => $tramiteId,
                    'nombre_archivo'  => $storedValue,
                    'nombre_original' => $archivo->getClientName(),
                    'ruta'            => $key,
                    'tipo'            => $archivo->getClientMimeType(),
                    'tamano'          => $archivo->getSize(),
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                // Req 7.1: DB write failed after a successful put => compensating delete
                // exactly once for the just-written key, then return 500 (no referencing row).
                $storage->delete($key);
                log_message(
                    'error',
                    'TramiteWizard::guardarArchivos DB write failed after put for key {key}: {msg}',
                    ['key' => $key, 'msg' => $e->getMessage()]
                );

                return [
                    'success'    => false,
                    'statusCode' => 500,
                    'message'    => 'Error al guardar el documento',
                    'documentos' => $documentos,
                ];
            }

            // Req 6.5: return the browser URL produced by the URL resolver for the stored value.
            $documentos[] = [
                'nombre_archivo' => $storedValue,
                'url'            => file_url($storedValue, 'tramites', (int) $tramiteId),
            ];
        }

        return [
            'success'    => true,
            'statusCode' => 200,
            'message'    => 'Archivos guardados',
            'documentos' => $documentos,
        ];
    }

    private function registrarBitacora($tramiteId, $descripcion, $userId)
    {
        $this->db->table('bitacora')->insert([
            'tramite_id' => $tramiteId,
            'descripcion' => $descripcion,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

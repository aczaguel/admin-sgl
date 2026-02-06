<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
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
        helper(['form', 'url', 'cliente_filter']);
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

        // Cargar datos para los selectores
        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'tra_tipos' => $this->getTraTipos(),
            'entidades' => $this->getEntidades(),
            'clientes' => $this->getClientesFiltrados($userId),
            'empresas_gestoras' => $this->getEmpresasGestoras(),
            'usuarios' => $this->getUsuarios(),
            'folio_sugerido' => $this->generarFolioSugerido()
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

        // Filtro multi-tenancy
        if (!user_is_admin($userId)) {
            $clienteIds = get_user_cliente_ids($userId);
            if (empty($clienteIds)) {
                $builder->where('1 = 0'); // Usuario sin clientes asignados
            } else {
                $builder->whereIn('t.cli_directo_id', $clienteIds);
            }
        }

        $builder->orderBy('t.id', 'DESC');
        $builder->limit(100);

        $tramites = $builder->get()->getResultArray();

        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'tramites' => $tramites
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
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión expirada'
            ]);
        }

        // Validar datos del trámite
        $validacion = $this->validarDatosTramite();
        if (!$validacion['success']) {
            return $this->response->setJSON($validacion);
        }

        try {
            // Preparar datos del trámite
            $datosTramite = [
                'folio' => $this->request->getPost('folio'),
                'contrato' => $this->request->getPost('contrato'),
                'unidad' => $this->request->getPost('unidad'),
                'serie' => $this->request->getPost('serie'),
                'placas' => $this->request->getPost('placas'),
                'tra_tipos_id' => $this->request->getPost('tra_tipos_id'),
                'ent_municipio_id' => $this->request->getPost('ent_municipio_id'),
                'cli_directo_id' => $this->request->getPost('cli_directo_id'),
                'cli_directo_ejecutivo_id' => $this->request->getPost('cli_directo_ejecutivo_id') ?: null,
                'empresa_gestora_id' => $this->request->getPost('empresa_gestora_id') ?: null,
                'gestor_id' => $this->request->getPost('gestor_id') ?: null,
                'tra_status_id' => 22, // En proceso por defecto
                'cobro_status_id' => 1, // Pendiente de cobro
                'observaciones' => $this->request->getPost('observaciones'),
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'started_at' => date('Y-m-d H:i:s')
            ];

            // Validar multi-tenancy
            if (!$this->validarAccesoCliente($userId, $datosTramite['cli_directo_id'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No tiene permisos para crear trámites para este cliente'
                ]);
            }

            // Iniciar transacción
            $this->db->transStart();

            // Insertar trámite
            $builder = $this->db->table('tramite');
            $builder->insert($datosTramite);
            $tramiteId = $this->db->insertID();

            if (!$tramiteId) {
                throw new \Exception('Error al crear el trámite');
            }

            // Guardar archivos si existen
            $archivos = $this->request->getFiles();
            if (!empty($archivos['documentos'])) {
                $this->guardarArchivos($tramiteId, $archivos['documentos']);
            }

            // Registrar en bitácora
            $this->registrarBitacora($tramiteId, 'Trámite creado mediante wizard', $userId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Trámite creado exitosamente',
                'tramite_id' => $tramiteId,
                'folio' => $datosTramite['folio']
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar borrador del trámite
     */
    public function guardar_borrador()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión expirada'
            ]);
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
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión expirada'
            ]);
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
        $builder->join('tra_status ts', 'ts.id = t.tra_status_id', 'left');
        $builder->join('users u', 'u.id = t.user_id', 'left');

        $builder->where('t.created_at >=', $fechaInicio);
        $builder->where('t.created_at <=', $fechaFin . ' 23:59:59');

        if ($statusId) {
            $builder->where('t.tra_status_id', $statusId);
        }

        // Filtro multi-tenancy
        if (!user_is_admin($userId)) {
            $clienteIds = get_user_cliente_ids($userId);
            if (empty($clienteIds)) {
                $builder->where('1 = 0');
            } else {
                $builder->whereIn('t.cli_directo_id', $clienteIds);
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
        $entidadId = $this->request->getPost('entidad_id');
        
        $builder = $this->db->table('rel_ent_municipio');
        $builder->select('ent_municipality_id as id, ent_municipality as municipio');
        
        if ($entidadId) {
            $builder->where('id_entity', $entidadId);
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
        $clienteId = $this->request->getPost('cliente_id');
        
        $builder = $this->db->table('cli_directo_ejecutivo');
        $builder->select('id, nombre');
        $builder->where('cli_directo_id', $clienteId);
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
        $empresaId = $this->request->getPost('empresa_id');
        
        $builder = $this->db->table('ges_gestor');
        $builder->select('id, nombre');
        $builder->where('empresa_gestora_id', $empresaId);
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

    private function getClientesFiltrados($userId)
    {
        $builder = $this->db->table('cli_directo');
        $builder->select('id, razon_social');
        
        // Aplicar filtro multi-tenancy
        if (!user_is_admin($userId)) {
            $clienteIds = get_user_cliente_ids($userId);
            if (empty($clienteIds)) {
                $builder->where('1 = 0');
            } else {
                $builder->whereIn('id', $clienteIds);
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
        if (user_is_admin($userId)) {
            return true;
        }

        $clienteIds = get_user_cliente_ids($userId);
        return in_array($clienteId, $clienteIds);
    }

    private function guardarArchivos($tramiteId, $archivos)
    {
        $uploadPath = WRITEPATH . 'uploads/tramites/' . $tramiteId . '/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        foreach ($archivos as $archivo) {
            if ($archivo->isValid() && !$archivo->hasMoved()) {
                $nuevoNombre = $archivo->getRandomName();
                $archivo->move($uploadPath, $nuevoNombre);

                // Registrar en tabla de documentos
                $this->db->table('tra_doc_status')->insert([
                    'tramite_id' => $tramiteId,
                    'nombre_archivo' => $nuevoNombre,
                    'nombre_original' => $archivo->getClientName(),
                    'ruta' => $uploadPath . $nuevoNombre,
                    'tipo' => $archivo->getClientMimeType(),
                    'tamano' => $archivo->getSize(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
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

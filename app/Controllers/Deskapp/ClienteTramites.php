<?php

namespace App\Controllers\Deskapp;

use App\Controllers\BaseController;
use Config\Database;
use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

class ClienteTramites extends BaseController
{
    protected $session;

    private function normalizeDisplayLabel(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $lower = function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);

        if (function_exists('mb_convert_case')) {
            return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
        }

        return ucwords($lower);
    }

    private function normalizeFieldInRows(array $rows, string $field): array
    {
        foreach ($rows as &$row) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $row[$field] = $this->normalizeDisplayLabel($row[$field]);
            }
        }
        unset($row);

        return $rows;
    }

    public function __construct()
    {
        helper(['form', 'url', 'cliente_filter', 'cliente_context', 'permissions', 'audit', 'acl_guard']);
        $this->session = session();

        $userId = $this->session->get('id');
        $requested = $this->request ? $this->request->getGet('cliente_id') : null;
        resolve_active_cliente_id($userId, $requested);
    }

    private function requireClienteAccess()
    {
        [$roles, $perms] = session_roles_perms($this->session);

        if (has_permission('menu_tramites_cliente', $perms, $roles)) {
            return null;
        }

        $accept = (string) $this->request->getHeaderLine('Accept');
        $wantsJson = $this->request->isAJAX() || (strpos($accept, 'application/json') !== false);
        if ($wantsJson) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 403,
                    'error' => 'forbidden',
                    'message' => 'No tiene permisos para acceder a este modulo.',
                ]);
        }

        $data = [
            'session' => \Config\Services::session(),
            'username' => $this->session->get('user_name'),
        ];

        return $this->response
            ->setStatusCode(403)
            ->setBody(view('deskapp/error-pages/403', $data));
    }

    private function getCliDirectoList(int $userId): array
    {
        $db = Database::connect();
        $builder = $db->table('cli_directo cd');
        $builder->select('cd.id, cd.razon_social, c.razon_social as cliente');
        $builder->join('cliente c', 'c.id = cd.cliente_id', 'inner');

        $clienteIds = get_user_cliente_ids($userId);
        if (is_array($clienteIds)) {
            if (empty($clienteIds)) {
                return [];
            }
            $builder->whereIn('c.id', array_map('intval', $clienteIds));
        }

        $builder->orderBy('cd.razon_social', 'ASC');
        $rows = $builder->get()->getResultArray();
        return $this->normalizeFieldInRows($rows, 'razon_social');
    }

    private function getTiposList(): array
    {
        $db = Database::connect();
        $rows = $db->table('tra_tipos')
            ->select('id, tipo_tramite')
            ->orderBy('tipo_tramite', 'ASC')
            ->get()
            ->getResultArray();

        return $this->normalizeFieldInRows($rows, 'tipo_tramite');
    }

    private function getStatusList(): array
    {
        $db = Database::connect();
        $rows = $db->table('tra_status')
            ->select('id, tra_status')
            ->orderBy('tra_status', 'ASC')
            ->get()
            ->getResultArray();

        return $this->normalizeFieldInRows($rows, 'tra_status');
    }

    private function getFiltersFromRequest(): array
    {
        $filters = [
            'cli_directo_id' => $this->request->getGet('cli_directo_id'),
            'tra_tipos_id' => $this->request->getGet('tra_tipos_id'),
            'tra_status_id' => $this->request->getGet('tra_status_id'),
            'fecha_inicio' => $this->request->getGet('fecha_inicio'),
            'fecha_fin' => $this->request->getGet('fecha_fin'),
            'pendiente_pago' => $this->request->getGet('pendiente_pago'),
            'q' => $this->request->getGet('q'),
        ];

        foreach (['cli_directo_id', 'tra_tipos_id', 'tra_status_id'] as $key) {
            if ($filters[$key] === '' || $filters[$key] === null) {
                $filters[$key] = null;
                continue;
            }
            $filters[$key] = is_numeric($filters[$key]) ? (int) $filters[$key] : null;
        }

        if (!empty($filters['fecha_inicio']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['fecha_inicio'])) {
            $filters['fecha_inicio'] = null;
        }
        if (!empty($filters['fecha_fin']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['fecha_fin'])) {
            $filters['fecha_fin'] = null;
        }

        $pendiente = $filters['pendiente_pago'];
        if ($pendiente === '1' || $pendiente === 1 || $pendiente === '0' || $pendiente === 0) {
            $filters['pendiente_pago'] = (string) $pendiente;
        } else {
            $filters['pendiente_pago'] = null;
        }

        $filters['q'] = is_string($filters['q']) ? trim($filters['q']) : null;

        return $filters;
    }

    private function applyListFilters($builder, array $filters, int $userId)
    {
        $builder->where(get_tramite_filter_sql($userId, 't'), null, false);

        if (!empty($filters['cli_directo_id'])) {
            $builder->where('t.cli_directo_id', (int) $filters['cli_directo_id']);
        }
        if (!empty($filters['tra_tipos_id'])) {
            $builder->where('t.tra_tipos_id', (int) $filters['tra_tipos_id']);
        }
        if (!empty($filters['tra_status_id'])) {
            $builder->where('t.tra_status_id', (int) $filters['tra_status_id']);
        }
        if (!empty($filters['fecha_inicio'])) {
            $builder->where('t.created_at >=', $filters['fecha_inicio'] . ' 00:00:00');
        }
        if (!empty($filters['fecha_fin'])) {
            $builder->where('t.created_at <=', $filters['fecha_fin'] . ' 23:59:59');
        }
        if (isset($filters['pendiente_pago'])) {
            $pendienteSql = "((t.numero_factura IS NOT NULL AND t.numero_factura != '') OR (t.numero_refactura IS NOT NULL AND t.numero_refactura != '')) AND t.cobro_status_id = 22";
            if ($filters['pendiente_pago'] === '1') {
                $builder->where($pendienteSql, null, false);
            } elseif ($filters['pendiente_pago'] === '0') {
                $builder->where("NOT ($pendienteSql)", null, false);
            }
        }

        if (!empty($filters['q'])) {
            $builder->groupStart()
                ->like('t.folio', $filters['q'])
                ->orLike('t.contrato', $filters['q'])
                ->orLike('t.unidad', $filters['q'])
                ->orLike('t.serie', $filters['q'])
                ->orLike('t.placas', $filters['q'])
                ->groupEnd();
        }

        return $builder;
    }

    public function index()
    {
        if ($resp = $this->requireClienteAccess()) {
            return $resp;
        }

        try {
            $data = [
                'session' => \Config\Services::session(),
                'username' => $this->session->get('user_name'),
            ];

            $userId = (int) $this->session->get('id');
            [$roles, $perms] = session_roles_perms($this->session);

            $tramiteCrud = $this->_getGroceryCrudEnterprise();

            $filterSql = get_tramite_filter_sql($userId);
            $tramiteCrud->where($filterSql);

            $tramiteCrud->unsetAdd();
            $tramiteCrud->unsetEdit();
            $tramiteCrud->unsetRead();
            $tramiteCrud->unsetDelete();
            $tramiteCrud->unsetDeleteMultiple();
            $tramiteCrud->unsetClone();
            if (!has_permission('export_cliente_tramite', $perms, $roles)) {
                $tramiteCrud->unsetExport();
            }
            if (!has_permission('print_cliente_tramite', $perms, $roles)) {
                $tramiteCrud->unsetPrint();
            }

            $tramiteCrud->setActionButton('Ver', 'fas fa-eye', function ($row) {
                return '/deskapp/clientes/ver/' . $row->id;
            }, false);

            $tramiteCrud->setCsrfTokenName(csrf_token());
            $tramiteCrud->setCsrfTokenValue(csrf_hash());

            $tramiteCrud->setTable('tramite');
            $tramiteCrud->setSubject('tramite', 'Tramites');
            $tramiteCrud->defaultOrdering('tramite.id', 'desc');

            $tramiteCrud->columns([
                'id', 'created_at', 'started_at', 'tra_status_id', 'folio', 'contrato', 'unidad', 'serie',
                'placas', 'tra_tipos_id', 'entidad_id', 'ent_municipio_id', 'cli_directo_id',
                'cli_directo_ejecutivo_id', 'empresa_gestora_id', 'gestor_id',
                'cobro_status_id', 'user_id',
                'observaciones'
            ]);

            $tramiteCrud->displayAs('created_at', 'Creacion');
            $tramiteCrud->displayAs('started_at', 'Desde Asignacion');

            $formatDateEs = static function ($value): string {
                if (empty($value)) {
                    return 'Pendiente';
                }
                $ts = strtotime((string) $value);
                if (!$ts) {
                    return (string) $value;
                }
                $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                $mIndex = (int) date('n', $ts);
                $mon = $months[max(1, min(12, $mIndex)) - 1];
                return date('j', $ts) . ' ' . $mon . ' ' . date('Y', $ts) . ', ' . date('H:i', $ts);
            };

            $tramiteCrud->callbackColumn('created_at', function ($value) use ($formatDateEs) {
                return $formatDateEs($value);
            });
            $tramiteCrud->setRelation('user_id', 'users', '{firstname} {midname} {lastname}');
            $tramiteCrud->displayAs('user_id', 'Ejecutivo');

            $tramiteCrud->callbackColumn('started_at', function ($value, $row) {
                $fechaAsignacion = new \DateTime($row->started_at);
                $fechaActual = new \DateTime();
                $diasDiferencia = $fechaAsignacion->diff($fechaActual)->days;

                $claseVerde = 'background-verde';
                $claseAmarillo = 'background-amarillo';
                $claseRojo = 'background-rojo';
                $claseVioleta = 'background-violeta';
                $claseGris = 'background-gris';
                $claseAzulClaro = 'background-azul-claro';
                $claseAzul = 'background-azul';
                $claseAzulCobroCliente = 'background-azul-cobro-cliente';

                if ($row->tra_status_id == 23 || $row->tra_status_id == 28) {
                    if ($row->tra_status_id == 23) {
                        $clase = $claseAzulClaro;
                    }
                    $txtGenerarFactura = '';

                    $db = Database::connect();
                    $registrosCobroCliente = $db->table('tra_cobro_cliente')
                        ->select('id')
                        ->where('tramite_id', (int) $row->id)
                        ->limit(1)
                        ->get()
                        ->getResultArray();

                    $registrosEvidenciasFinales = $db->table('tra_evidencias_finales')
                        ->select('id')
                        ->where('tramite_id', (int) $row->id)
                        ->limit(1)
                        ->get()
                        ->getResultArray();

                    if (!empty($registrosCobroCliente) || !empty($registrosEvidenciasFinales)) {
                        $txtGenerarFactura = 'Facturar';
                    }

                    if ($row->tra_status_id == 28) {
                        $clase = $claseAzulCobroCliente;
                        return '<span class="' . $clase . '">' . $txtGenerarFactura . '</span>';
                    }
                } elseif ($row->tra_status_id == 21) {
                    $clase = $claseGris;
                } elseif ($row->tra_status_id == 20) {
                    $clase = $claseAzul;
                } else {
                    $local = ($row->ent_municipio_id >= 266 && $row->ent_municipio_id <= 281) ||
                             ($row->ent_municipio_id >= 657 && $row->ent_municipio_id <= 781);

                    if ($local) {
                        if ($diasDiferencia < 5) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 8) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 12) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    } else {
                        if ($diasDiferencia < 10) {
                            $clase = $claseVerde;
                        } elseif ($diasDiferencia < 13) {
                            $clase = $claseAmarillo;
                        } elseif ($diasDiferencia < 16) {
                            $clase = $claseRojo;
                        } else {
                            $clase = $claseVioleta;
                        }
                    }
                }

                $arrFilter = [20, 21, 23, 28];
                if (!in_array($row->tra_status_id, $arrFilter)) {
                    return '<span class="' . $clase . '">' . $diasDiferencia . ' dias</span>';
                }

                return '<span class="' . $clase . '"></span>';
            });

            $tramiteCrud->fields([
                'folio','contrato','unidad','serie',
                'placas','tra_tipos_id','ent_municipio_id','cli_directo_id',
                'cli_directo_ejecutivo_id','empresa_gestora_id','gestor_id',
                'tra_status_id','cobro_status_id',
                'observaciones', 'user_id'
            ]);

            $tramiteCrud->setRelation('tra_tipos_id', 'tra_tipos', 'tipo_tramite');
            $tramiteCrud->displayAs('tra_tipos_id','Tipo de Tramite');

            $tramiteCrud->setRelation('tra_status_id', 'tra_status', 'tra_status');
            $tramiteCrud->displayAs('tra_status_id','Estatus del Tramite');

            $clienteRelationFilter = get_cliente_relation_filter($userId);
            if ($clienteRelationFilter !== null) {
                $tramiteCrud->setRelation('cli_directo_id', 'cli_directo', 'razon_social', $clienteRelationFilter);
            } else {
                $tramiteCrud->setRelation('cli_directo_id', 'cli_directo', 'razon_social');
            }
            $tramiteCrud->displayAs('cli_directo_id','Cliente Directo');

            $tramiteCrud->setRelation('cli_directo_ejecutivo_id', 'cli_directo_ejecutivo', 'nombre');
            $tramiteCrud->displayAs('cli_directo_ejecutivo_id','Ejecutivo del Cliente');
            $tramiteCrud->setDependentRelation('cli_directo_ejecutivo_id','cli_directo_id','cli_directo_id');

            $tramiteCrud->setRelation('entidad_id', 'entidad', 'entidad');
            $tramiteCrud->displayAs('entidad_id','Entidad');

            $tramiteCrud->setRelation('ent_municipio_id', 'rel_ent_municipio', 'ent_municipality');
            $tramiteCrud->displayAs('ent_municipio_id','Municipio');

            $tramiteCrud->setRelation('empresa_gestora_id', 'ges_empresa_gestora', 'razon_social');
            $tramiteCrud->displayAs('empresa_gestora_id','Empresa Gestora');

            $tramiteCrud->setRelation('gestor_id', 'ges_gestor', 'nombre');
            $tramiteCrud->displayAs('gestor_id','Gestor');
            $tramiteCrud->setDependentRelation('gestor_id','empresa_gestora_id','empresa_gestora_id');

            $tramiteSalida = $tramiteCrud->render();

            $salidaTotal = array_merge((array)$tramiteSalida, $data);
            $salidaTotal['insert_button_url'] = '';

            return $this->_example_output($salidaTotal);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function data()
    {
        if ($resp = $this->requireClienteAccess()) {
            return $resp;
        }

        $userId = (int) $this->session->get('id');
        $filters = $this->getFiltersFromRequest();

        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $db = Database::connect();
        $builder = $db->table('tramite t');
        $builder->select('t.id, t.folio, t.created_at, t.started_at, t.finished_at, t.tra_status_id, t.cobro_status_id, t.numero_factura, t.numero_refactura');
        $builder->select('tt.tipo_tramite, ts.tra_status, cd.razon_social as cliente_directo');
        $builder->join('tra_tipos tt', 't.tra_tipos_id = tt.id', 'left');
        $builder->join('tra_status ts', 't.tra_status_id = ts.id', 'left');
        $builder->join('cli_directo cd', 't.cli_directo_id = cd.id', 'left');

        $this->applyListFilters($builder, $filters, $userId);
        $builder->orderBy('t.id', 'DESC');
        $builder->limit($perPage, $offset);

        $rows = $builder->get()->getResultArray();
        $rows = $this->normalizeFieldInRows($rows, 'tipo_tramite');
        $rows = $this->normalizeFieldInRows($rows, 'tra_status');
        $rows = $this->normalizeFieldInRows($rows, 'cliente_directo');

        $countBuilder = $db->table('tramite t');
        $countBuilder->select('COUNT(*) as total');
        $this->applyListFilters($countBuilder, $filters, $userId);
        $totalRow = $countBuilder->get()->getRowArray();
        $total = (int) ($totalRow['total'] ?? 0);

        return $this->response->setJSON([
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'rows' => $rows,
        ]);
    }

    public function show($tramiteId = null)
    {
        if ($resp = $this->requireClienteAccess()) {
            return $resp;
        }

        if (!$tramiteId || !is_numeric($tramiteId)) {
            return redirect()->to(site_url('/deskapp/clientes/tramites'))
                ->with('error', 'Tramite no valido');
        }

        $tramiteId = (int) $tramiteId;
        $userId = (int) ($this->session->get('id') ?? 0);
        [$roles, $perms] = session_roles_perms($this->session);

        if (!acl_has_tramite_tenant_access($tramiteId, $userId, $roles)) {
            return redirect()->to(site_url('/deskapp/clientes/tramites'))
                ->with('error', 'No tienes permisos para ver este tramite');
        }

        $db = Database::connect();
        $builder = $db->table('tramite t');
        $builder->select('t.*, tt.tipo_tramite, ts.tra_status, cd.razon_social as cliente_directo, c.razon_social as cliente, u.firstname, u.lastname');
        $builder->select('cs.cobro_status, eg.razon_social as empresa_gestora, gg.nombre as gestor, e.entidad, em.ent_municipality as municipio, ce.nombre as ejecutivo_cliente');
        $builder->join('tra_tipos tt', 't.tra_tipos_id = tt.id', 'left');
        $builder->join('tra_status ts', 't.tra_status_id = ts.id', 'left');
        $builder->join('cli_directo cd', 't.cli_directo_id = cd.id', 'left');
        $builder->join('cliente c', 'cd.cliente_id = c.id', 'left');
        $builder->join('cli_directo_ejecutivo ce', 't.cli_directo_ejecutivo_id = ce.id', 'left');
        $builder->join('users u', 't.user_id = u.id', 'left');
        $builder->join('ges_empresa_gestora eg', 't.empresa_gestora_id = eg.id', 'left');
        $builder->join('ges_gestor gg', 't.gestor_id = gg.id', 'left');
        $builder->join('entidad e', 't.entidad_id = e.id', 'left');
        $builder->join('rel_ent_municipio em', 't.ent_municipio_id = em.ent_municipality_id', 'left');
        $builder->join('cobro_statuses cs', 't.cobro_status_id = cs.id', 'left');
        $builder->where('t.id', $tramiteId);

        $tramite = $builder->get()->getRowArray();
        if (!$tramite) {
            return redirect()->to(site_url('/deskapp/clientes/tramites'))
                ->with('error', 'Tramite no encontrado');
        }

        $auditLog = get_tramite_audit_log($tramiteId);

        // Documentos del trámite: requeridos por este trámite (tra_doc_status)
        // Se listan SOLO los documento_id presentes en tra_doc_status para este tramite_id,
        // y se cruza con `documento` únicamente para obtener la etiqueta.
        $docStatusDocs = [];
        $docStatusDocsTotal = 0;
        $docStatusDocsUploaded = 0;
        try {
            $hasTraDocStatus = $db->tableExists('tra_doc_status')
                && $db->fieldExists('file', 'tra_doc_status')
                && $db->fieldExists('tramite_id', 'tra_doc_status');

            $hasDocumentoCatalog = $db->tableExists('documento')
                && ($db->fieldExists('documento_id', 'documento') || $db->fieldExists('id', 'documento'))
                && $db->fieldExists('documento', 'documento');

            if ($hasTraDocStatus) {

                $hasDocStatuses = $db->tableExists('doc_statuses');

                $hasDocumentoId = $db->fieldExists('documento_id', 'tra_doc_status');
                $hasStatusDocumentoId = $db->fieldExists('status_documento_id', 'tra_doc_status');
                $hasComentario = $db->fieldExists('comentario', 'tra_doc_status');
                $hasCreatedAt = $db->fieldExists('created_at', 'tra_doc_status');
                $hasStatus = $db->fieldExists('status', 'tra_doc_status');

                $docCatalogPk = null;
                if ($hasDocumentoCatalog) {
                    $docCatalogPk = $db->fieldExists('documento_id', 'documento') ? 'documento_id' : 'id';
                }

                $builderDocs = $db->table('tra_doc_status tds');
                $selectParts = ['tds.id', 'tds.file'];
                if ($hasDocumentoId) {
                    $selectParts[] = 'tds.documento_id';
                }
                if ($hasStatusDocumentoId) {
                    $selectParts[] = 'tds.status_documento_id';
                }
                if ($hasComentario) {
                    $selectParts[] = 'tds.comentario';
                }
                if ($hasCreatedAt) {
                    $selectParts[] = 'tds.created_at';
                }
                $builderDocs->select(implode(', ', $selectParts));
                if ($hasDocumentoCatalog && $hasDocumentoId && $docCatalogPk) {
                    $builderDocs->select('d.documento as documento_nombre');
                    $builderDocs->join('documento d', 'd.' . $docCatalogPk . ' = tds.documento_id', 'left');
                }
                if ($hasDocStatuses && $hasStatusDocumentoId) {
                    $builderDocs->select('ds.st_documento as status_nombre');
                    $builderDocs->join('doc_statuses ds', 'ds.id = tds.status_documento_id', 'left');
                }

                $builderDocs->where('tds.tramite_id', $tramiteId);
                if ($hasStatus) {
                    $builderDocs->where('tds.status', 1);
                }

                $rows = $builderDocs
                    ->orderBy('tds.id', 'DESC')
                    ->get()
                    ->getResultArray();

                $extractFileNames = static function ($fileField): array {
                    if ($fileField === null) {
                        return [];
                    }
                    $raw = trim((string) $fileField);
                    if ($raw === '') {
                        return [];
                    }

                    // Intento JSON (GroceryCrud multiple upload suele guardar JSON)
                    if ($raw[0] === '[' || $raw[0] === '{') {
                        $decoded = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $out = [];
                            $push = static function ($value) use (&$out) {
                                if (is_string($value) && trim($value) !== '') {
                                    $out[] = trim($value);
                                }
                            };
                            if (is_array($decoded)) {
                                foreach ($decoded as $item) {
                                    if (is_string($item)) {
                                        $push($item);
                                        continue;
                                    }
                                    if (is_array($item)) {
                                        foreach (['file', 'fileName', 'filename', 'name', 'path'] as $key) {
                                            if (!empty($item[$key]) && is_string($item[$key])) {
                                                $push($item[$key]);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            if (!empty($out)) {
                                return $out;
                            }
                        }
                    }

                    // Fallback: lista separada por comas
                    if (strpos($raw, ',') !== false) {
                        $parts = array_map('trim', explode(',', $raw));
                        return array_values(array_filter($parts, static fn($p) => $p !== ''));
                    }

                    return [$raw];
                };

                $resolveDocUrl = static function (string $fileName): ?string {
                    $fileName = trim($fileName);
                    if ($fileName === '' || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                        return null;
                    }

                    // Si viene una ruta, nos quedamos solo con el nombre.
                    $fileBase = basename($fileName);
                    if ($fileBase === '' || $fileBase === '.' || $fileBase === '..' || strpos($fileBase, "\0") !== false || strpos($fileBase, '..') !== false) {
                        return null;
                    }

                    $driver = config('FileStorage')->driver;

                    // Bajo s3 no hay disco local: resolvemos directamente contra
                    // la categoría canónica 'documentostatus' vía file_url()
                    // (URL prefirmada), sin aplicar la compuerta de existencia
                    // local. Un valor no resoluble degrada a null.
                    if ($driver !== 'local') {
                        $url = file_url($fileBase, 'documentostatus');

                        return $url !== '' ? $url : null;
                    }

                    // Bajo local conservamos el sondeo de directorios candidatos
                    // con is_file() para mantener la salida byte-idéntica al
                    // comportamiento actual; file_url() devuelve la misma ruta
                    // base_url('/assets/uploads/'.<categoria>/<archivo>).
                    $candidates = [
                        ['dir' => 'assets/uploads/documentostatus/', 'category' => 'documentostatus'],
                        ['dir' => 'assets/uploads/docstatus/', 'category' => 'docstatus'],
                    ];

                    foreach ($candidates as $cand) {
                        $filePath = FCPATH . $cand['dir'] . $fileBase;
                        if (is_file($filePath)) {
                            return file_url($fileBase, $cand['category']);
                        }
                    }

                    return null;
                };

                // Mapa: documento_id => evidencia más reciente (con o sin archivo)
                $latestByDocId = [];
                foreach (($rows ?? []) as $row) {
                    $docId = $row['documento_id'] ?? null;
                    if ($docId === null || $docId === '') {
                        continue;
                    }
                    $docId = (int) $docId;
                    if ($docId <= 0) {
                        continue;
                    }
                    if (isset($latestByDocId[$docId])) {
                        continue;
                    }

                    $label = trim((string) ($row['documento_nombre'] ?? ''));
                    if ($label === '') {
                        $label = 'Documento #' . (string) $docId;
                    }

                    $pickedFile = '';
                    $pickedUrl = '';

                    $fileField = $row['file'] ?? '';
                    $fileNames = $extractFileNames($fileField);
                    foreach ($fileNames as $fileName) {
                        $url = $resolveDocUrl((string) $fileName);
                        if ($url !== null) {
                            $pickedFile = basename((string) $fileName);
                            $pickedUrl = $url;
                            break;
                        }
                    }

                    $latestByDocId[$docId] = [
                        'id' => $row['id'] ?? null,
                        'documento_id' => $docId,
                        'documento_nombre' => $label,
                        'status_documento_id' => $row['status_documento_id'] ?? null,
                        'status_nombre' => $row['status_nombre'] ?? null,
                        'file' => $pickedFile,
                        'url' => $pickedUrl,
                        'comentario' => $row['comentario'] ?? null,
                        'created_at' => $row['created_at'] ?? null,
                    ];
                }

                $docStatusDocs = array_values($latestByDocId);
                usort($docStatusDocs, static function (array $a, array $b): int {
                    $an = (string) ($a['documento_nombre'] ?? '');
                    $bn = (string) ($b['documento_nombre'] ?? '');
                    $cmp = strcasecmp($an, $bn);
                    if ($cmp !== 0) {
                        return $cmp;
                    }
                    return ((int) ($a['documento_id'] ?? 0)) <=> ((int) ($b['documento_id'] ?? 0));
                });

                $docStatusDocsTotal = count($docStatusDocs);
                $docStatusDocsUploaded = 0;
                foreach ($docStatusDocs as $docItem) {
                    if (!empty($docItem['url'])) {
                        $docStatusDocsUploaded++;
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'ClienteTramites::show tra_doc_status error (tramite_id=' . $tramiteId . '): ' . $e->getMessage());
            // Silencioso para cliente: si falla, solo no se muestran documentos.
        }

        // Documento entregado por gestor (Paso 4)
        $docGestorEntregado = null;
        $docGestorEntregadoUrl = null;
        $pagoGestorDocs = [];
        try {
            if ($db->tableExists('tra_pago_gestor')
                && $db->fieldExists('file', 'tra_pago_gestor')
                && $db->fieldExists('comprobante_final', 'tra_pago_gestor')) {
                // Lista de documentos (para modal de documentos)
                $docs = $db->table('tra_pago_gestor')
                    ->select('file, comprobante_final, created_at')
                    ->where('tramite_id', $tramiteId)
                    ->orderBy('created_at', 'DESC')
                    ->get()
                    ->getResultArray();

                foreach (($docs ?? []) as $doc) {
                    $fileName = trim((string) ($doc['file'] ?? ''));
                    if ($fileName === '' || $fileName !== basename($fileName) || strpos($fileName, "\0") !== false || strpos($fileName, '..') !== false) {
                        continue;
                    }
                    $filePath = FCPATH . 'assets/uploads/pago_gestor/' . $tramiteId . '/' . $fileName;
                    if (!is_file($filePath)) {
                        continue;
                    }
                    $doc['url'] = file_url($fileName, 'pago_gestor', (int) $tramiteId);
                    $pagoGestorDocs[] = $doc;
                }

                $rowDoc = $db->table('tra_pago_gestor')
                    ->select('file, comprobante_final, created_at')
                    ->where('tramite_id', $tramiteId)
                    ->where('comprobante_final', 'tramite_recibido')
                    ->orderBy('created_at', 'DESC')
                    ->get(1)
                    ->getRowArray();

                $fileName = trim((string) ($rowDoc['file'] ?? ''));
                if ($fileName !== '' && $fileName === basename($fileName) && strpos($fileName, "\0") === false && strpos($fileName, '..') === false) {
                    $filePath = FCPATH . 'assets/uploads/pago_gestor/' . $tramiteId . '/' . $fileName;
                    if (is_file($filePath)) {
                        $docGestorEntregado = $rowDoc;
                        $docGestorEntregadoUrl = file_url($fileName, 'pago_gestor', (int) $tramiteId);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silencioso para cliente: si falla, solo no se muestra el documento.
        }

        $lastMovement = null;
        if (!empty($auditLog)) {
            $lastMovement = $auditLog[0]['created_at'] ?? null;
        }

        $facturaTimestamp = null;
        $pagoTimestamp = null;
        $statusTimeline = [];

        foreach ($auditLog as $log) {
            $field = $log['field_name'] ?? '';
            if (!$facturaTimestamp && in_array($field, ['numero_factura', 'numero_refactura'], true)) {
                $facturaTimestamp = $log['created_at'] ?? null;
            }
            if (!$pagoTimestamp && $field === 'cobro_status_id') {
                $pagoTimestamp = $log['created_at'] ?? null;
            }
            if ($field === 'tra_status_id') {
                $statusTimeline[] = [
                    'timestamp' => $log['created_at'] ?? null,
                    'descripcion' => $log['description'] ?? 'Cambio de estatus',
                    'nuevo' => $log['new_value'] ?? null,
                ];
            }
        }

        $milestones = [
            [
                'titulo' => 'Creacion del tramite',
                'fecha' => $tramite['created_at'] ?? null,
                'detalle' => 'Registro inicial en el sistema',
            ],
            [
                'titulo' => 'Inicio de proceso',
                'fecha' => $tramite['started_at'] ?? null,
                'detalle' => 'Comienzo del seguimiento operativo',
            ],
            [
                'titulo' => 'Ultimo movimiento',
                'fecha' => $lastMovement,
                'detalle' => 'Ultima actualizacion registrada',
            ],
            [
                'titulo' => 'Concluido',
                'fecha' => $tramite['finished_at'] ?? null,
                'detalle' => 'Cierre operativo del tramite',
            ],
            [
                'titulo' => 'Factura registrada',
                'fecha' => $facturaTimestamp,
                'detalle' => 'Factura entregada al cliente',
            ],
            [
                'titulo' => 'Pago final',
                'fecha' => $pagoTimestamp,
                'detalle' => 'Confirmacion de pago del tramite',
            ],
        ];

        // Evidencias del proceso (tra_evidencias) — registro operativo visible al cliente
        $traEvidencias = [];
        try {
            if ($db->tableExists('tra_evidencias')) {
                $builder = $db->table('tra_evidencias')
                    ->select('id, folio_tramite, comentario, costo, file, created_at')
                    ->where('tramite_id', $tramiteId)
                    ->where('status', 1);

                if ($db->fieldExists('tipo_evidencia', 'tra_evidencias')) {
                    $builder->where('tipo_evidencia', 1);
                }

                $evRows = $builder
                    ->orderBy('created_at', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($evRows as $evRow) {
                    $evFile = trim((string)($evRow['file'] ?? ''));
                    $evUrl  = null;
                    if ($evFile !== '' && basename($evFile) === $evFile
                        && strpos($evFile, "\0") === false && strpos($evFile, '..') === false) {
                        $evPath = FCPATH . 'assets/uploads/evidencias/' . $tramiteId . '/' . $evFile;
                        if (is_file($evPath)) {
                            $evUrl = file_url($evFile, 'evidencias', (int) $tramiteId);
                        }
                    }
                    $evRow['url'] = $evUrl;
                    $traEvidencias[] = $evRow;
                }
            }
        } catch (\Throwable $e) {
            // Silencioso para el cliente
        }

        // Pagos de derecho (tra_pago_derechos)
        $traPagoDerechos = [];
        try {
            if ($db->tableExists('tra_pago_derechos')) {
                $pdRows = $db->table('tra_pago_derechos')
                    ->select('id, file, comentario, costo, created_at')
                    ->where('tramite_id', $tramiteId)
                    ->where('status', 1)
                    ->orderBy('created_at', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($pdRows as $pdRow) {
                    $pdFile = trim((string)($pdRow['file'] ?? ''));
                    $pdUrl  = null;
                    if ($pdFile !== '' && basename($pdFile) === $pdFile
                        && strpos($pdFile, "\0") === false && strpos($pdFile, '..') === false) {
                        // Siempre generamos la URL; el archivo puede existir en producción aunque no en local
                        $pdUrl = file_url($pdFile, 'pago_derechos', (int) $tramiteId);
                    }
                    $pdRow['url'] = $pdUrl;
                    $traPagoDerechos[] = $pdRow;
                }
            }
        } catch (\Throwable $e) {
            // Silencioso para el cliente
        }

        // Cobros al cliente (tra_cobro_cliente)
        $traCobros = [];
        try {
            if ($db->tableExists('tra_cobro_cliente')) {
                $coRows = $db->table('tra_cobro_cliente')
                    ->select('id, file, comentario, costo, cobro_correcto, created_at')
                    ->where('tramite_id', $tramiteId)
                    ->where('status', 1)
                    ->orderBy('created_at', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($coRows as $coRow) {
                    $coFile = trim((string)($coRow['file'] ?? ''));
                    $coUrl  = null;
                    if ($coFile !== '' && basename($coFile) === $coFile
                        && strpos($coFile, "\0") === false && strpos($coFile, '..') === false) {
                        $coPath = FCPATH . 'assets/uploads/cobro_cliente/' . $tramiteId . '/' . $coFile;
                        // Siempre generamos la URL; el archivo puede existir en producción aunque no en local
                        $coUrl = file_url($coFile, 'cobro_cliente', (int) $tramiteId);
                    }
                    $coRow['url'] = $coUrl;
                    $traCobros[] = $coRow;
                }
            }
        } catch (\Throwable $e) {
            // Silencioso para el cliente
        }

        $data = [
            'session' => \Config\Services::session(),
            'username' => $this->session->get('user_name'),
            'tramite' => $tramite,
            'milestones' => $milestones,
            'status_timeline' => $statusTimeline,
            'audit_log' => $auditLog,
            'doc_gestor_entregado' => $docGestorEntregado,
            'doc_gestor_entregado_url' => $docGestorEntregadoUrl,
            'pago_gestor_docs' => $pagoGestorDocs,
            'doc_status_docs' => $docStatusDocs,
            'doc_status_docs_total' => $docStatusDocsTotal,
            'doc_status_docs_uploaded' => $docStatusDocsUploaded,
            'tra_evidencias' => $traEvidencias,
            'tra_pago_derechos' => $traPagoDerechos,
            'tra_cobros' => $traCobros,
        ];

        return view('deskapp/clientes/tramites_show', $data);
    }

    private function _example_output($salida = null)
    {
        $salida = (object) esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $salida->output;
            exit;
        }
        return view('/deskapp/extra-pages/grocery_page_cliente', (array) $salida);
    }

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

    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true)
    {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
        return $groceryCrud;
    }
}

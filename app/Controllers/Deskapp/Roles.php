<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

use Config\Database as ConfigDatabase;
use Config\GroceryCrud as ConfigGroceryCrud;
use GroceryCrud\Core\GroceryCrud;

use Config\Database;



class Roles extends BaseController
{
    public function __construct() {
        // parent::__construct();
        helper(['form', 'url', 'permissions', 'acl_guard']);
    }

    private function guardManagementAccess()
    {
        $session = session();
        $userId = $session->get('id');
        $isApi = $this->request->isAJAX() || $this->request->getGet('gc_state') !== null;

        if (!$userId) {
            if ($isApi) {
                return acl_deny('Sesión expirada', 401, null, true);
            }
            return redirect()->to('/deskapp/auth/login');
        }

        [$roles, $perms] = session_roles_perms($session);
        $canManage = has_permission('menu_roles', $perms, $roles) || has_permission('menu_permisos', $perms, $roles);

        if (!$canManage) {
            if ($isApi) {
                return acl_deny('Acceso denegado', 403, null, true);
            }
            return redirect()->to('/deskapp/dashboard')->with('error', 'No tienes permisos para administrar roles.');
        }

        return null;
    }

    public function index()
    {
        $output = (object)[
            'js_files' => [],
            'output' => ''
        ];
        
        return $this->_example_output($output);
    }

    public function roles()
    {
        try {
            if ($resp = $this->guardManagementAccess()) {
                return $resp;
            }

            helper('acl_version');

            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $roles_crud = $this->_getGroceryCrudEnterprise();
            $roles_crud->setTable('us_roles');
            $roles_crud->setSubject('Rol', 'Roles');
            $roles_crud->defaultOrdering('us_roles.id', 'desc');
            
            $roles_crud->columns(['id', 'role_name', 'permisos', 'description']);
            $roles_crud->fields(['role_name', 'permisos', 'description']);
            $roles_crud->cloneFields(['role_name', 'permisos', 'description']);
            $roles_crud->requiredCloneFields(['role_name']);
            $roles_crud->unsetDeleteMultiple();

            $roles_crud->setActionButton('Clonar', 'fas fa-clone', function ($row) {
                return '/roles/roles/clone/' . $row->id;
            }, false);

            $roles_crud->setActionButton('Mapa', 'fas fa-map', function ($row) {
                return '/deskapp/roles/roles_mapa/' . $row->id;
            }, false);

            $roles_crud->callbackCloneField('role_name', function ($value, $primaryKeyValue, $rowData) {
                $baseName = trim((string) $value);
                if ($baseName === '') {
                    $baseName = 'Rol';
                }

                return $baseName . ' copia ' . date('YmdHis');
            });

            $roles_crud->callbackBeforeInsert(function ($stateParameters) {
                $stateParameters->data['created_at'] = date('Y-m-d H:i:s');
                $stateParameters->data['updated_at'] = date('Y-m-d H:i:s');
                return $stateParameters;
            });

            // Configuración de la relación N to N para permisos
            $roles_crud->setRelationNtoN(
                'permisos', // Nombre del campo en el formulario
                'us_role_permissions', // Tabla de unión
                'us_permissions', // Tabla de destino
                'role_id', // Llave foránea en la tabla de unión que apunta a la tabla de roles
                'permission_id', // Llave foránea en la tabla de unión que apunta a la tabla de permisos
                'permission_name' // Campo que se desea mostrar en el multiselect
            );

            $roles_crud->callbackAfterInsert(function ($stateParameters) use ($roles_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $roles_crud->callbackAfterUpdate(function ($stateParameters) use ($roles_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $roles_crud->callbackAfterDelete(function ($stateParameters) use ($roles_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $roles_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });

            $roles_output = $roles_crud->render();
            $db = \Config\Database::connect();
            $data['roles_permission_picker'] = [
                'zones' => $this->buildPermissionAssignmentZones($db),
            ];
            $final_output = array_merge((array)$roles_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function roles_mapa($roleId = null)
    {
        if ($resp = $this->guardManagementAccess()) {
            return $resp;
        }

        $roleId = (int)($roleId ?? 0);
        if ($roleId <= 0) {
            return redirect()->to('/deskapp/roles/roles')->with('error', 'Rol inválido.');
        }

        $db = \Config\Database::connect();
        $session = session();

        $roleCatalog = $this->getRolesCatalog($db);
        $baseMap = $this->buildRoleMapData($db, $roleId);
        $role = $baseMap['role'] ?? null;

        if (empty($role)) {
            return redirect()->to('/deskapp/roles/roles')->with('error', 'Rol no encontrado.');
        }
        $compareRoleId = (int) ($this->request->getGet('compare_role_id') ?? 0);
        $compareMap = null;
        if ($compareRoleId > 0 && $compareRoleId !== $roleId) {
            $compareCandidate = $this->buildRoleMapData($db, $compareRoleId);
            if (!empty($compareCandidate['role'])) {
                $compareMap = $compareCandidate;
            }
        }

        $comparison = $this->buildRoleComparison($baseMap, $compareMap);

        $data = [
            'session' => $session,
            'username' => $session->get('user_name'),
            'title' => 'Mapa de permisos (Rol)',
            'description' => 'Permisos del rol ordenados por zonas funcionales del flujo y zonas administrativas.',
            'target_role' => $role,
            'target_role_permissions' => $baseMap['role_permissions'],
            'target_role_permission_set' => $baseMap['role_permission_set'],
            'permission_zones' => $baseMap['permission_zones'],
            'steps' => $baseMap['steps'],
            'admin_permissions' => $baseMap['admin_permissions'],
            'permission_descriptions' => $baseMap['permission_descriptions'],
            'permission_ui_area' => $baseMap['permission_ui_area'],
            'can_move_step' => $baseMap['can_move_step'],
            'role_catalog' => $roleCatalog,
            'compare_role' => $compareMap['role'] ?? null,
            'compare_role_permissions' => $compareMap['role_permissions'] ?? [],
            'compare_role_permission_set' => $compareMap['role_permission_set'] ?? [],
            'compare_can_move_step' => $compareMap['can_move_step'] ?? [],
            'comparison' => $comparison,
        ];

        return view('deskapp/roles/roles_mapa', $data);
    }

    private function getRolesCatalog($db): array
    {
        try {
            $rows = $db->table('us_roles')
                ->select('id, role_name')
                ->orderBy('role_name', 'asc')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        $catalog = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $catalog[] = [
                'id' => $id,
                'role_name' => (string) ($row['role_name'] ?? ('Rol #' . $id)),
            ];
        }

        return $catalog;
    }

    private function buildPermissionAssignmentZones($db): array
    {
        $zoneDefinitions = $this->getPermissionZoneDefinitions();
        $zoneBuckets = [];

        foreach ($zoneDefinitions as $zoneKey => $definition) {
            $zoneBuckets[$zoneKey] = $definition + ['permissions' => []];
        }

        try {
            $permBuilder = $db->table('us_permissions')
                ->select('permission_name, description');

            if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
                $permBuilder->where('status', 1);
            }

            $permRows = $permBuilder
                ->orderBy('permission_name', 'asc')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return array_values($zoneBuckets);
        }

        foreach ($permRows as $row) {
            $permissionName = trim((string) ($row['permission_name'] ?? ''));
            if ($permissionName === '') {
                continue;
            }

            $zoneKey = $this->detectPermissionAssignmentZoneKey($permissionName);
            if (!isset($zoneBuckets[$zoneKey])) {
                $zoneKey = 'admin_otros';
            }

            $zoneBuckets[$zoneKey]['permissions'][] = [
                'name' => $permissionName,
                'label' => function_exists('permission_ui_label') ? permission_ui_label($permissionName) : $permissionName,
                'description' => trim((string) ($row['description'] ?? '')),
            ];
        }

        foreach ($zoneBuckets as &$zone) {
            usort($zone['permissions'], static function (array $left, array $right): int {
                return strcmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
            });
        }
        unset($zone);

        return array_values(array_filter($zoneBuckets, static function (array $zone): bool {
            return !empty($zone['permissions']);
        }));
    }

    private function getPermissionZoneDefinitions(): array
    {
        return [
            'paso_1' => [
                'key' => 'paso_1',
                'title' => 'Paso 1: Datos del tramite',
                'description' => 'Alta, consulta, edicion base y operaciones principales del tramite.',
            ],
            'paso_2' => [
                'key' => 'paso_2',
                'title' => 'Paso 2: Asignacion de gestor',
                'description' => 'Permisos para asignar gestor y visualizar ese bloque del flujo.',
            ],
            'paso_3' => [
                'key' => 'paso_3',
                'title' => 'Paso 3: Pago de derechos',
                'description' => 'Permisos para captura, documentos y autorizacion de pago de derechos.',
            ],
            'paso_4' => [
                'key' => 'paso_4',
                'title' => 'Paso 4: Evidencias y transicion',
                'description' => 'Permisos de evidencias finales y botones de transicion hacia pago a gestor o cobro a cliente.',
            ],
            'paso_5' => [
                'key' => 'paso_5',
                'title' => 'Paso 5: Pago a gestor',
                'description' => 'Permisos del panel, formulario y documentos del pago a gestor.',
            ],
            'paso_6' => [
                'key' => 'paso_6',
                'title' => 'Paso 6: Cobro a cliente y cierre',
                'description' => 'Permisos de costos finales, cobro a cliente y cierre del tramite.',
            ],
            'finales' => [
                'key' => 'finales',
                'title' => 'Finalizados y cancelados',
                'description' => 'Listados, detalle y acciones sobre tramites finalizados o cancelados.',
            ],
            'quick_actions' => [
                'key' => 'quick_actions',
                'title' => 'Acciones rapidas',
                'description' => 'Permisos CRUD de documentos, bitacora, pagos y evidencias.',
            ],
            'global' => [
                'key' => 'global',
                'title' => 'Navegacion y controles globales',
                'description' => 'Header, sidebar, dashboards, menus globales y guards del sistema.',
            ],
            'monitoreo' => [
                'key' => 'monitoreo',
                'title' => 'Monitoreo',
                'description' => 'Auditoria, bitacora y herramientas de seguimiento operativo.',
            ],
            'filtros_propios' => [
                'key' => 'filtros_propios',
                'title' => 'Wizard y filtros propios',
                'description' => 'Permisos del wizard principal y filtros de tramites propios.',
            ],
            'configuracion' => [
                'key' => 'configuracion',
                'title' => 'Configuracion y catalogos',
                'description' => 'Permisos de configuracion y catalogos auxiliares.',
            ],
            'admin_otros' => [
                'key' => 'admin_otros',
                'title' => 'Otros administrativos',
                'description' => 'Permisos administrativos que no caen en una zona operativa especifica.',
            ],
        ];
    }

    private function detectPermissionAssignmentZoneKey(string $permissionName): string
    {
        $normalizedPermission = strtolower(trim($permissionName));
        if ($normalizedPermission === 'important_ir_cobro_cliente' || $normalizedPermission === 'paso_5_navegacion_ir_paso_6_ver') {
            return 'paso_4';
        }

        $canonicalTargets = $this->resolvePermissionCanonicalTargets($permissionName);
        foreach ($canonicalTargets as $candidate) {
            $zoneKey = $this->detectCanonicalPermissionZoneKey($candidate);
            if ($zoneKey !== 'admin_otros') {
                return $zoneKey;
            }
        }

        return $this->detectCanonicalPermissionZoneKey($permissionName);
    }

    private function resolvePermissionCanonicalTargets(string $permissionName): array
    {
        $permissionName = trim($permissionName);
        if ($permissionName === '') {
            return [];
        }

        $config = config('AclPermissionMap');
        $exactAliases = is_object($config) && isset($config->exactAliases) && is_array($config->exactAliases)
            ? $config->exactAliases
            : [];
        $splitAliases = is_object($config) && isset($config->splitAliases) && is_array($config->splitAliases)
            ? $config->splitAliases
            : [];

        $resolved = [];
        $visited = [];
        $stack = [$permissionName];

        while (!empty($stack)) {
            $candidate = array_pop($stack);
            $candidate = trim((string) $candidate);
            if ($candidate === '' || isset($visited[$candidate])) {
                continue;
            }

            $visited[$candidate] = true;
            $next = [];

            if (isset($exactAliases[$candidate]) && is_array($exactAliases[$candidate])) {
                $next = array_merge($next, $exactAliases[$candidate]);
            }

            if (isset($splitAliases[$candidate]) && is_array($splitAliases[$candidate])) {
                $next = array_merge($next, $splitAliases[$candidate]);
            }

            if (empty($next)) {
                $resolved[$candidate] = true;
                continue;
            }

            foreach ($next as $nextCandidate) {
                $stack[] = $nextCandidate;
            }
        }

        return array_keys($resolved);
    }

    private function detectCanonicalPermissionZoneKey(string $canonicalPermission): string
    {
        $canonicalPermission = strtolower(trim($canonicalPermission));
        if ($canonicalPermission === '') {
            return 'admin_otros';
        }

        if (strpos($canonicalPermission, 'paso_1_') === 0 || strpos($canonicalPermission, 'tramites_listado_') === 0 || strpos($canonicalPermission, 'tramite_detalle_') === 0) {
            if (strpos($canonicalPermission, 'tramite_detalle_quick_actions_') === 0) {
                return 'quick_actions';
            }

            if (strpos($canonicalPermission, 'tramite_detalle_legacy_pasar_final_') === 0) {
                return 'finales';
            }

            return 'paso_1';
        }

        if (strpos($canonicalPermission, 'paso_2_') === 0) {
            return 'paso_2';
        }

        if (strpos($canonicalPermission, 'paso_3_') === 0) {
            return 'paso_3';
        }

        if (strpos($canonicalPermission, 'paso_4_') === 0) {
            return 'paso_4';
        }

        if (strpos($canonicalPermission, 'paso_5_') === 0) {
            return 'paso_5';
        }

        if (strpos($canonicalPermission, 'paso_6_') === 0) {
            return 'paso_6';
        }

        if (strpos($canonicalPermission, 'tramites_finalizados_') === 0 || strpos($canonicalPermission, 'tramites_cancelados_') === 0 || strpos($canonicalPermission, 'tramites_finales_') === 0) {
            return 'finales';
        }

        if (strpos($canonicalPermission, 'monitoreo_') === 0) {
            return 'monitoreo';
        }

        if (strpos($canonicalPermission, 'wizard_tramites_') === 0 || strpos($canonicalPermission, 'tramites_mios_') === 0) {
            return 'filtros_propios';
        }

        if (strpos($canonicalPermission, 'configuracion_catalogos_') === 0) {
            return 'configuracion';
        }

        if (strpos($canonicalPermission, 'global_header_') === 0 || strpos($canonicalPermission, 'global_sidebar_') === 0 || strpos($canonicalPermission, 'global_guard_') === 0) {
            return 'global';
        }

        return 'admin_otros';
    }

    private function buildRoleMapData($db, int $roleId): array
    {
        $role = null;
        try {
            $role = $db->table('us_roles')
                ->select('id, role_name, description')
                ->where('id', $roleId)
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            $role = null;
        }

        if (empty($role)) {
            return [];
        }

        $roleName = (string) ($role['role_name'] ?? '');
        $permissionDescription = [];
        $permissionUiArea = [];

        try {
            $permBuilder = $db->table('us_permissions')
                ->select('permission_name, description');

            if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
                $permBuilder->where('status', 1);
            }

            $permRows = $permBuilder
                ->orderBy('permission_name', 'asc')
                ->get()
                ->getResultArray();

            foreach ($permRows as $row) {
                $permName = trim((string) ($row['permission_name'] ?? ''));
                if ($permName === '') {
                    continue;
                }

                $permissionDescription[$permName] = (string) ($row['description'] ?? '');
                $p = strtolower($permName);
                if (strpos($p, 'menu_') === 0) {
                    $permissionUiArea[$permName] = 'Menú';
                } elseif (strpos($p, 'header_') === 0 || $p === 'header_buttons') {
                    $permissionUiArea[$permName] = 'Header';
                } elseif (strpos($p, 'important_') === 0) {
                    $permissionUiArea[$permName] = 'Acceso rápido';
                } elseif (strpos($p, 'section_') === 0) {
                    $permissionUiArea[$permName] = 'Sección';
                } else {
                    $permissionUiArea[$permName] = 'Acción';
                }
            }
        } catch (\Throwable $e) {
            $permissionDescription = [];
            $permissionUiArea = [];
        }

        $rolePerms = [];
        try {
            $rpBuilder = $db->table('us_role_permissions as rp')
                ->select('p.permission_name')
                ->join('us_permissions as p', 'rp.permission_id = p.id', 'inner')
                ->where('rp.role_id', $roleId);

            if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
                $rpBuilder->where('p.status', 1);
            }

            $rows = $rpBuilder
                ->orderBy('p.permission_name', 'asc')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $permName = trim((string) ($row['permission_name'] ?? ''));
                if ($permName !== '') {
                    $rolePerms[$permName] = true;
                }
            }
        } catch (\Throwable $e) {
            $rolePerms = [];
        }

        $rolePerms = array_keys($rolePerms);
        sort($rolePerms, SORT_STRING);
        $rolePermSet = array_fill_keys($rolePerms, true);

        $candidatePerms = !empty($permissionDescription)
            ? array_keys($permissionDescription)
            : $rolePerms;

        $permissionZones = $this->buildPermissionZonesCatalog($candidatePerms);

        $steps = [];
        $adminPermissions = [];
        $canMoveStep = [];

        return [
            'role' => $role,
            'role_permissions' => $rolePerms,
            'role_permission_set' => $rolePermSet,
            'permission_zones' => $permissionZones,
            'steps' => $steps,
            'admin_permissions' => $adminPermissions,
            'permission_descriptions' => $permissionDescription,
            'permission_ui_area' => $permissionUiArea,
            'can_move_step' => $canMoveStep,
        ];
    }

    private function buildPermissionZonesCatalog(array $permissionNames): array
    {
        $zoneDefinitions = $this->getPermissionZoneDefinitions();
        $zones = [];

        foreach ($zoneDefinitions as $zoneKey => $definition) {
            $zones[$zoneKey] = $definition + ['permissions' => []];
        }

        foreach ($permissionNames as $permissionName) {
            $permissionName = trim((string) $permissionName);
            if ($permissionName === '') {
                continue;
            }

            $zoneKey = $this->detectPermissionAssignmentZoneKey($permissionName);
            if (!isset($zones[$zoneKey])) {
                $zoneKey = 'admin_otros';
            }

            $zones[$zoneKey]['permissions'][$permissionName] = true;
        }

        foreach ($zones as &$zone) {
            $permissions = array_keys($zone['permissions']);
            usort($permissions, static function (string $left, string $right): int {
                $leftLabel = function_exists('permission_ui_label') ? permission_ui_label($left) : $left;
                $rightLabel = function_exists('permission_ui_label') ? permission_ui_label($right) : $right;
                $byLabel = strcmp($leftLabel, $rightLabel);

                return $byLabel !== 0 ? $byLabel : strcmp($left, $right);
            });
            $zone['permissions'] = $permissions;
        }
        unset($zone);

        return array_filter($zones, static function (array $zone): bool {
            return !empty($zone['permissions']);
        });
    }

    private function buildRoleComparison(array $baseMap, ?array $compareMap): array
    {
        if (empty($compareMap['role'])) {
            return [
                'enabled' => false,
                'shared_permissions' => [],
                'only_target_permissions' => [],
                'only_compare_permissions' => [],
                'counts' => [
                    'target' => count($baseMap['role_permissions'] ?? []),
                    'compare' => 0,
                    'shared' => 0,
                    'only_target' => 0,
                    'only_compare' => 0,
                ],
                'step_counts' => [],
                'zone_counts' => [],
            ];
        }

        $targetPerms = $baseMap['role_permissions'] ?? [];
        $comparePerms = $compareMap['role_permissions'] ?? [];
        $shared = array_values(array_intersect($targetPerms, $comparePerms));
        $onlyTarget = array_values(array_diff($targetPerms, $comparePerms));
        $onlyCompare = array_values(array_diff($comparePerms, $targetPerms));
        sort($shared, SORT_STRING);
        sort($onlyTarget, SORT_STRING);
        sort($onlyCompare, SORT_STRING);

        $zoneCounts = [];
        foreach (($baseMap['permission_zones'] ?? []) as $zoneKey => $cfg) {
            $catalog = $cfg['permissions'] ?? [];
            $zoneCounts[$zoneKey] = [
                'shared' => count(array_intersect($catalog, $shared)),
                'only_target' => count(array_intersect($catalog, $onlyTarget)),
                'only_compare' => count(array_intersect($catalog, $onlyCompare)),
            ];
        }

        return [
            'enabled' => true,
            'shared_permissions' => $shared,
            'only_target_permissions' => $onlyTarget,
            'only_compare_permissions' => $onlyCompare,
            'counts' => [
                'target' => count($targetPerms),
                'compare' => count($comparePerms),
                'shared' => count($shared),
                'only_target' => count($onlyTarget),
                'only_compare' => count($onlyCompare),
            ],
            'step_counts' => $zoneCounts,
            'zone_counts' => $zoneCounts,
        ];
    }

    public function toggle_permission()
    {
        if ($resp = $this->guardManagementAccess()) {
            return $resp;
        }

        helper('acl_version');

        $roleId = (int)$this->request->getPost('role_id');
        $permissionName = trim((string)$this->request->getPost('permission_name'));
        $action = strtolower(trim((string)$this->request->getPost('action')));

        if ($roleId <= 0 || $permissionName === '' || ($action !== 'add' && $action !== 'remove')) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'ok' => false,
                    'message' => 'Parámetros inválidos.',
                ]);
        }

        $db = \Config\Database::connect();

        // Validar rol
        try {
            $roleExists = $db->table('us_roles')->select('id')->where('id', $roleId)->get()->getRowArray();
            if (empty($roleExists)) {
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON(['ok' => false, 'message' => 'Rol no encontrado.']);
            }
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'message' => 'Error consultando rol.']);
        }

        // Resolver permission_id (y validar status=1 si existe)
        $permRow = null;
        try {
            $permBuilder = $db->table('us_permissions')
                ->select('id, permission_name');

            if (is_object($db) && method_exists($db, 'fieldExists') && $db->fieldExists('status', 'us_permissions')) {
                $permBuilder->select('status');
            }

            $permRow = $permBuilder
                ->where('permission_name', $permissionName)
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            $permRow = null;
        }

        if (empty($permRow)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['ok' => false, 'message' => 'Permiso no encontrado.']);
        }

        if (array_key_exists('status', $permRow) && (int)$permRow['status'] !== 1) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON(['ok' => false, 'message' => 'Permiso inactivo (status=0).']);
        }

        $permissionId = (int)($permRow['id'] ?? 0);
        if ($permissionId <= 0) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'message' => 'Permiso inválido.']);
        }

        try {
            if ($action === 'add') {
                $exists = $db->table('us_role_permissions')
                    ->select('id')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                if (empty($exists)) {
                    $db->table('us_role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }

                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }

                return $this->response->setJSON([
                    'ok' => true,
                    'granted' => true,
                    'action' => 'added',
                    'role_id' => $roleId,
                    'permission_name' => $permissionName,
                ]);
            }

            // remove
            $db->table('us_role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->delete();

            if (function_exists('acl_bump_version')) {
                acl_bump_version();
            }

            return $this->response->setJSON([
                'ok' => true,
                'granted' => false,
                'action' => 'removed',
                'role_id' => $roleId,
                'permission_name' => $permissionName,
            ]);
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'ok' => false,
                    'message' => 'Error al actualizar el rol-permiso.',
                ]);
        }
    }

    public function role_permissions()
    {
        try {
            if ($resp = $this->guardManagementAccess()) {
                return $resp;
            }

            helper('acl_version');

            $session = session();
            $data['session'] = \Config\Services::session();
            $data['username'] = $session->get('user_name');
            $myid = $session->get('id');
            
            $role_permissions_crud = $this->_getGroceryCrudEnterprise();
            $role_permissions_crud->setTable('us_role_permissions');
            $role_permissions_crud->setSubject('Rol-Permiso', 'Roles-Permisos');
            $role_permissions_crud->defaultOrdering('us_role_permissions.id', 'desc');
            
            $role_permissions_crud->columns(['id', 'role_id', 'permission_id']);
            $role_permissions_crud->fields(['role_id', 'permission_id']);
            $role_permissions_crud->unsetDeleteMultiple();

            // Relaciones
            $role_permissions_crud->setRelation('role_id', 'us_roles', 'role_name');
            $role_permissions_crud->setRelation('permission_id', 'us_permissions', 'permission_name');

            $role_permissions_crud->callbackColumn('permission_id', static function ($value) {
                $value = trim((string) $value);
                if ($value === '') {
                    return '';
                }

                $label = function_exists('permission_ui_label') ? permission_ui_label($value) : $value;
                if ($label === $value) {
                    return esc($label);
                }

                return esc($label) . '<br><small class="text-muted">' . esc($value) . '</small>';
            });

            $role_permissions_crud->callbackAfterInsert(function ($stateParameters) use ($role_permissions_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $role_permissions_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $role_permissions_crud->callbackAfterUpdate(function ($stateParameters) use ($role_permissions_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $role_permissions_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });
            $role_permissions_crud->callbackAfterDelete(function ($stateParameters) use ($role_permissions_crud) {
                if (function_exists('acl_bump_version')) {
                    acl_bump_version();
                }
                $tableName = $role_permissions_crud->getTable();
                return logOperation($stateParameters, $tableName);
            });

            $role_permissions_output = $role_permissions_crud->render();
            $final_output = array_merge((array)$role_permissions_output, $data);
            echo $this->_example_output($final_output);

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
        $db = $this->_getDbData();
        $config = (new ConfigGroceryCrud())->getDefaultConfig();

        $groceryCrud = new GroceryCrud($config, $db);
        $this->applyDefaultCrudDateTimeFormatting($groceryCrud);
        return $groceryCrud;
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

    private function _example_output($salida = null) {
        $salida = (object)esc($salida, 'raw');
        if ($salida->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');

            echo $salida->output;
            exit;
        }
        // echo "<br> bbbbbbb";
        return view('/deskapp/extra-pages/grocery_page', (array)$salida);
    }

}

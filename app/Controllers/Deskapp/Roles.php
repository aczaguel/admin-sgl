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
            'description' => 'Permisos del rol por zonas (pasos 1 a 5) y permisos administrativos.',
            'target_role' => $role,
            'target_role_permissions' => $baseMap['role_permissions'],
            'target_role_permission_set' => $baseMap['role_permission_set'],
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

        $steps = [
            1 => [
                'name' => 'Paso 1: Datos del trámite',
                'roles_can_move' => ['Starter', 'Executer', 'Admin', 'Super Admin'],
                'permissions' => [],
            ],
            2 => [
                'name' => 'Paso 2: Gestor y Empresa',
                'roles_can_move' => ['Executer', 'Admin', 'Super Admin'],
                'permissions' => [],
            ],
            3 => [
                'name' => 'Paso 3: Pago de derechos',
                'roles_can_move' => ['Executer', 'Admin', 'Super Admin'],
                'permissions' => [],
            ],
            4 => [
                'name' => 'Paso 4: Pago a gestor',
                'roles_can_move' => ['Authorizer Editor', 'Authorizer Simple', 'Admin', 'Super Admin'],
                'permissions' => [],
            ],
            5 => [
                'name' => 'Paso 5: Cobro a cliente',
                'roles_can_move' => ['Closer', 'Admin', 'Super Admin'],
                'permissions' => [],
            ],
        ];

        $assignStepForPerm = static function (string $permName): ?int {
            $p = strtolower($permName);
            $contains = static function (string $haystack, string $needle): bool {
                return $needle !== '' && strpos($haystack, $needle) !== false;
            };

            if ($contains($p, 'section_final_costos') || $contains($p, 'final_tramite') || $contains($p, 'cobro_cliente') || $contains($p, 'final_costos') || $contains($p, 'concluido') || $contains($p, 'concluir') || $contains($p, 'cancelar')) {
                return 5;
            }
            if ($contains($p, 'section_pago_gestor') || $contains($p, 'pago_gestor') || $contains($p, 'editar_pago_gestor') || $contains($p, 'pasar_a_pagos')) {
                return 4;
            }
            if ($contains($p, 'section_pago_derechos') || $contains($p, 'pago_derechos') || $contains($p, 'linea_captura') || $contains($p, 'documentos_pago')) {
                return 3;
            }
            if ($contains($p, 'section_asigna_gestor') || $contains($p, 'tramite_view_gestor')) {
                return 2;
            }
            if ($contains($p, 'read_tramite') || $contains($p, 'listar_tramite') || $contains($p, 'create_tramite') || $contains($p, 'editar_tramite') || $contains($p, 'delete_tramite') || $contains($p, 'export_tramite') || $contains($p, 'print_tramite') || $contains($p, 'clone_tramite') || $contains($p, 'section_inicial_datos') || $contains($p, 'editar_tramite_principal') || $contains($p, 'editar_tramite_asociado')) {
                return 1;
            }
            if (strpos($p, 'menu_') === 0) {
                if ($contains($p, 'proceso_final')) {
                    return 5;
                }
                if ($contains($p, 'tramites')) {
                    return 1;
                }
                return null;
            }
            if (strpos($p, 'listar_') === 0) {
                if ($contains($p, 'final_tramite') || $contains($p, 'concluido')) {
                    return 5;
                }
                if ($contains($p, 'tramite')) {
                    return 1;
                }
                return null;
            }
            if (strpos($p, 'export_') === 0 || strpos($p, 'print_') === 0) {
                if ($contains($p, 'final_tramite')) {
                    return 5;
                }
                if ($contains($p, 'tramite')) {
                    return 1;
                }
            }

            return null;
        };

        $adminPermissions = [];
        if (!empty($permissionDescription)) {
            $candidatePerms = array_keys($permissionDescription);
            $stepPermSet = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
            foreach ($candidatePerms as $permName) {
                $stepNum = $assignStepForPerm($permName);
                if ($stepNum === null) {
                    $adminPermissions[$permName] = true;
                    continue;
                }
                $stepPermSet[$stepNum][$permName] = true;
            }

            foreach ($steps as $stepNum => &$cfg) {
                $perms = array_keys($stepPermSet[$stepNum]);
                sort($perms, SORT_STRING);
                $cfg['permissions'] = $perms;
            }
            unset($cfg);

            $adminPermissions = array_keys($adminPermissions);
            sort($adminPermissions, SORT_STRING);
        } else {
            foreach ($rolePerms as $permName) {
                $stepNum = $assignStepForPerm($permName);
                if ($stepNum === null) {
                    $adminPermissions[$permName] = true;
                    continue;
                }
                $steps[$stepNum]['permissions'][$permName] = true;
            }

            foreach ($steps as $stepNum => &$cfg) {
                $perms = array_keys($cfg['permissions'] ?? []);
                sort($perms, SORT_STRING);
                $cfg['permissions'] = $perms;
            }
            unset($cfg);

            $adminPermissions = array_keys($adminPermissions);
            sort($adminPermissions, SORT_STRING);
        }

        $canMoveStep = [];
        $roleNameLower = strtolower($roleName);
        foreach ($steps as $stepNum => $cfg) {
            $canMoveStep[$stepNum] = false;
            foreach (($cfg['roles_can_move'] ?? []) as $rn) {
                if (strtolower((string) $rn) === $roleNameLower) {
                    $canMoveStep[$stepNum] = true;
                    break;
                }
            }
        }

        return [
            'role' => $role,
            'role_permissions' => $rolePerms,
            'role_permission_set' => $rolePermSet,
            'steps' => $steps,
            'admin_permissions' => $adminPermissions,
            'permission_descriptions' => $permissionDescription,
            'permission_ui_area' => $permissionUiArea,
            'can_move_step' => $canMoveStep,
        ];
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

        $stepCounts = [];
        foreach (($baseMap['steps'] ?? []) as $stepNum => $cfg) {
            $catalog = $cfg['permissions'] ?? [];
            $stepCounts[$stepNum] = [
                'shared' => count(array_intersect($catalog, $shared)),
                'only_target' => count(array_intersect($catalog, $onlyTarget)),
                'only_compare' => count(array_intersect($catalog, $onlyCompare)),
            ];
        }

        $adminCatalog = $baseMap['admin_permissions'] ?? [];
        $stepCounts['admin'] = [
            'shared' => count(array_intersect($adminCatalog, $shared)),
            'only_target' => count(array_intersect($adminCatalog, $onlyTarget)),
            'only_compare' => count(array_intersect($adminCatalog, $onlyCompare)),
        ];

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
            'step_counts' => $stepCounts,
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
                'charset' => 'utf8'
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

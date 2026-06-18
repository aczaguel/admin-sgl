<?php

helper(['url', 'permissions', 'cliente_context']);

if (!function_exists('sgl_navigation_context')) {
    function sgl_navigation_context($session = null): array
    {
        $session = $session ?: session();
        $perms = $session->get('user_permissions') ?? [];
        $roles = $session->get('user_roles') ?? [];

        $useClienteSidebar = has_permission_strict('ui_sidebar_cliente', $perms)
            && !(
                has_permission('menu_dashboard_admin', $perms, $roles)
                || has_permission('menu_tramites', $perms, $roles)
                || has_permission('menu_proceso_final', $perms, $roles)
                || has_permission('menu_gestores', $perms, $roles)
                || has_permission('menu_clientes', $perms, $roles)
                || has_permission('menu_configuracion', $perms, $roles)
                || has_permission('menu_permisos', $perms, $roles)
            );

        return [
            'session' => $session,
            'perms' => $perms,
            'roles' => $roles,
            'is_cliente_ui' => $useClienteSidebar,
        ];
    }
}

if (!function_exists('sgl_navigation_request_state')) {
    function sgl_navigation_request_state(): array
    {
        $request = service('request');
        $uri = $request ? $request->getUri() : null;
        $query = [];

        foreach (($request->getGet() ?? []) as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $query[(string) $key] = (string) $value;
        }

        return [
            'path' => trim($uri ? $uri->getPath() : '', '/'),
            'query' => $query,
        ];
    }
}

if (!function_exists('sgl_navigation_url')) {
    function sgl_navigation_url(string $path, array $query = []): string
    {
        $normalizedPath = trim($path, '/');
        $url = preg_match('#^https?://#i', $path) ? $path : base_url($normalizedPath);

        if ($query === []) {
            return $url;
        }

        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . http_build_query($query);
    }
}

if (!function_exists('sgl_navigation_item')) {
    function sgl_navigation_item(string $label, string $path, array $options = []): array
    {
        $query = $options['query'] ?? [];

        return [
            'label' => $label,
            'path' => trim($path, '/'),
            'url' => sgl_navigation_url($path, $query),
            'icon' => $options['icon'] ?? null,
            'group' => $options['group'] ?? null,
            'perm_tags' => array_values(array_filter($options['perm_tags'] ?? [])),
            'match_paths' => array_values(array_filter(array_map(static function ($value) {
                return trim((string) $value, '/');
            }, $options['match_paths'] ?? []))),
            'match_prefixes' => array_values(array_filter(array_map(static function ($value) {
                return trim((string) $value, '/');
            }, $options['match_prefixes'] ?? []))),
            'query' => $query,
            'active' => false,
        ];
    }
}

if (!function_exists('sgl_navigation_query_matches')) {
    function sgl_navigation_query_matches(array $expectedQuery, array $currentQuery): bool
    {
        foreach ($expectedQuery as $key => $value) {
            if (!array_key_exists($key, $currentQuery)) {
                return false;
            }

            if ((string) $currentQuery[$key] !== (string) $value) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('sgl_navigation_item_is_active')) {
    function sgl_navigation_item_is_active(array $item, array $requestState): bool
    {
        $currentPath = $requestState['path'] ?? '';
        $currentQuery = $requestState['query'] ?? [];
        $paths = array_unique(array_filter(array_merge([$item['path']], $item['match_paths'] ?? [])));

        foreach ($paths as $path) {
            if ($path === $currentPath && sgl_navigation_query_matches($item['query'] ?? [], $currentQuery)) {
                return true;
            }
        }

        foreach (($item['match_prefixes'] ?? []) as $prefix) {
            if ($prefix !== '' && ($currentPath === $prefix || strpos($currentPath, $prefix . '/') === 0)) {
                if (sgl_navigation_query_matches($item['query'] ?? [], $currentQuery)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('sgl_navigation_section_is_active')) {
    function sgl_navigation_section_is_active(array $section, array $requestState): bool
    {
        $currentPath = $requestState['path'] ?? '';

        foreach (($section['match_prefixes'] ?? []) as $prefix) {
            if ($prefix !== '' && ($currentPath === $prefix || strpos($currentPath, $prefix . '/') === 0)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('sgl_navigation_finalize')) {
    function sgl_navigation_finalize(array $sections): array
    {
        $requestState = sgl_navigation_request_state();
        $finalSections = [];
        $activeSectionKey = null;

        foreach ($sections as $section) {
            $items = [];
            $hasActiveItem = false;

            foreach (($section['items'] ?? []) as $item) {
                $item['active'] = sgl_navigation_item_is_active($item, $requestState);
                if ($item['active']) {
                    $hasActiveItem = true;
                }
                $items[] = $item;
            }

            if ($items === []) {
                continue;
            }

            $section['items'] = $items;
            $section['active'] = $hasActiveItem || sgl_navigation_section_is_active($section, $requestState);

            if ($section['active'] && $activeSectionKey === null) {
                $activeSectionKey = $section['key'];
            }

            $finalSections[] = $section;
        }

        if ($activeSectionKey === null && $finalSections !== []) {
            $activeSectionKey = $finalSections[0]['key'];
        }

        foreach ($finalSections as &$section) {
            $section['active'] = ($section['key'] === $activeSectionKey);
        }
        unset($section);

        return [
            'sections' => $finalSections,
            'activeSectionKey' => $activeSectionKey,
            'request' => $requestState,
        ];
    }
}

if (!function_exists('sgl_build_admin_navigation')) {
    function sgl_build_admin_navigation(array $context): array
    {
        $session = $context['session'];
        $perms = $context['perms'];
        $roles = $context['roles'];
        $userId = $session->get('id');
        $isAdmin = function_exists('user_is_admin') ? user_is_admin($userId) : false;

        $sections = [];

        if (has_permission('menu_dashboard_admin', $perms, $roles)) {
            $sections[] = [
                'key' => 'dashboard-admin',
                'label' => 'Dashboard',
                'icon' => 'fas fa-chart-line',
                'match_prefixes' => ['deskapp/dashboardadmin'],
                'items' => [
                    sgl_navigation_item('Panel Principal 2026', 'deskapp/dashboardadmin', [
                        'icon' => 'fas fa-home',
                        'group' => 'Vista General',
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                    sgl_navigation_item('Alertas Críticas', 'deskapp/dashboardadmin/alertas', [
                        'icon' => 'fas fa-exclamation-triangle',
                        'group' => 'Vista General',
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                    sgl_navigation_item('Análisis Financiero', 'deskapp/dashboardadmin/financiero', [
                        'icon' => 'fas fa-file-invoice-dollar',
                        'group' => 'Vista General',
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                    sgl_navigation_item('Reportes y Estadísticas', 'deskapp/dashboardadmin/reportes', [
                        'icon' => 'fas fa-chart-bar',
                        'group' => 'Vista General',
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                    sgl_navigation_item('Trámites por Cliente', 'deskapp/dashboardadmin/por_cliente', [
                        'icon' => 'fas fa-building',
                        'group' => 'Vista General',
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                    sgl_navigation_item('2025', 'deskapp/dashboardadmin', [
                        'icon' => 'fas fa-calendar-alt',
                        'group' => 'Histórico',
                        'query' => ['anio' => '2025'],
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                    sgl_navigation_item('2024', 'deskapp/dashboardadmin', [
                        'icon' => 'fas fa-calendar-alt',
                        'group' => 'Histórico',
                        'query' => ['anio' => '2024'],
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                    sgl_navigation_item('2023', 'deskapp/dashboardadmin', [
                        'icon' => 'fas fa-calendar-alt',
                        'group' => 'Histórico',
                        'query' => ['anio' => '2023'],
                        'perm_tags' => ['menu_dashboard_admin'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_tramites', $perms, $roles)) {
            $tramitesItems = [];

            if ($isAdmin || has_permission('create_tramite', $perms, $roles)) {
                $tramitesItems[] = sgl_navigation_item('Nuevo Trámite', 'deskapp/tramites/add', [
                    'icon' => 'fas fa-plus-circle',
                    'group' => 'Acciones',
                    'perm_tags' => ['create_tramite'],
                    'match_prefixes' => ['deskapp/tramites/add'],
                ]);
            }

            if (has_permission('listar_tramite', $perms, $roles)) {
                $tramitesItems[] = sgl_navigation_item('Trámites (nuevo flujo)', 'deskapp/tramitesn/tramite', [
                    'icon' => 'fas fa-magic',
                    'group' => 'Operación',
                    'perm_tags' => ['listar_tramite'],
                    'match_prefixes' => ['deskapp/tramitesn/tramite'],
                ]);
            }

            if (has_permission('search_tramite', $perms, $roles)) {
                $tramitesItems[] = sgl_navigation_item('Busca un trámite', 'deskapp/tramitesn/search', [
                    'icon' => 'fas fa-search',
                    'group' => 'Operación',
                    'perm_tags' => ['search_tramite'],
                    'match_prefixes' => ['deskapp/tramitesn/search'],
                ]);
            }

            if (has_permission('listar_tramite', $perms, $roles)) {
                $tramitesItems[] = sgl_navigation_item('Prototipo Layout', 'deskapp/tramitesn/prototipo-layout', [
                    'icon' => 'fas fa-drafting-compass',
                    'group' => 'Laboratorio',
                    'perm_tags' => ['listar_tramite'],
                    'match_prefixes' => ['deskapp/tramitesn/prototipo-layout'],
                ]);
            }

            $tramitesItems[] = sgl_navigation_item('Flotillas (Importar)', 'deskapp/flotillas/import', [
                'icon' => 'fas fa-layer-group',
                'group' => 'Operación',
                'perm_tags' => ['menu_tramites'],
                'match_prefixes' => ['deskapp/flotillas'],
            ]);

            if (has_permission('create_tramite', $perms, $roles)) {
                $tramitesItems[] = sgl_navigation_item('Trámites Masivos', 'deskapp/tramites_masivos/import', [
                    'icon' => 'fas fa-file-upload',
                    'group' => 'Operación',
                    'perm_tags' => ['menu_tramites', 'create_tramite'],
                    'match_prefixes' => ['deskapp/tramites_masivos'],
                ]);
            }

            if (has_permission('listar_tramites_concluidos', $perms, $roles)) {
                $tramitesItems[] = sgl_navigation_item('Concluidos', 'deskapp/concluido/final', [
                    'icon' => 'fas fa-check-circle',
                    'group' => 'Seguimiento',
                    'perm_tags' => ['listar_tramites_concluidos'],
                    'match_prefixes' => ['deskapp/concluido'],
                ]);
            }

            if (has_permission('menu_tramites_tenencias', $perms, $roles)) {
                $tramitesItems[] = sgl_navigation_item('Tenencias', 'deskapp/tramites/tenencias', [
                    'icon' => 'fas fa-car',
                    'group' => 'Seguimiento',
                    'perm_tags' => ['menu_tramites_tenencias'],
                    'match_prefixes' => ['deskapp/tramites/tenencias'],
                ]);
            }

            if ($tramitesItems !== []) {
                $sections[] = [
                    'key' => 'tramites',
                    'label' => 'Trámites',
                    'icon' => 'fas fa-folder-open',
                    'match_prefixes' => [
                        'deskapp/tramitesn',
                        'deskapp/tramites_masivos',
                        'deskapp/flotillas',
                        'deskapp/concluido',
                        'deskapp/tramites',
                    ],
                    'items' => $tramitesItems,
                ];
            }
        }

        $showCobranzaMenu = has_permission('list_cobro_cliente', $perms, $roles);
        $showProcesoFinalMenu = has_permission('menu_proceso_final', $perms, $roles);
        if ($showCobranzaMenu || $showProcesoFinalMenu) {
            $faseFinalItems = [];

            if ($showCobranzaMenu) {
                $faseFinalItems[] = sgl_navigation_item('Centro de Cobranza', 'deskapp/cobranza', [
                    'icon' => 'fas fa-hand-holding-usd',
                    'group' => 'Cobranza',
                    'perm_tags' => ['list_cobro_cliente'],
                    'match_prefixes' => ['deskapp/cobranza'],
                ]);
            }

            if ($showProcesoFinalMenu && has_permission('read_final_tramite', $perms, $roles)) {
                $faseFinalItems[] = sgl_navigation_item('Finalizado', 'deskapp/proceso/final', [
                    'icon' => 'fas fa-check-double',
                    'group' => 'Cierre',
                    'perm_tags' => ['read_final_tramite'],
                    'match_prefixes' => ['deskapp/proceso/final'],
                ]);
            }

            if ($showProcesoFinalMenu && has_permission('listar_tramites_cancelado', $perms, $roles)) {
                $faseFinalItems[] = sgl_navigation_item('Cancelados', 'deskapp/tramites/cancelados', [
                    'icon' => 'fas fa-times-circle',
                    'group' => 'Cierre',
                    'perm_tags' => ['listar_tramites_cancelado'],
                    'match_prefixes' => ['deskapp/tramites/cancelados'],
                ]);
            }

            if ($faseFinalItems !== []) {
                $sections[] = [
                    'key' => 'fase-final',
                    'label' => 'Fase Final',
                    'icon' => 'fas fa-flag-checkered',
                    'match_prefixes' => ['deskapp/cobranza', 'deskapp/proceso', 'deskapp/tramites/cancelados'],
                    'items' => $faseFinalItems,
                ];
            }
        }

        $configItems = [];
        if (has_permission('menu_gestores', $perms, $roles)) {
            $configItems[] = sgl_navigation_item('Empresa Gestora', 'deskapp/gestores/gestores', [
                'icon' => 'fas fa-building',
                'group' => 'Gestores',
                'perm_tags' => ['menu_gestores'],
                'match_prefixes' => ['deskapp/gestores/gestores'],
            ]);
            $configItems[] = sgl_navigation_item('Gestor', 'deskapp/gestores/gestor', [
                'icon' => 'fas fa-user-tie',
                'group' => 'Gestores',
                'perm_tags' => ['menu_gestores'],
                'match_prefixes' => ['deskapp/gestores/gestor'],
            ]);
        }

        if (has_permission('menu_clientes', $perms, $roles)) {
            $configItems[] = sgl_navigation_item('Cliente', 'deskapp/clientes/cliente', [
                'icon' => 'fas fa-user-circle',
                'group' => 'Clientes',
                'perm_tags' => ['menu_clientes'],
                'match_prefixes' => ['deskapp/clientes/cliente'],
            ]);
            $configItems[] = sgl_navigation_item('Clientes directos', 'deskapp/clidirecto/clidirecto', [
                'icon' => 'fas fa-building',
                'group' => 'Clientes',
                'perm_tags' => ['menu_clientes'],
                'match_prefixes' => ['deskapp/clidirecto/clidirecto'],
            ]);
            $configItems[] = sgl_navigation_item('Ejecutivos de cliente', 'deskapp/clidirecto/ejecutivo', [
                'icon' => 'fas fa-user-tie',
                'group' => 'Clientes',
                'perm_tags' => ['menu_clientes'],
                'match_prefixes' => ['deskapp/clidirecto/ejecutivo'],
            ]);
        }

        if (has_permission('menu_configuracion', $perms, $roles)) {
            $configItems[] = sgl_navigation_item('Tipo de Trámite', 'deskapp/tramites/tipo', [
                'icon' => 'fas fa-tags',
                'group' => 'Tipos de Trámite',
                'perm_tags' => ['menu_configuracion'],
                'match_prefixes' => ['deskapp/tramites/tipo'],
            ]);
            $configItems[] = sgl_navigation_item('Estatuses de Trámite', 'deskapp/tramites/status', [
                'icon' => 'fas fa-traffic-light',
                'group' => 'Tipos de Trámite',
                'perm_tags' => ['menu_configuracion'],
                'match_prefixes' => ['deskapp/tramites/status'],
            ]);
        }

        if (has_permission('menu_documentos', $perms, $roles)) {
            $configItems[] = sgl_navigation_item('Documento', 'deskapp/documentos/documento', [
                'icon' => 'fas fa-file',
                'group' => 'Documentos',
                'perm_tags' => ['menu_documentos'],
                'match_prefixes' => ['deskapp/documentos/documento'],
            ]);
            $configItems[] = sgl_navigation_item('Status de Documentos', 'deskapp/documentos/status', [
                'icon' => 'fas fa-info-circle',
                'group' => 'Documentos',
                'perm_tags' => ['menu_documentos'],
                'match_prefixes' => ['deskapp/documentos/status'],
            ]);
        }

        if ($configItems !== []) {
            $sections[] = [
                'key' => 'configuracion',
                'label' => 'Configuración',
                'icon' => 'fas fa-cogs',
                'match_prefixes' => ['deskapp/gestores', 'deskapp/clientes', 'deskapp/cliente', 'deskapp/clidirecto', 'deskapp/tramites/tipo', 'deskapp/tramites/status', 'deskapp/documentos'],
                'items' => $configItems,
            ];
        }

        if (has_permission('menu_roles', $perms, $roles)) {
            $sections[] = [
                'key' => 'seguridad',
                'label' => 'Usuarios',
                'icon' => 'fas fa-user-shield',
                'match_prefixes' => ['deskapp/roles', 'deskapp/permisos', 'deskapp/users'],
                'items' => [
                    sgl_navigation_item('Roles', 'deskapp/roles/roles', [
                        'icon' => 'fas fa-user-tag',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_roles'],
                        'match_prefixes' => ['deskapp/roles/roles'],
                    ]),
                    sgl_navigation_item('Permisos', 'deskapp/permisos/permisos', [
                        'icon' => 'fas fa-key',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_roles'],
                        'match_prefixes' => ['deskapp/permisos/permisos'],
                    ]),
                    sgl_navigation_item('Roles-Permisos', 'deskapp/roles/role_permissions', [
                        'icon' => 'fas fa-link',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_roles'],
                        'match_prefixes' => ['deskapp/roles/role_permissions'],
                    ]),
                    sgl_navigation_item('Usuarios', 'deskapp/users/users', [
                        'icon' => 'fas fa-users',
                        'group' => 'Usuarios',
                        'perm_tags' => ['menu_roles'],
                        'match_prefixes' => ['deskapp/users/users'],
                    ]),
                    sgl_navigation_item('Usuarios-Roles', 'deskapp/users/user_roles', [
                        'icon' => 'fas fa-user-cog',
                        'group' => 'Usuarios',
                        'perm_tags' => ['menu_roles'],
                        'match_prefixes' => ['deskapp/users/user_roles'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_monitoreo_actividad', $perms, $roles)) {
            $monitorItems = [];

            if (has_permission('monitoreo_bitacora_search', $perms, $roles)) {
                $monitorItems[] = sgl_navigation_item('Bitácora Search', 'bitacora/search', [
                    'icon' => 'fas fa-history',
                    'group' => 'Monitoreo',
                    'perm_tags' => ['monitoreo_bitacora_search'],
                    'match_prefixes' => ['bitacora/search', 'deskapp/bitacora'],
                ]);
            }

            if (has_permission('monitoreo_correccion_tramites', $perms, $roles)) {
                $monitorItems[] = sgl_navigation_item('Corrección de Trámites', 'correccion-tramites', [
                    'icon' => 'fas fa-edit',
                    'group' => 'Monitoreo',
                    'perm_tags' => ['monitoreo_correccion_tramites'],
                    'match_prefixes' => ['correccion-tramites'],
                ]);
            }

            if (has_permission('monitoreo_auditoria_tramite', $perms, $roles)) {
                $monitorItems[] = sgl_navigation_item('Auditoría de Trámite', 'deskapp/tramites/audit_search', [
                    'icon' => 'fas fa-clipboard-check',
                    'group' => 'Monitoreo',
                    'perm_tags' => ['monitoreo_auditoria_tramite'],
                    'match_prefixes' => ['deskapp/tramites/audit_search', 'deskapp/tramites/audit_timeline'],
                ]);
            }

            if ($monitorItems !== []) {
                $sections[] = [
                    'key' => 'monitoreo',
                    'label' => 'Monitoreo',
                    'icon' => 'fas fa-clipboard-list',
                    'match_prefixes' => ['bitacora', 'correccion-tramites', 'deskapp/tramites/audit'],
                    'items' => $monitorItems,
                ];
            }
        }

        return $sections;
    }
}

if (!function_exists('sgl_build_cliente_navigation')) {
    function sgl_build_cliente_navigation(array $context): array
    {
        $session = $context['session'];
        $perms = $context['perms'];
        $roles = $context['roles'];
        $sections = [];

        $clienteItems = [];
        if (has_permission('menu_dashboard_cliente', $perms, $roles)) {
            $clienteItems[] = sgl_navigation_item('Resumen en tiempo real', 'deskapp/clientes/dashboard', [
                'icon' => 'dw dw-analytics-21',
                'group' => 'Dashboard Cliente',
                'perm_tags' => ['menu_dashboard_cliente'],
                'match_prefixes' => ['deskapp/clientes/dashboard'],
            ]);
        }
        if (has_permission('menu_tramites_cliente', $perms, $roles)) {
            $clienteItems[] = sgl_navigation_item('Trámites', 'deskapp/clientes/tramites', [
                'icon' => 'dw dw-house-1',
                'group' => 'Dashboard Cliente',
                'perm_tags' => ['menu_tramites_cliente'],
                'match_prefixes' => ['deskapp/clientes/tramites'],
            ]);
        }
        if ($clienteItems !== []) {
            $sections[] = [
                'key' => 'cliente-dashboard',
                'label' => 'Cliente',
                'icon' => 'fas fa-chart-line',
                'match_prefixes' => ['deskapp/clientes'],
                'items' => $clienteItems,
            ];
        }

        if (has_permission('menu_proceso_final', $perms, $roles) && has_permission('read_final_tramite', $perms, $roles)) {
            $sections[] = [
                'key' => 'cliente-proceso-final',
                'label' => 'Proceso Final',
                'icon' => 'fas fa-flag-checkered',
                'match_prefixes' => ['deskapp/proceso/final'],
                'items' => [
                    sgl_navigation_item('Finalizando', 'deskapp/proceso/final', [
                        'icon' => 'fas fa-check-double',
                        'group' => 'Proceso Final',
                        'perm_tags' => ['read_final_tramite'],
                        'match_prefixes' => ['deskapp/proceso/final'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_gestores', $perms, $roles)) {
            $sections[] = [
                'key' => 'cliente-gestores',
                'label' => 'Gestores',
                'icon' => 'fas fa-handshake',
                'match_prefixes' => ['deskapp/gestores'],
                'items' => [
                    sgl_navigation_item('Empresa Gestora', 'deskapp/gestores/gestores', [
                        'icon' => 'fas fa-building',
                        'group' => 'Gestores',
                        'perm_tags' => ['menu_gestores'],
                        'match_prefixes' => ['deskapp/gestores/gestores'],
                    ]),
                    sgl_navigation_item('Gestor', 'deskapp/gestores/gestor', [
                        'icon' => 'fas fa-user-tie',
                        'group' => 'Gestores',
                        'perm_tags' => ['menu_gestores'],
                        'match_prefixes' => ['deskapp/gestores/gestor'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_clientes', $perms, $roles)) {
            $sections[] = [
                'key' => 'cliente-clientes',
                'label' => 'Clientes',
                'icon' => 'fas fa-user-friends',
                'match_prefixes' => ['deskapp/cliente', 'deskapp/clidirecto'],
                'items' => [
                    sgl_navigation_item('Clientes', 'deskapp/cliente/cliente', [
                        'icon' => 'fas fa-user-circle',
                        'group' => 'Clientes',
                        'perm_tags' => ['menu_clientes'],
                        'match_prefixes' => ['deskapp/cliente/cliente'],
                    ]),
                    sgl_navigation_item('Cliente Directo', 'deskapp/clidirecto/clidirecto', [
                        'icon' => 'fas fa-building',
                        'group' => 'Clientes',
                        'perm_tags' => ['menu_clientes'],
                        'match_prefixes' => ['deskapp/clidirecto/clidirecto'],
                    ]),
                    sgl_navigation_item('Ejecutivo', 'deskapp/clidirecto/ejecutivo', [
                        'icon' => 'fas fa-user-tie',
                        'group' => 'Clientes',
                        'perm_tags' => ['menu_clientes'],
                        'match_prefixes' => ['deskapp/clidirecto/ejecutivo'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_configuracion', $perms, $roles)) {
            $sections[] = [
                'key' => 'cliente-configuracion',
                'label' => 'Configuración',
                'icon' => 'fas fa-cog',
                'match_prefixes' => ['deskapp/tramites/tipo', 'deskapp/tramites/status'],
                'items' => [
                    sgl_navigation_item('Tipo de Trámite', 'deskapp/tramites/tipo', [
                        'icon' => 'fas fa-tags',
                        'group' => 'Configuración',
                        'perm_tags' => ['menu_configuracion'],
                        'match_prefixes' => ['deskapp/tramites/tipo'],
                    ]),
                    sgl_navigation_item('Estatuses de Trámite', 'deskapp/tramites/status', [
                        'icon' => 'fas fa-traffic-light',
                        'group' => 'Configuración',
                        'perm_tags' => ['menu_configuracion'],
                        'match_prefixes' => ['deskapp/tramites/status'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_documentos', $perms, $roles)) {
            $sections[] = [
                'key' => 'cliente-documentos',
                'label' => 'Documentos',
                'icon' => 'fas fa-file-alt',
                'match_prefixes' => ['deskapp/documentos'],
                'items' => [
                    sgl_navigation_item('Documento', 'deskapp/documentos/documento', [
                        'icon' => 'fas fa-file',
                        'group' => 'Documentos',
                        'perm_tags' => ['menu_documentos'],
                        'match_prefixes' => ['deskapp/documentos/documento'],
                    ]),
                    sgl_navigation_item('Estatus', 'deskapp/documentos/status', [
                        'icon' => 'fas fa-info-circle',
                        'group' => 'Documentos',
                        'perm_tags' => ['menu_documentos'],
                        'match_prefixes' => ['deskapp/documentos/status'],
                    ]),
                    sgl_navigation_item('Por Trámite', 'deskapp/documentos/por_tramite', [
                        'icon' => 'fas fa-stream',
                        'group' => 'Documentos',
                        'perm_tags' => ['menu_documentos'],
                        'match_prefixes' => ['deskapp/documentos/por_tramite'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_permisos', $perms, $roles)) {
            $sections[] = [
                'key' => 'cliente-permisos',
                'label' => 'Permisos',
                'icon' => 'fas fa-shield-alt',
                'match_prefixes' => ['deskapp/users', 'deskapp/roles', 'deskapp/permisos'],
                'items' => [
                    sgl_navigation_item('Usuarios', 'deskapp/users/users', [
                        'icon' => 'fas fa-users',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_permisos'],
                        'match_prefixes' => ['deskapp/users/users'],
                    ]),
                    sgl_navigation_item('Usuarios - Roles', 'deskapp/users/user_roles', [
                        'icon' => 'fas fa-user-cog',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_permisos'],
                        'match_prefixes' => ['deskapp/users/user_roles'],
                    ]),
                    sgl_navigation_item('Roles', 'deskapp/roles/roles', [
                        'icon' => 'fas fa-user-tag',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_permisos'],
                        'match_prefixes' => ['deskapp/roles/roles'],
                    ]),
                    sgl_navigation_item('Permisos', 'deskapp/permisos/permisos', [
                        'icon' => 'fas fa-key',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_permisos'],
                        'match_prefixes' => ['deskapp/permisos/permisos'],
                    ]),
                    sgl_navigation_item('Roles - Permisos', 'deskapp/roles/role_permissions', [
                        'icon' => 'fas fa-link',
                        'group' => 'Accesos',
                        'perm_tags' => ['menu_permisos'],
                        'match_prefixes' => ['deskapp/roles/role_permissions'],
                    ]),
                ],
            ];
        }

        if (has_permission('menu_erp_sa', $perms, $roles)) {
            $sections[] = [
                'key' => 'cliente-extras',
                'label' => 'Extras',
                'icon' => 'fas fa-rocket',
                'match_prefixes' => ['deskapp/calendar', 'deskapp/ui', 'deskapp/icons', 'deskapp/charts', 'deskapp/Additionalpages', 'deskapp/error', 'deskapp/extrapages', 'deskapp/docs', 'deskapp/sitemap', 'deskapp/chat', 'deskapp/invoice'],
                'items' => [
                    sgl_navigation_item('Calendar', 'deskapp/calendar', [
                        'icon' => 'dw dw-calendar1',
                        'group' => 'Extras',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/calendar'],
                    ]),
                    sgl_navigation_item('Buttons', 'deskapp/ui/buttons', [
                        'icon' => 'fas fa-square',
                        'group' => 'UI Elements',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/ui/buttons'],
                    ]),
                    sgl_navigation_item('Cards', 'deskapp/ui/cards', [
                        'icon' => 'fas fa-clone',
                        'group' => 'UI Elements',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/ui/cards'],
                    ]),
                    sgl_navigation_item('FontAwesome Icons', 'deskapp/icons/fontawesome', [
                        'icon' => 'fas fa-icons',
                        'group' => 'Icons',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/icons/fontawesome'],
                    ]),
                    sgl_navigation_item('Apexcharts', 'deskapp/charts/apexcharts', [
                        'icon' => 'fas fa-chart-area',
                        'group' => 'Charts',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/charts/apexcharts'],
                    ]),
                    sgl_navigation_item('Video Player', 'deskapp/Additionalpages/videoplayer', [
                        'icon' => 'fas fa-play-circle',
                        'group' => 'Additional Pages',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/Additionalpages/videoplayer'],
                    ]),
                    sgl_navigation_item('Error 404', 'deskapp/error/error_404', [
                        'icon' => 'fas fa-exclamation-circle',
                        'group' => 'Error Pages',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/error/error_404'],
                    ]),
                    sgl_navigation_item('Profile', 'deskapp/extrapages/profile', [
                        'icon' => 'fas fa-user',
                        'group' => 'Extra Pages',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/extrapages/profile'],
                    ]),
                    sgl_navigation_item('Sitemap', 'deskapp/sitemap', [
                        'icon' => 'dw dw-diagram',
                        'group' => 'Extras',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/sitemap'],
                    ]),
                    sgl_navigation_item('Chat', 'deskapp/chat', [
                        'icon' => 'dw dw-chat3',
                        'group' => 'Extras',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/chat'],
                    ]),
                    sgl_navigation_item('Invoice', 'deskapp/invoice', [
                        'icon' => 'dw dw-invoice',
                        'group' => 'Extras',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/invoice'],
                    ]),
                    sgl_navigation_item('Documentation', 'deskapp/docs/introduction', [
                        'icon' => 'fas fa-book',
                        'group' => 'Documentation',
                        'perm_tags' => ['menu_erp_sa'],
                        'match_prefixes' => ['deskapp/docs'],
                    ]),
                    sgl_navigation_item('Landing Page', 'https://dropways.github.io/deskapp-free-single-page-website-template/', [
                        'icon' => 'dw dw-paper-plane1',
                        'group' => 'Documentation',
                        'perm_tags' => ['menu_erp_sa'],
                    ]),
                ],
            ];
        }

        return $sections;
    }
}

if (!function_exists('sgl_build_ribbon_navigation')) {
    function sgl_build_ribbon_navigation($session = null): array
    {
        $context = sgl_navigation_context($session);
        $sections = $context['is_cliente_ui']
            ? sgl_build_cliente_navigation($context)
            : sgl_build_admin_navigation($context);

        $navigation = sgl_navigation_finalize($sections);
        $navigation['variant'] = $context['is_cliente_ui'] ? 'cliente' : 'admin';

        return $navigation;
    }
}
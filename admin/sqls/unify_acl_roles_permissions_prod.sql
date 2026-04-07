-- Unifica us_role_permissions de prod al estado de local.
-- Generado automaticamente desde acl_roles_permissions_local.sql vs acl_roles_permissions_prod.sql.
-- Inserta solo relaciones faltantes; no elimina nada.

START TRANSACTION;

-- Rol: Admin
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name IN (
    'menu_erp_sa',
    'export_tramite',
    'print_tramite',
    'read_tramite',
    'clone_tramite',
    'search_tramite',
    'export_final_tramite',
    'print_final_tramite',
    'read_final_tramite',
    'listar_settings',
    'editar_pago_gestor',
    'section_pago_gestor',
    'listar_concluidos_tramite',
    'menu_tramites_tenencias',
    'create_tramite',
    'delete_tramite_asociado',
    'editar_tramite_asociado',
    'editar_tramite_principal',
    'listar_tramites_concluidos',
    'menu_dashboard_admin',
    'menu_roles',
    'list_cobro_cliente',
    'can_upload_dropzone_cobro_cliente',
    'can_upload_dropzone_pago_derechos',
    'can_upload_dropzone_pago_gestor',
    'write_tramite_asigna_gestor',
    'write_tramite_datos_tramite',
    'write_tramite_pago_derechos',
    'menu_dashboard_cliente',
    'menu_tramites_cliente',
    'quick_action_bitacora',
    'quick_action_cobros_cliente',
    'quick_action_documentos',
    'quick_action_evidencias_finales',
    'quick_action_pago_gestor',
    'quick_action_pagos_derecho',
    'ui_sidebar_cliente',
    'menu_notifications',
    'wizard_list_only_own',
    'quick_action_documentos_add',
    'quick_action_documentos_edit',
    'quick_action_documentos_delete',
    'quick_action_bitacora_add',
    'quick_action_bitacora_edit',
    'quick_action_bitacora_delete',
    'quick_action_pagos_derecho_add',
    'quick_action_pagos_derecho_edit',
    'quick_action_pagos_derecho_delete',
    'quick_action_pago_gestor_add',
    'quick_action_pago_gestor_edit',
    'quick_action_pago_gestor_delete',
    'quick_action_evidencias_finales_add',
    'quick_action_evidencias_finales_edit',
    'quick_action_evidencias_finales_delete',
    'quick_action_cobros_cliente_add',
    'quick_action_cobros_cliente_edit',
    'quick_action_cobros_cliente_delete',
    'mios_filter_status_11',
    'mios_filter_status_22',
    'tramitesn_filter_owner_only',
    'override_tramite_approved_lock',
    'override_puede_editar_modulo',
    'listar_tramites_cancelado',
    'read_tramite_cancelado',
    'delete_tramite_cancelado',
    'export_tramite_cancelado',
    'print_tramite_cancelado',
    'clone_tramite_cancelado',
    'editar_tramite_cancelado'
)
LEFT JOIN us_role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
WHERE r.role_name = 'Admin'
  AND rp.id IS NULL;

-- Rol: Executer
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name IN (
    'clone_tramite',
    'search_tramite',
    'read_final_tramite',
    'section_asigna_gestor',
    'section_pago_derechos',
    'section_linea_captura',
    'section_documentos_pago',
    'header_buttons',
    'create_tramite',
    'editar_tramite_asociado',
    'editar_tramite_principal',
    'listar_tramites_concluidos',
    'write_tramite_asigna_gestor',
    'write_tramite_datos_tramite',
    'write_tramite_pago_derechos',
    'monitoreo_bitacora_search',
    'menu_notifications',
    'quick_action_documentos_add',
    'quick_action_documentos_edit',
    'quick_action_documentos_delete',
    'quick_action_bitacora_add',
    'quick_action_bitacora_edit',
    'quick_action_bitacora_delete',
    'quick_action_pagos_derecho_add',
    'quick_action_pagos_derecho_edit',
    'quick_action_pagos_derecho_delete'
)
LEFT JOIN us_role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
WHERE r.role_name = 'Executer'
  AND rp.id IS NULL;

-- Rol: Closer
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name IN (
    'listar_final_tramite',
    'editar_final_tramite',
    'search_tramite',
    'export_final_tramite',
    'print_final_tramite',
    'read_final_tramite',
    'listar_settings',
    'menu_proceso_final',
    'important_concluir_tramite',
    'important_cancelar_tramite',
    'section_final_costos',
    'listar_concluidos_tramite',
    'listar_tramites_concluidos',
    'menu_dashboard_admin',
    'list_cobro_cliente',
    'can_upload_dropzone_cobro_cliente',
    'menu_notifications',
    'quick_action_documentos_add',
    'quick_action_documentos_edit',
    'quick_action_documentos_delete',
    'quick_action_bitacora_add',
    'quick_action_bitacora_edit',
    'quick_action_bitacora_delete',
    'quick_action_pagos_derecho_add',
    'quick_action_pagos_derecho_edit',
    'quick_action_pagos_derecho_delete',
    'quick_action_pago_gestor_add',
    'quick_action_pago_gestor_edit',
    'quick_action_pago_gestor_delete',
    'quick_action_evidencias_finales_add',
    'quick_action_evidencias_finales_edit',
    'quick_action_evidencias_finales_delete',
    'quick_action_cobros_cliente_add',
    'quick_action_cobros_cliente_edit',
    'quick_action_cobros_cliente_delete',
    'override_puede_editar_modulo',
    'upload_cobro_cliente'
)
LEFT JOIN us_role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
WHERE r.role_name = 'Closer'
  AND rp.id IS NULL;

-- Rol: Starter
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name IN (
    'listar_tramite',
    'read_tramite',
    'clone_tramite',
    'search_tramite',
    'listar_settings',
    'section_final_costos',
    'header_buttons',
    'listar_concluidos_tramite',
    'create_tramite',
    'quick_action_bitacora',
    'quick_action_documentos',
    'quick_action_evidencias_finales',
    'menu_notifications',
    'quick_action_documentos_edit',
    'quick_action_bitacora_edit',
    'quick_action_evidencias_finales_add',
    'quick_action_evidencias_finales_edit'
)
LEFT JOIN us_role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
WHERE r.role_name = 'Starter'
  AND rp.id IS NULL;

-- Rol: Authorizer Editor
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name IN (
    'editar_tramite',
    'listar_tramite',
    'listar_final_tramite',
    'export_tramite',
    'print_tramite',
    'read_tramite',
    'clone_tramite',
    'search_tramite',
    'print_final_tramite',
    'read_final_tramite',
    'listar_settings',
    'menu_tramites',
    'menu_proceso_final',
    'important_pasar_a_pagos',
    'section_inicial_datos',
    'section_pago_derechos',
    'section_linea_captura',
    'section_documentos_pago',
    'header_buttons',
    'editar_pago_gestor',
    'section_pago_gestor',
    'listar_concluidos_tramite',
    'menu_tramites_tenencias',
    'create_tramite',
    'editar_tramite_asociado',
    'editar_tramite_principal',
    'listar_tramites_concluidos',
    'can_upload_dropzone_pago_derechos',
    'can_upload_dropzone_pago_gestor',
    'write_tramite_asigna_gestor',
    'write_tramite_datos_tramite',
    'write_tramite_pago_derechos',
    'menu_monitoreo_actividad',
    'monitoreo_auditoria_tramite',
    'monitoreo_bitacora_search',
    'quick_action_pagos_derecho',
    'menu_notifications',
    'quick_action_documentos_add',
    'quick_action_documentos_edit',
    'quick_action_documentos_delete',
    'quick_action_bitacora_add',
    'quick_action_bitacora_edit',
    'quick_action_bitacora_delete',
    'quick_action_pagos_derecho_add',
    'quick_action_pagos_derecho_edit',
    'quick_action_pagos_derecho_delete',
    'quick_action_pago_gestor_add',
    'quick_action_pago_gestor_edit',
    'quick_action_pago_gestor_delete',
    'quick_action_evidencias_finales_add',
    'quick_action_evidencias_finales_edit',
    'quick_action_evidencias_finales_delete',
    'listar_tramites_cancelado',
    'read_tramite_cancelado',
    'export_tramite_cancelado',
    'print_tramite_cancelado',
    'clone_tramite_cancelado'
)
LEFT JOIN us_role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
WHERE r.role_name = 'Authorizer Editor'
  AND rp.id IS NULL;

-- Rol: Authorizer Simple
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name IN (
    'editar_tramite',
    'listar_tramite',
    'listar_final_tramite',
    'export_tramite',
    'print_tramite',
    'read_tramite',
    'clone_tramite',
    'search_tramite',
    'print_final_tramite',
    'read_final_tramite',
    'listar_settings',
    'menu_tramites',
    'menu_proceso_final',
    'tramite_view_gestor',
    'section_inicial_datos',
    'section_asigna_gestor',
    'section_pago_derechos',
    'section_linea_captura',
    'section_documentos_pago',
    'header_buttons',
    'editar_pago_gestor',
    'section_pago_gestor',
    'listar_concluidos_tramite',
    'menu_tramites_tenencias',
    'create_tramite',
    'editar_tramite_asociado',
    'editar_tramite_principal',
    'listar_tramites_concluidos',
    'can_upload_dropzone_pago_derechos',
    'write_tramite_asigna_gestor',
    'write_tramite_datos_tramite',
    'write_tramite_pago_derechos',
    'menu_monitoreo_actividad',
    'monitoreo_bitacora_search',
    'quick_action_pagos_derecho',
    'menu_notifications',
    'quick_action_documentos_add',
    'quick_action_documentos_edit',
    'quick_action_documentos_delete',
    'quick_action_bitacora_add',
    'quick_action_bitacora_edit',
    'quick_action_bitacora_delete',
    'quick_action_pagos_derecho_add',
    'quick_action_pagos_derecho_edit',
    'quick_action_pagos_derecho_delete',
    'quick_action_pago_gestor_add',
    'quick_action_pago_gestor_edit',
    'quick_action_pago_gestor_delete',
    'quick_action_evidencias_finales_add',
    'quick_action_evidencias_finales_edit',
    'quick_action_evidencias_finales_delete',
    'listar_tramites_cancelado',
    'read_tramite_cancelado',
    'export_tramite_cancelado',
    'print_tramite_cancelado'
)
LEFT JOIN us_role_permissions rp ON rp.role_id = r.id AND rp.permission_id = p.id
WHERE r.role_name = 'Authorizer Simple'
  AND rp.id IS NULL;

COMMIT;
-- Permisos faltantes en us_permissions (generado automáticamente)
-- Revísalo antes de ejecutar en PROD.

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'menu_notifications', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'menu_notifications');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'sincronizar_tramites', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'sincronizar_tramites');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'wizard_list_only_own', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'wizard_list_only_own');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'search_tramite', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'search_tramite');


-- -------------------- Trámites: Cancelados --------------------

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'editar_tramite_cancelado', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'editar_tramite_cancelado');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'read_tramite_cancelado', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'read_tramite_cancelado');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'delete_tramite_cancelado', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'delete_tramite_cancelado');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'export_tramite_cancelado', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'export_tramite_cancelado');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'print_tramite_cancelado', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'print_tramite_cancelado');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'clone_tramite_cancelado', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'clone_tramite_cancelado');


-- -------------------- Acciones Rápidas (CRUD fino) --------------------

-- Base: visibilidad de botones/listón
INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_documentos', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_documentos');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_bitacora', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_bitacora');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pagos_derecho', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pagos_derecho');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pago_gestor', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pago_gestor');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_evidencias_finales', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_evidencias_finales');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_cobros_cliente', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_cobros_cliente');

-- Documentos
INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_documentos_add', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_documentos_add');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_documentos_edit', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_documentos_edit');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_documentos_delete', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_documentos_delete');

-- Bitácora
INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_bitacora_add', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_bitacora_add');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_bitacora_edit', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_bitacora_edit');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_bitacora_delete', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_bitacora_delete');

-- Pagos Derecho
INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pagos_derecho_add', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pagos_derecho_add');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pagos_derecho_edit', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pagos_derecho_edit');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pagos_derecho_delete', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pagos_derecho_delete');

-- Pago Gestor
INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pago_gestor_add', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pago_gestor_add');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pago_gestor_edit', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pago_gestor_edit');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_pago_gestor_delete', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_pago_gestor_delete');

-- Evidencias Finales
INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_evidencias_finales_add', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_evidencias_finales_add');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_evidencias_finales_edit', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_evidencias_finales_edit');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_evidencias_finales_delete', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_evidencias_finales_delete');

-- Cobros Cliente
INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_cobros_cliente_add', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_cobros_cliente_add');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_cobros_cliente_edit', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_cobros_cliente_edit');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'quick_action_cobros_cliente_delete', NULL, NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'quick_action_cobros_cliente_delete');


-- -------------------- Filtros / Overrides (ACL) --------------------

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'mios_filter_status_11', 'Filtro Mis Trámites: status 11', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'mios_filter_status_11');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'mios_filter_status_22', 'Filtro Mis Trámites: status 22', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'mios_filter_status_22');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'tramitesn_filter_owner_only', 'Wizard nuevo: listar solo trámites propios', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'tramitesn_filter_owner_only');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'override_tramite_approved_lock', 'Override: permitir editar pasos 1-3 aunque esté aprobado (wizard nuevo)', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'override_tramite_approved_lock');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'override_puede_editar_modulo', 'Override: bypass de puede_editar_modulo() por permisos', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'override_puede_editar_modulo');

INSERT INTO us_permissions (permission_name, description, created_at, updated_at)
SELECT 'debug_perm_audit_tags', 'Debug: mostrar etiquetas perm_audit_tag en UI', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM us_permissions WHERE permission_name = 'debug_perm_audit_tags');


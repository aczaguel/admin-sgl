-- Asignación idempotente de permisos CRUD finos de "Acciones Rápidas" por rol.
--
-- Requisitos previos:
-- 1) Crear permisos en `us_permissions` (correr `missing_permissions.sql`).
-- 2) (Opcional) Activar permisos usados en código (`activate_used_permissions.sql`) si tu `us_permissions` maneja `status`.
--
-- Nota: estos permisos gobiernan el habilitado de acciones (Add/Edit/Delete) dentro de GroceryCRUD
-- de cada modal de Acciones Rápidas. El backend además exige los permisos funcionales (editar_tramite,
-- write_tramite_*, can_upload_dropzone_*, section_*, etc.).

-- Helper macro mental: para cada permiso P, se inserta en us_role_permissions si no existe.

-- -------------------- Executer --------------------

-- Executer → Documentos (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_add'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_edit'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_delete'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Executer → Bitácora (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_add'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_edit'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_delete'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Executer → Pagos Derecho (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pagos_derecho_add'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pagos_derecho_edit'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pagos_derecho_delete'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);


-- -------------------- Closer --------------------

-- Closer → Documentos (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_add'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_edit'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_delete'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Closer → Bitácora (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_add'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_edit'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_delete'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Closer → Pagos Derecho (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pagos_derecho_add'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pagos_derecho_edit'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pagos_derecho_delete'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Closer → Pago Gestor (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_add'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_edit'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_delete'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Closer → Evidencias Finales (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_add'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_edit'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_delete'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Closer → Cobros Cliente (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_cobros_cliente_add'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_cobros_cliente_edit'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_cobros_cliente_delete'
WHERE r.role_name = 'Closer'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);


-- -------------------- Authorizers --------------------

-- Authorizer Editor → Documentos (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_add'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_edit'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_delete'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Authorizer Editor → Bitácora (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_add'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_edit'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_delete'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Authorizer Editor → Pago Gestor (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_add'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_edit'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_delete'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Authorizer Editor → Evidencias Finales (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_add'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_edit'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_delete'
WHERE r.role_name = 'Authorizer Editor'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);


-- Authorizer Simple → Documentos (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_add'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_edit'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_delete'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Authorizer Simple → Bitácora (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_add'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_edit'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_delete'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Authorizer Simple → Pago Gestor (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_add'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_edit'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_pago_gestor_delete'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Authorizer Simple → Evidencias Finales (Add/Edit/Delete)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_add'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_edit'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_evidencias_finales_delete'
WHERE r.role_name = 'Authorizer Simple'
  AND NOT EXISTS (SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Asignación idempotente de permisos CRUD finos de "Acciones Rápidas" al rol Starter.
--
-- Requisitos previos:
-- 1) Crear permisos en `us_permissions` (correr `missing_permissions.sql`).
-- 2) (Opcional) Activar permisos usados en código (`activate_used_permissions.sql`) si tu `us_permissions` maneja `status`.
--
-- Nota:
-- - `quick_action_documentos` / `quick_action_bitacora` solo controlan visibilidad del modal.
-- - Para habilitar el botón Agregar dentro de GroceryCRUD se requiere el permiso fino *_add.

-- Starter → Documentos (Agregar)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_documentos_add'
WHERE r.role_name = 'Starter'
  AND NOT EXISTS (
    SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Starter → Bitácora (Agregar)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'quick_action_bitacora_add'
WHERE r.role_name = 'Starter'
  AND NOT EXISTS (
    SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Asignación idempotente de permisos para el filtro "Mis Trámites".
--
-- Requisitos previos:
-- 1) Crear permisos en `us_permissions` (correr `missing_permissions.sql`).
-- 2) (Opcional) Activar permisos usados en código (`activate_used_permissions.sql`) si tu `us_permissions` maneja `status`.

-- Starter → ver "Mis Trámites" filtrado a status 11 (legacy)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'mios_filter_status_11'
WHERE r.role_name = 'Starter'
  AND NOT EXISTS (
    SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Executer → ver "Mis Trámites" filtrado a status 22 (legacy)
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'mios_filter_status_22'
WHERE r.role_name = 'Executer'
  AND NOT EXISTS (
    SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

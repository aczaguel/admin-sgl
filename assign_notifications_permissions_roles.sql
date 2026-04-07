-- Asignación idempotente de permisos para Notificaciones.
--
-- Requisitos previos:
-- 1) Crear permisos en `us_permissions` (correr `missing_permissions.sql`).
-- 2) (Opcional) Activar permisos usados en código (`activate_used_permissions.sql`) si tu `us_permissions` maneja `status`.

-- Cliente → Notificaciones
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'menu_notifications'
WHERE r.role_name = 'Cliente'
  AND NOT EXISTS (
    SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Admin → Notificaciones
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'menu_notifications'
WHERE r.role_name = 'Admin'
  AND NOT EXISTS (
    SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

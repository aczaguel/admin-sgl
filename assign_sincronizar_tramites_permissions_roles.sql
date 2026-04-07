-- Asignación idempotente de permisos de mantenimiento.
--
-- Requisitos previos:
-- 1) Crear permisos en `us_permissions` (correr `missing_permissions.sql`).
-- 2) (Opcional) Activar permisos usados en código (`activate_used_permissions.sql`) si tu `us_permissions` maneja `status`.

-- Admin → Permitir sincronización de trámites sin asociados
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p ON p.permission_name = 'sincronizar_tramites'
WHERE r.role_name = 'Admin'
  AND NOT EXISTS (
    SELECT 1 FROM us_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

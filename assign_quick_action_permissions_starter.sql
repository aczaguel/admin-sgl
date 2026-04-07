-- Asignación idempotente de permisos de "Acciones Rápidas" al rol Starter.
--
-- Requisitos previos:
-- 1) Crear permisos en `us_permissions` (ej. correr `missing_permissions.sql`).
-- 2) (Opcional) Activar permisos usados en código (ej. `activate_used_permissions.sql`) si tu tabla `us_permissions` tiene columna `status`.

-- Starter → Documentos
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p
  ON p.permission_name = 'quick_action_documentos'
WHERE r.role_name = 'Starter'
  AND NOT EXISTS (
    SELECT 1
    FROM us_role_permissions rp
    WHERE rp.role_id = r.id
      AND rp.permission_id = p.id
  );

-- Starter → Bitácora
INSERT INTO us_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM us_roles r
JOIN us_permissions p
  ON p.permission_name = 'quick_action_bitacora'
WHERE r.role_name = 'Starter'
  AND NOT EXISTS (
    SELECT 1
    FROM us_role_permissions rp
    WHERE rp.role_id = r.id
      AND rp.permission_id = p.id
  );

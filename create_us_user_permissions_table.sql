-- Tabla de overrides de permisos por usuario (extras y denegaciones)
-- - granted=1: permitir explícitamente (aunque el rol no lo tenga)
-- - granted=0: denegar explícitamente (aunque el rol lo tenga)
--
-- Nota: se mantiene consistencia porque no se altera us_role_permissions.

CREATE TABLE IF NOT EXISTS us_user_permissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  permission_id INT NOT NULL,
  granted TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_permission (user_id, permission_id),
  KEY idx_user (user_id),
  KEY idx_permission (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

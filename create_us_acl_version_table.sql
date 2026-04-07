-- Tabla para versionar/invalidatear cache de ACL en sesión.
-- La idea: en cada request se lee `version` (query muy barato). Si cambió vs sesión, se recalculan roles/permisos.

CREATE TABLE IF NOT EXISTS `us_acl_version` (
  `id` TINYINT UNSIGNED NOT NULL,
  `version` BIGINT UNSIGNED NOT NULL DEFAULT 1,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `us_acl_version` (`id`, `version`, `updated_at`)
SELECT 1, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `us_acl_version` WHERE `id` = 1);

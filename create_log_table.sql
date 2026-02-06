-- Tabla para registrar correcciones en trámites
CREATE TABLE IF NOT EXISTS `tramite_correccion_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tramite_id` int(11) NOT NULL,
  `folio` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `cambios` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tramite_id` (`tramite_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

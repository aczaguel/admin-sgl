-- ============================================
-- MÓDULO DE NOTIFICACIONES SISTEMA
-- ============================================

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Usuario que recibirá la notificación',
  `tramite_id` int(11) DEFAULT NULL COMMENT 'Trámite relacionado',
  `type` enum('tramite_creado','tramite_actualizado','gestor_asignado','pago_gestor','factura_generada','factura_cobrada','comentario','alerta') NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Título de la notificación',
  `message` text NOT NULL COMMENT 'Mensaje descriptivo',
  `icon` varchar(50) DEFAULT 'fa-bell' COMMENT 'Icono Font Awesome',
  `color` varchar(20) DEFAULT 'primary' COMMENT 'Color: primary, success, warning, danger, info',
  `url` varchar(500) DEFAULT NULL COMMENT 'URL de redirección',
  `is_read` tinyint(1) DEFAULT 0 COMMENT '0=No leída, 1=Leída',
  `read_at` datetime DEFAULT NULL COMMENT 'Fecha de lectura',
  `created_by` int(11) DEFAULT NULL COMMENT 'Usuario que generó la acción',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_tramite_id` (`tramite_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración de notificaciones por usuario
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notify_tramite_creado` tinyint(1) DEFAULT 1,
  `notify_tramite_actualizado` tinyint(1) DEFAULT 1,
  `notify_gestor_asignado` tinyint(1) DEFAULT 1,
  `notify_pago_gestor` tinyint(1) DEFAULT 1,
  `notify_factura_generada` tinyint(1) DEFAULT 1,
  `notify_factura_cobrada` tinyint(1) DEFAULT 1,
  `notify_comentarios` tinyint(1) DEFAULT 1,
  `notify_alertas` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices compuestos para optimización
CREATE INDEX idx_user_unread ON notifications(user_id, is_read, created_at DESC);
CREATE INDEX idx_tramite_type ON notifications(tramite_id, type);

-- Vista para consultas rápidas de notificaciones
CREATE OR REPLACE VIEW v_notifications_summary AS
SELECT 
    n.id,
    n.user_id,
    n.tramite_id,
    n.type,
    n.title,
    n.message,
    n.icon,
    n.color,
    n.url,
    n.is_read,
    n.read_at,
    n.created_at,
    CONCAT(u.firstname, ' ', u.lastname) as created_by_name,
    t.folio_tramite,
    t.tra_status_id,
    ts.tra_status as tramite_status
FROM notifications n
LEFT JOIN users u ON n.created_by = u.id
LEFT JOIN tramite t ON n.tramite_id = t.id
LEFT JOIN tra_status ts ON t.tra_status_id = ts.id
ORDER BY n.created_at DESC;

-- Datos iniciales: Configuración por defecto para usuarios existentes
INSERT INTO notification_settings (user_id)
SELECT id FROM users 
WHERE id NOT IN (SELECT user_id FROM notification_settings);

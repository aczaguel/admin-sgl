-- ============================================================================
-- SISTEMA DE AUDITORÍA COMPLETO PARA TRÁMITES
-- ============================================================================
-- Autor: GitHub Copilot
-- Fecha: 2026-02-03
-- Descripción: Sistema de logging detallado para trámites con timeline completo
-- ============================================================================

-- Tabla mejorada de auditoría de trámites
CREATE TABLE IF NOT EXISTS `tramite_audit_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tramite_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del trámite',
  `folio` VARCHAR(100) DEFAULT NULL COMMENT 'Folio del trámite para referencia rápida',
  
  -- Usuario que realizó el cambio
  `user_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'ID del usuario',
  `username` VARCHAR(150) DEFAULT NULL COMMENT 'Nombre completo del usuario',
  `user_email` VARCHAR(150) DEFAULT NULL COMMENT 'Email del usuario',
  
  -- Información del cambio
  `action` ENUM('insert', 'update', 'delete', 'upload', 'download', 'status_change', 'assignment', 'other') NOT NULL DEFAULT 'update' COMMENT 'Tipo de acción',
  `entity_type` VARCHAR(50) NOT NULL DEFAULT 'tramite' COMMENT 'Tabla afectada: tramite, tra_doc_status, tra_evidencias, etc.',
  `entity_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'ID del registro en la tabla afectada',
  
  -- Detalles del cambio
  `field_name` VARCHAR(100) DEFAULT NULL COMMENT 'Campo específico modificado',
  `old_value` TEXT DEFAULT NULL COMMENT 'Valor anterior',
  `new_value` TEXT DEFAULT NULL COMMENT 'Valor nuevo',
  `description` TEXT DEFAULT NULL COMMENT 'Descripción legible del cambio',
  
  -- Información adicional
  `metadata` JSON DEFAULT NULL COMMENT 'Datos adicionales (archivos, IPs, etc.)',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP del usuario',
  `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Navegador/dispositivo',
  
  -- Timestamps
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha del cambio',
  
  PRIMARY KEY (`id`),
  KEY `idx_tramite_id` (`tramite_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoría completa de cambios en trámites';

-- Agregar columnas de auditoría a la tabla tramite
ALTER TABLE `tramite` 
ADD COLUMN IF NOT EXISTS `last_modified_by` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Último usuario que modificó' AFTER `user_id`,
ADD COLUMN IF NOT EXISTS `last_modified_at` DATETIME DEFAULT NULL COMMENT 'Fecha de última modificación' AFTER `last_modified_by`,
ADD COLUMN IF NOT EXISTS `modification_count` INT(11) UNSIGNED DEFAULT 0 COMMENT 'Número de modificaciones' AFTER `last_modified_at`;

-- Índices para mejor performance
ALTER TABLE `tramite` 
ADD KEY IF NOT EXISTS `idx_last_modified` (`last_modified_by`, `last_modified_at`);

-- Vista para obtener últimos cambios por trámite
CREATE OR REPLACE VIEW `v_tramite_last_changes` AS
SELECT 
    t.id as tramite_id,
    t.folio,
    t.last_modified_by,
    t.last_modified_at,
    t.modification_count,
    CONCAT(u.firstname, ' ', IFNULL(u.midname, ''), ' ', u.lastname) as last_user_name,
    (SELECT COUNT(*) FROM tramite_audit_log WHERE tramite_id = t.id) as total_changes,
    (SELECT description FROM tramite_audit_log WHERE tramite_id = t.id ORDER BY created_at DESC LIMIT 1) as last_change_description
FROM tramite t
LEFT JOIN users u ON t.last_modified_by = u.id;

-- Vista para timeline de auditoría
CREATE OR REPLACE VIEW `v_tramite_audit_timeline` AS
SELECT 
    tal.id,
    tal.tramite_id,
    tal.folio,
    tal.action,
    tal.entity_type,
    tal.description,
    tal.username,
    tal.created_at,
    tal.field_name,
    tal.old_value,
    tal.new_value,
    CASE 
        WHEN tal.action = 'insert' THEN 'success'
        WHEN tal.action = 'update' THEN 'info'
        WHEN tal.action = 'delete' THEN 'danger'
        WHEN tal.action = 'upload' THEN 'primary'
        WHEN tal.action = 'status_change' THEN 'warning'
        ELSE 'secondary'
    END as badge_color,
    CASE 
        WHEN tal.action = 'insert' THEN 'fa-plus-circle'
        WHEN tal.action = 'update' THEN 'fa-edit'
        WHEN tal.action = 'delete' THEN 'fa-trash'
        WHEN tal.action = 'upload' THEN 'fa-cloud-upload'
        WHEN tal.action = 'status_change' THEN 'fa-exchange-alt'
        ELSE 'fa-info-circle'
    END as icon_class
FROM tramite_audit_log tal
ORDER BY tal.created_at DESC;

-- Procedimiento almacenado para registrar cambios
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS `sp_log_tramite_change`(
    IN p_tramite_id INT,
    IN p_user_id INT,
    IN p_action VARCHAR(20),
    IN p_entity_type VARCHAR(50),
    IN p_description TEXT,
    IN p_field_name VARCHAR(100),
    IN p_old_value TEXT,
    IN p_new_value TEXT
)
BEGIN
    DECLARE v_folio VARCHAR(100);
    DECLARE v_username VARCHAR(150);
    
    -- Obtener folio
    SELECT folio INTO v_folio FROM tramite WHERE id = p_tramite_id LIMIT 1;
    
    -- Obtener username
    SELECT CONCAT(firstname, ' ', IFNULL(midname, ''), ' ', lastname) 
    INTO v_username 
    FROM users 
    WHERE id = p_user_id 
    LIMIT 1;
    
    -- Insertar en log
    INSERT INTO tramite_audit_log (
        tramite_id, folio, user_id, username, action, entity_type,
        description, field_name, old_value, new_value, created_at
    ) VALUES (
        p_tramite_id, v_folio, p_user_id, v_username, p_action, p_entity_type,
        p_description, p_field_name, p_old_value, p_new_value, NOW()
    );
    
    -- Actualizar último modificador en tramite
    UPDATE tramite 
    SET last_modified_by = p_user_id,
        last_modified_at = NOW(),
        modification_count = modification_count + 1
    WHERE id = p_tramite_id;
    
END$$

DELIMITER ;

-- ============================================================================
-- EJEMPLOS DE USO
-- ============================================================================

-- Ejemplo 1: Registrar cambio de estatus
-- CALL sp_log_tramite_change(
--     7669,                                    -- tramite_id
--     4,                                       -- user_id
--     'status_change',                         -- action
--     'tramite',                               -- entity_type
--     'Cambio de estatus a En Proceso',       -- description
--     'tra_status_id',                         -- field_name
--     'Pendiente',                             -- old_value
--     'En Proceso',                            -- new_value
-- );

-- Ejemplo 2: Registrar subida de archivo
-- CALL sp_log_tramite_change(
--     7669,
--     4,
--     'upload',
--     'tra_evidencias',
--     'Subida de evidencia: INE_Frontal.jpg',
--     'archivo',
--     NULL,
--     'INE_Frontal.jpg'
-- );

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================

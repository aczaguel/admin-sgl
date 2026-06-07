-- Indices recomendados para mejorar Bitacora Search y timeline por tramite/folio.
-- Script idempotente para MySQL/MariaDB: solo crea el indice si no existe.

SET @schema_name = DATABASE();

SET @sql = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = @schema_name
          AND table_name = 'bitacora'
          AND index_name = 'idx_bitacora_tramite_created'
    ),
    'SELECT ''idx_bitacora_tramite_created ya existe'' AS message',
    'ALTER TABLE bitacora ADD INDEX idx_bitacora_tramite_created (tramite_id, created_at)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF (
    EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = @schema_name
          AND table_name = 'bitacora'
          AND index_name = 'idx_bitacora_folio_created'
    ),
    'SELECT ''idx_bitacora_folio_created ya existe'' AS message',
    'ALTER TABLE bitacora ADD INDEX idx_bitacora_folio_created (folio_tramite, created_at)'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SHOW INDEX FROM bitacora;

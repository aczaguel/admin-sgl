-- ============================================================================
-- DASHBOARD ADMINISTRATIVO - AJUSTES OPCIONALES DE BASE DE DATOS
-- Sistema SGL - Gestión de Trámites
-- ============================================================================

-- NOTA: Estos ajustes son OPCIONALES y solo mejoran el rendimiento
-- El dashboard funciona perfectamente sin ellos

-- ============================================================================
-- ÍNDICES PARA MEJORAR RENDIMIENTO DE CONSULTAS
-- ============================================================================

-- Índices en la tabla tramite
-- Estos mejoran significativamente el rendimiento de las queries del dashboard

-- Índice para búsquedas por estado de trámite
CREATE INDEX idx_tramite_status ON tramite(tra_status_id);

-- Índice para búsquedas por estado de cobro
CREATE INDEX idx_tramite_cobro_status ON tramite(cobro_status_id);

-- Índice para búsquedas por fechas
CREATE INDEX idx_tramite_created_at ON tramite(created_at);
CREATE INDEX idx_tramite_started_at ON tramite(started_at);
CREATE INDEX idx_tramite_finished_at ON tramite(finished_at);

-- Índice para búsquedas por usuario (ejecutivo)
CREATE INDEX idx_tramite_user ON tramite(user_id);

-- Índice para búsquedas por gestor
CREATE INDEX idx_tramite_gestor ON tramite(gestor_id);

-- Índice para búsquedas por empresa gestora
CREATE INDEX idx_tramite_empresa_gestora ON tramite(empresa_gestora_id);

-- Índice compuesto para mejorar queries de alertas
CREATE INDEX idx_tramite_status_cobro ON tramite(tra_status_id, cobro_status_id);

-- Índice compuesto para mejorar queries de fechas y estados
CREATE INDEX idx_tramite_dates_status ON tramite(created_at, tra_status_id, cobro_status_id);

-- ============================================================================
-- VISTAS MATERIALIZADAS (OPCIONAL - Solo si MySQL lo soporta)
-- ============================================================================

-- Si tu versión de MySQL soporta vistas materializadas, estas pueden mejorar
-- aún más el rendimiento. De lo contrario, las vistas normales también ayudan.

-- Vista para métricas rápidas del día
CREATE OR REPLACE VIEW v_metricas_hoy AS
SELECT 
    COUNT(*) as total_ingresados,
    SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
    SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
    SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado_hoy
FROM tramite
WHERE DATE(created_at) = CURDATE();

-- Vista para trámites activos
CREATE OR REPLACE VIEW v_tramites_activos AS
SELECT 
    t.*,
    ts.tra_status,
    cs.cobro_status,
    cd.razon_social AS cliente,
    tt.tipo_tramite,
    CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo
FROM tramite t
LEFT JOIN tra_status ts ON t.tra_status_id = ts.id
LEFT JOIN cobro_statuses cs ON t.cobro_status_id = cs.id
LEFT JOIN cli_directo cd ON t.cli_directo_id = cd.id
LEFT JOIN tra_tipos tt ON t.tra_tipos_id = tt.id
LEFT JOIN users u ON t.user_id = u.id
WHERE t.tra_status_id NOT IN (20, 21);

-- Vista para cuentas por cobrar
CREATE OR REPLACE VIEW v_cuentas_por_cobrar AS
SELECT 
    t.id,
    t.folio,
    t.contrato,
    t.numero_factura,
    t.numero_refactura,
    t.finished_at,
    t.costo_total,
    DATEDIFF(CURDATE(), t.finished_at) as dias_vencidos,
    cd.razon_social AS cliente,
    CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo
FROM tramite t
LEFT JOIN cli_directo cd ON t.cli_directo_id = cd.id
LEFT JOIN users u ON t.user_id = u.id
WHERE (t.numero_factura IS NOT NULL AND t.numero_factura != '' 
    OR t.numero_refactura IS NOT NULL AND t.numero_refactura != '')
AND t.cobro_status_id = 22
AND t.finished_at IS NOT NULL;

-- ============================================================================
-- PROCEDIMIENTOS ALMACENADOS (OPCIONAL)
-- ============================================================================

-- Procedimiento para obtener métricas por período
DELIMITER //

CREATE PROCEDURE sp_metricas_periodo(IN periodo VARCHAR(20))
BEGIN
    DECLARE fecha_inicio DATE;
    DECLARE fecha_fin DATE;
    
    CASE periodo
        WHEN 'hoy' THEN
            SET fecha_inicio = CURDATE();
            SET fecha_fin = CURDATE();
        WHEN 'semana' THEN
            SET fecha_inicio = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);
            SET fecha_fin = CURDATE();
        WHEN 'mes' THEN
            SET fecha_inicio = DATE_FORMAT(CURDATE(), '%Y-%m-01');
            SET fecha_fin = CURDATE();
        WHEN 'anio' THEN
            SET fecha_inicio = DATE_FORMAT(CURDATE(), '%Y-01-01');
            SET fecha_fin = CURDATE();
        ELSE
            SET fecha_inicio = '2000-01-01';
            SET fecha_fin = CURDATE();
    END CASE;
    
    SELECT 
        COUNT(*) as total_ingresados,
        SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos,
        SUM(CASE WHEN cobro_status_id = 23 THEN 1 ELSE 0 END) as total_cobrados,
        SUM(CASE WHEN cobro_status_id = 23 THEN costo_total ELSE 0 END) as monto_cobrado
    FROM tramite
    WHERE created_at >= fecha_inicio AND created_at <= fecha_fin;
END //

DELIMITER ;

-- ============================================================================
-- TRIGGERS PARA AUDITORÍA (OPCIONAL)
-- ============================================================================

-- Trigger para registrar cambios importantes en trámites
DELIMITER //

CREATE TRIGGER tr_tramite_cambio_estado
AFTER UPDATE ON tramite
FOR EACH ROW
BEGIN
    IF OLD.tra_status_id != NEW.tra_status_id THEN
        INSERT INTO tramite_auditoria (
            tramite_id,
            campo_modificado,
            valor_anterior,
            valor_nuevo,
            fecha_modificacion,
            usuario_modificacion
        ) VALUES (
            NEW.id,
            'tra_status_id',
            OLD.tra_status_id,
            NEW.tra_status_id,
            NOW(),
            NEW.user_id
        );
    END IF;
    
    IF OLD.cobro_status_id != NEW.cobro_status_id THEN
        INSERT INTO tramite_auditoria (
            tramite_id,
            campo_modificado,
            valor_anterior,
            valor_nuevo,
            fecha_modificacion,
            usuario_modificacion
        ) VALUES (
            NEW.id,
            'cobro_status_id',
            OLD.cobro_status_id,
            NEW.cobro_status_id,
            NOW(),
            NEW.user_id
        );
    END IF;
END //

DELIMITER ;

-- ============================================================================
-- TABLA DE AUDITORÍA (Solo si se quiere implementar el trigger anterior)
-- ============================================================================

CREATE TABLE IF NOT EXISTS tramite_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tramite_id INT NOT NULL,
    campo_modificado VARCHAR(100),
    valor_anterior VARCHAR(255),
    valor_nuevo VARCHAR(255),
    fecha_modificacion DATETIME,
    usuario_modificacion INT,
    FOREIGN KEY (tramite_id) REFERENCES tramite(id),
    FOREIGN KEY (usuario_modificacion) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índice para búsquedas rápidas en auditoría
CREATE INDEX idx_auditoria_tramite ON tramite_auditoria(tramite_id);
CREATE INDEX idx_auditoria_fecha ON tramite_auditoria(fecha_modificacion);

-- ============================================================================
-- TABLA DE CONFIGURACIÓN DEL DASHBOARD (OPCIONAL)
-- ============================================================================

CREATE TABLE IF NOT EXISTS dashboard_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Valores por defecto de configuración
INSERT INTO dashboard_config (config_key, config_value, descripcion) VALUES
('dias_alerta_retrasado', '30', 'Días para considerar un trámite como retrasado'),
('dias_alerta_cobro', '15', 'Días para alertar sobre facturas sin cobrar'),
('dias_alerta_estancado', '7', 'Días sin movimiento para considerar estancado'),
('auto_refresh_interval', '300000', 'Intervalo de auto-refresh en milisegundos'),
('max_tramites_alerta', '50', 'Máximo de trámites a mostrar en alertas'),
('email_notificaciones', 'admin@sgl.com', 'Email para notificaciones de alertas')
ON DUPLICATE KEY UPDATE config_value=VALUES(config_value);

-- ============================================================================
-- CONSULTAS DE MANTENIMIENTO
-- ============================================================================

-- Analizar tablas para optimizar queries
ANALYZE TABLE tramite;
ANALYZE TABLE users;
ANALYZE TABLE tra_status;
ANALYZE TABLE cobro_statuses;
ANALYZE TABLE cli_directo;
ANALYZE TABLE tra_tipos;

-- Verificar índices existentes
SHOW INDEX FROM tramite;

-- Ver estadísticas de la tabla
SHOW TABLE STATUS LIKE 'tramite';

-- ============================================================================
-- QUERIES DE PRUEBA
-- ============================================================================

-- Verificar que las queries del dashboard funcionan correctamente

-- Test 1: Métricas del día
SELECT 
    COUNT(*) as total_ingresados,
    SUM(CASE WHEN tra_status_id = 20 THEN 1 ELSE 0 END) as total_concluidos
FROM tramite
WHERE DATE(created_at) = CURDATE();

-- Test 2: Trámites retrasados
SELECT COUNT(*) as tramites_retrasados
FROM tramite
WHERE tra_status_id NOT IN (20, 21)
AND DATEDIFF(CURDATE(), COALESCE(started_at, created_at)) > 30;

-- Test 3: Pendientes de cobro
SELECT COUNT(*) as pendientes_cobro, SUM(costo_total) as monto_total
FROM tramite
WHERE tra_status_id NOT IN (20, 21)
AND (numero_factura IS NOT NULL AND numero_factura != '' 
    OR numero_refactura IS NOT NULL AND numero_refactura != '')
AND cobro_status_id = 22;

-- Test 4: Rendimiento de ejecutivos este mes
SELECT 
    CONCAT(u.firstname, ' ', u.lastname) AS ejecutivo,
    COUNT(*) as total_tramites,
    SUM(CASE WHEN t.tra_status_id = 20 THEN 1 ELSE 0 END) as concluidos
FROM tramite t
INNER JOIN users u ON t.user_id = u.id
WHERE YEAR(t.created_at) = YEAR(CURDATE()) 
AND MONTH(t.created_at) = MONTH(CURDATE())
GROUP BY u.id
ORDER BY concluidos DESC
LIMIT 5;

-- ============================================================================
-- LIMPIEZA (Usar con precaución)
-- ============================================================================

-- Para remover los índices si causan problemas (NO RECOMENDADO):
-- DROP INDEX idx_tramite_status ON tramite;
-- DROP INDEX idx_tramite_cobro_status ON tramite;
-- etc.

-- Para remover las vistas:
-- DROP VIEW IF EXISTS v_metricas_hoy;
-- DROP VIEW IF EXISTS v_tramites_activos;
-- DROP VIEW IF EXISTS v_cuentas_por_cobrar;

-- Para remover el procedimiento:
-- DROP PROCEDURE IF EXISTS sp_metricas_periodo;

-- Para remover el trigger:
-- DROP TRIGGER IF EXISTS tr_tramite_cambio_estado;

-- ============================================================================
-- NOTAS FINALES
-- ============================================================================

/*
IMPORTANTE:
1. Estos scripts son OPCIONALES
2. El dashboard funciona sin ellos
3. Solo mejoran el rendimiento en bases de datos muy grandes
4. Hacer backup antes de ejecutar cualquier script
5. Probar en ambiente de desarrollo primero
6. Los índices ocupan espacio en disco
7. Analizar el impacto en inserts/updates antes de usar todos los índices

RECOMENDACIONES:
- Empezar solo con los índices básicos (tra_status_id, created_at)
- Monitorear el rendimiento
- Agregar más índices solo si es necesario
- Usar EXPLAIN para analizar queries problemáticas
- Revisar el uso de índices con: SHOW INDEX FROM tramite
*/

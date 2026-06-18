ALTER TABLE tra_evidencias
ADD COLUMN tipo_evidencia TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 evidencia normal, 2 evidencia pago a gestor, 3 evidencia cobro a cliente';

UPDATE tra_evidencias
SET tipo_evidencia = 1
WHERE tipo_evidencia IS NULL OR tipo_evidencia = 0;

ALTER TABLE tra_evidencias
ADD INDEX idx_tra_evidencias_tramite_tipo_status (tramite_id, tipo_evidencia, status),
ADD INDEX idx_tra_evidencias_folio_tipo (folio_tramite, tipo_evidencia);
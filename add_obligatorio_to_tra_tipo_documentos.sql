ALTER TABLE tra_tipo_documentos
ADD COLUMN es_obligatorio TINYINT(1) NOT NULL DEFAULT 1 AFTER documento_id;

UPDATE tra_tipo_documentos
SET es_obligatorio = 1
WHERE es_obligatorio IS NULL;
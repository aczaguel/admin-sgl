ALTER TABLE `tramite`
ADD COLUMN `status_doctos_gestor` ENUM('en proceso', 'entregados') NOT NULL DEFAULT 'en proceso' AFTER `pago_gestor_st_id`;

UPDATE `tramite`
SET `status_doctos_gestor` = CASE
    WHEN `cobrar_cliente` = 1 THEN 'entregados'
    ELSE 'en proceso'
END;


SET SQL_SAFE_UPDATES = 0;
-- Rollback opcional:
-- ALTER TABLE `tramite` DROP COLUMN `status_doctos_gestor`;
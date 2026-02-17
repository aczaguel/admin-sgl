CREATE TABLE IF NOT EXISTS flotilla (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    cliente_id INT NOT NULL,
    archivo_origen VARCHAR(255) NULL,
    total_registros INT NOT NULL DEFAULT 0,
    total_importados INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    created_by INT NOT NULL,
    INDEX idx_flotilla_cliente (cliente_id)
);

CREATE TABLE IF NOT EXISTS flotilla_tramite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flotilla_id INT NOT NULL,
    tramite_id INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_flotilla_tramite_flotilla (flotilla_id),
    INDEX idx_flotilla_tramite_tramite (tramite_id)
);

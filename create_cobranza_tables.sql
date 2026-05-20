CREATE TABLE IF NOT EXISTS `cobranza_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cobranza_status_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cobranza_status` (`id`, `code`, `name`, `sort_order`, `is_active`) VALUES
  (1, 'abierto', 'Abierto', 10, 1),
  (2, 'en_gestion', 'En gestion', 20, 1),
  (3, 'promesa_activa', 'Promesa activa', 30, 1),
  (4, 'pago_en_revision', 'Pago en revision', 40, 1),
  (5, 'cobrado', 'Cobrado', 50, 1),
  (6, 'cerrado', 'Cerrado', 60, 1),
  (7, 'disputa', 'Disputa', 70, 1);

CREATE TABLE IF NOT EXISTS `cobranza_prioridad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cobranza_prioridad_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cobranza_prioridad` (`id`, `code`, `name`, `sort_order`, `is_active`) VALUES
  (1, 'alta', 'Alta', 10, 1),
  (2, 'media', 'Media', 20, 1),
  (3, 'baja', 'Baja', 30, 1);

CREATE TABLE IF NOT EXISTS `cobranza_tipo_gestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cobranza_tipo_gestion_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cobranza_tipo_gestion` (`id`, `code`, `name`, `sort_order`, `is_active`) VALUES
  (1, 'apertura', 'Apertura', 10, 1),
  (2, 'seguimiento', 'Seguimiento', 20, 1),
  (3, 'promesa', 'Promesa de pago', 30, 1),
  (4, 'pago', 'Pago', 40, 1),
  (5, 'incidencia', 'Incidencia', 50, 1),
  (6, 'comentario', 'Comentario interno', 60, 1);

CREATE TABLE IF NOT EXISTS `cobranza_canal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cobranza_canal_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cobranza_canal` (`id`, `code`, `name`, `sort_order`, `is_active`) VALUES
  (1, 'sistema', 'Sistema', 10, 1),
  (2, 'interno', 'Interno', 20, 1),
  (3, 'llamada', 'Llamada', 30, 1),
  (4, 'whatsapp', 'WhatsApp', 40, 1),
  (5, 'correo', 'Correo', 50, 1);

CREATE TABLE IF NOT EXISTS `cobranza_medio_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cobranza_medio_pago_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cobranza_medio_pago` (`id`, `code`, `name`, `sort_order`, `is_active`) VALUES
  (1, 'transferencia', 'Transferencia', 10, 1),
  (2, 'deposito', 'Deposito', 20, 1),
  (3, 'efectivo', 'Efectivo', 30, 1),
  (4, 'tarjeta', 'Tarjeta', 40, 1),
  (5, 'cheque', 'Cheque', 50, 1);

CREATE TABLE IF NOT EXISTS `cobranza_resultado_gestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cobranza_resultado_gestion_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cobranza_resultado_gestion` (`id`, `code`, `name`, `sort_order`, `is_active`) VALUES
  (1, 'expediente_abierto', 'Expediente abierto', 10, 1),
  (2, 'seguimiento_registrado', 'Seguimiento registrado', 20, 1),
  (3, 'contacto_exitoso', 'Contacto exitoso', 30, 1),
  (4, 'sin_contacto', 'Sin contacto', 40, 1),
  (5, 'promesa_registrada', 'Promesa registrada', 50, 1),
  (6, 'pago_reportado', 'Pago reportado', 60, 1),
  (7, 'pago_confirmado', 'Pago confirmado', 70, 1);

CREATE TABLE IF NOT EXISTS `cobranza_expediente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tramite_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `cli_directo_id` int(11) DEFAULT NULL,
  `cli_directo_ejecutivo_id` int(11) DEFAULT NULL,
  `owner_user_id` int(11) DEFAULT NULL,
  `supervisor_user_id` int(11) DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `prioridad_id` int(11) NOT NULL,
  `origen_apertura` varchar(80) NOT NULL DEFAULT 'modulo_cobranza',
  `monto_objetivo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo_actual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `moneda` varchar(10) NOT NULL DEFAULT 'MXN',
  `fecha_apertura` datetime NOT NULL,
  `fecha_ultimo_contacto` datetime DEFAULT NULL,
  `fecha_proximo_seguimiento` datetime DEFAULT NULL,
  `fecha_promesa_actual` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `motivo_cierre_id` int(11) DEFAULT NULL,
  `sla_at_first_contact_at` datetime DEFAULT NULL,
  `sla_resolve_at` datetime DEFAULT NULL,
  `is_disputa` tinyint(1) NOT NULL DEFAULT 0,
  `is_requiere_revision` tinyint(1) NOT NULL DEFAULT 0,
  `external_reference` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cobranza_expediente_tramite` (`tramite_id`),
  KEY `idx_cobranza_expediente_owner` (`owner_user_id`),
  KEY `idx_cobranza_expediente_status` (`status_id`),
  KEY `idx_cobranza_expediente_active` (`is_active`),
  KEY `idx_cobranza_expediente_followup` (`fecha_proximo_seguimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cobranza_gestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expediente_id` int(11) NOT NULL,
  `tipo_gestion_id` int(11) NOT NULL,
  `canal_id` int(11) NOT NULL,
  `resultado_id` int(11) NOT NULL,
  `fecha_gestion` datetime NOT NULL,
  `siguiente_accion` varchar(255) DEFAULT NULL,
  `fecha_proximo_seguimiento` datetime DEFAULT NULL,
  `comentarios` text NOT NULL,
  `metadata_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cobranza_gestion_expediente` (`expediente_id`),
  KEY `idx_cobranza_gestion_fecha` (`fecha_gestion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cobranza_promesa_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expediente_id` int(11) NOT NULL,
  `monto_prometido` decimal(12,2) NOT NULL,
  `fecha_promesa` datetime NOT NULL,
  `medio_pago_id` int(11) NOT NULL,
  `status_code` varchar(40) NOT NULL DEFAULT 'activa',
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cobranza_promesa_expediente` (`expediente_id`),
  KEY `idx_cobranza_promesa_fecha` (`fecha_promesa`),
  KEY `idx_cobranza_promesa_status` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cobranza_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expediente_id` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `tipo_pago` varchar(20) NOT NULL DEFAULT 'parcial',
  `fecha_pago_reportada` datetime NOT NULL,
  `fecha_pago_confirmada` datetime DEFAULT NULL,
  `medio_pago_id` int(11) NOT NULL,
  `referencia_pago` varchar(191) DEFAULT NULL,
  `status_code` varchar(40) NOT NULL DEFAULT 'reportado',
  `documento_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cobranza_pago_expediente` (`expediente_id`),
  KEY `idx_cobranza_pago_fecha_reportada` (`fecha_pago_reportada`),
  KEY `idx_cobranza_pago_status` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
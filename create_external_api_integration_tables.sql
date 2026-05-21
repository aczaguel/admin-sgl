CREATE TABLE IF NOT EXISTS `external_api_tramite_reference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_system` varchar(100) NOT NULL,
  `external_reference` varchar(191) NOT NULL,
  `tramite_id` int(11) NOT NULL,
  `last_status_payload_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_external_api_reference` (`source_system`,`external_reference`),
  UNIQUE KEY `uk_external_api_reference_tramite` (`tramite_id`),
  KEY `idx_external_api_reference_tramite` (`tramite_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `external_api_idempotency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_system` varchar(100) NOT NULL,
  `idempotency_key` varchar(191) NOT NULL,
  `request_hash` char(64) NOT NULL,
  `tramite_id` int(11) DEFAULT NULL,
  `response_status_code` smallint(6) NOT NULL,
  `response_body_json` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_external_api_idempotency` (`source_system`,`idempotency_key`),
  KEY `idx_external_api_idempotency_tramite` (`tramite_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `external_api_webhook_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_system` varchar(100) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `tramite_id` int(11) NOT NULL,
  `external_reference` varchar(191) DEFAULT NULL,
  `delivery_status` varchar(30) NOT NULL DEFAULT 'pending',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `payload_json` longtext NOT NULL,
  `last_attempt_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_external_api_webhook_status` (`delivery_status`),
  KEY `idx_external_api_webhook_tramite` (`tramite_id`),
  KEY `idx_external_api_webhook_event` (`event_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
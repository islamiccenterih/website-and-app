-- Public website Live now (not student live classes).
CREATE TABLE IF NOT EXISTS `public_live_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `source` ENUM('camera','screen') NOT NULL DEFAULT 'camera',
  `status` ENUM('live','ended') NOT NULL DEFAULT 'live',
  `started_at` DATETIME NOT NULL,
  `ended_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `public_live_status` (`status`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `public_live_peers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `peer_id` VARCHAR(40) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `role` ENUM('host','viewer') NOT NULL,
  `last_seen_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_live_session_peer` (`session_id`, `peer_id`),
  KEY `public_live_session_role` (`session_id`, `role`),
  KEY `public_live_seen` (`last_seen_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `public_live_signals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `from_peer` VARCHAR(40) NOT NULL,
  `to_peer` VARCHAR(40) NOT NULL,
  `kind` VARCHAR(16) NOT NULL,
  `payload` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `public_live_take` (`session_id`, `to_peer`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

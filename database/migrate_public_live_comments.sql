CREATE TABLE IF NOT EXISTS `public_live_comments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `peer_id` VARCHAR(40) NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `body` VARCHAR(240) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `public_live_comments_session` (`session_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

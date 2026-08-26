-- Add live class tables to an existing Islamic Center database.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `live_classes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `admin_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `notes` VARCHAR(400) DEFAULT NULL,
  `status` ENUM('scheduled','live','ended') NOT NULL DEFAULT 'scheduled',
  `started_at` DATETIME DEFAULT NULL,
  `ended_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `live_classes_course_status` (`course_id`,`status`),
  KEY `live_classes_status` (`status`),
  CONSTRAINT `live_classes_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `live_class_peers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `peer_id` VARCHAR(40) NOT NULL,
  `role` ENUM('host','student') NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `display_name` VARCHAR(120) NOT NULL,
  `audio_on` TINYINT(1) NOT NULL DEFAULT 1,
  `video_on` TINYINT(1) NOT NULL DEFAULT 1,
  `screen_on` TINYINT(1) NOT NULL DEFAULT 0,
  `is_presenter` TINYINT(1) NOT NULL DEFAULT 0,
  `hand_raised` TINYINT(1) NOT NULL DEFAULT 0,
  `last_seen_at` DATETIME NOT NULL,
  `joined_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `live_class_peers_peer` (`class_id`,`peer_id`),
  KEY `live_class_peers_seen` (`class_id`,`last_seen_at`),
  CONSTRAINT `live_class_peers_fk` FOREIGN KEY (`class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `live_class_signals` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `from_peer` VARCHAR(40) NOT NULL,
  `to_peer` VARCHAR(40) NOT NULL,
  `kind` VARCHAR(20) NOT NULL,
  `payload` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `live_class_signals_inbox` (`class_id`,`to_peer`,`id`),
  CONSTRAINT `live_class_signals_fk` FOREIGN KEY (`class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `live_class_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `peer_id` VARCHAR(40) NOT NULL,
  `display_name` VARCHAR(120) NOT NULL,
  `body` VARCHAR(400) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `live_class_messages_class` (`class_id`,`id`),
  CONSTRAINT `live_class_messages_fk` FOREIGN KEY (`class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `live_class_attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `joined_at` DATETIME NOT NULL,
  `left_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `live_class_attendance_class` (`class_id`),
  KEY `live_class_attendance_student` (`student_id`),
  CONSTRAINT `live_class_attendance_class_fk` FOREIGN KEY (`class_id`) REFERENCES `live_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `live_class_attendance_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

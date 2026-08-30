<?php

declare(strict_types=1);

namespace App\Core;

final class Schema
{
    public static function ensure(): void
    {
        self::once('schema-live-v1.ok', static function (): void {
            $db = Database::get();
            $exists = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'live_classes'"
            );
            if ($exists === 0) {
                self::applyFile(ROOT_PATH . '/database/migrate_live_classes.sql');
            }
        });

        self::once('schema-gallery-flat-v1.ok', static function (): void {
            $db = Database::get();
            $pdo = $db->pdo();
            $table = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'gallery_images'"
            );
            if ($table === 0) {
                return;
            }
            $fk = $db->fetchColumn(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_images'
                   AND CONSTRAINT_TYPE = 'FOREIGN KEY' LIMIT 1"
            );
            if (is_string($fk) && $fk !== '') {
                $pdo->exec('ALTER TABLE `gallery_images` DROP FOREIGN KEY `' . str_replace('`', '', $fk) . '`');
            }
            $pdo->exec('ALTER TABLE `gallery_images` MODIFY `category_id` INT UNSIGNED NULL');
        });

        self::once('schema-activity-sections-v1.ok', static function (): void {
            $db = Database::get();
            $pdo = $db->pdo();
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS `social_activity_sections` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(160) NOT NULL,
                  `slug` VARCHAR(160) NOT NULL,
                  `kicker` VARCHAR(80) DEFAULT NULL,
                  `lead` VARCHAR(400) DEFAULT NULL,
                  `sort_order` INT NOT NULL DEFAULT 0,
                  `status` ENUM('draft','published') NOT NULL DEFAULT 'published',
                  `created_at` DATETIME NOT NULL,
                  `updated_at` DATETIME NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `activity_sections_slug` (`slug`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $col = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = 'social_activities' AND column_name = 'section_id'"
            );
            if ($col === 0) {
                $pdo->exec('ALTER TABLE `social_activities` ADD `section_id` INT UNSIGNED NULL AFTER `slug`');
                $pdo->exec('ALTER TABLE `social_activities` ADD KEY `activities_section` (`section_id`)');
            }
        });

        self::once('catalog-activities-v2.ok', static function (): void {
            \App\Services\ActivityCatalog::sync();
        });

        self::once('catalog-about-v2.ok', static function (): void {
            \App\Services\AboutCatalog::sync();
        });

        self::once('catalog-activities-v3.ok', static function (): void {
            \App\Services\ActivityCatalog::sync();
        });

        self::once('catalog-about-v4.ok', static function (): void {
            \App\Services\AboutCatalog::sync();
        });

        self::once('schema-student-remember-v1.ok', static function (): void {
            \App\Core\StudentRemember::ensureTable();
        });

        self::once('schema-course-enquiries-v1.ok', static function (): void {
            Database::get()->pdo()->exec(
                "CREATE TABLE IF NOT EXISTS `course_enquiries` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `course_id` INT UNSIGNED DEFAULT NULL,
                  `course_title` VARCHAR(180) NOT NULL,
                  `name` VARCHAR(120) NOT NULL,
                  `email` VARCHAR(190) NOT NULL,
                  `phone` VARCHAR(40) NOT NULL,
                  `whatsapp` VARCHAR(40) NOT NULL,
                  `address` VARCHAR(400) NOT NULL,
                  `ip_address` VARCHAR(45) DEFAULT NULL,
                  `status` ENUM('new','contacted') NOT NULL DEFAULT 'new',
                  `created_at` DATETIME NOT NULL,
                  `updated_at` DATETIME DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `course_enquiries_course` (`course_id`),
                  KEY `course_enquiries_status` (`status`, `created_at`),
                  CONSTRAINT `course_enquiries_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        });

        self::once('schema-admin-members-v1.ok', static function (): void {
            $db = Database::get();
            $pdo = $db->pdo();
            $has = static function (string $column) use ($db): bool {
                return (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM information_schema.columns
                     WHERE table_schema = DATABASE() AND table_name = 'admins' AND column_name = ?",
                    [$column]
                ) > 0;
            };
            if (!$has('job_title')) {
                $pdo->exec('ALTER TABLE `admins` ADD `job_title` VARCHAR(80) NULL AFTER `name`');
            }
            if (!$has('panel_role')) {
                $pdo->exec("ALTER TABLE `admins` ADD `panel_role` ENUM('owner','member') NOT NULL DEFAULT 'owner' AFTER `status`");
            }
            if (!$has('permissions')) {
                $pdo->exec('ALTER TABLE `admins` ADD `permissions` TEXT NULL AFTER `panel_role`');
            }
        });

        self::once('schema-fatawa-v1.ok', static function (): void {
            $pdo = Database::get()->pdo();
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS `fatawa` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `slug` VARCHAR(180) NOT NULL,
                  `issued_on` DATE NOT NULL,
                  `title_ar` VARCHAR(240) DEFAULT NULL,
                  `body_ar` MEDIUMTEXT,
                  `title_en` VARCHAR(240) DEFAULT NULL,
                  `body_en` MEDIUMTEXT,
                  `title_hi` VARCHAR(240) DEFAULT NULL,
                  `body_hi` MEDIUMTEXT,
                  `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
                  `created_at` DATETIME NOT NULL,
                  `updated_at` DATETIME NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `fatawa_slug` (`slug`),
                  KEY `fatawa_issued` (`status`, `issued_on`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS `fatwa_questions` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `fatwa_id` INT UNSIGNED NOT NULL,
                  `name` VARCHAR(120) NOT NULL,
                  `email` VARCHAR(190) DEFAULT NULL,
                  `body` TEXT,
                  `attachment_path` VARCHAR(255) DEFAULT NULL,
                  `attachment_name` VARCHAR(180) DEFAULT NULL,
                  `attachment_mime` VARCHAR(80) DEFAULT NULL,
                  `ip_address` VARCHAR(45) DEFAULT NULL,
                  `status` ENUM('new','answered','hidden') NOT NULL DEFAULT 'new',
                  `answer` MEDIUMTEXT,
                  `answered_at` DATETIME DEFAULT NULL,
                  `created_at` DATETIME NOT NULL,
                  `updated_at` DATETIME DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `fatwa_questions_fatwa` (`fatwa_id`, `status`),
                  CONSTRAINT `fatwa_questions_fatwa_fk` FOREIGN KEY (`fatwa_id`) REFERENCES `fatawa` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        });

        self::once('seed-fatawa-sample-v1.ok', static function (): void {
            $db = Database::get();
            $count = (int) $db->fetchColumn('SELECT COUNT(*) FROM fatawa');
            if ($count > 0) {
                return;
            }
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            $id = $db->insert('fatawa', [
                'slug' => $today,
                'issued_on' => $today,
                'title_ar' => 'فضل صلاة الجماعة',
                'body_ar' => "صلاة الجماعة في المسجد أفضل من صلاة الفذ بسبع وعشرين درجة، كما جاء في الحديث الشريف.\n\nوينبغي للمسلم أن يحرص عليها ما استطاع، إلا من عذر شرعي كالمرض أو الخوف أو ما يمنع من حضور المسجد.",
                'title_en' => 'The virtue of congregational prayer',
                'body_en' => "Praying in congregation at the mosque is better than praying alone by twenty-seven degrees, as reported in the authentic hadith.\n\nA Muslim should attend it whenever able, unless there is a valid excuse such as illness, fear, or anything that prevents reaching the mosque.",
                'title_hi' => 'जमात की नमाज़ की फ़ज़ीलत',
                'body_hi' => "मस्जिद में जमात के साथ नमाज़ अकेले नमाज़ से सत्ताईस दर्जा बेहतर है, जैसा सही हदीस में आया है।\n\nबिना शरई उज़्र के — बीमारी, खौफ़, या मस्जिद न पहुँच पाने वाले किसी कारण के — मुसलमान को जमात का हिरस रखना चाहिए।",
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $db->insert('fatwa_questions', [
                'fatwa_id' => $id,
                'name' => 'Yusuf',
                'email' => null,
                'body' => 'If I work a night shift and miss Fajr jamaat at the mosque, do I still receive the reward if I pray at home with my family?',
                'attachment_path' => null,
                'attachment_name' => null,
                'attachment_mime' => null,
                'ip_address' => '127.0.0.1',
                'status' => 'answered',
                'answer' => 'Yes. Praying with your family is still congregation, and you receive its reward. When your shift allows, attend the mosque; that is more complete.',
                'answered_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $db->insert('fatwa_questions', [
                'fatwa_id' => $id,
                'name' => 'Amina',
                'email' => null,
                'body' => 'Can women pray jamaat at home behind a mahram?',
                'attachment_path' => null,
                'attachment_name' => null,
                'attachment_mime' => null,
                'ip_address' => '127.0.0.1',
                'status' => 'new',
                'answer' => null,
                'answered_at' => null,
                'created_at' => $now,
                'updated_at' => null,
            ]);
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $db->insert('fatawa', [
                'slug' => $yesterday,
                'issued_on' => $yesterday,
                'title_ar' => 'النصيحة قبل الفتوى',
                'body_ar' => 'الفتوى جواب عن سؤال مخصوص. ما ينشر هنا توجيه عام، ومن احتاج حكماً لواقعة نفسه فليسأل تحت الفتوى.',
                'title_en' => 'Advice before a fatwa',
                'body_en' => 'A fatwa answers a specific question. What is published here is general guidance. If your situation needs a ruling, ask under that day’s fatwa.',
                'title_hi' => 'फ़तवे से पहले एक बात',
                'body_hi' => 'फ़तवा किसी खास सवाल का जवाब होता है। यहाँ सामान्य हिदायत प्रकाशित होती है। अपनी सूरत का हुक्म चाहिए तो उसी दिन के फ़तवे के नीचे पूछें।',
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        self::once('seed-fatawa-previous-v1.ok', static function (): void {
            $db = Database::get();
            $today = date('Y-m-d');
            $older = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM fatawa WHERE status = 'published' AND issued_on < ?",
                [$today]
            );
            if ($older > 0) {
                return;
            }
            $now = date('Y-m-d H:i:s');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $exists = $db->fetch('SELECT id FROM fatawa WHERE slug = ? LIMIT 1', [$yesterday]);
            $slug = $exists ? $yesterday . '-previous' : $yesterday;
            $db->insert('fatawa', [
                'slug' => $slug,
                'issued_on' => $yesterday,
                'title_ar' => 'النصيحة قبل الفتوى',
                'body_ar' => 'الفتوى جواب عن سؤال مخصوص. ما ينشر هنا توجيه عام، ومن احتاج حكماً لواقعة نفسه فليسأل تحت الفتوى.',
                'title_en' => 'Advice before a fatwa',
                'body_en' => 'A fatwa answers a specific question. What is published here is general guidance. If your situation needs a ruling, ask under that day’s fatwa.',
                'title_hi' => 'फ़तवे से पहले एक बात',
                'body_hi' => 'फ़तवा किसी खास सवाल का जवाब होता है। यहाँ सामान्य हिदायत प्रकाशित होती है। अपनी सूरत का हुक्म चाहिए तो उसी दिन के फ़तवे के नीचे पूछें।',
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        self::once('schema-center-updates-v1.ok', static function (): void {
            $pdo = Database::get()->pdo();
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS `center_updates` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `slug` VARCHAR(180) NOT NULL,
                  `published_on` DATE NOT NULL,
                  `title` VARCHAR(240) NOT NULL,
                  `excerpt` VARCHAR(400) DEFAULT NULL,
                  `body_html` MEDIUMTEXT,
                  `cover_image` VARCHAR(255) DEFAULT NULL,
                  `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
                  `created_at` DATETIME NOT NULL,
                  `updated_at` DATETIME NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `updates_slug` (`slug`),
                  KEY `updates_published` (`status`, `published_on`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        });

        self::once('seed-center-updates-v1.ok', static function (): void {
            $db = Database::get();
            $count = (int) $db->fetchColumn('SELECT COUNT(*) FROM center_updates');
            if ($count > 0) {
                return;
            }
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $db->insert('center_updates', [
                'slug' => $today . '-jummah-gathering',
                'published_on' => $today,
                'title' => 'Jummah gathering and a short talk after Zuhr',
                'excerpt' => 'Brothers are invited to stay a few minutes after Jummah for a short reminder in the hall.',
                'body_html' => '<p>After Jummah this week the administration invites families to remain in the hall for a ten-minute reminder on keeping salah in congregation during the working week.</p><p>Tea will be served at the back of the courtyard. Sisters may sit in the usual section. Bring children — they are welcome if they sit with a parent.</p>',
                'cover_image' => null,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $db->insert('center_updates', [
                'slug' => $yesterday . '-quran-class',
                'published_on' => $yesterday,
                'title' => 'Evening Qur’an class seats still open',
                'excerpt' => 'A few seats remain in the after-Maghrib Tajweed circle for boys aged 10 to 14.',
                'body_html' => '<p>The after-Maghrib Tajweed circle still has a few places. Classes run Sunday to Thursday. Parents may sit in on the first evening.</p><p>Register at the office with the student’s name and a parent phone number. Bring a Qur’an if you have one; copies are also kept in the classroom.</p>',
                'cover_image' => null,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        self::once('setting-faith-terms-v1.ok', static function (): void {
            \App\Models\Setting::put('faith_terms', '1');
        });

        self::once('schema-student-courses-v1.ok', static function (): void {
            $db = Database::get();
            $pdo = $db->pdo();
            $table = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'student_courses'"
            );
            if ($table === 0) {
                $pdo->exec(
                    'CREATE TABLE `student_courses` (
                        `student_id` INT UNSIGNED NOT NULL,
                        `course_id` INT UNSIGNED NOT NULL,
                        `created_at` DATETIME NOT NULL,
                        PRIMARY KEY (`student_id`, `course_id`),
                        KEY `student_courses_course` (`course_id`),
                        CONSTRAINT `student_courses_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
                        CONSTRAINT `student_courses_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }
            $pdo->exec(
                'INSERT IGNORE INTO `student_courses` (`student_id`, `course_id`, `created_at`)
                 SELECT `id`, `course_id`, NOW() FROM `students` WHERE `course_id` IS NOT NULL'
            );
        });

        self::once('schema-live-present-v1.ok', static function (): void {
            $db = Database::get();
            $pdo = $db->pdo();
            $table = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'live_class_peers'"
            );
            if ($table === 0) {
                return;
            }
            $screen = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = 'live_class_peers' AND column_name = 'screen_on'"
            );
            if ($screen === 0) {
                $pdo->exec('ALTER TABLE `live_class_peers` ADD `screen_on` TINYINT(1) NOT NULL DEFAULT 0 AFTER `video_on`');
            }
            $presenter = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = 'live_class_peers' AND column_name = 'is_presenter'"
            );
            if ($presenter === 0) {
                $pdo->exec('ALTER TABLE `live_class_peers` ADD `is_presenter` TINYINT(1) NOT NULL DEFAULT 0 AFTER `screen_on`');
            }
        });

        self::once('schema-public-live-v1.ok', static function (): void {
            self::applyFile(ROOT_PATH . '/database/migrate_public_live.sql');
        });
        self::once('schema-public-live-v2.ok', static function (): void {
            self::applyFile(ROOT_PATH . '/database/migrate_public_live_comments.sql');
        });
        self::once('schema-settings-mediumtext-v1.ok', static function (): void {
            Database::get()->pdo()->exec(
                'ALTER TABLE `settings` MODIFY `setting_value` MEDIUMTEXT'
            );
        });
        self::once('schema-coordinators-v1.ok', static function (): void {
            $db = Database::get();
            $pdo = $db->pdo();
            $table = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'founders'"
            );
            if ($table === 0) {
                return;
            }
            $has = static function (string $column) use ($db): bool {
                return (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM information_schema.columns
                     WHERE table_schema = DATABASE() AND table_name = 'founders' AND column_name = ?",
                    [$column]
                ) > 0;
            };
            if (!$has('highlights')) {
                $pdo->exec('ALTER TABLE `founders` ADD `highlights` TEXT NULL AFTER `biography`');
            }
            $pdo->exec('ALTER TABLE `founders` MODIFY `name` VARCHAR(180) NOT NULL');
            $pdo->exec('ALTER TABLE `founders` MODIFY `designation` VARCHAR(255) NULL');
            \App\Services\CoordinatorService::seed();
        });

        self::once('content-terms-bake-v1.ok', static function (): void {
            \App\Services\ContentTerms::bake();
        });
    }

    private static function once(string $flagName, callable $work): void
    {
        $flag = STORAGE_PATH . '/cache/' . $flagName;
        if (is_file($flag)) {
            return;
        }
        try {
            $work();
            @file_put_contents($flag, date('c'));
        } catch (\Throwable $e) {
            error_log('Schema ' . $flagName . ': ' . $e->getMessage());
        }
    }

    private static function applyFile(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        $sql = (string) file_get_contents($path);
        $pdo = Database::get()->pdo();
        $buffer = '';
        foreach (preg_split("/\r\n|\n|\r/", $sql) as $line) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '--')) {
                continue;
            }
            $buffer .= $line . "\n";
            if (str_ends_with(rtrim($line), ';')) {
                $stmt = trim($buffer);
                $buffer = '';
                if ($stmt !== '') {
                    $pdo->exec($stmt);
                }
            }
        }
    }
}

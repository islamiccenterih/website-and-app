<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Keeps a student signed in on the same browser for 15 days.
 * Each device has its own token. Sign-out or a password reset clears it.
 */
final class StudentRemember
{
    public const COOKIE = 'ic_student';

    public static function days(): int
    {
        $days = (int) cfg('security.student_remember_days', 15);
        return $days > 0 ? $days : 15;
    }

    public static function issue(int $studentId): void
    {
        if ($studentId < 1) {
            return;
        }
        self::ensureTable();
        self::purgeExpired();
        self::capTokens($studentId);

        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + self::days() * 86400);
        Database::get()->insert('student_remember_tokens', [
            'student_id' => $studentId,
            'selector' => $selector,
            'token_hash' => hash('sha256', $validator),
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s'),
            'last_used_at' => date('Y-m-d H:i:s'),
        ]);
        self::writeCookie($selector . ':' . $validator, strtotime($expires));
        $_SESSION['student_remember_selector'] = $selector;
    }

    public static function restoreIfNeeded(): void
    {
        if (!empty($_COOKIE[self::COOKIE])) {
            Session::start();
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $role = $_SESSION['auth']['role'] ?? '';
        if ($role === 'admin' || $role === 'student') {
            return;
        }
        self::restore();
    }

    public static function restore(): void
    {
        $parsed = self::readCookie();
        if ($parsed === null) {
            return;
        }
        self::ensureTable();
        [$selector, $validator] = $parsed;
        $row = Database::get()->fetch(
            'SELECT * FROM student_remember_tokens WHERE selector = ? LIMIT 1',
            [$selector]
        );
        if (!$row || strtotime((string) $row['expires_at']) < time()) {
            if ($row) {
                Database::get()->delete('student_remember_tokens', 'id = ?', [(int) $row['id']]);
            }
            self::clearCookie();
            return;
        }
        if (!hash_equals((string) $row['token_hash'], hash('sha256', $validator))) {
            Database::get()->delete('student_remember_tokens', 'id = ?', [(int) $row['id']]);
            self::clearCookie();
            return;
        }
        $student = Database::get()->fetch(
            "SELECT * FROM students WHERE id = ? AND status = 'active' LIMIT 1",
            [(int) $row['student_id']]
        );
        if (!$student) {
            Database::get()->delete('student_remember_tokens', 'student_id = ?', [(int) $row['student_id']]);
            self::clearCookie();
            return;
        }

        Session::regenerate();
        $_SESSION['auth'] = Auth::sessionPayload('student', $student);
        $_SESSION['student_remember_selector'] = $selector;
        Database::get()->update('student_remember_tokens', [
            'last_used_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $row['id']]);
        Database::get()->update('students', [
            'last_login_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $student['id']]);
    }

    public static function forgetCurrent(): void
    {
        $parsed = self::readCookie();
        $selector = $parsed[0] ?? (string) ($_SESSION['student_remember_selector'] ?? '');
        if ($selector !== '') {
            self::ensureTable();
            Database::get()->delete('student_remember_tokens', 'selector = ?', [$selector]);
        }
        unset($_SESSION['student_remember_selector']);
        self::clearCookie();
    }

    public static function forgetAll(int $studentId): void
    {
        if ($studentId < 1) {
            return;
        }
        self::ensureTable();
        $clearThisBrowser = false;
        $parsed = self::readCookie();
        if ($parsed !== null) {
            $row = Database::get()->fetch(
                'SELECT student_id FROM student_remember_tokens WHERE selector = ? LIMIT 1',
                [$parsed[0]]
            );
            $clearThisBrowser = $row && (int) $row['student_id'] === $studentId;
        }
        $sessionStudent = (int) (($_SESSION['auth']['id'] ?? 0));
        $sessionIsStudent = (($_SESSION['auth']['role'] ?? '') === 'student');
        if ($sessionIsStudent && $sessionStudent === $studentId) {
            $clearThisBrowser = true;
        }
        Database::get()->delete('student_remember_tokens', 'student_id = ?', [$studentId]);
        if ($clearThisBrowser) {
            unset($_SESSION['student_remember_selector']);
            self::clearCookie();
        }
    }

    public static function ensureTable(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }
        $exists = (int) Database::get()->fetchColumn(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'student_remember_tokens'"
        );
        if ($exists === 0) {
            Database::get()->pdo()->exec(
                "CREATE TABLE IF NOT EXISTS `student_remember_tokens` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `student_id` INT UNSIGNED NOT NULL,
                  `selector` CHAR(24) NOT NULL,
                  `token_hash` CHAR(64) NOT NULL,
                  `user_agent` VARCHAR(255) DEFAULT NULL,
                  `expires_at` DATETIME NOT NULL,
                  `created_at` DATETIME NOT NULL,
                  `last_used_at` DATETIME DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `student_remember_selector` (`selector`),
                  KEY `student_remember_student` (`student_id`),
                  KEY `student_remember_expires` (`expires_at`),
                  CONSTRAINT `student_remember_student_fk`
                    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
        $ready = true;
    }

    private static function capTokens(int $studentId): void
    {
        $ids = Database::get()->fetchAll(
            'SELECT id FROM student_remember_tokens WHERE student_id = ? ORDER BY id DESC',
            [$studentId]
        );
        if (count($ids) < 8) {
            return;
        }
        $keep = array_slice($ids, 0, 7);
        $keepIds = array_map(static fn(array $row): int => (int) $row['id'], $keep);
        if ($keepIds === []) {
            return;
        }
        $in = implode(',', $keepIds);
        Database::get()->pdo()->exec(
            'DELETE FROM student_remember_tokens WHERE student_id = ' . (int) $studentId . ' AND id NOT IN (' . $in . ')'
        );
    }

    private static function purgeExpired(): void
    {
        Database::get()->pdo()->exec("DELETE FROM student_remember_tokens WHERE expires_at < NOW()");
    }

    /** @return array{0:string,1:string}|null */
    private static function readCookie(): ?array
    {
        $raw = (string) ($_COOKIE[self::COOKIE] ?? '');
        if (!preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/', $raw, $m)) {
            return null;
        }
        return [$m[1], $m[2]];
    }

    private static function writeCookie(string $value, int $expires): void
    {
        setcookie(self::COOKIE, $value, [
            'expires' => $expires,
            'path' => '/',
            'secure' => request_is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE] = $value;
    }

    private static function clearCookie(): void
    {
        setcookie(self::COOKIE, '', [
            'expires' => time() - 42000,
            'path' => '/',
            'secure' => request_is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[self::COOKIE]);
    }
}

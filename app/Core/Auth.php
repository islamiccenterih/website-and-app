<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function attempt(string $role, string $email, string $password): bool
    {
        $email = mb_strtolower(trim($email));
        if ($email === '' || $password === '') {
            return false;
        }

        if (self::isLocked($email)) {
            return false;
        }

        $table = $role === 'admin' ? 'admins' : 'students';
        $row = Database::get()->fetch(
            "SELECT * FROM `$table` WHERE email = ? AND status = 'active' LIMIT 1",
            [$email]
        );

        if (!$row || !password_verify($password, $row['password_hash'])) {
            self::recordAttempt($email, false);
            return false;
        }

        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            Database::get()->update($table, [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ], 'id = ?', [(int) $row['id']]);
        }

        Session::regenerate();
        $_SESSION['auth'] = self::sessionFromRow($role, $row);

        Database::get()->update($table, [
            'last_login_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $row['id']]);

        if ($role === 'student') {
            StudentRemember::issue((int) $row['id']);
        }

        self::recordAttempt($email, true);
        return true;
    }

    public static function logout(): void
    {
        StudentRemember::forgetCurrent();
        Session::destroy();
        Session::start();
    }

    public static function requireRole(string $role): void
    {
        if ($role === 'student') {
            StudentRemember::restoreIfNeeded();
        }
        $auth = $_SESSION['auth'] ?? null;
        if (!$auth || ($auth['role'] ?? '') !== $role) {
            $login = $role === 'admin' ? '/admin/login' : '/student/login';
            flash('error', 'Please sign in to continue.');
            redirect($login);
        }
        if ($role === 'admin') {
            self::refreshAdminSession();
        }
        if ($role === 'student') {
            self::refreshStudentSession();
        }
    }

    public static function refreshAdminSession(): void
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!$auth || ($auth['role'] ?? '') !== 'admin') {
            return;
        }
        $row = Database::get()->fetch('SELECT * FROM admins WHERE id = ? LIMIT 1', [(int) $auth['id']]);
        if (!$row || ($row['status'] ?? '') !== 'active') {
            self::logout();
            flash('error', 'This account is no longer active.');
            redirect('/admin/login');
        }
        $_SESSION['auth'] = array_merge($auth, self::sessionFromRow('admin', $row));
    }

    public static function refreshStudentSession(): void
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!$auth || ($auth['role'] ?? '') !== 'student') {
            return;
        }
        $row = Database::get()->fetch('SELECT * FROM students WHERE id = ? LIMIT 1', [(int) $auth['id']]);
        if (!$row || ($row['status'] ?? '') !== 'active') {
            self::logout();
            flash('error', 'This account is no longer active.');
            redirect('/student/login');
        }
        $_SESSION['auth'] = array_merge($auth, self::sessionFromRow('student', $row));
    }

    public static function sessionPayload(string $role, array $row): array
    {
        return self::sessionFromRow($role, $row);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function sessionFromRow(string $role, array $row): array
    {
        $panelRole = (string) ($row['panel_role'] ?? 'owner');
        if ($panelRole !== 'member') {
            $panelRole = 'owner';
        }
        return [
            'id' => (int) $row['id'],
            'role' => $role,
            'name' => $row['name'],
            'email' => $row['email'],
            'panel_role' => $role === 'admin' ? $panelRole : null,
            'job_title' => $role === 'admin' ? (string) ($row['job_title'] ?? '') : '',
            'permissions' => $role === 'admin' ? AdminAccess::fromJson($row['permissions'] ?? null) : [],
            'avatar' => $role === 'student' ? (string) ($row['avatar'] ?? '') : '',
        ];
    }

    public static function guestOnly(string $role): void
    {
        if ($role === 'student') {
            StudentRemember::restoreIfNeeded();
        }
        $auth = $_SESSION['auth'] ?? null;
        if ($auth && ($auth['role'] ?? '') === $role) {
            redirect($role === 'admin' ? '/admin' : '/student');
        }
    }

    public static function isLocked(string $email): bool
    {
        $minutes = (int) cfg('security.login_lock_minutes', 15);
        $max = (int) cfg('security.login_max_attempts', 8);
        $count = Database::get()->fetchColumn(
            'SELECT COUNT(*) FROM login_attempts
             WHERE identifier = ? AND ip_address = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)',
            [mb_strtolower($email), request_ip(), $minutes]
        );
        return (int) $count >= $max;
    }

    private static function recordAttempt(string $email, bool $success): void
    {
        Database::get()->insert('login_attempts', [
            'identifier' => mb_strtolower($email),
            'ip_address' => request_ip(),
            'success' => $success ? 1 : 0,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

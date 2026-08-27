<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function needed(): bool
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $name = (string) cfg('security.session_name', 'ICSESSID');
        if (!empty($_COOKIE[$name]) || !empty($_COOKIE[StudentRemember::COOKIE])) {
            return true;
        }
        if ($method !== 'GET' && $method !== 'HEAD') {
            return true;
        }

        $path = current_path();
        if (str_starts_with($path, '/admin') || str_starts_with($path, '/student')) {
            return true;
        }
        if (str_starts_with($path, '/language/')) {
            return true;
        }
        if ($path === '/contact-us' || $path === '/live' || str_starts_with($path, '/live/')) {
            return true;
        }
        if (preg_match('#^/courses/[^/]+$#', $path) || preg_match('#^/fatawa/[^/]+$#', $path)) {
            return true;
        }

        return false;
    }

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_cache_limiter('');
        session_name((string) cfg('security.session_name', 'ICSESSID'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => request_is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();

        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - (int) $_SESSION['_created'] > 86400) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

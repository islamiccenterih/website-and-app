<?php

declare(strict_types=1);

namespace App\Core;

final class Application
{
    public function run(): void
    {
        if (Session::needed()) {
            Session::start();
        }
        StudentRemember::restoreIfNeeded();
        \App\I18n\Lang::boot();

        if (!headers_sent()) {
            header('Permissions-Policy: accelerometer=*, gyroscope=*, magnetometer=*, geolocation=*, camera=(self), microphone=(self), display-capture=(self)');
            $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
            if (session_status() !== PHP_SESSION_ACTIVE && ($method === 'GET' || $method === 'HEAD')) {
                header('Cache-Control: public, max-age=60, s-maxage=60');
            }
        }

        $router = new Router();
        require APP_PATH . '/routes.php';

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = current_path();

        $router->dispatch($method, $path);
    }
}

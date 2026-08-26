<?php

declare(strict_types=1);

namespace App\Core;

final class Application
{
    public function run(): void
    {
        Session::start();
        StudentRemember::restoreIfNeeded();
        \App\I18n\Lang::boot();

        if (!headers_sent()) {
            header('Permissions-Policy: accelerometer=*, gyroscope=*, magnetometer=*, geolocation=*, camera=(self), microphone=(self), display-capture=(self)');
        }

        $router = new Router();
        require APP_PATH . '/routes.php';

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = current_path();

        $router->dispatch($method, $path);
    }
}

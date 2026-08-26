<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');

$configFile = CONFIG_PATH . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    echo 'Configuration file is missing. Copy config/config.example.php to config/config.php.';
    exit;
}

$GLOBALS['config'] = require $configFile;

date_default_timezone_set((string) ($GLOBALS['config']['app']['timezone'] ?? 'UTC'));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = APP_PATH . '/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . '/Core/helpers.php';

if (!is_dir(STORAGE_PATH . '/logs')) {
    @mkdir(STORAGE_PATH . '/logs', 0755, true);
}
if (!is_dir(STORAGE_PATH . '/cache')) {
    @mkdir(STORAGE_PATH . '/cache', 0755, true);
}
if (!is_dir(STORAGE_PATH . '/live')) {
    @mkdir(STORAGE_PATH . '/live', 0755, true);
}
if (!is_dir(STORAGE_PATH . '/live-class')) {
    @mkdir(STORAGE_PATH . '/live-class', 0755, true);
}

\App\Core\Schema::ensure();

set_exception_handler(static function (Throwable $e): void {
    $log = STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log';
    $line = sprintf("[%s] %s in %s:%d\n%s\n\n", date('c'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    @file_put_contents($log, $line, FILE_APPEND);

    http_response_code(500);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $e->getMessage() . PHP_EOL);
        return;
    }

    $message = cfg('app.env') === 'development'
        ? $e->getMessage()
        : 'The page could not be loaded. Please try again shortly.';

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    $safe = e($message);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Server error</title>';
    echo '<style>body{font-family:Georgia,serif;background:#0e2a22;color:#f4ead6;display:grid;place-items:center;min-height:100vh;margin:0}main{max-width:32rem;padding:2rem;text-align:center}a{color:#c9a227}</style></head><body><main>';
    echo '<p>Islamic Center</p><h1>Something went wrong</h1><p>' . $safe . '</p><p><a href="' . e(url('/')) . '">Return home</a></p>';
    echo '</main></body></html>';
});

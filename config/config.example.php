<?php

/**
 * Copy this file to config.php and update the values for your hosting account.
 * Never commit real production passwords.
 */
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Islamic Center Information Hub',
        'url' => 'https://your-domain.example',
        'base_path' => '',
        'env' => 'production',
        'timezone' => 'Asia/Kolkata',
    ],
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'your_database_name',
        'user' => 'your_database_user',
        'pass' => 'your_database_password',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'ICSESSID',
        'csrf_key' => 'ic_csrf',
        'login_max_attempts' => 8,
        'login_lock_minutes' => 15,
        'student_remember_days' => 15,
    ],
    'uploads' => [
        'max_bytes' => 10 * 1024 * 1024,
        'allowed_ext' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'dir' => 'uploads',
    ],
    'moon' => [
        'provider' => 'aladhan_sunrisesunset',
        'cache_seconds' => 21600,
        'timeout' => 8,
    ],
];

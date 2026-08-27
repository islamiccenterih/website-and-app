<?php

declare(strict_types=1);

namespace App\Models;

final class Setting extends Model
{
    protected static string $table = 'settings';

    public static function put(string $key, ?string $value): void
    {
        $exists = static::db()->fetch('SELECT id FROM settings WHERE setting_key = ? LIMIT 1', [$key]);
        if ($exists) {
            static::db()->update('settings', [
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'setting_key = ?', [$key]);
        } else {
            static::db()->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if (isset($GLOBALS['ic_settings']) && is_array($GLOBALS['ic_settings'])) {
            $GLOBALS['ic_settings'][$key] = $value;
        }
    }

    public static function putMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::put((string) $key, $value === null ? null : (string) $value);
        }
    }
}

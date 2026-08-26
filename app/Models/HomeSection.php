<?php

declare(strict_types=1);

namespace App\Models;

final class HomeSection extends Model
{
    protected static string $table = 'home_sections';

    public static function keyed(): array
    {
        $rows = static::db()->fetchAll('SELECT * FROM home_sections');
        $out = [];
        foreach ($rows as $row) {
            $out[$row['section_key']] = $row;
        }
        return $out;
    }

    public static function upsert(string $key, array $data): void
    {
        $existing = static::db()->fetch('SELECT id FROM home_sections WHERE section_key = ? LIMIT 1', [$key]);
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($existing) {
            static::db()->update('home_sections', $data, 'section_key = ?', [$key]);
            return;
        }
        $data['section_key'] = $key;
        static::db()->insert('home_sections', $data);
    }
}

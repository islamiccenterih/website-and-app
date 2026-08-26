<?php

declare(strict_types=1);

namespace App\Models;

final class AboutSection extends Model
{
    protected static string $table = 'about_sections';

    public static function keyed(): array
    {
        $rows = static::db()->fetchAll('SELECT * FROM about_sections');
        $out = [];
        foreach ($rows as $row) {
            $out[$row['section_key']] = $row;
        }
        return $out;
    }

    public static function upsert(string $key, array $data): void
    {
        $existing = static::db()->fetch('SELECT id FROM about_sections WHERE section_key = ? LIMIT 1', [$key]);
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($existing) {
            static::db()->update('about_sections', $data, 'section_key = ?', [$key]);
            return;
        }
        $data['section_key'] = $key;
        static::db()->insert('about_sections', $data);
    }
}

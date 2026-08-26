<?php

declare(strict_types=1);

namespace App\Models;

final class ActivitySection extends Model
{
    protected static string $table = 'social_activity_sections';

    public static function published(): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM social_activity_sections WHERE status = ? ORDER BY sort_order ASC, id ASC',
            ['published']
        );
    }

    public static function bySlug(string $slug): ?array
    {
        return static::db()->fetch(
            'SELECT * FROM social_activity_sections WHERE slug = ? LIMIT 1',
            [$slug]
        );
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM social_activity_sections WHERE slug = ?';
            $params = [$slug];
            if ($ignoreId) {
                $sql .= ' AND id != ?';
                $params[] = $ignoreId;
            }
            if (!static::db()->fetch($sql . ' LIMIT 1', $params)) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

final class Course extends Model
{
    protected static string $table = 'courses';

    public static function published(): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM courses WHERE status = ? ORDER BY sort_order ASC, id ASC',
            ['published']
        );
    }

    public static function featured(int $limit = 6): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM courses WHERE status = ? AND featured = 1 ORDER BY sort_order ASC, id ASC LIMIT ' . (int) $limit,
            ['published']
        );
    }

    public static function bySlug(string $slug): ?array
    {
        return static::db()->fetch(
            'SELECT * FROM courses WHERE slug = ? AND status = ? LIMIT 1',
            [$slug, 'published']
        );
    }

    public static function images(int $courseId): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM course_images WHERE course_id = ? ORDER BY sort_order ASC, id ASC',
            [$courseId]
        );
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM courses WHERE slug = ?';
            $params = [$slug];
            if ($ignoreId) {
                $sql .= ' AND id != ?';
                $params[] = $ignoreId;
            }
            $exists = static::db()->fetch($sql . ' LIMIT 1', $params);
            if (!$exists) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }
}

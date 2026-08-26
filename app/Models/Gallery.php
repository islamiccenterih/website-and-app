<?php

declare(strict_types=1);

namespace App\Models;

final class Gallery extends Model
{
    protected static string $table = 'gallery_images';

    public static function publishedImages(): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM gallery_images
             WHERE status = ?
             ORDER BY sort_order ASC, id DESC',
            ['published']
        );
    }

    public static function featured(int $limit = 8): array
    {
        $limit = max(1, $limit);
        $featured = static::db()->fetchAll(
            'SELECT * FROM gallery_images
             WHERE status = ? AND featured = 1
             ORDER BY sort_order ASC, id DESC
             LIMIT ' . $limit,
            ['published']
        );
        if ($featured) {
            return $featured;
        }
        return static::db()->fetchAll(
            'SELECT * FROM gallery_images
             WHERE status = ?
             ORDER BY id DESC
             LIMIT ' . $limit,
            ['published']
        );
    }
}

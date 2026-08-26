<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\HtmlSanitizer;

final class CenterUpdate extends Model
{
    protected static string $table = 'center_updates';

    public static function published(): array
    {
        return static::db()->fetchAll(
            "SELECT * FROM center_updates WHERE status = 'published' ORDER BY published_on DESC, id DESC"
        );
    }

    public static function today(): ?array
    {
        return static::db()->fetch(
            "SELECT * FROM center_updates WHERE status = 'published' AND published_on = ? ORDER BY id DESC LIMIT 1",
            [self::todayDate()]
        );
    }

    public static function archive(?int $exceptId = null): array
    {
        $sql = "SELECT * FROM center_updates WHERE status = 'published' AND published_on < ?";
        $params = [self::todayDate()];
        if ($exceptId) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }
        $sql .= ' ORDER BY published_on DESC, id DESC';
        return static::db()->fetchAll($sql, $params);
    }

    public static function bySlug(string $slug): ?array
    {
        return static::db()->fetch(
            "SELECT * FROM center_updates WHERE slug = ? AND status = 'published' LIMIT 1",
            [$slug]
        );
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug !== '' ? $slug : 'update';
        $candidate = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM center_updates WHERE slug = ?';
            $params = [$candidate];
            if ($ignoreId) {
                $sql .= ' AND id != ?';
                $params[] = $ignoreId;
            }
            $exists = static::db()->fetch($sql . ' LIMIT 1', $params);
            if (!$exists) {
                return $candidate;
            }
            $candidate = $base . '-' . $i;
            $i++;
        }
    }

    public static function todayDate(): string
    {
        $name = (string) (function_exists('setting') ? setting('timezone', cfg('app.timezone', 'Asia/Kolkata')) : cfg('app.timezone', 'Asia/Kolkata'));
        try {
            $tz = new \DateTimeZone($name !== '' ? $name : 'Asia/Kolkata');
        } catch (\Exception) {
            $tz = new \DateTimeZone('Asia/Kolkata');
        }
        return (new \DateTimeImmutable('now', $tz))->format('Y-m-d');
    }

    public static function cardImage(array $row): ?string
    {
        $cover = trim((string) ($row['cover_image'] ?? ''));
        if ($cover !== '') {
            return $cover;
        }
        return HtmlSanitizer::firstImage((string) ($row['body_html'] ?? ''));
    }

    public static function cardExcerpt(array $row, int $len = 180): string
    {
        $excerpt = trim((string) ($row['excerpt'] ?? ''));
        if ($excerpt !== '') {
            return mb_strlen($excerpt) <= $len ? $excerpt : rtrim(mb_substr($excerpt, 0, $len)) . '…';
        }
        return HtmlSanitizer::excerpt((string) ($row['body_html'] ?? ''), $len);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

final class FatwaQuestion extends Model
{
    protected static string $table = 'fatwa_questions';

    public static function forFatwa(int $fatwaId, bool $publicOnly = false): array
    {
        $sql = 'SELECT * FROM fatwa_questions WHERE fatwa_id = ?';
        $params = [$fatwaId];
        if ($publicOnly) {
            $sql .= " AND status != 'hidden'";
        }
        $sql .= ' ORDER BY created_at ASC, id ASC';
        return static::db()->fetchAll($sql, $params);
    }

    public static function recentCountForIp(string $ip): int
    {
        return (int) static::db()->fetchColumn(
            'SELECT COUNT(*) FROM fatwa_questions WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)',
            [$ip]
        );
    }

    public static function isImage(?string $mime): bool
    {
        return is_string($mime) && str_starts_with($mime, 'image/');
    }
}

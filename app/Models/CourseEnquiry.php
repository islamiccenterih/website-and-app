<?php

declare(strict_types=1);

namespace App\Models;

final class CourseEnquiry extends Model
{
    protected static string $table = 'course_enquiries';

    public static function countNew(): int
    {
        return (int) static::db()->fetchColumn(
            "SELECT COUNT(*) FROM course_enquiries WHERE status = 'new'"
        );
    }

    public static function recentCountForIp(string $ip): int
    {
        return (int) static::db()->fetchColumn(
            'SELECT COUNT(*) FROM course_enquiries WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)',
            [$ip]
        );
    }

    public static function latest(): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM course_enquiries ORDER BY created_at DESC, id DESC'
        );
    }

    /**
     * @param list<int|string> $ids
     * @return list<array<string, mixed>>
     */
    public static function byIds(array $ids): array
    {
        $clean = [];
        foreach ($ids as $id) {
            $n = (int) $id;
            if ($n > 0) {
                $clean[$n] = $n;
            }
        }
        $clean = array_values($clean);
        if (!$clean) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($clean), '?'));
        return static::db()->fetchAll(
            "SELECT * FROM course_enquiries WHERE id IN ($placeholders) ORDER BY created_at DESC, id DESC",
            $clean
        );
    }

    public static function markContacted(int $id): void
    {
        static::db()->update('course_enquiries', [
            'status' => 'contacted',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
    }
}

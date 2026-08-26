<?php

declare(strict_types=1);

namespace App\Models;

final class Activity extends Model
{
    protected static string $table = 'social_activities';

    public static function published(): array
    {
        return static::db()->fetchAll(
            'SELECT a.*, s.name AS section_name, s.slug AS section_slug
             FROM social_activities a
             LEFT JOIN social_activity_sections s ON s.id = a.section_id
             WHERE a.status = ?
             ORDER BY s.sort_order ASC, a.sort_order ASC, a.id ASC',
            ['published']
        );
    }

    /**
     * @return list<array{section:array<string,mixed>,activities:list<array<string,mixed>>}>
     */
    public static function publishedGrouped(): array
    {
        $sections = ActivitySection::published();
        $items = self::published();
        $bySection = [];
        foreach ($items as $row) {
            $sid = (int) ($row['section_id'] ?? 0);
            $bySection[$sid][] = $row;
        }
        $out = [];
        foreach ($sections as $section) {
            $list = $bySection[(int) $section['id']] ?? [];
            if ($list === []) {
                continue;
            }
            $out[] = ['section' => $section, 'activities' => $list];
        }
        return $out;
    }

    /**
     * @return list<array{section:array<string,mixed>,activities:list<array<string,mixed>>}>
     */
    public static function groupedForAdmin(): array
    {
        $sections = ActivitySection::all('sort_order ASC, id ASC');
        $items = static::db()->fetchAll(
            'SELECT a.*, s.name AS section_name
             FROM social_activities a
             LEFT JOIN social_activity_sections s ON s.id = a.section_id
             ORDER BY s.sort_order ASC, a.sort_order ASC, a.id ASC'
        );
        $bySection = [];
        $unsectioned = [];
        foreach ($items as $row) {
            $sid = (int) ($row['section_id'] ?? 0);
            if ($sid === 0) {
                $unsectioned[] = $row;
            } else {
                $bySection[$sid][] = $row;
            }
        }
        $out = [];
        foreach ($sections as $section) {
            $out[] = ['section' => $section, 'activities' => $bySection[(int) $section['id']] ?? []];
        }
        if ($unsectioned !== []) {
            $out[] = [
                'section' => [
                    'id' => 0,
                    'name' => 'Unassigned',
                    'slug' => '',
                    'kicker' => '',
                    'lead' => '',
                    'sort_order' => 999,
                    'status' => 'draft',
                ],
                'activities' => $unsectioned,
            ];
        }
        return $out;
    }

    public static function featured(int $limit = 6): array
    {
        return static::db()->fetchAll(
            'SELECT a.*, s.name AS section_name
             FROM social_activities a
             LEFT JOIN social_activity_sections s ON s.id = a.section_id
             WHERE a.status = ? AND a.featured = 1
             ORDER BY s.sort_order ASC, a.sort_order ASC, a.id ASC LIMIT ' . (int) $limit,
            ['published']
        );
    }

    public static function bySlug(string $slug): ?array
    {
        return static::db()->fetch(
            'SELECT a.*, s.name AS section_name, s.slug AS section_slug
             FROM social_activities a
             LEFT JOIN social_activity_sections s ON s.id = a.section_id
             WHERE a.slug = ? AND a.status = ? LIMIT 1',
            [$slug, 'published']
        );
    }

    public static function images(int $id): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM social_activity_images WHERE activity_id = ? ORDER BY sort_order ASC, id ASC',
            [$id]
        );
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM social_activities WHERE slug = ?';
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

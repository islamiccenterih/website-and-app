<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\SitePages;
use App\I18n\FaithTerms;
use App\Models\Setting;

/**
 * One-time: store the bilingual text the English site already shows,
 * so later admin edits are saved and displayed exactly.
 */
final class ContentTerms
{
    public static function bake(): void
    {
        self::bakePageCopy();
        self::bakeNav('header_nav');
        self::bakeNav('footer_links');
        self::bakeSettings();
        self::bakeTables();
        setting_cache_clear();
    }

    private static function bakePageCopy(): void
    {
        $all = json_setting('page_copy');
        foreach (SitePages::all() as $page) {
            $copyKey = (string) ($page['copy'] ?? '');
            if ($copyKey === '') {
                continue;
            }
            if (!isset($all[$copyKey]) || !is_array($all[$copyKey])) {
                $all[$copyKey] = [];
            }
            $defaults = SitePages::copyDefaults($copyKey);
            $fields = is_array($page['fields'] ?? null) ? $page['fields'] : array_keys($defaults);
            foreach ($fields as $field) {
                $field = (string) $field;
                if ($field === 'body' || $field === 'updated') {
                    continue;
                }
                $current = trim((string) ($all[$copyKey][$field] ?? ''));
                $base = $current !== '' ? $current : (string) ($defaults[$field] ?? '');
                if ($base !== '') {
                    $all[$copyKey][$field] = FaithTerms::apply($base);
                }
            }
        }
        Setting::put('page_copy', json_encode($all, JSON_UNESCAPED_UNICODE));
    }

    private static function bakeNav(string $key): void
    {
        $links = $key === 'footer_links' ? footer_links_all() : header_nav_all();
        foreach ($links as $i => $link) {
            $label = trim((string) ($link['label'] ?? ''));
            if ($label !== '') {
                $links[$i]['label'] = FaithTerms::apply($label);
            }
        }
        Setting::put($key, json_encode($links, JSON_UNESCAPED_UNICODE));
    }

    private static function bakeSettings(): void
    {
        $skip = [
            'logo_image' => true,
            'faith_terms' => true,
            'page_copy' => true,
            'header_nav' => true,
            'footer_links' => true,
        ];
        $rows = Database::get()->fetchAll('SELECT setting_key, setting_value FROM settings');
        foreach ($rows as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if ($key === '' || isset($skip[$key]) || skip_public_copy_key($key)) {
                continue;
            }
            $raw = (string) ($row['setting_value'] ?? '');
            if ($raw === '') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $baked = self::walk($decoded, $key);
                Setting::put($key, json_encode($baked, JSON_UNESCAPED_UNICODE));
                continue;
            }
            Setting::put($key, FaithTerms::apply($raw));
        }
    }

    private static function bakeTables(): void
    {
        $map = [
            'courses' => ['title', 'short_description', 'full_description', 'additional_info', 'duration'],
            'social_activity_sections' => ['name', 'kicker', 'lead'],
            'social_activities' => ['title', 'short_description', 'full_description'],
            'social_activity_images' => ['caption'],
            'gallery_categories' => ['name', 'description'],
            'gallery_images' => ['title', 'alt_text'],
            'about_sections' => ['title', 'content', 'extra_json'],
            'founders' => ['name', 'designation', 'biography'],
            'home_sections' => ['title', 'subtitle', 'content', 'extra_json'],
            'programs' => ['title', 'short_description'],
            'calendar_months' => ['title'],
            'calendar_events' => ['title', 'description'],
            'fatawa' => ['title_en', 'body_en'],
            'center_updates' => ['title', 'excerpt', 'body_html'],
            'live_classes' => ['title'],
            'course_images' => ['caption'],
        ];
        $db = Database::get();
        foreach ($map as $table => $columns) {
            if (!self::tableExists($table)) {
                continue;
            }
            $quoted = [];
            foreach ($columns as $column) {
                if (self::columnExists($table, $column)) {
                    $quoted[] = $column;
                }
            }
            if ($quoted === []) {
                continue;
            }
            $rows = $db->fetchAll('SELECT `id`, `' . implode('`, `', $quoted) . '` FROM `' . $table . '`');
            foreach ($rows as $row) {
                $patch = [];
                foreach ($quoted as $column) {
                    $value = $row[$column] ?? null;
                    if (!is_string($value) || $value === '') {
                        continue;
                    }
                    $next = self::bakeColumn($table, $column, $value);
                    if ($next !== $value) {
                        $patch[$column] = $next;
                    }
                }
                if ($patch !== []) {
                    $db->update($table, $patch, 'id = ?', [(int) $row['id']]);
                }
            }
        }
    }

    private static function bakeColumn(string $table, string $column, string $value): string
    {
        if ($column === 'extra_json' || str_ends_with($column, '_json')) {
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                return $value;
            }
            return json_encode(self::walk($decoded, $column), JSON_UNESCAPED_UNICODE) ?: $value;
        }
        $baked = FaithTerms::apply($value);
        return self::fit($table, $column, $baked);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function walk(mixed $value, string $key)
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $childKey => $child) {
                $out[$childKey] = self::walk($child, is_string($childKey) ? $childKey : $key);
            }
            return $out;
        }
        if (is_string($value) && $value !== '' && !skip_public_copy_key($key)) {
            return FaithTerms::apply($value);
        }
        return $value;
    }

    private static function fit(string $table, string $column, string $value): string
    {
        $len = Database::get()->fetchColumn(
            "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
            [$table, $column]
        );
        if (!is_numeric($len) || (int) $len <= 0) {
            return $value;
        }
        $max = (int) $len;
        if (mb_strlen($value) <= $max) {
            return $value;
        }
        return mb_substr($value, 0, $max);
    }

    private static function tableExists(string $table): bool
    {
        return (int) Database::get()->fetchColumn(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?",
            [$table]
        ) > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        return (int) Database::get()->fetchColumn(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
            [$table, $column]
        ) > 0;
    }
}

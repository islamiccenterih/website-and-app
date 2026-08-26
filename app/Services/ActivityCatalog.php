<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivitySection;
use App\Models\Setting;

/**
 * Social Activities catalogue for Islamic Center Information Hub.
 * Programme copy lives in ActivityCopy; images stay as SVG placeholders.
 */
final class ActivityCatalog
{
    public static function sync(): void
    {
        $db = Activity::db();
        foreach (Activity::all('id ASC') as $row) {
            foreach (Activity::images((int) $row['id']) as $img) {
                $db->delete('social_activity_images', 'id = ?', [(int) $img['id']]);
            }
            Activity::deleteById((int) $row['id']);
        }

        $order = 0;
        foreach (self::sections() as $section) {
            $order++;
            $existing = ActivitySection::bySlug($section['slug']);
            $payload = [
                'name' => $section['name'],
                'slug' => $section['slug'],
                'kicker' => mb_substr((string) $section['kicker'], 0, 80),
                'lead' => mb_substr((string) $section['lead'], 0, 400),
                'sort_order' => $order,
                'status' => 'published',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($existing) {
                ActivitySection::updateById((int) $existing['id'], $payload);
                $sectionId = (int) $existing['id'];
            } else {
                $payload['created_at'] = date('Y-m-d H:i:s');
                $sectionId = ActivitySection::create($payload);
            }
            $image = self::image($section['slug'], $section['name']);
            $extra = self::image($section['slug'] . '-more', $section['name']);
            $itemOrder = 0;
            foreach ($section['items'] as $item) {
                $itemOrder++;
                $slug = slugify($item['title']);
                $found = $db->fetch('SELECT id FROM social_activities WHERE slug = ? LIMIT 1', [$slug]);
                $row = [
                    'title' => $item['title'],
                    'slug' => $slug,
                    'section_id' => $sectionId,
                    'short_description' => mb_substr((string) $item['short'], 0, 500),
                    'full_description' => $item['full'],
                    'event_date' => $item['date'] ?? null,
                    'event_year' => $item['year'] ?? null,
                    'main_image' => $image,
                    'status' => 'published',
                    'featured' => !empty($item['featured']) ? 1 : 0,
                    'sort_order' => $itemOrder,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($found) {
                    Activity::updateById((int) $found['id'], $row);
                    $id = (int) $found['id'];
                } else {
                    $row['created_at'] = date('Y-m-d H:i:s');
                    $id = Activity::create($row);
                }
                $hasExtra = $db->fetchColumn(
                    'SELECT COUNT(*) FROM social_activity_images WHERE activity_id = ?',
                    [$id]
                );
                if ((int) $hasExtra === 0) {
                    $db->insert('social_activity_images', [
                        'activity_id' => $id,
                        'image_path' => $extra,
                        'caption' => $item['title'],
                        'sort_order' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $copy = json_setting('page_copy');
        if (!isset($copy['activities']) || !is_array($copy['activities'])) {
            $copy['activities'] = [];
        }
        $copy['activities']['title'] = 'Social Activities in Firozabad';
        $copy['activities']['kicker'] = 'Islamic Center Information Hub';
        $copy['activities']['lead'] = self::pageLead();
        $copy['activities']['inner_title'] = '';
        $copy['activities']['inner_kicker'] = 'Programmes';
        $copy['activities']['detail_kicker'] = 'Programme details';
        Setting::put('page_copy', json_encode($copy, JSON_UNESCAPED_UNICODE));
    }

    public static function pageLead(): string
    {
        return 'Workshops, seminars, conferences, competitions, welfare and awareness programmes at Islamic Center Information Hub, Madina Colony — Deen, knowledge and character for students and families.';
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(): array
    {
        return ActivityCopy::sections();
    }

    private static function image(string $slug, string $label): string
    {
        $rel = 'assets/img/activity-' . $slug . '.svg';
        $path = PUBLIC_PATH . '/' . $rel;
        if (!is_file($path)) {
            $safe = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#0e2a22"/>
      <stop offset="1" stop-color="#1e4a3b"/>
    </linearGradient>
    <pattern id="p" width="56" height="56" patternUnits="userSpaceOnUse">
      <path d="M28 4 L52 28 L28 52 L4 28 Z" fill="none" stroke="#c9a227" stroke-width="0.7" opacity="0.28"/>
    </pattern>
  </defs>
  <rect width="1200" height="800" fill="url(#g)"/>
  <rect width="1200" height="800" fill="url(#p)"/>
  <g transform="translate(600,340)" fill="#c9a227" opacity="0.55">
    <path d="M0 -101 L18 -18 L101 0 L18 18 L0 101 L-18 18 L-101 0 L-18 -18 Z"/>
  </g>
  <rect x="40" y="40" width="1120" height="720" fill="none" stroke="#c9a227" stroke-width="1.5" opacity="0.45"/>
  <text x="50%" y="62%" text-anchor="middle" fill="#f4ead6" font-family="Georgia, serif" font-size="36">{$safe}</text>
</svg>
SVG;
            @file_put_contents($path, $svg);
        }
        return $rel;
    }
}

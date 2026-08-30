<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AboutSection;

final class AboutController extends BaseController
{
    public function index(): void
    {
        redirect('/admin/pages/about');
    }

    public function update(): void
    {
        $this->requireCsrf();
        \App\Core\SitePages::saveMenuFromRequest('about');
        $keys = ['page_hero', 'foundation', 'history', 'mission', 'vision', 'who_we_are'];
        $existing = AboutSection::keyed();
        foreach ($keys as $key) {
            $row = $existing[$key] ?? [];
            try {
                $image = $this->storeImage('image_' . $key, 'about', $row['image'] ?? null);
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('/admin/pages/about');
            }
            $decoded = json_decode((string) ($row['extra_json'] ?? ''), true);
            $extra = is_array($decoded) ? $decoded : [];
            $extra['kicker'] = trim((string) ($_POST['kicker'][$key] ?? ''));
            if ($key === 'foundation') {
                $extra['established'] = trim((string) ($_POST['established'] ?? ''));
                $extra['location'] = trim((string) ($_POST['location'] ?? ''));
            }
            if ($key === 'history') {
                $years = $_POST['timeline_year'] ?? [];
                $titles = $_POST['timeline_title'] ?? [];
                $texts = $_POST['timeline_text'] ?? [];
                $timeline = [];
                foreach ($years as $i => $year) {
                    if (trim((string) $year) === '' && trim((string) ($titles[$i] ?? '')) === '') {
                        continue;
                    }
                    $timeline[] = [
                        'year' => trim((string) $year),
                        'title' => trim((string) ($titles[$i] ?? '')),
                        'text' => trim((string) ($texts[$i] ?? '')),
                    ];
                }
                $extra['timeline'] = $timeline;
            }
            $title = trim((string) ($_POST['title'][$key] ?? $row['title'] ?? ''));
            if ($title === '') {
                $title = (string) ($row['title'] ?? ucwords(str_replace('_', ' ', $key)));
            }
            $extra = is_array($extra) ? store_copy_tree($extra) : $extra;
            AboutSection::upsert($key, [
                'title' => faith_terms_store($title),
                'content' => faith_terms_store((string) ($_POST['content'][$key] ?? '')),
                'image' => $image,
                'extra_json' => json_encode($extra, JSON_UNESCAPED_UNICODE),
            ]);
        }
        flash('success', 'Page saved.');
        redirect('/admin/pages/about');
    }
}

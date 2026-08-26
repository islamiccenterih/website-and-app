<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\HomeSection;

final class HomeController extends BaseController
{
    /** @var list<string> */
    private const KEYS = ['hero', 'about_preview', 'programs_intro', 'pillars', 'courses_intro', 'activities_intro', 'gallery_intro', 'cta'];

    public function index(): void
    {
        redirect('/admin/pages/home');
    }

    public function update(): void
    {
        $this->requireCsrf();
        \App\Core\SitePages::saveMenuFromRequest('home');
        $existing = HomeSection::keyed();
        foreach (self::KEYS as $key) {
            $row = $existing[$key] ?? [];
            try {
                $image = $this->storeImage('image_' . $key, 'home', $row['image'] ?? null);
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('/admin/pages/home');
            }
            $extra = json_decode((string) ($row['extra_json'] ?? ''), true);
            $extra = is_array($extra) ? $extra : [];
            if ($key === 'hero') {
                $extra['cta_label'] = trim((string) ($_POST['hero_cta_label'] ?? ''));
                $extra['cta_url'] = trim((string) ($_POST['hero_cta_url'] ?? '/courses'));
                $extra['cta2_label'] = trim((string) ($_POST['hero_cta2_label'] ?? ''));
                $extra['cta2_url'] = trim((string) ($_POST['hero_cta2_url'] ?? '/contact-us'));
                $extra['arabic'] = trim((string) ($_POST['hero_arabic'] ?? ''));
            }
            if ($key === 'about_preview') {
                $extra['cta_label'] = trim((string) ($_POST['about_cta_label'] ?? 'Learn More'));
                $extra['points'] = array_values(array_filter(array_map(
                    static fn($v) => trim((string) $v),
                    $_POST['about_point'] ?? []
                )));
            }
            if ($key === 'cta') {
                $extra['cta_label'] = trim((string) ($_POST['cta_label'] ?? ''));
                $extra['cta_url'] = trim((string) ($_POST['cta_url'] ?? '/contact-us'));
            }
            if ($key === 'courses_intro') {
                $extra['more_label'] = trim((string) ($_POST['courses_more_label'] ?? 'View all courses'));
                $extra['more_url'] = trim((string) ($_POST['courses_more_url'] ?? '/courses'));
            }
            if ($key === 'activities_intro') {
                $extra['more_label'] = trim((string) ($_POST['activities_more_label'] ?? 'All social activities'));
                $extra['more_url'] = trim((string) ($_POST['activities_more_url'] ?? '/social-activities'));
            }
            if ($key === 'gallery_intro') {
                $extra['more_label'] = trim((string) ($_POST['gallery_more_label'] ?? 'View Full Gallery'));
                $extra['more_url'] = trim((string) ($_POST['gallery_more_url'] ?? '/gallery'));
            }
            if ($key === 'pillars') {
                $titles = $_POST['pillar_title'] ?? [];
                $meanings = $_POST['pillar_meaning'] ?? [];
                $items = [];
                foreach ($titles as $i => $title) {
                    $title = trim((string) $title);
                    $meaning = trim((string) ($meanings[$i] ?? ''));
                    if ($title === '' && $meaning === '') {
                        continue;
                    }
                    $items[] = ['title' => $title, 'meaning' => $meaning];
                }
                $extra['items'] = $items;
            }
            $extra = is_array($extra) ? store_copy_tree($extra) : $extra;
            HomeSection::upsert($key, [
                'title' => faith_terms_store(trim((string) ($_POST['title'][$key] ?? ''))),
                'subtitle' => faith_terms_store(trim((string) ($_POST['subtitle'][$key] ?? ''))),
                'content' => faith_terms_store(trim((string) ($_POST['content'][$key] ?? ''))),
                'image' => $image,
                'extra_json' => $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null,
            ]);
        }
        flash('success', 'Page saved.');
        redirect('/admin/pages/home');
    }
}

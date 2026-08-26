<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;
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
        $keys = ['page_hero', 'foundation', 'founders_intro', 'history', 'mission', 'vision', 'who_we_are'];
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

    public function storeFounder(): void
    {
        $this->requireCsrf();
        try {
            $photo = $this->storeImage('photo', 'founders', null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/pages/about');
        }
        $this->db()->insert('founders', [
            'name' => faith_terms_store(trim((string) $_POST['name'])),
            'designation' => faith_terms_store(trim((string) ($_POST['designation'] ?? ''))),
            'biography' => faith_terms_store(trim((string) ($_POST['biography'] ?? ''))),
            'photo' => $photo,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Founder member added.');
        redirect('/admin/pages/about');
    }

    public function updateFounder(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM founders WHERE id = ?', [(int) $id]);
        if (!$row) {
            redirect('/admin/pages/about');
        }
        $photo = $this->storeImage('photo', 'founders', $row['photo']);
        $this->db()->update('founders', [
            'name' => faith_terms_store(trim((string) $_POST['name'])),
            'designation' => faith_terms_store(trim((string) ($_POST['designation'] ?? ''))),
            'biography' => faith_terms_store(trim((string) ($_POST['biography'] ?? ''))),
            'photo' => $photo,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $id]);
        flash('success', 'Founder updated.');
        redirect('/admin/pages/about');
    }

    public function destroyFounder(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM founders WHERE id = ?', [(int) $id]);
        if ($row) {
            Uploader::delete($row['photo']);
            $this->db()->delete('founders', 'id = ?', [(int) $id]);
        }
        flash('success', 'Founder removed.');
        redirect('/admin/pages/about');
    }
}

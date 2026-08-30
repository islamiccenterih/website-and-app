<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;
use App\Models\AboutSection;
use App\Services\CoordinatorService;

final class CoordinatorController extends BaseController
{
    public function index(): void
    {
        $intro = AboutSection::keyed()['founders_intro'] ?? [];
        $extra = json_decode((string) ($intro['extra_json'] ?? ''), true) ?: [];
        $this->screen('admin/coordinators/index', [
            'pageTitle' => 'Coordinator Info — Admin',
            'intro' => $intro,
            'introExtra' => is_array($extra) ? $extra : [],
            'coordinators' => CoordinatorService::all(),
        ]);
    }

    public function updateIntro(): void
    {
        $this->requireCsrf();
        $existing = AboutSection::keyed()['founders_intro'] ?? [];
        $decoded = json_decode((string) ($existing['extra_json'] ?? ''), true);
        $extra = is_array($decoded) ? $decoded : [];
        $extra['kicker'] = trim((string) ($_POST['kicker'] ?? 'Leadership'));
        AboutSection::upsert('founders_intro', [
            'title' => faith_terms_store(trim((string) ($_POST['title'] ?? 'Coordinators')) ?: 'Coordinators'),
            'content' => faith_terms_store(trim((string) ($_POST['content'] ?? ''))),
            'extra_json' => json_encode($extra, JSON_UNESCAPED_UNICODE),
        ]);
        flash('success', 'Coordinator section heading saved. It now shows on Home and About Us.');
        redirect('/admin/coordinators');
    }

    public function store(): void
    {
        $this->requireCsrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            flash('error', 'Please enter the coordinator’s name.');
            redirect('/admin/coordinators');
        }
        try {
            $photo = $this->storeImage('photo', 'founders', null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/coordinators');
        }
        $this->db()->insert('founders', [
            'name' => faith_terms_store($name),
            'designation' => faith_terms_store(trim((string) ($_POST['designation'] ?? ''))),
            'biography' => '',
            'highlights' => CoordinatorService::highlightsToStorage((string) ($_POST['highlights'] ?? '')),
            'photo' => $photo,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Coordinator added. The public Home and About Us pages will show this person.');
        redirect('/admin/coordinators');
    }

    public function update(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM founders WHERE id = ?', [(int) $id]);
        if (!$row) {
            redirect('/admin/coordinators');
        }
        try {
            $photo = $this->storeImage('photo', 'founders', $row['photo']);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/coordinators');
        }
        if (!empty($_POST['remove_photo'])) {
            Uploader::delete($row['photo']);
            $photo = null;
        }
        $this->db()->update('founders', [
            'name' => faith_terms_store(trim((string) ($_POST['name'] ?? ''))),
            'designation' => faith_terms_store(trim((string) ($_POST['designation'] ?? ''))),
            'biography' => '',
            'highlights' => CoordinatorService::highlightsToStorage((string) ($_POST['highlights'] ?? '')),
            'photo' => $photo,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $id]);
        flash('success', 'Coordinator updated. Home and About Us now show the new details.');
        redirect('/admin/coordinators');
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM founders WHERE id = ?', [(int) $id]);
        if ($row) {
            Uploader::delete($row['photo']);
            $this->db()->delete('founders', 'id = ?', [(int) $id]);
        }
        flash('success', 'Coordinator removed from Home and About Us.');
        redirect('/admin/coordinators');
    }
}

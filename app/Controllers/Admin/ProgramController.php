<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;

final class ProgramController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/programs/index', [
            'pageTitle' => 'Center programs — Admin',
            'programs' => present_copy_tree($this->db()->fetchAll('SELECT * FROM programs ORDER BY sort_order ASC, id ASC')),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        try {
            $image = $this->storeImage('image', 'programs', null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/programs');
        }
        $this->db()->insert('programs', [
            'title' => faith_terms_store(trim((string) $_POST['title'])),
            'short_description' => faith_terms_store(trim((string) ($_POST['short_description'] ?? ''))),
            'image' => $image,
            'link_url' => trim((string) ($_POST['link_url'] ?? '')) ?: null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Program added.');
        redirect('/admin/programs');
    }

    public function update(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM programs WHERE id = ?', [(int) $id]);
        if (!$row) {
            redirect('/admin/programs');
        }
        $image = $this->storeImage('image', 'programs', $row['image']);
        $this->db()->update('programs', [
            'title' => faith_terms_store(trim((string) $_POST['title'])),
            'short_description' => faith_terms_store(trim((string) ($_POST['short_description'] ?? ''))),
            'image' => $image,
            'link_url' => trim((string) ($_POST['link_url'] ?? '')) ?: null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $id]);
        flash('success', 'Program updated.');
        redirect('/admin/programs');
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM programs WHERE id = ?', [(int) $id]);
        if ($row) {
            Uploader::delete($row['image']);
            $this->db()->delete('programs', 'id = ?', [(int) $id]);
        }
        flash('success', 'Program deleted.');
        redirect('/admin/programs');
    }
}

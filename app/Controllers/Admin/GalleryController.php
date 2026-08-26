<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;

final class GalleryController extends BaseController
{
    public function index(): void
    {
        $images = $this->db()->fetchAll(
            'SELECT * FROM gallery_images ORDER BY sort_order ASC, id DESC'
        );
        $this->screen('admin/gallery/index', [
            'pageTitle' => 'Gallery — Admin',
            'images' => $images,
        ]);
    }

    public function storeImages(): void
    {
        $this->requireCsrf();
        if (empty($_FILES['images']) || empty($_FILES['images']['name'])) {
            flash('error', 'Choose one or more images to upload.');
            redirect('/admin/gallery');
        }
        $count = 0;
        try {
            foreach ($this->storeMany('images', 'gallery') as $path) {
                $this->db()->insert('gallery_images', [
                    'category_id' => null,
                    'image_path' => $path,
                    'title' => trim((string) ($_POST['title'] ?? '')) ?: null,
                    'alt_text' => trim((string) ($_POST['alt_text'] ?? '')) ?: null,
                    'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    'featured' => isset($_POST['featured']) ? 1 : 0,
                    'status' => 'published',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/gallery');
        }
        flash('success', $count ? "$count image(s) uploaded." : 'No images were selected.');
        redirect('/admin/gallery');
    }

    public function updateImage(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM gallery_images WHERE id = ?', [(int) $id]);
        if (!$row) {
            redirect('/admin/gallery');
        }
        $path = $row['image_path'];
        try {
            $path = $this->storeImage('image', 'gallery', $row['image_path']);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/gallery');
        }
        $this->db()->update('gallery_images', [
            'title' => trim((string) ($_POST['title'] ?? '')) ?: null,
            'alt_text' => trim((string) ($_POST['alt_text'] ?? '')) ?: null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'status' => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
            'image_path' => $path,
        ], 'id = ?', [(int) $id]);
        flash('success', 'Image updated.');
        redirect('/admin/gallery');
    }

    public function destroyImage(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM gallery_images WHERE id = ?', [(int) $id]);
        if ($row) {
            Uploader::delete($row['image_path']);
            $this->db()->delete('gallery_images', 'id = ?', [(int) $id]);
        }
        flash('success', 'Image deleted.');
        redirect('/admin/gallery');
    }
}

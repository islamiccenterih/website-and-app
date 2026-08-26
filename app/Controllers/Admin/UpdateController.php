<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;
use App\Models\CenterUpdate;
use App\Services\HtmlSanitizer;
use App\Services\MediaEmbed;

final class UpdateController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/updates/index', [
            'pageTitle' => 'Center Updates — Admin',
            'updates' => CenterUpdate::all('published_on DESC, id DESC'),
        ]);
    }

    public function create(): void
    {
        $this->screen('admin/updates/form', [
            'pageTitle' => 'Write an update — Admin',
            'item' => null,
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $item = CenterUpdate::find((int) $id);
        if (!$item) {
            redirect('/admin/updates');
        }
        $this->screen('admin/updates/form', [
            'pageTitle' => 'Edit update — Admin',
            'item' => present_copy_tree($item),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $item = CenterUpdate::find((int) $id);
        if ($item) {
            Uploader::delete($item['cover_image'] ?? null);
            CenterUpdate::deleteById((int) $id);
        }
        flash('success', 'Update deleted.');
        redirect('/admin/updates');
    }

    public function upload(): void
    {
        $this->requireCsrf();
        $kind = (string) ($_POST['kind'] ?? 'image');
        try {
            if ($kind === 'video') {
                $path = Uploader::storeVideo($_FILES['file'] ?? [], 'updates');
            } else {
                $path = Uploader::store($_FILES['file'] ?? [], 'updates');
            }
        } catch (\RuntimeException $e) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 422);
        }
        if (!$path) {
            json_response(['ok' => false, 'error' => 'Choose a file to upload.'], 422);
        }
        json_response([
            'ok' => true,
            'kind' => $kind === 'video' ? 'video' : 'image',
            'path' => $path,
            'url' => upload_url($path),
        ]);
    }

    public function embed(): void
    {
        $this->requireCsrf();
        $src = MediaEmbed::iframeSrc((string) ($_POST['url'] ?? ''));
        if (!$src) {
            json_response(['ok' => false, 'error' => 'Paste a YouTube or Vimeo link.'], 422);
        }
        json_response(['ok' => true, 'src' => $src]);
    }

    private function save(?int $id): void
    {
        $this->requireCsrf();
        $title = faith_terms_store(trim((string) ($_POST['title'] ?? '')));
        $body = faith_terms_store(HtmlSanitizer::clean((string) ($_POST['body_html'] ?? '')));
        $excerpt = faith_terms_store(trim((string) ($_POST['excerpt'] ?? '')));
        $publishedOn = trim((string) ($_POST['published_on'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishedOn)) {
            $publishedOn = date('Y-m-d');
        }
        if ($title === '') {
            flash('error', 'Give the update a title.');
            redirect($id ? '/admin/updates/' . $id . '/edit' : '/admin/updates/create');
        }
        if ($body === '') {
            flash('error', 'Write the update — text, a picture, a video, or all three.');
            redirect($id ? '/admin/updates/' . $id . '/edit' : '/admin/updates/create');
        }

        $existing = $id ? CenterUpdate::find($id) : null;
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $now = date('Y-m-d H:i:s');
        $postedSlug = trim((string) ($_POST['slug'] ?? ''));
        $slugBase = $postedSlug !== '' ? slugify($postedSlug) : slugify($title);
        $slug = CenterUpdate::uniqueSlug($slugBase, $id);

        $payload = [
            'slug' => $slug,
            'published_on' => $publishedOn,
            'title' => $title,
            'excerpt' => $excerpt !== '' ? mb_substr($excerpt, 0, 400) : null,
            'body_html' => $body,
            'cover_image' => $existing['cover_image'] ?? null,
            'status' => $status,
            'updated_at' => $now,
        ];
        if ($id && $existing) {
            CenterUpdate::updateById($id, $payload);
            flash('success', $status === 'published' ? 'Saved. It is on Center Updates exactly as composed.' : 'Draft saved.');
            redirect('/admin/updates/' . $id . '/edit');
        }
        $payload['created_at'] = $now;
        $newId = CenterUpdate::create($payload);
        flash('success', $status === 'published' ? 'Published on Center Updates.' : 'Draft saved.');
        redirect('/admin/updates/' . $newId . '/edit');
    }
}

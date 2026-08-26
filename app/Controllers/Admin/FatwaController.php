<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;
use App\Models\Fatwa;
use App\Models\FatwaQuestion;

final class FatwaController extends BaseController
{
    public function index(): void
    {
        $rows = Fatwa::all('issued_on DESC, id DESC');
        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['id']] = Fatwa::unansweredCount((int) $row['id']);
        }
        $this->screen('admin/fatawa/index', [
            'pageTitle' => 'Fatawa — Admin',
            'fatawa' => $rows,
            'unanswered' => $counts,
            'pendingTotal' => Fatwa::unansweredCount(),
        ]);
    }

    public function create(): void
    {
        $this->screen('admin/fatawa/form', [
            'pageTitle' => 'Publish a fatwa — Admin',
            'fatwa' => null,
            'questions' => [],
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $fatwa = Fatwa::find((int) $id);
        if (!$fatwa) {
            redirect('/admin/fatawa');
        }
        $this->screen('admin/fatawa/form', [
            'pageTitle' => 'Edit fatwa — Admin',
            'fatwa' => present_copy_tree($fatwa),
            'questions' => FatwaQuestion::forFatwa((int) $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $fatwa = Fatwa::find((int) $id);
        if ($fatwa) {
            foreach (FatwaQuestion::forFatwa((int) $id) as $question) {
                Uploader::delete($question['attachment_path'] ?? null);
            }
            Fatwa::deleteById((int) $id);
        }
        flash('success', 'Fatwa deleted.');
        redirect('/admin/fatawa');
    }

    public function answer(string $id): void
    {
        $this->requireCsrf();
        $question = FatwaQuestion::find((int) $id);
        if (!$question) {
            redirect('/admin/fatawa');
        }
        $answer = trim((string) ($_POST['answer'] ?? ''));
        if ($answer === '') {
            flash('error', 'Write an answer before saving.');
            redirect('/admin/fatawa/' . (int) $question['fatwa_id'] . '/edit#q-' . (int) $id);
        }
        $now = date('Y-m-d H:i:s');
        FatwaQuestion::updateById((int) $id, [
            'answer' => $answer,
            'status' => 'answered',
            'answered_at' => $now,
            'updated_at' => $now,
        ]);
        flash('success', 'Answer saved. It now appears under that question on the public fatwa page.');
        redirect('/admin/fatawa/' . (int) $question['fatwa_id'] . '/edit#q-' . (int) $id);
    }

    public function hide(string $id): void
    {
        $this->requireCsrf();
        $question = FatwaQuestion::find((int) $id);
        if (!$question) {
            redirect('/admin/fatawa');
        }
        FatwaQuestion::updateById((int) $id, [
            'status' => 'hidden',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Question hidden from the public page.');
        redirect('/admin/fatawa/' . (int) $question['fatwa_id'] . '/edit#questions');
    }

    public function destroyQuestion(string $id): void
    {
        $this->requireCsrf();
        $question = FatwaQuestion::find((int) $id);
        if ($question) {
            Uploader::delete($question['attachment_path'] ?? null);
            FatwaQuestion::deleteById((int) $id);
            flash('success', 'Question removed.');
            redirect('/admin/fatawa/' . (int) $question['fatwa_id'] . '/edit#questions');
        }
        redirect('/admin/fatawa');
    }

    private function save(?int $id): void
    {
        $this->requireCsrf();
        $issuedOn = trim((string) ($_POST['issued_on'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $issuedOn)) {
            $issuedOn = date('Y-m-d');
        }

        $titleAr = trim((string) ($_POST['title_ar'] ?? ''));
        $bodyAr = trim((string) ($_POST['body_ar'] ?? ''));
        $titleEn = trim((string) ($_POST['title_en'] ?? ''));
        $bodyEn = trim((string) ($_POST['body_en'] ?? ''));
        $titleHi = trim((string) ($_POST['title_hi'] ?? ''));
        $bodyHi = trim((string) ($_POST['body_hi'] ?? ''));

        $hasAny = ($titleAr !== '' && $bodyAr !== '')
            || ($titleEn !== '' && $bodyEn !== '')
            || ($titleHi !== '' && $bodyHi !== '');
        if (!$hasAny) {
            flash('error', 'Fill at least one language completely (title and text) — Arabic, English, or Hindi.');
            redirect($id ? '/admin/fatawa/' . $id . '/edit' : '/admin/fatawa/create');
        }

        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $now = date('Y-m-d H:i:s');
        $existing = $id ? Fatwa::find($id) : null;
        $postedSlug = trim((string) ($_POST['slug'] ?? ''));
        $slugBase = $postedSlug !== '' ? slugify($postedSlug) : $issuedOn;
        if ($slugBase === '' || $slugBase === 'item') {
            $slugBase = $issuedOn;
        }
        $slug = Fatwa::uniqueSlug($slugBase, $id);

        $payload = [
            'slug' => $slug,
            'issued_on' => $issuedOn,
            'title_ar' => $titleAr !== '' ? $titleAr : null,
            'body_ar' => $bodyAr !== '' ? $bodyAr : null,
            'title_en' => $titleEn !== '' ? faith_terms_store($titleEn) : null,
            'body_en' => $bodyEn !== '' ? faith_terms_store($bodyEn) : null,
            'title_hi' => $titleHi !== '' ? $titleHi : null,
            'body_hi' => $bodyHi !== '' ? $bodyHi : null,
            'status' => $status,
            'updated_at' => $now,
        ];
        if ($id && $existing) {
            Fatwa::updateById($id, $payload);
            flash('success', $status === 'published' ? 'Fatwa updated. It is on the public Fatawa page.' : 'Draft saved. Publish it when it should appear on the website.');
            redirect('/admin/fatawa/' . $id . '/edit');
        }

        $payload['created_at'] = $now;
        $newId = Fatwa::create($payload);
        flash('success', $status === 'published' ? 'Fatwa published. Visitors can read it and ask questions today.' : 'Draft saved.');
        redirect('/admin/fatawa/' . $newId . '/edit');
    }
}

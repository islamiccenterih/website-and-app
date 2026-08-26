<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;
use App\Models\CalendarMonth;
use App\Services\CalendarOcrService;

final class CalendarController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/calendar/index', [
            'pageTitle' => 'Islamic calendar — Admin',
            'months' => CalendarMonth::all('is_current DESC, sort_order ASC, id DESC'),
        ]);
    }

    public function create(): void
    {
        $this->screen('admin/calendar/form', [
            'pageTitle' => 'Create calendar month — Admin',
            'month' => null,
            'events' => [],
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $month = CalendarMonth::find((int) $id);
        if (!$month) {
            redirect('/admin/calendar');
        }
        $this->screen('admin/calendar/form', [
            'pageTitle' => 'Edit calendar — Admin',
            'month' => $month,
            'events' => CalendarMonth::events((int) $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $row = CalendarMonth::find((int) $id);
        if ($row) {
            Uploader::delete($row['image_path']);
            CalendarMonth::deleteById((int) $id);
        }
        flash('success', 'Calendar month deleted.');
        redirect('/admin/calendar');
    }

    public function storeEvent(string $id): void
    {
        $this->requireCsrf();
        $this->db()->insert('calendar_events', [
            'calendar_month_id' => (int) $id,
            'title' => trim((string) $_POST['title']),
            'hijri_date' => trim((string) ($_POST['hijri_date'] ?? '')) ?: null,
            'gregorian_date' => trim((string) ($_POST['gregorian_date'] ?? '')) ?: null,
            'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
            'is_important' => isset($_POST['is_important']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        flash('success', 'Event added. It appears on the public calendar only if this month is published.');
        redirect('/admin/calendar/' . (int) $id . '/edit');
    }

    public function destroyEvent(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT calendar_month_id FROM calendar_events WHERE id = ?', [(int) $id]);
        $this->db()->delete('calendar_events', 'id = ?', [(int) $id]);
        flash('success', 'Event removed.');
        redirect('/admin/calendar/' . (int) ($row['calendar_month_id'] ?? 0) . '/edit');
    }

    private function save(?int $id): void
    {
        $this->requireCsrf();
        $existing = $id ? CalendarMonth::find($id) : null;
        try {
            $image = $this->storeImage('image', 'calendar', $existing['image_path'] ?? null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect($id ? '/admin/calendar/' . $id . '/edit' : '/admin/calendar/create');
        }

        $ocrText = $existing['ocr_raw_text'] ?? null;
        $ocrNote = $existing['ocr_note'] ?? null;
        if ($image && (!empty($_POST['run_ocr']) || !$id)) {
            $ocr = (new CalendarOcrService())->extract($image);
            $ocrText = $ocr['raw_text'] ?: $ocrText;
            $ocrNote = $ocr['note'];
        }

        $payload = [
            'title' => trim((string) $_POST['title']),
            'hijri_year' => trim((string) ($_POST['hijri_year'] ?? '')) ?: null,
            'hijri_month' => trim((string) ($_POST['hijri_month'] ?? '')) ?: null,
            'gregorian_label' => trim((string) ($_POST['gregorian_label'] ?? '')) ?: null,
            'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
            'image_path' => $image,
            'ocr_raw_text' => trim((string) ($_POST['ocr_raw_text'] ?? '')) ?: $ocrText,
            'ocr_note' => $ocrNote,
            'status' => ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
            'is_current' => isset($_POST['is_current']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            CalendarMonth::updateById($id, $payload);
            $monthId = $id;
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $monthId = CalendarMonth::create($payload);
        }

        if ($payload['is_current']) {
            $this->db()->execute('UPDATE calendar_months SET is_current = 0 WHERE id != ?', [$monthId]);
        }

        flash('success', 'Calendar saved. Publish only after reviewing any extracted text.');
        redirect('/admin/calendar/' . $monthId . '/edit');
    }
}

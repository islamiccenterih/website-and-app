<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\CourseEnquiry;
use App\Services\XlsxExport;

final class EnquiryController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/enquiries/index', [
            'pageTitle' => 'Course enquiries — Admin',
            'enquiries' => CourseEnquiry::latest(),
        ]);
    }

    public function show(string $id): void
    {
        $enquiry = CourseEnquiry::find((int) $id);
        if (!$enquiry) {
            flash('error', 'That enquiry was not found.');
            redirect('/admin/enquiries');
        }

        $this->screen('admin/enquiries/show', [
            'pageTitle' => 'Enquiry from ' . $enquiry['name'] . ' — Admin',
            'enquiry' => $enquiry,
        ]);
    }

    public function markContacted(string $id): void
    {
        $this->requireCsrf();
        $enquiry = CourseEnquiry::find((int) $id);
        if (!$enquiry) {
            flash('error', 'That enquiry was not found.');
            redirect('/admin/enquiries');
        }
        CourseEnquiry::markContacted((int) $enquiry['id']);
        flash('success', 'Marked as contacted.');
        redirect('/admin/enquiries/' . (int) $enquiry['id']);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        CourseEnquiry::deleteById((int) $id);
        flash('success', 'Enquiry deleted.');
        redirect('/admin/enquiries');
    }

    public function export(): void
    {
        $this->requireCsrf();
        $raw = $_POST['ids'] ?? [];
        $ids = is_array($raw) ? $raw : [];
        $rows = CourseEnquiry::byIds($ids);
        if (!$rows) {
            flash('error', 'Tick the enquiries you want to export, or use Select all.');
            redirect('/admin/enquiries');
        }

        $sheet = [];
        foreach ($rows as $row) {
            $sheet[] = [
                (string) ($row['created_at'] ?? ''),
                (string) ($row['course_title'] ?? ''),
                (string) ($row['name'] ?? ''),
                (string) ($row['email'] ?? ''),
                (string) ($row['phone'] ?? ''),
                (string) ($row['whatsapp'] ?? ''),
                (string) ($row['address'] ?? ''),
                ($row['status'] ?? '') === 'contacted' ? 'Contacted' : 'New',
            ];
        }

        XlsxExport::download(
            'course-enquiries-' . date('Y-m-d') . '.xlsx',
            'Course enquiries',
            ['Received', 'Course', 'Name', 'Email', 'Phone', 'WhatsApp', 'Address', 'Status'],
            $sheet
        );
    }
}

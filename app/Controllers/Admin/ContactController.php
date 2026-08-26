<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;

final class ContactController extends BaseController
{
    public function index(): void
    {
        redirect('/admin/pages/contact');
    }

    public function update(): void
    {
        $this->requireCsrf();
        \App\Core\SitePages::saveMenuFromRequest('contact');
        try {
            $photo = $this->storeImage('contact_image', 'contact', setting('contact_image') ?: null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/pages/contact');
        }
        Setting::putMany([
            'contact_address' => trim((string) ($_POST['contact_address'] ?? '')),
            'contact_email' => trim((string) ($_POST['contact_email'] ?? '')),
            'contact_phone' => trim((string) ($_POST['contact_phone'] ?? '')),
            'contact_hours' => trim((string) ($_POST['contact_hours'] ?? '')),
            'contact_image' => $photo ?: '',
        ]);
        $page = \App\Core\SitePages::get('contact');
        $fields = is_array($page['fields'] ?? null) ? $page['fields'] : [];
        $postedCopy = is_array($_POST['copy'] ?? null) ? $_POST['copy'] : [];
        if ($fields !== []) {
            $out = [];
            foreach ($fields as $field) {
                $out[(string) $field] = trim((string) ($postedCopy[$field] ?? ''));
            }
            PagesController::putCopy('contact', $out);
        }
        flash('success', 'Page saved.');
        redirect('/admin/pages/contact');
    }
}

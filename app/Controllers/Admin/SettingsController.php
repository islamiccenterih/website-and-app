<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;

final class SettingsController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/settings/index', [
            'pageTitle' => 'Settings — Admin',
        ]);
    }

    public function update(): void
    {
        $this->requireCsrf();
        try {
            $logo = $this->storeImage('logo_image', 'home', setting('logo_image') ?: null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/admin/settings');
        }
        Setting::putMany([
            'site_name' => trim((string) ($_POST['site_name'] ?? 'Islamic Center')),
            'site_tagline' => trim((string) ($_POST['site_tagline'] ?? '')),
            'footer_note' => trim((string) ($_POST['footer_note'] ?? '')),
            'seo_home_title' => trim((string) ($_POST['seo_home_title'] ?? '')),
            'seo_home_description' => trim((string) ($_POST['seo_home_description'] ?? '')),
            'location_lat' => trim((string) ($_POST['location_lat'] ?? '')),
            'location_lng' => trim((string) ($_POST['location_lng'] ?? '')),
            'location_label' => trim((string) ($_POST['location_label'] ?? '')),
            'timezone' => trim((string) ($_POST['timezone'] ?? 'Asia/Kolkata')),
            'logo_image' => $logo ?: '',
            'faith_terms' => !empty($_POST['faith_terms']) ? '1' : '0',
        ]);
        flash('success', 'Settings saved.');
        redirect('/admin/settings');
    }
}

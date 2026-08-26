<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;
use App\Services\QiblaService;

final class QiblaController extends BaseController
{
    public function index(): void
    {
        redirect('/admin/pages/qibla');
    }

    public function update(): void
    {
        $this->requireCsrf();
        \App\Core\SitePages::saveMenuFromRequest('qibla');
        $lat = (float) str_replace(',', '.', (string) ($_POST['location_lat'] ?? '27.1591'));
        $lng = (float) str_replace(',', '.', (string) ($_POST['location_lng'] ?? '78.3957'));
        if ($lat < -90 || $lat > 90) {
            $lat = 27.1591;
        }
        if ($lng < -180 || $lng > 180) {
            $lng = 78.3957;
        }
        Setting::putMany([
            'location_lat' => (string) $lat,
            'location_lng' => (string) $lng,
            'location_label' => mb_substr(trim((string) ($_POST['location_label'] ?? '')), 0, 180),
        ]);
        PagesController::putCopy('qibla', [
            'kicker' => (string) ($_POST['kicker'] ?? ''),
            'title' => (string) ($_POST['title'] ?? ''),
            'lead' => (string) ($_POST['lead'] ?? ''),
            'help' => (string) ($_POST['help'] ?? ''),
        ]);
        flash('success', 'Page saved.');
        redirect('/admin/pages/qibla');
    }
}

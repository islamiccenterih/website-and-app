<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;
use App\Services\RamadanService;

final class RamadanController extends BaseController
{
    public function index(): void
    {
        redirect('/admin/pages/ramadan');
    }

    public function update(): void
    {
        $this->requireCsrf();
        \App\Core\SitePages::saveMenuFromRequest('ramadan');
        $duasIn = $_POST['dua'] ?? [];
        $duas = [];
        if (is_array($duasIn)) {
            foreach ($duasIn as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $duas[] = [
                    'title' => mb_substr(faith_terms_store(trim((string) ($row['title'] ?? ''))), 0, 160),
                    'arabic' => mb_substr(trim((string) ($row['arabic'] ?? '')), 0, 500),
                    'translit' => mb_substr(trim((string) ($row['translit'] ?? '')), 0, 400),
                    'meaning' => mb_substr(faith_terms_store(trim((string) ($row['meaning'] ?? ''))), 0, 400),
                ];
            }
        }
        $tools = json_setting('worship_tools');
        $tools['ramadan_city'] = mb_substr(trim((string) ($_POST['ramadan_city'] ?? 'Firozabad')), 0, 80);
        $tools['ramadan_state'] = mb_substr(trim((string) ($_POST['ramadan_state'] ?? 'Uttar Pradesh')), 0, 80);
        $tools['duas'] = $duas;
        Setting::put('worship_tools', json_encode($tools, JSON_UNESCAPED_UNICODE));
        PagesController::putCopy('ramadan', [
            'kicker' => (string) ($_POST['kicker'] ?? ''),
            'title' => (string) ($_POST['title'] ?? ''),
            'lead' => (string) ($_POST['lead'] ?? ''),
            'calendar_kicker' => (string) ($_POST['calendar_kicker'] ?? ''),
            'calendar_title' => (string) ($_POST['calendar_title'] ?? ''),
            'calendar_lead' => (string) ($_POST['calendar_lead'] ?? ''),
            'duas_kicker' => (string) ($_POST['duas_kicker'] ?? ''),
            'duas_title' => (string) ($_POST['duas_title'] ?? ''),
            'duas_lead' => (string) ($_POST['duas_lead'] ?? ''),
        ]);
        flash('success', 'Page saved.');
        redirect('/admin/pages/ramadan');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;
use App\Services\ZakatService;

final class ZakatController extends BaseController
{
    public function index(): void
    {
        redirect('/admin/pages/zakat');
    }

    public function update(): void
    {
        $this->requireCsrf();
        \App\Core\SitePages::saveMenuFromRequest('zakat');
        $method = (string) ($_POST['nisab_method'] ?? 'lower');
        if (!in_array($method, ['lower', 'gold', 'silver'], true)) {
            $method = 'lower';
        }
        $tools = json_setting('worship_tools');
        $tools['gold_nisab_g'] = $this->positive((string) ($_POST['gold_nisab_g'] ?? ''), 87.48);
        $tools['silver_nisab_g'] = $this->positive((string) ($_POST['silver_nisab_g'] ?? ''), 612.36);
        $tools['zakat_rate'] = $this->positive((string) ($_POST['zakat_rate'] ?? ''), 2.5);
        $tools['nisab_method'] = $method;
        $tools['zakat_notes'] = trim((string) ($_POST['zakat_notes'] ?? ''));
        Setting::put('worship_tools', json_encode($tools, JSON_UNESCAPED_UNICODE));
        PagesController::putCopy('zakat', [
            'kicker' => (string) ($_POST['kicker'] ?? ''),
            'title' => (string) ($_POST['title'] ?? ''),
            'lead' => (string) ($_POST['lead'] ?? ''),
            'notes_title' => (string) ($_POST['notes_title'] ?? ''),
            'notes' => (string) ($_POST['notes'] ?? ''),
        ]);
        flash('success', 'Page saved.');
        redirect('/admin/pages/zakat');
    }

    private function positive(string $raw, float $fallback): float
    {
        $n = (float) str_replace(',', '', $raw);
        return $n > 0 ? $n : $fallback;
    }
}

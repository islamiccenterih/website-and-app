<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\ZakatService;

final class ZakatController extends Controller
{
    public function index(): void
    {
        $svc = new ZakatService();
        $this->view('public/zakat', [
            'pageTitle' => page_copy('zakat', 'title', 'Zakat Calculator') . ' — ' . site_name(),
            'metaDescription' => page_copy('zakat', 'lead', 'Calculate this year’s zakat from live gold and silver nisab, with cash, gold, business, and debts.'),
            'spot' => $svc->snapshot(),
        ]);
    }

    public function nisab(): void
    {
        json_response((new ZakatService())->snapshot());
    }

    public function calculate(): void
    {
        $input = $_GET;
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? json_decode($raw, true) : null;
            $input = is_array($json) ? $json : $_POST;
        }
        json_response((new ZakatService())->calculate(is_array($input) ? $input : []));
    }
}

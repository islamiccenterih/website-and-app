<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\RamadanService;

final class RamadanController extends Controller
{
    public function index(): void
    {
        $city = trim((string) ($_GET['city'] ?? ''));
        $state = trim((string) ($_GET['state'] ?? ''));
        $page = (new RamadanService())->page($city, $state);
        $this->view('public/ramadan', [
            'pageTitle' => page_copy('ramadan', 'title', 'Ramadan Mode') . ' — ' . site_name(),
            'metaDescription' => page_copy('ramadan', 'lead', 'Sehri and Iftar for every city in India, a roza calendar, and the duas of Ramadan.'),
            'ramadan' => $page,
        ]);
    }

    public function api(): void
    {
        $city = trim((string) ($_GET['city'] ?? ''));
        $state = trim((string) ($_GET['state'] ?? ''));
        if (mb_strlen($city) > 80) {
            $city = mb_substr($city, 0, 80);
        }
        if (mb_strlen($state) > 80) {
            $state = mb_substr($state, 0, 80);
        }
        json_response((new RamadanService())->page($city, $state));
    }
}

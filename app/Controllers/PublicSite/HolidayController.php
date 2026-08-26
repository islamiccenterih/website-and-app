<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\IslamicHolidayService;

final class HolidayController extends Controller
{
    public function index(): void
    {
        $year = isset($_GET['year']) ? (int) $_GET['year'] : null;
        $page = (new IslamicHolidayService())->page($year);

        $this->view('public/holidays', [
            'pageTitle' => page_copy('holidays', 'title', 'Islamic Holidays') . ' — ' . site_name(),
            'metaDescription' => page_copy('holidays', 'lead', 'Eid ul-Fitr, Eid al-Adha, and every major Islamic holiday from 2026 to 2031, with Hijri and Gregorian dates.'),
            'page' => $page,
        ]);
    }
}

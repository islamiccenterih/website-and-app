<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\PrayerService;

final class PrayerController extends Controller
{
    public function times(): void
    {
        $city = trim((string) ($_GET['city'] ?? ''));
        $state = trim((string) ($_GET['state'] ?? ''));
        if (mb_strlen($city) > 80) {
            $city = mb_substr($city, 0, 80);
        }
        if (mb_strlen($state) > 80) {
            $state = mb_substr($state, 0, 80);
        }
        json_response((new PrayerService())->timings($city, $state));
    }
}

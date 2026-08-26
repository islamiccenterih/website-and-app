<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\QiblaService;

final class QiblaController extends Controller
{
    public function index(): void
    {
        $qibla = (new QiblaService())->for(null, null);
        $this->view('public/qibla', [
            'pageTitle' => page_copy('qibla', 'title', 'Qibla Direction') . ' — ' . site_name(),
            'metaDescription' => page_copy('qibla', 'lead', 'Find the direction of the Kaaba from your phone with a live compass.'),
            'qibla' => $qibla,
        ]);
    }

    public function api(): void
    {
        $latRaw = $_GET['lat'] ?? null;
        $lngRaw = $_GET['lng'] ?? null;
        $lat = is_numeric($latRaw) ? (float) $latRaw : null;
        $lng = is_numeric($lngRaw) ? (float) $lngRaw : null;
        json_response((new QiblaService())->for($lat, $lng));
    }
}

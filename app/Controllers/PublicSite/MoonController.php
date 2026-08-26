<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\MoonService;

final class MoonController extends Controller
{
    public function index(): void
    {
        $data = (new MoonService())->current();

        $this->view('public/moon', [
            'pageTitle' => 'Moon Timing — ' . site_name(),
            'metaDescription' => 'Current Islamic date, moon phase, moonrise, and moonset for the Islamic Center location.',
            'moon' => $data,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\PublicLiveService;

final class PublicLiveController extends Controller
{
    public function index(): void
    {
        $status = (new PublicLiveService())->publicStatus();
        $this->view('public/live', [
            'pageTitle' => page_copy('live', 'title', 'Live') . ' — ' . site_name(),
            'metaDescription' => page_copy(
                'live',
                'lead',
                'Watch the center live on this website — camera or screen, with no YouTube link.'
            ),
            'status' => $status,
        ]);
    }
}

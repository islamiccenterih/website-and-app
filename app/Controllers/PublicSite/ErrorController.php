<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;

final class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->view('public/404', [
            'pageTitle' => 'Page not found — ' . site_name(),
            'metaDescription' => 'The requested page could not be found.',
        ]);
    }
}

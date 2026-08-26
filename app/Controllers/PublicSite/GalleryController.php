<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Models\Gallery;

final class GalleryController extends Controller
{
    public function index(): void
    {
        $this->view('public/gallery', [
            'pageTitle' => 'Gallery — ' . site_name(),
            'metaDescription' => 'Photographs from Islamic Center programs, classes, and community gatherings.',
            'images' => Gallery::publishedImages(),
        ]);
    }
}

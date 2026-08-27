<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\LegalContent;

final class LegalController extends Controller
{
    public function privacy(): void
    {
        $this->show('privacy');
    }

    public function terms(): void
    {
        $this->show('terms');
    }

    public function disclaimer(): void
    {
        $this->show('disclaimer');
    }

    private function show(string $key): void
    {
        $doc = LegalContent::resolved($key);
        $this->view('public/legal', [
            'pageTitle' => page_copy($key, 'title', $doc['title']) . ' — ' . site_name(),
            'metaDescription' => page_copy($key, 'lead', $doc['meta']),
            'canonical' => absolute_url($doc['path']),
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $doc['title'],
                'url' => absolute_url($doc['path']),
                'dateModified' => LegalContent::isoDate((string) ($doc['updated'] ?? LegalContent::UPDATED)),
                'isPartOf' => [
                    '@type' => 'EducationalOrganization',
                    'name' => site_name(),
                    'url' => absolute_url('/'),
                ],
            ],
            'doc' => $doc,
            'others' => LegalContent::siblings($key),
            'updated' => (string) ($doc['updated'] ?? LegalContent::UPDATED),
        ]);
    }
}

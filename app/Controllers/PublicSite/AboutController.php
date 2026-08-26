<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Core\Html;
use App\Models\AboutSection;

final class AboutController extends Controller
{
    public function index(): void
    {
        $sections = AboutSection::keyed();
        $history = $sections['history'] ?? [];
        $timeline = json_decode((string) ($history['extra_json'] ?? ''), true);
        $foundation = $sections['foundation'] ?? [];
        $foundationMeta = json_decode((string) ($foundation['extra_json'] ?? ''), true) ?: [];

        $this->view('public/about', [
            'pageTitle' => (string) setting(
                'seo_about_title',
                'About Islamic Center Information Hub | Our Story Since 2013'
            ),
            'metaDescription' => (string) setting(
                'seo_about_description',
                'Islamic Center Information Hub began in 2013 at Abu Hurairah High School and now serves Madina Colony with Qur’an, character, ICA (2025), and Deen with Duniya.'
            ),
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'AboutPage',
                'name' => 'About Islamic Center Information Hub',
                'url' => absolute_url('/about-us'),
                'isPartOf' => [
                    '@type' => 'EducationalOrganization',
                    'name' => 'Islamic Center Information Hub',
                    'url' => absolute_url('/'),
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => 'Madina Colony',
                        'addressLocality' => 'Firozabad',
                        'addressRegion' => 'Uttar Pradesh',
                        'addressCountry' => 'IN',
                    ],
                ],
            ],
            'sections' => $sections,
            'timeline' => is_array($timeline['timeline'] ?? null) ? $timeline['timeline'] : [],
            'foundationMeta' => $foundationMeta,
            'founders' => $this->db()->fetchAll(
                'SELECT * FROM founders WHERE status = ? ORDER BY sort_order ASC, id ASC',
                ['published']
            ),
        ]);
    }
}

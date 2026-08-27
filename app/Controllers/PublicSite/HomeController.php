<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Gallery;
use App\Models\HomeSection;
use App\Services\PrayerService;
use App\Services\QuranDuaService;

final class HomeController extends Controller
{
    public function index(): void
    {
        $sections = HomeSection::keyed();
        $hero = $sections['hero'] ?? [];
        $about = $sections['about_preview'] ?? [];
        $cta = $sections['cta'] ?? [];

        $this->view('public/home', [
            'pageTitle' => (string) setting(
                'seo_home_title',
                'Islamic Center Information Hub | Faith, Knowledge & Character'
            ),
            'metaDescription' => (string) setting('seo_home_description', ''),
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'EducationalOrganization',
                'name' => 'Islamic Center Information Hub',
                'url' => absolute_url('/'),
                'description' => (string) setting('seo_home_description', ''),
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => 'Madina Colony',
                    'addressLocality' => 'Firozabad',
                    'addressRegion' => 'Uttar Pradesh',
                    'addressCountry' => 'IN',
                ],
                'areaServed' => 'Firozabad',
            ],
            'sections' => $sections,
            'hero' => $hero,
            'heroExtra' => json_decode((string) ($hero['extra_json'] ?? ''), true) ?: [],
            'aboutPreview' => $about,
            'aboutExtra' => json_decode((string) ($about['extra_json'] ?? ''), true) ?: [],
            'cta' => $cta,
            'ctaExtra' => json_decode((string) ($cta['extra_json'] ?? ''), true) ?: [],
            'programs' => $this->db()->fetchAll(
                'SELECT * FROM programs WHERE status = ? ORDER BY sort_order ASC, id ASC LIMIT 3',
                ['published']
            ),
            'courses' => Course::featured(6),
            'activities' => Activity::featured(6),
            'gallery' => Gallery::featured(8),
            'prayer' => (new PrayerService())->timings('Firozabad', 'Uttar Pradesh', false),
            'heroDua' => QuranDuaService::current(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Models\Activity;
use App\Models\Course;

final class SeoController extends Controller
{
    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /student\nSitemap: " . absolute_url('/sitemap.xml') . "\n";
    }

    public function sitemap(): void
    {
        $urls = [
            '/',
            '/about-us',
            '/gallery',
            '/courses',
            '/social-activities',
            '/contact-us',
            '/moon-timing',
            '/islamic-calendar',
            '/qibla-direction',
            '/zakat-calculator',
            '/ramadan-mode',
            '/fatawa',
            '/islamic-holidays',
            '/center-updates',
            '/live',
            '/privacy-policy',
            '/terms-and-conditions',
            '/disclaimer',
        ];
        foreach (Course::published() as $course) {
            $urls[] = '/courses/' . $course['slug'];
        }
        foreach (Activity::published() as $activity) {
            $urls[] = '/social-activities/' . $activity['slug'];
        }
        foreach (\App\Models\Fatwa::published() as $fatwa) {
            $urls[] = '/fatawa/' . $fatwa['slug'];
        }
        foreach (\App\Models\CenterUpdate::published() as $update) {
            $urls[] = '/center-updates/' . $update['slug'];
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $path) {
            echo '  <url><loc>' . e(absolute_url($path)) . '</loc></url>' . "\n";
        }
        echo '</urlset>';
    }
}

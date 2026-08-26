<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Core\Html;
use App\Models\Activity;

final class ActivityController extends Controller
{
    public function index(): void
    {
        $this->view('public/activities', [
            'pageTitle' => page_copy('activities', 'title', 'Social Activities') . ' | Islamic Center',
            'metaDescription' => page_copy('activities', 'lead', \App\Services\ActivityCatalog::pageLead()),
            'groups' => Activity::publishedGrouped(),
        ]);
    }

    public function show(string $slug): void
    {
        $activity = Activity::bySlug($slug);
        if (!$activity) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }

        $section = trim((string) ($activity['section_name'] ?? ''));
        $title = (string) $activity['title'];
        $pageTitle = $title . ' | Islamic Center Information Hub';
        if ($section !== '' && !str_contains(mb_strtolower($title), mb_strtolower($section))) {
            $pageTitle = $title . ' | ' . $section . ' in Firozabad';
        }

        $this->view('public/activity-detail', [
            'pageTitle' => $pageTitle,
            'metaDescription' => excerpt($activity['short_description'], 155),
            'ogImage' => upload_url($activity['main_image']),
            'activity' => $activity,
            'images' => Activity::images((int) $activity['id']),
            'bodyHtml' => Html::clean($activity['full_description'] ?? ''),
        ]);
    }
}

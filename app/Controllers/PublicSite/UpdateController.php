<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Models\CenterUpdate;
use App\Services\HtmlSanitizer;

final class UpdateController extends Controller
{
    public function index(): void
    {
        $today = CenterUpdate::today();
        $this->view('public/updates', [
            'pageTitle' => page_copy('updates', 'title', 'Center Updates') . ' — ' . site_name(),
            'metaDescription' => page_copy('updates', 'lead', 'Daily news from Islamic Center Information Hub — gatherings, classes, and notices.'),
            'today' => $today,
            'updates' => CenterUpdate::archive($today ? (int) $today['id'] : null),
        ]);
    }

    public function show(string $slug): void
    {
        $item = CenterUpdate::bySlug($slug);
        if (!$item) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }
        $more = [];
        foreach (CenterUpdate::published() as $row) {
            if ((int) $row['id'] === (int) $item['id']) {
                continue;
            }
            $more[] = $row;
            if (count($more) >= 4) {
                break;
            }
        }
        $this->view('public/update-detail', [
            'pageTitle' => (string) $item['title'] . ' — ' . site_name(),
            'metaDescription' => CenterUpdate::cardExcerpt($item, 160),
            'ogImage' => CenterUpdate::cardImage($item) ? upload_url(CenterUpdate::cardImage($item)) : null,
            'item' => $item,
            'body' => HtmlSanitizer::clean((string) ($item['body_html'] ?? '')),
            'more' => $more,
        ]);
    }
}

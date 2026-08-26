<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\PublicLiveService;

final class LiveNowController extends BaseController
{
    public function index(): void
    {
        $svc = new PublicLiveService();
        $live = $svc->current();
        $adminId = (int) (auth_user()['id'] ?? 0);
        if ($live && (int) $live['admin_id'] !== $adminId) {
            $svc->sweep((int) $live['id']);
            $live = $svc->current();
        }
        $this->screen('admin/live-now/index', [
            'pageTitle' => 'Live now — Admin',
            'live' => $live,
            'viewerCount' => $live ? $svc->viewerCount((int) $live['id']) : 0,
            'isHost' => $live && (int) $live['admin_id'] === $adminId,
            'secure' => request_is_https() || in_array($_SERVER['SERVER_NAME'] ?? '', ['127.0.0.1', 'localhost'], true),
        ]);
    }
}

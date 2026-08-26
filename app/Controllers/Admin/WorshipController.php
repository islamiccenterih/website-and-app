<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class WorshipController extends BaseController
{
    public function index(): void
    {
        redirect('/admin/pages/qibla');
    }

    public function update(): void
    {
        redirect('/admin/pages/qibla');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\I18n\Lang;

final class LanguageController extends Controller
{
    public function set(string $code): void
    {
        Lang::set($code);
        $back = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $path = is_string($back) && $back !== '' ? parse_url($back, PHP_URL_PATH) : null;
        $query = is_string($back) && $back !== '' ? parse_url($back, PHP_URL_QUERY) : null;
        if (is_string($path) && $path !== '' && !str_starts_with($path, '/language')) {
            redirect($path . (is_string($query) && $query !== '' ? '?' . $query : ''));
        }
        redirect('/');
    }
}

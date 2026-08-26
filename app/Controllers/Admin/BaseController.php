<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Uploader;

abstract class BaseController extends Controller
{
    protected function screen(string $template, array $data = []): void
    {
        $this->view($template, $data, 'dashboard');
    }

    protected function storeImage(string $field, string $subdir, ?string $existing = null): ?string
    {
        if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $existing;
        }
        $path = Uploader::store($_FILES[$field], $subdir);
        if ($path && $existing && $existing !== $path && !str_starts_with((string) $existing, 'assets/')) {
            Uploader::delete($existing);
        }
        return $path ?: $existing;
    }

    protected function storeMany(string $field, string $subdir): array
    {
        if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) {
            return [];
        }
        $saved = [];
        $count = count($_FILES[$field]['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $file = [
                'name' => $_FILES[$field]['name'][$i],
                'type' => $_FILES[$field]['type'][$i],
                'tmp_name' => $_FILES[$field]['tmp_name'][$i],
                'error' => $_FILES[$field]['error'][$i],
                'size' => $_FILES[$field]['size'][$i],
            ];
            $path = Uploader::store($file, $subdir);
            if ($path) {
                $saved[] = $path;
            }
        }
        return $saved;
    }
}

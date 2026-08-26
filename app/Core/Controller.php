<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'public'): void
    {
        $data['pageTitle'] = $data['pageTitle'] ?? site_name();
        $data['metaDescription'] = $data['metaDescription'] ?? '';
        $data['canonical'] = $data['canonical'] ?? absolute_url(current_path());
        $data['ogImage'] = $data['ogImage'] ?? absolute_url('/assets/img/og-default.svg');

        $viewFile = APP_PATH . '/Views/' . $template . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === '') {
            echo $content;
            return;
        }

        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        require $layoutFile;
    }

    protected function db(): Database
    {
        return Database::get();
    }

    protected function requireCsrf(): void
    {
        if (
            ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && empty($_POST)
            && empty($_FILES)
            && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
        ) {
            flash('error', 'The upload is larger than the server allows. Use a JPG or PNG under 10 MB.');
            redirect(current_path());
        }
        if (!verify_csrf()) {
            http_response_code(419);
            flash('error', 'Your session expired. Please try again.');
            redirect(current_path());
        }
    }

    protected function validate(array $rules, array $input): array
    {
        return Validator::make($rules, $input);
    }
}

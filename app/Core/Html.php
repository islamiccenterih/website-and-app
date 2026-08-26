<?php

declare(strict_types=1);

namespace App\Core;

final class Html
{
    public static function clean(?string $html): string
    {
        $html = (string) $html;
        if ($html === '') {
            return '';
        }

        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? '';
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? '';
        $html = preg_replace('#on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? '';
        $html = preg_replace('#javascript\s*:#i', '', $html) ?? '';

        $allowed = '<p><br><strong><b><em><i><ul><ol><li><h3><h4><a><blockquote><span>';
        $html = strip_tags($html, $allowed);

        $html = preg_replace_callback('#<a\s+[^>]*href=([\'"])(.*?)\1[^>]*>#i', static function (array $m): string {
            $href = $m[2];
            if (!preg_match('#^(https?:)?/#i', $href) && !str_starts_with($href, 'mailto:')) {
                $href = '#';
            }
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" rel="noopener noreferrer">';
        }, $html) ?? $html;

        return $html;
    }
}

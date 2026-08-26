<?php

declare(strict_types=1);

namespace App\Services;

final class HtmlSanitizer
{
    /** @var list<string> */
    private const TAGS = [
        'p', 'br', 'h2', 'h3', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'blockquote', 'hr', 'a', 'img', 'figure', 'figcaption', 'video', 'iframe',
        'span', 'div',
    ];

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;

        $dom = new \DOMDocument();
        $dom->encoding = 'UTF-8';
        $wrapped = '<div id="ic-root">' . $html . '</div>';
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $root = $dom->getElementById('ic-root');
        if (!$root) {
            return '';
        }
        self::scrub($root);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        return trim($out);
    }

    public static function excerpt(string $html, int $len = 180): string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        if (mb_strlen($text) <= $len) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $len)) . '…';
    }

    public static function firstImage(string $html): ?string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
            $src = html_entity_decode((string) $m[1], ENT_QUOTES, 'UTF-8');
            return self::safeMediaSrc($src) ? $src : null;
        }
        return null;
    }

    private static function scrub(\DOMNode $node): void
    {
        $remove = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMComment) {
                $remove[] = $child;
                continue;
            }
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if ($tag === 'span' && str_contains((string) $child->getAttribute('class'), 'compose-resize-handle')) {
                    $remove[] = $child;
                    continue;
                }
                if (!in_array($tag, self::TAGS, true)) {
                    $remove[] = $child;
                    continue;
                }
                self::scrubAttrs($child);
                if ($tag === 'a' && !$child->hasAttribute('href')) {
                    $remove[] = $child;
                    continue;
                }
                if (in_array($tag, ['img', 'video'], true) && !self::safeMediaSrc((string) $child->getAttribute('src'))) {
                    $remove[] = $child;
                    continue;
                }
                if ($tag === 'iframe' && !self::safeIframeSrc((string) $child->getAttribute('src'))) {
                    $remove[] = $child;
                    continue;
                }
                self::scrub($child);
            }
        }
        foreach ($remove as $dead) {
            $dead->parentNode?->removeChild($dead);
        }
    }

    private static function scrubAttrs(\DOMElement $el): void
    {
        $tag = strtolower($el->tagName);
        $keep = match ($tag) {
            'a' => ['href', 'title'],
            'img' => ['src', 'alt', 'style', 'width'],
            'video' => ['src', 'controls', 'playsinline', 'preload'],
            'iframe' => ['src', 'title', 'allow', 'allowfullscreen', 'loading'],
            'figure', 'div' => ['class', 'style'],
            default => [],
        };
        $remove = [];
        foreach ($el->attributes ?? [] as $attr) {
            $name = strtolower($attr->name);
            if (str_starts_with($name, 'on')) {
                $remove[] = $name;
                continue;
            }
            if ($name === 'style' && !in_array($tag, ['img', 'figure', 'div'], true)) {
                $remove[] = $name;
                continue;
            }
            if (!in_array($name, $keep, true)) {
                $remove[] = $name;
            }
        }
        foreach ($remove as $name) {
            $el->removeAttribute($name);
        }
        if ($tag === 'a') {
            $href = (string) $el->getAttribute('href');
            if (!preg_match('#^(https?:|/|#|mailto:)#i', $href)) {
                $el->removeAttribute('href');
            }
            $el->setAttribute('rel', 'noopener noreferrer');
        }
        if ($tag === 'video') {
            $el->setAttribute('controls', 'controls');
            $el->setAttribute('playsinline', 'playsinline');
        }
        if ($tag === 'iframe') {
            $el->setAttribute('loading', 'lazy');
            $el->setAttribute('allowfullscreen', 'allowfullscreen');
            $el->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
        }
        if ($tag === 'img' && $el->getAttribute('alt') === '') {
            $el->setAttribute('alt', '');
        }
        if (in_array($tag, ['img', 'figure', 'div'], true) && $el->hasAttribute('style')) {
            $safe = self::safeBoxStyle((string) $el->getAttribute('style'));
            if ($safe !== null) {
                $el->setAttribute('style', $safe);
            } else {
                $el->removeAttribute('style');
            }
        }
        if ($tag === 'img' && $el->hasAttribute('width')) {
            $width = (int) $el->getAttribute('width');
            if ($width < 80 || $width > 2000) {
                $el->removeAttribute('width');
            }
        }
        if (in_array($tag, ['figure', 'div'], true)) {
            $class = trim((string) $el->getAttribute('class'));
            $allowed = [];
            foreach (preg_split('/\s+/', $class) ?: [] as $part) {
                if (in_array($part, ['update-media', 'update-video', 'update-embed'], true)) {
                    $allowed[] = $part;
                }
            }
            if ($allowed) {
                $el->setAttribute('class', implode(' ', $allowed));
            } else {
                $el->removeAttribute('class');
            }
        }
    }

    /** Allow only a width on composed pictures so each image can be sized. */
    private static function safeBoxStyle(string $style): ?string
    {
        if (!preg_match('/(?:^|;)\s*width\s*:\s*([^;]+)/i', $style, $m)) {
            return null;
        }
        $val = strtolower(trim((string) $m[1]));
        if (preg_match('/^(\d{1,3}(?:\.\d+)?)%$/', $val, $p)) {
            $n = (float) $p[1];
            if ($n < 15 || $n > 100) {
                return null;
            }
            $pct = rtrim(rtrim(sprintf('%.1F', $n), '0'), '.');
            return 'width: ' . $pct . '%;';
        }
        if (preg_match('/^(\d{2,4})px$/', $val, $p)) {
            $n = (int) $p[1];
            if ($n < 80 || $n > 2000) {
                return null;
            }
            return 'width: ' . $n . 'px; max-width: 100%;';
        }
        return null;
    }

    public static function safeMediaSrc(string $src): bool
    {
        $src = html_entity_decode(trim($src), ENT_QUOTES, 'UTF-8');
        if ($src === '' || str_contains($src, '..')) {
            return false;
        }
        return (bool) preg_match('#^(/)?uploads/#', $src);
    }

    public static function safeIframeSrc(string $src): bool
    {
        $src = html_entity_decode(trim($src), ENT_QUOTES, 'UTF-8');
        return (bool) preg_match(
            '#^https://(www\.)?(youtube\.com/embed/|youtube-nocookie\.com/embed/|player\.vimeo\.com/video/)#i',
            $src
        );
    }
}

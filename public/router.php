<?php

declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file) && !is_dir($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $types = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
        'mp4' => 'video/mp4',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/mp4',
        'json' => 'application/json; charset=utf-8',
        'webmanifest' => 'application/manifest+json',
        'zip' => 'application/zip',
    ];
    if (isset($types[$ext])) {
        serve_static($file, $types[$ext]);
        return true;
    }
    return false;
}

require __DIR__ . '/index.php';

function serve_static(string $file, string $mime): void
{
    $size = filesize($file);
    if ($size === false) {
        http_response_code(404);
        return;
    }

    $start = 0;
    $end = $size - 1;
    $code = 200;
    $range = $_SERVER['HTTP_RANGE'] ?? '';
    if (is_string($range) && preg_match('/bytes=(\d*)-(\d*)/', $range, $match)) {
        if ($match[1] !== '') {
            $start = (int) $match[1];
        }
        if ($match[2] !== '') {
            $end = (int) $match[2];
        }
        if ($end >= $size) {
            $end = $size - 1;
        }
        if ($start > $end || $start < 0) {
            http_response_code(416);
            header('Content-Range: bytes */' . $size);
            return;
        }
        $code = 206;
    }

    $length = $end - $start + 1;
    http_response_code($code);
    header('Content-Type: ' . $mime);
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . $length);
    header('Cache-Control: public, max-age=604800');
    header('X-Content-Type-Options: nosniff');
    if (str_contains($mime, 'zip')) {
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Cache-Control: no-store');
    }
    if ($code === 206) {
        header("Content-Range: bytes {$start}-{$end}/{$size}");
    }

    $handle = fopen($file, 'rb');
    if ($handle === false) {
        http_response_code(500);
        return;
    }
    if ($start > 0) {
        fseek($handle, $start);
    }
    $left = $length;
    while ($left > 0 && !feof($handle)) {
        $chunk = fread($handle, min(8192, $left));
        if ($chunk === false) {
            break;
        }
        echo $chunk;
        $left -= strlen($chunk);
    }
    fclose($handle);
}

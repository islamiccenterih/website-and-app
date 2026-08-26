<?php

declare(strict_types=1);

namespace App\Core;

final class Uploader
{
    private const MIME_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private const DOC_MIME_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'text/plain' => 'txt',
    ];

    public static function store(array $file, string $subdir): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(self::uploadErrorMessage((int) $file['error']));
        }

        $max = (int) cfg('uploads.max_bytes', 10485760);
        if (($file['size'] ?? 0) > $max) {
            throw new \RuntimeException('The file is too large. Maximum size is ' . (int) round($max / 1048576) . ' MB.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp) || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('The file did not arrive on the server. Try a smaller JPG or PNG (under 10 MB).');
        }

        $info = @getimagesize($tmp);
        if ($info === false || empty($info['mime'])) {
            throw new \RuntimeException('Only image files (JPG, PNG, WebP, GIF) are allowed.');
        }

        $mime = strtolower((string) $info['mime']);
        if (!isset(self::MIME_MAP[$mime])) {
            throw new \RuntimeException('Only image files (JPG, PNG, WebP, GIF) are allowed.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = strtolower((string) $finfo->file($tmp));
        if ($detected !== $mime) {
            throw new \RuntimeException('The file type could not be verified.');
        }

        $ext = self::MIME_MAP[$mime];
        $name = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
        $subdir = trim($subdir, '/');
        $relativeDir = trim((string) cfg('uploads.dir', 'uploads'), '/') . '/' . $subdir;
        $destDir = PUBLIC_PATH . '/' . $relativeDir;

        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new \RuntimeException('Upload directory is not writable.');
        }

        $dest = $destDir . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('The file could not be saved.');
        }

        @chmod($dest, 0644);
        self::optimize($dest, $mime);

        return $relativeDir . '/' . $name;
    }

    /**
     * Images, PDF, Word, or plain text for fatwa questions.
     *
     * @return array{path:string,name:string,mime:string}|null
     */
    public static function storeAttachment(array $file, string $subdir): ?array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(self::uploadErrorMessage((int) $file['error']));
        }

        $max = (int) cfg('uploads.max_bytes', 10485760);
        if (($file['size'] ?? 0) > $max) {
            throw new \RuntimeException('The file is too large. Maximum size is ' . (int) round($max / 1048576) . ' MB.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp) || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('The file did not arrive on the server. Try again with a smaller file (under 10 MB).');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = strtolower((string) $finfo->file($tmp));
        $originalName = strtolower((string) ($file['name'] ?? ''));
        $nameExt = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if (!isset(self::DOC_MIME_MAP[$detected])) {
            if ($detected === 'application/zip' && $nameExt === 'docx') {
                $detected = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            } elseif (in_array($detected, ['application/x-pdf', 'application/acrobat'], true)) {
                $detected = 'application/pdf';
            } elseif ($detected === 'application/octet-stream' && in_array($nameExt, ['pdf', 'doc', 'docx', 'txt'], true)) {
                $aliases = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'txt' => 'text/plain',
                ];
                $detected = $aliases[$nameExt];
            } else {
                throw new \RuntimeException('Upload an image, PDF, Word document, or text file.');
            }
        }

        $ext = self::DOC_MIME_MAP[$detected];
        $original = basename((string) ($file['name'] ?? 'attachment.' . $ext));
        $original = preg_replace('/[^\p{L}\p{N}._-]+/u', '-', $original) ?: ('file.' . $ext);
        $name = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
        $subdir = trim($subdir, '/');
        $relativeDir = trim((string) cfg('uploads.dir', 'uploads'), '/') . '/' . $subdir;
        $destDir = PUBLIC_PATH . '/' . $relativeDir;

        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new \RuntimeException('Upload directory is not writable.');
        }

        $dest = $destDir . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('The file could not be saved.');
        }
        @chmod($dest, 0644);

        if (str_starts_with($detected, 'image/')) {
            self::optimize($dest, $detected);
        }

        return [
            'path' => $relativeDir . '/' . $name,
            'name' => mb_substr($original, 0, 180),
            'mime' => $detected,
        ];
    }

    public static function delete(?string $relative): void
    {
        if ($relative === null || $relative === '') {
            return;
        }
        if (str_contains($relative, '..')) {
            return;
        }
        if (str_starts_with(ltrim($relative, '/'), 'assets/')) {
            return;
        }
        $full = PUBLIC_PATH . '/' . ltrim($relative, '/');
        if (is_file($full)) {
            @unlink($full);
        }
        $thumb = self::thumbPath($full);
        if (is_file($thumb)) {
            @unlink($thumb);
        }
    }

    public static function thumbUrl(?string $relative): string
    {
        if (!$relative) {
            return upload_url(null);
        }
        $full = PUBLIC_PATH . '/' . ltrim($relative, '/');
        $thumb = self::thumbPath($full);
        if (is_file($thumb)) {
            $dir = dirname($relative);
            return asset('/' . $dir . '/' . basename($thumb));
        }
        return upload_url($relative);
    }

    private static function thumbPath(string $full): string
    {
        $pi = pathinfo($full);
        return ($pi['dirname'] ?? '') . '/' . ($pi['filename'] ?? 'file') . '-sm.' . ($pi['extension'] ?? 'jpg');
    }

    private static function optimize(string $path, string $mime): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            return;
        }
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'image/gif' => @imagecreatefromgif($path),
            default => null,
        };
        if (!$src) {
            return;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $max = 1600;
        if ($w > $max || $h > $max) {
            $scale = min($max / $w, $max / $h);
            $nw = (int) max(1, floor($w * $scale));
            $nh = (int) max(1, floor($h * $scale));
            $resized = imagecreatetruecolor($nw, $nh);
            self::preserveAlpha($resized, $mime);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            self::saveImage($resized, $path, $mime);
            imagedestroy($src);
            $src = $resized;
            $w = $nw;
            $h = $nh;
        }

        $tw = 480;
        if ($w > $tw) {
            $scale = $tw / $w;
            $nw = $tw;
            $nh = (int) max(1, floor($h * $scale));
            $thumb = imagecreatetruecolor($nw, $nh);
            self::preserveAlpha($thumb, $mime);
            imagecopyresampled($thumb, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            self::saveImage($thumb, self::thumbPath($path), $mime);
            imagedestroy($thumb);
        }
        imagedestroy($src);
    }

    private static function preserveAlpha($img, string $mime): void
    {
        if (in_array($mime, ['image/png', 'image/webp', 'image/gif'], true)) {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $transparent);
        }
    }

    private static function saveImage($img, string $path, string $mime): void
    {
        match ($mime) {
            'image/jpeg' => imagejpeg($img, $path, 82),
            'image/png' => imagepng($img, $path, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($img, $path, 82) : imagepng($img, $path, 6),
            'image/gif' => imagegif($img, $path),
            default => null,
        };
    }

    /**
     * Short MP4 / WebM clips for Center Updates.
     */
    public static function storeVideo(array $file, string $subdir): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(self::uploadErrorMessage((int) $file['error']));
        }

        $max = 32 * 1024 * 1024;
        if (($file['size'] ?? 0) > $max) {
            throw new \RuntimeException('The video is too large. Use an MP4 or WebM under 32 MB, or paste a YouTube link.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp) || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('The video did not arrive on the server.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = strtolower((string) $finfo->file($tmp));
        $map = [
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/quicktime' => 'mov',
        ];
        if (!isset($map[$detected])) {
            throw new \RuntimeException('Only MP4 or WebM video is allowed. You can also paste a YouTube or Vimeo link.');
        }

        $ext = $map[$detected];
        $name = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
        $subdir = trim($subdir, '/');
        $relativeDir = trim((string) cfg('uploads.dir', 'uploads'), '/') . '/' . $subdir;
        $destDir = PUBLIC_PATH . '/' . $relativeDir;
        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new \RuntimeException('Upload directory is not writable.');
        }
        $dest = $destDir . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('The video could not be saved.');
        }
        @chmod($dest, 0644);
        return $relativeDir . '/' . $name;
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The image is larger than the server allows. Use a JPG or PNG under 10 MB.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary folder for uploads.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not save the file. Check that public/uploads is writable.',
            UPLOAD_ERR_EXTENSION => 'A server extension blocked this upload.',
            default => 'The file could not be uploaded. Use JPG, PNG, WebP, or GIF under 10 MB.',
        };
    }
}

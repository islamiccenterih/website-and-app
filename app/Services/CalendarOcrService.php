<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Optional OCR helper for Islamic calendar images.
 * Shared hosting rarely has Tesseract. This service never publishes
 * extracted text automatically — admin review is always required.
 */
final class CalendarOcrService
{
    public function extract(?string $imageRelativePath): array
    {
        $result = [
            'available' => false,
            'raw_text' => '',
            'note' => 'Automatic text extraction is not enabled on this server. Upload the calendar image and enter dates manually, then review before publishing.',
        ];

        if (!$imageRelativePath) {
            return $result;
        }

        $full = PUBLIC_PATH . '/' . ltrim($imageRelativePath, '/');
        if (!is_file($full)) {
            $result['note'] = 'The uploaded image could not be found for extraction.';
            return $result;
        }

        $binaries = ['tesseract', '/usr/bin/tesseract', '/usr/local/bin/tesseract'];
        $binary = null;
        foreach ($binaries as $candidate) {
            $check = @shell_exec('command -v ' . escapeshellarg($candidate) . ' 2>/dev/null');
            if (is_string($check) && trim($check) !== '') {
                $binary = trim($check);
                break;
            }
            if (is_executable($candidate)) {
                $binary = $candidate;
                break;
            }
        }

        if ($binary === null) {
            return $result;
        }

        $outBase = STORAGE_PATH . '/tmp/ocr-' . bin2hex(random_bytes(4));
        if (!is_dir(STORAGE_PATH . '/tmp')) {
            @mkdir(STORAGE_PATH . '/tmp', 0755, true);
        }

        $cmd = escapeshellcmd($binary) . ' ' . escapeshellarg($full) . ' ' . escapeshellarg($outBase) . ' -l eng+ara 2>/dev/null';
        @exec($cmd, $output, $code);
        $textFile = $outBase . '.txt';
        if (is_file($textFile)) {
            $text = trim((string) file_get_contents($textFile));
            @unlink($textFile);
            $result['available'] = $text !== '';
            $result['raw_text'] = $text;
            $result['note'] = $text === ''
                ? 'No readable text was found. Enter calendar data manually and review before publishing.'
                : 'Text was extracted automatically. Review and correct it before publishing. OCR is not fully accurate.';
        }

        return $result;
    }
}

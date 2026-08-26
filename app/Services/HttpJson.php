<?php

declare(strict_types=1);

namespace App\Services;

final class HttpJson
{
    public static function get(string $url, int $timeout = 10, int $tries = 2): array
    {
        $last = null;
        for ($i = 0; $i < max(1, $tries); $i++) {
            try {
                $raw = self::fetch($url, $timeout);
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    throw new \RuntimeException('Invalid JSON from upstream.');
                }
                return $decoded;
            } catch (\Throwable $e) {
                $last = $e;
                if ($i + 1 < $tries) {
                    usleep(180000);
                }
            }
        }
        throw $last ?? new \RuntimeException('Upstream request failed.');
    }

    public static function fetch(string $url, int $timeout = 10): string
    {
        $timeout = max(3, $timeout);
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 4,
                    CURLOPT_CONNECTTIMEOUT => min(6, $timeout),
                    CURLOPT_TIMEOUT => $timeout,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'User-Agent: IslamicCenter/1.1',
                    ],
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ]);
                $raw = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);
                curl_close($ch);
                if (is_string($raw) && $raw !== '' && $code > 0 && $code < 400) {
                    return $raw;
                }
                if ($err !== '') {
                    throw new \RuntimeException('Upstream request failed: ' . $err);
                }
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'follow_location' => true,
                'max_redirects' => 4,
                'header' => "Accept: application/json\r\nUser-Agent: IslamicCenter/1.1\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
        if (!is_string($raw) || $raw === '') {
            throw new \RuntimeException('Upstream request failed.');
        }
        return $raw;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function write(string $file, array $data): void
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function read(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($file), true);
        return is_array($decoded) ? $decoded : null;
    }
}

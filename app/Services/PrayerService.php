<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Daily salah times from AlAdhan (no API key).
 * Method 1 = University of Islamic Sciences, Karachi; school 1 = Hanafi Asr.
 */
final class PrayerService
{
    public function defaultCity(): array
    {
        return ['name' => 'Firozabad', 'state' => 'Uttar Pradesh'];
    }

    public function timings(?string $city = null, ?string $state = null): array
    {
        $city = trim((string) $city);
        $state = trim((string) $state);
        if ($city === '') {
            $fallback = $this->defaultCity();
            $city = $fallback['name'];
            $state = $fallback['state'];
        }

        $tz = new \DateTimeZone('Asia/Kolkata');
        $now = new \DateTimeImmutable('now', $tz);
        $today = $now->format('Y-m-d');
        $dateParam = $now->format('d-m-Y');
        $cacheKey = 'prayer-' . md5(mb_strtolower($city . '|' . $state) . '|' . $today . '|imsak');
        $untilMidnight = max(60, $now->modify('tomorrow')->setTime(0, 0)->getTimestamp() - $now->getTimestamp());
        $cached = $this->cacheGet($cacheKey, $today, $untilMidnight);
        if ($cached !== null) {
            return $this->withLiveCurrent($cached, $now);
        }

        $query = http_build_query([
            'city' => $city,
            'country' => 'India',
            'method' => 1,
            'school' => 1,
        ]);
        $url = 'https://api.aladhan.com/v1/timingsByCity/' . $dateParam . '?' . $query;

        try {
            $payload = HttpJson::get($url, 10, 2);
            $times = $payload['data']['timings'] ?? [];
            $metaDate = $payload['data']['date'] ?? [];
            if (!$times || (int) ($payload['code'] ?? 0) !== 200) {
                throw new \RuntimeException('Prayer times were empty.');
            }
        } catch (\Throwable $e) {
            $stale = $this->cacheStale($cacheKey);
            if ($stale !== null) {
                $stale['stale'] = true;
                $stale['city'] = $city;
                $stale['state'] = $state;
                return $this->withLiveCurrent($stale, $now);
            }
            return [
                'ok' => false,
                'error' => 'Prayer times could not be loaded for this city. Try another city, or wait a moment.',
                'city' => $city,
                'state' => $state,
                'date' => $now->format('l, j F Y'),
                'for_date' => $today,
                'prayers' => [],
                'current' => null,
            ];
        }

        $prayers = [
            ['key' => 'fajr', 'name' => 'Fajr', 'time' => $this->formatTime($times['Fajr'] ?? '')],
            ['key' => 'zuhr', 'name' => 'Zuhr', 'time' => $this->formatTime($times['Dhuhr'] ?? '')],
            ['key' => 'asr', 'name' => 'Asr', 'time' => $this->formatTime($times['Asr'] ?? '')],
            ['key' => 'maghrib', 'name' => 'Maghrib', 'time' => $this->formatTime($times['Maghrib'] ?? '')],
            ['key' => 'isha', 'name' => 'Isha', 'time' => $this->formatTime($times['Isha'] ?? '')],
            ['key' => 'jummah', 'name' => 'Jummah', 'time' => $this->formatTime($times['Dhuhr'] ?? '')],
        ];

        $readable = trim((string) ($metaDate['readable'] ?? ''));
        $result = [
            'ok' => true,
            'error' => null,
            'city' => $city,
            'state' => $state,
            'date' => $readable !== '' ? $readable : $now->format('j F Y'),
            'weekday' => $now->format('l'),
            'for_date' => $today,
            'timezone' => 'Asia/Kolkata',
            'prayers' => $prayers,
            'current' => $this->currentFromPrayers($prayers, $now),
            'sunrise' => $this->formatTime($times['Sunrise'] ?? ''),
            'imsak' => $this->formatTime($times['Imsak'] ?? ''),
            'fajr' => $this->formatTime($times['Fajr'] ?? ''),
            'maghrib' => $this->formatTime($times['Maghrib'] ?? ''),
        ];
        $this->cacheSet($cacheKey, $result);
        return $result;
    }

    private function formatTime(string $raw): string
    {
        $raw = trim(explode(' ', $raw)[0] ?? '');
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', $raw, $m)) {
            return $raw;
        }
        $hour = (int) $m[1];
        $minute = $m[2];
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $hour12 = $hour % 12;
        if ($hour12 === 0) {
            $hour12 = 12;
        }
        return $hour12 . ':' . $minute . ' ' . $suffix;
    }

    /**
     * Prayer times stay cached for the day; the "now" highlight must follow the IST clock.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withLiveCurrent(array $data, \DateTimeImmutable $now): array
    {
        $data['current'] = $this->currentFromPrayers(is_array($data['prayers'] ?? null) ? $data['prayers'] : [], $now);
        return $data;
    }

    /**
     * @param list<array{key?:string,time?:string}> $prayers
     */
    private function currentFromPrayers(array $prayers, \DateTimeImmutable $now): ?string
    {
        if (!$prayers) {
            return null;
        }
        $byKey = [];
        foreach ($prayers as $row) {
            $key = (string) ($row['key'] ?? '');
            if ($key !== '') {
                $byKey[$key] = (string) ($row['time'] ?? '');
            }
        }
        $order = $now->format('N') === '5'
            ? ['fajr', 'jummah', 'asr', 'maghrib', 'isha']
            : ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'];
        $minutes = ((int) $now->format('G')) * 60 + (int) $now->format('i');
        $current = null;
        foreach ($order as $key) {
            $value = $this->minutesFromLabel($byKey[$key] ?? '');
            if ($value === null) {
                continue;
            }
            if ($minutes >= $value) {
                $current = $key;
            }
        }
        return $current ?? 'isha';
    }

    private function minutesFromLabel(string $raw): ?int
    {
        $raw = trim($raw);
        if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $raw, $m)) {
            $hour = (int) $m[1];
            $minute = (int) $m[2];
            $suffix = strtoupper($m[3]);
            if ($suffix === 'AM') {
                if ($hour === 12) {
                    $hour = 0;
                }
            } elseif ($hour !== 12) {
                $hour += 12;
            }
            return $hour * 60 + $minute;
        }
        $stamp = trim(explode(' ', $raw)[0] ?? '');
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', $stamp, $m)) {
            return null;
        }
        return ((int) $m[1]) * 60 + (int) $m[2];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function cacheGet(string $key, string $today, int $ttl): ?array
    {
        $file = STORAGE_PATH . '/cache/' . $key . '.json';
        if (!is_file($file) || time() - filemtime($file) > $ttl) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data) || ($data['for_date'] ?? '') !== $today) {
            return null;
        }
        return $data;
    }

    private function cacheSet(string $key, array $data): void
    {
        @file_put_contents(STORAGE_PATH . '/cache/' . $key . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function cacheStale(string $key): ?array
    {
        $file = STORAGE_PATH . '/cache/' . $key . '.json';
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) && !empty($data['ok']) ? $data : null;
    }
}

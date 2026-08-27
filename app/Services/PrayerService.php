<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Daily salah times: Karachi method, Hanafi Asr, computed on the server.
 * Times refresh every day from the date and city coordinates — no remote wait.
 */
final class PrayerService
{
    public function defaultCity(): array
    {
        return ['name' => 'Firozabad', 'state' => 'Uttar Pradesh'];
    }

    public function timings(?string $city = null, ?string $state = null, bool $wait = true): array
    {
        $tz = new \DateTimeZone('Asia/Kolkata');

        return $this->forDate($city, $state, new \DateTimeImmutable('now', $tz), $wait);
    }

    /**
     * @return array<string, mixed>
     */
    public function forDate(?string $city, ?string $state, \DateTimeImmutable $when, bool $wait = true): array
    {
        unset($wait);
        $city = trim((string) $city);
        $state = trim((string) $state);
        if ($city === '') {
            $fallback = $this->defaultCity();
            $city = $fallback['name'];
            $state = $fallback['state'];
        }

        $tz = new \DateTimeZone('Asia/Kolkata');
        $when = $when->setTimezone($tz);
        $clock = new \DateTimeImmutable('now', $tz);
        $day = $when->setTime(12, 0);
        $today = $day->format('Y-m-d');
        $cacheKey = 'prayer-' . md5(mb_strtolower($city . '|' . $state) . '|' . $today . '|local-karachi');
        $untilMidnight = max(60, $clock->modify('tomorrow')->setTime(0, 0)->getTimestamp() - $clock->getTimestamp());
        $cached = $this->cacheGet($cacheKey, $today, $untilMidnight);
        if ($cached !== null) {
            return $this->withLiveCurrent($cached, $clock);
        }

        $point = IndiaCoords::forCity($city, $state);
        $times = LocalPrayerTimes::compute($point['lat'], $point['lng'], $day);
        $prayers = [
            ['key' => 'fajr', 'name' => 'Fajr', 'time' => $this->formatTime($times['fajr'])],
            ['key' => 'zuhr', 'name' => 'Zuhr', 'time' => $this->formatTime($times['dhuhr'])],
            ['key' => 'asr', 'name' => 'Asr', 'time' => $this->formatTime($times['asr'])],
            ['key' => 'maghrib', 'name' => 'Maghrib', 'time' => $this->formatTime($times['maghrib'])],
            ['key' => 'isha', 'name' => 'Isha', 'time' => $this->formatTime($times['isha'])],
            ['key' => 'jummah', 'name' => 'Jummah', 'time' => $this->formatTime($times['dhuhr'])],
        ];

        $result = [
            'ok' => true,
            'error' => null,
            'city' => $city,
            'state' => $state,
            'date' => $day->format('j F Y'),
            'weekday' => $day->format('l'),
            'for_date' => $today,
            'timezone' => 'Asia/Kolkata',
            'prayers' => $prayers,
            'current' => $this->currentFromPrayers($prayers, $clock),
            'sunrise' => $this->formatTime($times['sunrise']),
            'imsak' => $this->formatTime($times['imsak']),
            'fajr' => $this->formatTime($times['fajr']),
            'maghrib' => $this->formatTime($times['maghrib']),
            'isha' => $this->formatTime($times['isha']),
        ];
        $this->cacheSet($cacheKey, $result);

        return $this->withLiveCurrent($result, $clock);
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
        $dir = STORAGE_PATH . '/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($dir . '/' . $key . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
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

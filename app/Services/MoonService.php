<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Moon phase, rise/set, and Hijri date — computed on the server every day.
 */
final class MoonService
{
    public function current(): array
    {
        $lat = (float) setting('location_lat', '27.1591');
        $lng = (float) setting('location_lng', '78.3957');
        $timezone = (string) setting('timezone', cfg('app.timezone', 'Asia/Kolkata'));
        $label = trim((string) setting('location_label', 'Firozabad, Uttar Pradesh, India 283203'));

        try {
            $tz = new \DateTimeZone($timezone);
        } catch (\Exception) {
            $tz = new \DateTimeZone('Asia/Kolkata');
            $timezone = 'Asia/Kolkata';
        }
        $today = (new \DateTimeImmutable('now', $tz))->format('Y-m-d');

        $cacheKey = 'moon-' . md5($lat . '|' . $lng . '|' . $today . '|local');
        $now = new \DateTimeImmutable('now', $tz);
        $untilMidnight = max(60, $now->modify('tomorrow')->setTime(0, 0)->getTimestamp() - $now->getTimestamp());
        $cached = $this->cacheGet($cacheKey, $today, $untilMidnight);
        if ($cached !== null) {
            return $cached;
        }

        $hijriToday = (new IslamicCalendarService())->todayHijri();
        $sky = LocalMoon::forDay($lat, $lng, $now);
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $now->modify('+' . $i . ' days')->setTime(12, 0);
            $row = LocalMoon::forDay($lat, $lng, $day);
            $week[] = [
                'date' => $row['date'],
                'label' => $day->format('D'),
                'daynum' => $day->format('j'),
                'is_today' => $row['date'] === $today,
                'phase' => $row['phase'],
                'illumination' => $row['illumination'],
                'phase_value' => $row['phase_value'],
            ];
        }

        $result = [
            'ok' => true,
            'error' => null,
            'gregorian' => $now->format('l, j F Y'),
            'hijri' => [
                'date' => sprintf('%02d-%02d-%04d', (int) $hijriToday['day'], (int) $hijriToday['month'], (int) $hijriToday['year']),
                'day' => (string) $hijriToday['day'],
                'month_en' => $hijriToday['month_en'],
                'month_ar' => $hijriToday['month_ar'],
                'year' => (string) $hijriToday['year'],
                'weekday_en' => $hijriToday['weekday'],
                'weekday_ar' => '',
                'holidays' => [],
            ],
            'moon' => $sky,
            'week' => $week,
            'location_label' => $label,
            'timezone' => $timezone,
            'for_date' => $today,
            'fetched_at' => date('c'),
        ];
        $this->cacheSet($cacheKey, $result);
        $this->forgetOldCaches($cacheKey);

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchSkyDays(float $lat, float $lng, string $timezone, string $start, string $end): array
    {
        $rangeUrl = sprintf(
            'https://api.sunrisesunset.io/json?lat=%s&lng=%s&timezone=%s&date_start=%s&date_end=%s',
            rawurlencode((string) $lat),
            rawurlencode((string) $lng),
            rawurlencode($timezone),
            rawurlencode($start),
            rawurlencode($end)
        );
        $payload = HttpJson::get($rangeUrl, 10, 2);
        $results = $payload['results'] ?? null;
        if (is_array($results) && isset($results[0]) && is_array($results[0])) {
            return $results;
        }
        if (is_array($results) && isset($results['sunrise'])) {
            return [$results];
        }

        $dayUrl = sprintf(
            'https://api.sunrisesunset.io/json?lat=%s&lng=%s&timezone=%s&date=%s',
            rawurlencode((string) $lat),
            rawurlencode((string) $lng),
            rawurlencode($timezone),
            rawurlencode($start)
        );
        $single = HttpJson::get($dayUrl, 10, 2);
        $row = $single['results'] ?? null;
        return is_array($row) ? [$row] : [];
    }

    /**
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function mapSky(array $results): array
    {
        $golden = $results['golden_hour'] ?? null;
        if (is_array($results['golden_hour_evening'] ?? null)) {
            $golden = $results['golden_hour_evening']['begin'] ?? $golden;
        }

        return [
            'phase' => $results['moon_phase'] ?? null,
            'illumination' => $results['moon_illumination'] ?? null,
            'phase_value' => $results['moon_phase_value'] ?? null,
            'moonrise' => $results['moonrise'] ?? null,
            'moonset' => $results['moonset'] ?? null,
            'sunrise' => $results['sunrise'] ?? null,
            'sunset' => $results['sunset'] ?? null,
            'golden_hour' => is_string($golden) ? $golden : null,
            'timezone' => $results['timezone'] ?? null,
            'date' => $results['date'] ?? null,
        ];
    }

    private function fetchJson(string $url): array
    {
        return HttpJson::get($url, (int) cfg('moon.timeout', 10), 2);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function cacheStale(string $key): ?array
    {
        $file = STORAGE_PATH . '/cache/' . $key . '.json';
        if (!is_file($file)) {
            foreach (glob(STORAGE_PATH . '/cache/moon-*.json') ?: [] as $old) {
                $data = json_decode((string) file_get_contents($old), true);
                if (is_array($data) && !empty($data['ok'])) {
                    return $data;
                }
            }
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) && !empty($data['ok']) ? $data : null;
    }

    private function cacheGet(string $key, string $today, int $ttl): ?array
    {
        $file = STORAGE_PATH . '/cache/' . $key . '.json';
        if (!is_file($file)) {
            return null;
        }
        if (time() - filemtime($file) > $ttl) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            return null;
        }
        if (($data['for_date'] ?? '') !== $today) {
            return null;
        }
        return $data;
    }

    private function cacheSet(string $key, array $data): void
    {
        $file = STORAGE_PATH . '/cache/' . $key . '.json';
        @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function forgetOldCaches(string $keepKey): void
    {
        foreach (glob(STORAGE_PATH . '/cache/moon-*.json') ?: [] as $file) {
            if (basename($file, '.json') !== $keepKey) {
                @unlink($file);
            }
        }
    }
}

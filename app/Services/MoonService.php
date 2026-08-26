<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Moon data adapter.
 * Default providers (no API key required):
 *  - AlAdhan (Hijri date): https://api.aladhan.com
 *  - sunrisesunset.io (moonrise/moonset/phase)
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
        $weekEnd = (new \DateTimeImmutable('now', $tz))->modify('+6 days')->format('Y-m-d');

        $cacheKey = 'moon-' . md5($lat . '|' . $lng . '|' . $today . '|week');
        $now = new \DateTimeImmutable('now', $tz);
        $untilMidnight = max(60, $now->modify('tomorrow')->setTime(0, 0)->getTimestamp() - $now->getTimestamp());
        $cached = $this->cacheGet($cacheKey, $today, $untilMidnight);
        if ($cached !== null) {
            return $cached;
        }

        $result = [
            'ok' => false,
            'error' => null,
            'gregorian' => (new \DateTimeImmutable('now', $tz))->format('l, j F Y'),
            'hijri' => null,
            'moon' => null,
            'week' => [],
            'location_label' => $label,
            'timezone' => $timezone,
            'for_date' => $today,
            'fetched_at' => date('c'),
        ];

        try {
            $hijri = HttpJson::get(
                'https://api.aladhan.com/v1/gToH?date=' . (new \DateTimeImmutable('now', $tz))->format('d-m-Y'),
                10,
                2
            );
            if (($hijri['code'] ?? 0) === 200 && isset($hijri['data']['hijri'])) {
                $h = $hijri['data']['hijri'];
                $g = $hijri['data']['gregorian'] ?? [];
                $result['hijri'] = [
                    'date' => $h['date'] ?? '',
                    'day' => $h['day'] ?? '',
                    'month_en' => $h['month']['en'] ?? '',
                    'month_ar' => $h['month']['ar'] ?? '',
                    'year' => $h['year'] ?? '',
                    'weekday_en' => $h['weekday']['en'] ?? '',
                    'weekday_ar' => $h['weekday']['ar'] ?? '',
                    'holidays' => $h['holidays'] ?? [],
                ];
                if (!empty($g['date'])) {
                    $result['gregorian'] = trim(
                        ($g['weekday']['en'] ?? '') . ', ' . ($g['day'] ?? '') . ' ' . ($g['month']['en'] ?? '') . ' ' . ($g['year'] ?? '')
                    );
                }
            }

            $days = $this->fetchSkyDays($lat, $lng, $timezone, $today, $weekEnd);
            $week = [];
            foreach ($days as $row) {
                $sky = $this->mapSky($row);
                $date = (string) ($sky['date'] ?? '');
                $ts = $date !== '' ? strtotime($date) : false;
                $week[] = [
                    'date' => $date,
                    'label' => $ts ? date('D', $ts) : '',
                    'daynum' => $ts ? date('j', $ts) : '',
                    'is_today' => $date === $today,
                    'phase' => $sky['phase'],
                    'illumination' => $sky['illumination'],
                    'phase_value' => $sky['phase_value'],
                ];
                if ($date === $today) {
                    $result['moon'] = $sky;
                }
            }
            $result['week'] = $week;
            if ($result['moon'] === null && $days !== []) {
                $result['moon'] = $this->mapSky($days[0]);
            }

            $result['ok'] = $result['hijri'] !== null || $result['moon'] !== null;
            if (!$result['ok']) {
                $stale = $this->cacheStale($cacheKey);
                if ($stale !== null) {
                    $stale['stale'] = true;
                    $stale['error'] = null;
                    return $stale;
                }
                $result['error'] = 'Moon timing data is temporarily unavailable. Please try again later.';
            } else {
                $this->cacheSet($cacheKey, $result);
                $this->forgetOldCaches($cacheKey);
            }
        } catch (\Throwable) {
            $stale = $this->cacheStale($cacheKey);
            if ($stale !== null) {
                $stale['stale'] = true;
                $stale['error'] = null;
                return $stale;
            }
            $result['error'] = 'Moon timing data is temporarily unavailable. Please try again later.';
        }

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

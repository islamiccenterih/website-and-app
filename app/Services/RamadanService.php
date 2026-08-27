<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Sehri / Iftar calendar for any Indian city (AlAdhan, Karachi method, Hanafi Asr).
 */
final class RamadanService
{
    public function defaultCity(): array
    {
        $tools = json_setting('worship_tools');
        $city = trim((string) ($tools['ramadan_city'] ?? ''));
        $state = trim((string) ($tools['ramadan_state'] ?? ''));
        if ($city === '') {
            return ['name' => 'Firozabad', 'state' => 'Uttar Pradesh'];
        }
        return ['name' => $city, 'state' => $state !== '' ? $state : 'Uttar Pradesh'];
    }

    public function page(?string $city = null, ?string $state = null, ?int $hijriYear = null): array
    {
        $fallback = $this->defaultCity();
        $city = trim((string) $city);
        $state = trim((string) $state);
        if ($city === '') {
            $city = $fallback['name'];
            $state = $fallback['state'];
        }

        $today = (new IslamicCalendarService())->todayHijri();
        $hy = $hijriYear && $hijriYear > 1300 ? $hijriYear : $this->ramadanYear($today);
        $isRamadan = (int) $today['month'] === 9 && (int) $today['year'] === $hy;
        $month = $this->month($hy, $city, $state);
        $prayer = (new PrayerService())->timings($city, $state);

        $todayIso = (string) ($today['gregorian_iso'] ?? '');
        $hijriLabel = trim((int) ($today['day'] ?? 0) . ' ' . (string) ($today['month_en'] ?? '') . ' ' . (int) ($today['year'] ?? 0) . ' AH');
        $gregLabel = (string) ($today['gregorian_label'] ?? '');
        $todayRow = [
            'hijri_day' => (int) ($today['day'] ?? 0),
            'hijri_label' => $hijriLabel,
            'hijri_month_en' => (string) ($today['month_en'] ?? ''),
            'hijri_year' => (int) ($today['year'] ?? 0),
            'gregorian_iso' => $todayIso,
            'gregorian_label' => $gregLabel,
            'weekday' => (string) ($today['weekday'] ?? ''),
            'imsak' => (string) ($prayer['imsak'] ?? ''),
            'fajr' => (string) ($prayer['fajr'] ?? ''),
            'maghrib' => (string) ($prayer['maghrib'] ?? ''),
            'isha' => (string) ($prayer['isha'] ?? ''),
            'is_today' => true,
            'ok' => !empty($prayer['ok']),
        ];

        $days = [];
        foreach ($month['days'] ?? [] as $row) {
            $row['is_today'] = ($row['gregorian_iso'] ?? '') === $todayIso;
            if ($row['is_today'] && $isRamadan) {
                $todayRow['imsak'] = (string) ($row['imsak'] ?: $todayRow['imsak']);
                $todayRow['fajr'] = (string) ($row['fajr'] ?: $todayRow['fajr']);
                $todayRow['maghrib'] = (string) ($row['maghrib'] ?: $todayRow['maghrib']);
            }
            $days[] = $row;
        }

        $first = $days[0] ?? null;
        $startIso = (string) ($first['gregorian_iso'] ?? '');
        $startGreg = $this->englishDate($startIso) ?: (string) ($first['gregorian_label'] ?? '');
        $startUnix = $this->unixAtMidnight($startIso);
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Kolkata'));
        $seconds = $startUnix > 0 ? max(0, $startUnix - $now->getTimestamp()) : 0;
        $remainingRozas = 0;
        if ($isRamadan) {
            foreach ($days as $row) {
                if (($row['gregorian_iso'] ?? '') >= $todayIso) {
                    $remainingRozas++;
                }
            }
        }

        $startHijri = '1 Ramadan ' . $hy . ' AH';
        if ($isRamadan) {
            $flag = 'Ramadan ' . $hy . ' AH is here — day ' . (int) $today['day'] . '.';
        } elseif ($startGreg !== '') {
            $flag = 'Ramadan ' . $hy . ' AH begins ' . $startGreg . ' · ' . $startHijri;
        } else {
            $flag = 'Next Ramadan is ' . $hy . ' AH.';
        }

        return [
            'ok' => !empty($prayer['ok']) || !empty($month['ok']),
            'error' => empty($prayer['ok']) ? ($prayer['error'] ?? $month['error'] ?? null) : null,
            'city' => $city,
            'state' => $state,
            'hijri_year' => $hy,
            'is_ramadan' => $isRamadan,
            'today' => $today,
            'today_row' => $todayRow,
            'days' => $days,
            'gregorian_span' => (string) ($month['gregorian_span'] ?? ''),
            'duas' => $this->duas(),
            'next_ramadan_label' => $flag,
            'ramadan_start' => [
                'gregorian_iso' => $startIso,
                'gregorian_label' => $startGreg,
                'hijri_label' => $startHijri,
                'hijri_year' => $hy,
                'unix' => $startUnix,
                'seconds' => $seconds,
            ],
            'remaining_rozas' => $remainingRozas,
        ];
    }

    /**
     * @return list<array{key:string,title:string,arabic:string,translit:string,meaning:string}>
     */
    public function duas(): array
    {
        $saved = json_setting('worship_tools')['duas'] ?? [];
        $defaults = $this->defaultDuas();
        if (!is_array($saved) || $saved === []) {
            return $defaults;
        }
        $out = [];
        foreach ($defaults as $i => $row) {
            $item = is_array($saved[$i] ?? null) ? $saved[$i] : [];
            $out[] = [
                'key' => $row['key'],
                'title' => trim((string) ($item['title'] ?? '')) ?: $row['title'],
                'arabic' => trim((string) ($item['arabic'] ?? '')) ?: $row['arabic'],
                'translit' => trim((string) ($item['translit'] ?? '')) ?: $row['translit'],
                'meaning' => trim((string) ($item['meaning'] ?? '')) ?: $row['meaning'],
            ];
        }
        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function month(int $year, string $city, string $state): array
    {
        $cacheKey = 'ramadan-' . md5(mb_strtolower($city . '|' . $state) . '|' . $year . '|local');
        $cacheFile = STORAGE_PATH . '/cache/' . $cacheKey . '.json';
        $cached = HttpJson::read($cacheFile);
        if (is_array($cached) && !empty($cached['days']) && (int) ($cached['hijri_year'] ?? 0) === $year) {
            return $cached;
        }

        $cal = new IslamicCalendarService();
        $rawDays = $cal->hijriMonthDays($year, 9);
        if ($rawDays === []) {
            return [
                'ok' => false,
                'error' => 'Sehri and Iftar times could not be loaded for this city. Try another city, or wait a moment.',
                'hijri_year' => $year,
                'days' => [],
            ];
        }

        $tz = new \DateTimeZone('Asia/Kolkata');
        $svc = new PrayerService();
        $days = [];
        $first = $last = '';
        foreach ($rawDays as $item) {
            if (!is_array($item)) {
                continue;
            }
            $iso = $this->gregorianIso($item);
            if ($iso === '') {
                continue;
            }
            try {
                $when = new \DateTimeImmutable($iso . ' 12:00:00', $tz);
            } catch (\Throwable) {
                continue;
            }
            $prayer = $svc->forDate($city, $state, $when);
            $hijriDay = (int) ($item['hijri_day'] ?? 0);
            $readable = trim((string) ($item['gregorian_month_en'] ?? '') . ' ' . (int) ($item['gregorian_day'] ?? 0) . ', ' . (int) ($item['gregorian_year'] ?? 0));
            if (!empty($item['gregorian_date']) && preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', (string) $item['gregorian_date'], $m)) {
                try {
                    $readable = (new \DateTimeImmutable(sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]), $tz))->format('j F Y');
                } catch (\Throwable) {
                }
            }
            $row = [
                'hijri_day' => $hijriDay,
                'hijri_label' => $hijriDay . ' Ramadan ' . $year,
                'gregorian_iso' => $iso,
                'gregorian_label' => $readable,
                'weekday' => (string) ($item['weekday_en'] ?? $when->format('l')),
                'imsak' => (string) ($prayer['imsak'] ?? ''),
                'fajr' => (string) ($prayer['fajr'] ?? ''),
                'maghrib' => (string) ($prayer['maghrib'] ?? ''),
                'isha' => (string) ($prayer['isha'] ?? ''),
            ];
            if ($first === '') {
                $first = $row['gregorian_label'];
            }
            $last = $row['gregorian_label'];
            $days[] = $row;
        }

        $out = [
            'ok' => $days !== [],
            'error' => $days === [] ? 'No Ramadan timetable was returned for this city.' : null,
            'hijri_year' => $year,
            'city' => $city,
            'state' => $state,
            'gregorian_span' => $first && $last ? $first . ' – ' . $last : '',
            'days' => $days,
            'fetched_at' => gmdate('c'),
        ];
        HttpJson::write($cacheFile, $out);
        return $out;
    }

    /**
     * @param array<string, mixed> $today
     */
    private function ramadanYear(array $today): int
    {
        $year = (int) ($today['year'] ?? 1448);
        $month = (int) ($today['month'] ?? 1);
        return $month <= 9 ? $year : $year + 1;
    }

    private function englishDate(string $iso): string
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $iso)) {
            return '';
        }
        try {
            $dt = new \DateTimeImmutable($iso . ' 00:00:00', new \DateTimeZone('Asia/Kolkata'));
            return $dt->format('l, j F Y');
        } catch (\Throwable) {
            return '';
        }
    }

    private function unixAtMidnight(string $iso): int
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $iso)) {
            return 0;
        }
        try {
            return (new \DateTimeImmutable($iso . ' 00:00:00', new \DateTimeZone('Asia/Kolkata')))->getTimestamp();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @param array<string, mixed> $day
     */
    private function gregorianIso(array $day): string
    {
        $date = (string) ($day['gregorian_date'] ?? '');
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $date, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }
        $y = (int) ($day['gregorian_year'] ?? 0);
        $mo = (int) ($day['gregorian_month'] ?? 0);
        $d = (int) ($day['gregorian_day'] ?? 0);

        return $y && $mo && $d ? sprintf('%04d-%02d-%02d', $y, $mo, $d) : '';
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
     * @return list<array{key:string,title:string,arabic:string,translit:string,meaning:string}>
     */
    private function defaultDuas(): array
    {
        return [
            [
                'key' => 'sehri',
                'title' => 'Dua at Sehri (intention to fast)',
                'arabic' => 'وَبِصَوْمِ غَدٍ نَّوَيْتُ مِنْ شَهْرِ رَمَضَانَ',
                'translit' => 'Wa bi-sawmi ghadin nawaytu min shahri Ramadan.',
                'meaning' => 'I intend to keep the fast for tomorrow in the month of Ramadan.',
            ],
            [
                'key' => 'iftar',
                'title' => 'Dua at Iftar',
                'arabic' => 'اللَّهُمَّ إِنِّي لَكَ صُمْتُ وَبِكَ آمَنْتُ وَعَلَيْكَ تَوَكَّلْتُ وَعَلَى رِزْقِكَ أَفْطَرْتُ',
                'translit' => 'Allahumma inni laka sumtu, wa bika aamantu, wa ‘alayka tawakkaltu, wa ‘ala rizqika aftartu.',
                'meaning' => 'O Allah, I fasted for You, I believe in You, I put my trust in You, and with Your provision I break my fast.',
            ],
            [
                'key' => 'breaking',
                'title' => 'When the fast is opened',
                'arabic' => 'ذَهَبَ الظَّمَأُ وَابْتَلَّتِ الْعُرُوقُ وَثَبَتَ الْأَجْرُ إِنْ شَاءَ اللَّهُ',
                'translit' => 'Dhahaba al-zama’u wabtallatil-‘urooqu wa thabatal-ajru in sha Allah.',
                'meaning' => 'Thirst is gone, the veins are moistened, and the reward is confirmed, if Allah wills.',
            ],
            [
                'key' => 'taraweeh',
                'title' => 'Dua after Taraweeh',
                'arabic' => 'سُبْحَانَ ذِي الْمُلْكِ وَالْمَلَكُوتِ، سُبْحَانَ ذِي الْعِزَّةِ وَالْعَظَمَةِ وَالْهَيْبَةِ وَالْقُدْرَةِ وَالْكِبْرِيَاءِ وَالْجَبَرُوتِ',
                'translit' => 'Subhana dhil-mulki wal-malakut. Subhana dhil-‘izzati wal-‘azamati wal-haybati wal-qudrati wal-kibriya’i wal-jabarut.',
                'meaning' => 'Glory be to the Owner of dominion and the unseen kingdom; glory be to the Owner of honour, greatness, awe, power, pride, and majesty.',
            ],
            [
                'key' => 'qadr',
                'title' => 'Dua on Laylat al-Qadr',
                'arabic' => 'اللَّهُمَّ إِنَّكَ عَفُوٌّ تُحِبُّ الْعَفْوَ فَاعْفُ عَنِّي',
                'translit' => 'Allahumma innaka ‘afuwwun tuhibbul-‘afwa fa‘fu ‘anni.',
                'meaning' => 'O Allah, You are Forgiving and love forgiveness, so forgive me.',
            ],
            [
                'key' => 'last',
                'title' => 'Seeking acceptance of the fast',
                'arabic' => 'اللَّهُمَّ تَقَبَّلْ مِنَّا إِنَّكَ أَنْتَ السَّمِيعُ الْعَلِيمُ',
                'translit' => 'Allahumma taqabbal minna innaka antas-sami‘ul-‘aleem.',
                'meaning' => 'O Allah, accept from us. You are the All-Hearing, the All-Knowing.',
            ],
        ];
    }
}

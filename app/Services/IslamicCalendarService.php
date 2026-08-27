<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Functional Hijri calendar.
 * Month grids are stored under public/assets/data/islamic-calendar/ so the
 * site keeps working if the upstream API is later removed.
 */
final class IslamicCalendarService
{
    private const DATA_DIR = '/assets/data/islamic-calendar';

    /** @var array<int, string> */
    private const MONTH_EN = [
        1 => 'Muharram',
        2 => 'Safar',
        3 => 'Rabīʿ al-awwal',
        4 => 'Rabīʿ al-thānī',
        5 => 'Jumādā al-ūlā',
        6 => 'Jumādā al-ākhirah',
        7 => 'Rajab',
        8 => 'Shaʿbān',
        9 => 'Ramadan',
        10 => 'Shawwāl',
        11 => 'Dhū al-Qaʿdah',
        12 => 'Dhū al-Ḥijjah',
    ];

    /** @var array<int, string> */
    private const MONTH_AR = [
        1 => 'مُحَرَّم',
        2 => 'صَفَر',
        3 => 'رَبيع الأوّل',
        4 => 'رَبيع الثاني',
        5 => 'جُمادى الأولى',
        6 => 'جُمادى الآخرة',
        7 => 'رَجَب',
        8 => 'شَعْبان',
        9 => 'رَمَضان',
        10 => 'شَوّال',
        11 => 'ذوالقعدة',
        12 => 'ذوالحجة',
    ];

    public function month(?int $year = null, ?int $month = null): array
    {
        $today = $this->todayHijri();
        $year = $year !== null && $year > 0 ? $year : (int) $today['year'];
        $month = $month !== null && $month > 0 ? $month : (int) $today['month'];
        if ($month < 1) {
            $month = 1;
        }
        if ($month > 12) {
            $month = 12;
        }
        if ($year < 1300 || $year > 1600) {
            $year = (int) $today['year'];
        }

        $raw = $this->loadMonth($year, $month);
        if ($raw === null) {
            return [
                'ok' => false,
                'error' => 'This Hijri month is not available yet. Open today’s month, or try again in a moment.',
                'hijri_year' => $year,
                'hijri_month' => $month,
                'hijri_month_en' => self::MONTH_EN[$month] ?? 'Hijri month',
                'hijri_month_ar' => self::MONTH_AR[$month] ?? '',
                'gregorian_span' => '',
                'today' => $today,
                'is_current_month' => $year === (int) $today['year'] && $month === (int) $today['month'],
                'prev' => $this->shift($year, $month, -1),
                'next' => $this->shift($year, $month, 1),
                'weekdays' => $this->weekdayLabels(),
                'weeks' => [],
                'observances' => [],
                'today_label' => $this->todayBanner($today),
                'years' => $this->availableYears($year),
                'months' => self::MONTH_EN,
            ];
        }

        $days = [];
        foreach ($raw['days'] ?? [] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $days[] = $this->enrichDay($item, $today);
        }

        $weeks = $this->buildWeeks($days);
        $observances = [];
        foreach ($days as $day) {
            foreach ($day['holidays'] as $title) {
                $observances[] = [
                    'hijri_day' => $day['hijri_day'],
                    'title' => $title,
                    'gregorian_label' => $day['gregorian_label'],
                    'weekday' => $day['weekday_short'],
                    'is_today' => $day['is_today'],
                ];
            }
        }

        $monthEn = (string) ($raw['hijri_month_en'] ?: (self::MONTH_EN[$month] ?? ''));
        $monthAr = (string) ($raw['hijri_month_ar'] ?: (self::MONTH_AR[$month] ?? ''));

        return [
            'ok' => true,
            'error' => null,
            'hijri_year' => $year,
            'hijri_month' => $month,
            'hijri_month_en' => $monthEn,
            'hijri_month_ar' => $monthAr,
            'gregorian_span' => (string) ($raw['gregorian_span'] ?? $this->spanFromDays($days)),
            'today' => $today,
            'is_current_month' => $year === (int) $today['year'] && $month === (int) $today['month'],
            'prev' => $this->shift($year, $month, -1),
            'next' => $this->shift($year, $month, 1),
            'weekdays' => $this->weekdayLabels(),
            'weeks' => $weeks,
            'observances' => $observances,
            'today_label' => $this->todayBanner($today),
            'years' => $this->availableYears($year),
            'months' => self::MONTH_EN,
        ];
    }

    /**
     * Upcoming observances for dashboards (this month onward).
     *
     * @return list<array{title:string,hijri_date:string,gregorian_date:string,description:string}>
     */
    public function upcoming(int $limit = 6): array
    {
        $today = $this->todayHijri();
        $year = (int) $today['year'];
        $month = (int) $today['month'];
        $dayNum = (int) $today['day'];
        $out = [];

        for ($i = 0; $i < 12 && count($out) < $limit; $i++) {
            $cal = $this->month($year, $month);
            foreach ($cal['observances'] as $item) {
                if ($year === (int) $today['year'] && $month === (int) $today['month'] && (int) $item['hijri_day'] < $dayNum) {
                    continue;
                }
                $out[] = [
                    'title' => $item['title'],
                    'hijri_date' => $item['hijri_day'] . ' ' . ($cal['hijri_month_en'] ?? '') . ' ' . $year,
                    'gregorian_date' => $item['gregorian_label'],
                    'description' => $item['is_today'] ? 'Today' : '',
                ];
                if (count($out) >= $limit) {
                    break;
                }
            }
            $next = $this->shift($year, $month, 1);
            $year = $next['hy'];
            $month = $next['hm'];
        }

        return $out;
    }

    /**
     * @return array{day:int,month:int,year:int,month_en:string,month_ar:string,gregorian_iso:string,gregorian_label:string,weekday:string}
     */
    public function todayHijri(): array
    {
        $tz = $this->timezone();
        $now = new \DateTimeImmutable('now', $tz);
        $iso = $now->format('Y-m-d');
        $cacheFile = STORAGE_PATH . '/cache/hijri-today.json';
        if (is_file($cacheFile)) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && ($cached['gregorian_iso'] ?? '') === $iso && !empty($cached['year'])) {
                return $cached;
            }
        }

        $found = $this->findLocalHijri($iso);
        if ($found !== null) {
            $this->writeJson($cacheFile, $found);
            return $found;
        }

        try {
            $payload = HttpJson::get('https://api.aladhan.com/v1/gToH?date=' . $now->format('d-m-Y'), 2, 1);
            $h = $payload['data']['hijri'] ?? [];
            $g = $payload['data']['gregorian'] ?? [];
            $result = [
                'day' => (int) ($h['day'] ?? $now->format('j')),
                'month' => (int) (($h['month']['number'] ?? 0) ?: 1),
                'year' => (int) ($h['year'] ?? 1448),
                'month_en' => (string) ($h['month']['en'] ?? ''),
                'month_ar' => (string) ($h['month']['ar'] ?? ''),
                'gregorian_iso' => $iso,
                'gregorian_label' => $now->format('l, j F Y'),
                'weekday' => $now->format('l'),
            ];
            if (!empty($g['date'])) {
                $result['gregorian_label'] = trim(
                    ($g['weekday']['en'] ?? $now->format('l')) . ', ' . ($g['day'] ?? '') . ' ' . ($g['month']['en'] ?? '') . ' ' . ($g['year'] ?? '')
                );
            }
            $this->writeJson($cacheFile, $result);
            return $result;
        } catch (\Throwable) {
            $fallback = [
                'day' => (int) $now->format('j'),
                'month' => 3,
                'year' => 1448,
                'month_en' => self::MONTH_EN[3],
                'month_ar' => self::MONTH_AR[3],
                'gregorian_iso' => $iso,
                'gregorian_label' => $now->format('l, j F Y'),
                'weekday' => $now->format('l'),
            ];
            return $found ?? $fallback;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function hijriMonthDays(int $year, int $month): array
    {
        $raw = $this->loadMonth($year, $month);
        if (!is_array($raw) || !is_array($raw['days'] ?? null)) {
            return [];
        }
        $days = [];
        foreach ($raw['days'] as $day) {
            if (is_array($day)) {
                $days[] = $day;
            }
        }

        return $days;
    }

    /**
     * @return array{hy:int,hm:int}
     */
    public function shift(int $year, int $month, int $delta): array
    {
        $month += $delta;
        while ($month < 1) {
            $month += 12;
            $year--;
        }
        while ($month > 12) {
            $month -= 12;
            $year++;
        }
        return ['hy' => $year, 'hm' => $month];
    }

    /**
     * @return list<int>
     */
    public function availableYears(int $include): array
    {
        $years = [$include];
        foreach (glob($this->dataPath() . '/*.json') ?: [] as $file) {
            if (preg_match('/(\d{4})-\d{2}\.json$/', $file, $m)) {
                $years[] = (int) $m[1];
            }
        }
        $years = array_values(array_unique($years));
        sort($years);
        return $years;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadMonth(int $year, int $month): ?array
    {
        $name = sprintf('%d-%02d.json', $year, $month);
        $public = $this->dataPath() . '/' . $name;
        $cache = STORAGE_PATH . '/cache/islamic-calendar-' . sprintf('%d-%02d', $year, $month) . '.json';

        foreach ([$public, $cache] as $file) {
            if (!is_file($file)) {
                continue;
            }
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded) && !empty($decoded['days'])) {
                return $decoded;
            }
        }

        $fetched = $this->fetchMonth($year, $month);
        if ($fetched === null) {
            return null;
        }
        $this->persistMonth($year, $month, $fetched);
        return $fetched;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchMonth(int $year, int $month): ?array
    {
        try {
            $payload = HttpJson::get('https://api.aladhan.com/v1/hToGCalendar/' . $month . '/' . $year, 2, 1);
        } catch (\Throwable) {
            return null;
        }
        $rows = $payload['data'] ?? [];
        if (!is_array($rows) || $rows === []) {
            return null;
        }

        $days = [];
        $monthEn = self::MONTH_EN[$month] ?? '';
        $monthAr = self::MONTH_AR[$month] ?? '';
        $first = $last = null;
        foreach ($rows as $item) {
            if (!is_array($item)) {
                continue;
            }
            $hijri = is_array($item['hijri'] ?? null) ? $item['hijri'] : [];
            $greg = is_array($item['gregorian'] ?? null) ? $item['gregorian'] : [];
            $hMonth = is_array($hijri['month'] ?? null) ? $hijri['month'] : [];
            $gMonth = is_array($greg['month'] ?? null) ? $greg['month'] : [];
            $weekday = (string) (($hijri['weekday']['en'] ?? '') ?: ($greg['weekday']['en'] ?? ''));
            $day = [
                'hijri_day' => (int) ($hijri['day'] ?? 0),
                'hijri_month' => (int) ($hMonth['number'] ?? $month),
                'hijri_year' => (int) ($hijri['year'] ?? $year),
                'hijri_month_en' => (string) ($hMonth['en'] ?? $monthEn),
                'hijri_month_ar' => (string) ($hMonth['ar'] ?? $monthAr),
                'weekday_en' => $weekday,
                'weekday_ar' => (string) ($hijri['weekday']['ar'] ?? ''),
                'gregorian_date' => (string) ($greg['date'] ?? ''),
                'gregorian_day' => (int) ($greg['day'] ?? 0),
                'gregorian_month' => (int) ($gMonth['number'] ?? 0),
                'gregorian_month_en' => (string) ($gMonth['en'] ?? ''),
                'gregorian_year' => (int) ($greg['year'] ?? 0),
                'holidays' => is_array($hijri['holidays'] ?? null) ? array_values($hijri['holidays']) : [],
            ];
            $monthEn = $day['hijri_month_en'] ?: $monthEn;
            $monthAr = $day['hijri_month_ar'] ?: $monthAr;
            $label = trim($day['gregorian_day'] . ' ' . $day['gregorian_month_en'] . ' ' . $day['gregorian_year']);
            if ($first === null) {
                $first = $label;
            }
            $last = $label;
            $days[] = $day;
        }
        if ($days === []) {
            return null;
        }

        return [
            'source' => 'aladhan',
            'fetched_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'hijri_year' => $year,
            'hijri_month' => $month,
            'hijri_month_en' => $monthEn,
            'hijri_month_ar' => $monthAr,
            'gregorian_span' => $first && $last ? $first . ' – ' . $last : '',
            'days' => $days,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function persistMonth(int $year, int $month, array $payload): void
    {
        $dir = $this->dataPath();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $public = $dir . '/' . sprintf('%d-%02d.json', $year, $month);
        $cache = STORAGE_PATH . '/cache/islamic-calendar-' . sprintf('%d-%02d', $year, $month) . '.json';
        $this->writeJson($public, $payload);
        $this->writeJson($cache, $payload);
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $today
     * @return array<string, mixed>
     */
    private function enrichDay(array $item, array $today): array
    {
        $hijriDay = (int) ($item['hijri_day'] ?? 0);
        $hijriMonth = (int) ($item['hijri_month'] ?? 0);
        $gDay = (int) ($item['gregorian_day'] ?? 0);
        $gMonth = (int) ($item['gregorian_month'] ?? $item['gregorian_month_number'] ?? 0);
        $gYear = (int) ($item['gregorian_year'] ?? 0);
        $gDate = (string) ($item['gregorian_date'] ?? '');
        if ($gDate !== '' && preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $gDate, $m)) {
            $gDay = $gDay ?: (int) $m[1];
            $gMonth = $gMonth ?: (int) $m[2];
            $gYear = $gYear ?: (int) $m[3];
        }
        $iso = $gYear > 0 && $gMonth > 0 && $gDay > 0
            ? sprintf('%04d-%02d-%02d', $gYear, $gMonth, $gDay)
            : '';
        $weekdayIndex = $this->weekdayIndex($item, $iso);
        $weekdayName = $this->weekdayName($weekdayIndex);
        $holidays = $this->observancesFor($hijriMonth, $hijriDay, $item['holidays'] ?? []);
        $gMonthName = (string) ($item['gregorian_month_en'] ?? '');
        if ($gMonthName === '' && $gMonth > 0) {
            $gMonthName = date('F', mktime(0, 0, 0, $gMonth, 1, $gYear ?: 2000));
        }

        return [
            'hijri_day' => $hijriDay,
            'hijri_month' => $hijriMonth,
            'hijri_year' => (int) ($item['hijri_year'] ?? 0),
            'weekday_index' => $weekdayIndex,
            'weekday' => $weekdayName,
            'weekday_short' => $this->weekdayLabels()[$weekdayIndex] ?? '',
            'gregorian_iso' => $iso,
            'gregorian_day' => $gDay,
            'gregorian_month' => $gMonth,
            'gregorian_month_en' => $gMonthName,
            'gregorian_year' => $gYear,
            'gregorian_label' => trim($gDay . ' ' . $gMonthName . ' ' . $gYear),
            'is_today' => $iso !== '' && $iso === (string) ($today['gregorian_iso'] ?? ''),
            'is_friday' => $weekdayIndex === 5,
            'is_holiday' => $holidays !== [],
            'holidays' => $holidays,
        ];
    }

    /**
     * @param list<array<string, mixed>> $days
     * @return list<list<array<string, mixed>|null>>
     */
    private function buildWeeks(array $days): array
    {
        $weeks = [];
        $week = array_fill(0, 7, null);
        $started = false;
        foreach ($days as $day) {
            $index = (int) $day['weekday_index'];
            if (!$started) {
                $started = true;
            }
            $week[$index] = $day;
            if ($index === 6) {
                $weeks[] = $week;
                $week = array_fill(0, 7, null);
            }
        }
        if ($started) {
            $has = false;
            foreach ($week as $cell) {
                if ($cell !== null) {
                    $has = true;
                    break;
                }
            }
            if ($has) {
                $weeks[] = $week;
            }
        }
        return $weeks;
    }

    /**
     * @param mixed $raw
     * @return list<string>
     */
    private function observancesFor(int $month, int $day, mixed $raw): array
    {
        $known = $this->knownMap();
        $titles = [];
        $key = $month . '-' . $day;
        if (isset($known[$key])) {
            $titles[] = $known[$key];
        }
        if (is_array($raw)) {
            foreach ($raw as $title) {
                $title = trim((string) $title);
                if ($title === '' || !$this->keepHoliday($title)) {
                    continue;
                }
                if ($this->alreadyCovered($titles, $title)) {
                    continue;
                }
                $titles[] = $title;
            }
        }
        return array_values(array_unique($titles));
    }

    /**
     * @return array<string, string>
     */
    private function knownMap(): array
    {
        return [
            '1-1' => 'Islamic New Year',
            '1-10' => 'Ashura',
            '3-12' => 'Mawlid an-Nabi ﷺ',
            '7-27' => 'Isra and Miʿraj',
            '8-15' => 'Nisf Shaʿban',
            '9-1' => 'First day of Ramadan',
            '9-27' => 'Laylat al-Qadr',
            '10-1' => 'Eid al-Fitr',
            '12-8' => 'Yawm at-Tarwiyah',
            '12-9' => 'Day of ʿArafah',
            '12-10' => 'Eid al-Adha',
            '12-11' => 'Days of Tashreeq',
            '12-12' => 'Days of Tashreeq',
            '12-13' => 'Days of Tashreeq',
        ];
    }

    private function keepHoliday(string $title): bool
    {
        $lower = mb_strtolower($title);
        if (str_contains($lower, 'urs of') || str_contains($lower, 'veiling of') || str_contains($lower, 'adhan')) {
            return false;
        }
        if (str_contains($lower, 'new year')) {
            return true;
        }
        return (bool) preg_match(
            '/\b(mawlid|milad|ashura|eid|ramadan|ramazan|isra|mi[\'’]?raj|miʿraj|laylat|lailat|qadr|qadar|arafah|arafat|muharram|sha[\'’]?ban|shaban|shaʿban|nisf|tashreeq|hijri|fitr|adha)\b/u',
            $lower
        );
    }

    /**
     * @param list<string> $titles
     */
    private function alreadyCovered(array $titles, string $candidate): bool
    {
        $c = mb_strtolower($candidate);
        foreach ($titles as $title) {
            $t = mb_strtolower($title);
            if ($t === $c || str_contains($t, $c) || str_contains($c, $t)) {
                return true;
            }
            if ((str_contains($t, 'mawlid') && str_contains($c, 'mawlid'))
                || (str_contains($t, 'eid al-fitr') && str_contains($c, 'fitr'))
                || (str_contains($t, 'eid al-adha') && str_contains($c, 'adha'))
                || (str_contains($t, 'ashura') && str_contains($c, 'ashura'))
                || ((str_contains($t, 'qadr') || str_contains($t, 'lailat')) && (str_contains($c, 'qadr') || str_contains($c, 'lailat')))
                || ((str_contains($t, 'isra') || str_contains($t, 'miraj') || str_contains($t, 'miʿraj'))
                    && (str_contains($c, 'isra') || str_contains($c, 'miraj') || str_contains($c, 'miʿraj')))
                || (str_contains($t, 'ramadan') && str_contains($c, 'ramadan'))
                || ((str_contains($t, 'nisf') || str_contains($t, 'shaʿban') || str_contains($t, 'bara'))
                    && (str_contains($c, 'nisf') || str_contains($c, 'shaʿban') || str_contains($c, 'bara')))
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function weekdayIndex(array $item, string $iso): int
    {
        if ($iso !== '') {
            try {
                $dt = new \DateTimeImmutable($iso, $this->timezone());
                return (int) $dt->format('w');
            } catch (\Exception) {
            }
        }
        $name = mb_strtolower(trim((string) ($item['weekday_en'] ?? '')));
        $name = str_replace(['al ', 'an '], '', $name);
        $map = [
            'sunday' => 0, 'ahad' => 0, 'الاحد' => 0, 'الأحد' => 0,
            'monday' => 1, 'ithnayn' => 1, 'الاثنين' => 1,
            'tuesday' => 2, 'thalaata' => 2, 'الثلاثاء' => 2,
            'wednesday' => 3, "arba'a" => 3, 'arbaa' => 3, 'الاربعاء' => 3, 'الأربعاء' => 3,
            'thursday' => 4, 'khamees' => 4, 'الخميس' => 4,
            'friday' => 5, 'jumaah' => 5, 'jumaa' => 5, 'jumuah' => 5, 'الجمعة' => 5,
            'saturday' => 6, 'sabt' => 6, 'السبت' => 6,
        ];
        return $map[$name] ?? 0;
    }

    private function weekdayName(int $index): string
    {
        return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$index] ?? '';
    }

    /**
     * @return list<string>
     */
    private function weekdayLabels(): array
    {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    }

    /**
     * @param array<string, mixed> $today
     */
    private function todayBanner(array $today): string
    {
        $hijri = trim(($today['day'] ?? '') . ' ' . ($today['month_en'] ?? '') . ' ' . ($today['year'] ?? '') . ' AH');
        $greg = (string) ($today['gregorian_label'] ?? '');
        return $greg !== '' ? $hijri . ' · ' . $greg : $hijri;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findLocalHijri(string $iso): ?array
    {
        foreach (glob($this->dataPath() . '/*.json') ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (!is_array($data)) {
                continue;
            }
            foreach ($data['days'] ?? [] as $day) {
                if (!is_array($day)) {
                    continue;
                }
                if ($this->gregorianIso($day) !== $iso) {
                    continue;
                }
                return [
                    'day' => (int) ($day['hijri_day'] ?? 0),
                    'month' => (int) ($day['hijri_month'] ?? ($data['hijri_month'] ?? 0)),
                    'year' => (int) ($day['hijri_year'] ?? ($data['hijri_year'] ?? 0)),
                    'month_en' => (string) ($day['hijri_month_en'] ?? ($data['hijri_month_en'] ?? '')),
                    'month_ar' => (string) ($day['hijri_month_ar'] ?? ($data['hijri_month_ar'] ?? '')),
                    'gregorian_iso' => $iso,
                    'gregorian_label' => $this->gregorianLabel($day),
                    'weekday' => (string) ($day['weekday_en'] ?? ''),
                ];
            }
        }
        return null;
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
        $mo = (int) ($day['gregorian_month'] ?? $day['gregorian_month_number'] ?? 0);
        $d = (int) ($day['gregorian_day'] ?? 0);
        return $y && $mo && $d ? sprintf('%04d-%02d-%02d', $y, $mo, $d) : '';
    }

    /**
     * @param array<string, mixed> $day
     */
    private function gregorianLabel(array $day): string
    {
        $weekday = (string) ($day['weekday_en'] ?? '');
        if ($weekday !== '' && !str_starts_with(mb_strtolower($weekday), 'al ')) {
            $weekday .= ', ';
        } else {
            $iso = $this->gregorianIso($day);
            $weekday = $iso !== '' ? (new \DateTimeImmutable($iso, $this->timezone()))->format('l') . ', ' : '';
        }
        return trim($weekday . ($day['gregorian_day'] ?? '') . ' ' . ($day['gregorian_month_en'] ?? '') . ' ' . ($day['gregorian_year'] ?? ''));
    }

    /**
     * @param list<array<string, mixed>> $days
     */
    private function spanFromDays(array $days): string
    {
        if ($days === []) {
            return '';
        }
        $first = $days[0]['gregorian_label'] ?? '';
        $last = $days[array_key_last($days)]['gregorian_label'] ?? '';
        return $first && $last ? $first . ' – ' . $last : (string) $first;
    }

    private function dataPath(): string
    {
        return PUBLIC_PATH . self::DATA_DIR;
    }

    private function timezone(): \DateTimeZone
    {
        $name = (string) setting('timezone', cfg('app.timezone', 'Asia/Kolkata'));
        try {
            return new \DateTimeZone($name);
        } catch (\Exception) {
            return new \DateTimeZone('Asia/Kolkata');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function writeJson(string $file, array $data): void
    {
        @file_put_contents(
            $file,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n"
        );
    }
}

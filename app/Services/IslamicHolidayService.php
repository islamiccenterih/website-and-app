<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Major Islamic holidays with Gregorian dates from AlAdhan hToG (HJCoSA).
 * Bundled under public/assets/data/islamic-holidays.json for 2026–2031.
 */
final class IslamicHolidayService
{
    public const YEAR_MIN = 2026;
    public const YEAR_MAX = 2031;

    private const DATA_FILE = '/assets/data/islamic-holidays.json';

    public function page(?int $year = null): array
    {
        $tz = new \DateTimeZone((string) cfg('app.timezone', 'Asia/Kolkata'));
        $today = new \DateTimeImmutable('today', $tz);
        $year = $this->clampYear($year ?? (int) $today->format('Y'));

        $occurrences = $this->decorate($this->occurrences(), $today);
        $forYear = array_values(array_filter(
            $occurrences,
            static fn(array $row): bool => (int) $row['gregorian_year'] === $year
        ));
        usort($forYear, static fn(array $a, array $b): int => strcmp((string) $a['gregorian_iso'], (string) $b['gregorian_iso']));

        $eids = [];
        foreach (['eid-al-fitr', 'eid-al-adha'] as $id) {
            $match = null;
            foreach ($forYear as $row) {
                if ($row['id'] === $id) {
                    $match = $row;
                    break;
                }
            }
            if ($match === null) {
                $match = $this->nextOf($occurrences, $id);
            }
            if ($match !== null) {
                $eids[] = $match;
            }
        }

        return [
            'ok' => $forYear !== [],
            'year' => $year,
            'years' => range(self::YEAR_MIN, self::YEAR_MAX),
            'today_iso' => $today->format('Y-m-d'),
            'today_label' => $today->format('j F Y'),
            'eids' => $eids,
            'holidays' => $forYear,
            'note' => 'Dates follow Indian moon sighting, which is typically one day after the Saudi civil calendar. The Centre confirms the final day after the moon is seen in India.',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function occurrences(): array
    {
        $path = PUBLIC_PATH . self::DATA_FILE;
        $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        $rows = is_array($decoded['occurrences'] ?? null) ? $decoded['occurrences'] : [];
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row['id']) || empty($row['gregorian_iso'])) {
                continue;
            }
            $meta = $this->catalog()[(string) $row['id']] ?? null;
            if ($meta === null) {
                continue;
            }
            $out[] = array_merge($meta, $row);
        }
        return $out;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function decorate(array $rows, \DateTimeImmutable $today): array
    {
        $todayIso = $today->format('Y-m-d');
        foreach ($rows as &$row) {
            $iso = (string) $row['gregorian_iso'];
            $target = \DateTimeImmutable::createFromFormat('!Y-m-d', $iso, $today->getTimezone()) ?: $today;
            $days = (int) $today->diff($target)->format('%r%a');
            if ($iso === $todayIso) {
                $row['status'] = 'today';
                $row['days'] = 0;
                $row['when'] = tt('Today');
            } elseif ($days > 0) {
                $row['status'] = 'upcoming';
                $row['days'] = $days;
                $row['when'] = $days === 1
                    ? tt('Tomorrow')
                    : str_replace('{n}', (string) $days, tt('In {n} days'));
            } else {
                $row['status'] = 'passed';
                $row['days'] = $days;
                $abs = abs($days);
                $row['when'] = $abs === 1
                    ? tt('Yesterday')
                    : str_replace('{n}', (string) $abs, tt('{n} days ago'));
            }
            $row['gregorian_full'] = trim(
                ($row['weekday_en'] ?? '') . ', ' .
                (int) ($row['gregorian_day'] ?? 0) . ' ' .
                ($row['gregorian_month_en'] ?? '') . ' ' .
                (int) ($row['gregorian_year'] ?? 0)
            );
            $row['hijri_full'] = trim(
                (int) ($row['hijri_day'] ?? 0) . ' ' .
                ($row['hijri_month_en'] ?? '') . ' ' .
                (int) ($row['hijri_year'] ?? 0) . ' AH'
            );
        }
        unset($row);
        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<string, mixed>|null
     */
    private function nextOf(array $rows, string $id): ?array
    {
        $best = null;
        foreach ($rows as $row) {
            if ($row['id'] !== $id) {
                continue;
            }
            if (($row['status'] ?? '') === 'passed') {
                continue;
            }
            if ($best === null || strcmp((string) $row['gregorian_iso'], (string) $best['gregorian_iso']) < 0) {
                $best = $row;
            }
        }
        if ($best !== null) {
            return $best;
        }
        $last = null;
        foreach ($rows as $row) {
            if ($row['id'] === $id) {
                $last = $row;
            }
        }
        return $last;
    }

    public function clampYear(int $year): int
    {
        if ($year < self::YEAR_MIN) {
            return self::YEAR_MIN;
        }
        if ($year > self::YEAR_MAX) {
            return self::YEAR_MAX;
        }
        return $year;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function catalog(): array
    {
        return [
            'eid-al-fitr' => [
                'id' => 'eid-al-fitr',
                'name' => 'Eid ul-Fitr',
                'name_ar' => 'عید الفطر',
                'blurb' => 'The festival of breaking the fast — 1 Shawwal, the morning after Ramadan.',
                'featured' => true,
                'tone' => 'fitr',
            ],
            'eid-al-adha' => [
                'id' => 'eid-al-adha',
                'name' => 'Eid al-Adha',
                'name_ar' => 'عید الاضحیٰ',
                'blurb' => 'The festival of sacrifice — 10 Dhul Hijjah, after the Day of ʿArafah.',
                'featured' => true,
                'tone' => 'adha',
            ],
            'islamic-new-year' => [
                'id' => 'islamic-new-year',
                'name' => 'Islamic New Year',
                'name_ar' => 'رأس السنة الهجرية',
                'blurb' => '1 Muharram — the first day of the Hijri year.',
                'featured' => false,
                'tone' => '',
            ],
            'ashura' => [
                'id' => 'ashura',
                'name' => 'Ashura',
                'name_ar' => 'عاشوراء',
                'blurb' => '10 Muharram — a day of fasting and remembrance.',
                'featured' => false,
                'tone' => '',
            ],
            'mawlid' => [
                'id' => 'mawlid',
                'name' => 'Mawlid an-Nabi ﷺ',
                'name_ar' => 'المولد النبوي',
                'blurb' => '12 Rabiʿ al-awwal — the birth of the Prophet ﷺ.',
                'featured' => false,
                'tone' => '',
            ],
            'isra-miraj' => [
                'id' => 'isra-miraj',
                'name' => 'Isra and Miʿraj',
                'name_ar' => 'الإسراء والمعراج',
                'blurb' => '27 Rajab — the night journey and ascension.',
                'featured' => false,
                'tone' => '',
            ],
            'nisf-shaban' => [
                'id' => 'nisf-shaban',
                'name' => 'Nisf Shaʿban',
                'name_ar' => 'ليلة النصف من شعبان',
                'blurb' => '15 Shaʿban — Shab-e-Barat.',
                'featured' => false,
                'tone' => '',
            ],
            'ramadan-start' => [
                'id' => 'ramadan-start',
                'name' => 'First day of Ramadan',
                'name_ar' => 'أول رمضان',
                'blurb' => '1 Ramadan — the blessed month begins.',
                'featured' => false,
                'tone' => '',
            ],
            'laylat-al-qadr' => [
                'id' => 'laylat-al-qadr',
                'name' => 'Laylat al-Qadr',
                'name_ar' => 'ليلة القدر',
                'blurb' => '27 Ramadan — commonly observed as the Night of Power.',
                'featured' => false,
                'tone' => '',
            ],
            'arafah' => [
                'id' => 'arafah',
                'name' => 'Day of ʿArafah',
                'name_ar' => 'يوم عرفة',
                'blurb' => '9 Dhul Hijjah — the day before Eid al-Adha.',
                'featured' => false,
                'tone' => '',
            ],
        ];
    }
}

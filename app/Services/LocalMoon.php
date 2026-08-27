<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Moon phase and rise/set from local astronomy (no outbound HTTP).
 */
final class LocalMoon
{
    /**
     * @return array<string, mixed>
     */
    public static function forDay(float $lat, float $lng, \DateTimeImmutable $day): array
    {
        $tz = $day->getTimezone();
        $start = $day->setTime(0, 0);
        $jd0 = self::julian($start);
        $age = self::lunarAge($jd0 + 0.5);
        $synodic = 29.53058867;
        $illum = (1 - cos(2 * M_PI * $age / $synodic)) / 2 * 100;
        $phaseValue = $age / $synodic;
        $sun = LocalPrayerTimes::compute($lat, $lng, $day);
        $times = self::moonTimes($lat, $lng, $start);

        return [
            'phase' => self::phaseName($age),
            'illumination' => round($illum, 1),
            'phase_value' => round($phaseValue, 4),
            'moonrise' => $times['rise'],
            'moonset' => $times['set'],
            'sunrise' => self::toTwelve($sun['sunrise']),
            'sunset' => self::toTwelve($sun['sunset']),
            'golden_hour' => null,
            'timezone' => $tz->getName(),
            'date' => $day->format('Y-m-d'),
        ];
    }

    /**
     * @return array{rise:?string,set:?string}
     */
    private static function moonTimes(float $lat, float $lng, \DateTimeImmutable $midnight): array
    {
        $rise = null;
        $set = null;
        $prev = self::moonAltitude($lat, $lng, $midnight);
        for ($h = 1; $h <= 24; $h++) {
            $at = $midnight->modify('+' . $h . ' hours');
            $alt = self::moonAltitude($lat, $lng, $at);
            if ($prev < 0 && $alt >= 0 && $rise === null) {
                $rise = self::interpTime($midnight, $h - 1, $prev, $alt);
            }
            if ($prev >= 0 && $alt < 0 && $set === null) {
                $set = self::interpTime($midnight, $h - 1, $prev, $alt);
            }
            $prev = $alt;
        }

        return ['rise' => $rise, 'set' => $set];
    }

    private static function interpTime(\DateTimeImmutable $midnight, int $hour, float $a, float $b): string
    {
        $frac = abs($b - $a) < 1e-6 ? 0.0 : $a / ($a - $b);
        $minutes = (int) round(($hour + $frac) * 60);
        $minutes = max(0, min(1439, $minutes));

        return $midnight->modify('+' . $minutes . ' minutes')->format('g:i A');
    }

    private static function moonAltitude(float $lat, float $lng, \DateTimeImmutable $when): float
    {
        $jd = self::julian($when);
        $d = $jd - 2451545.0;
        $L = self::fix(218.316 + 13.176396 * $d);
        $M = self::fix(134.963 + 13.064993 * $d);
        $F = self::fix(93.272 + 13.229350 * $d);
        $lon = $L + 6.289 * sin(deg2rad($M));
        $latMoon = 5.128 * sin(deg2rad($F));
        $e = deg2rad(23.439 - 0.00000036 * $d);
        $lambda = deg2rad($lon);
        $beta = deg2rad($latMoon);
        $x = cos($beta) * cos($lambda);
        $y = cos($e) * cos($beta) * sin($lambda) - sin($e) * sin($beta);
        $z = sin($e) * cos($beta) * sin($lambda) + cos($e) * sin($beta);
        $ra = atan2($y, $x);
        $dec = atan2($z, sqrt($x * $x + $y * $y));

        $gmst = self::fix(280.16 + 360.9856235 * $d);
        $lst = deg2rad(self::fix($gmst + $lng));
        $ha = $lst - $ra;
        $latR = deg2rad($lat);
        $alt = asin(sin($latR) * sin($dec) + cos($latR) * cos($dec) * cos($ha));

        return rad2deg($alt) - 0.727 * 0.145;
    }

    private static function lunarAge(float $jd): float
    {
        $synodic = 29.53058867;
        $knownNew = 2451550.1;
        $age = fmod($jd - $knownNew, $synodic);

        return $age < 0 ? $age + $synodic : $age;
    }

    private static function phaseName(float $age): string
    {
        if ($age < 1.84566) {
            return 'New Moon';
        }
        if ($age < 5.53699) {
            return 'Waxing Crescent';
        }
        if ($age < 9.22831) {
            return 'First Quarter';
        }
        if ($age < 12.91963) {
            return 'Waxing Gibbous';
        }
        if ($age < 16.61096) {
            return 'Full Moon';
        }
        if ($age < 20.30228) {
            return 'Waning Gibbous';
        }
        if ($age < 23.99361) {
            return 'Last Quarter';
        }
        if ($age < 27.68493) {
            return 'Waning Crescent';
        }

        return 'New Moon';
    }

    private static function julian(\DateTimeImmutable $when): float
    {
        $utc = $when->setTimezone(new \DateTimeZone('UTC'));
        $y = (int) $utc->format('Y');
        $m = (int) $utc->format('n');
        $d = ((int) $utc->format('j'))
            + ((int) $utc->format('G')) / 24
            + ((int) $utc->format('i')) / 1440
            + ((int) $utc->format('s')) / 86400;
        if ($m <= 2) {
            $y -= 1;
            $m += 12;
        }
        $a = (int) floor($y / 100);
        $b = 2 - $a + (int) floor($a / 4);

        return (int) floor(365.25 * ($y + 4716))
            + (int) floor(30.6001 * ($m + 1))
            + $d + $b - 1524.5;
    }

    private static function fix(float $a): float
    {
        $a = fmod($a, 360.0);

        return $a < 0 ? $a + 360.0 : $a;
    }

    private static function toTwelve(string $hhmm): string
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', $hhmm, $m)) {
            return $hhmm;
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
}

<?php

declare(strict_types=1);

namespace App\Services;

/**
 * University of Islamic Sciences, Karachi (fajr/isha 18°) + Hanafi Asr.
 * Computed locally so Cloudways does not depend on AlAdhan staying reachable.
 */
final class LocalPrayerTimes
{
    /**
     * @return array{fajr:string,sunrise:string,dhuhr:string,asr:string,maghrib:string,isha:string,imsak:string,sunset:string}
     */
    public static function compute(float $lat, float $lng, \DateTimeImmutable $date): array
    {
        $tzHours = ((int) $date->format('Z')) / 3600.0;
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');
        $jDate = self::julian($year, $month, $day) - $lng / (15.0 * 24.0);

        $times = [
            'imsak' => 5.0,
            'fajr' => 5.0,
            'sunrise' => 6.0,
            'dhuhr' => 12.0,
            'asr' => 13.0,
            'sunset' => 18.0,
            'maghrib' => 18.0,
            'isha' => 18.0,
        ];

        for ($i = 0; $i < 2; $i++) {
            $times = self::computeOnce($lat, $jDate, $times);
        }

        $times['imsak'] = $times['fajr'] - 10 / 60;
        $times['maghrib'] = $times['sunset'];
        foreach ($times as $name => $hour) {
            $times[$name] = $hour + $tzHours - $lng / 15.0;
        }

        return [
            'imsak' => self::toClock($times['imsak']),
            'fajr' => self::toClock($times['fajr']),
            'sunrise' => self::toClock($times['sunrise']),
            'dhuhr' => self::toClock($times['dhuhr']),
            'asr' => self::toClock($times['asr']),
            'sunset' => self::toClock($times['sunset']),
            'maghrib' => self::toClock($times['maghrib']),
            'isha' => self::toClock($times['isha']),
        ];
    }

    /**
     * @param array<string, float> $times
     * @return array<string, float>
     */
    private static function computeOnce(float $lat, float $jDate, array $times): array
    {
        $dhuhr = self::midDay($jDate, $times['dhuhr']);
        $sunrise = self::sunAngleTime($lat, $jDate, $times['sunrise'], 0.833, true);
        $sunset = self::sunAngleTime($lat, $jDate, $times['sunset'], 0.833, false);
        $fajr = self::sunAngleTime($lat, $jDate, $times['fajr'], 18.0, true);
        $isha = self::sunAngleTime($lat, $jDate, $times['isha'], 18.0, false);
        $asr = self::asrTime($lat, $jDate, $times['asr'], 2);

        return [
            'imsak' => $fajr - 10 / 60,
            'fajr' => $fajr,
            'sunrise' => $sunrise,
            'dhuhr' => $dhuhr,
            'asr' => $asr,
            'sunset' => $sunset,
            'maghrib' => $sunset,
            'isha' => $isha,
        ];
    }

    private static function julian(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }
        $a = (int) floor($year / 100);
        $b = 2 - $a + (int) floor($a / 4);

        return (int) floor(365.25 * ($year + 4716))
            + (int) floor(30.6001 * ($month + 1))
            + $day + $b - 1524.5;
    }

    private static function sunPosition(float $jd): array
    {
        $d = $jd - 2451545.0;
        $g = self::fixAngle(357.529 + 0.98560028 * $d);
        $q = self::fixAngle(280.459 + 0.98564736 * $d);
        $l = self::fixAngle($q + 1.915 * self::sin($g) + 0.020 * self::sin(2 * $g));
        $e = 23.439 - 0.00000036 * $d;
        $ra = self::arctan2(self::cos($e) * self::sin($l), self::cos($l)) / 15.0;
        $decl = self::arcsin(self::sin($e) * self::sin($l));
        $eqt = $q / 15.0 - self::fixHour($ra);

        return ['declination' => $decl, 'equation' => $eqt];
    }

    private static function midDay(float $jDate, float $time): float
    {
        $eqt = self::sunPosition($jDate + $time / 24.0)['equation'];

        return self::fixHour(12 - $eqt);
    }

    private static function sunAngleTime(float $lat, float $jDate, float $time, float $angle, bool $ccw): float
    {
        $decl = self::sunPosition($jDate + $time / 24.0)['declination'];
        $noon = self::midDay($jDate, $time);
        $t = (1 / 15.0) * self::arccos(
            (-self::sin($angle) - self::sin($decl) * self::sin($lat))
            / (self::cos($decl) * self::cos($lat))
        );

        return $noon + ($ccw ? -$t : $t);
    }

    private static function asrTime(float $lat, float $jDate, float $time, float $factor): float
    {
        $decl = self::sunPosition($jDate + $time / 24.0)['declination'];
        $angle = -self::arccot($factor + self::tan(abs($lat - $decl)));

        return self::sunAngleTime($lat, $jDate, $time, $angle, false);
    }

    private static function toClock(float $hours): string
    {
        $hours = self::fixHour($hours + 0.5 / 60.0);
        $h = (int) floor($hours);
        $m = (int) floor(($hours - $h) * 60.0);

        return sprintf('%02d:%02d', $h, $m);
    }

    private static function sin(float $d): float
    {
        return sin(deg2rad($d));
    }

    private static function cos(float $d): float
    {
        return cos(deg2rad($d));
    }

    private static function tan(float $d): float
    {
        return tan(deg2rad($d));
    }

    private static function arcsin(float $x): float
    {
        return rad2deg(asin(max(-1.0, min(1.0, $x))));
    }

    private static function arccos(float $x): float
    {
        return rad2deg(acos(max(-1.0, min(1.0, $x))));
    }

    private static function arctan2(float $y, float $x): float
    {
        return rad2deg(atan2($y, $x));
    }

    private static function arccot(float $x): float
    {
        return rad2deg(atan(1 / $x));
    }

    private static function fixAngle(float $a): float
    {
        $a = fmod($a, 360.0);

        return $a < 0 ? $a + 360.0 : $a;
    }

    private static function fixHour(float $a): float
    {
        $a = fmod($a, 24.0);

        return $a < 0 ? $a + 24.0 : $a;
    }
}

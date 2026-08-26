<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Qibla bearing from any coordinates to the Kaaba (great-circle / true north).
 */
final class QiblaService
{
    public const KAABA_LAT = 21.422487;
    public const KAABA_LNG = 39.826206;

    public function defaultPoint(): array
    {
        return [
            'lat' => (float) setting('location_lat', '27.1591'),
            'lng' => (float) setting('location_lng', '78.3957'),
            'label' => (string) setting('location_label', 'Firozabad, Uttar Pradesh, India 283203'),
        ];
    }

    public function for(?float $lat, ?float $lng): array
    {
        $fallback = $this->defaultPoint();
        $fromGps = $lat !== null && $lng !== null;
        $lat = $fromGps ? $this->clamp($lat, -90, 90) : $fallback['lat'];
        $lng = $fromGps ? $this->clamp($lng, -180, 180) : $fallback['lng'];

        $local = $this->bearing($lat, $lng, self::KAABA_LAT, self::KAABA_LNG);
        $distance = $this->distanceKm($lat, $lng, self::KAABA_LAT, self::KAABA_LNG);

        return [
            'ok' => true,
            'lat' => round($lat, 6),
            'lng' => round($lng, 6),
            'qibla' => round($local, 2),
            'distance_km' => round($distance, 1),
            'kaaba_lat' => self::KAABA_LAT,
            'kaaba_lng' => self::KAABA_LNG,
            'source' => 'calculated',
            'using_default' => !$fromGps,
            'label' => $fromGps ? 'Your GPS location' : $fallback['label'],
            'compass' => $this->cardinal($local),
        ];
    }

    private function bearing(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δλ = deg2rad($lng2 - $lng1);
        $y = sin($Δλ) * cos($φ2);
        $x = cos($φ1) * sin($φ2) - sin($φ1) * cos($φ2) * cos($Δλ);
        $θ = atan2($y, $x);
        return fmod(rad2deg($θ) + 360, 360);
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371.0;
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lng2 - $lng1);
        $a = sin($Δφ / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($Δλ / 2) ** 2;
        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function cardinal(float $deg): string
    {
        $names = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        return $names[(int) round($deg / 45) % 8];
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Live gold/silver spot prices for nisab, then a 2.5% zakat calculation.
 * Last good rates are stored in the repo so the page still works if a feed is down.
 */
final class ZakatService
{
    private const TROY_OUNCE_GRAMS = 31.1034768;
    private const GOLD_NISAB_G = 87.48;
    private const SILVER_NISAB_G = 612.36;
    private const RATE = 2.5;

    /**
     * Fresh India IBJA rates for the calculator. Cached for 10 minutes.
     *
     * @return array<string, mixed>
     */
    public function liveSnapshot(): array
    {
        $tz = new \DateTimeZone('Asia/Kolkata');
        $today = (new \DateTimeImmutable('now', $tz))->format('Y-m-d');
        $cfg = $this->config();
        $file = STORAGE_PATH . '/cache/zakat-india.json';
        $cached = HttpJson::read($file);
        if (is_array($cached) && !empty($cached['india']) && !empty($cached['gold_per_gram_inr']) && !empty($cached['fetched_at'])) {
            $age = time() - strtotime((string) $cached['fetched_at']);
            if ($age >= 0 && $age < 600) {
                $cached['live'] = true;
                $cached['stale'] = false;
                $cached['ok'] = true;
                return $this->withConfig($cached, $cfg);
            }
        }
        try {
            $fresh = $this->fetchIndia($today);
            HttpJson::write($file, $fresh);
            return $this->withConfig($fresh, $cfg);
        } catch (\Throwable) {
            return $this->withConfig([
                'ok' => false,
                'live' => false,
                'stale' => true,
                'india' => false,
                'error' => 'India gold and silver rates could not be loaded.',
                'for_date' => $today,
                'gold_per_gram_inr' => 0,
                'silver_per_gram_inr' => 0,
            ], $cfg);
        }
    }

    public function snapshot(): array
    {
        $tz = new \DateTimeZone('Asia/Kolkata');
        $today = (new \DateTimeImmutable('now', $tz))->format('Y-m-d');
        $cfg = $this->config();
        $cached = $this->loadCached($today);
        if ($cached !== null) {
            $this->syncPublicFallback($cached);
            return $this->withConfig($cached, $cfg);
        }

        $stored = HttpJson::read(PUBLIC_PATH . '/assets/data/zakat-spot.json');
        $haveFallback = is_array($stored) && !empty($stored['gold_per_gram_inr']);
        if ($haveFallback) {
            $stored['ok'] = true;
            $stored['stale'] = ($stored['for_date'] ?? '') !== $today;
            $stored['error'] = null;
            return $this->withConfig($stored, $cfg);
        }

        try {
            $fresh = $this->fetchSpot($today);
            $this->persist($fresh);
            return $this->withConfig($fresh, $cfg);
        } catch (\Throwable) {
            return $this->withConfig([
                'ok' => false,
                'error' => 'Metal prices are temporarily unavailable. Try again in a moment.',
                'for_date' => $today,
                'gold_per_gram_inr' => 0,
                'silver_per_gram_inr' => 0,
                'usd_inr' => 0,
                'stale' => false,
            ], $cfg);
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    public function calculate(array $input, ?array $snapshot = null): array
    {
        $snap = $snapshot ?? $this->snapshot();
        $goldG = $this->money($input['gold_grams'] ?? 0);
        $goldKarat = (float) ($input['gold_karat'] ?? 24);
        if ($goldKarat < 9) {
            $goldKarat = 9;
        }
        if ($goldKarat > 24) {
            $goldKarat = 24;
        }
        $silverG = $this->money($input['silver_grams'] ?? 0);
        $cash = $this->money($input['cash'] ?? 0);
        $bank = $this->money($input['bank'] ?? 0);
        $business = $this->money($input['business'] ?? 0);
        $receivables = $this->money($input['receivables'] ?? 0);
        $investments = $this->money($input['investments'] ?? 0);
        $crypto = $this->money($input['crypto'] ?? 0);
        $other = $this->money($input['other'] ?? 0);
        $debts = $this->money($input['debts'] ?? 0);

        $goldValue = $goldG * ($goldKarat / 24) * (float) ($snap['gold_per_gram_inr'] ?? 0);
        $silverValue = $silverG * (float) ($snap['silver_per_gram_inr'] ?? 0);
        $assets = $goldValue + $silverValue + $cash + $bank + $business + $receivables + $investments + $crypto + $other;
        $net = max(0, $assets - $debts);

        $goldNisab = (float) $snap['gold_nisab_inr'];
        $silverNisab = (float) $snap['silver_nisab_inr'];
        $method = (string) ($snap['nisab_method'] ?? 'lower');
        $nisab = match ($method) {
            'gold' => $goldNisab,
            'silver' => $silverNisab,
            default => min($goldNisab, $silverNisab) ?: max($goldNisab, $silverNisab),
        };
        $due = $net >= $nisab && $nisab > 0;
        $rate = (float) ($snap['rate'] ?? self::RATE);
        $zakat = $due ? round($net * ($rate / 100), 2) : 0.0;

        return [
            'ok' => !empty($snap['ok']),
            'assets' => round($assets, 2),
            'debts' => round($debts, 2),
            'net' => round($net, 2),
            'gold_value' => round($goldValue, 2),
            'silver_value' => round($silverValue, 2),
            'nisab' => round($nisab, 2),
            'gold_nisab' => round($goldNisab, 2),
            'silver_nisab' => round($silverNisab, 2),
            'above_nisab' => $due,
            'rate' => $rate,
            'zakat' => $zakat,
            'lines' => [
                ['label' => 'Gold', 'amount' => round($goldValue, 2)],
                ['label' => 'Silver', 'amount' => round($silverValue, 2)],
                ['label' => 'Cash in hand', 'amount' => round($cash, 2)],
                ['label' => 'Bank and savings', 'amount' => round($bank, 2)],
                ['label' => 'Business stock', 'amount' => round($business, 2)],
                ['label' => 'Money owed to you', 'amount' => round($receivables, 2)],
                ['label' => 'Shares and funds', 'amount' => round($investments, 2)],
                ['label' => 'Crypto and digital', 'amount' => round($crypto, 2)],
                ['label' => 'Other zakatable wealth', 'amount' => round($other, 2)],
                ['label' => 'Debts due now', 'amount' => round(-$debts, 2)],
            ],
        ];
    }

    /**
     * @return array{gold_nisab_g:float,silver_nisab_g:float,rate:float,nisab_method:string,notes:string}
     */
    public function config(): array
    {
        $tools = json_setting('worship_tools');
        $gold = (float) ($tools['gold_nisab_g'] ?? 0);
        $silver = (float) ($tools['silver_nisab_g'] ?? 0);
        $rate = (float) ($tools['zakat_rate'] ?? 0);
        $method = (string) ($tools['nisab_method'] ?? 'lower');
        if (!in_array($method, ['lower', 'gold', 'silver'], true)) {
            $method = 'lower';
        }
        return [
            'gold_nisab_g' => $gold > 0 ? $gold : self::GOLD_NISAB_G,
            'silver_nisab_g' => $silver > 0 ? $silver : self::SILVER_NISAB_G,
            'rate' => $rate > 0 ? $rate : self::RATE,
            'nisab_method' => $method,
            'notes' => trim((string) ($tools['zakat_notes'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $snap
     * @param array<string, mixed> $cfg
     * @return array<string, mixed>
     */
    private function withConfig(array $snap, array $cfg): array
    {
        $goldG = (float) $cfg['gold_nisab_g'];
        $silverG = (float) $cfg['silver_nisab_g'];
        $goldPerG = (float) ($snap['gold_per_gram_inr'] ?? 0);
        $silverPerG = (float) ($snap['silver_per_gram_inr'] ?? 0);
        $snap['gold_nisab_g'] = $goldG;
        $snap['silver_nisab_g'] = $silverG;
        $snap['rate'] = (float) $cfg['rate'];
        $snap['nisab_method'] = $cfg['nisab_method'];
        $snap['notes'] = $cfg['notes'];
        $snap['gold_nisab_inr'] = round($goldG * $goldPerG, 2);
        $snap['silver_nisab_inr'] = round($silverG * $silverPerG, 2);
        $snap['gold_per_10g_inr'] = round($goldPerG * 10, 2);
        $snap['silver_per_kg_inr'] = round($silverPerG * 1000, 2);
        return $snap;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchIndia(string $today): array
    {
        $html = '';
        try {
            $html = $this->fetchPage('https://r.jina.ai/https://ibjarates.com/', 12);
        } catch (\Throwable) {
            $html = '';
        }
        if ($html === '') {
            $html = $this->fetchPage('https://ibjarates.com/', 8);
        }
        $row = $this->parseIbja($html);
        if ($row === null) {
            throw new \RuntimeException('India bullion table was empty.');
        }
        $gst = 1.03;
        $gold10 = $row['gold10'] * $gst;
        $silverKg = $row['silver_kg'] * $gst;
        $goldPerG = $gold10 / 10;
        $silverPerG = $silverKg / 1000;
        $parts = explode('/', $row['date']);
        $iso = count($parts) === 3 ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $today;

        return [
            'ok' => true,
            'error' => null,
            'stale' => false,
            'live' => true,
            'india' => true,
            'for_date' => $iso,
            'fetched_at' => gmdate('c'),
            'source' => 'IBJA India 24k + 3% GST',
            'gold_per_gram_inr' => round($goldPerG, 4),
            'silver_per_gram_inr' => round($silverPerG, 4),
            'purity' => 'India 24k / 999 silver (IBJA + GST, not jewellery making charges)',
        ];
    }

    /**
     * @return array{date:string,gold10:float,silver_kg:float}|null
     */
    private function parseIbja(string $text): ?array
    {
        $best = null;
        if (preg_match_all('/\|\s*\*{0,2}(\d{2}\/\d{2}\/\d{4})\*{0,2}\s*\|\s*(\d{5,7})\s*\|\s*\d+\s*\|\s*\d+\s*\|\s*\d+\s*\|\s*\d+\s*\|\s*(\d{5,7})\s*\|/', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $row = [
                    'date' => $match[1],
                    'gold10' => (float) $match[2],
                    'silver_kg' => (float) $match[3],
                ];
                if ($best === null || $row['date'] === $best['date']) {
                    $best = $row;
                }
            }
        }
        if ($best !== null) {
            return $best;
        }
        if (
            preg_match('/data-label="Gold 999">\s*(\d{5,7})/', $text, $gold)
            && preg_match('/data-label="Silver 999">\s*(\d{5,7})/', $text, $silver)
        ) {
            $date = '';
            if (preg_match('/<strong>(\d{2}\/\d{2}\/\d{4})<\/strong>/', $text, $d)) {
                $date = $d[1];
            }
            return [
                'date' => $date,
                'gold10' => (float) $gold[1],
                'silver_kg' => (float) $silver[1],
            ];
        }
        return null;
    }

    private function fetchPage(string $url, int $timeout): string
    {
        $timeout = max(3, $timeout);
        if (!function_exists('curl_init')) {
            return HttpJson::fetch($url, $timeout);
        }
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Upstream request failed.');
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 4,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,text/plain,*/*',
                'User-Agent: Mozilla/5.0 (compatible; IslamicCenter/1.2)',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if (is_string($raw) && $raw !== '' && $code > 0 && $code < 400) {
            return $raw;
        }
        throw new \RuntimeException($err !== '' ? $err : 'Upstream HTTP ' . $code);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchSpot(string $today): array
    {
        $goldOz = 0.0;
        $silverOz = 0.0;
        $usdInr = 0.0;
        try {
            $gold = HttpJson::get('https://api.gold-api.com/price/XAU', 2, 1);
            $goldOz = (float) ($gold['price'] ?? 0);
        } catch (\Throwable) {
            $goldOz = 0.0;
        }
        try {
            $silver = HttpJson::get('https://api.gold-api.com/price/XAG', 2, 1);
            $silverOz = (float) ($silver['price'] ?? 0);
        } catch (\Throwable) {
            $silverOz = 0.0;
        }
        if ($goldOz <= 0 || $silverOz <= 0) {
            try {
                $spot = HttpJson::get('https://api.metals.live/v1/spot', 2, 1);
                if (isset($spot[0]) && is_array($spot[0])) {
                    foreach ($spot as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        $metal = strtolower((string) ($row['metal'] ?? array_key_first($row) ?? ''));
                        $price = (float) ($row['price'] ?? $row[$metal] ?? 0);
                        if (str_contains($metal, 'gold') && $price > 0) {
                            $goldOz = $price;
                        }
                        if (str_contains($metal, 'silver') && $price > 0) {
                            $silverOz = $price;
                        }
                    }
                } elseif (is_array($spot)) {
                    foreach ($spot as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        if (isset($row['gold'])) {
                            $goldOz = (float) $row['gold'];
                        }
                        if (isset($row['silver'])) {
                            $silverOz = (float) $row['silver'];
                        }
                    }
                }
            } catch (\Throwable) {
                // Fall through to the stored rates.
            }
        }
        try {
            $fx = HttpJson::get('https://api.frankfurter.dev/v1/latest?from=USD&to=INR', 2, 1);
            $usdInr = (float) ($fx['rates']['INR'] ?? 0);
        } catch (\Throwable) {
            $usdInr = 0.0;
        }
        if ($usdInr <= 0) {
            try {
                $fx = HttpJson::get('https://api.frankfurter.app/latest?from=USD&to=INR', 2, 1);
                $usdInr = (float) ($fx['rates']['INR'] ?? 0);
            } catch (\Throwable) {
                $usdInr = 0.0;
            }
        }
        if ($usdInr <= 0) {
            try {
                $fx2 = HttpJson::get('https://open.er-api.com/v6/latest/USD', 2, 1);
                $usdInr = (float) ($fx2['rates']['INR'] ?? 0);
            } catch (\Throwable) {
                $usdInr = 0.0;
            }
        }
        if ($goldOz <= 0 || $silverOz <= 0 || $usdInr <= 0) {
            throw new \RuntimeException('Spot payload was incomplete.');
        }
        $goldPerG = ($goldOz / self::TROY_OUNCE_GRAMS) * $usdInr;
        $silverPerG = ($silverOz / self::TROY_OUNCE_GRAMS) * $usdInr;

        return [
            'ok' => true,
            'error' => null,
            'stale' => false,
            'for_date' => $today,
            'fetched_at' => gmdate('c'),
            'source' => 'gold-api.com + frankfurter.dev',
            'gold_usd_oz' => round($goldOz, 4),
            'silver_usd_oz' => round($silverOz, 4),
            'usd_inr' => round($usdInr, 4),
            'gold_per_gram_inr' => round($goldPerG, 4),
            'silver_per_gram_inr' => round($silverPerG, 4),
            'purity' => '24k / 999 silver spot (value of the metal, not jewellery making charges)',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadCached(string $today): ?array
    {
        $file = STORAGE_PATH . '/cache/zakat-spot.json';
        $data = HttpJson::read($file);
        if (!is_array($data) || ($data['for_date'] ?? '') !== $today || empty($data['gold_per_gram_inr'])) {
            return null;
        }
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function persist(array $data): void
    {
        HttpJson::write(STORAGE_PATH . '/cache/zakat-spot.json', $data);
        $this->syncPublicFallback($data);
    }

    /**
     * Keep the public fallback file on the same day as live rates so a fresh
     * hosting upload still has a usable nisab if the first API call fails.
     *
     * @param array<string, mixed> $data
     */
    private function syncPublicFallback(array $data): void
    {
        $public = PUBLIC_PATH . '/assets/data/zakat-spot.json';
        $existing = HttpJson::read($public);
        if (
            is_array($existing)
            && ($existing['for_date'] ?? '') === ($data['for_date'] ?? '')
            && !empty($existing['gold_per_gram_inr'])
        ) {
            return;
        }
        HttpJson::write($public, $data);
    }

    private function money(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace([',', '₹', ' '], '', $value);
        }
        $n = (float) $value;
        return $n < 0 ? 0.0 : $n;
    }
}

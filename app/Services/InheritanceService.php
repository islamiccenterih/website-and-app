<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Hanafi estimate for the common nuclear-family case (spouse, children, parents,
 * and siblings only when there is no son and no father). Not a fatwa.
 */
final class InheritanceService
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $estate = $this->money($input['estate'] ?? 0);
        $deceased = (($input['deceased'] ?? 'male') === 'female') ? 'female' : 'male';
        $spouse = !empty($input['spouse']);
        $sons = max(0, (int) ($input['sons'] ?? 0));
        $daughters = max(0, (int) ($input['daughters'] ?? 0));
        $father = !empty($input['father']);
        $mother = !empty($input['mother']);
        $brothers = max(0, (int) ($input['brothers'] ?? 0));
        $sisters = max(0, (int) ($input['sisters'] ?? 0));

        $kids = $sons + $daughters;
        $descendants = $kids > 0;
        $shares = [];

        if ($spouse && $deceased === 'female') {
            $shares[] = ['key' => 'husband', 'label' => 'Husband', 'portion' => $descendants ? 1 / 4 : 1 / 2];
        }
        if ($spouse && $deceased === 'male') {
            $shares[] = ['key' => 'wife', 'label' => 'Wife', 'portion' => $descendants ? 1 / 8 : 1 / 4];
        }

        $umariyyah = $spouse && $father && $mother && !$descendants;
        if ($mother) {
            if ($descendants || ($brothers + $sisters) >= 2) {
                $shares[] = ['key' => 'mother', 'label' => 'Mother', 'portion' => 1 / 6];
            } elseif ($umariyyah) {
                $spousePart = $deceased === 'female' ? 1 / 2 : 1 / 4;
                $shares[] = ['key' => 'mother', 'label' => 'Mother (1/3 of remainder)', 'portion' => (1 - $spousePart) / 3];
            } else {
                $shares[] = ['key' => 'mother', 'label' => 'Mother', 'portion' => 1 / 3];
            }
        }
        if ($father) {
            if ($descendants) {
                $shares[] = ['key' => 'father', 'label' => 'Father', 'portion' => 1 / 6];
            } else {
                $shares[] = ['key' => 'father', 'label' => 'Father (residue)', 'portion' => 0, 'asaba' => true];
            }
        }

        if ($sons === 0 && $daughters === 1) {
            $shares[] = ['key' => 'daughter', 'label' => 'Daughter', 'portion' => 1 / 2, 'count' => 1];
        } elseif ($sons === 0 && $daughters >= 2) {
            $shares[] = ['key' => 'daughters', 'label' => 'Daughters', 'portion' => 2 / 3, 'count' => $daughters];
        } elseif ($sons > 0) {
            $shares[] = ['key' => 'children', 'label' => 'Sons and daughters (2:1)', 'portion' => 0, 'asaba' => true, 'sons' => $sons, 'daughters' => $daughters];
        }

        $asabaSiblings = !$descendants && !$father && ($brothers > 0 || $sisters > 0);
        if ($asabaSiblings && $brothers === 0 && $sisters === 1) {
            $shares[] = ['key' => 'sister', 'label' => 'Full sister', 'portion' => 1 / 2, 'count' => 1];
        } elseif ($asabaSiblings && $brothers === 0 && $sisters >= 2) {
            $shares[] = ['key' => 'sisters', 'label' => 'Full sisters', 'portion' => 2 / 3, 'count' => $sisters];
        } elseif ($asabaSiblings && $brothers > 0) {
            $shares[] = ['key' => 'siblings', 'label' => 'Full brothers and sisters (2:1)', 'portion' => 0, 'asaba' => true, 'sons' => $brothers, 'daughters' => $sisters];
        }

        $fixed = 0.0;
        foreach ($shares as $row) {
            if (empty($row['asaba'])) {
                $fixed += (float) $row['portion'];
            }
        }

        $awl = $fixed > 1.0001;
        $radd = $fixed < 0.999 && !$this->hasAsaba($shares);
        $scale = 1.0;
        if ($awl && $fixed > 0) {
            $scale = 1 / $fixed;
        }

        $assigned = 0.0;
        $out = [];
        foreach ($shares as $row) {
            if (!empty($row['asaba'])) {
                continue;
            }
            $portion = (float) $row['portion'] * $scale;
            if ($radd && $fixed > 0) {
                $portion = (float) $row['portion'] / $fixed;
            }
            $amount = round($estate * $portion, 2);
            $assigned += $amount;
            $out[] = $this->line($row, $portion, $amount);
        }

        $residue = max(0, round($estate - $assigned, 2));
        foreach ($shares as $row) {
            if (empty($row['asaba']) || $residue <= 0) {
                continue;
            }
            $s = (int) ($row['sons'] ?? 0);
            $d = (int) ($row['daughters'] ?? 0);
            if (($row['key'] ?? '') === 'father') {
                $out[] = $this->line($row, $estate > 0 ? $residue / $estate : 0, $residue);
                $residue = 0;
                continue;
            }
            $units = ($s * 2) + $d;
            if ($units <= 0) {
                $out[] = $this->line($row, $estate > 0 ? $residue / $estate : 0, $residue);
                $residue = 0;
                continue;
            }
            $sonAmt = round($residue * (2 / $units), 2);
            $dauAmt = round($residue * (1 / $units), 2);
            if ($s > 0) {
                $out[] = [
                    'label' => $s === 1 ? 'Son' : $s . ' sons',
                    'fraction' => $this->fracLabel(($s * 2) / $units * ($residue / max($estate, 1))),
                    'percent' => round(($sonAmt * $s) / max($estate, 1) * 100, 2),
                    'amount' => round($sonAmt * $s, 2),
                ];
            }
            if ($d > 0) {
                $out[] = [
                    'label' => $d === 1 ? 'Daughter' : $d . ' daughters',
                    'fraction' => $this->fracLabel($d / $units * ($residue / max($estate, 1))),
                    'percent' => round(($dauAmt * $d) / max($estate, 1) * 100, 2),
                    'amount' => round($dauAmt * $d, 2),
                ];
            }
            $residue = 0;
        }

        if ($residue > 0) {
            $out[] = [
                'label' => 'Unassigned (ask a teacher — bayt al-mal / radd)',
                'fraction' => '—',
                'percent' => round($residue / max($estate, 1) * 100, 2),
                'amount' => $residue,
            ];
        }

        return [
            'ok' => true,
            'estate' => $estate,
            'awl' => $awl,
            'radd' => $radd,
            'lines' => $out,
            'note' => $awl
                ? 'Shares added up to more than the estate, so each fixed share was reduced (awl).'
                : ($radd ? 'After fixed shares, remainder returned to those heirs (radd).' : ''),
        ];
    }

    /**
     * @param list<array<string, mixed>> $shares
     */
    private function hasAsaba(array $shares): bool
    {
        foreach ($shares as $row) {
            if (!empty($row['asaba'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<string, mixed> $row
     * @return array{label:string,fraction:string,percent:float,amount:float}
     */
    private function line(array $row, float $portion, float $amount): array
    {
        $label = (string) $row['label'];
        if (!empty($row['count']) && (int) $row['count'] > 1 && !str_contains($label, (string) $row['count'])) {
            $label .= ' (' . (int) $row['count'] . ')';
        }
        return [
            'label' => $label,
            'fraction' => $this->fracLabel($portion),
            'percent' => round($portion * 100, 2),
            'amount' => round($amount, 2),
        ];
    }

    private function fracLabel(float $portion): string
    {
        $map = [
            0.5 => '1/2',
            0.25 => '1/4',
            0.125 => '1/8',
            0.3333 => '1/3',
            0.1667 => '1/6',
            0.6667 => '2/3',
        ];
        foreach ($map as $val => $label) {
            if (abs($portion - $val) < 0.01) {
                return $label;
            }
        }
        return round($portion * 100, 1) . '%';
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

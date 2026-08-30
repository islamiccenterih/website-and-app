<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\AboutSection;

/**
 * Coordinators shown on Home and About Us — edited from Admin → Coordinator Info.
 */
final class CoordinatorService
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function published(): array
    {
        $rows = Database::get()->fetchAll(
            'SELECT * FROM founders WHERE status = ? ORDER BY sort_order ASC, id ASC',
            ['published']
        ) ?: [];
        return array_map([self::class, 'present'], $rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $rows = Database::get()->fetchAll(
            'SELECT * FROM founders ORDER BY sort_order ASC, id ASC'
        ) ?: [];
        return array_map([self::class, 'present'], $rows);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function present(array $row): array
    {
        $row['highlights'] = self::highlightsFrom($row);
        $row['initials'] = self::initials((string) ($row['name'] ?? ''));
        $row['highlights_text'] = implode("\n", $row['highlights']);
        return $row;
    }

    /**
     * @param array<string, mixed> $row
     * @return list<string>
     */
    public static function highlightsFrom(array $row): array
    {
        $raw = trim((string) ($row['highlights'] ?? ''));
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $out = [];
                foreach ($decoded as $item) {
                    $line = trim((string) $item);
                    if ($line !== '') {
                        $out[] = $line;
                    }
                }
                if ($out) {
                    return $out;
                }
            }
        }
        $bio = trim((string) ($row['biography'] ?? ''));
        if ($bio === '') {
            return [];
        }
        $parts = preg_split('/\r\n|\n|\r/', $bio) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $line = trim(ltrim((string) $part, "-• \t"));
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return $out;
    }

    public static function highlightsToStorage(string $text): string
    {
        $parts = preg_split('/\r\n|\n|\r/', $text) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $line = trim(ltrim((string) $part, "-• \t"));
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = [];
        foreach ($parts as $part) {
            $clean = preg_replace('/[^A-Za-z]/', '', $part) ?? '';
            if ($clean === '') {
                continue;
            }
            $skip = ['SYED', 'DR', 'PROF', 'MOULANA', 'MAULANA'];
            if (in_array(strtoupper($clean), $skip, true)) {
                continue;
            }
            $letters[] = strtoupper($clean[0]);
            if (count($letters) >= 2) {
                break;
            }
        }
        if ($letters === []) {
            return 'IC';
        }
        return implode('', $letters);
    }

    public static function seed(): void
    {
        $now = date('Y-m-d H:i:s');
        AboutSection::upsert('founders_intro', [
            'title' => 'Coordinators',
            'content' => 'The coordinators who guide Islamic Center Information Hub — in faith, education, and service.',
            'extra_json' => json_encode(['kicker' => 'Leadership'], JSON_UNESCAPED_UNICODE),
        ]);

        $db = Database::get();
        $keep = [];
        foreach (self::catalog() as $i => $person) {
            $existing = $db->fetch('SELECT * FROM founders WHERE name = ?', [$person['name']]);
            $payload = [
                'name' => $person['name'],
                'designation' => $person['designation'],
                'biography' => '',
                'highlights' => json_encode($person['highlights'], JSON_UNESCAPED_UNICODE),
                'sort_order' => $i + 1,
                'status' => 'published',
                'updated_at' => $now,
            ];
            if ($existing) {
                $db->update('founders', $payload, 'id = ?', [(int) $existing['id']]);
                $keep[] = (int) $existing['id'];
            } else {
                $payload['photo'] = null;
                $payload['created_at'] = $now;
                $keep[] = $db->insert('founders', $payload);
            }
        }
        if ($keep) {
            $placeholders = implode(',', array_fill(0, count($keep), '?'));
            $db->execute(
                "UPDATE founders SET status = 'draft', updated_at = ? WHERE id NOT IN ($placeholders) AND (name LIKE '%Placeholder%' OR status = 'published')",
                array_merge([$now], $keep)
            );
        }
    }

    /**
     * @return list<array{name:string,designation:string,highlights:list<string>}>
     */
    public static function catalog(): array
    {
        return [
            [
                'name' => 'MOULANA SYED ALAM MUSTAFA YAQUBI',
                'designation' => 'Founder, Islamic Center | Director, Islamic Children Academy',
                'highlights' => [
                    '25+ Years of distinguished experience in Teaching, Education & Academic Leadership',
                    'Founder of Islamic Center & Director of Islamic Children Academy',
                    'Islamic Scholar (Alim) — Graduate of Madrasa Mazahir Uloom Saharanpur',
                    'Master’s Degree in Computer Science & Software Engineer',
                    'Degree in Journalism with expertise in Communication & Media',
                    'Manager, Abu Hurairah Inter College',
                    'Chief Master Trainer, Haj Committee — serving for 7+ Years',
                    'Co-Ordinator, Waqf Board',
                    'Motivational Speaker & Educationist',
                    'Actively working in the field of Artificial Intelligence (AI) and emerging technologies',
                    'Dedicated to integrating Islamic Values, Modern Education, Technology & Future-Ready Skills',
                ],
            ],
            [
                'name' => 'PROF. DR. SHAMIM AHMED',
                'designation' => 'Founder | Distinguished Microbiologist & Academician',
                'highlights' => [
                    '50+ Years of distinguished experience in Microbiology & Medical Education',
                    'Senior Professor of Microbiology, Aligarh Muslim University, Aligarh',
                    'Lifetime Achievement Award Recipient — USA',
                    'Recipient of 6 International Fellowships from Germany, Japan, UK, Turkey & Slovak Republic',
                    'Post-Doctoral Training in Clinical & Applied Microbiology across 6 Countries & 7 Universities',
                    'Guided 52 Ph.D. & M.S. Research Scholars associated with 33 Countries',
                    'Extensive contribution to Clinical Microbiology, Ocular Infections, MRSA & Antibacterial Research',
                    '5+ Years of International Academic Service in reputed medical institutions in Libya & Saudi Arabia',
                    'Author/Co-author of Research Papers, Books & Book Chapters, including Herbal Unani',
                    'Distinguished contributor to Medical Education, Scientific Research & Academic Leadership',
                ],
            ],
        ];
    }
}

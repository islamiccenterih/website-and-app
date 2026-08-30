<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\SitePages;
use App\Services\FaithContentService;

final class FaithContentController extends BaseController
{
    public function update(string $key): void
    {
        $this->requireCsrf();
        $allowed = ['daily_quran', 'daily_duas', 'janazah', 'hajj_umrah'];
        if (!in_array($key, $allowed, true) || !SitePages::canEdit($key)) {
            flash('error', 'You cannot edit that page.');
            redirect('/admin/pages');
        }
        SitePages::saveMenuFromRequest($key);
        $page = SitePages::get($key);
        $fields = is_array($page['fields'] ?? null) ? $page['fields'] : [];
        $postedCopy = is_array($_POST['copy'] ?? null) ? $_POST['copy'] : [];
        $out = [];
        foreach ($fields as $field) {
            $out[(string) $field] = trim((string) ($postedCopy[$field] ?? $_POST[$field] ?? ''));
        }
        if ($out !== []) {
            PagesController::putCopy((string) $page['copy'], $out);
        }
        $svc = new FaithContentService();
        if ($key === 'daily_quran') {
            $svc->save('hadith', $this->hadithFromPost());
        } elseif ($key === 'daily_duas') {
            $svc->save('duas', $this->groupsFromPost());
        } elseif ($key === 'janazah') {
            $svc->save('janazah', $this->janazahFromPost());
        } elseif ($key === 'hajj_umrah') {
            $svc->save('hajj', $this->hajjFromPost());
        }
        flash('success', 'Page saved.');
        redirect('/admin/pages/' . $key);
    }

    /**
     * @return list<array<string, string>>
     */
    private function hadithFromPost(): array
    {
        $rows = is_array($_POST['hadith'] ?? null) ? $_POST['hadith'] : [];
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $ar = trim((string) ($row['ar'] ?? ''));
            $en = trim((string) ($row['en'] ?? ''));
            if ($ar === '' && $en === '') {
                continue;
            }
            $out[] = [
                'ar' => $ar,
                'en' => $en,
                'src' => trim((string) ($row['src'] ?? '')),
            ];
        }
        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function groupsFromPost(): array
    {
        $groups = is_array($_POST['group'] ?? null) ? $_POST['group'] : [];
        $out = [];
        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }
            $items = [];
            foreach (is_array($group['items'] ?? null) ? $group['items'] : [] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $items[] = [
                    'title' => trim((string) ($item['title'] ?? '')),
                    'arabic' => trim((string) ($item['arabic'] ?? '')),
                    'translit' => trim((string) ($item['translit'] ?? '')),
                    'meaning' => trim((string) ($item['meaning'] ?? '')),
                    'hi' => trim((string) ($item['hi'] ?? '')),
                    'ur' => trim((string) ($item['ur'] ?? '')),
                ];
            }
            $out[] = [
                'id' => trim((string) ($group['id'] ?? '')),
                'title' => trim((string) ($group['title'] ?? '')),
                'items' => $items,
            ];
        }
        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function janazahFromPost(): array
    {
        $rows = is_array($_POST['step'] ?? null) ? $_POST['step'] : [];
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out[] = [
                'title' => trim((string) ($row['title'] ?? '')),
                'lead' => trim((string) ($row['lead'] ?? '')),
                'lead_hi' => trim((string) ($row['lead_hi'] ?? '')),
                'dua' => [
                    'title' => trim((string) ($row['dua_title'] ?? '')),
                    'arabic' => trim((string) ($row['arabic'] ?? '')),
                    'translit' => trim((string) ($row['translit'] ?? '')),
                    'meaning' => trim((string) ($row['meaning'] ?? '')),
                    'hi' => trim((string) ($row['hi'] ?? '')),
                    'ur' => trim((string) ($row['ur'] ?? '')),
                ],
            ];
        }
        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function hajjFromPost(): array
    {
        $pack = static function (mixed $raw): array {
            $out = [];
            foreach (is_array($raw) ? $raw : [] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $out[] = [
                    'title' => trim((string) ($row['title'] ?? '')),
                    'text' => trim((string) ($row['text'] ?? '')),
                    'text_hi' => trim((string) ($row['text_hi'] ?? '')),
                ];
            }
            return $out;
        };
        $duas = [];
        foreach (is_array($_POST['hajj_dua'] ?? null) ? $_POST['hajj_dua'] : [] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $duas[] = [
                'title' => trim((string) ($row['title'] ?? '')),
                'arabic' => trim((string) ($row['arabic'] ?? '')),
                'translit' => trim((string) ($row['translit'] ?? '')),
                'meaning' => trim((string) ($row['meaning'] ?? '')),
                'hi' => trim((string) ($row['hi'] ?? '')),
                'ur' => trim((string) ($row['ur'] ?? '')),
            ];
        }
        return [
            'umrah' => $pack($_POST['umrah'] ?? []),
            'hajj' => $pack($_POST['hajj_step'] ?? []),
            'duas' => $duas,
        ];
    }
}

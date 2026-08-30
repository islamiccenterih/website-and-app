<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;

final class FooterController extends BaseController
{
    public function index(): void
    {
        $nav = header_nav_all();
        $this->screen('admin/footer/index', [
            'pageTitle' => 'Header & Footer — Admin',
            'nav' => $this->padded($nav, count($nav) + 3),
            'links' => $this->padded(footer_links_all(), 6),
        ]);
    }

    public function update(): void
    {
        $this->requireCsrf();
        Setting::putMany([
            'footer_brand_title' => faith_terms_store(trim((string) ($_POST['footer_brand_title'] ?? ''))),
            'site_tagline' => faith_terms_store(trim((string) ($_POST['site_tagline'] ?? ''))),
            'footer_note' => faith_terms_store(trim((string) ($_POST['footer_note'] ?? ''))),
            'footer_visit_heading' => faith_terms_store(trim((string) ($_POST['footer_visit_heading'] ?? 'Visit'))),
            'footer_legal_heading' => faith_terms_store(trim((string) ($_POST['footer_legal_heading'] ?? $_POST['footer_explore_heading'] ?? 'Legal'))),
            'footer_explore_heading' => faith_terms_store(trim((string) ($_POST['footer_legal_heading'] ?? $_POST['footer_explore_heading'] ?? 'Legal'))),
            'footer_copyright' => trim((string) ($_POST['footer_copyright'] ?? '')),
            'header_login_label' => trim((string) ($_POST['header_login_label'] ?? 'Student Login')),
            'header_nav' => json_encode($this->collectLinks('nav_label', 'nav_url', 'nav_hidden', 'nav_group'), JSON_UNESCAPED_UNICODE),
            'footer_links' => json_encode($this->collectLinks('link_label', 'link_url', 'link_hidden'), JSON_UNESCAPED_UNICODE),
        ]);
        flash('success', 'Header and footer saved. The public website now shows these values.');
        redirect('/admin/footer');
    }

    /** @return list<array{label:string,url:string,hidden:bool,group?:string}> */
    private function collectLinks(string $labelKey, string $urlKey, string $hiddenKey, ?string $groupKey = null): array
    {
        $labels = $_POST[$labelKey] ?? [];
        $urls = $_POST[$urlKey] ?? [];
        $hiddens = $_POST[$hiddenKey] ?? [];
        $groups = $groupKey !== null ? ($_POST[$groupKey] ?? []) : [];
        $links = [];
        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            $url = trim((string) ($urls[$i] ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }
            $row = [
                'label' => mb_substr(faith_terms_store($label), 0, 160),
                'url' => mb_substr($url, 0, 255),
                'hidden' => (string) ($hiddens[$i] ?? '0') === '1',
            ];
            if ($groupKey !== null) {
                $row['group'] = nav_item_group([
                    'url' => $url,
                    'group' => (string) ($groups[$i] ?? ''),
                ]);
            }
            $links[] = $row;
        }
        return $links;
    }

    /** @param list<array{label:string,url:string,hidden?:bool,group?:string}> $links */
    private function padded(array $links, int $min): array
    {
        $out = [];
        foreach ($links as $link) {
            $label = (string) ($link['label'] ?? '');
            $out[] = [
                'label' => $label !== '' ? ftc($label) : '',
                'url' => (string) ($link['url'] ?? ''),
                'hidden' => !empty($link['hidden']),
                'group' => nav_item_group($link),
            ];
        }
        while (count($out) < $min) {
            $out[] = ['label' => '', 'url' => '', 'hidden' => false, 'group' => 'more'];
        }
        return $out;
    }
}

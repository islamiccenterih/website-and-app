<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\SitePages;
use App\Models\Setting;

final class PagesController extends BaseController
{
    /** @return array<string, array{label:string,fields:array<int,string>}> */
    public static function catalog(): array
    {
        $out = [];
        foreach (SitePages::all() as $key => $page) {
            $fields = $page['fields'] ?? [];
            if (!is_array($fields) || $fields === []) {
                continue;
            }
            $out[$key] = [
                'label' => (string) $page['name'],
                'fields' => array_values($fields),
            ];
        }
        return $out;
    }

    /**
     * @param array<string, string> $fields
     */
    public static function putCopy(string $page, array $fields): void
    {
        $all = json_setting('page_copy');
        if (!isset($all[$page]) || !is_array($all[$page])) {
            $all[$page] = [];
        }
        foreach ($fields as $key => $value) {
            $all[$page][$key] = faith_terms_store(trim((string) $value));
        }
        Setting::put('page_copy', json_encode($all, JSON_UNESCAPED_UNICODE));
    }

    public function index(): void
    {
        $rows = [];
        foreach (SitePages::visibleFor() as $key => $page) {
            $menu = SitePages::menuState((string) $page['url']);
            $copy = [];
            if (!empty($page['copy'])) {
                $all = json_setting('page_copy');
                $copy = is_array($all[$page['copy']] ?? null) ? $all[$page['copy']] : [];
            }
            $rows[] = [
                'key' => $key,
                'name' => (string) $page['name'],
                'url' => (string) $page['url'],
                'blurb' => (string) ($page['blurb'] ?? ''),
                'menu' => $menu['label'] !== '' ? $menu['label'] : (string) $page['name'],
                'heading' => trim((string) ($copy['title'] ?? '')),
                'in_header' => $menu['in_header'],
                'in_footer' => $menu['in_footer'],
            ];
        }
        $this->screen('admin/pages/index', [
            'pageTitle' => 'Pages — Admin',
            'pages' => $rows,
        ]);
    }

    public function edit(string $key): void
    {
        $page = SitePages::get($key);
        if (!$page || !SitePages::canEdit($key)) {
            flash('error', 'You cannot edit that page.');
            redirect('/admin/pages');
        }
        $copyKey = (string) ($page['copy'] ?? '');
        $copy = [];
        if ($copyKey !== '') {
            $all = json_setting('page_copy');
            $stored = is_array($all[$copyKey] ?? null) ? $all[$copyKey] : [];
            $copy = SitePages::copyForEditor($copyKey, $stored);
        }
        $data = [
            'pageTitle' => $page['name'] . ' — Pages — Admin',
            'page' => $page,
            'menu' => SitePages::menuState((string) $page['url']),
            'copy' => $copy,
            'fieldLabels' => SitePages::fieldLabels(),
            'embed' => '',
            'embedInPage' => true,
        ];
        if ($key === 'home') {
            $data['embed'] = 'home';
            $data['sections'] = present_section_map(\App\Models\HomeSection::keyed());
        } elseif ($key === 'about') {
            $data['embed'] = 'about';
            $data['sections'] = present_section_map(\App\Models\AboutSection::keyed());
            $data['founders'] = present_copy_tree($this->db()->fetchAll('SELECT * FROM founders ORDER BY sort_order ASC, id ASC'));
        } elseif ($key === 'contact') {
            $data['embed'] = 'contact';
        } elseif ($key === 'qibla') {
            $point = (new \App\Services\QiblaService())->defaultPoint();
            $data['embed'] = 'qibla';
            $data['lat'] = (string) ($point['lat'] ?? '27.1591');
            $data['lng'] = (string) ($point['lng'] ?? '78.3957');
            $data['label'] = (string) ($point['label'] ?? '');
        } elseif ($key === 'zakat') {
            $data['embed'] = 'zakat';
            $data['zakat'] = (new \App\Services\ZakatService())->config();
        } elseif ($key === 'ramadan') {
            $tools = json_setting('worship_tools');
            $data['embed'] = 'ramadan';
            $data['duas'] = present_copy_tree((new \App\Services\RamadanService())->duas());
            $data['ramadan_city'] = (string) ($tools['ramadan_city'] ?? 'Firozabad');
            $data['ramadan_state'] = (string) ($tools['ramadan_state'] ?? 'Uttar Pradesh');
        }
        $this->screen('admin/pages/edit', $data);
    }

    public function updatePage(string $key): void
    {
        $this->requireCsrf();
        $page = SitePages::get($key);
        if (!$page || !SitePages::canEdit($key)) {
            flash('error', 'You cannot edit that page.');
            redirect('/admin/pages');
        }
        $label = trim((string) ($_POST['menu_name'] ?? ''));
        if ($label === '') {
            $label = (string) $page['name'];
        }
        SitePages::applyMenu(
            (string) $page['url'],
            $label,
            !empty($_POST['in_header']),
            !empty($_POST['in_footer'])
        );
        $copyKey = (string) ($page['copy'] ?? '');
        $fields = is_array($page['fields'] ?? null) ? $page['fields'] : [];
        $postedCopy = is_array($_POST['copy'] ?? null) ? $_POST['copy'] : null;
        if ($copyKey !== '' && $fields !== [] && $postedCopy !== null) {
            $out = [];
            foreach ($fields as $field) {
                $out[(string) $field] = trim((string) ($postedCopy[$field] ?? ''));
            }
            self::putCopy($copyKey, $out);
        }
        flash('success', 'Page saved.');
        redirect('/admin/pages/' . $key);
    }

    public function update(): void
    {
        $this->requireCsrf();
        $posted = $_POST['page'] ?? [];
        $out = json_setting('page_copy');
        foreach (self::catalog() as $slug => $meta) {
            if (!SitePages::canEdit($slug)) {
                continue;
            }
            $row = is_array($posted[$slug] ?? null) ? $posted[$slug] : [];
            foreach ($meta['fields'] as $field) {
                $out[$slug][$field] = trim((string) ($row[$field] ?? ''));
            }
        }
        Setting::put('page_copy', json_encode($out, JSON_UNESCAPED_UNICODE));
        flash('success', 'Page headings saved. The public pages now use this copy.');
        redirect('/admin/pages');
    }
}

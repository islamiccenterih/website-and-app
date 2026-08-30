<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Setting;

final class SitePages
{
    /**
     * Public website pages, keyed for the admin Pages screen.
     *
     * `name` is the English label in the admin sidebar/list (never translated).
     * Menu labels on the public site come from header/footer rows for `url`.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'home' => [
                'name' => 'Home',
                'url' => '/',
                'module' => 'home',
                'copy' => null,
                'fields' => [],
                'blurb' => 'Hero, sections, and homepage text.',
                'actions' => [
                    ['href' => '/admin/programs', 'label' => 'Program cards'],
                ],
            ],
            'about' => [
                'name' => 'About Us',
                'url' => '/about-us',
                'module' => 'about',
                'copy' => null,
                'fields' => [],
                'blurb' => 'Banner, history, mission, vision, and coordinators.',
                'actions' => [
                    ['href' => '/admin/coordinators', 'label' => 'Coordinator Info'],
                ],
            ],
            'courses' => [
                'name' => 'Courses',
                'url' => '/courses',
                'module' => 'courses',
                'copy' => 'courses',
                'fields' => ['kicker', 'title', 'lead', 'inner_kicker', 'inner_title', 'detail_kicker', 'images_heading', 'back_label'],
                'blurb' => 'Course list headings. Add courses from the Courses menu.',
                'actions' => [
                    ['href' => '/admin/courses', 'label' => 'Manage courses'],
                ],
            ],
            'activities' => [
                'name' => 'Social Activities',
                'url' => '/social-activities',
                'module' => 'activities',
                'copy' => 'activities',
                'fields' => ['kicker', 'title', 'lead', 'inner_kicker', 'detail_kicker', 'images_heading', 'back_label'],
                'blurb' => 'Activity page headings. Programmes are added from Social Activities.',
                'actions' => [
                    ['href' => '/admin/activities', 'label' => 'Manage activities'],
                ],
            ],
            'gallery' => [
                'name' => 'Gallery',
                'url' => '/gallery',
                'module' => 'gallery',
                'copy' => 'gallery',
                'fields' => ['kicker', 'title', 'lead', 'inner_kicker', 'inner_title'],
                'blurb' => 'Gallery headings. Photos are uploaded from Gallery.',
                'actions' => [
                    ['href' => '/admin/gallery', 'label' => 'Manage gallery'],
                ],
            ],
            'contact' => [
                'name' => 'Contact Us',
                'url' => '/contact-us',
                'module' => 'contact',
                'copy' => 'contact',
                'fields' => ['kicker', 'title', 'lead', 'form_kicker', 'form_title', 'aside_kicker', 'aside_title', 'submit_label'],
                'blurb' => 'Contact headings, form labels, address, and photograph.',
                'actions' => [],
            ],
            'moon' => [
                'name' => 'Moon Timing',
                'url' => '/moon-timing',
                'module' => 'pages',
                'copy' => 'moon',
                'fields' => ['kicker', 'title', 'lead', 'week_kicker', 'week_title', 'week_lead', 'sighting_title', 'sighting_text'],
                'blurb' => 'Moonrise, moonset, and sighting text.',
                'actions' => [],
            ],
            'calendar' => [
                'name' => 'Islamic Calendar',
                'url' => '/islamic-calendar',
                'module' => 'calendar',
                'copy' => 'calendar',
                'fields' => ['kicker', 'title', 'lead'],
                'blurb' => 'Calendar headings. Months are added from Islamic Calendar.',
                'actions' => [
                    ['href' => '/admin/calendar', 'label' => 'Manage calendar months'],
                ],
            ],
            'qibla' => [
                'name' => 'Qibla Direction',
                'url' => '/qibla-direction',
                'module' => 'qibla',
                'copy' => 'qibla',
                'fields' => ['kicker', 'title', 'lead', 'help'],
                'blurb' => 'Compass headings and fallback location.',
                'actions' => [],
            ],
            'zakat' => [
                'name' => 'Zakat Calculator',
                'url' => '/zakat-calculator',
                'module' => 'zakat',
                'copy' => 'zakat',
                'fields' => ['kicker', 'title', 'lead', 'notes_title', 'notes'],
                'blurb' => 'Calculator headings and nisab settings.',
                'actions' => [],
            ],
            'ramadan' => [
                'name' => 'Ramadan Mode',
                'url' => '/ramadan-mode',
                'module' => 'ramadan',
                'copy' => 'ramadan',
                'fields' => ['kicker', 'title', 'lead', 'calendar_kicker', 'calendar_title', 'calendar_lead', 'duas_kicker', 'duas_title', 'duas_lead'],
                'blurb' => 'Sehri, Iftar, calendar, and dua text.',
                'actions' => [],
            ],
            'fatawa' => [
                'name' => 'Fatawa',
                'url' => '/fatawa',
                'module' => 'fatawa',
                'copy' => 'fatawa',
                'fields' => ['kicker', 'title', 'lead', 'archive_kicker', 'archive_title', 'detail_lead'],
                'blurb' => 'Fatawa headings. Rulings are published from Fatawa.',
                'actions' => [
                    ['href' => '/admin/fatawa', 'label' => 'Manage fatawa'],
                ],
            ],
            'holidays' => [
                'name' => 'Islamic Holidays',
                'url' => '/islamic-holidays',
                'module' => 'pages',
                'copy' => 'holidays',
                'fields' => ['kicker', 'title', 'lead'],
                'blurb' => 'Eid and other Islamic dates observed in India.',
                'actions' => [],
            ],
            'updates' => [
                'name' => 'Center Updates',
                'url' => '/center-updates',
                'module' => 'updates',
                'copy' => 'updates',
                'fields' => ['kicker', 'title', 'lead', 'archive_kicker', 'archive_title'],
                'blurb' => 'News headings. Posts are written from Center Updates.',
                'actions' => [
                    ['href' => '/admin/updates', 'label' => 'Manage updates'],
                ],
            ],
            'live' => [
                'name' => 'Live',
                'url' => '/live',
                'module' => 'live-now',
                'copy' => 'live',
                'fields' => ['kicker', 'title', 'lead'],
                'blurb' => 'Public live page headings. Go live from Live now.',
                'actions' => [
                    ['href' => '/admin/live-now', 'label' => 'Live now'],
                ],
            ],
            'privacy' => [
                'name' => 'Privacy Policy',
                'url' => '/privacy-policy',
                'module' => 'pages',
                'copy' => 'privacy',
                'fields' => ['kicker', 'title', 'lead', 'updated', 'body'],
                'blurb' => 'Privacy policy heading and the full legal text visitors read on the website.',
                'actions' => [],
            ],
            'terms' => [
                'name' => 'Terms & Conditions',
                'url' => '/terms-and-conditions',
                'module' => 'pages',
                'copy' => 'terms',
                'fields' => ['kicker', 'title', 'lead', 'updated', 'body'],
                'blurb' => 'Terms heading and the full legal text visitors read on the website.',
                'actions' => [],
            ],
            'disclaimer' => [
                'name' => 'Disclaimer',
                'url' => '/disclaimer',
                'module' => 'pages',
                'copy' => 'disclaimer',
                'fields' => ['kicker', 'title', 'lead', 'updated', 'body'],
                'blurb' => 'Disclaimer heading and the full legal text visitors read on the website.',
                'actions' => [],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        $all = self::all();
        if (!isset($all[$key])) {
            return null;
        }
        $page = $all[$key];
        $page['key'] = $key;
        return $page;
    }

    public static function canEdit(string $key, ?array $user = null): bool
    {
        $page = self::get($key);
        if (!$page) {
            return false;
        }
        if (AdminAccess::canModule('pages', $user)) {
            return true;
        }
        return AdminAccess::canModule((string) $page['module'], $user);
    }

    public static function canList(?array $user = null): bool
    {
        if (AdminAccess::canModule('pages', $user)) {
            return true;
        }
        foreach (self::all() as $page) {
            if (AdminAccess::canModule((string) $page['module'], $user)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function visibleFor(?array $user = null): array
    {
        $out = [];
        foreach (self::all() as $key => $page) {
            if (self::canEdit($key, $user)) {
                $page['key'] = $key;
                $out[$key] = $page;
            }
        }
        return $out;
    }

    /**
     * @return array{label:string,in_header:bool,in_footer:bool,has_header:bool,has_footer:bool}
     */
    public static function menuState(string $url): array
    {
        $header = self::findLink(header_nav_all(), $url);
        $footer = self::findLink(footer_links_all(), $url);
        $label = (string) (($header['label'] ?? '') !== '' ? $header['label'] : ($footer['label'] ?? ''));
        return [
            'label' => $label,
            'in_header' => $header !== null && empty($header['hidden']),
            'in_footer' => $footer !== null && empty($footer['hidden']),
            'has_header' => $header !== null,
            'has_footer' => $footer !== null,
        ];
    }

    public static function applyMenu(string $url, string $label, bool $inHeader, bool $inFooter): void
    {
        $label = mb_substr(faith_terms_store(trim($label)), 0, 160);
        if ($label === '') {
            return;
        }
        Setting::put('header_nav', json_encode(
            self::upsertLink(header_nav_all(), $url, $label, $inHeader),
            JSON_UNESCAPED_UNICODE
        ));
        Setting::put('footer_links', json_encode(
            self::upsertLink(footer_links_all(), $url, $label, $inFooter),
            JSON_UNESCAPED_UNICODE
        ));
    }

    public static function saveMenuFromRequest(string $key): void
    {
        if (!array_key_exists('menu_name', $_POST)) {
            return;
        }
        $page = self::get($key);
        if (!$page) {
            return;
        }
        $label = trim((string) $_POST['menu_name']);
        if ($label === '') {
            $label = (string) $page['name'];
        }
        self::applyMenu(
            (string) $page['url'],
            $label,
            !empty($_POST['in_header']),
            !empty($_POST['in_footer'])
        );
    }

    /**
     * Stored copy merged with the English currently shown on the public page.
     *
     * @param array<string, mixed> $stored
     * @return array<string, string>
     */
    public static function copyForEditor(string $copyKey, array $stored): array
    {
        $defaults = self::copyDefaults($copyKey);
        $out = $defaults;
        foreach ($stored as $field => $value) {
            $text = trim((string) $value);
            if ($text !== '') {
                $out[(string) $field] = $text;
            }
        }
        foreach ($out as $field => $text) {
            if (in_array((string) $field, ['body', 'updated'], true)) {
                $out[$field] = (string) $text;
                continue;
            }
            $out[$field] = ftc((string) $text);
        }
        return $out;
    }

    /**
     * @return array<string, string>
     */
    public static function copyDefaults(string $copyKey): array
    {
        $all = [
            'courses' => [
                'kicker' => 'Education',
                'title' => 'Courses',
                'lead' => 'Online and on-site courses. Each card is created in the Admin Panel and shown here from the database.',
                'inner_kicker' => 'Education',
                'inner_title' => 'Current Courses',
                'detail_kicker' => 'Course details',
                'images_heading' => 'Course images',
                'back_label' => 'Back to courses',
            ],
            'activities' => [
                'kicker' => 'Community life',
                'title' => 'Social Activities',
                'lead' => \App\Services\ActivityCatalog::pageLead(),
                'inner_kicker' => 'Programmes',
                'detail_kicker' => 'Program details',
                'images_heading' => 'Photographs',
                'back_label' => 'Back to activities',
            ],
            'gallery' => [
                'kicker' => 'Photographs',
                'title' => 'Gallery',
                'lead' => 'Images from the life of the center — classes, gatherings, and the campus.',
                'inner_kicker' => 'Moments',
                'inner_title' => 'From the center',
            ],
            'contact' => [
                'kicker' => 'Get in touch',
                'title' => 'Contact Us',
                'lead' => 'Send a message to the administration. Address and phone details are managed from the Admin Panel.',
                'form_kicker' => 'Write to us',
                'form_title' => 'Send a message',
                'aside_kicker' => 'Islamic Center',
                'aside_title' => 'Visit and write',
                'submit_label' => 'Send message',
            ],
            'moon' => [
                'kicker' => 'Tonight’s sky',
                'title' => 'Moon Timing',
                'lead' => 'Islamic date, moonrise, moonset, and phase for Firozabad, Uttar Pradesh, India 283203.',
                'week_kicker' => 'Looking ahead',
                'week_title' => 'Seven-day phase',
                'week_lead' => 'Calculated illumination for the coming nights.',
                'sighting_title' => 'Moon sighting window',
                'sighting_text' => 'The Hijri month is near its end. Local moon-sighting for the next month, including Ramadan and Eid, follows the center’s announcement rather than these calculated times.',
            ],
            'calendar' => [
                'kicker' => 'Hijri months',
                'title' => 'Islamic Calendar',
                'lead' => 'Today’s Hijri date, the full month, and the days of worship — kept on this site so the calendar stays with the center.',
            ],
            'qibla' => [
                'kicker' => 'Face the House of Allah',
                'title' => 'Qibla Direction',
                'lead' => 'Hold your phone flat. Allow location and compass. The gold mark turns with you until it points to the Kaaba.',
                'help' => 'Start compass dabayein, Location Allow karein. Kaaba aapke phone ki GPS se calculate hota hai — Delhi, Mumbai, ya duniya mein kahin. Firozabad sirf center ka pata hai, aapka Qibla nahi. Beech ka number aapki direction hai. Gold notch aap ka rukh hai; Kaaba mark ko uske neeche laayein. Ulta ghumey to Reverse compass.',
            ],
            'zakat' => [
                'kicker' => 'Purify what you keep',
                'title' => 'Zakat Calculator',
                'lead' => 'Enter gold, silver, cash, business stock, and debts. Nisab updates itself from today’s metal prices. Zakat is 2.5% of what is above nisab.',
                'notes_title' => 'How this is worked out',
                'notes' => 'Nisab is 87.48 g of gold or 612.36 g of silver. This page uses the lower of the two (the usual Hanafi practice in India) unless the administration changes it. The rate is 2.5% of net zakatable wealth held for one lunar year. Jewellery is valued by the gold or silver it contains, not by making charges. Livestock, crops, and minerals follow other rules — ask a teacher at the center if that is your case.',
            ],
            'ramadan' => [
                'kicker' => 'Prepare for the blessed month',
                'title' => 'Ramadan Mode',
                'lead' => 'Sehri and Iftar for every city in India, a full roza calendar, and the duas recited at the table and in the night prayer.',
                'calendar_kicker' => 'The month',
                'calendar_title' => 'Roza calendar',
                'calendar_lead' => 'Every fast of Ramadan with Sehri (Fajr) and Iftar (Maghrib) for the city you chose.',
                'duas_kicker' => 'Words of the month',
                'duas_title' => 'Ramadan duas',
                'duas_lead' => 'Read at Sehri, at Iftar, after Taraweeh, and on Laylat al-Qadr. Arabic, transliteration, and meaning.',
            ],
            'fatawa' => [
                'kicker' => 'Daily guidance',
                'title' => 'Fatawa',
                'lead' => 'A new fatwa is published here each day. Read it, then ask a question on that fatwa if you need a ruling for your own situation.',
                'archive_kicker' => 'Previous days',
                'archive_title' => 'Previous fatawa',
                'detail_lead' => 'Read the fatwa, then ask a question about this ruling. The answer will appear under your question.',
            ],
            'holidays' => [
                'kicker' => 'India',
                'title' => 'Islamic Holidays',
                'lead' => 'Eid ul-Fitr and Eid al-Adha as observed in India, then every major Islamic day of the year — Hijri date and the civil date used in Firozabad.',
            ],
            'updates' => [
                'kicker' => 'From the Center',
                'title' => 'Center Updates',
                'lead' => 'Daily news from Islamic Center Information Hub — gatherings, classes, and notices, written as they are posted.',
                'archive_kicker' => 'Notice board',
                'archive_title' => 'Earlier updates',
            ],
            'live' => [
                'kicker' => 'On this website',
                'title' => 'Live',
                'lead' => 'When the center goes live from a phone or laptop, the stream plays here. No YouTube account is needed.',
            ],
            'privacy' => [
                'kicker' => 'Your information',
                'title' => 'Privacy Policy',
                'lead' => 'What this website collects, why we keep it, and how you can ask for a copy or a correction.',
                'updated' => \App\Services\LegalContent::UPDATED,
                'body' => \App\Services\LegalContent::bodyText('privacy'),
            ],
            'terms' => [
                'kicker' => 'Using this website',
                'title' => 'Terms & Conditions',
                'lead' => 'The rules for using this website, student login, live classes, and Live now.',
                'updated' => \App\Services\LegalContent::UPDATED,
                'body' => \App\Services\LegalContent::bodyText('terms'),
            ],
            'disclaimer' => [
                'kicker' => 'Please read',
                'title' => 'Disclaimer',
                'lead' => 'Limits of fatawa, prayer times, qibla, zakat estimates, and live streams on this website.',
                'updated' => \App\Services\LegalContent::UPDATED,
                'body' => \App\Services\LegalContent::bodyText('disclaimer'),
            ],
        ];
        return $all[$copyKey] ?? [];
    }

    /**
     * @return array<string, string>
     */
    public static function fieldLabels(): array
    {
        return [
            'kicker' => 'Gold tag',
            'title' => 'Heading',
            'lead' => 'Introduction',
            'inner_kicker' => 'Section tag',
            'inner_title' => 'Section heading',
            'form_kicker' => 'Form tag',
            'form_title' => 'Form heading',
            'aside_kicker' => 'Visit panel tag',
            'aside_title' => 'Visit panel heading',
            'submit_label' => 'Send button',
            'detail_kicker' => 'Detail page tag',
            'images_heading' => 'Images heading',
            'back_label' => 'Back link',
            'week_kicker' => 'Seven-day tag',
            'week_title' => 'Seven-day heading',
            'week_lead' => 'Seven-day introduction',
            'sighting_title' => 'Moon-sighting heading',
            'sighting_text' => 'Moon-sighting text',
            'help' => 'Help text under the compass',
            'notes_title' => 'Notes heading',
            'notes' => 'Notes under the calculator',
            'calendar_kicker' => 'Roza calendar tag',
            'calendar_title' => 'Roza calendar heading',
            'calendar_lead' => 'Roza calendar introduction',
            'duas_kicker' => 'Duas tag',
            'duas_title' => 'Duas heading',
            'duas_lead' => 'Duas introduction',
            'archive_kicker' => 'Previous items tag',
            'archive_title' => 'Previous items heading',
            'detail_lead' => 'Detail page introduction',
            'updated' => 'Last updated date',
            'body' => 'Full page text',
        ];
    }

    /**
     * @param list<array{label?:string,url?:string,hidden?:bool}> $links
     * @return array{label:string,url:string,hidden:bool}|null
     */
    private static function findLink(array $links, string $url): ?array
    {
        $want = nav_item_path($url);
        foreach ($links as $link) {
            if (nav_item_path((string) ($link['url'] ?? '')) === $want) {
                return [
                    'label' => (string) ($link['label'] ?? ''),
                    'url' => (string) ($link['url'] ?? ''),
                    'hidden' => !empty($link['hidden']),
                ];
            }
        }
        return null;
    }

    /**
     * @param list<array{label?:string,url?:string,hidden?:bool}> $links
     * @return list<array{label:string,url:string,hidden:bool}>
     */
    private static function upsertLink(array $links, string $url, string $label, bool $visible): array
    {
        $want = nav_item_path($url);
        $found = false;
        $out = [];
        foreach ($links as $link) {
            $itemUrl = (string) ($link['url'] ?? '');
            $itemLabel = trim((string) ($link['label'] ?? ''));
            if ($itemUrl === '' || $itemLabel === '') {
                continue;
            }
            if (nav_item_path($itemUrl) === $want) {
                $out[] = [
                    'label' => $label,
                    'url' => $itemUrl,
                    'hidden' => !$visible,
                ];
                $found = true;
                continue;
            }
            $out[] = [
                'label' => $itemLabel,
                'url' => $itemUrl,
                'hidden' => !empty($link['hidden']),
            ];
        }
        if (!$found) {
            $out[] = [
                'label' => $label,
                'url' => $url,
                'hidden' => !$visible,
            ];
        }
        return $out;
    }
}

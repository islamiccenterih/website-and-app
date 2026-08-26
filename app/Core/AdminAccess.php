<?php

declare(strict_types=1);

namespace App\Core;

final class AdminAccess
{
    /**
     * @return array<string, array{label:string,prefix:string}>
     */
    public static function modules(): array
    {
        return [
            'home' => ['label' => 'Home', 'prefix' => '/admin/home'],
            'about' => ['label' => 'About Us', 'prefix' => '/admin/about'],
            'courses' => ['label' => 'Courses', 'prefix' => '/admin/courses'],
            'activities' => ['label' => 'Social Activities', 'prefix' => '/admin/activities'],
            'gallery' => ['label' => 'Gallery', 'prefix' => '/admin/gallery'],
            'programs' => ['label' => 'Center programs', 'prefix' => '/admin/programs'],
            'calendar' => ['label' => 'Islamic Calendar', 'prefix' => '/admin/calendar'],
            'contact' => ['label' => 'Contact Us', 'prefix' => '/admin/contact'],
            'footer' => ['label' => 'Header & Footer', 'prefix' => '/admin/footer'],
            'pages' => ['label' => 'Pages', 'prefix' => '/admin/pages'],
            'qibla' => ['label' => 'Qibla', 'prefix' => '/admin/qibla'],
            'zakat' => ['label' => 'Zakat', 'prefix' => '/admin/zakat'],
            'ramadan' => ['label' => 'Ramadan', 'prefix' => '/admin/ramadan'],
            'fatawa' => ['label' => 'Fatawa', 'prefix' => '/admin/fatawa'],
            'updates' => ['label' => 'Center Updates', 'prefix' => '/admin/updates'],
            'messages' => ['label' => 'Messages', 'prefix' => '/admin/messages'],
            'enquiries' => ['label' => 'Course enquiries', 'prefix' => '/admin/enquiries'],
            'students' => ['label' => 'Students', 'prefix' => '/admin/students'],
            'live-classes' => ['label' => 'Live classes', 'prefix' => '/admin/live-classes'],
            'live-now' => ['label' => 'Live now', 'prefix' => '/admin/live-now'],
            'results' => ['label' => 'Results', 'prefix' => '/admin/results'],
            'settings' => ['label' => 'Settings', 'prefix' => '/admin/settings'],
        ];
    }

    public static function isOwner(?array $user = null): bool
    {
        $user = $user ?? auth_user();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            return false;
        }
        return ($user['panel_role'] ?? 'owner') === 'owner';
    }

    /**
     * @param list<mixed> $raw
     * @return list<string>
     */
    public static function sanitizeKeys(array $raw): array
    {
        $keys = array_keys(self::modules());
        $out = [];
        foreach ($raw as $item) {
            $key = (string) $item;
            if (in_array($key, $keys, true)) {
                $out[] = $key;
            }
        }
        return array_values(array_unique($out));
    }

    /**
     * @return list<string>
     */
    public static function fromJson(?string $json): array
    {
        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? self::sanitizeKeys($decoded) : [];
    }

    /**
     * @return list<string>
     */
    public static function permissions(?array $user = null): array
    {
        $user = $user ?? auth_user();
        $raw = $user['permissions'] ?? [];
        if (!is_array($raw)) {
            return [];
        }
        return self::sanitizeKeys($raw);
    }

    /**
     * @param list<string> $keys
     */
    public static function labelsFor(array $keys): string
    {
        $modules = self::modules();
        $labels = [];
        foreach (self::sanitizeKeys($keys) as $key) {
            $labels[] = $modules[$key]['label'];
        }
        return $labels ? implode(', ', $labels) : 'Dashboard only';
    }

    public static function canModule(string $module, ?array $user = null): bool
    {
        if (self::isOwner($user)) {
            return true;
        }
        $perms = self::permissions($user);
        if (in_array($module, $perms, true)) {
            return true;
        }
        // Managers who already handle Contact messages also see course applications.
        if ($module === 'enquiries' && in_array('messages', $perms, true)) {
            return true;
        }
        return false;
    }

    public static function canPath(string $path, ?array $user = null): bool
    {
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/') ?: '/';
        if (in_array($path, ['/admin/login', '/admin/logout'], true)) {
            return true;
        }
        if ($path === '/admin') {
            return true;
        }
        if (str_starts_with($path, '/admin/members')) {
            return self::isOwner($user);
        }
        if ($path === '/admin/worship' || str_starts_with($path, '/admin/worship/')) {
            return self::canModule('qibla', $user)
                || self::canModule('zakat', $user)
                || self::canModule('ramadan', $user)
                || self::canModule('fatawa', $user)
                || self::canModule('pages', $user);
        }
        if (str_starts_with($path, '/admin/pages')) {
            return \App\Core\SitePages::canList($user);
        }
        if (str_starts_with($path, '/api/live-class')) {
            return self::canModule('live-classes', $user);
        }
        if (str_starts_with($path, '/api/public-live/host')) {
            return self::canModule('live-now', $user);
        }
        foreach (self::modules() as $key => $mod) {
            $prefix = $mod['prefix'];
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return self::canModule($key, $user);
            }
        }
        return self::isOwner($user);
    }

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function navLinks(?array $user = null): array
    {
        $links = [['/admin', 'Dashboard']];
        foreach (self::modules() as $key => $mod) {
            if (self::canModule($key, $user)) {
                $links[] = [$mod['prefix'], $mod['label']];
            }
        }
        if (self::isOwner($user)) {
            $links[] = ['/admin/members', 'Panel members'];
        }
        return $links;
    }

    /**
     * @return array<string, list<array{0:string,1:string}>>
     */
    public static function navGroups(?array $user = null): array
    {
        $byPath = [];
        foreach (self::navLinks($user) as [$path, $label]) {
            $byPath[$path] = $label;
        }
        $order = [
            'Overview' => ['/admin'],
            'Website' => ['/admin/pages', '/admin/courses', '/admin/activities', '/admin/gallery', '/admin/updates', '/admin/programs'],
            'Worship' => ['/admin/calendar', '/admin/fatawa'],
            'People' => ['/admin/messages', '/admin/enquiries', '/admin/students', '/admin/live-classes', '/admin/live-now', '/admin/results', '/admin/members'],
            'System' => ['/admin/footer', '/admin/settings'],
        ];
        $out = [];
        foreach ($order as $group => $paths) {
            $items = [];
            foreach ($paths as $path) {
                if (isset($byPath[$path])) {
                    $items[] = [$path, $byPath[$path]];
                }
            }
            if ($items) {
                $out[$group] = $items;
            }
        }
        return $out;
    }

    public static function deny(): never
    {
        flash('error', 'You do not have access to that section.');
        redirect('/admin');
    }
}

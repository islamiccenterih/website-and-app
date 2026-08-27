<?php

declare(strict_types=1);

function cfg(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'] ?? [];
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_public_website(): bool
{
    $path = current_path();
    foreach (['/admin', '/student', '/live', '/api'] as $prefix) {
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            return false;
        }
    }
    return true;
}

function cms(?string $text): string
{
    return \App\I18n\Lang::text((string) $text);
}

function faith_terms_enabled(): bool
{
    $raw = setting('faith_terms', '1');
    return $raw !== '0' && strtolower((string) $raw) !== 'off';
}

function faith_terms_active(): bool
{
    return faith_terms_enabled() && is_public_website() && current_lang() === 'en';
}

function ft(?string $text): string
{
    $text = (string) $text;
    if ($text === '' || !faith_terms_active()) {
        return $text;
    }
    return \App\I18n\FaithTerms::apply($text);
}

/**
 * Stored CMS text is shown as saved. Use ft() only for fixed English phrases
 * in templates. cms() translates stored text for Hindi / Urdu / Arabic.
 */
function ftc(?string $text): string
{
    return (string) $text;
}

function faith_terms_store(?string $text): string
{
    return (string) $text;
}

function skip_public_copy_key(string $key): bool
{
    $k = strtolower($key);
    if (in_array($k, ['id', 'lat', 'lng', 'sort_order', 'featured', 'extra_json', 'main_image', 'image_path', 'created_at', 'updated_at', 'url', 'slug', 'email', 'phone', 'status', 'mode', 'hidden', 'arabic', 'translit', 'transliteration'], true)) {
        return true;
    }
    return (bool) preg_match('/(_url|_image|_photo|_path|_lat|_lng|_json|_id|_at|_ar|_hi)$/', $k);
}

function present_copy_tree(mixed $value, string $key = ''): mixed
{
    if (is_array($value)) {
        $out = [];
        foreach ($value as $childKey => $child) {
            $out[$childKey] = present_copy_tree($child, is_string($childKey) ? $childKey : $key);
        }
        return $out;
    }
    if (is_string($value) && $value !== '' && !skip_public_copy_key($key)) {
        return ftc($value);
    }
    return $value;
}

function store_copy_tree(mixed $value, string $key = ''): mixed
{
    if (is_array($value)) {
        $out = [];
        foreach ($value as $childKey => $child) {
            $out[$childKey] = store_copy_tree($child, is_string($childKey) ? $childKey : $key);
        }
        return $out;
    }
    if (is_string($value) && $value !== '' && !skip_public_copy_key($key)) {
        return faith_terms_store($value);
    }
    return $value;
}

/**
 * @param array<string, array<string, mixed>> $sections
 * @return array<string, array<string, mixed>>
 */
function present_section_map(array $sections): array
{
    foreach ($sections as $key => $row) {
        if (!is_array($row)) {
            continue;
        }
        foreach (['title', 'subtitle', 'content'] as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $row[$field] = ftc($row[$field]);
            }
        }
        $extra = json_decode((string) ($row['extra_json'] ?? ''), true);
        if (is_array($extra)) {
            $row['extra_json'] = json_encode(present_copy_tree($extra), JSON_UNESCAPED_UNICODE);
        }
        $sections[$key] = $row;
    }
    return $sections;
}

function content_terms_preview(?string $raw, string $label = 'How this reads on the website'): string
{
    return '';
}

function tt(?string $text): string
{
    return ft(\App\I18n\Lang::text((string) $text));
}

function current_lang(): string
{
    return \App\I18n\Lang::code();
}

function request_is_https(): bool
{
    $forwarded = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($forwarded !== '') {
        return trim(explode(',', $forwarded)[0]) === 'https';
    }
    $visitor = (string) ($_SERVER['HTTP_CF_VISITOR'] ?? '');
    if ($visitor !== '' && preg_match('/"scheme"\s*:\s*"https"/i', $visitor)) {
        return true;
    }
    $std = strtolower((string) ($_SERVER['HTTP_FORWARDED'] ?? ''));
    if ($std !== '' && preg_match('/(?:^|[;,]\s*)proto=https(?:[;,]|$)/', $std)) {
        return true;
    }
    if (strtolower((string) ($_SERVER['REQUEST_SCHEME'] ?? '')) === 'https') {
        return true;
    }
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);
}

function live_no_store_headers(string $contentType): void
{
    header('Content-Type: ' . $contentType);
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('CDN-Cache-Control: no-store');
    header('Cloudflare-CDN-Cache-Control: no-store');
    header('Surrogate-Control: no-store');
    header('X-Accel-Buffering: no');
    header('X-Content-Type-Options: nosniff');
}

function base_url(): string
{
    $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
    $host = is_string($forwardedHost) && $forwardedHost !== ''
        ? trim(explode(',', $forwardedHost)[0])
        : (string) ($_SERVER['HTTP_HOST'] ?? '');
    if ($host !== '') {
        return (request_is_https() ? 'https' : 'http') . '://' . $host;
    }
    return rtrim((string) cfg('app.url', ''), '/');
}

function url(string $path = '/'): string
{
    $prefix = trim((string) cfg('app.base_path', ''), '/');
    $path = '/' . ltrim($path, '/');
    if ($prefix !== '') {
        return '/' . $prefix . ($path === '/' ? '' : $path);
    }
    return $path === '/' ? '/' : $path;
}

function absolute_url(string $path = '/'): string
{
    return rtrim(base_url(), '/') . url($path);
}

function asset(string $path): string
{
    return url('/' . ltrim($path, '/'));
}

function upload_url(?string $path): string
{
    if ($path === null || $path === '') {
        return asset('/assets/img/placeholder.svg');
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return asset('/' . ltrim($path, '/'));
}

function redirect(string $path, int $code = 302): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)), true, $code);
    exit;
}

function old(string $key, mixed $default = ''): mixed
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return $default;
    }
    $flash = $_SESSION['_old'][$key] ?? $default;
    return $flash;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        App\Core\Session::start();
        $_SESSION['_flash'][$key] = $message;
        return $message;
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function csrf_token(): string
{
    App\Core\Session::start();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $token ??= $_POST['_csrf'] ?? (is_string($header) ? $header : '');
    return is_string($token) && $token !== '' && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

function json_response(array $data, int $code = 200): never
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('CDN-Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array
{
    static $cached = null;
    static $done = false;
    if ($done) {
        return $cached ?? [];
    }
    $done = true;
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        $cached = [];
        return $cached;
    }
    $data = json_decode($raw, true);
    $cached = is_array($data) ? $data : [];
    return $cached;
}

function request_payload(): array
{
    $json = read_json_body();
    $post = is_array($_POST) ? $_POST : [];
    if ($json && $post) {
        return array_merge($post, $json);
    }
    return $json ?: $post;
}

function public_live_broadcast(): ?array
{
    static $done = false;
    static $row = null;
    if ($done) {
        return $row;
    }
    $done = true;
    try {
        $status = (new \App\Services\PublicLiveService())->publicStatus();
        $row = !empty($status['live']) ? $status : null;
    } catch (\Throwable) {
        $row = null;
    }
    return $row;
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e($method) . '">';
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = trim((string) cfg('app.base_path', ''), '/');
    if ($base !== '' && str_starts_with($uri, '/' . $base)) {
        $uri = substr($uri, strlen($base) + 1) ?: '/';
    }
    if ($uri === '' || $uri === false) {
        $uri = '/';
    }
    return rtrim($uri, '/') ?: '/';
}

function is_active(string $path): bool
{
    $current = current_path();
    if ($path === '/') {
        return $current === '/';
    }
    if (in_array($path, ['/admin', '/student'], true)) {
        return $current === $path;
    }
    return $current === $path || str_starts_with($current, $path . '/');
}

function auth_user(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }
    return $_SESSION['auth'] ?? null;
}

function auth_role(): ?string
{
    return $_SESSION['auth']['role'] ?? null;
}

function is_admin(): bool
{
    return auth_role() === 'admin';
}

function is_panel_owner(): bool
{
    return \App\Core\AdminAccess::isOwner();
}

function admin_can(string $module): bool
{
    return \App\Core\AdminAccess::canModule($module);
}

function is_student(): bool
{
    return auth_role() === 'student';
}

function excerpt(?string $text, int $length = 140): string
{
    $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES, 'UTF-8')) ?? '');
    if (mb_strlen($plain) <= $length) {
        return $plain;
    }
    return rtrim(mb_substr($plain, 0, $length - 1)) . '…';
}

function money_display(?string $value): string
{
    $value = trim((string) $value);
    return $value === '' ? 'To be announced' : $value;
}

function setting_cache_clear(): void
{
    $GLOBALS['ic_settings'] = null;
}

function setting(string $key, mixed $default = null): mixed
{
    if (!isset($GLOBALS['ic_settings']) || !is_array($GLOBALS['ic_settings'])) {
        $GLOBALS['ic_settings'] = [];
        try {
            $rows = App\Core\Database::get()->fetchAll('SELECT setting_key, setting_value FROM settings');
            foreach ($rows as $row) {
                $GLOBALS['ic_settings'][$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable) {
            $GLOBALS['ic_settings'] = [];
        }
    }
    return $GLOBALS['ic_settings'][$key] ?? $default;
}

function site_name(): string
{
    return (string) setting('site_name', cfg('app.name', 'Islamic Center Information Hub'));
}

function json_setting(string $key, array $default = []): array
{
    $raw = setting($key);
    if (!is_string($raw) || $raw === '') {
        return $default;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $default;
}

function page_copy(string $page, string $field, string $default = ''): string
{
    $all = json_setting('page_copy');
    $value = trim((string) ($all[$page][$field] ?? ''));
    return cms($value !== '' ? $value : $default);
}

/**
 * @return list<array{label:string,url:string,hidden:bool}>
 */
function link_list(string $settingKey, array $fallback): array
{
    $links = json_setting($settingKey);
    $out = [];
    foreach ($links as $link) {
        if (!is_array($link)) {
            continue;
        }
        $label = trim((string) ($link['label'] ?? ''));
        $url = trim((string) ($link['url'] ?? ''));
        if ($label === '' || $url === '') {
            continue;
        }
        $out[] = [
            'label' => $label,
            'url' => $url,
            'hidden' => !empty($link['hidden']),
        ];
    }
    if ($out) {
        return $out;
    }
    $fallbackOut = [];
    foreach ($fallback as $link) {
        $fallbackOut[] = [
            'label' => (string) ($link['label'] ?? ''),
            'url' => (string) ($link['url'] ?? ''),
            'hidden' => !empty($link['hidden']),
        ];
    }
    return $fallbackOut;
}

/**
 * Public menus: drop hidden rows and translate labels.
 *
 * @param list<array{label:string,url:string,hidden?:bool}> $items
 * @return list<array{label:string,url:string}>
 */
function visible_nav_items(array $items): array
{
    $out = [];
    foreach ($items as $item) {
        if (!empty($item['hidden'])) {
            continue;
        }
        $out[] = [
            'label' => cms((string) ($item['label'] ?? '')),
            'url' => (string) ($item['url'] ?? ''),
        ];
    }

    return $out;
}

/** @return list<array{label:string,url:string}> */
function header_required_catalog(): array
{
    return [
        ['label' => 'Qibla Direction', 'url' => '/qibla-direction'],
        ['label' => 'Zakat Calculator', 'url' => '/zakat-calculator'],
        ['label' => 'Ramadan Mode', 'url' => '/ramadan-mode'],
        ['label' => 'Fatawa', 'url' => '/fatawa'],
        ['label' => 'Islamic Holidays', 'url' => '/islamic-holidays'],
        ['label' => 'Center Updates', 'url' => '/center-updates'],
        ['label' => 'Live', 'url' => '/live'],
    ];
}

/** @return list<array{label:string,url:string}> */
function footer_required_catalog(): array
{
    return [
        ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
        ['label' => 'Terms & Conditions', 'url' => '/terms-and-conditions'],
        ['label' => 'Disclaimer', 'url' => '/disclaimer'],
    ];
}

/** @return list<string> */
function footer_legacy_explore_urls(): array
{
    return [
        '/',
        '/about-us',
        '/courses',
        '/social-activities',
        '/gallery',
        '/contact-us',
        '/moon-timing',
        '/islamic-calendar',
        '/qibla-direction',
        '/zakat-calculator',
        '/ramadan-mode',
        '/fatawa',
        '/islamic-holidays',
        '/center-updates',
        '/live',
    ];
}

/** @return list<array{label:string,url:string,hidden:bool}> */
function footer_links_all(): array
{
    $saved = link_list('footer_links', footer_required_catalog());
    $legacy = [];
    foreach (footer_legacy_explore_urls() as $url) {
        $legacy[nav_item_path($url)] = true;
    }
    $kept = [];
    foreach ($saved as $link) {
        $path = nav_item_path((string) ($link['url'] ?? ''));
        if (isset($legacy[$path])) {
            continue;
        }
        $kept[] = $link;
    }
    return ensure_nav_links($kept, footer_required_catalog());
}

function footer_links(): array
{
    return visible_nav_items(footer_links_all());
}

/** @return list<array{label:string,url:string,hidden:bool}> */
function header_nav_all(): array
{
    return ensure_nav_links(link_list('header_nav', [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'About Us', 'url' => '/about-us'],
        ['label' => 'Courses', 'url' => '/courses'],
        ['label' => 'Social Activities', 'url' => '/social-activities'],
        ['label' => 'Gallery', 'url' => '/gallery'],
        ['label' => 'Contact Us', 'url' => '/contact-us'],
        ['label' => 'Moon Timing', 'url' => '/moon-timing'],
        ['label' => 'Islamic Calendar', 'url' => '/islamic-calendar'],
        ['label' => 'Qibla Direction', 'url' => '/qibla-direction'],
        ['label' => 'Zakat Calculator', 'url' => '/zakat-calculator'],
        ['label' => 'Ramadan Mode', 'url' => '/ramadan-mode'],
        ['label' => 'Fatawa', 'url' => '/fatawa'],
        ['label' => 'Islamic Holidays', 'url' => '/islamic-holidays'],
        ['label' => 'Center Updates', 'url' => '/center-updates'],
    ]), header_required_catalog());
}

function header_nav(): array
{
    return visible_nav_items(header_nav_all());
}

/** @return list<string> */
function header_more_paths(): array
{
    return [
        '/moon-timing',
        '/islamic-calendar',
        '/qibla-direction',
        '/zakat-calculator',
        '/ramadan-mode',
        '/fatawa',
        '/islamic-holidays',
        '/center-updates',
        '/live',
    ];
}

function nav_item_path(string $url): string
{
    if (preg_match('#^https?://#i', $url)) {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '/');
    } else {
        $path = $url;
    }
    $path = '/' . ltrim($path, '/');
    return rtrim($path, '/') ?: '/';
}

/**
 * @return array{primary:list<array{label:string,url:string}>,more:list<array{label:string,url:string}>}
 */
function header_nav_split(): array
{
    $morePaths = header_more_paths();
    $primary = [];
    $more = [];
    foreach (header_nav() as $item) {
        if (in_array(nav_item_path((string) $item['url']), $morePaths, true)) {
            $more[] = $item;
        } else {
            $primary[] = $item;
        }
    }
    return ['primary' => $primary, 'more' => $more];
}

function default_page_menu_labels(): array
{
    return [
        '/' => 'Home',
        '/about-us' => 'About Us',
        '/courses' => 'Courses',
        '/social-activities' => 'Social Activities',
        '/gallery' => 'Gallery',
        '/contact-us' => 'Contact Us',
        '/moon-timing' => 'Moon Timing',
        '/islamic-calendar' => 'Islamic Calendar',
        '/qibla-direction' => 'Qibla Direction',
        '/zakat-calculator' => 'Zakat Calculator',
        '/ramadan-mode' => 'Ramadan Mode',
        '/fatawa' => 'Fatawa',
        '/islamic-holidays' => 'Islamic Holidays',
        '/center-updates' => 'Center Updates',
        '/live' => 'Live',
        '/privacy-policy' => 'Privacy Policy',
        '/privacy' => 'Privacy Policy',
        '/terms-and-conditions' => 'Terms & Conditions',
        '/terms' => 'Terms & Conditions',
        '/disclaimer' => 'Disclaimer',
    ];
}

function repair_nav_label(string $url, string $label): string
{
    $label = trim($label);
    if ($label !== '') {
        return $label;
    }
    return default_page_menu_labels()[nav_item_path($url)] ?? '';
}

/**
 * Keep hidden rows in the list so they stay Hide in admin and are not
 * re-inserted as visible catalog pages.
 *
 * @param list<array{label:string,url:string,hidden?:bool}> $links
 * @param list<array{label:string,url:string}> $required
 * @return list<array{label:string,url:string,hidden:bool}>
 */
function ensure_nav_links(array $links, array $required): array
{
    $have = [];
    foreach ($links as $i => $link) {
        $url = (string) ($link['url'] ?? '');
        $links[$i]['hidden'] = !empty($link['hidden']);
        $links[$i]['label'] = repair_nav_label($url, (string) ($link['label'] ?? ''));
        $path = nav_item_path($url);
        if ($path !== '') {
            $have[$path] = true;
        }
    }
    foreach ($required as $item) {
        $path = nav_item_path((string) ($item['url'] ?? ''));
        if ($path === '' || isset($have[$path])) {
            continue;
        }
        $links[] = [
            'label' => (string) ($item['label'] ?? ''),
            'url' => (string) ($item['url'] ?? ''),
            'hidden' => false,
        ];
        $have[$path] = true;
    }
    return $links;
}

function extra_field(?array $row, string $key, string $default = ''): string
{
    $extra = json_decode((string) ($row['extra_json'] ?? ''), true);
    if (!is_array($extra)) {
        return $default;
    }
    $value = trim((string) ($extra[$key] ?? ''));
    return $value !== '' ? $value : $default;
}

function request_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item';
}

function selected($a, $b): string
{
    return (string) $a === (string) $b ? ' selected' : '';
}

function checked($a, $b = '1'): string
{
    return (string) $a === (string) $b ? ' checked' : '';
}

/**
 * @param list<string> $titles
 */
function course_pills(array $titles, string $empty = '—'): string
{
    $clean = [];
    foreach ($titles as $title) {
        $title = trim((string) $title);
        if ($title !== '') {
            $clean[] = $title;
        }
    }
    if ($clean === []) {
        return e($empty);
    }
    $html = '<span class="course-pills">';
    foreach ($clean as $title) {
        $html .= '<span class="course-pill" dir="auto">' . e(ftc($title)) . '</span>';
    }
    return $html . '</span>';
}

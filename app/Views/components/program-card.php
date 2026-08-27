<?php
$key = 'community';
$titleLower = strtolower((string) ($program['title'] ?? ''));
if (str_contains($titleLower, 'quran')) {
    $key = 'quran';
} elseif (str_contains($titleLower, 'youth')) {
    $key = 'youth';
}
$href = $program['link_url'] ?: '/about-us';
if (!str_starts_with($href, 'http')) {
    $href = url($href);
}
$icons = [
    'quran' => '<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M8 12c8 5 16 5 16 5s8 0 16-5v22s-8 6-16 6-16-6-16-6V12z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M24 17v23" stroke="currentColor" stroke-width="2.2"/><path d="M14 22h6M28 22h6" stroke="currentColor" stroke-width="2"/></svg>',
    'community' => '<svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="16" cy="15" r="5" fill="none" stroke="currentColor" stroke-width="2.2"/><circle cx="32" cy="15" r="5" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M7 34c1-7 5-11 9-11s8 4 9 11M23 34c1-7 5-11 9-11s8 4 9 11" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M24 24c-3-4-3-7 0-9 3 2 3 5 0 9z" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    'youth' => '<svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="14" r="6" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M12 40c2-10 6-15 12-15s10 5 12 15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M34 8c5 1 8 5 9 9-6 0-10-3-11-9z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M36 20l3 6 7 1-5 4 2 7-7-4-7 4 2-7-5-4 7-1z" fill="currentColor"/></svg>',
];
?>
<a class="service-card" href="<?= e($href) ?>">
    <span class="service-icon"><?= $icons[$key] ?></span>
    <h3><?= e(cms($program['title'])) ?></h3>
</a>

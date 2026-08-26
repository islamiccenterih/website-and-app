<?php
$role = auth_role();
$isAdmin = $role === 'admin';
$groups = $isAdmin ? \App\Core\AdminAccess::navGroups() : [
    'Overview' => [
        ['/student', 'Dashboard'],
        ['/student/join-class', 'Join class'],
        ['/student/course', 'My courses'],
        ['/student/profile', 'Profile'],
        ['/student/results', 'My results'],
    ],
];
$logout = $isAdmin ? '/admin/logout' : '/student/logout';
$home = $isAdmin ? '/admin' : '/student';
$logo = setting('logo_image');
$logoSrc = $logo ? upload_url($logo) : asset('assets/img/logo.png');
$user = auth_user() ?? [];
$jobTitle = trim((string) ($user['job_title'] ?? ''));
$roleLabel = $isAdmin
    ? (is_panel_owner() ? 'Owner' : ($jobTitle !== '' ? $jobTitle : 'Panel member'))
    : 'Student';
$lang = \App\I18n\Lang::code();
$htmlLang = $isAdmin ? 'en' : \App\I18n\Lang::html();
$htmlDir = $isAdmin ? 'ltr' : \App\I18n\Lang::dir();
?>
<!DOCTYPE html>
<html lang="<?= e($htmlLang) ?>" dir="<?= e($htmlDir) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle ?? 'Dashboard') ?></title>
    <link rel="icon" href="<?= e(asset('assets/img/favicon.png')) ?>">
    <link rel="preload" href="<?= e(asset('assets/fonts/merriweather-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>?v=74">
    <meta name="robots" content="noindex,nofollow">
</head>
<body class="dash-body">
<div class="dash-backdrop" data-dash-backdrop hidden></div>
<div class="dash-shell">
    <aside class="dash-side" id="dash-side">
        <div class="dash-side-head">
        <a class="dash-brand" href="<?= e(url($home)) ?>">
            <img src="<?= e($logoSrc) ?>" alt="<?= e(site_name()) ?>">
            <span>
                <em><?= $isAdmin ? 'Administration' : e(tt('Students')) ?></em>
                <?= e(site_name()) ?>
            </span>
        </a>
        <button class="dash-side-close" type="button" data-dash-close aria-label="<?= $isAdmin ? 'Close menu' : e(tt('Close menu')) ?>">✕</button>
        </div>
        <?php if ($isAdmin): ?>
            <label class="dash-filter">
                <span class="visually-hidden">Filter the menu</span>
                <input type="search" data-dash-filter placeholder="Filter the menu" autocomplete="off">
            </label>
        <?php endif; ?>
        <nav>
            <?php foreach ($groups as $group => $items): ?>
                <p class="dash-nav-label"><?= $isAdmin ? e($group) : e(tt($group)) ?></p>
                <?php foreach ($items as [$path, $label]): ?>
                    <a href="<?= e(url($path)) ?>" class="<?= is_active($path) ? 'is-active' : '' ?>"><?= $isAdmin ? e($label) : e(tt($label)) ?></a>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <a href="<?= e(url('/')) ?>"><?= $isAdmin ? 'View website' : e(tt('View website')) ?></a>
        </nav>
        <form method="post" action="<?= e(url($logout)) ?>" class="dash-signout">
            <?= csrf_field() ?>
            <button class="btn btn-ghost btn-block" type="submit"><?= $isAdmin ? 'Sign out' : e(tt('Sign out')) ?></button>
        </form>
    </aside>
    <div class="dash-main">
        <div class="dash-top">
            <div class="dash-user">
                <button class="btn btn-outline btn-sm dash-toggle" type="button" data-dash-toggle><?= $isAdmin ? 'Menu' : e(tt('Menu')) ?></button>
                <span class="dash-user-who">
                <?php
                $avatar = trim((string) ($user['avatar'] ?? ''));
                if ($avatar !== ''): ?>
                    <img class="dash-avatar" src="<?= e(upload_url($avatar)) ?>" alt="">
                <?php elseif (!$isAdmin): ?>
                    <span class="dash-avatar dash-avatar-fallback" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) ($user['name'] ?? 'S'), 0, 1))) ?></span>
                <?php endif; ?>
                <span>
                    <strong><?= e($user['name'] ?? '') ?></strong>
                    <span class="dash-role"><?= $isAdmin ? e($roleLabel) : e(tt($roleLabel)) ?></span>
                </span>
                </span>
            </div>
            <div class="dash-top-actions">
                <nav class="dash-lang" aria-label="<?= $isAdmin ? 'Website language' : e(tt('Select your language')) ?>">
                    <?php foreach (\App\I18n\Lang::LOCALES as $code => $meta): ?>
                        <a href="<?= e(url('/language/' . $code)) ?>" class="<?= $lang === $code ? 'is-active' : '' ?>"><?= e($meta['native']) ?></a>
                    <?php endforeach; ?>
                </nav>
                <a class="btn btn-gold btn-sm" href="<?= e(url($home)) ?>"><?= $isAdmin ? 'Overview' : e(tt('Overview')) ?></a>
            </div>
        </div>
        <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
        <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
        <?= $content ?>
    </div>
</div>
<script src="<?= e(asset('assets/js/app.js')) ?>?v=20" defer></script>
</body>
</html>

<?php
$navSplit = header_nav_split();
$navPrimary = $navSplit['primary'];
$navMore = $navSplit['more'];
$navMoreOpen = false;
foreach ($navMore as $moreItem) {
    if (!str_starts_with((string) $moreItem['url'], 'http') && is_active((string) $moreItem['url'])) {
        $navMoreOpen = true;
        break;
    }
}
$logo = setting('logo_image');
$loginLabel = tt((string) setting('header_login_label', 'Student Login') ?: 'Student Login');
$lang = \App\I18n\Lang::code();
$langDir = \App\I18n\Lang::dir();
$langHtml = \App\I18n\Lang::html();
$footerNote = trim((string) setting('footer_note', ''));
$legalHeading = trim((string) setting('footer_legal_heading', ''));
if ($legalHeading === '') {
    $legalHeading = trim((string) setting('footer_explore_heading', 'Legal'));
}
if ($legalHeading === '' || strcasecmp($legalHeading, 'Explore') === 0) {
    $legalHeading = 'Legal';
}
?>
<!DOCTYPE html>
<html lang="<?= e($langHtml) ?>" dir="<?= e($langDir) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle ?? site_name()) ?></title>
    <?php if (!empty($metaDescription)): ?>
        <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= e($canonical ?? absolute_url(current_path())) ?>">
    <meta property="og:title" content="<?= e($pageTitle ?? site_name()) ?>">
    <meta property="og:description" content="<?= e($metaDescription ?? '') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonical ?? absolute_url(current_path())) ?>">
    <meta property="og:image" content="<?= e(str_starts_with((string) ($ogImage ?? ''), 'http') ? $ogImage : absolute_url($ogImage ?? '/assets/img/og-default.svg')) ?>">
    <?php if (!empty($jsonLd) && is_array($jsonLd)): ?>
        <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>
    <?php endif; ?>
    <link rel="icon" href="<?= e(asset('assets/img/favicon.png')) ?>">
    <link rel="preload" href="<?= e(asset('assets/fonts/merriweather-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>?v=86">
    <script src="<?= e(asset('assets/js/live-worship.js')) ?>?v=6" defer></script>
</head>
<body class="<?= faith_terms_active() ? 'has-faith-terms' : '' ?>">
<a class="skip-link" href="#main"><?= e(tt('Skip to content')) ?></a>
<div class="site-top">
    <div class="lang-bar">
        <div class="container lang-bar-inner">
            <span class="lang-bar-label"><?= e(tt('Select your language')) ?></span>
            <nav class="lang-bar-nav" aria-label="<?= e(tt('Select your language')) ?>">
                <?php foreach (\App\I18n\Lang::LOCALES as $code => $meta): ?>
                    <a href="<?= e(url('/language/' . $code)) ?>" class="<?= $lang === $code ? 'is-active' : '' ?>" hreflang="<?= e($meta['html']) ?>" lang="<?= e($meta['html']) ?>"><?= e($meta['native']) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    <header class="site-header">
        <div class="container header-inner">
            <a class="logo" href="<?= e(url('/')) ?>">
                <?php if ($logo): ?>
                    <img src="<?= e(upload_url($logo)) ?>" alt="<?= e(site_name()) ?>" fetchpriority="high">
                <?php else: ?>
                    <img src="<?= e(asset('assets/img/logo.png')) ?>" alt="<?= e(site_name()) ?>" fetchpriority="high">
                <?php endif; ?>
            </a>
            <nav class="site-nav" id="site-nav" aria-label="<?= e(tt('Main')) ?>">
                <div class="nav-panel">
                    <div class="nav-panel-top">
                        <img class="nav-panel-logo" src="<?= e($logo ? upload_url($logo) : asset('assets/img/logo.png')) ?>" alt="">
                        <button class="nav-close" type="button" aria-label="<?= e(tt('Close menu')) ?>" data-nav-close>
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <ul>
                        <?php foreach ($navPrimary as $item):
                            $path = $item['url'];
                            $href = str_starts_with($path, 'http') ? $path : url($path);
                            ?>
                            <li><a href="<?= e($href) ?>" class="<?= !str_starts_with($path, 'http') && is_active($path) ? 'is-active' : '' ?>"><?= e($item['label']) ?></a></li>
                        <?php endforeach; ?>
                        <?php if ($navMore): ?>
                            <li class="nav-more<?= $navMoreOpen ? ' has-active' : '' ?>" data-nav-more>
                                <button type="button" class="nav-more-btn<?= $navMoreOpen ? ' is-active' : '' ?>" data-nav-more-btn aria-expanded="false" aria-haspopup="true" aria-controls="nav-more-menu">
                                    <?= e(tt('More')) ?>
                                    <span class="nav-more-caret" aria-hidden="true"></span>
                                </button>
                                <ul class="nav-more-menu" id="nav-more-menu">
                                    <?php foreach ($navMore as $item):
                                        $path = $item['url'];
                                        $href = str_starts_with($path, 'http') ? $path : url($path);
                                        ?>
                                        <li><a href="<?= e($href) ?>" class="<?= !str_starts_with($path, 'http') && is_active($path) ? 'is-active' : '' ?>"><?= e($item['label']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <a class="btn-header nav-panel-cta" href="<?= e(url('/student/login')) ?>"><?= e($loginLabel) ?></a>
                    <?php if (public_live_broadcast()): ?>
                        <a class="header-live" href="<?= e(url('/live')) ?>"><?= e(tt('Live')) ?></a>
                    <?php endif; ?>
                </div>
            </nav>
            <div class="header-cta">
                <?php if (public_live_broadcast()): ?>
                    <a class="header-live" href="<?= e(url('/live')) ?>"><?= e(tt('Live')) ?></a>
                <?php endif; ?>
                <a class="btn-header" href="<?= e(url('/student/login')) ?>"><?= e($loginLabel) ?></a>
            </div>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="<?= e(tt('Open menu')) ?>" data-nav-toggle>
                <span class="hamburger" aria-hidden="true"><span></span><span></span><span></span></span>
            </button>
        </div>
    </header>
</div>
<main id="main">
    <?= $content ?>
</main>
<footer class="site-footer" id="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="footer-logo" href="<?= e(url('/')) ?>">
                <img src="<?= e($logo ? upload_url($logo) : asset('assets/img/logo.png')) ?>" alt="<?= e(site_name()) ?>" loading="lazy">
            </a>
            <div class="footer-brand-copy">
                <h3><?= e(cms((string) setting('footer_brand_title', site_name()) ?: site_name())) ?></h3>
                <?php if ($footerNote !== ''): ?>
                    <p><?= e(cms($footerNote)) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-col footer-visit">
            <h3><?= e(tt((string) setting('footer_visit_heading', 'Visit') ?: 'Visit')) ?></h3>
            <p class="footer-address"><?= e((string) setting('contact_address', '')) ?></p>
            <p class="footer-contact">
                <a href="mailto:<?= e((string) setting('contact_email', '')) ?>"><?= e((string) setting('contact_email', '')) ?></a>
                <?php if (setting('contact_phone')): ?><span><?= e((string) setting('contact_phone', '')) ?></span><?php endif; ?>
            </p>
        </div>
        <div class="footer-col footer-legal">
            <h3><?= e(cms($legalHeading)) ?></h3>
            <nav class="footer-links" aria-label="<?= e(tt('Legal')) ?>">
                <?php foreach (footer_links() as $link): ?>
                    <a href="<?= e(str_starts_with($link['url'], 'http') ? $link['url'] : url($link['url'])) ?>"><?= e($link['label']) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    <div class="container footer-bottom">
        <span><?= e((string) setting('footer_copyright') ?: ('© ' . date('Y') . ' ' . site_name() . '. All rights reserved.')) ?></span>
    </div>
</footer>
<div class="scroll-bar" data-scroll-bar aria-hidden="true"></div>
<button class="scroll-meter" type="button" data-scroll-top aria-label="<?= e(tt('Back to top')) ?>">
    <svg viewBox="0 0 36 36" aria-hidden="true">
        <path class="track" d="M18 3 a 15 15 0 1 1 0 30 a 15 15 0 1 1 0 -30"></path>
        <path class="fill" data-scroll-ring d="M18 3 a 15 15 0 1 1 0 30 a 15 15 0 1 1 0 -30" pathLength="100"></path>
    </svg>
    <span data-scroll-pct>0%</span>
</button>
<script src="<?= e(asset('assets/js/app.js')) ?>?v=21" defer></script>
<script src="<?= e(asset('assets/js/prayer-times.js')) ?>?v=6" defer></script>
</body>
</html>

<!DOCTYPE html>
<html lang="<?= e(\App\I18n\Lang::html()) ?>" dir="<?= e(\App\I18n\Lang::dir()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle ?? 'Panel') ?></title>
    <link rel="icon" href="<?= e(asset('assets/img/favicon.png')) ?>">
    <link rel="preload" href="<?= e(asset('assets/fonts/merriweather-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>?v=89">
    <meta name="robots" content="noindex,nofollow">
</head>
<body>
<div class="lang-bar">
    <div class="container lang-bar-inner">
        <span class="lang-bar-label"><?= e(tt('Select your language')) ?></span>
        <nav class="lang-bar-nav" aria-label="<?= e(tt('Select your language')) ?>">
            <?php $lang = \App\I18n\Lang::code(); foreach (\App\I18n\Lang::LOCALES as $code => $meta): ?>
                <a href="<?= e(url('/language/' . $code)) ?>" class="<?= $lang === $code ? 'is-active' : '' ?>"><?= e($meta['native']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>
<?= $content ?>
    <script src="<?= e(asset('assets/js/app.js')) ?>?v=20" defer></script>
</body>
</html>

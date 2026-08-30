<?php
$page = $page ?? [];
$menu = $menu ?? ['label' => '', 'in_header' => true, 'in_footer' => false];
$copy = $copy ?? [];
$fields = is_array($page['fields'] ?? null) ? $page['fields'] : [];
$actions = is_array($page['actions'] ?? null) ? $page['actions'] : [];
$fieldLabels = $fieldLabels ?? \App\Core\SitePages::fieldLabels();
$embed = (string) ($embed ?? '');
?>
<div class="page-editor">
    <header class="page-editor-head">
        <div>
            <p class="page-editor-kicker"><a href="<?= e(url('/admin/pages')) ?>">Pages</a></p>
            <h1><?= e((string) ($page['name'] ?? 'Page')) ?></h1>
            <p class="page-editor-url"><code><?= e((string) ($page['url'] ?? '')) ?></code></p>
        </div>
        <p class="dash-actions">
            <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/pages')) ?>">All pages</a>
            <a class="btn btn-outline btn-sm" href="<?= e(url((string) ($page['url'] ?? '/'))) ?>" target="_blank" rel="noopener">View website</a>
        </p>
    </header>

    <?php if ($embed === ''): ?>
        <form class="form stack-form" method="post" action="<?= e(url('/admin/pages/' . ($page['key'] ?? ''))) ?>">
            <?= csrf_field() ?>
            <?php require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; ?>
            <?php
            $copyHeading = 'Page text';
            require APP_PATH . '/Views/admin/partials/page-copy-fields.php';
            ?>
            <button class="btn btn-walnut page-save" type="submit">Save page</button>
        </form>
        <?php if ($actions): ?>
            <section class="panel-card page-items-card">
                <h2>Items on this page</h2>
                <p class="dash-actions">
                    <?php foreach ($actions as $action): ?>
                        <a class="btn btn-walnut" href="<?= e(url((string) $action['href'])) ?>"><?= e((string) $action['label']) ?></a>
                    <?php endforeach; ?>
                </p>
            </section>
        <?php endif; ?>
    <?php elseif ($embed === 'home'): ?>
        <?php require APP_PATH . '/Views/admin/home/index.php'; ?>
    <?php elseif ($embed === 'about'): ?>
        <?php require APP_PATH . '/Views/admin/about/index.php'; ?>
    <?php elseif ($embed === 'contact'): ?>
        <?php require APP_PATH . '/Views/admin/contact/index.php'; ?>
    <?php elseif ($embed === 'qibla'): ?>
        <?php require APP_PATH . '/Views/admin/qibla/index.php'; ?>
    <?php elseif ($embed === 'zakat'): ?>
        <?php require APP_PATH . '/Views/admin/zakat/index.php'; ?>
        <?php elseif ($embed === 'ramadan'): ?>
            <?php require APP_PATH . '/Views/admin/ramadan/index.php'; ?>
        <?php elseif ($embed === 'daily_quran'): ?>
            <?php require APP_PATH . '/Views/admin/faith/hadith.php'; ?>
        <?php elseif ($embed === 'daily_duas'): ?>
            <?php require APP_PATH . '/Views/admin/faith/duas.php'; ?>
        <?php elseif ($embed === 'janazah'): ?>
            <?php require APP_PATH . '/Views/admin/faith/janazah.php'; ?>
        <?php elseif ($embed === 'hajj_umrah'): ?>
            <?php require APP_PATH . '/Views/admin/faith/hajj.php'; ?>
        <?php endif; ?>
</div>

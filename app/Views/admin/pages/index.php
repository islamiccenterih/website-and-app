<?php
$kicker = 'Website';
$title = 'Pages';
$tag = 'h1';
$lead = 'Every public page. Open one to change its name and the text on the website.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>
<div class="page-grid">
    <?php if (!$pages): ?>
        <p class="help">No pages are assigned to this account.</p>
    <?php endif; ?>
    <?php foreach ($pages as $row): ?>
        <article class="page-card">
            <p class="page-card-url"><code><?= e($row['url']) ?></code></p>
            <h2 dir="auto"><?= e(ftc((string) $row['menu'])) ?></h2>
            <p class="page-card-flags">
                <span class="badge <?= $row['in_header'] ? 'badge-on' : 'badge-off' ?>"><?= $row['in_header'] ? 'Header' : 'Not in header' ?></span>
                <span class="badge <?= $row['in_footer'] ? 'badge-on' : 'badge-off' ?>"><?= $row['in_footer'] ? 'Footer' : 'Not in footer' ?></span>
            </p>
            <p class="dash-actions">
                <a class="btn btn-walnut btn-sm" href="<?= e(url('/admin/pages/' . $row['key'])) ?>">Edit</a>
                <a class="btn btn-outline btn-sm" href="<?= e(url($row['url'])) ?>" target="_blank" rel="noopener">View</a>
            </p>
        </article>
    <?php endforeach; ?>
</div>
<p class="help">Courses, photos, and other lists stay in the sidebar. Site name and logo are in <a href="<?= e(url('/admin/settings')) ?>">Settings</a>. Extra header links are in <a href="<?= e(url('/admin/footer')) ?>">Header &amp; Footer</a>.</p>

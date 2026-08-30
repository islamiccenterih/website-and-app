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
                <?php $placement = (string) ($row['placement'] ?? ($row['in_header'] ? 'primary' : 'off')); ?>
                <span class="badge <?= $placement === 'off' ? 'badge-off' : 'badge-on' ?>"><?= e(header_group_label($placement)) ?></span>
                <span class="badge <?= $row['in_footer'] ? 'badge-on' : 'badge-off' ?>"><?= $row['in_footer'] ? 'Footer' : 'Not in footer' ?></span>
            </p>
            <p class="dash-actions">
                <a class="btn btn-walnut btn-sm" href="<?= e(url('/admin/pages/' . $row['key'])) ?>">Edit</a>
                <a class="btn btn-outline btn-sm" href="<?= e(url($row['url'])) ?>" target="_blank" rel="noopener">View</a>
            </p>
        </article>
    <?php endforeach; ?>
</div>
<p class="help">Open a page to put it in the top menu or the More dropdown — or to hide it. Extra custom links are in <a href="<?= e(url('/admin/footer')) ?>">Header &amp; Footer</a>. Site name and logo are in <a href="<?= e(url('/admin/settings')) ?>">Settings</a>.</p>

<?php
$cmsKey = $cmsKey ?? '';
$cmsPage = $cmsKey !== '' ? \App\Core\SitePages::get($cmsKey) : null;
if (!$cmsPage) {
    return;
}
?>
<p class="cms-bar">
    <a href="<?= e(url('/admin/pages')) ?>">Pages</a>
    <span aria-hidden="true">/</span>
    <strong><?= e((string) $cmsPage['name']) ?></strong>
    <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/pages/' . $cmsPage['key'])) ?>">Page name &amp; text</a>
    <a class="btn btn-outline btn-sm" href="<?= e(url((string) $cmsPage['url'])) ?>" target="_blank" rel="noopener">View website</a>
</p>

<?php
$kicker = 'Administration';
$title = 'Dashboard';
$tag = 'h1';
$lead = $lead ?? 'Overview of content that appears on the public website and in the student panel.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>
<p class="help" style="margin-top:-0.6rem;margin-bottom:1.1rem">Signed in as <?= e($roleLabel ?? 'Admin') ?>.</p>
<?php if ($stats): ?>
<div class="stats">
    <?php foreach ($stats as $label => $value): ?>
        <div class="stat"><span><?= e($label) ?></span><strong><?= e((string) $value) ?></strong></div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<p class="help">No section counts to show yet. Use the menu for the areas assigned to you.</p>
<?php endif; ?>
<p class="dash-actions">
    <?php foreach ($actions as $action): ?>
        <a class="<?= e($action['class']) ?>" href="<?= e(url($action['href'])) ?>"><?= e($action['label']) ?></a>
    <?php endforeach; ?>
</p>

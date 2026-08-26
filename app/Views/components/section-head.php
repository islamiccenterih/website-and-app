<?php
$align = $align ?? 'center';
$kicker = $kicker ?? '';
$title = $title ?? '';
$tag = in_array($tag ?? 'h2', ['h1', 'h2', 'h3'], true) ? $tag : 'h2';
$lead = $lead ?? '';
$light = !empty($light);
$rule = $light ? 'assets/img/heading-rule-light.svg' : 'assets/img/heading-rule.svg';
?>
<div class="sec-head sec-head-<?= e($align) ?><?= $light ? ' is-light' : '' ?>">
    <?php if ($kicker !== ''): ?>
        <span class="sec-kicker"><?= e(tt($kicker)) ?></span>
    <?php endif; ?>
    <<?= $tag ?>><?= e(tt($title)) ?></<?= $tag ?>>
    <span class="sec-rule" aria-hidden="true">
        <img src="<?= e(asset($rule)) ?>" alt="">
    </span>
    <?php if ($lead !== ''): ?>
        <p class="sec-lead"><?= e(tt($lead)) ?></p>
    <?php endif; ?>
</div>

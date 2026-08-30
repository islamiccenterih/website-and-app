<?php
$dua = is_array($dua ?? null) ? $dua : [];
$title = (string) ($dua['title'] ?? '');
$arabic = (string) ($dua['arabic'] ?? '');
$translit = (string) ($dua['translit'] ?? '');
$meaning = (string) ($dua['meaning'] ?? '');
$hi = (string) ($dua['hi'] ?? '');
$ur = (string) ($dua['ur'] ?? '');
$ayah = (string) ($dua['ayah'] ?? '');
$hisn = (string) ($dua['hisn'] ?? '');
$share = implode("\n", array_filter([$arabic, $translit, $meaning, $hi, $ur]));
$showPlay = empty($duaNoPlay) && $arabic !== '';
?>
<article class="ft-dua">
    <div class="ft-dua-head">
        <?php if ($title !== ''): ?>
            <h3><?= e($title) ?></h3>
        <?php else: ?>
            <h3>Du‘a</h3>
        <?php endif; ?>
        <?php if ($showPlay): ?>
            <button class="ft-play-btn" type="button" data-dua-play data-ar="<?= e($arabic) ?>" data-ayah="<?= e($ayah) ?>" data-hisn="<?= e($hisn) ?>">Play</button>
        <?php endif; ?>
    </div>
    <?php if ($arabic !== ''): ?>
        <p class="ft-ar" lang="ar" dir="rtl"><?= e($arabic) ?></p>
    <?php endif; ?>
    <?php if ($translit !== ''): ?>
        <p class="ft-tr"><?= e($translit) ?></p>
    <?php endif; ?>
    <?php if ($meaning !== ''): ?>
        <div class="ft-lang"><span>English</span><p class="ft-en"><?= e($meaning) ?></p></div>
    <?php endif; ?>
    <?php if ($hi !== ''): ?>
        <div class="ft-lang" lang="hi"><span>हिन्दी</span><p class="ft-hi"><?= e($hi) ?></p></div>
    <?php endif; ?>
    <?php if ($ur !== ''): ?>
        <div class="ft-lang" lang="ur" dir="rtl"><span>اردو</span><p class="ft-ur"><?= e($ur) ?></p></div>
    <?php endif; ?>
    <button class="btn btn-outline btn-sm ft-share" type="button" data-share="<?= e($share) ?>">Share on WhatsApp</button>
</article>
<?php unset($duaNoPlay); ?>

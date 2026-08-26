<?php
$illum = is_numeric($illum ?? null) ? (float) $illum : 50.0;
$illum = max(0.0, min(100.0, $illum));
$phase = (string) ($phase ?? '');
$phaseValue = isset($phaseValue) && is_numeric($phaseValue) ? (float) $phaseValue : null;
$size = ($size ?? 'lg') === 'sm' ? 'sm' : 'lg';
$live = $size === 'lg';
$oid = 'moon-' . $size . '-' . bin2hex(random_bytes(4));

$waxing = true;
if ($phaseValue !== null) {
    $waxing = $phaseValue < 0.5;
} else {
    $pl = strtolower($phase);
    $waxing = !str_contains($pl, 'waning') && !str_contains($pl, 'last') && !str_contains($pl, 'third');
}

$i = $illum / 100;
if ($i < 0.02) {
    $shape = 'new';
    $litPath = '';
} elseif ($i > 0.98) {
    $shape = 'full';
    $litPath = '';
} else {
    $shape = $i < 0.5 ? 'crescent' : 'gibbous';
    $rx = max(0.35, abs((2 * $i) - 1) * 50);
    $limbSweep = $waxing ? 1 : 0;
    $termSweep = $i > 0.5 ? $limbSweep : (1 - $limbSweep);
    $litPath = sprintf(
        'M50,0 A50,50 0 0 %d 50,100 A%.2f,50 0 0 %d 50,0',
        $limbSweep,
        $rx,
        $termSweep
    );
}
$blur = $size === 'lg' ? '1.05' : '0.45';
?>
<span class="moon-orb moon-orb-<?= e($size) ?> is-<?= e($shape) ?> <?= $waxing ? 'is-waxing' : 'is-waning' ?><?= $live ? ' moon-orb-live' : '' ?>" aria-hidden="true">
    <span class="moon-face"></span>
    <?php if ($shape !== 'full'): ?>
        <svg class="moon-phase" viewBox="0 0 100 100" focusable="false">
            <defs>
                <filter id="<?= e($oid) ?>-soft" x="-15%" y="-15%" width="130%" height="130%">
                    <feGaussianBlur stdDeviation="<?= e($blur) ?>"/>
                </filter>
                <mask id="<?= e($oid) ?>-mask">
                    <rect width="100" height="100" fill="white"/>
                    <?php if ($litPath !== ''): ?>
                        <path d="<?= e($litPath) ?>" fill="black"/>
                    <?php endif; ?>
                </mask>
            </defs>
            <rect width="100" height="100" fill="#071510" mask="url(#<?= e($oid) ?>-mask)" filter="url(#<?= e($oid) ?>-soft)" opacity="0.82"/>
        </svg>
    <?php endif; ?>
</span>

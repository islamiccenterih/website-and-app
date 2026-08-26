<?php
$hijri = $moon['hijri'] ?? null;
$sky = $moon['moon'] ?? [];
$week = is_array($moon['week'] ?? null) ? $moon['week'] : [];
$illum = is_numeric($sky['illumination'] ?? null) ? (float) $sky['illumination'] : null;
$phase = trim((string) ($sky['phase'] ?? ''));
if ($phase === '') {
    $phase = 'Moon phase unavailable';
}
$fmt = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '' || strcasecmp($value, 'null') === 0 || strcasecmp($value, 'none') === 0) {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date('g:i A', $ts) : $value;
};
$hijriTitle = $hijri
    ? trim(($hijri['day'] ?? '') . ' ' . ($hijri['month_en'] ?? '') . ' ' . ($hijri['year'] ?? '') . ' AH')
    : 'Moon Timing';
$hijriDay = is_array($hijri) ? (int) ($hijri['day'] ?? 0) : 0;
$illumLabel = $illum !== null
    ? rtrim(rtrim(number_format($illum, 1), '0'), '.') . '% illuminated'
    : null;
$times = [
    ['Moonrise', $fmt($sky['moonrise'] ?? null)],
    ['Moonset', $fmt($sky['moonset'] ?? null)],
    ['Sunrise', $fmt($sky['sunrise'] ?? null)],
    ['Sunset', $fmt($sky['sunset'] ?? null)],
];
?>
<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('moon', 'kicker', 'Tonight’s sky');
        $title = page_copy('moon', 'title', 'Moon Timing');
        $tag = 'h1';
        $lead = page_copy('moon', 'lead', 'Islamic date, moonrise, moonset, and phase for Firozabad, Uttar Pradesh, India 283203.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container">
        <?php if (!empty($moon['error']) && empty($moon['ok'])): ?>
            <div class="alert alert-error"><?= e($moon['error']) ?></div>
        <?php elseif (!empty($moon['error'])): ?>
            <div class="alert alert-info"><?= e($moon['error']) ?></div>
        <?php endif; ?>

        <?php
        $kicker = 'Firozabad, Uttar Pradesh, India 283203';
        $title = $hijriTitle;
        $tag = 'h2';
        $lead = $moon['gregorian'] ?? '';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>

        <?php if ($hijri): ?>
            <p class="moon-hijri-line"><?= e($hijri['weekday_en'] ?? '') ?><?= !empty($hijri['month_ar']) ? ' · ' . e($hijri['month_ar']) : '' ?></p>
            <?php if (!empty($hijri['holidays'])): ?>
                <div class="meta-row moon-holidays">
                    <?php foreach ((array) $hijri['holidays'] as $holiday): ?>
                        <span class="pill"><?= e((string) $holiday) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state"><h3>Islamic date unavailable</h3><p>The Hijri conversion service could not be reached.</p></div>
        <?php endif; ?>

        <?php if (!empty($moon['moon'])): ?>
            <div class="moon-stage" data-moon-date="<?= e((string) ($sky['date'] ?? $moon['for_date'] ?? '')) ?>">
                <div class="moon-stage-sky">
                    <?php
                    $phaseValue = $sky['phase_value'] ?? null;
                    $size = 'lg';
                    require APP_PATH . '/Views/components/moon-orb.php';
                    ?>
                    <p class="arabic-mark moon-ar"><?= e(is_array($hijri) ? ($hijri['weekday_ar'] ?? 'قمر') : 'قمر') ?></p>
                    <h3><?= e($phase) ?></h3>
                    <?php if ($illumLabel): ?>
                        <p><?= e($illumLabel) ?></p>
                    <?php endif; ?>
                </div>
                <div class="moon-stage-times">
                    <?php foreach ($times as [$label, $value]): ?>
                        <div>
                            <span><?= e($label) ?></span>
                            <strong><?= e($value) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="moon-meta">
                Times are in IST for Firozabad, Uttar Pradesh, India 283203.
                <?php if (!empty($sky['golden_hour'])): ?>
                    Evening golden hour begins at <?= e($fmt($sky['golden_hour'])) ?>.
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($week): ?>
            <div class="moon-week-head">
                <?php
                $kicker = page_copy('moon', 'week_kicker', 'Looking ahead');
                $title = page_copy('moon', 'week_title', 'Seven-day phase');
                $tag = 'h3';
                $lead = page_copy('moon', 'week_lead', 'Calculated illumination for the coming nights.');
                $align = 'center';
                $light = false;
                require APP_PATH . '/Views/components/section-head.php';
                ?>
            </div>
            <div class="moon-week" role="list">
                <?php foreach ($week as $day): ?>
                    <article class="moon-week-day<?= !empty($day['is_today']) ? ' is-today' : '' ?>" role="listitem">
                        <span class="moon-week-label"><?= e((string) ($day['label'] ?? '')) ?></span>
                        <?php
                        $illum = $day['illumination'] ?? 50;
                        $phase = (string) ($day['phase'] ?? '');
                        $phaseValue = $day['phase_value'] ?? null;
                        $size = 'sm';
                        require APP_PATH . '/Views/components/moon-orb.php';
                        ?>
                        <strong><?= e((string) ($day['daynum'] ?? '')) ?></strong>
                        <span class="moon-week-phase"><?= e((string) ($day['phase'] ?? '')) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($hijriDay >= 28): ?>
            <div class="moon-note moon-note-sighting">
                <h3><?= e(page_copy('moon', 'sighting_title', 'Moon sighting window')) ?></h3>
                <p><?= e(page_copy('moon', 'sighting_text', 'The Hijri month is near its end. Local moon-sighting for the next month, including Ramadan and Eid, follows the center’s announcement rather than these calculated times.')) ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>
<script>
(function () {
    var stage = document.querySelector('[data-moon-date]');
    if (!stage) return;
    var pageDate = stage.getAttribute('data-moon-date');
    if (!pageDate) return;
    var tick = function () {
        var today = new Intl.DateTimeFormat('en-CA', {
            timeZone: 'Asia/Kolkata',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date());
        if (today !== pageDate) {
            window.location.reload();
        }
    };
    setInterval(tick, 60000);
})();
</script>

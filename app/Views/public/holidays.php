<?php
$p = is_array($page ?? null) ? $page : [];
$year = (int) ($p['year'] ?? date('Y'));
$years = is_array($p['years'] ?? null) ? $p['years'] : range(2026, 2031);
$eids = is_array($p['eids'] ?? null) ? $p['eids'] : [];
$rows = is_array($p['holidays'] ?? null) ? $p['holidays'] : [];
?>
<section class="page-hero hol-hero">
    <div class="hol-sky" aria-hidden="true">
        <span class="hol-crescent"></span>
        <span class="hol-star s1"></span>
        <span class="hol-star s2"></span>
        <span class="hol-star s3"></span>
    </div>
    <div class="container">
        <?php
        $kicker = page_copy('holidays', 'kicker', 'India');
        $title = page_copy('holidays', 'title', 'Islamic Holidays');
        $tag = 'h1';
        $lead = page_copy('holidays', 'lead', 'Eid ul-Fitr and Eid al-Adha as observed in India, then every major Islamic day of the year — Hijri date and the civil date used in Firozabad.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>

<section class="section section-sand hol-eids-sec">
    <div class="container">
        <nav class="hol-years" aria-label="<?= e(tt('Choose a year')) ?>">
            <p class="hol-years-label"><?= e(tt('Dates for India')) ?></p>
            <div class="hol-year-pills">
                <?php foreach ($years as $y): ?>
                    <a class="hol-year<?= (int) $y === $year ? ' is-on' : '' ?>" href="<?= e(url('/islamic-holidays') . '?year=' . (int) $y) ?>"><?= (int) $y ?></a>
                <?php endforeach; ?>
            </div>
        </nav>

        <?php if (!$eids): ?>
            <div class="empty-state">
                <h3><?= e(tt('Dates could not be loaded')) ?></h3>
                <p><?= e(tt('Open this page again in a moment.')) ?></p>
            </div>
        <?php else: ?>
            <div class="hol-eid-grid">
                <?php foreach ($eids as $eid): ?>
                    <?php
                    $tone = (string) ($eid['tone'] ?? 'fitr');
                    $status = (string) ($eid['status'] ?? '');
                    ?>
                    <article class="hol-eid hol-eid-<?= e($tone) ?> is-<?= e($status) ?>">
                        <div class="hol-eid-ornament" aria-hidden="true"></div>
                        <p class="hol-eid-place"><?= e(tt('In India')) ?></p>
                        <p class="hol-eid-kicker"><?= e(tt($tone === 'adha' ? 'The greater Eid' : 'The festival of fast-breaking')) ?></p>
                        <p class="hol-eid-ar" lang="ar" dir="rtl"><?= e((string) ($eid['name_ar'] ?? '')) ?></p>
                        <h2><?= e(tt((string) ($eid['name'] ?? ''))) ?></h2>
                        <div class="hol-eid-date">
                            <span class="hol-eid-num"><?= (int) ($eid['gregorian_day'] ?? 0) ?></span>
                            <span class="hol-eid-mon">
                                <strong><?= e(tt((string) ($eid['gregorian_month_en'] ?? ''))) ?></strong>
                                <em><?= (int) ($eid['gregorian_year'] ?? 0) ?></em>
                            </span>
                        </div>
                        <p class="hol-eid-full"><?= e(tt((string) ($eid['weekday_en'] ?? ''))) ?></p>
                        <p class="hol-eid-hijri">
                            <span><?= e(ft((string) ($eid['hijri_full'] ?? ''))) ?></span>
                            <?php if (!empty($eid['hijri_month_ar'])): ?>
                                <span lang="ar" dir="rtl"><?= (int) ($eid['hijri_day'] ?? 0) ?> <?= e((string) $eid['hijri_month_ar']) ?> <?= (int) ($eid['hijri_year'] ?? 0) ?></span>
                            <?php endif; ?>
                        </p>
                        <p class="hol-eid-when is-<?= e($status) ?>"><?= e((string) ($eid['when'] ?? '')) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section hol-table-sec">
    <div class="container hol-year-wrap">
        <?php
        $kicker = tt('Full year');
        $title = tt('All Islamic holidays') . ' · ' . $year;
        $tag = 'h2';
        $lead = tt('Every major day of worship in this year, with the date used in India and the Hijri day beside it.');
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>

        <?php if (!$rows): ?>
            <div class="empty-state">
                <h3><?= e(tt('No holidays listed for this year')) ?></h3>
                <p><?= e(tt('Choose another year from 2026 to 2031.')) ?></p>
            </div>
        <?php else: ?>
            <ol class="hol-list">
                <?php foreach ($rows as $row): ?>
                    <?php $isEid = in_array((string) ($row['id'] ?? ''), ['eid-al-fitr', 'eid-al-adha'], true); ?>
                    <li class="hol-item is-<?= e((string) ($row['status'] ?? '')) ?><?= $isEid ? ' is-eid' : '' ?>">
                        <div class="hol-item-date">
                            <strong><?= (int) ($row['gregorian_day'] ?? 0) ?></strong>
                            <span><?= e(tt((string) ($row['gregorian_month_en'] ?? ''))) ?> <?= (int) ($row['gregorian_year'] ?? 0) ?></span>
                            <em><?= e(tt((string) ($row['weekday_en'] ?? ''))) ?></em>
                        </div>
                        <div class="hol-item-copy">
                            <h3><?= e(tt((string) ($row['name'] ?? ''))) ?></h3>
                            <p class="hol-ar" lang="ar" dir="rtl"><?= e((string) ($row['name_ar'] ?? '')) ?></p>
                            <p class="hol-item-hijri"><?= e(ft((string) ($row['hijri_full'] ?? ''))) ?></p>
                        </div>
                        <span class="hol-chip is-<?= e((string) ($row['status'] ?? '')) ?>"><?= e((string) ($row['when'] ?? '')) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <p class="hol-note"><?= e(tt((string) ($p['note'] ?? ''))) ?></p>
    </div>
</section>

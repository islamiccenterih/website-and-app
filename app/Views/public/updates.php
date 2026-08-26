<?php
$today = $today ?? null;
$updates = $updates ?? [];
$todayStamp = $today ? strtotime((string) $today['published_on']) : false;
?>
<section class="updates-hero">
    <div class="container">
        <?php
        $kicker = page_copy('updates', 'kicker', 'From the Center');
        $title = page_copy('updates', 'title', 'Center Updates');
        $tag = 'h1';
        $lead = page_copy('updates', 'lead', 'Daily news from Islamic Center Information Hub — gatherings, classes, and notices, written as they are posted.');
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>

<section class="section updates-spotlight">
    <div class="container">
        <?php if ($today): ?>
            <p class="updates-spotlight-label">
                <span><?= e(tt('Today’s notice')) ?></span>
                <?php if ($todayStamp): ?>
                    <time datetime="<?= e((string) $today['published_on']) ?>"><?= e(tt(date('l', $todayStamp))) ?>, <?= e(date('j', $todayStamp)) ?> <?= e(tt(date('F', $todayStamp))) ?> <?= e(date('Y', $todayStamp)) ?></time>
                <?php endif; ?>
            </p>
            <article class="updates-spotlight-card">
                <div class="updates-spotlight-copy">
                    <h2><a href="<?= e(url('/center-updates/' . $today['slug'])) ?>"><?= e(ft((string) $today['title'])) ?></a></h2>
                    <p><?= e(\App\Models\CenterUpdate::cardExcerpt($today, 240)) ?></p>
                    <p class="updates-spotlight-cta">
                        <a class="btn btn-gold" href="<?= e(url('/center-updates/' . $today['slug'])) ?>"><?= e(tt('Read update')) ?></a>
                    </p>
                </div>
            </article>
        <?php else: ?>
            <div class="updates-empty">
                <h2><?= e(tt('No update for today yet')) ?></h2>
                <p><?= e(tt('When the administration publishes today’s notice, it will appear here first.')) ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($updates): ?>
<section class="section updates-ledger">
    <div class="container">
        <?php
        $kicker = page_copy('updates', 'archive_kicker', 'Notice board');
        $title = page_copy('updates', 'archive_title', 'Earlier updates');
        $tag = 'h2';
        $lead = tt('Stories posted before today.');
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <div class="updates-mosaic">
            <?php foreach ($updates as $row): ?>
                <?php $stamp = strtotime((string) $row['published_on']); ?>
                <a class="updates-tile" href="<?= e(url('/center-updates/' . $row['slug'])) ?>">
                    <span class="updates-tile-body">
                        <?php if ($stamp): ?>
                            <time datetime="<?= e((string) $row['published_on']) ?>"><?= e(date('j', $stamp)) ?> <?= e(tt(date('F', $stamp))) ?> <?= e(date('Y', $stamp)) ?></time>
                        <?php endif; ?>
                        <strong><?= e(ft((string) $row['title'])) ?></strong>
                        <em><?= e(\App\Models\CenterUpdate::cardExcerpt($row, 120)) ?></em>
                        <span class="updates-tile-go"><?= e(tt('Open this update')) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

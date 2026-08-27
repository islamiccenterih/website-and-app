<?php
$today = $today ?? null;
$fatawa = $fatawa ?? [];
$todayBlocks = $today ? \App\Models\Fatwa::languageBlocks($today) : [];
$todayFirst = $todayBlocks[0] ?? null;
$todayStamp = $today ? strtotime((string) $today['issued_on']) : false;
?>
<section class="page-hero fatwa-hero">
    <div class="fatwa-hero-ornament" aria-hidden="true"></div>
    <div class="container">
        <?php
        $kicker = page_copy('fatawa', 'kicker', 'Daily guidance');
        $title = page_copy('fatawa', 'title', 'Fatawa');
        $tag = 'h1';
        $lead = page_copy('fatawa', 'lead', 'A new fatwa is published here each day. Read it, then ask a question on that fatwa if you need a ruling for your own situation.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>

<section class="section section-sand">
    <div class="container fatwa-home">
        <?php if ($today && $todayFirst): ?>
            <article class="fatwa-feature">
                <div class="fatwa-feature-date">
                    <span><?= e(tt('Today’s fatwa')) ?></span>
                    <?php if ($todayStamp): ?>
                        <strong><?= e(date('j', $todayStamp)) ?></strong>
                        <em><?= e(tt(date('F', $todayStamp))) ?> <?= e(date('Y', $todayStamp)) ?></em>
                    <?php endif; ?>
                </div>
                <div class="fatwa-feature-body" lang="<?= e($todayFirst['lang']) ?>" dir="<?= e($todayFirst['dir']) ?>">
                    <h2><?= e(cms($todayFirst['title'] !== '' ? $todayFirst['title'] : tt('Today’s fatwa'))) ?></h2>
                    <?php if ($todayFirst['body'] !== ''): ?>
                        <p><?= e(\App\Models\Fatwa::excerpt($today, 280)) ?></p>
                    <?php endif; ?>
                    <?php $langs = \App\Models\Fatwa::langCodes($today); ?>
                    <?php if ($langs): ?>
                        <p class="fatwa-chips"><?php foreach ($langs as $code): ?><span><?= e($code) ?></span><?php endforeach; ?></p>
                    <?php endif; ?>
                    <p class="fatwa-feature-cta">
                        <a class="btn btn-gold" href="<?= e(url('/fatawa/' . $today['slug'])) ?>"><?= e(tt('Read this fatwa')) ?></a>
                        <a class="btn btn-outline" href="<?= e(url('/fatawa/' . $today['slug'] . '#ask')) ?>"><?= e(tt('Ask a question')) ?></a>
                    </p>
                </div>
            </article>
        <?php else: ?>
            <div class="empty-state">
                <h3><?= e(tt('No fatwa for today yet')) ?></h3>
                <p><?= e(tt('When the administration publishes today’s fatwa, it will appear here first.')) ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($fatawa): ?>
<section class="section">
    <div class="container fatwa-home">
        <?php
        $kicker = page_copy('fatawa', 'archive_kicker', 'Previous days');
        $title = page_copy('fatawa', 'archive_title', 'Previous fatawa');
        $tag = 'h2';
        $lead = tt('Open any day to read the ruling and ask a question.');
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <ol class="fatwa-past">
            <?php foreach ($fatawa as $row): ?>
                <?php
                $stamp = strtotime((string) $row['issued_on']);
                $langs = \App\Models\Fatwa::langCodes($row);
                $first = \App\Models\Fatwa::languageBlocks($row)[0] ?? null;
                $excerpt = \App\Models\Fatwa::excerpt($row, 110);
                ?>
                <li>
                    <a class="fatwa-item" href="<?= e(url('/fatawa/' . $row['slug'])) ?>">
                        <div class="fatwa-item-date">
                            <?php if ($stamp): ?>
                                <strong><?= e(date('j', $stamp)) ?></strong>
                                <span><?= e(tt(date('F', $stamp))) ?> <?= e(date('Y', $stamp)) ?></span>
                                <em><?= e(tt(date('l', $stamp))) ?></em>
                            <?php endif; ?>
                        </div>
                        <div class="fatwa-item-copy"<?php if ($first): ?> lang="<?= e($first['lang']) ?>" dir="<?= e($first['dir']) ?>"<?php endif; ?>>
                            <h3><?= e(cms(\App\Models\Fatwa::cardTitle($row))) ?></h3>
                            <?php if ($excerpt !== ''): ?>
                                <p><?= e($excerpt) ?></p>
                            <?php endif; ?>
                            <?php if ($langs): ?>
                                <p class="fatwa-item-langs"><?= e(implode(' · ', $langs)) ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="fatwa-item-go"><?= e(tt('Read')) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>

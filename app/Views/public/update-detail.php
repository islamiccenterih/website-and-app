<?php
$item = $item ?? [];
$body = (string) ($body ?? '');
$more = $more ?? [];
$issued = (string) ($item['published_on'] ?? '');
$stamp = $issued !== '' ? strtotime($issued) : false;
$backUrl = url('/center-updates');
?>
<section class="page-hero fatwa-hero">
    <div class="fatwa-hero-ornament" aria-hidden="true"></div>
    <div class="container">
        <?php
        $kicker = $stamp ? date('j F Y', $stamp) : page_copy('updates', 'kicker', 'From the Center');
        $title = (string) ($item['title'] ?? tt('Center Updates'));
        $tag = 'h1';
        $lead = '';
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <p class="hero-cta fatwa-hero-cta">
            <a class="btn btn-gold" href="<?= e($backUrl) ?>"><?= e(tt('Back to Center Updates')) ?></a>
        </p>
    </div>
</section>

<section class="section">
    <div class="container update-detail">
        <nav class="fatwa-crumb" aria-label="<?= e(tt('Center Updates')) ?>">
            <a href="<?= e($backUrl) ?>"><?= e(tt('← Back to Center Updates')) ?></a>
            <span aria-hidden="true">/</span>
            <span><?= $stamp ? e(date('j M Y', $stamp)) : e(tt('Update')) ?></span>
        </nav>

        <article class="update-read">
            <div class="update-body">
                <?= $body !== '' ? ft($body) : '<p>' . e(tt('This update has no text yet')) . '</p>' ?>
            </div>
        </article>

        <?php if ($more): ?>
            <div class="fatwa-more">
                <h2><?= e(tt('More updates')) ?></h2>
                <ul>
                    <?php foreach ($more as $row): ?>
                        <li>
                            <a href="<?= e(url('/center-updates/' . $row['slug'])) ?>">
                                <time><?= e(date('j M Y', strtotime((string) $row['published_on']))) ?></time>
                                <strong><?= e(ft((string) $row['title'])) ?></strong>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <p class="fatwa-back">
            <a class="btn btn-gold" href="<?= e($backUrl) ?>"><?= e(tt('Back to Center Updates')) ?></a>
        </p>
    </div>
</section>

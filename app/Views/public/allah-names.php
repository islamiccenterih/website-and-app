<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('allah_names', 'kicker', 'Asma ul Husna');
        $title = page_copy('allah_names', 'title', '99 Allah Names');
        $tag = 'h1';
        $lead = page_copy('allah_names', 'lead', 'The ninety-nine beautiful names. Tap a name to hear it, and read the meaning.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page" data-names-root>
        <div class="ft-toolbar">
            <input type="search" data-names-search placeholder="Search a name or meaning" aria-label="Search names">
            <p class="ft-help"><?= e(page_copy('allah_names', 'help', 'Tap Play to hear this name.')) ?></p>
        </div>
        <div class="ft-names-grid" data-names-grid>
            <?php foreach ($names ?? [] as $name): ?>
                <article class="ft-name-card" data-name-card data-n="<?= (int) ($name['n'] ?? 0) ?>" data-ar="<?= e((string) ($name['ar'] ?? '')) ?>" data-search="<?= e(strtolower(($name['tr'] ?? '') . ' ' . ($name['en'] ?? '') . ' ' . ($name['hi'] ?? ''))) ?>">
                    <span class="ft-name-num"><?= (int) ($name['n'] ?? 0) ?></span>
                    <p class="ft-ar" lang="ar" dir="rtl"><?= e((string) ($name['ar'] ?? '')) ?></p>
                    <h3><?= e((string) ($name['tr'] ?? '')) ?></h3>
                    <p><?= e((string) ($name['en'] ?? '')) ?></p>
                    <p class="ft-hi"><?= e((string) ($name['hi'] ?? '')) ?></p>
                    <button class="btn btn-gold btn-sm" type="button" data-name-play>Play</button>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="ft-empty" hidden data-names-empty>No name matches that search.</p>
    </div>
</section>
<script src="<?= e(asset('assets/js/allah-names.js')) ?>?v=5" defer></script>

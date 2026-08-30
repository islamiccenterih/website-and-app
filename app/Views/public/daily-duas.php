<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('daily_duas', 'kicker', 'Words for the day');
        $title = page_copy('daily_duas', 'title', 'Daily Duas');
        $tag = 'h1';
        $lead = page_copy('daily_duas', 'lead', 'Duas for morning and evening, food, travel, the home, illness, and janazah.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page" data-duas-root>
        <nav class="ft-tabs" aria-label="Dua categories">
            <?php foreach ($groups ?? [] as $i => $group): ?>
                <button type="button" class="ft-tab<?= $i === 0 ? ' is-on' : '' ?>" data-dua-tab="<?= e((string) ($group['id'] ?? $i)) ?>"><?= e((string) ($group['title'] ?? '')) ?></button>
            <?php endforeach; ?>
        </nav>
        <?php foreach ($groups ?? [] as $i => $group): ?>
            <div class="ft-tab-panel" data-dua-panel="<?= e((string) ($group['id'] ?? $i)) ?>"<?= $i === 0 ? '' : ' hidden' ?>>
                <h2 class="ft-section-title"><?= e((string) ($group['title'] ?? '')) ?></h2>
                <div class="ft-dua-list">
                    <?php foreach (($group['items'] ?? []) as $dua): ?>
                        <?php $duaNoPlay = true; require APP_PATH . '/Views/components/dua-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<script src="<?= e(asset('assets/js/faith-ui.js')) ?>?v=5" defer></script>

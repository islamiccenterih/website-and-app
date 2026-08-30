<?php
$hajj = is_array($hajj ?? null) ? $hajj : [];
$umrah = is_array($hajj['umrah'] ?? null) ? $hajj['umrah'] : [];
$hajjSteps = is_array($hajj['hajj'] ?? null) ? $hajj['hajj'] : [];
$hajjDuas = is_array($hajj['duas'] ?? null) ? $hajj['duas'] : [];
?>
<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('hajj_umrah', 'kicker', 'The journey');
        $title = page_copy('hajj_umrah', 'title', 'Hajj & Umrah');
        $tag = 'h1';
        $lead = page_copy('hajj_umrah', 'lead', 'A short checklist and the main duas for Umrah and Hajj. / उमरा और हज की सूची और मुख्य दुआएँ।');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page" data-hajj-root>
        <div class="ft-split">
            <article class="ft-panel">
                <p class="ft-kicker">Umrah</p>
                <h2>Umrah checklist</h2>
                <ol class="ft-check-list" data-check-list="umrah">
                    <?php foreach ($umrah as $i => $item): ?>
                        <li>
                            <label>
                                <input type="checkbox" data-check-item="umrah-<?= $i ?>">
                                <span>
                                    <strong><?= e((string) ($item['title'] ?? '')) ?></strong>
                                    <?= e((string) ($item['text'] ?? '')) ?>
                                    <?php if (trim((string) ($item['text_hi'] ?? '')) !== ''): ?>
                                        <span class="ft-hi-block" lang="hi" style="display:block;margin-top:0.55rem">
                                            <span>हिन्दी</span>
                                            <?= e((string) $item['text_hi']) ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </article>
            <article class="ft-panel">
                <p class="ft-kicker">Hajj</p>
                <h2>Hajj days</h2>
                <ol class="ft-check-list" data-check-list="hajj">
                    <?php foreach ($hajjSteps as $i => $item): ?>
                        <li>
                            <label>
                                <input type="checkbox" data-check-item="hajj-<?= $i ?>">
                                <span>
                                    <strong><?= e((string) ($item['title'] ?? '')) ?></strong>
                                    <?= e((string) ($item['text'] ?? '')) ?>
                                    <?php if (trim((string) ($item['text_hi'] ?? '')) !== ''): ?>
                                        <span class="ft-hi-block" lang="hi" style="display:block;margin-top:0.55rem">
                                            <span>हिन्दी</span>
                                            <?= e((string) $item['text_hi']) ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </article>
        </div>
        <h2 class="ft-section-title">Duas on the journey</h2>
        <div class="ft-dua-list">
            <?php foreach ($hajjDuas as $dua): ?>
                <?php $duaNoPlay = true; require APP_PATH . '/Views/components/dua-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script src="<?= e(asset('assets/js/faith-ui.js')) ?>?v=5" defer></script>

<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('janazah', 'kicker', 'When a Muslim dies');
        $title = page_copy('janazah', 'title', 'Janazah Steps');
        $tag = 'h1';
        $lead = page_copy('janazah', 'lead', 'Ghusl, kafan, the janazah prayer, and burial — with the duas said at each step.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page">
        <ol class="ft-steps">
            <?php foreach ($steps ?? [] as $i => $step): ?>
                <li class="ft-step">
                    <span class="ft-step-num"><?= $i + 1 ?></span>
                    <div>
                        <h2><?= e((string) ($step['title'] ?? '')) ?></h2>
                        <p><?= e((string) ($step['lead'] ?? '')) ?></p>
                        <?php if (trim((string) ($step['lead_hi'] ?? '')) !== ''): ?>
                            <div class="ft-hi-block" lang="hi">
                                <span>हिन्दी</span>
                                <p><?= e((string) $step['lead_hi']) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php $dua = is_array($step['dua'] ?? null) ? $step['dua'] : []; ?>
                        <?php $duaNoPlay = true; require APP_PATH . '/Views/components/dua-card.php'; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<script src="<?= e(asset('assets/js/faith-ui.js')) ?>?v=5" defer></script>

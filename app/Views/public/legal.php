<?php
$doc = is_array($doc ?? null) ? $doc : [];
$sections = is_array($doc['sections'] ?? null) ? $doc['sections'] : [];
$others = is_array($others ?? null) ? $others : [];
$copyKey = (string) ($doc['key'] ?? 'privacy');
?>
<section class="page-hero legal-hero">
    <div class="container">
        <?php
        $kicker = page_copy($copyKey, 'kicker', (string) ($doc['kicker'] ?? 'Legal'));
        $title = page_copy($copyKey, 'title', (string) ($doc['title'] ?? 'Legal'));
        $tag = 'h1';
        $lead = page_copy($copyKey, 'lead', (string) ($doc['lead'] ?? ''));
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <p class="legal-updated"><?= e(tt('Last updated')) ?>: <?= e(tt((string) ($updated ?? ''))) ?></p>
    </div>
</section>
<section class="section legal-section">
    <div class="container legal-layout">
        <?php if ($sections): ?>
            <nav class="legal-toc" aria-label="<?= e(tt('On this page')) ?>">
                <h2><?= e(tt('On this page')) ?></h2>
                <ul>
                    <?php foreach ($sections as $section): ?>
                        <li><a href="#<?= e((string) ($section['id'] ?? '')) ?>"><?= e(tt((string) ($section['title'] ?? ''))) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
        <article class="legal-doc prose">
            <p class="legal-lang-note"><?= e(tt('This page is the official English text. If a translated heading differs, the English wording applies.')) ?></p>
            <?php foreach ($sections as $section): ?>
                <h2 id="<?= e((string) ($section['id'] ?? '')) ?>"><?= e(tt((string) ($section['title'] ?? ''))) ?></h2>
                <?php foreach (is_array($section['paragraphs'] ?? null) ? $section['paragraphs'] : [] as $para): ?>
                    <p><?= e((string) $para) ?></p>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if ($others): ?>
                <div class="legal-related">
                    <h2><?= e(tt('Related')) ?></h2>
                    <p>
                        <?php foreach ($others as $i => $item): ?>
                            <?php if ($i > 0): ?> · <?php endif; ?>
                            <a href="<?= e(url((string) ($item['path'] ?? '/'))) ?>"><?= e(tt((string) ($item['title'] ?? ''))) ?></a>
                        <?php endforeach; ?>
                    </p>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>

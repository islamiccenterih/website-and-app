<?php
$fatwa = $fatwa ?? [];
$blocks = $blocks ?? [];
$questions = $questions ?? [];
$more = $more ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$issued = (string) ($fatwa['issued_on'] ?? '');
$stamp = $issued !== '' ? strtotime($issued) : false;
$primary = $blocks[0] ?? null;
$backUrl = url('/fatawa');
?>
<section class="page-hero fatwa-hero">
    <div class="fatwa-hero-ornament" aria-hidden="true"></div>
    <div class="container">
        <?php
        $kicker = $stamp ? date('j F Y', $stamp) : page_copy('fatawa', 'kicker', 'Daily guidance');
        $title = $primary['title'] ?? tt('Fatwa');
        $tag = 'h1';
        $lead = page_copy('fatawa', 'detail_lead', 'Read the fatwa, then ask a question about this ruling. The answer will appear under your question.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <p class="hero-cta fatwa-hero-cta">
            <a class="btn btn-gold" href="<?= e($backUrl) ?>"><?= e(tt('Back to Fatawa')) ?></a>
            <a class="btn btn-ghost" href="#ask"><?= e(tt('Ask a question')) ?></a>
        </p>
    </div>
</section>

<section class="section fatwa-detail-sec">
    <div class="container fatwa-detail">
        <nav class="fatwa-crumb" aria-label="<?= e(tt('Fatawa')) ?>">
            <a href="<?= e($backUrl) ?>"><?= e(tt('← Back to Fatawa')) ?></a>
            <span aria-hidden="true">/</span>
            <span><?= $stamp ? e(date('j M Y', $stamp)) : e(tt('Fatwa')) ?></span>
        </nav>

        <?php if (!$blocks): ?>
            <div class="empty-state"><h3><?= e(tt('This fatwa has no text yet')) ?></h3></div>
        <?php else: ?>
            <div class="fatwa-read">
                <?php if (count($blocks) > 1): ?>
                    <?php foreach ($blocks as $i => $block): ?>
                        <input class="fatwa-lang-input" id="fatwa-lang-<?= e($block['code']) ?>" type="radio" name="fatwa-lang"<?= $i === 0 ? ' checked' : '' ?>>
                    <?php endforeach; ?>
                    <div class="fatwa-lang-tabs" role="tablist" aria-label="<?= e(tt('Languages')) ?>">
                        <?php foreach ($blocks as $i => $block): ?>
                            <label class="fatwa-lang-tab" for="fatwa-lang-<?= e($block['code']) ?>"><?= e($block['label']) ?></label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="fatwa-lang-panels">
                    <?php foreach ($blocks as $i => $block): ?>
                        <article class="fatwa-block<?= $i === 0 ? ' is-primary' : '' ?>" data-lang="<?= e($block['code']) ?>" lang="<?= e($block['lang']) ?>" dir="<?= e($block['dir']) ?>">
                            <?php if (count($blocks) === 1): ?>
                                <p class="fatwa-lang-label"><?= e($block['label']) ?></p>
                            <?php endif; ?>
                            <?php if ($block['title'] !== ''): ?>
                                <h2><?= e(ft($block['title'])) ?></h2>
                            <?php endif; ?>
                            <?php if ($block['body'] !== ''): ?>
                                <div class="prose"><p><?= nl2br(e($block['body'])) ?></p></div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="fatwa-qa" id="questions">
            <div class="fatwa-qa-head">
                <p class="fatwa-qa-kicker"><?= e(tt('Questions')) ?></p>
                <h2><?= e(tt('Questions on this fatwa')) ?></h2>
                <p><?= e(tt('Each answer sits under the question it belongs to.')) ?></p>
            </div>
            <?php if (!$questions): ?>
                <p class="fatwa-qa-empty"><?= e(tt('No questions yet. Ask the first one below.')) ?></p>
            <?php endif; ?>
            <?php foreach ($questions as $i => $q): ?>
                <article class="fatwa-q" id="question-<?= (int) $q['id'] ?>">
                    <header>
                        <span class="fatwa-q-num"><?= (int) $i + 1 ?></span>
                        <div>
                            <strong><?= e((string) $q['name']) ?></strong>
                            <time datetime="<?= e((string) $q['created_at']) ?>"><?= e(date('j M Y', strtotime((string) $q['created_at']))) ?></time>
                        </div>
                    </header>
                    <?php if (!empty($q['body'])): ?>
                        <p><?= nl2br(e((string) $q['body'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($q['attachment_path'])): ?>
                        <p class="fatwa-q-file">
                            <?php if (\App\Models\FatwaQuestion::isImage($q['attachment_mime'] ?? null)): ?>
                                <a href="<?= e(upload_url($q['attachment_path'])) ?>" target="_blank" rel="noopener">
                                    <img src="<?= e(upload_url($q['attachment_path'])) ?>" alt="<?= e((string) ($q['attachment_name'] ?: '')) ?>">
                                </a>
                            <?php else: ?>
                                <a class="btn btn-outline btn-sm" href="<?= e(upload_url($q['attachment_path'])) ?>" target="_blank" rel="noopener"><?= e(tt('Open attached file')) ?> — <?= e((string) ($q['attachment_name'] ?: 'file')) ?></a>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if (($q['status'] ?? '') === 'answered' && trim((string) ($q['answer'] ?? '')) !== ''): ?>
                        <div class="fatwa-a">
                            <p class="fatwa-a-label"><?= e(tt('Answer')) ?></p>
                            <p><?= nl2br(e((string) $q['answer'])) ?></p>
                        </div>
                    <?php else: ?>
                        <p class="fatwa-waiting"><?= e(tt('The administration will answer this question here.')) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="fatwa-ask" id="ask">
            <div class="fatwa-qa-head">
                <p class="fatwa-qa-kicker"><?= e(tt('Ask')) ?></p>
                <h2><?= e(tt('Ask a question on this fatwa')) ?></h2>
                <p><?= e(tt('Write your question, attach a file (image, PDF, or Word), or both. The reply will show under your question on this page.')) ?></p>
            </div>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
            <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
            <form class="form stack-form" method="post" action="<?= e(url('/fatawa/' . $fatwa['slug'])) ?>" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <div class="hp" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <div class="row-2">
                    <div class="field <?= isset($errors['name']) ? 'is-invalid' : '' ?>">
                        <label for="q-name"><?= e(tt('Name')) ?></label>
                        <input id="q-name" name="name" required maxlength="120" value="<?= e((string) ($old['name'] ?? '')) ?>" autocomplete="name">
                        <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
                    </div>
                    <div class="field <?= isset($errors['email']) ? 'is-invalid' : '' ?>">
                        <label for="q-email"><?= e(tt('Email')) ?> <span class="help">(<?= e(tt('optional')) ?>)</span></label>
                        <input id="q-email" type="email" name="email" maxlength="190" value="<?= e((string) ($old['email'] ?? '')) ?>" autocomplete="email">
                        <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="field <?= isset($errors['body']) ? 'is-invalid' : '' ?>">
                    <label for="q-body"><?= e(tt('Your question')) ?></label>
                    <textarea id="q-body" name="body" rows="5" maxlength="4000"><?= e((string) ($old['body'] ?? '')) ?></textarea>
                    <?php if (isset($errors['body'])): ?><div class="error"><?= e($errors['body']) ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="q-file"><?= e(tt('Attach a file')) ?> <span class="help">(<?= e(tt('optional')) ?>)</span></label>
                    <input id="q-file" type="file" name="attachment" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,.doc,.docx,.txt">
                    <p class="help"><?= e(tt('Image, PDF, Word, or text — up to 10 MB.')) ?></p>
                </div>
                <button class="btn btn-walnut" type="submit"><?= e(tt('Send question')) ?></button>
            </form>
        </div>

        <?php if ($more): ?>
            <div class="fatwa-more">
                <h2><?= e(tt('See more fatawa')) ?></h2>
                <ul>
                    <?php foreach ($more as $row): ?>
                        <li>
                            <a href="<?= e(url('/fatawa/' . $row['slug'])) ?>">
                                <time datetime="<?= e((string) $row['issued_on']) ?>"><?= e(date('j M Y', strtotime((string) $row['issued_on']))) ?></time>
                                <strong><?= e(ft(\App\Models\Fatwa::cardTitle($row))) ?></strong>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <p class="fatwa-back">
            <a class="btn btn-gold" href="<?= e($backUrl) ?>"><?= e(tt('Back to Fatawa')) ?></a>
            <a class="btn btn-outline" href="<?= e($backUrl) ?>"><?= e(tt('See more fatawa')) ?></a>
        </p>
    </div>
</section>

<?php
$f = $fatwa ?? [];
$questions = $questions ?? [];
?>
<h1><?= $fatwa ? 'Edit fatwa' : 'Publish a fatwa' ?></h1>
<p>Fill any language you want. Empty boxes do not appear on the website. Arabic, English, and Hindi are all optional as long as one language has both a title and the fatwa text.</p>
<form class="form stack-form" method="post" action="<?= e(url($fatwa ? '/admin/fatawa/' . $fatwa['id'] : '/admin/fatawa')) ?>">
    <?= csrf_field() ?>
    <div class="row-2">
        <div class="field">
            <label for="issued_on">Date (daily fatwa)</label>
            <input id="issued_on" type="date" name="issued_on" required value="<?= e((string) ($f['issued_on'] ?? date('Y-m-d'))) ?>">
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft"<?= selected($f['status'] ?? 'published', 'draft') ?>>Draft — not public</option>
                <option value="published"<?= selected($f['status'] ?? 'published', 'published') ?>>Published on the website</option>
            </select>
        </div>
    </div>
    <?php if ($fatwa): ?>
        <div class="field">
            <label for="slug">URL slug</label>
            <input id="slug" name="slug" value="<?= e((string) ($f['slug'] ?? '')) ?>" placeholder="2026-08-25">
            <p class="help">Public address: /fatawa/<?= e((string) ($f['slug'] ?? '')) ?></p>
        </div>
    <?php endif; ?>

    <fieldset class="fatwa-lang-block" lang="ar" dir="rtl">
        <legend>العربية — Arabic (optional)</legend>
        <div class="field">
            <label for="title_ar">العنوان</label>
            <input id="title_ar" name="title_ar" maxlength="240" value="<?= e((string) ($f['title_ar'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="body_ar">نص الفتوى</label>
            <textarea id="body_ar" name="body_ar" rows="8"><?= e((string) ($f['body_ar'] ?? '')) ?></textarea>
        </div>
    </fieldset>

    <fieldset class="fatwa-lang-block">
        <legend>English (optional)</legend>
        <div class="field">
            <label for="title_en">Title</label>
            <input id="title_en" name="title_en" maxlength="240" value="<?= e((string) ($f['title_en'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="body_en">Fatwa text</label>
            <textarea id="body_en" name="body_en" rows="8"><?= e((string) ($f['body_en'] ?? '')) ?></textarea>
        </div>
    </fieldset>

    <fieldset class="fatwa-lang-block">
        <legend>हिन्दी — Hindi (optional)</legend>
        <div class="field">
            <label for="title_hi">शीर्षक</label>
            <input id="title_hi" name="title_hi" maxlength="240" value="<?= e((string) ($f['title_hi'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="body_hi">फ़तवे का पाठ</label>
            <textarea id="body_hi" name="body_hi" rows="8"><?= e((string) ($f['body_hi'] ?? '')) ?></textarea>
        </div>
    </fieldset>

    <button class="btn btn-walnut" type="submit"><?= $fatwa ? 'Save fatwa' : 'Publish fatwa' ?></button>
</form>

<?php if ($fatwa): ?>
    <h2 id="questions">Questions on this fatwa</h2>
    <p>Answers you save here appear under that question on the public page.</p>
    <?php if (!$questions): ?>
        <p class="help">No questions yet. They arrive from the public fatwa page.</p>
    <?php endif; ?>
    <?php foreach ($questions as $q): ?>
        <article class="fatwa-admin-q" id="q-<?= (int) $q['id'] ?>">
            <header>
                <strong><?= e((string) $q['name']) ?></strong>
                <span class="help"><?= e((string) $q['created_at']) ?> · <?= e((string) $q['status']) ?><?php if (!empty($q['email'])): ?> · <?= e((string) $q['email']) ?><?php endif; ?></span>
            </header>
            <?php if (!empty($q['body'])): ?>
                <p><?= nl2br(e((string) $q['body'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($q['attachment_path'])): ?>
                <p>
                    <?php if (\App\Models\FatwaQuestion::isImage($q['attachment_mime'] ?? null)): ?>
                        <a href="<?= e(upload_url($q['attachment_path'])) ?>" target="_blank" rel="noopener">
                            <img class="fatwa-q-img" src="<?= e(upload_url($q['attachment_path'])) ?>" alt="">
                        </a>
                    <?php else: ?>
                        <a class="btn btn-outline btn-sm" href="<?= e(upload_url($q['attachment_path'])) ?>" target="_blank" rel="noopener">Open <?= e((string) ($q['attachment_name'] ?: 'attachment')) ?></a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <form class="form" method="post" action="<?= e(url('/admin/fatawa/questions/' . $q['id'] . '/answer')) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label>Answer</label>
                    <textarea name="answer" rows="4" required><?= e((string) ($q['answer'] ?? '')) ?></textarea>
                </div>
                <button class="btn btn-walnut btn-sm" type="submit"><?= !empty($q['answer']) ? 'Update answer' : 'Publish answer' ?></button>
            </form>
            <div class="dash-actions" style="margin-top:0.6rem">
                <?php if (($q['status'] ?? '') !== 'hidden'): ?>
                    <form method="post" action="<?= e(url('/admin/fatawa/questions/' . $q['id'] . '/hide')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline btn-sm" type="submit">Hide on website</button>
                    </form>
                <?php endif; ?>
                <form method="post" action="<?= e(url('/admin/fatawa/questions/' . $q['id'] . '/delete')) ?>" data-confirm="Delete this question?">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" type="submit">Delete question</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

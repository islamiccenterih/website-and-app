<?php
$copy = is_array($copy ?? null) ? $copy : [];
$rows = is_array($faithHadith ?? null) ? $faithHadith : [];
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/faith-content/daily_quran')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <h2>Page text</h2>
    <div class="field"><label>Gold tag</label><input name="copy[kicker]" value="<?= e((string) ($copy['kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Heading</label><input name="copy[title]" value="<?= e((string) ($copy['title'] ?? '')) ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="copy[lead]" rows="3"><?= e((string) ($copy['lead'] ?? '')) ?></textarea></div>
    <div class="field"><label>Ayah tag</label><input name="copy[ayah_kicker]" value="<?= e((string) ($copy['ayah_kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Hadith tag</label><input name="copy[hadith_kicker]" value="<?= e((string) ($copy['hadith_kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Notes</label><textarea name="copy[notes]" rows="2"><?= e((string) ($copy['notes'] ?? '')) ?></textarea></div>
    <h2>Hadith rotation</h2>
    <p class="help">One hadith is shown each day (India time). Edit the list below. The ayah and tafsir load live from the Qur’an text.</p>
    <?php foreach ($rows as $i => $item): ?>
        <h3>Hadith <?= (int) $i + 1 ?></h3>
        <div class="field"><label>Arabic</label><textarea name="hadith[<?= (int) $i ?>][ar]" rows="2" dir="rtl"><?= e((string) ($item['ar'] ?? '')) ?></textarea></div>
        <div class="field"><label>Meaning</label><textarea name="hadith[<?= (int) $i ?>][en]" rows="2"><?= e((string) ($item['en'] ?? '')) ?></textarea></div>
        <div class="field"><label>Source</label><input name="hadith[<?= (int) $i ?>][src]" value="<?= e((string) ($item['src'] ?? '')) ?>"></div>
    <?php endforeach; ?>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

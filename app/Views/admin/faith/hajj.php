<?php
$copy = is_array($copy ?? null) ? $copy : [];
$hajj = is_array($faithHajj ?? null) ? $faithHajj : [];
$umrah = is_array($hajj['umrah'] ?? null) ? $hajj['umrah'] : [];
$hajjSteps = is_array($hajj['hajj'] ?? null) ? $hajj['hajj'] : [];
$duas = is_array($hajj['duas'] ?? null) ? $hajj['duas'] : [];
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/faith-content/hajj_umrah')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <h2>Page text</h2>
    <div class="field"><label>Gold tag</label><input name="copy[kicker]" value="<?= e((string) ($copy['kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Heading</label><input name="copy[title]" value="<?= e((string) ($copy['title'] ?? '')) ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="copy[lead]" rows="3"><?= e((string) ($copy['lead'] ?? '')) ?></textarea></div>
    <h2>Umrah checklist</h2>
    <?php foreach ($umrah as $i => $item): ?>
        <div class="field"><label>Title</label><input name="umrah[<?= (int) $i ?>][title]" value="<?= e((string) ($item['title'] ?? '')) ?>"></div>
        <div class="field"><label>Text (English)</label><textarea name="umrah[<?= (int) $i ?>][text]" rows="3"><?= e((string) ($item['text'] ?? '')) ?></textarea></div>
        <div class="field"><label>Text (Hindi)</label><textarea name="umrah[<?= (int) $i ?>][text_hi]" rows="3"><?= e((string) ($item['text_hi'] ?? '')) ?></textarea></div>
    <?php endforeach; ?>
    <h2>Hajj days</h2>
    <?php foreach ($hajjSteps as $i => $item): ?>
        <div class="field"><label>Title</label><input name="hajj_step[<?= (int) $i ?>][title]" value="<?= e((string) ($item['title'] ?? '')) ?>"></div>
        <div class="field"><label>Text (English)</label><textarea name="hajj_step[<?= (int) $i ?>][text]" rows="3"><?= e((string) ($item['text'] ?? '')) ?></textarea></div>
        <div class="field"><label>Text (Hindi)</label><textarea name="hajj_step[<?= (int) $i ?>][text_hi]" rows="3"><?= e((string) ($item['text_hi'] ?? '')) ?></textarea></div>
    <?php endforeach; ?>
    <h2>Duas</h2>
    <?php foreach ($duas as $i => $item): ?>
        <div class="field"><label>Title</label><input name="hajj_dua[<?= (int) $i ?>][title]" value="<?= e((string) ($item['title'] ?? '')) ?>"></div>
        <div class="field"><label>Arabic</label><textarea name="hajj_dua[<?= (int) $i ?>][arabic]" rows="2" dir="rtl"><?= e((string) ($item['arabic'] ?? '')) ?></textarea></div>
        <div class="field"><label>How to read</label><textarea name="hajj_dua[<?= (int) $i ?>][translit]" rows="2"><?= e((string) ($item['translit'] ?? '')) ?></textarea></div>
        <div class="field"><label>Meaning (English)</label><textarea name="hajj_dua[<?= (int) $i ?>][meaning]" rows="2"><?= e((string) ($item['meaning'] ?? '')) ?></textarea></div>
        <div class="field"><label>Meaning (Hindi)</label><textarea name="hajj_dua[<?= (int) $i ?>][hi]" rows="2"><?= e((string) ($item['hi'] ?? '')) ?></textarea></div>
        <div class="field"><label>Meaning (Urdu)</label><textarea name="hajj_dua[<?= (int) $i ?>][ur]" rows="2" dir="rtl"><?= e((string) ($item['ur'] ?? '')) ?></textarea></div>
    <?php endforeach; ?>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

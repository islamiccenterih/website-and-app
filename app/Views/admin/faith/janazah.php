<?php
$copy = is_array($copy ?? null) ? $copy : [];
$steps = is_array($faithSteps ?? null) ? $faithSteps : [];
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/faith-content/janazah')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <h2>Page text</h2>
    <div class="field"><label>Gold tag</label><input name="copy[kicker]" value="<?= e((string) ($copy['kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Heading</label><input name="copy[title]" value="<?= e((string) ($copy['title'] ?? '')) ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="copy[lead]" rows="3"><?= e((string) ($copy['lead'] ?? '')) ?></textarea></div>
    <?php foreach ($steps as $i => $step): ?>
        <h2>Step <?= (int) $i + 1 ?></h2>
        <div class="field"><label>Title</label><input name="step[<?= (int) $i ?>][title]" value="<?= e((string) ($step['title'] ?? '')) ?>"></div>
        <div class="field"><label>Instructions (English)</label><textarea name="step[<?= (int) $i ?>][lead]" rows="4"><?= e((string) ($step['lead'] ?? '')) ?></textarea></div>
        <div class="field"><label>Instructions (Hindi)</label><textarea name="step[<?= (int) $i ?>][lead_hi]" rows="4"><?= e((string) ($step['lead_hi'] ?? '')) ?></textarea></div>
        <div class="field"><label>Dua title</label><input name="step[<?= (int) $i ?>][dua_title]" value="<?= e((string) (($step['dua']['title'] ?? ''))) ?>"></div>
        <div class="field"><label>Arabic</label><textarea name="step[<?= (int) $i ?>][arabic]" rows="2" dir="rtl"><?= e((string) (($step['dua']['arabic'] ?? ''))) ?></textarea></div>
        <div class="field"><label>How to read</label><textarea name="step[<?= (int) $i ?>][translit]" rows="2"><?= e((string) (($step['dua']['translit'] ?? ''))) ?></textarea></div>
        <div class="field"><label>Meaning (English)</label><textarea name="step[<?= (int) $i ?>][meaning]" rows="2"><?= e((string) (($step['dua']['meaning'] ?? ''))) ?></textarea></div>
        <div class="field"><label>Meaning (Hindi)</label><textarea name="step[<?= (int) $i ?>][hi]" rows="2"><?= e((string) (($step['dua']['hi'] ?? ''))) ?></textarea></div>
        <div class="field"><label>Meaning (Urdu)</label><textarea name="step[<?= (int) $i ?>][ur]" rows="2" dir="rtl"><?= e((string) (($step['dua']['ur'] ?? ''))) ?></textarea></div>
    <?php endforeach; ?>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

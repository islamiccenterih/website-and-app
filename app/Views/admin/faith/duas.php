<?php
$copy = is_array($copy ?? null) ? $copy : [];
$groups = is_array($faithGroups ?? null) ? $faithGroups : [];
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/faith-content/daily_duas')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <h2>Page text</h2>
    <div class="field"><label>Gold tag</label><input name="copy[kicker]" value="<?= e((string) ($copy['kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Heading</label><input name="copy[title]" value="<?= e((string) ($copy['title'] ?? '')) ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="copy[lead]" rows="3"><?= e((string) ($copy['lead'] ?? '')) ?></textarea></div>
    <?php foreach ($groups as $g => $group): ?>
        <h2><?= e((string) ($group['title'] ?? 'Group')) ?></h2>
        <input type="hidden" name="group[<?= (int) $g ?>][id]" value="<?= e((string) ($group['id'] ?? '')) ?>">
        <div class="field"><label>Category name</label><input name="group[<?= (int) $g ?>][title]" value="<?= e((string) ($group['title'] ?? '')) ?>"></div>
        <?php foreach (($group['items'] ?? []) as $i => $item): ?>
            <h3><?= e((string) ($item['title'] ?? 'Dua')) ?></h3>
            <div class="field"><label>Title</label><input name="group[<?= (int) $g ?>][items][<?= (int) $i ?>][title]" value="<?= e((string) ($item['title'] ?? '')) ?>"></div>
            <div class="field"><label>Arabic</label><textarea name="group[<?= (int) $g ?>][items][<?= (int) $i ?>][arabic]" rows="2" dir="rtl"><?= e((string) ($item['arabic'] ?? '')) ?></textarea></div>
            <div class="field"><label>How to read</label><textarea name="group[<?= (int) $g ?>][items][<?= (int) $i ?>][translit]" rows="2"><?= e((string) ($item['translit'] ?? '')) ?></textarea></div>
            <div class="field"><label>Meaning (English)</label><textarea name="group[<?= (int) $g ?>][items][<?= (int) $i ?>][meaning]" rows="2"><?= e((string) ($item['meaning'] ?? '')) ?></textarea></div>
            <div class="field"><label>Meaning (Hindi)</label><textarea name="group[<?= (int) $g ?>][items][<?= (int) $i ?>][hi]" rows="2"><?= e((string) ($item['hi'] ?? '')) ?></textarea></div>
            <div class="field"><label>Meaning (Urdu)</label><textarea name="group[<?= (int) $g ?>][items][<?= (int) $i ?>][ur]" rows="2" dir="rtl"><?= e((string) ($item['ur'] ?? '')) ?></textarea></div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

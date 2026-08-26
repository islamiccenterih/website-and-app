<?php
$copy = is_array($copy ?? null) ? $copy : [];
$zakat = is_array($zakat ?? null) ? $zakat : [];
$embedInPage = !empty($embedInPage);
if (!$embedInPage):
$kicker = 'Website';
$title = 'Zakat';
$tag = 'h1';
$lead = 'Nisab weights, the zakat rate, and the headings on the Zakat Calculator.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
endif;
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/zakat')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <h2>Page text</h2>
    <div class="field"><label>Gold tag</label><input name="kicker" value="<?= e((string) ($copy['kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Heading</label><input name="title" value="<?= e((string) ($copy['title'] ?? '')) ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="lead" rows="3"><?= e((string) ($copy['lead'] ?? '')) ?></textarea></div>
    <div class="field"><label>Notes heading</label><input name="notes_title" value="<?= e((string) ($copy['notes_title'] ?? '')) ?>"></div>
    <div class="field"><label>Notes under the calculator</label><textarea name="notes" rows="3"><?= e((string) ($copy['notes'] ?? '')) ?></textarea></div>

    <h2>Nisab and rate</h2>
    <div class="row-2">
        <div class="field"><label>Gold nisab (grams)</label><input name="gold_nisab_g" value="<?= e((string) ($zakat['gold_nisab_g'] ?? '87.48')) ?>"></div>
        <div class="field"><label>Silver nisab (grams)</label><input name="silver_nisab_g" value="<?= e((string) ($zakat['silver_nisab_g'] ?? '612.36')) ?>"></div>
    </div>
    <div class="row-2">
        <div class="field"><label>Zakat rate (%)</label><input name="zakat_rate" value="<?= e((string) ($zakat['rate'] ?? '2.5')) ?>"></div>
        <div class="field">
            <label>Which nisab to use</label>
            <select name="nisab_method">
                <option value="lower"<?= selected($zakat['nisab_method'] ?? 'lower', 'lower') ?>>Lower of gold or silver (usual in India)</option>
                <option value="gold"<?= selected($zakat['nisab_method'] ?? '', 'gold') ?>>Gold nisab only</option>
                <option value="silver"<?= selected($zakat['nisab_method'] ?? '', 'silver') ?>>Silver nisab only</option>
            </select>
        </div>
    </div>
    <div class="field"><label>Internal note (optional)</label><textarea name="zakat_notes" rows="2"><?= e((string) ($zakat['notes'] ?? '')) ?></textarea></div>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

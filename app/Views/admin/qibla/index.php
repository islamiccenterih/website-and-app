<?php
$copy = is_array($copy ?? null) ? $copy : [];
$embedInPage = !empty($embedInPage);
if (!$embedInPage):
$kicker = 'Website';
$title = 'Qibla';
$tag = 'h1';
$lead = 'Headings and the fallback location for the Qibla Direction page.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
endif;
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/qibla')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <h2>Page text</h2>
    <div class="field"><label>Gold tag</label><input name="kicker" value="<?= e((string) ($copy['kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Heading</label><input name="title" value="<?= e((string) ($copy['title'] ?? '')) ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="lead" rows="3"><?= e((string) ($copy['lead'] ?? '')) ?></textarea></div>
    <div class="field"><label>Help under the compass</label><textarea name="help" rows="3"><?= e((string) ($copy['help'] ?? '')) ?></textarea></div>

    <h2>Fallback location</h2>
    <div class="row-2">
        <div class="field"><label>Latitude</label><input name="location_lat" value="<?= e((string) $lat) ?>"></div>
        <div class="field"><label>Longitude</label><input name="location_lng" value="<?= e((string) $lng) ?>"></div>
    </div>
    <div class="field"><label>Place label</label><input name="location_label" value="<?= e((string) $label) ?>"></div>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

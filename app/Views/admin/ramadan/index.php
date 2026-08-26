<?php
$copy = is_array($copy ?? null) ? $copy : [];
$duas = is_array($duas ?? null) ? $duas : [];
$embedInPage = !empty($embedInPage);
if (!$embedInPage):
$kicker = 'Website';
$title = 'Ramadan';
$tag = 'h1';
$lead = 'Default city, duas, and the headings on the Ramadan Mode page.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
endif;
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/ramadan')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <h2>Page text</h2>
    <div class="field"><label>Gold tag</label><input name="kicker" value="<?= e((string) ($copy['kicker'] ?? '')) ?>"></div>
    <div class="field"><label>Heading</label><input name="title" value="<?= e((string) ($copy['title'] ?? '')) ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="lead" rows="3"><?= e((string) ($copy['lead'] ?? '')) ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>Roza calendar tag</label><input name="calendar_kicker" value="<?= e((string) ($copy['calendar_kicker'] ?? '')) ?>"></div>
        <div class="field"><label>Roza calendar heading</label><input name="calendar_title" value="<?= e((string) ($copy['calendar_title'] ?? '')) ?>"></div>
    </div>
    <div class="field"><label>Roza calendar introduction</label><textarea name="calendar_lead" rows="2"><?= e((string) ($copy['calendar_lead'] ?? '')) ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>Duas tag</label><input name="duas_kicker" value="<?= e((string) ($copy['duas_kicker'] ?? '')) ?>"></div>
        <div class="field"><label>Duas heading</label><input name="duas_title" value="<?= e((string) ($copy['duas_title'] ?? '')) ?>"></div>
    </div>
    <div class="field"><label>Duas introduction</label><textarea name="duas_lead" rows="2"><?= e((string) ($copy['duas_lead'] ?? '')) ?></textarea></div>

    <h2>Default city</h2>
    <div class="row-2">
        <div class="field"><label>City</label><input name="ramadan_city" value="<?= e((string) $ramadan_city) ?>"></div>
        <div class="field"><label>State</label><input name="ramadan_state" value="<?= e((string) $ramadan_state) ?>"></div>
    </div>

    <h2>Ramadan duas</h2>
    <?php foreach ($duas as $i => $dua): ?>
        <h3><?= e((string) $dua['title']) ?></h3>
        <div class="field"><label>Title</label><input name="dua[<?= (int) $i ?>][title]" value="<?= e((string) $dua['title']) ?>"></div>
        <div class="field"><label>Arabic</label><textarea name="dua[<?= (int) $i ?>][arabic]" rows="2" dir="rtl"><?= e((string) $dua['arabic']) ?></textarea></div>
        <div class="field"><label>Transliteration</label><textarea name="dua[<?= (int) $i ?>][translit]" rows="2"><?= e((string) $dua['translit']) ?></textarea></div>
        <div class="field"><label>Meaning</label><textarea name="dua[<?= (int) $i ?>][meaning]" rows="2"><?= e((string) $dua['meaning']) ?></textarea></div>
    <?php endforeach; ?>

    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

<?php
$s = $sections;
$keys = [
    'page_hero' => 'Page top (About Us banner)',
    'foundation' => 'Foundation / establishment',
    'history' => 'Our Journey',
    'mission' => 'Mission',
    'vision' => 'Vision',
    'who_we_are' => 'Our Approach',
];
$foundationExtra = json_decode((string) ($s['foundation']['extra_json'] ?? ''), true) ?: [];
$historyExtra = json_decode((string) ($s['history']['extra_json'] ?? ''), true) ?: [];
$timeline = is_array($historyExtra['timeline'] ?? null) ? $historyExtra['timeline'] : [['year'=>'','title'=>'','text'=>'']];
$embedInPage = !empty($embedInPage);
if (!$embedInPage):
$kicker = 'About Us';
$title = 'About Us content';
$tag = 'h1';
$lead = 'Each About section has its own gold tag, heading, and text.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
endif;
?>
<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/about')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <?php foreach ($keys as $key => $label):
        $row = $s[$key] ?? [];
        $extra = json_decode((string) ($row['extra_json'] ?? ''), true) ?: [];
        ?>
        <h2><?= e($label) ?></h2>
        <div class="field"><label>Gold tag</label><input name="kicker[<?= e($key) ?>]" value="<?= e((string) ($extra['kicker'] ?? '')) ?>"></div>
        <div class="field"><label>Heading</label><input name="title[<?= e($key) ?>]" value="<?= e($row['title'] ?? '') ?>"></div>
        <div class="field"><label><?= $key === 'page_hero' ? 'Introduction' : 'Content' ?></label>
            <textarea name="content[<?= e($key) ?>]" rows="<?= $key === 'page_hero' ? '3' : '5' ?>"><?= e($row['content'] ?? '') ?></textarea>
        </div>
        <div class="field">
            <label>Image</label>
            <div class="image-field">
                <div class="image-preview">
                    <?php if (!empty($row['image'])): ?>
                        <img src="<?= e(upload_url($row['image'])) ?>" alt="">
                    <?php else: ?>
                        No picture yet
                    <?php endif; ?>
                </div>
                <div>
                    <input type="file" name="image_<?= e($key) ?>" accept="image/jpeg,image/png,image/webp,image/gif">
                    <p class="help">JPG, PNG or WebP. Leave empty to keep the current picture.</p>
                </div>
            </div>
        </div>
        <?php if ($key === 'foundation'): ?>
            <div class="row-2">
                <div class="field"><label>Established</label><input name="established" value="<?= e($foundationExtra['established'] ?? '') ?>"></div>
                <div class="field"><label>Location</label><input name="location" value="<?= e($foundationExtra['location'] ?? '') ?>"></div>
            </div>
        <?php endif; ?>
        <?php if ($key === 'history'): ?>
            <p>Journey milestones</p>
            <?php foreach ($timeline as $item): ?>
                <div class="row-2">
                    <div class="field"><label>Year</label><input name="timeline_year[]" value="<?= e($item['year'] ?? '') ?>"></div>
                    <div class="field"><label>Title</label><input name="timeline_title[]" value="<?= e($item['title'] ?? '') ?>"></div>
                </div>
                <div class="field"><label>Text</label><input name="timeline_text[]" value="<?= e($item['text'] ?? '') ?>"></div>
            <?php endforeach; ?>
            <div class="row-2">
                <div class="field"><label>Year</label><input name="timeline_year[]" placeholder="Add another"></div>
                <div class="field"><label>Title</label><input name="timeline_title[]"></div>
            </div>
            <div class="field"><label>Text</label><input name="timeline_text[]"></div>
        <?php endif; ?>
    <?php endforeach; ?>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>
<p class="help">Coordinator names, photos, and details are edited under <a href="<?= e(url('/admin/coordinators')) ?>">Coordinator Info</a>. Changes there appear on Home and About Us.</p>

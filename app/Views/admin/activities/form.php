<?php
$a = $activity ?? [];
$sections = $sections ?? [];
$sectionCounts = $sectionCounts ?? [];
$selectedSection = (string) ($a['section_id'] ?? ($prefillSection ?? ''));
$selectedName = '';
foreach ($sections as $section) {
    if ((string) $section['id'] === $selectedSection) {
        $selectedName = (string) $section['name'];
        break;
    }
}
?>
<h1><?= $activity ? 'Edit activity' : 'Add an activity' ?></h1>
<?php if ($selectedName !== ''): ?>
    <p class="activity-form-banner">This activity belongs to the heading <strong><?= e($selectedName) ?></strong>. Change the dropdown below only if it should sit under a different group.</p>
<?php else: ?>
    <p class="help">Pick the heading first. The activity will show on the public page under that heading only.</p>
<?php endif; ?>
<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url($activity ? '/admin/activities/' . $activity['id'] : '/admin/activities')) ?>">
    <?= csrf_field() ?>
    <div class="field">
        <label>Show this activity under this heading</label>
        <select name="section_id" required>
            <option value="">Choose a heading</option>
            <?php foreach ($sections as $section):
                $n = (int) ($sectionCounts[(int) $section['id']] ?? 0);
                $statusNote = ($section['status'] ?? '') === 'published' ? 'published heading' : 'draft heading';
                $countNote = $n === 1 ? '1 activity already here' : $n . ' activities already here';
                ?>
                <option value="<?= e((string) $section['id']) ?>"<?= selected($selectedSection, (string) $section['id']) ?>><?= e((string) $section['name']) ?> — <?= e($countNote) ?> (<?= e($statusNote) ?>)</option>
            <?php endforeach; ?>
        </select>
        <p class="help">Headings are the group titles on Social Activities. This dropdown is how you choose which group the activity belongs to.</p>
    </div>
    <div class="row-2">
        <div class="field"><label>Title</label><input name="title" required dir="auto" value="<?= e($a['title'] ?? '') ?>"></div>
        <div class="field"><label>Slug</label><input name="slug" value="<?= e($a['slug'] ?? '') ?>"></div>
    </div>
    <div class="field"><label>Short description</label><textarea name="short_description" rows="3" required dir="auto"><?= e($a['short_description'] ?? '') ?></textarea></div>
    <div class="field"><label>Full description</label><textarea name="full_description" rows="8" dir="auto"><?= e($a['full_description'] ?? '') ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>Date</label><input type="date" name="event_date" value="<?= e($a['event_date'] ?? '') ?>"></div>
        <div class="field"><label>Year / Hijri year</label><input name="event_year" value="<?= e($a['event_year'] ?? '') ?>"></div>
    </div>
    <div class="row-2">
        <div class="field"><label>Status</label>
            <select name="status">
                <option value="draft"<?= selected($a['status'] ?? 'draft', 'draft') ?>>Draft</option>
                <option value="published"<?= selected($a['status'] ?? '', 'published') ?>>Published</option>
            </select>
        </div>
        <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="<?= e((string) ($a['sort_order'] ?? '0')) ?>"></div>
    </div>
    <div class="field"><label><input type="checkbox" name="featured" value="1"<?= checked($a['featured'] ?? '0', '1') ?>> Featured on Home</label></div>
    <div class="field">
        <label>Main image</label>
        <?php if (!empty($a['main_image'])): ?><img class="thumb" src="<?= e(upload_url($a['main_image'])) ?>" alt=""><?php endif; ?>
        <input type="file" name="main_image" accept="image/jpeg,image/png,image/webp,image/gif">
        <p class="help">Upload a photograph to replace the placeholder image.</p>
    </div>
    <div class="field"><label>Additional images</label><input type="file" name="extra_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple></div>
    <button class="btn btn-walnut" type="submit">Save activity</button>
    <a class="btn btn-outline" href="<?= e(url('/admin/activities' . ($selectedSection !== '' ? '#section-' . $selectedSection : ''))) ?>">Back to headings</a>
</form>
<?php if (!empty($images)): ?>
    <h2 style="margin-top:2rem">Uploaded images</h2>
    <?php foreach ($images as $image): ?>
        <div style="display:inline-block;margin:0 1rem 1rem 0">
            <img class="thumb" src="<?= e(upload_url($image['image_path'])) ?>" alt="">
            <form method="post" action="<?= e(url('/admin/activities/' . $activity['id'] . '/images/' . $image['id'] . '/delete')) ?>" data-confirm="Remove this image?">
                <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Remove</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

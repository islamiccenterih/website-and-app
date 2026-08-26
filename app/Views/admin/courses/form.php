<?php $c = $course ?? []; ?>
<h1><?= $course ? 'Edit course' : 'Create course' ?></h1>
<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url($course ? '/admin/courses/' . $course['id'] : '/admin/courses')) ?>">
    <?= csrf_field() ?>
    <div class="row-2">
        <div class="field"><label>Title</label><input name="title" required dir="auto" value="<?= e($c['title'] ?? '') ?>"></div>
        <div class="field"><label>Slug</label><input name="slug" value="<?= e($c['slug'] ?? '') ?>" placeholder="auto from title"></div>
    </div>
    <div class="field"><label>Short description</label><textarea name="short_description" rows="3" required dir="auto"><?= e($c['short_description'] ?? '') ?></textarea></div>
    <div class="field"><label>Full description</label><textarea name="full_description" rows="8" dir="auto"><?= e($c['full_description'] ?? '') ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>Fees</label><input name="fees" value="<?= e($c['fees'] ?? '') ?>" placeholder="₹XXXX"></div>
        <div class="field"><label>Duration</label><input name="duration" value="<?= e($c['duration'] ?? '') ?>" placeholder="XX months"></div>
    </div>
    <div class="row-2">
        <div class="field"><label>Mode</label>
            <select name="mode">
                <?php foreach (['offline','online','hybrid'] as $m): ?>
                    <option value="<?= $m ?>"<?= selected($c['mode'] ?? 'offline', $m) ?>><?= ucfirst($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Status</label>
            <select name="status">
                <option value="draft"<?= selected($c['status'] ?? 'draft', 'draft') ?>>Draft</option>
                <option value="published"<?= selected($c['status'] ?? '', 'published') ?>>Published</option>
            </select>
        </div>
    </div>
    <div class="row-2">
        <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="<?= e((string) ($c['sort_order'] ?? '0')) ?>"></div>
        <div class="field"><label><input type="checkbox" name="featured" value="1"<?= checked($c['featured'] ?? '0', '1') ?>> Featured on Home</label></div>
    </div>
    <div class="field"><label>Additional information</label><textarea name="additional_info" rows="3"><?= e($c['additional_info'] ?? '') ?></textarea></div>
    <div class="field">
        <label>Main image</label>
        <div class="image-field">
            <div class="image-preview">
                <?php if (!empty($c['main_image'])): ?>
                    <img src="<?= e(upload_url($c['main_image'])) ?>" alt="">
                <?php else: ?>
                    <?= e(tt('No picture yet')) ?>
                <?php endif; ?>
            </div>
            <div>
                <input type="file" name="main_image" accept="image/jpeg,image/png,image/webp,image/gif">
                <p class="help"><?= e(tt('JPG, PNG or WebP. This is the picture visitors see on the public page.')) ?> <?= e(tt('Leave empty to keep the current picture.')) ?></p>
            </div>
        </div>
    </div>
    <div class="field">
        <label>Additional images</label>
        <input type="file" name="extra_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
    </div>
    <button class="btn btn-walnut" type="submit">Save course</button>
    <a class="btn btn-outline" href="<?= e(url('/admin/courses')) ?>">Back</a>
</form>
<?php if ($images): ?>
    <h2 style="margin-top:2rem">Uploaded images</h2>
    <div class="gallery-grid">
        <?php foreach ($images as $image): ?>
            <div>
                <img src="<?= e(upload_url($image['image_path'])) ?>" alt="">
                <form method="post" action="<?= e(url('/admin/courses/' . $course['id'] . '/images/' . $image['id'] . '/delete')) ?>" data-confirm="Remove this image?">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" type="submit">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

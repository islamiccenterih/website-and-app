<?php
$kicker = 'Photographs';
$title = 'Gallery';
$tag = 'h1';
$lead = 'Upload images here. They appear on the public Gallery page. There are no albums — one list of photographs.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
$cmsKey = 'gallery';
require APP_PATH . '/Views/admin/partials/cms-bar.php';
?>

<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/gallery/images')) ?>">
    <?= csrf_field() ?>
    <div class="field">
        <label for="gallery-images">Images</label>
        <input id="gallery-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
        <p class="help">JPG, PNG, WebP, or GIF. Up to 10 MB each. You can select several files at once.</p>
    </div>
    <div class="row-2">
        <div class="field"><label>Title (optional)</label><input name="title" placeholder="Shown under the image in admin"></div>
        <div class="field"><label>Alt text (optional)</label><input name="alt_text" placeholder="For accessibility"></div>
    </div>
    <div class="field"><label><input type="checkbox" name="featured" value="1"> Show on the Home page gallery</label></div>
    <button class="btn btn-walnut" type="submit">Upload images</button>
</form>

<?php if (!$images): ?>
    <div class="empty-state">
        <h3>No images yet</h3>
        <p>Upload photographs above. They will list here and on the public Gallery page.</p>
    </div>
<?php else: ?>
    <div class="admin-gallery-list">
        <?php foreach ($images as $image): ?>
            <article class="admin-gallery-card">
                <img src="<?= e(upload_url($image['image_path'])) ?>" alt="<?= e($image['alt_text'] ?: $image['title'] ?: 'Gallery image') ?>">
                <form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/gallery/images/' . $image['id'])) ?>">
                    <?= csrf_field() ?>
                    <div class="field"><label>Title</label><input name="title" value="<?= e((string) $image['title']) ?>"></div>
                    <div class="field"><label>Alt text</label><input name="alt_text" value="<?= e((string) $image['alt_text']) ?>"></div>
                    <div class="row-2">
                        <div class="field"><label>Sort</label><input type="number" name="sort_order" value="<?= e((string) $image['sort_order']) ?>"></div>
                        <div class="field"><label>Status</label>
                            <select name="status">
                                <option value="published"<?= selected($image['status'], 'published') ?>>Published</option>
                                <option value="draft"<?= selected($image['status'], 'draft') ?>>Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="field"><label><input type="checkbox" name="featured" value="1"<?= checked($image['featured'], '1') ?>> Home page</label></div>
                    <div class="field"><label>Replace image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></div>
                    <div class="dash-actions">
                        <button class="btn btn-walnut btn-sm" type="submit">Save</button>
                    </div>
                </form>
                <form method="post" action="<?= e(url('/admin/gallery/images/' . $image['id'] . '/delete')) ?>" data-confirm="Delete this image?">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $cmsKey = 'home'; require APP_PATH . '/Views/admin/partials/cms-bar.php'; ?>
<h1>Center programs</h1>
<p>These cards appear in the Home page activities preview.</p>
<form class="form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/programs')) ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Title</label><input name="title" required></div>
    <div class="field"><label>Short description</label><textarea name="short_description" rows="2"></textarea></div>
    <div class="field"><label>Link URL</label><input name="link_url" placeholder="/courses"></div>
    <div class="field"><label>Image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></div>
    <button class="btn btn-walnut" type="submit">Add program</button>
</form>
<?php foreach ($programs as $program): ?>
    <form class="form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/programs/' . $program['id'])) ?>" style="margin-top:1rem">
        <?= csrf_field() ?>
        <img class="thumb" src="<?= e(upload_url($program['image'])) ?>" alt="">
        <div class="field"><label>Title</label><input name="title" required dir="auto" value="<?= e($program['title']) ?>"></div>
        <div class="field"><label>Short description</label><textarea name="short_description" rows="2" dir="auto"><?= e($program['short_description']) ?></textarea></div>
        <div class="field"><label>Link URL</label><input name="link_url" value="<?= e($program['link_url']) ?>"></div>
        <div class="field"><label>Replace image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></div>
        <div class="field"><label>Status</label>
            <select name="status">
                <option value="published"<?= selected($program['status'], 'published') ?>>Published</option>
                <option value="draft"<?= selected($program['status'], 'draft') ?>>Draft</option>
            </select>
        </div>
        <button class="btn btn-walnut btn-sm" type="submit">Save</button>
    </form>
    <form method="post" action="<?= e(url('/admin/programs/' . $program['id'] . '/delete')) ?>" data-confirm="Delete this program?">
        <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete</button>
    </form>
<?php endforeach; ?>

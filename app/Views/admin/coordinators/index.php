<?php
$intro = $intro ?? [];
$introExtra = $introExtra ?? [];
$coordinators = $coordinators ?? [];
$kicker = 'Website';
$title = 'Coordinator Info';
$tag = 'h1';
$lead = 'These coordinators appear on the public Home page and the About Us page. Save here and both places update together.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>

<section class="panel-card coord-admin-intro">
    <h2>Section heading</h2>
    <p class="help">This gold tag, title, and short line sit above the coordinator cards on Home and About Us.</p>
    <form class="form stack-form" method="post" action="<?= e(url('/admin/coordinators')) ?>">
        <?= csrf_field() ?>
        <div class="row-2">
            <div class="field">
                <label for="coord-kicker">Gold tag</label>
                <input id="coord-kicker" name="kicker" value="<?= e((string) ($introExtra['kicker'] ?? 'Leadership')) ?>">
            </div>
            <div class="field">
                <label for="coord-title">Heading</label>
                <input id="coord-title" name="title" value="<?= e((string) ($intro['title'] ?? 'Coordinators')) ?>">
            </div>
        </div>
        <div class="field">
            <label for="coord-lead">Short introduction</label>
            <textarea id="coord-lead" name="content" rows="2"><?= e((string) ($intro['content'] ?? '')) ?></textarea>
        </div>
        <button class="btn btn-walnut" type="submit">Save heading</button>
    </form>
</section>

<section class="panel-card coord-admin-add">
    <h2>Add a coordinator</h2>
    <form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/coordinators/add')) ?>">
        <?= csrf_field() ?>
        <div class="row-2">
            <div class="field">
                <label for="new-name">Name</label>
                <input id="new-name" name="name" required maxlength="180" placeholder="Full name">
            </div>
            <div class="field">
                <label for="new-role">Role / designation</label>
                <input id="new-role" name="designation" maxlength="255" placeholder="Coordinator, Islamic Center">
            </div>
        </div>
        <div class="field">
            <label for="new-points">Details</label>
            <textarea id="new-points" name="highlights" rows="8" placeholder="One point per line"></textarea>
            <p class="help">Write each achievement or role on its own line. Those lines become the bullet list on the website.</p>
        </div>
        <div class="row-2">
            <div class="field">
                <label for="new-order">Order</label>
                <input id="new-order" name="sort_order" type="number" value="<?= count($coordinators) + 1 ?>">
                <p class="help">Lower numbers appear first.</p>
            </div>
            <div class="field">
                <label for="new-status">Status</label>
                <select id="new-status" name="status">
                    <option value="published">Published — show on the website</option>
                    <option value="draft">Draft — hidden until published</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label for="new-photo">Photo</label>
            <input id="new-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
            <p class="help">Portrait photo, <strong>800 × 1000 pixels</strong> (4:5). JPG, PNG or WebP, under 2 MB. Face centered. You can add the photo later — initials show until then.</p>
        </div>
        <button class="btn btn-walnut" type="submit">Add coordinator</button>
    </form>
</section>

<?php if (!$coordinators): ?>
    <p class="help">No coordinators yet. Add the first person above.</p>
<?php endif; ?>

<?php foreach ($coordinators as $person): ?>
    <section class="panel-card coord-admin-card<?= ($person['status'] ?? '') === 'draft' ? ' is-draft' : '' ?>">
        <div class="coord-admin-photo">
            <?php if (!empty($person['photo'])): ?>
                <img src="<?= e(upload_url($person['photo'])) ?>" alt="">
            <?php else: ?>
                <span class="coord-admin-initials" aria-hidden="true"><?= e($person['initials'] ?? 'IC') ?></span>
            <?php endif; ?>
            <p class="help"><?= ($person['status'] ?? '') === 'published' ? 'On the website' : 'Draft — hidden' ?></p>
        </div>
        <form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/coordinators/' . $person['id'])) ?>">
            <?= csrf_field() ?>
            <div class="row-2">
                <div class="field">
                    <label>Name</label>
                    <input name="name" required maxlength="180" value="<?= e((string) $person['name']) ?>">
                </div>
                <div class="field">
                    <label>Role / designation</label>
                    <input name="designation" maxlength="255" value="<?= e((string) ($person['designation'] ?? '')) ?>">
                </div>
            </div>
            <div class="field">
                <label>Details (one point per line)</label>
                <textarea name="highlights" rows="10"><?= e((string) ($person['highlights_text'] ?? '')) ?></textarea>
            </div>
            <div class="row-2">
                <div class="field">
                    <label>Order</label>
                    <input name="sort_order" type="number" value="<?= e((string) ($person['sort_order'] ?? 0)) ?>">
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="published"<?= selected($person['status'], 'published') ?>>Published — show on the website</option>
                        <option value="draft"<?= selected($person['status'], 'draft') ?>>Draft — hidden</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Photo</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                <p class="help">Replace with a portrait <strong>800 × 1000 px</strong> (4:5), JPG / PNG / WebP, under 2 MB. Leave empty to keep the current picture.</p>
                <?php if (!empty($person['photo'])): ?>
                    <label class="coord-admin-remove"><input type="checkbox" name="remove_photo" value="1"> Remove current photo</label>
                <?php endif; ?>
            </div>
            <p class="dash-actions">
                <button class="btn btn-walnut" type="submit">Save changes</button>
            </p>
        </form>
        <form class="coord-admin-delete" method="post" action="<?= e(url('/admin/coordinators/' . $person['id'] . '/delete')) ?>" data-confirm="Remove this coordinator from Home and About Us?">
            <?= csrf_field() ?>
            <button class="btn btn-outline" type="submit">Delete</button>
        </form>
    </section>
<?php endforeach; ?>

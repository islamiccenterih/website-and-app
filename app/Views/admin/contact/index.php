<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/contact')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>
    <?php
    $fields = is_array($page['fields'] ?? null) ? $page['fields'] : [];
    $copyHeading = 'Page text';
    require APP_PATH . '/Views/admin/partials/page-copy-fields.php';
    ?>
    <h2>Address and photograph</h2>
    <div class="field"><label>Address</label><textarea name="contact_address" rows="3"><?= e((string) setting('contact_address')) ?></textarea></div>
    <div class="field"><label>Email</label><input type="email" name="contact_email" value="<?= e((string) setting('contact_email')) ?>"></div>
    <div class="field"><label>Phone</label><input name="contact_phone" value="<?= e((string) setting('contact_phone')) ?>"></div>
    <div class="field"><label>Hours</label><input name="contact_hours" value="<?= e((string) setting('contact_hours')) ?>"></div>
    <div class="field">
        <label>Contact photograph</label>
        <div class="image-field">
            <div class="image-preview">
                <?php if (setting('contact_image')): ?>
                    <img src="<?= e(upload_url((string) setting('contact_image'))) ?>" alt="">
                <?php else: ?>
                    No picture yet
                <?php endif; ?>
            </div>
            <div>
                <input type="file" name="contact_image" accept="image/jpeg,image/png,image/webp,image/gif">
                <p class="help">Shown beside the contact form. Leave empty to keep the current picture.</p>
            </div>
        </div>
    </div>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

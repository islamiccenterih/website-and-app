<?php $m = $month ?? []; ?>
<h1><?= $month ? 'Edit calendar month' : 'Create calendar month' ?></h1>
<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url($month ? '/admin/calendar/' . $month['id'] : '/admin/calendar')) ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Title</label><input name="title" required value="<?= e($m['title'] ?? '') ?>"></div>
    <div class="row-2">
        <div class="field"><label>Hijri month</label><input name="hijri_month" value="<?= e($m['hijri_month'] ?? '') ?>"></div>
        <div class="field"><label>Hijri year</label><input name="hijri_year" value="<?= e($m['hijri_year'] ?? '') ?>"></div>
    </div>
    <div class="field"><label>Gregorian label</label><input name="gregorian_label" value="<?= e($m['gregorian_label'] ?? '') ?>"></div>
    <div class="field"><label>Notes</label><textarea name="notes" rows="4"><?= e($m['notes'] ?? '') ?></textarea></div>
    <div class="field">
        <label>Calendar image</label>
        <?php if (!empty($m['image_path'])): ?><img src="<?= e(upload_url($m['image_path'])) ?>" alt="" style="max-width:280px"><?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="field"><label>Extracted text (review before publishing)</label>
        <textarea name="ocr_raw_text" rows="6"><?= e($m['ocr_raw_text'] ?? '') ?></textarea>
        <p class="help"><?= e($m['ocr_note'] ?? 'OCR is optional and never published automatically.') ?></p>
    </div>
    <div class="field"><label><input type="checkbox" name="run_ocr" value="1"> Attempt text extraction on save (if Tesseract is installed). You must still review the text.</label></div>
    <div class="row-2">
        <div class="field"><label>Status</label>
            <select name="status">
                <option value="draft"<?= selected($m['status'] ?? 'draft', 'draft') ?>>Draft — not public</option>
                <option value="published"<?= selected($m['status'] ?? '', 'published') ?>>Published</option>
            </select>
        </div>
        <div class="field"><label>Sort</label><input type="number" name="sort_order" value="<?= e((string) ($m['sort_order'] ?? '0')) ?>"></div>
    </div>
    <div class="field"><label><input type="checkbox" name="is_current" value="1"<?= checked($m['is_current'] ?? '0', '1') ?>> Mark as current month</label></div>
    <button class="btn btn-walnut" type="submit">Save calendar</button>
</form>

<?php if ($month): ?>
    <h2>Important dates</h2>
    <form class="form" method="post" action="<?= e(url('/admin/calendar/' . $month['id'] . '/events')) ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Title</label><input name="title" required></div>
        <div class="row-2">
            <div class="field"><label>Hijri date</label><input name="hijri_date"></div>
            <div class="field"><label>Gregorian date</label><input name="gregorian_date"></div>
        </div>
        <div class="field"><label>Description</label><input name="description"></div>
        <div class="field"><label><input type="checkbox" name="is_important" value="1"> Important</label></div>
        <button class="btn btn-walnut btn-sm" type="submit">Add date</button>
    </form>
    <ul>
        <?php foreach ($events as $event): ?>
            <li>
                <strong><?= e($event['title']) ?></strong>
                — <?= e($event['hijri_date']) ?> / <?= e($event['gregorian_date']) ?>
                <form method="post" action="<?= e(url('/admin/calendar/events/' . $event['id'] . '/delete')) ?>" style="display:inline">
                    <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Remove</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

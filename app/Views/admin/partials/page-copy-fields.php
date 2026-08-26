<?php
$copy = is_array($copy ?? null) ? $copy : [];
$fields = is_array($fields ?? null) ? $fields : [];
$fieldLabels = is_array($fieldLabels ?? null) ? $fieldLabels : \App\Core\SitePages::fieldLabels();
$copyHeading = (string) ($copyHeading ?? 'Page text');
$long = ['lead', 'week_lead', 'sighting_text', 'help', 'notes', 'calendar_lead', 'duas_lead', 'detail_lead'];
if ($fields === []) {
    return;
}
?>
<?php if ($copyHeading !== ''): ?>
    <h2><?= e($copyHeading) ?></h2>
<?php endif; ?>
<?php foreach ($fields as $field): ?>
    <div class="field">
        <label><?= e((string) ($fieldLabels[$field] ?? $field)) ?></label>
        <?php if (in_array($field, $long, true)): ?>
            <textarea name="copy[<?= e((string) $field) ?>]" rows="3" dir="auto"><?= e((string) ($copy[$field] ?? '')) ?></textarea>
        <?php else: ?>
            <input name="copy[<?= e((string) $field) ?>]" dir="auto" value="<?= e((string) ($copy[$field] ?? '')) ?>">
        <?php endif; ?>
    </div>
<?php endforeach; ?>

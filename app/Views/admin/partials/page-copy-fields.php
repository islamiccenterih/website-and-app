<?php
$copy = is_array($copy ?? null) ? $copy : [];
$fields = is_array($fields ?? null) ? $fields : [];
$fieldLabels = is_array($fieldLabels ?? null) ? $fieldLabels : \App\Core\SitePages::fieldLabels();
$copyHeading = (string) ($copyHeading ?? 'Page text');
$long = ['lead', 'week_lead', 'sighting_text', 'help', 'notes', 'calendar_lead', 'duas_lead', 'detail_lead', 'body'];
$fieldHints = [
    'body' => 'Each section heading starts with ## (example: ## 1. Who we are). Leave a blank line between paragraphs. This English text is what visitors read on the website.',
];
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
            <textarea name="copy[<?= e((string) $field) ?>]" rows="<?= $field === 'body' ? '22' : '3' ?>"<?= $field === 'body' ? ' class="legal-body"' : '' ?> dir="auto"><?= e((string) ($copy[$field] ?? '')) ?></textarea>
            <?php if (!empty($fieldHints[$field])): ?>
                <p class="help"><?= e((string) $fieldHints[$field]) ?></p>
            <?php endif; ?>
        <?php else: ?>
            <input name="copy[<?= e((string) $field) ?>]" dir="auto" value="<?= e((string) ($copy[$field] ?? '')) ?>">
        <?php endif; ?>
    </div>
<?php endforeach; ?>

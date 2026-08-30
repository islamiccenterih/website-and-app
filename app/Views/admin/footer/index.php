<?php
$kicker = 'Website';
$title = 'Header & Footer';
$tag = 'h1';
$lead = 'These fields appear on every public page. Save here and the website header and footer update immediately.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';

/**
 * @param array{label?:string,url?:string,hidden?:bool} $link
 */
$visibilityField = static function (string $name, array $link, int $i, string $kind): void {
    $hidden = !empty($link['hidden']);
    ?>
    <div class="field">
        <label><?= e($kind) ?> <?= $i + 1 ?> visibility</label>
        <select name="<?= e($name) ?>">
            <option value="0"<?= $hidden ? '' : ' selected' ?>>Show</option>
            <option value="1"<?= $hidden ? ' selected' : '' ?>>Hide</option>
        </select>
    </div>
    <?php
};
$legalHeadingValue = trim((string) setting('footer_legal_heading', ''));
if ($legalHeadingValue === '') {
    $legalHeadingValue = trim((string) setting('footer_explore_heading', 'Legal'));
}
if ($legalHeadingValue === '' || strcasecmp($legalHeadingValue, 'Explore') === 0) {
    $legalHeadingValue = 'Legal';
}
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/footer')) ?>">
    <?= csrf_field() ?>

    <h2>Header menu</h2>
    <div class="field"><label>Student login button</label><input name="header_login_label" value="<?= e((string) setting('header_login_label', 'Student Login')) ?>"></div>
    <p class="help">Each row can sit in the top bar or the More dropdown. Hide removes it from the public menu. Empty rows are ignored — use them to add a new page or an external link.</p>
    <?php foreach ($nav as $i => $link): ?>
        <?php $group = nav_item_group($link); ?>
        <div class="row-nav<?= !empty($link['hidden']) ? ' is-nav-hidden' : '' ?>" data-nav-row>
            <div class="field"><label>Menu <?= $i + 1 ?> label</label><input name="nav_label[]" dir="auto" value="<?= e((string) ($link['label'] ?? '')) ?>" placeholder="About Us"></div>
            <div class="field"><label>Menu <?= $i + 1 ?> URL</label><input name="nav_url[]" value="<?= e((string) ($link['url'] ?? '')) ?>" placeholder="/about-us"></div>
            <div class="field">
                <label>Menu <?= $i + 1 ?> group</label>
                <select name="nav_group[]">
                    <option value="primary"<?= selected($group, 'primary') ?>><?= e(header_group_label('primary')) ?></option>
                    <option value="more"<?= selected($group, 'more') ?>><?= e(header_group_label('more')) ?></option>
                </select>
            </div>
            <?php $visibilityField('nav_hidden[]', $link, $i, 'Menu'); ?>
        </div>
    <?php endforeach; ?>
    <p class="help">Rename website pages under <a href="<?= e(url('/admin/pages')) ?>">Pages</a>. That screen also has this group list on every page.</p>

    <h2>Footer — about column</h2>
    <div class="field"><label>Heading</label><input name="footer_brand_title" dir="auto" value="<?= e(ftc((string) setting('footer_brand_title', site_name()))) ?>"></div>
    <div class="field"><label>Tagline</label><input name="site_tagline" dir="auto" value="<?= e(ftc((string) setting('site_tagline'))) ?>"></div>
    <div class="field"><label>About text</label><textarea name="footer_note" rows="3" dir="auto"><?= e(ftc((string) setting('footer_note'))) ?></textarea></div>

    <h2>Footer — visit column</h2>
    <div class="field"><label>Heading</label><input name="footer_visit_heading" value="<?= e((string) setting('footer_visit_heading', 'Visit')) ?>"></div>
    <p class="help">Address, email, and phone come from Pages → Contact Us.</p>

    <h2>Footer — legal column</h2>
    <div class="field"><label>Heading</label><input name="footer_legal_heading" value="<?= e($legalHeadingValue) ?>"></div>
    <?php foreach ($links as $i => $link): ?>
        <div class="row-3<?= !empty($link['hidden']) ? ' is-nav-hidden' : '' ?>" data-nav-row>
            <div class="field"><label>Link <?= $i + 1 ?> label</label><input name="link_label[]" dir="auto" value="<?= e((string) ($link['label'] ?? '')) ?>" placeholder="Privacy Policy"></div>
            <div class="field"><label>Link <?= $i + 1 ?> URL</label><input name="link_url[]" value="<?= e((string) ($link['url'] ?? '')) ?>" placeholder="/privacy-policy"></div>
            <?php $visibilityField('link_hidden[]', $link, $i, 'Link'); ?>
        </div>
    <?php endforeach; ?>
    <p class="help">The footer shows Privacy Policy, Terms &amp; Conditions, and Disclaimer. Extra rows can be other legal or external links. Empty rows are ignored. Website pages such as Courses stay in the header, not in this column.</p>

    <h2>Footer — bottom bar</h2>
    <div class="field"><label>Copyright line</label><input name="footer_copyright" value="<?= e((string) setting('footer_copyright')) ?>" placeholder="© <?= date('Y') ?> <?= e(site_name()) ?>. All rights reserved."></div>
    <button class="btn btn-walnut" type="submit">Save header and footer</button>
</form>
<script>
document.querySelectorAll('[data-nav-row]').forEach(function (row) {
    var select = row.querySelector('select');
    if (!select) return;
    var sync = function () {
        row.classList.toggle('is-nav-hidden', select.value === '1');
    };
    select.addEventListener('change', sync);
    sync();
});
</script>

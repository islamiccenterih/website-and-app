<?php
$page = $page ?? [];
$menu = $menu ?? ['label' => '', 'in_header' => true, 'in_footer' => false, 'placement' => 'primary'];
$defaultName = (string) ($page['name'] ?? '');
$value = (string) (($menu['label'] ?? '') !== '' ? $menu['label'] : $defaultName);
$placement = (string) ($menu['placement'] ?? (!empty($menu['in_header']) ? ($menu['group'] ?? 'primary') : 'off'));
if ($placement === 'daily') {
    $placement = 'more';
}
if (!in_array($placement, ['off', 'primary', 'more'], true)) {
    $placement = !empty($menu['in_header']) ? 'primary' : 'off';
}
?>
<section class="page-name-block">
    <div class="field">
        <label for="menu_name">Page name</label>
        <input id="menu_name" name="menu_name" required maxlength="160" dir="auto" value="<?= e($value) ?>">
        <p class="help">This is the name in the header and footer wherever this page is listed.</p>
    </div>
    <div class="field">
        <label for="header_group">Header menu</label>
        <select id="header_group" name="header_group">
            <option value="off"<?= selected($placement, 'off') ?>><?= e(header_group_label('off')) ?></option>
            <option value="primary"<?= selected($placement, 'primary') ?>><?= e(header_group_label('primary')) ?></option>
            <option value="more"<?= selected($placement, 'more') ?>><?= e(header_group_label('more')) ?></option>
        </select>
        <p class="help">Top menu sits on the bar. More is the dropdown on desktop and the extra section in the mobile menu. Not in header hides this page from both.</p>
    </div>
    <div class="page-vis">
        <label class="perm-item">
            <input type="checkbox" name="in_footer" value="1"<?= !empty($menu['in_footer']) ? ' checked' : '' ?>>
            <span>Footer list</span>
        </label>
    </div>
</section>

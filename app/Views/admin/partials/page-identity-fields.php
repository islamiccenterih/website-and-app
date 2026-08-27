<?php
$page = $page ?? [];
$menu = $menu ?? ['label' => '', 'in_header' => true, 'in_footer' => false];
$defaultName = (string) ($page['name'] ?? '');
$value = (string) (($menu['label'] ?? '') !== '' ? $menu['label'] : $defaultName);
?>
<section class="page-name-block">
    <div class="field">
        <label for="menu_name">Page name</label>
        <input id="menu_name" name="menu_name" required maxlength="160" dir="auto" value="<?= e($value) ?>">
        <p class="help">This is the name in the header and footer wherever this page is listed.</p>
    </div>
    <div class="page-vis">
        <label class="perm-item">
            <input type="checkbox" name="in_header" value="1"<?= !empty($menu['in_header']) ? ' checked' : '' ?>>
            <span>Header menu</span>
        </label>
        <label class="perm-item">
            <input type="checkbox" name="in_footer" value="1"<?= !empty($menu['in_footer']) ? ' checked' : '' ?>>
            <span>Footer list</span>
        </label>
    </div>
</section>

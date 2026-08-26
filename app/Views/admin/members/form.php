<?php
$m = $member ?? [];
$access = $access ?? [];
?>
<h1><?= $member ? 'Edit panel member' : 'Add a panel member' ?></h1>
<p>Give this person an email and password, then tick the sections they may view and edit. Unticked sections stay hidden in the menu and blocked if they type the URL.</p>
<form class="form stack-form" method="post" action="<?= e(url($member ? '/admin/members/' . $member['id'] : '/admin/members')) ?>">
    <?= csrf_field() ?>
    <div class="row-2">
        <div class="field">
            <label for="member-name">Name</label>
            <input id="member-name" name="name" required maxlength="120" value="<?= e($m['name'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="member-title">Job title</label>
            <input id="member-title" name="job_title" list="job-titles" maxlength="80" placeholder="Manager" value="<?= e($m['job_title'] ?? 'Manager') ?>">
            <datalist id="job-titles">
                <option value="Manager"></option>
                <option value="Editor"></option>
                <option value="Teacher"></option>
                <option value="Reception"></option>
                <option value="Content writer"></option>
            </datalist>
        </div>
    </div>
    <div class="row-2">
        <div class="field">
            <label for="member-email">Email they will use to sign in</label>
            <input id="member-email" type="email" name="email" required maxlength="190" autocomplete="off" value="<?= e($m['email'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="member-status">Status</label>
            <select id="member-status" name="status">
                <option value="active"<?= selected($m['status'] ?? 'active', 'active') ?>>Active — can sign in</option>
                <option value="disabled"<?= selected($m['status'] ?? '', 'disabled') ?>>Disabled — blocked</option>
            </select>
        </div>
    </div>
    <div class="field">
        <label for="member-password">Password <?= $member ? '(leave blank to keep the current password)' : 'you will give them' ?></label>
        <input id="member-password" type="password" name="password" <?= $member ? '' : 'required' ?> minlength="8" autocomplete="new-password">
        <p class="help">At least 8 characters. Share this password with them privately; they use it with the email above at Admin login.</p>
    </div>
    <fieldset class="perm-fieldset">
        <legend>Sections they may open</legend>
        <p class="help perm-toolbar">
            <button type="button" class="btn btn-outline btn-sm" data-perm-all>Tick all</button>
            <button type="button" class="btn btn-outline btn-sm" data-perm-none>Clear</button>
            Tick only what they should touch. Panel members can never add other members.
        </p>
        <div class="perm-grid">
            <?php foreach ($modules as $key => $mod): ?>
                <label class="perm-item">
                    <input type="checkbox" name="access[]" value="<?= e($key) ?>"<?= in_array($key, $access, true) ? ' checked' : '' ?>>
                    <span><?= e($mod['label']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>
    <button class="btn btn-walnut" type="submit"><?= $member ? 'Save panel member' : 'Create panel member' ?></button>
    <a class="btn btn-outline" href="<?= e(url('/admin/members')) ?>">Cancel</a>
</form>
<script>
(function () {
    var all = document.querySelector('[data-perm-all]');
    var none = document.querySelector('[data-perm-none]');
    var boxes = function () { return document.querySelectorAll('.perm-grid input[type="checkbox"]'); };
    if (all) all.addEventListener('click', function () { boxes().forEach(function (el) { el.checked = true; }); });
    if (none) none.addEventListener('click', function () { boxes().forEach(function (el) { el.checked = false; }); });
})();
</script>

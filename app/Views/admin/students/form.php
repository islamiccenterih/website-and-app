<?php
$s = $student ?? [];
$enrolledIds = array_map('intval', $enrolledIds ?? []);
?>
<h1><?= $student ? 'Edit student' : 'Add student' ?></h1>
<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url($student ? '/admin/students/' . $student['id'] : '/admin/students')) ?>">
    <?= csrf_field() ?>
    <div class="row-2">
        <div class="field"><label>Name</label><input name="name" required value="<?= e($s['name'] ?? '') ?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($s['email'] ?? '') ?>"></div>
    </div>
    <div class="row-2">
        <div class="field"><label>Phone</label><input name="phone" value="<?= e($s['phone'] ?? '') ?>"></div>
        <div class="field"><label>Enrollment no.</label><input name="enrollment_no" value="<?= e($s['enrollment_no'] ?? '') ?>"></div>
    </div>
    <fieldset class="perm-fieldset">
        <legend>Assigned courses</legend>
        <p class="help perm-toolbar">
            <button type="button" class="btn btn-outline btn-sm" data-perm-all>Tick all</button>
            <button type="button" class="btn btn-outline btn-sm" data-perm-none>Clear</button>
            Tick every course this student should join. They can open live classes for each ticked course from their portal.
        </p>
        <?php if (!$courses): ?>
            <p class="help">No courses yet. Create a course first, then assign it here.</p>
        <?php else: ?>
            <div class="perm-grid">
                <?php foreach ($courses as $course): ?>
                    <?php $cid = (int) $course['id']; ?>
                    <label class="perm-item">
                        <input type="checkbox" name="course_ids[]" value="<?= $cid ?>"<?= in_array($cid, $enrolledIds, true) ? ' checked' : '' ?>>
                        <span dir="auto"><?= e(ftc((string) $course['title'])) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </fieldset>
    <div class="field"><label>Status</label>
        <select name="status">
            <option value="active"<?= selected($s['status'] ?? 'active', 'active') ?>>Active</option>
            <option value="disabled"<?= selected($s['status'] ?? '', 'disabled') ?>>Disabled</option>
        </select>
    </div>
    <div class="field"><label>Password <?= $student ? '(leave blank to keep)' : '' ?></label><input type="password" name="password" <?= $student ? '' : 'required' ?> minlength="8"></div>
    <div class="field">
        <label>Photo</label>
        <?php if (!empty($s['avatar'])): ?>
            <img class="student-photo" src="<?= e(upload_url($s['avatar'])) ?>" alt="">
        <?php endif; ?>
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif">
        <p class="help">This photograph is shown only on this student’s panel. Leave empty to keep the current photo.</p>
    </div>
    <button class="btn btn-walnut" type="submit">Save student</button>
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

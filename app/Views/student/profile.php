<?php
$kicker = 'Account';
$title = 'My profile';
$tag = 'h1';
$lead = 'This profile belongs only to your student account. Name, phone, and photograph stay saved until you change them, or the administration updates your record.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>
<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url('/student/profile')) ?>">
    <?= csrf_field() ?>
    <div class="row-2">
        <div class="field"><label>Name</label><input name="name" required value="<?= e($student['name']) ?>"></div>
        <div class="field"><label>Email</label><input value="<?= e($student['email']) ?>" disabled><p class="help">Email is managed by the administration.</p></div>
    </div>
    <div class="row-2">
        <div class="field"><label>Phone</label><input name="phone" value="<?= e((string) $student['phone']) ?>"></div>
        <div class="field"><label>New password (optional)</label><input type="password" name="password" minlength="8"></div>
    </div>
    <div class="field">
        <label>Enrolled courses</label>
        <div class="profile-courses"><?= course_pills(array_column($courses ?? [], 'title'), 'None assigned') ?></div>
        <p class="help">The administration assigns your courses. You can join a live class for any of them. This list cannot be changed from your profile.</p>
    </div>
    <div class="field">
        <label>Photo</label>
        <?php if ($student['avatar']): ?><img class="student-photo" src="<?= e(upload_url($student['avatar'])) ?>" alt="<?= e($student['name']) ?>"><?php endif; ?>
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif">
        <p class="help">JPG, PNG, WebP, or GIF under 10 MB.</p>
    </div>
    <button class="btn btn-walnut" type="submit">Save profile</button>
</form>

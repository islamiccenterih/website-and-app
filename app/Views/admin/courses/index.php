<?php $cmsKey = 'courses'; require APP_PATH . '/Views/admin/partials/cms-bar.php'; ?>
<div class="dash-top">
    <h1>Courses</h1>
    <a class="btn btn-walnut" href="<?= e(url('/admin/courses/create')) ?>">Create course</a>
</div>
<p>Published courses appear on the public Courses page immediately.</p>
<div class="table-wrap">
<table class="data">
    <thead><tr><th>Image</th><th>Title</th><th>Mode</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$courses): ?>
        <tr><td colspan="5">No courses yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($courses as $course): ?>
        <tr>
            <td><img class="thumb" src="<?= e(upload_url($course['main_image'])) ?>" alt=""></td>
            <td>
                <strong dir="auto"><?= e(ftc((string) $course['title'])) ?></strong><br>
                <span class="help">/courses/<?= e($course['slug']) ?></span>
            </td>
            <td><?= e($course['mode']) ?></td>
            <td><span class="badge <?= $course['status'] === 'published' ? 'badge-on' : 'badge-off' ?>"><?= e($course['status']) ?></span></td>
            <td class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/courses/' . $course['id'] . '/edit')) ?>">Edit</a>
                <form method="post" action="<?= e(url('/admin/courses/' . $course['id'] . '/delete')) ?>" data-confirm="Delete this course?">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

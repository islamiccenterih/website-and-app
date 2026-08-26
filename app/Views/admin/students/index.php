<div class="dash-top">
    <h1>Students</h1>
    <a class="btn btn-walnut" href="<?= e(url('/admin/students/create')) ?>">Add student</a>
</div>
<div class="table-wrap">
<table class="data">
    <thead><tr><th>Name</th><th>Email</th><th>Enrollment</th><th>Courses</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($students as $student): ?>
        <tr>
            <td>
                <span class="dash-user-who">
                    <?php if (!empty($student['avatar'])): ?>
                        <img class="dash-avatar" src="<?= e(upload_url($student['avatar'])) ?>" alt="">
                    <?php endif; ?>
                    <?= e($student['name']) ?>
                </span>
            </td>
            <td><?= e($student['email']) ?></td>
            <td><?= e($student['enrollment_no']) ?></td>
            <td><?= course_pills($student['course_titles'] ?? [], 'None') ?></td>
            <td><?= e($student['status']) ?></td>
            <td class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/students/' . $student['id'] . '/edit')) ?>">Edit</a>
                <form method="post" action="<?= e(url('/admin/students/' . $student['id'] . '/delete')) ?>" data-confirm="Delete this student and their results?">
                    <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="dash-top">
    <h1>Results</h1>
    <a class="btn btn-walnut" href="<?= e(url('/admin/results/create')) ?>">Create result</a>
</div>
<p>A student can see a result only when it is published and assigned to that student.</p>
<div class="table-wrap">
<table class="data">
    <thead><tr><th>Student</th><th>Title</th><th>Course</th><th>Grade</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$results): ?><tr><td colspan="6">No results yet.</td></tr><?php endif; ?>
    <?php foreach ($results as $result): ?>
        <tr>
            <td><?= e($result['student_name']) ?><br><span class="help"><?= e($result['student_email']) ?></span></td>
            <td><?= e($result['title']) ?></td>
            <td><?= e(ftc((string) ($result['course_title'] ?? '—'))) ?></td>
            <td><?= e($result['grade'] ?: $result['score']) ?></td>
            <td><?= e($result['status']) ?></td>
            <td class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/results/' . $result['id'] . '/edit')) ?>">Edit</a>
                <form method="post" action="<?= e(url('/admin/results/' . $result['id'] . '/delete')) ?>" data-confirm="Delete this result?">
                    <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

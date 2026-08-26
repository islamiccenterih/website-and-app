<?php
$kicker = 'Classroom';
$title = 'Attendance';
$tag = 'h1';
$lead = ($class['title'] ?? 'Class') . ' · ' . ftc((string) ($class['course_title'] ?? ''));
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>
<p class="dash-actions"><a class="btn btn-outline" href="<?= e(url('/admin/live-classes')) ?>">Back to live classes</a></p>
<div class="table-wrap">
<table class="data">
    <thead><tr><th>Student</th><th>Enrollment</th><th>Joined</th><th>Left</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="4">No students joined this class.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><strong><?= e($row['name']) ?></strong><br><span class="help"><?= e($row['email']) ?></span></td>
            <td><?= e($row['enrollment_no'] ?: '—') ?></td>
            <td><?= e($row['joined_at'] ? date('j M, g:i A', strtotime($row['joined_at'])) : '—') ?></td>
            <td><?= e($row['left_at'] ? date('j M, g:i A', strtotime($row['left_at'])) : 'Still in class / connection dropped') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

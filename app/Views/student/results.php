<?php
$kicker = 'Record';
$title = 'My results';
$tag = 'h1';
$lead = 'Only results assigned to your account and marked published are listed.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>
<?php if (!$results): ?>
    <div class="empty-state"><h3>No published results</h3><p>Your results will appear here when the administration publishes them.</p></div>
<?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr><th>Title</th><th>Course</th><th>Term</th><th>Score</th><th>Grade</th><th>Remarks</th></tr></thead>
        <tbody>
        <?php foreach ($results as $result): ?>
            <tr>
                <td><?= e($result['title']) ?></td>
                <td><?= e(ftc((string) ($result['course_title'] ?? '—'))) ?></td>
                <td><?= e($result['term'] ?: '—') ?></td>
                <td><?= e($result['score'] ?: '—') ?></td>
                <td><?= e($result['grade'] ?: '—') ?></td>
                <td><?= e($result['remarks'] ?: '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>

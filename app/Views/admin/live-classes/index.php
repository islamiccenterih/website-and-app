<?php
$kicker = 'Classroom';
$title = 'Live classes';
$tag = 'h1';
$lead = 'Pick a course, go live, and only enrolled students can join — video, voice, and chat stay on this website. A class stays open until you end it.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>
<p class="dash-actions"><a class="btn btn-walnut" href="<?= e(url('/admin/live-classes/create')) ?>">Create live class</a></p>

<h2>Live now</h2>
<?php if (!$live): ?>
    <div class="empty-state">
        <h3>No class is live</h3>
        <p>Create a class and start it. Enrolled students will see “Class is live now” in Join class.</p>
    </div>
<?php else: ?>
    <div class="table-wrap" style="margin-bottom:1.6rem">
        <table class="data">
            <thead><tr><th>Class</th><th>Course</th><th>In room</th><th>Started</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($live as $row): ?>
                <tr>
                    <td><strong><?= e($row['title']) ?></strong></td>
                    <td><?= e(ftc((string) $row['course_title'])) ?></td>
                    <td><?= (int) ($row['in_room'] ?? 0) ?></td>
                    <td><?= e($row['started_at'] ? date('g:i A', strtotime($row['started_at'])) : '—') ?></td>
                    <td class="dash-actions">
                        <a class="btn btn-gold btn-sm" href="<?= e(url('/admin/live-classes/' . $row['id'] . '/room')) ?>">Enter class</a>
                        <form method="post" action="<?= e(url('/admin/live-classes/' . $row['id'] . '/end')) ?>" data-confirm="End this live class for everyone?">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline btn-sm" type="submit">End class</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h2>Recent classes</h2>
<div class="table-wrap">
<table class="data">
    <thead><tr><th>Class</th><th>Course</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$recent): ?>
        <tr><td colspan="4">No saved classes yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($recent as $row): ?>
        <tr>
            <td><strong><?= e($row['title']) ?></strong></td>
            <td><?= e(ftc((string) $row['course_title'])) ?></td>
            <td><span class="badge <?= $row['status'] === 'scheduled' ? 'badge-on' : 'badge-off' ?>"><?= e($row['status']) ?></span></td>
            <td class="dash-actions">
                <?php if ($row['status'] === 'scheduled'): ?>
                    <form method="post" action="<?= e(url('/admin/live-classes/' . $row['id'] . '/start')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-gold btn-sm" type="submit">Start class</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/live-classes/' . $row['id'] . '/attendance')) ?>">Attendance</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php
$courses = $courses ?? [];
$courseTitles = array_values(array_filter(array_map(
    static fn(array $course): string => trim((string) ($course['title'] ?? '')),
    $courses
)));
$kicker = 'Classroom';
$title = 'Join class';
$tag = 'h1';
$lead = 'Live classes on this website are tied to the courses you are enrolled in. Join appears when a teacher starts a class for any of those courses.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>

<?php if (!$courses): ?>
    <div class="empty-state">
        <h3>No courses assigned yet</h3>
        <p>Ask the administration to enroll you in a course. After that, live classes for those courses will appear here.</p>
    </div>
<?php else: ?>
    <div class="stats">
        <div class="stat stat-courses"><span>Your courses</span><?= course_pills($courseTitles, '—') ?></div>
        <div class="stat"><span>Live now</span><strong><?= $live ? 'Yes — join below' : 'Not yet' ?></strong></div>
        <div class="stat"><span>Saved classes</span><strong><?= !empty($scheduled) ? (string) count($scheduled) : 'None' ?></strong></div>
    </div>

    <?php if ($live): ?>
        <?php foreach ($live as $row): ?>
            <article class="live-callout">
                <p class="live-pulse">Class is live now</p>
                <h2><?= e($row['title']) ?></h2>
                <p><?= e(ftc((string) $row['course_title'])) ?></p>
                <a class="btn btn-gold" href="<?= e(url('/student/join-class/' . $row['id'])) ?>">Join class</a>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3>No live class for your courses right now</h3>
            <p>When a teacher starts a class for any course you are enrolled in, this page shows a Join button. Keep this tab open — it checks every few seconds. The class stays open until the teacher ends it.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($scheduled)): ?>
        <section class="panel-card">
            <?php
            $kicker = 'Upcoming';
            $title = 'Prepared by your teacher';
            $tag = 'h2';
            $lead = 'These rooms are saved. They cannot be joined until the teacher starts them.';
            $align = 'left';
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <ul class="plain-list">
                <?php foreach ($scheduled as $row): ?>
                    <li><strong><?= e($row['title']) ?></strong> — <?= e(ftc((string) $row['course_title'])) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if (!empty($attendance)): ?>
        <section class="panel-card">
            <?php
            $kicker = 'History';
            $title = 'Classes you joined';
            $tag = 'h2';
            $lead = '';
            $align = 'left';
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Class</th><th>Joined</th><th>Left</th></tr></thead>
                    <tbody>
                    <?php foreach ($attendance as $row): ?>
                        <tr>
                            <td><strong><?= e($row['title']) ?></strong><br><span class="help"><?= e(ftc((string) ($row['course_title'] ?: ''))) ?></span></td>
                            <td><?= e(date('j M Y, g:i A', strtotime((string) $row['joined_at']))) ?></td>
                            <td><?= !empty($row['left_at']) ? e(date('g:i A', strtotime((string) $row['left_at']))) : 'In class / open' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>
<script>
(function () {
    setInterval(function () {
        fetch(<?= json_encode(url('/api/student/live')) ?>, { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.live && data.live.length && !document.querySelector('.live-callout')) {
                    window.location.reload();
                }
            })
            .catch(function () {});
    }, 8000);
})();
</script>

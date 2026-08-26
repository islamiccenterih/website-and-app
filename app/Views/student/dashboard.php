<?php
$kicker = 'Students';
$title = 'Assalamu alaikum, ' . ($student['name'] ?? '');
$tag = 'h1';
$lead = 'Your private student area. Other students cannot see your information.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
$courseTitles = $student['course_titles'] ?? array_column($courses ?? [], 'title');
?>

<div class="stats">
    <div class="stat"><span>Enrollment</span><strong><?= e($student['enrollment_no'] ?: '—') ?></strong></div>
    <div class="stat stat-courses">
        <span>Courses</span>
        <?php if ($courseTitles): ?>
            <?= course_pills($courseTitles) ?>
        <?php else: ?>
            <strong>Not assigned</strong>
        <?php endif; ?>
    </div>
</div>

<section class="panel-card student-identity">
    <div class="student-identity-row">
        <?php if (!empty($student['avatar'])): ?>
            <img class="student-photo" src="<?= e(upload_url($student['avatar'])) ?>" alt="<?= e($student['name'] ?? '') ?>">
        <?php endif; ?>
        <div>
            <span class="sec-kicker">Your account</span>
            <h2><?= e($student['name'] ?? '') ?></h2>
            <p><?= e($student['email'] ?? '') ?><?php if (!empty($student['phone'])): ?> · <?= e($student['phone']) ?><?php endif; ?></p>
            <p class="help">These details stay on your account. Other students cannot see them. Only you or the administration can change them.</p>
            <p class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/student/profile')) ?>">Edit profile</a>
                <a class="btn btn-outline btn-sm" href="<?= e(url('/student/course')) ?>">View my courses</a>
            </p>
        </div>
    </div>
</section>

<?php if (!empty($live)): ?>
    <?php foreach ($live as $row): ?>
        <article class="live-callout">
            <p class="live-pulse">Class is live now</p>
            <h2><?= e($row['title']) ?></h2>
            <p><?= e(ftc((string) $row['course_title'])) ?> — only students of this course can enter.</p>
            <a class="btn btn-gold" href="<?= e(url('/student/join-class/' . $row['id'])) ?>">Join class</a>
        </article>
    <?php endforeach; ?>
<?php elseif (!empty($scheduled)): ?>
    <article class="panel-card">
        <span class="sec-kicker">Upcoming</span>
        <h2>Class saved for later</h2>
        <p>Your teacher has prepared <?= e($scheduled[0]['title']) ?>. The Join button appears the moment it goes live. You can join a live class for any course you are enrolled in.</p>
        <a class="btn btn-walnut" href="<?= e(url('/student/join-class')) ?>">Open Join class</a>
    </article>
<?php else: ?>
    <p class="dash-actions">
        <a class="btn btn-walnut" href="<?= e(url('/student/join-class')) ?>">Open Join class</a>
        <a class="btn btn-outline" href="<?= e(url('/student/course')) ?>">View my courses</a>
    </p>
<?php endif; ?>

<div class="panel-grid">
    <section class="panel-card">
        <?php
        $kicker = 'Record';
        $title = 'Published results';
        $tag = 'h2';
        $lead = '';
        $align = 'left';
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (!$results): ?>
            <div class="empty-state"><h3>No results yet</h3><p>When the administration publishes a result for you, it will appear here.</p></div>
        <?php else: ?>
            <ul class="plain-list">
                <?php foreach ($results as $result): ?>
                    <li><strong><?= e($result['title']) ?></strong> — <?= e($result['grade'] ?: $result['score'] ?: 'See details') ?></li>
                <?php endforeach; ?>
            </ul>
            <p><a class="btn btn-walnut btn-sm" href="<?= e(url('/student/results')) ?>">View all results</a></p>
        <?php endif; ?>
    </section>

    <section class="panel-card">
        <?php
        $kicker = 'Classroom';
        $title = 'Recent attendance';
        $tag = 'h2';
        $lead = '';
        $align = 'left';
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (empty($attendance)): ?>
            <div class="empty-state"><h3>No classes attended yet</h3><p>When you join a live class, the date is saved here.</p></div>
        <?php else: ?>
            <ul class="plain-list">
                <?php foreach ($attendance as $row): ?>
                    <li>
                        <strong><?= e($row['title']) ?></strong>
                        — <?= e(date('j M Y, g:i A', strtotime((string) $row['joined_at']))) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<?php if (!empty($events)): ?>
    <section class="panel-card">
        <?php
        $kicker = 'Calendar';
        $title = 'Upcoming Islamic dates';
        $tag = 'h2';
        $lead = '';
        $align = 'left';
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <ul class="event-list">
            <?php foreach ($events as $event): ?>
                <li>
                    <span class="when"><?= e($event['hijri_date'] ?: $event['gregorian_date'] ?: 'Date') ?></span>
                    <div>
                        <strong><?= e($event['title']) ?></strong>
                        <?php if (!empty($event['description'])): ?><p><?= e($event['description']) ?></p><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><a class="btn btn-outline btn-sm" href="<?= e(url('/islamic-calendar')) ?>">Open Islamic calendar</a></p>
    </section>
<?php endif; ?>

<nav class="panel-links">
    <a href="<?= e(url('/gallery')) ?>">Gallery</a>
    <a href="<?= e(url('/moon-timing')) ?>">Moon timing</a>
    <a href="<?= e(url('/contact-us')) ?>">Contact the center</a>
    <a href="<?= e(url('/courses')) ?>">All courses</a>
</nav>

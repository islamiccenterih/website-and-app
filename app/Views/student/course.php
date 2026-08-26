<?php
$courses = $courses ?? [];
$kicker = 'Enrollment';
$title = 'My courses';
$tag = 'h1';
$lead = 'Every course assigned to your student account. You can join a live class for any of them.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>

<?php if (!$courses): ?>
    <div class="empty-state">
        <h3>No courses assigned</h3>
        <p>The administration has not enrolled you in a course yet. Once they do, the syllabus details and live-class access appear here.</p>
    </div>
<?php else: ?>
    <?php foreach ($courses as $course): ?>
        <article class="panel-card course-panel">
            <?php if (!empty($course['main_image'])): ?>
                <img class="course-panel-image" src="<?= e(upload_url($course['main_image'])) ?>" alt="">
            <?php endif; ?>
            <span class="sec-kicker">Your class</span>
            <h2 dir="auto"><?= e(ftc((string) $course['title'])) ?></h2>
            <div class="meta-row">
                <?php if (!empty($course['duration'])): ?><span class="pill"><?= e($course['duration']) ?></span><?php endif; ?>
                <?php if (!empty($course['fees'])): ?><span class="pill"><?= e($course['fees']) ?></span><?php endif; ?>
                <?php if (!empty($course['mode'])): ?><span class="pill"><?= e(ucfirst((string) $course['mode'])) ?></span><?php endif; ?>
            </div>
            <?php if (!empty($course['short_description'])): ?>
                <p dir="auto"><?= e(ftc((string) $course['short_description'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($course['full_description'])): ?>
                <div class="prose"><?= ftc(\App\Core\Html::clean($course['full_description'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($course['additional_info'])): ?>
                <p dir="auto"><?= e(ftc((string) $course['additional_info'])) ?></p>
            <?php endif; ?>
            <p class="dash-actions">
                <a class="btn btn-walnut" href="<?= e(url('/student/join-class')) ?>">Join class</a>
                <?php if (!empty($course['slug'])): ?>
                    <a class="btn btn-outline" href="<?= e(url('/courses/' . $course['slug'])) ?>">Open public course page</a>
                <?php endif; ?>
            </p>
        </article>
    <?php endforeach; ?>

    <?php if (!empty($live)): ?>
        <?php foreach ($live as $row): ?>
            <article class="live-callout">
                <p class="live-pulse">Class is live now</p>
                <h2><?= e($row['title']) ?></h2>
                <p><?= e(ftc((string) ($row['course_title'] ?? ''))) ?></p>
                <a class="btn btn-gold" href="<?= e(url('/student/join-class/' . $row['id'])) ?>">Join now</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

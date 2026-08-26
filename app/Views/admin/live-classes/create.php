<?php
$kicker = 'Classroom';
$title = 'Create live class';
$tag = 'h1';
$lead = 'Choose the course you are teaching. When you start, only students enrolled in that course can join. A student may be enrolled in more than one course and can enter this room if this course is one of them. The room stays open until you end it.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
?>
<form class="form stack-form" method="post" action="<?= e(url('/admin/live-classes')) ?>">
    <?= csrf_field() ?>
    <div class="field">
        <label for="course_id">Course</label>
        <select id="course_id" name="course_id" required>
            <option value="">Select a course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= (int) $course['id'] ?>"><?= e(ftc((string) $course['title'])) ?><?= ($course['status'] ?? '') !== 'published' ? ' (draft)' : '' ?></option>
            <?php endforeach; ?>
        </select>
        <p class="help">Example: select Tajweed Ul Quran to open a room that only those students can join. A student enrolled in several courses can still join if Tajweed Ul Quran is one of them.</p>
    </div>
    <div class="field">
        <label for="title">Class title</label>
        <input id="title" name="title" maxlength="180" placeholder="Tajweed Ul Quran — this evening">
    </div>
    <div class="field">
        <label for="notes">Note for yourself (optional)</label>
        <textarea id="notes" name="notes" rows="2" maxlength="400" placeholder="Lesson focus, batch, or room reminder"></textarea>
    </div>
    <p class="dash-actions">
        <button class="btn btn-gold" type="submit" name="start_now" value="1">Start class now</button>
        <button class="btn btn-walnut" type="submit">Save and start later</button>
        <a class="btn btn-outline" href="<?= e(url('/admin/live-classes')) ?>">Cancel</a>
    </p>
</form>

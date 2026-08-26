<?php $r = $result ?? []; ?>
<h1><?= $result ? 'Edit result' : 'Create result' ?></h1>
<form class="form stack-form" method="post" action="<?= e(url($result ? '/admin/results/' . $result['id'] : '/admin/results')) ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Student</label>
        <select name="student_id" id="result-student" required>
            <option value="">Select student</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= (int) $student['id'] ?>"<?= selected($r['student_id'] ?? '', $student['id']) ?>><?= e($student['name'] . ' — ' . $student['email']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field"><label>Course (optional)</label>
        <select name="course_id" id="result-course">
            <option value="">None</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= (int) $course['id'] ?>"<?= selected($r['course_id'] ?? '', $course['id']) ?>><?= e(ftc((string) $course['title'])) ?></option>
            <?php endforeach; ?>
        </select>
        <p class="help">Only courses this student is enrolled in. Leave as None if the result is not tied to one course.</p>
    </div>
    <div class="field"><label>Title</label><input name="title" required value="<?= e($r['title'] ?? '') ?>"></div>
    <div class="row-2">
        <div class="field"><label>Term</label><input name="term" value="<?= e($r['term'] ?? '') ?>"></div>
        <div class="field"><label>Issued date</label><input type="date" name="issued_at" value="<?= e($r['issued_at'] ?? '') ?>"></div>
    </div>
    <div class="row-2">
        <div class="field"><label>Score</label><input name="score" value="<?= e($r['score'] ?? '') ?>"></div>
        <div class="field"><label>Grade</label><input name="grade" value="<?= e($r['grade'] ?? '') ?>"></div>
    </div>
    <div class="field"><label>Remarks</label><textarea name="remarks" rows="4"><?= e($r['remarks'] ?? '') ?></textarea></div>
    <div class="field"><label>Status</label>
        <select name="status">
            <option value="draft"<?= selected($r['status'] ?? 'draft', 'draft') ?>>Draft — hidden from student</option>
            <option value="published"<?= selected($r['status'] ?? '', 'published') ?>>Published — visible to that student</option>
        </select>
    </div>
    <button class="btn btn-walnut" type="submit">Save result</button>
</form>
<script>
(function () {
    var enrollments = <?= json_encode($enrollments ?? new \stdClass(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>;
    var allCourses = <?= json_encode(array_values(array_map(static function (array $course): array {
        return [
            'id' => (int) $course['id'],
            'title' => ftc((string) ($course['title'] ?? '')),
        ];
    }, $courses ?? [])), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>;
    var resultCourseId = <?= json_encode((string) ($r['course_id'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
    var studentSel = document.getElementById('result-student');
    var courseSel = document.getElementById('result-course');
    if (!studentSel || !courseSel) return;

    function courseTitle(id) {
        var sid = String(id);
        for (var i = 0; i < allCourses.length; i++) {
            if (String(allCourses[i].id) === sid) return allCourses[i].title;
        }
        var current = courseSel.querySelector('option[value="' + sid.replace(/"/g, '') + '"]');
        return current ? current.textContent : ('Course #' + sid);
    }

    function addOption(value, text, selected) {
        var opt = document.createElement('option');
        opt.value = value;
        opt.textContent = text;
        if (selected) opt.selected = true;
        courseSel.appendChild(opt);
    }

    function fill() {
        var keep = courseSel.value || resultCourseId;
        var sid = studentSel.value;
        var list = [];
        if (sid) {
            list = enrollments[sid] || enrollments[String(sid)] || [];
        }
        var seen = {};
        courseSel.innerHTML = '';
        addOption('', 'None', keep === '');
        list.forEach(function (course) {
            var id = String(course.id);
            seen[id] = true;
            addOption(id, course.title, id === keep);
        });
        if (keep !== '' && !seen[keep]) {
            addOption(keep, courseTitle(keep), true);
        }
    }

    studentSel.addEventListener('change', fill);
    fill();
})();
</script>

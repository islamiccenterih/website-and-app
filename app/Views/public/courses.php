<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('courses', 'kicker', 'Education');
        $title = page_copy('courses', 'title', 'Courses');
        $tag = 'h1';
        $lead = page_copy('courses', 'lead', 'Online and on-site courses. Each card is created in the Admin Panel and shown here from the database.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php
        $kicker = page_copy('courses', 'inner_kicker', 'Education');
        $title = page_copy('courses', 'inner_title', 'Current Courses');
        $tag = 'h2';
        $lead = '';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (!$courses): ?>
            <div class="empty-state"><h3>No courses published yet</h3><p>Published courses from the Admin Panel will appear in this grid.</p></div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <?php require APP_PATH . '/Views/components/course-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

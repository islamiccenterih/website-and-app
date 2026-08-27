<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('activities', 'kicker', 'Community life');
        $title = page_copy('activities', 'title', 'Social Activities');
        $tag = 'h1';
        $lead = page_copy('activities', 'lead', \App\Services\ActivityCatalog::pageLead());
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (!empty($groups)): ?>
            <nav class="activity-toc" aria-label="Activity sections">
                <?php foreach ($groups as $group): ?>
                    <a href="#<?= e((string) $group['section']['slug']) ?>"><?= e(cms($group['section']['name'])) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php if (empty($groups)): ?>
    <section class="section section-sand">
        <div class="container">
            <div class="empty-state"><h3>No social activities published yet</h3><p>Create a section and an activity in the Admin Panel to show it here.</p></div>
        </div>
    </section>
<?php else: ?>
    <?php foreach ($groups as $i => $group):
        $section = $group['section'];
        $activities = $group['activities'];
        ?>
        <section class="section <?= $i % 2 === 0 ? 'section-sand' : '' ?> activity-block" id="<?= e((string) $section['slug']) ?>">
            <div class="container">
                <?php
                $kicker = $section['kicker'] ?: page_copy('activities', 'inner_kicker', 'Programmes');
                $title = $section['name'];
                $tag = 'h2';
                $lead = $section['lead'] ?: '';
                $align = 'center';
                $light = false;
                require APP_PATH . '/Views/components/section-head.php';
                ?>
                <div class="courses-grid">
                    <?php foreach ($activities as $activity): ?>
                        <?php require APP_PATH . '/Views/components/activity-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

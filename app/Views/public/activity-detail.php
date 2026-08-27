<section class="page-hero">
    <div class="container">
        <?php
        $kicker = $activity['section_name'] ?? ($activity['event_year'] ?: 'Community programme');
        $title = $activity['title'];
        $tag = 'h1';
        $lead = $activity['short_description'];
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php
        $kicker = page_copy('activities', 'detail_kicker', 'Program details');
        $title = $activity['title'];
        $tag = 'h2';
        $lead = '';
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <div class="detail-hero">
            <img src="<?= e(upload_url($activity['main_image'])) ?>" alt="<?= e($activity['title']) ?>">
        </div>
        <div class="meta-row">
            <?php if ($activity['event_date']): ?><span class="pill"><?= e($activity['event_date']) ?></span><?php endif; ?>
            <?php if ($activity['event_year']): ?><span class="pill"><?= e($activity['event_year']) ?></span><?php endif; ?>
        </div>
        <div class="prose">
            <?= $bodyHtml ?: '<p>A full description will appear here once it is entered in the Admin Panel.</p>' ?>
        </div>
        <?php if ($images): ?>
            <h2 style="margin-top:2rem"><?= e(page_copy('activities', 'images_heading', 'Photographs')) ?></h2>
            <div class="gallery-grid" style="margin-top:1rem">
                <?php foreach ($images as $image): ?>
                    <a href="<?= e(upload_url($image['image_path'])) ?>" data-gallery-item data-alt="<?= e($image['caption'] ?: $activity['title']) ?>">
                        <img src="<?= e(upload_url($image['image_path'])) ?>" alt="<?= e($image['caption'] ?: $activity['title']) ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p style="margin-top:2rem"><a class="btn btn-outline" href="<?= e(url('/social-activities')) ?>"><?= e(page_copy('activities', 'back_label', 'Back to activities')) ?></a></p>
    </div>
</section>
<div class="lightbox" data-lightbox><button class="lightbox-close" type="button" aria-label="Close">&times;</button><img src="" alt=""></div>

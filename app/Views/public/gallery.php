<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('gallery', 'kicker', 'Photographs');
        $title = page_copy('gallery', 'title', 'Gallery');
        $tag = 'h1';
        $lead = page_copy('gallery', 'lead', 'Images from the life of the center — classes, gatherings, and the campus.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container">
        <?php
        $kicker = page_copy('gallery', 'inner_kicker', 'Moments');
        $title = page_copy('gallery', 'inner_title', 'From the center');
        $tag = 'h2';
        $lead = '';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>

        <?php if (!$images): ?>
            <div class="empty-state"><h3>No photographs yet</h3><p>Images uploaded from Admin → Gallery appear here.</p></div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($images as $image): ?>
                    <a href="<?= e(upload_url($image['image_path'])) ?>" data-gallery-item data-alt="<?= e($image['alt_text'] ?: $image['title']) ?>">
                        <img src="<?= e(upload_url($image['image_path'])) ?>" alt="<?= e($image['alt_text'] ?: $image['title'] ?: 'Gallery image') ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<div class="lightbox" data-lightbox role="dialog" aria-modal="true">
    <button class="lightbox-close" type="button" aria-label="Close">&times;</button>
    <img src="" alt="">
</div>

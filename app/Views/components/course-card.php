<article class="media-card">
    <img src="<?= e(upload_url($course['main_image'])) ?>" alt="<?= e($course['title']) ?>" loading="lazy">
    <div class="body">
        <p class="meta"><?= e(ucfirst($course['mode'])) ?><?= $course['duration'] ? ' · ' . e($course['duration']) : '' ?></p>
        <h3><?= e(ft($course['title'])) ?></h3>
        <p><?= e(ft($course['short_description'])) ?></p>
        <div class="card-actions">
            <a class="btn btn-walnut" href="<?= e(url('/courses/' . $course['slug'])) ?>"><?= e(tt('Learn More')) ?></a>
            <a class="btn btn-outline" href="<?= e(url('/courses/' . $course['slug'] . '#apply')) ?>"><?= e(tt('Apply')) ?></a>
        </div>
    </div>
</article>

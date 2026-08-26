<article class="media-card">
    <img src="<?= e(upload_url($activity['main_image'])) ?>" alt="<?= e($activity['title']) ?>" loading="lazy">
    <div class="body">
        <p class="meta"><?= e(ft($activity['section_name'] ?? ($activity['event_year'] ?: ($activity['event_date'] ?: 'Programme')))) ?></p>
        <h3><?= e(ft($activity['title'])) ?></h3>
        <p><?= e(ft($activity['short_description'])) ?></p>
        <a class="btn btn-walnut" href="<?= e(url('/social-activities/' . $activity['slug'])) ?>">Learn More</a>
    </div>
</article>

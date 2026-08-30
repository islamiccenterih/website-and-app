<?php
$coordinators = $coordinators ?? [];
if (!$coordinators) {
    return;
}
$intro = $coordinatorIntro ?? [];
$extra = json_decode((string) ($intro['extra_json'] ?? ''), true) ?: [];
$sand = !empty($coordinatorSand);
?>
<section class="section<?= $sand ? ' section-sand' : '' ?> coord-sec">
    <div class="container">
        <?php
        $kicker = $extra['kicker'] ?? 'Leadership';
        $title = $intro['title'] ?? 'Coordinators';
        $tag = 'h2';
        $lead = $intro['content'] ?? '';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <div class="coord-grid" data-count="<?= count($coordinators) ?>">
            <?php foreach ($coordinators as $person): ?>
                <?php
                $name = (string) ($person['name'] ?? '');
                $role = (string) ($person['designation'] ?? '');
                $points = is_array($person['highlights'] ?? null) ? $person['highlights'] : [];
                $photo = trim((string) ($person['photo'] ?? ''));
                $initials = (string) ($person['initials'] ?? 'IC');
                ?>
                <article class="coord-card">
                    <div class="coord-photo<?= $photo === '' ? ' is-placeholder' : '' ?>">
                        <?php if ($photo !== ''): ?>
                            <img src="<?= e(upload_url($photo)) ?>" alt="<?= e($name) ?>">
                        <?php else: ?>
                            <span class="coord-initials" aria-hidden="true"><?= e($initials) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="coord-body">
                        <h3><?= e($name) ?></h3>
                        <?php if ($role !== ''): ?>
                            <p class="coord-role"><?= e(cms($role)) ?></p>
                        <?php endif; ?>
                        <?php if ($points): ?>
                            <ul class="coord-points">
                                <?php foreach ($points as $point): ?>
                                    <li><?= e(cms((string) $point)) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

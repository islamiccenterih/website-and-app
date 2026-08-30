<?php
$pageHero = $sections['page_hero'] ?? [];
$pageExtra = json_decode((string) ($pageHero['extra_json'] ?? ''), true) ?: [];
?>
<section class="page-hero<?= !empty($pageHero['image']) ? ' has-photo' : '' ?>"<?php if (!empty($pageHero['image'])): ?> style="--hero-photo: url('<?= e(upload_url($pageHero['image'])) ?>')"<?php endif; ?>>
    <div class="container">
        <?php
        $kicker = $pageExtra['kicker'] ?? 'Our story';
        $title = $pageHero['title'] ?? 'About Islamic Center Information Hub';
        $tag = 'h1';
        $lead = $pageHero['content'] ?? 'Where Faith Guides Learning, and Learning Inspires Purpose';
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>

<?php
$foundation = $sections['foundation'] ?? [];
$foundationExtra = json_decode((string) ($foundation['extra_json'] ?? ''), true) ?: [];
$values = is_array($foundationExtra['values'] ?? null) ? $foundationExtra['values'] : ['Faith', 'Knowledge', 'Character', 'Skills', 'Service'];
?>
<section class="section">
    <div class="container split">
        <img src="<?= e(upload_url($foundation['image'] ?? null)) ?>" alt="<?= e($foundation['title'] ?? 'Islamic Center Information Hub') ?>">
        <div class="prose">
            <?php
            $kicker = $foundationExtra['kicker'] ?? 'The Center';
            $title = $foundation['title'] ?? 'A strong future begins with strong foundations';
            $tag = 'h2';
            $lead = '';
            $align = 'left';
            $light = false;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <?= App\Core\Html::clean($foundation['content'] ?? '') ?>
            <?php if ($values): ?>
                <ul class="value-row">
                    <?php foreach ($values as $value): ?>
                        <li><?= e(cms((string) $value)) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($foundationMeta): ?>
                <div class="meta-row">
                    <?php if (!empty($foundationMeta['established'])): ?><span class="pill">Established: <?= e($foundationMeta['established']) ?></span><?php endif; ?>
                    <?php if (!empty($foundationMeta['location'])): ?><span class="pill"><?= e($foundationMeta['location']) ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$coordinatorSand = true;
require APP_PATH . '/Views/components/coordinator-section.php';
?>

<?php $history = $sections['history'] ?? []; $historyExtra = json_decode((string) ($history['extra_json'] ?? ''), true) ?: []; ?>
<section class="section<?= $coordinators ? '' : ' section-sand' ?>">
    <div class="container journey-wrap">
        <?php
        $kicker = $historyExtra['kicker'] ?? 'From a small room to a growing vision';
        $title = $history['title'] ?? 'Our Journey';
        $tag = 'h2';
        $lead = '';
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <div class="prose"><?= App\Core\Html::clean($history['content'] ?? '') ?></div>
        <?php if ($timeline): ?>
            <ol class="timeline">
                <?php foreach ($timeline as $item): ?>
                    <li>
                        <div class="year"><?= e($item['year'] ?? '') ?></div>
                        <h3><?= e(cms($item['title'] ?? '')) ?></h3>
                        <p><?= e(cms($item['text'] ?? '')) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>

<section class="section<?= $coordinators ? ' section-sand' : '' ?>">
    <div class="container">
        <?php $mission = $sections['mission'] ?? []; $vision = $sections['vision'] ?? [];
        $missionExtra = json_decode((string) ($mission['extra_json'] ?? ''), true) ?: [];
        $visionExtra = json_decode((string) ($vision['extra_json'] ?? ''), true) ?: []; ?>
        <div class="mission-grid">
            <article class="purpose-block">
                <?php if (!empty($mission['image'])): ?>
                    <img class="purpose-photo" src="<?= e(upload_url($mission['image'])) ?>" alt="">
                <?php endif; ?>
                <?php
                $kicker = $missionExtra['kicker'] ?? 'Our Mission';
                $title = $mission['title'] ?? 'Educating Hearts. Empowering Minds. Building Character.';
                $tag = 'h2';
                $lead = '';
                $align = 'center';
                $light = false;
                require APP_PATH . '/Views/components/section-head.php';
                ?>
                <div class="prose"><?= App\Core\Html::clean($mission['content'] ?? '') ?></div>
            </article>
            <article class="purpose-block">
                <?php if (!empty($vision['image'])): ?>
                    <img class="purpose-photo" src="<?= e(upload_url($vision['image'])) ?>" alt="">
                <?php endif; ?>
                <?php
                $kicker = $visionExtra['kicker'] ?? 'Our Vision';
                $title = $vision['title'] ?? 'A Generation Rooted in Faith, Ready for the Future';
                $tag = 'h2';
                $lead = '';
                $align = 'center';
                $light = false;
                require APP_PATH . '/Views/components/section-head.php';
                ?>
                <div class="prose"><?= App\Core\Html::clean($vision['content'] ?? '') ?></div>
            </article>
        </div>
    </div>
</section>

<?php $who = $sections['who_we_are'] ?? []; $whoExtra = json_decode((string) ($who['extra_json'] ?? ''), true) ?: []; ?>
<section class="section<?= $coordinators ? '' : ' section-sand' ?>">
    <div class="container approach-wrap">
        <?php
        $kicker = $whoExtra['kicker'] ?? 'Our Approach';
        $title = $who['title'] ?? 'Deen & Duniya — Together, Not Apart';
        $tag = 'h2';
        $lead = '';
        $align = 'left';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <div class="prose"><?= App\Core\Html::clean($who['content'] ?? '') ?></div>
    </div>
</section>

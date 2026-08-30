<?php
$aboutImg = upload_url($aboutPreview['image'] ?? 'assets/img/about-placeholder.svg');
$blurImg = asset('assets/img/blur-mosque.svg');
$heroImage = !empty($hero['image']) ? upload_url($hero['image']) : null;
$heroPoster = $heroImage ?: asset('assets/img/hero-nabawi-poster.jpg');
$heroVideo = asset('assets/video/hero-nabawi.mp4');
$heroLead = trim((string) ($hero['content'] ?? ''));
if ($heroLead !== '') {
    $parts = preg_split('/(?<=[.!?])\s+/', $heroLead, 2);
    $heroLead = $parts[0] ?? $heroLead;
}
$pillars = [
    [
        'title' => 'Shahadah',
        'meaning' => 'Faith',
        'icon' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M32 8c-9 10-18 18-18 30a18 18 0 1 0 36 0C50 26 41 18 32 8z" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M38 20l6-3 2 6-4 4z" fill="currentColor"/></svg>',
    ],
    [
        'title' => 'Salah',
        'meaning' => 'Prayer',
        'icon' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M32 8l16 12v36H16V20z" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M32 8v12M24 56V40h16v16M28 28h8" fill="none" stroke="currentColor" stroke-width="2.2"/></svg>',
    ],
    [
        'title' => 'Sawm',
        'meaning' => 'Fasting',
        'icon' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M40 12a20 20 0 1 0 12 34A22 22 0 0 1 40 12z" fill="none" stroke="currentColor" stroke-width="2.2"/></svg>',
    ],
    [
        'title' => 'Zakat',
        'meaning' => 'Almsgiving',
        'icon' => '<svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="26" cy="30" r="12" fill="none" stroke="currentColor" stroke-width="2.2"/><circle cx="38" cy="36" r="12" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M26 26v8M38 32v8" stroke="currentColor" stroke-width="2.2"/></svg>',
    ],
    [
        'title' => 'Hajj',
        'meaning' => 'Pilgrimage',
        'icon' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M18 52V24l14-10 14 10v28z" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M28 52V36h8v16" fill="none" stroke="currentColor" stroke-width="2.2"/></svg>',
    ],
];
?>
<section class="hero hero-video-sec">
    <?php if ($heroImage): ?>
        <img class="hero-photo" src="<?= e($heroImage) ?>" alt="">
    <?php else: ?>
    <video id="hero-video" class="hero-video" autoplay muted loop playsinline webkit-playsinline preload="metadata" poster="<?= e($heroPoster) ?>" aria-hidden="true">
        <source src="<?= e($heroVideo) ?>" type="video/mp4">
    </video>
    <?php endif; ?>
    <div class="hero-shade"></div>
    <div class="container hero-inner hero-inner-center">
        <?php
        $heroDua = is_array($heroDua ?? null) ? $heroDua : [];
        $heroDuaAr = trim((string) ($heroDua['arabic'] ?? ''));
        $heroDuaHi = trim((string) ($heroDua['hindi'] ?? ''));
        $heroDuaSurah = trim((string) ($heroDua['surah'] ?? ''));
        $heroDuaAyah = trim((string) ($heroDua['ayah'] ?? ''));
        ?>
        <?php if ($heroDuaAr !== ''): ?>
            <div class="hero-dua" data-hero-dua hidden aria-hidden="true">
                <div class="hero-dua-box">
                    <span class="hero-dua-flash" aria-hidden="true"></span>
                    <button class="hero-dua-close" type="button" data-hero-dua-close aria-label="<?= e(tt('Close dua')) ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <p class="hero-dua-ar" lang="ar" dir="rtl"><?= e($heroDuaAr) ?></p>
                    <?php if ($heroDuaHi !== ''): ?>
                        <p class="hero-dua-hi" lang="hi"><?= e($heroDuaHi) ?></p>
                    <?php endif; ?>
                    <?php if ($heroDuaSurah !== ''): ?>
                        <p class="hero-dua-ref"><?= e(tt($heroDuaSurah)) ?><?= $heroDuaAyah !== '' ? ' · ' . e($heroDuaAyah) : '' ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <p class="arabic-mark"><?= e(cms($heroExtra['arabic'] ?? 'بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ')) ?></p>
        <p class="hero-kicker"><?= e(cms($hero['subtitle'] ?? 'Islamic Center Information Hub')) ?></p>
        <h1><?= e(cms($hero['title'] ?? 'Where Faith Guides Learning, and Learning Inspires Purpose')) ?></h1>
        <span class="sec-rule" aria-hidden="true"><img src="<?= e(asset('assets/img/heading-rule-light.svg')) ?>" alt=""></span>
        <?php if ($heroLead !== ''): ?>
            <p class="hero-lead"><?= e(cms($heroLead)) ?></p>
        <?php endif; ?>
    </div>
    <script>
    (function () {
        var v = document.getElementById('hero-video');
        if (v) {
            v.muted = true;
            v.defaultMuted = true;
            v.playsInline = true;
            var play = function () { v.play().catch(function () {}); };
            if (v.readyState >= 2) play();
            v.addEventListener('canplay', play);
            v.addEventListener('loadeddata', play);
            document.addEventListener('click', play, { once: true });
            document.addEventListener('touchstart', play, { once: true });
        }

        var dua = document.querySelector('[data-hero-dua]');
        if (!dua) return;
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.setTimeout(function () {
            dua.hidden = false;
            dua.setAttribute('aria-hidden', 'false');
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    dua.classList.add('is-in');
                });
            });
        }, reduce ? 0 : 1000);
        var closeBtn = dua.querySelector('[data-hero-dua-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dua.classList.remove('is-in');
                dua.classList.add('is-out');
                window.setTimeout(function () {
                    dua.hidden = true;
                    dua.setAttribute('aria-hidden', 'true');
                    dua.classList.remove('is-out');
                }, reduce ? 0 : 360);
            });
        }
    })();
    </script>
</section>

<section class="section">
    <div class="container split">
        <div class="split-media">
            <img src="<?= e($aboutImg) ?>" alt="About <?= e(site_name()) ?>">
        </div>
        <div>
            <?php
            $kicker = $aboutPreview['subtitle'] ?? 'Faith • Knowledge • Character • Skills • Service';
            $title = $aboutPreview['title'] ?? 'About Islamic Center Information Hub';
            $tag = 'h2';
            $lead = '';
            $align = 'left';
            $light = false;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <p><?= e(cms($aboutPreview['content'] ?? '')) ?></p>
            <?php
            $aboutPoints = is_array($aboutExtra['points'] ?? null) ? $aboutExtra['points'] : [
                'Qur’an, Sunnah, and Islamic character as the foundation',
                'Contemporary learning, technology, and practical skills',
                'Youth, families, and a life of sincere service',
            ];
            $aboutPoints = array_values(array_filter(array_map('strval', $aboutPoints), static fn($p) => trim($p) !== ''));
            ?>
            <?php if ($aboutPoints): ?>
            <ul class="about-points">
                <?php foreach ($aboutPoints as $point): ?>
                    <li><?= e(cms($point)) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <a class="btn btn-walnut" href="<?= e(url('/about-us')) ?>"><?= e(cms($aboutExtra['cta_label'] ?? 'Learn More')) ?></a>
        </div>
    </div>
</section>

<?php
$coordinatorSand = true;
require APP_PATH . '/Views/components/coordinator-section.php';
?>

<section class="section">
    <div class="container">
        <?php
        $kicker = $sections['programs_intro']['subtitle'] ?? 'What We Offer';
        $title = $sections['programs_intro']['title'] ?? 'Center Activities';
        $tag = 'h2';
        $lead = '';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (!$programs): ?>
            <div class="empty-state"><h3>No activities published yet</h3><p>Center activities will appear here when they are published.</p></div>
        <?php else: ?>
            <div class="programs-grid">
                <?php foreach ($programs as $program): ?>
                    <?php require APP_PATH . '/Views/components/program-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-blur pillars-sec">
    <div class="blur-image" style="background-image:url('<?= e($blurImg) ?>')"></div>
    <div class="container">
        <?php
        $kicker = $sections['pillars']['subtitle'] ?? 'About Essential';
        $title = $sections['pillars']['title'] ?? 'Pillars of Islam';
        $tag = 'h2';
        $lead = $sections['pillars']['content'] ?? 'Shahadah, Salah, Sawm, Zakat and Hajj — taught as living practice at Islamic Center Information Hub.';
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <ul class="pillars">
            <?php
            $pillarExtra = json_decode((string) ($sections['pillars']['extra_json'] ?? ''), true) ?: [];
            $customPillars = is_array($pillarExtra['items'] ?? null) ? $pillarExtra['items'] : [];
            foreach ($pillars as $i => $pillar):
                $title = trim((string) ($customPillars[$i]['title'] ?? $pillar['title']));
                $meaning = trim((string) ($customPillars[$i]['meaning'] ?? $pillar['meaning']));
            ?>
                <li>
                    <article class="pillar-card">
                        <span class="pillar-mark"><?= $pillar['icon'] ?></span>
                        <h3><?= e(cms($title !== '' ? $title : $pillar['title'])) ?></h3>
                        <p>(<?= e(cms($meaning !== '' ? $meaning : $pillar['meaning'])) ?>)</p>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php
        $kicker = $sections['courses_intro']['subtitle'] ?? 'Education';
        $title = $sections['courses_intro']['title'] ?? 'Courses';
        $tag = 'h2';
        $lead = $sections['courses_intro']['content'] ?? 'Qur’an, tajweed, family life and practical skills — on-site in Madina Colony and online from Islamic Center Information Hub.';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (!$courses): ?>
            <div class="empty-state"><h3>No courses published yet</h3><p>When an administrator publishes a course, it will appear here.</p></div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <?php require APP_PATH . '/Views/components/course-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php $coursesExtra = json_decode((string) ($sections['courses_intro']['extra_json'] ?? ''), true) ?: []; ?>
            <p class="section-cta"><a class="btn btn-walnut" href="<?= e(url($coursesExtra['more_url'] ?? '/courses')) ?>"><?= e(cms($coursesExtra['more_label'] ?? 'View all courses')) ?></a></p>
        <?php endif; ?>
    </div>
</section>

<?php require APP_PATH . '/Views/components/prayer-times.php'; ?>

<section class="section section-sand">
    <div class="container">
        <?php
        $kicker = $sections['activities_intro']['subtitle'] ?? 'Community';
        $title = $sections['activities_intro']['title'] ?? 'Social Activities';
        $tag = 'h2';
        $lead = $sections['activities_intro']['content'] ?? 'Workshops, seminars, welfare and awareness programmes for students, youth and families at Islamic Center Information Hub.';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (!$activities): ?>
            <div class="empty-state"><h3>No social activities published yet</h3><p>Community programmes of Islamic Center Information Hub will appear here when they are published.</p></div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($activities as $activity): ?>
                    <?php require APP_PATH . '/Views/components/activity-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php $activitiesExtra = json_decode((string) ($sections['activities_intro']['extra_json'] ?? ''), true) ?: []; ?>
            <p class="section-cta"><a class="btn btn-walnut" href="<?= e(url($activitiesExtra['more_url'] ?? '/social-activities')) ?>"><?= e(cms($activitiesExtra['more_label'] ?? 'All social activities')) ?></a></p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php
        $kicker = $sections['gallery_intro']['subtitle'] ?? 'Moments';
        $title = $sections['gallery_intro']['title'] ?? 'Gallery';
        $tag = 'h2';
        $lead = $sections['gallery_intro']['content'] ?? 'Glimpses of classes, gatherings and campus life at Islamic Center Information Hub in Madina Colony.';
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <?php if (!$gallery): ?>
            <div class="empty-state"><h3>No gallery images yet</h3><p>Photographs from the life of Islamic Center Information Hub will appear here.</p></div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($gallery as $image): ?>
                    <a href="<?= e(upload_url($image['image_path'])) ?>" data-gallery-item data-alt="<?= e($image['alt_text'] ?: $image['title']) ?>">
                        <img src="<?= e(upload_url($image['image_path'])) ?>" alt="<?= e($image['alt_text'] ?: $image['title'] ?: 'Gallery image') ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
            <?php $galleryExtra = json_decode((string) ($sections['gallery_intro']['extra_json'] ?? ''), true) ?: []; ?>
            <p class="section-cta"><a class="btn btn-walnut" href="<?= e(url($galleryExtra['more_url'] ?? '/gallery')) ?>"><?= e(cms($galleryExtra['more_label'] ?? 'View Full Gallery')) ?></a></p>
        <?php endif; ?>
    </div>
</section>

<section class="section section-blur cta-band">
    <div class="blur-image" style="background-image:url('<?= e($heroPoster) ?>')"></div>
    <div class="container">
        <?php
        $kicker = $cta['subtitle'] ?? 'You are welcome';
        $title = $cta['title'] ?? 'Visit, learn, and take part';
        $tag = 'h2';
        $lead = $cta['content'] ?? 'Students, families, and neighbours are invited to learn with purpose — in faith, knowledge, and service.';
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <a class="btn btn-gold" href="<?= e(url($ctaExtra['cta_url'] ?? '/contact-us')) ?>"><?= e(cms($ctaExtra['cta_label'] ?? 'Contact Us')) ?></a>
    </div>
</section>

<div class="lightbox" data-lightbox role="dialog" aria-modal="true">
    <button class="lightbox-close" type="button" aria-label="Close">&times;</button>
    <img src="" alt="">
</div>

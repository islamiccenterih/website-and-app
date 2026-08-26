<?php
$hero = $sections['hero'] ?? [];
$about = $sections['about_preview'] ?? [];
$programs = $sections['programs_intro'] ?? [];
$pillars = $sections['pillars'] ?? [];
$courses = $sections['courses_intro'] ?? [];
$activities = $sections['activities_intro'] ?? [];
$gallery = $sections['gallery_intro'] ?? [];
$cta = $sections['cta'] ?? [];
$heroExtra = json_decode((string) ($hero['extra_json'] ?? ''), true) ?: [];
$aboutExtra = json_decode((string) ($about['extra_json'] ?? ''), true) ?: [];
$coursesExtra = json_decode((string) ($courses['extra_json'] ?? ''), true) ?: [];
$activitiesExtra = json_decode((string) ($activities['extra_json'] ?? ''), true) ?: [];
$galleryExtra = json_decode((string) ($gallery['extra_json'] ?? ''), true) ?: [];
$ctaExtra = json_decode((string) ($cta['extra_json'] ?? ''), true) ?: [];
$pillarsExtra = json_decode((string) ($pillars['extra_json'] ?? ''), true) ?: [];
$pillarItems = is_array($pillarsExtra['items'] ?? null)
    ? $pillarsExtra['items']
    : [
        ['title' => 'Shahadah', 'meaning' => 'Faith'],
        ['title' => 'Salah', 'meaning' => 'Prayer'],
        ['title' => 'Sawm', 'meaning' => 'Fasting'],
        ['title' => 'Zakat', 'meaning' => 'Almsgiving'],
        ['title' => 'Hajj', 'meaning' => 'Pilgrimage'],
    ];
$aboutPoints = is_array($aboutExtra['points'] ?? null) ? $aboutExtra['points'] : [
    'Qur’an, Sunnah, and Islamic character as the foundation',
    'Contemporary learning, technology, and practical skills',
    'Youth, families, and a life of sincere service',
];
while (count($aboutPoints) < 3) {
    $aboutPoints[] = '';
}
$embedInPage = !empty($embedInPage);
if (!$embedInPage):
$kicker = 'Homepage';
$title = 'Home page content';
$tag = 'h1';
$lead = 'Every heading, gold tag, button, and preview text on the homepage is edited here.';
$align = 'left';
$light = false;
require APP_PATH . '/Views/components/section-head.php';
endif;
?>
<form class="form stack-form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/home')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($page)) { require APP_PATH . '/Views/admin/partials/page-identity-fields.php'; } ?>

    <h2>Hero</h2>
    <div class="field"><label>Arabic line</label><input name="hero_arabic" value="<?= e((string) ($heroExtra['arabic'] ?? 'بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ')) ?>"></div>
    <div class="field"><label>Gold tag</label><input name="subtitle[hero]" value="<?= e($hero['subtitle'] ?? '') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[hero]" value="<?= e($hero['title'] ?? '') ?>"></div>
    <div class="field"><label>Supporting text</label><textarea name="content[hero]" rows="4"><?= e($hero['content'] ?? '') ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>First button label</label><input name="hero_cta_label" value="<?= e($heroExtra['cta_label'] ?? '') ?>"></div>
        <div class="field"><label>First button URL</label><input name="hero_cta_url" value="<?= e($heroExtra['cta_url'] ?? '/courses') ?>"></div>
    </div>
    <div class="row-2">
        <div class="field"><label>Second button label</label><input name="hero_cta2_label" value="<?= e($heroExtra['cta2_label'] ?? 'Visit the Center') ?>"></div>
        <div class="field"><label>Second button URL</label><input name="hero_cta2_url" value="<?= e($heroExtra['cta2_url'] ?? '/contact-us') ?>"></div>
    </div>
    <div class="field">
        <label>Hero image (replaces the video background)</label>
        <div class="image-field">
            <div class="image-preview">
                <?php if (!empty($hero['image'])): ?>
                    <img src="<?= e(upload_url($hero['image'])) ?>" alt="">
                <?php else: ?>
                    No picture yet
                <?php endif; ?>
            </div>
            <div>
                <input type="file" name="image_hero" accept="image/jpeg,image/png,image/webp,image/gif">
                <p class="help">Upload a picture to use instead of the video. Leave empty to keep the current picture.</p>
            </div>
        </div>
    </div>

    <h2>About preview</h2>
    <div class="field"><label>Gold tag</label><input name="subtitle[about_preview]" value="<?= e($about['subtitle'] ?? '') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[about_preview]" value="<?= e($about['title'] ?? '') ?>"></div>
    <div class="field"><label>Text</label><textarea name="content[about_preview]" rows="4"><?= e($about['content'] ?? '') ?></textarea></div>
    <?php foreach ($aboutPoints as $i => $point): ?>
        <div class="field"><label>Bullet <?= $i + 1 ?></label><input name="about_point[]" value="<?= e((string) $point) ?>"></div>
    <?php endforeach; ?>
    <div class="field"><label>Button label</label><input name="about_cta_label" value="<?= e((string) ($aboutExtra['cta_label'] ?? 'Learn More')) ?>"></div>
    <div class="field">
        <label>Image</label>
        <div class="image-field">
            <div class="image-preview">
                <?php if (!empty($about['image'])): ?>
                    <img src="<?= e(upload_url($about['image'])) ?>" alt="">
                <?php else: ?>
                    No picture yet
                <?php endif; ?>
            </div>
            <div>
                <input type="file" name="image_about_preview" accept="image/jpeg,image/png,image/webp,image/gif">
                <p class="help">JPG, PNG or WebP.</p>
            </div>
        </div>
    </div>

    <h2>Center activities heading</h2>
    <div class="field"><label>Gold tag</label><input name="subtitle[programs_intro]" value="<?= e($programs['subtitle'] ?? 'What We Offer') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[programs_intro]" value="<?= e($programs['title'] ?? 'Center Activities') ?>"></div>
    <p class="help">Center Activities shows three compact cards: icon and heading only. Edit them in Center programs.</p>

    <h2>Pillars of Islam</h2>
    <div class="field"><label>Gold tag</label><input name="subtitle[pillars]" value="<?= e($pillars['subtitle'] ?? 'About Essential') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[pillars]" value="<?= e($pillars['title'] ?? 'Pillars of Islam') ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="content[pillars]" rows="2"><?= e($pillars['content'] ?? 'The five foundations of faith, presented for visitors of the center.') ?></textarea></div>
    <?php foreach ($pillarItems as $item): ?>
        <div class="row-2">
            <div class="field"><label>Name</label><input name="pillar_title[]" value="<?= e((string) ($item['title'] ?? '')) ?>"></div>
            <div class="field"><label>Meaning</label><input name="pillar_meaning[]" value="<?= e((string) ($item['meaning'] ?? '')) ?>"></div>
        </div>
    <?php endforeach; ?>

    <h2>Courses heading</h2>
    <div class="field"><label>Gold tag</label><input name="subtitle[courses_intro]" value="<?= e($courses['subtitle'] ?? 'Education') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[courses_intro]" value="<?= e($courses['title'] ?? 'Courses') ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="content[courses_intro]" rows="2"><?= e($courses['content'] ?? 'Online and on-site programs managed from the Admin Panel.') ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>All-courses button</label><input name="courses_more_label" value="<?= e((string) ($coursesExtra['more_label'] ?? 'View all courses')) ?>"></div>
        <div class="field"><label>Button URL</label><input name="courses_more_url" value="<?= e((string) ($coursesExtra['more_url'] ?? '/courses')) ?>"></div>
    </div>

    <h2>Social activities heading</h2>
    <div class="field"><label>Gold tag</label><input name="subtitle[activities_intro]" value="<?= e($activities['subtitle'] ?? 'Community') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[activities_intro]" value="<?= e($activities['title'] ?? 'Social Activities') ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="content[activities_intro]" rows="2"><?= e($activities['content'] ?? 'Civic and community programs published from the Admin Panel.') ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>All-activities button</label><input name="activities_more_label" value="<?= e((string) ($activitiesExtra['more_label'] ?? 'All social activities')) ?>"></div>
        <div class="field"><label>Button URL</label><input name="activities_more_url" value="<?= e((string) ($activitiesExtra['more_url'] ?? '/social-activities')) ?>"></div>
    </div>

    <h2>Gallery heading</h2>
    <div class="field"><label>Gold tag</label><input name="subtitle[gallery_intro]" value="<?= e($gallery['subtitle'] ?? 'Moments') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[gallery_intro]" value="<?= e($gallery['title'] ?? 'Gallery') ?>"></div>
    <div class="field"><label>Introduction</label><textarea name="content[gallery_intro]" rows="2"><?= e($gallery['content'] ?? 'Photographs from the life of the center.') ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>Full-gallery button</label><input name="gallery_more_label" value="<?= e((string) ($galleryExtra['more_label'] ?? 'View Full Gallery')) ?>"></div>
        <div class="field"><label>Button URL</label><input name="gallery_more_url" value="<?= e((string) ($galleryExtra['more_url'] ?? '/gallery')) ?>"></div>
    </div>

    <h2>Closing invitation</h2>
    <div class="field"><label>Gold tag</label><input name="subtitle[cta]" value="<?= e($cta['subtitle'] ?? '') ?>"></div>
    <div class="field"><label>Heading</label><input name="title[cta]" value="<?= e($cta['title'] ?? '') ?>"></div>
    <div class="field"><label>Text</label><textarea name="content[cta]" rows="3"><?= e($cta['content'] ?? '') ?></textarea></div>
    <div class="row-2">
        <div class="field"><label>Button label</label><input name="cta_label" value="<?= e($ctaExtra['cta_label'] ?? '') ?>"></div>
        <div class="field"><label>Button URL</label><input name="cta_url" value="<?= e($ctaExtra['cta_url'] ?? '/contact-us') ?>"></div>
    </div>
    <p class="help">Featured courses, activities, and gallery images use the Featured checkbox on those records. Program cards are in Center programs.</p>
    <button class="btn btn-walnut page-save" type="submit">Save page</button>
</form>

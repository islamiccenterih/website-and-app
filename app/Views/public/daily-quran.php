<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('daily_quran', 'kicker', 'Today’s recitation');
        $title = page_copy('daily_quran', 'title', 'Daily Quran');
        $tag = 'h1';
        $lead = page_copy('daily_quran', 'lead', 'An ayah for today, a short tafsir, and a hadith — new each morning (India time). Share it on WhatsApp.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page" data-daily-quran
        data-api="<?= e(url('/api/faith/daily')) ?>"
        data-ayah-kicker="<?= e(page_copy('daily_quran', 'ayah_kicker', 'Ayah of the day')) ?>"
        data-hadith-kicker="<?= e(page_copy('daily_quran', 'hadith_kicker', 'Hadith of the day')) ?>">
        <p class="ft-live-note" data-dq-date>Loading today’s ayah…</p>
        <article class="ft-panel ft-ayah-card">
            <div class="ft-dua-head">
                <p class="ft-kicker" data-dq-ayah-kicker><?= e(page_copy('daily_quran', 'ayah_kicker', 'Ayah of the day')) ?></p>
                <button class="ft-play-btn" type="button" data-dq-play data-ayah="<?= e((string) ($fallbackAyah['ayah'] ?? '')) ?>">Play</button>
            </div>
            <p class="ft-ar" lang="ar" dir="rtl" data-dq-ar><?= e((string) ($fallbackAyah['arabic'] ?? '')) ?></p>
            <div class="ft-lang" data-dq-en-box>
                <span>English</span>
                <p class="ft-tr" data-dq-en><?= e((string) ($fallbackAyah['english'] ?? '')) ?></p>
            </div>
            <div class="ft-lang" lang="ur" dir="rtl" data-dq-ur-box hidden>
                <span>اردو</span>
                <p class="ft-ur" data-dq-ur></p>
            </div>
            <div class="ft-lang" lang="hi" data-dq-hi-box hidden>
                <span>हिन्दी</span>
                <p class="ft-hi" data-dq-hi><?= e((string) ($fallbackAyah['hindi'] ?? '')) ?></p>
            </div>
            <div class="ft-lang" data-dq-tf-en-box hidden>
                <span>Tafsir · English</span>
                <p class="ft-tafsir" data-dq-tafsir-en></p>
            </div>
            <div class="ft-lang" lang="ur" dir="rtl" data-dq-tf-ur-box hidden>
                <span>تفسیر · اردو</span>
                <p class="ft-tafsir" data-dq-tafsir-ur></p>
            </div>
            <p class="ft-ref" data-dq-ref><?= e(trim((string) (($fallbackAyah['surah'] ?? '') . ' ' . ($fallbackAyah['ayah'] ?? '')))) ?></p>
            <button class="btn btn-gold" type="button" data-dq-share-ayah>Share ayah on WhatsApp</button>
        </article>
        <article class="ft-panel">
            <p class="ft-kicker"><?= e(page_copy('daily_quran', 'hadith_kicker', 'Hadith of the day')) ?></p>
            <p class="ft-ar" lang="ar" dir="rtl" data-dq-hadith-ar></p>
            <p class="ft-en" data-dq-hadith-en></p>
            <p class="ft-ref" data-dq-hadith-src></p>
            <button class="btn btn-outline" type="button" data-dq-share-hadith>Share hadith on WhatsApp</button>
        </article>
    </div>
</section>
<script type="application/json" data-dq-hadith><?= json_encode($hadith ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="<?= e(asset('assets/js/daily-quran.js')) ?>?v=4" defer></script>

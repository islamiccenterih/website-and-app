<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('quran_reader', 'kicker', 'The Book');
        $title = page_copy('quran_reader', 'title', 'Quran Reader');
        $tag = 'h1';
        $lead = page_copy('quran_reader', 'lead', 'Open any surah, search an ayah, and listen. Arabic is the Tanzil Uthmani text.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page" data-quran-root>
        <div class="ft-reader-bar">
            <label>
                <span>Surah</span>
                <select data-quran-surah></select>
            </label>
            <label>
                <span>Search</span>
                <input type="search" data-quran-search placeholder="Ayah number or a word" aria-label="Search ayahs">
            </label>
            <div class="ft-reader-actions">
                <button class="btn btn-gold" type="button" data-quran-play>Play surah</button>
                <button class="btn btn-outline" type="button" data-quran-stop>Stop</button>
            </div>
        </div>
        <p class="ft-help"><?= e(page_copy('quran_reader', 'help', 'Audio is Mishary Rashid Alafasy. Search looks inside the surah you opened.')) ?></p>
        <p class="salah-error" hidden data-quran-error></p>
        <div class="ft-ayah-list" data-quran-ayahs>
            <p class="ft-live-note">Choose a surah to begin.</p>
        </div>
    </div>
</section>
<script src="<?= e(asset('assets/js/quran-reader.js')) ?>?v=4" defer></script>

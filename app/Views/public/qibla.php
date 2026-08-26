<div class="qibla-page" data-qibla-root
    data-api="<?= e(url('/api/qibla')) ?>"
    data-qibla="">
<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('qibla', 'kicker', 'Face the House of Allah');
        $title = page_copy('qibla', 'title', 'Qibla Direction');
        $tag = 'h1';
        $lead = page_copy('qibla', 'lead', 'Hold your phone flat. Allow location and compass. The gold mark turns with you until it points to the Kaaba.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <div class="qibla-start" data-qibla-start>
            <button class="btn btn-gold qibla-start-btn" type="button" data-qibla-compass><?= e(tt('Start compass')) ?></button>
            <p class="qibla-start-hint" data-qibla-start-hint>Pehle yahan tap karein aur Location Allow karein. Kaaba aapki GPS se dikhega.</p>
        </div>
    </div>
</section>
<section class="section section-sand">
    <div class="container">
        <p class="qibla-banner" data-qibla-banner hidden></p>
        <div class="qibla-layout">
            <div class="qibla-compass" aria-label="Qibla compass">
                <div class="qibla-bezel">
                    <button class="qibla-gate" type="button" data-qibla-gate>
                        <strong>Start compass</strong>
                        <span>Upar Start compass par tap karein, ya yahan. Location + Motion allow karein.</span>
                    </button>
                    <div class="qibla-notch" aria-hidden="true"></div>
                    <div class="qibla-dial" data-qibla-dial>
                        <span class="qibla-n">N</span>
                        <span class="qibla-e">E</span>
                        <span class="qibla-s">S</span>
                        <span class="qibla-w">W</span>
                        <div class="qibla-ring" aria-hidden="true"></div>
                        <div class="qibla-kaaba" data-qibla-mark hidden>
                            <span><?= e(ft('Kaaba')) ?></span>
                        </div>
                        <div class="qibla-needle" data-qibla-needle hidden></div>
                    </div>
                    <div class="qibla-hub">
                        <span class="qibla-face-line" data-qibla-face-hint>Tap to start</span>
                        <strong data-qibla-face>—</strong>
                        <em data-qibla-card>—</em>
                    </div>
                </div>
            </div>
            <div class="qibla-meta">
                <p class="qibla-status" data-qibla-status>Pehle upar Start compass dabayein aur Location Allow karein. Kaaba tab aapki asl jagah se set hoga — Firozabad ka rukh aapka Qibla nahi hai.</p>
                <dl class="qibla-stats">
                    <div><dt>Qibla from true north</dt><dd data-qibla-bearing>—</dd></div>
                    <div><dt>Distance to Makkah</dt><dd data-qibla-km>—</dd></div>
                    <div><dt>Your place</dt><dd data-qibla-place>Waiting for GPS…</dd></div>
                </dl>
                <div class="qibla-actions">
                    <button class="btn btn-walnut" type="button" data-qibla-locate><?= e(tt('Use my location')) ?></button>
                    <button class="btn btn-outline" type="button" data-qibla-invert><?= e(tt('Reverse compass')) ?></button>
                </div>
                <p class="help"><?= e(page_copy('qibla', 'help', 'Start compass dabayein, Location Allow karein. Kaaba aapke phone ki GPS se calculate hota hai — Delhi, Mumbai, ya duniya mein kahin. Firozabad sirf center ka pata hai, aapka Qibla nahi. Beech ka number aapki direction hai. Gold notch aap ka rukh hai; Kaaba mark ko uske neeche laayein. Ulta ghumey to Reverse compass.')) ?></p>
            </div>
        </div>
    </div>
</section>
</div>
<script src="<?= e(asset('assets/js/qibla.js')) ?>?v=9" defer></script>

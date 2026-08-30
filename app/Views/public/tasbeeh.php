<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('tasbeeh', 'kicker', 'Dhikr');
        $title = page_copy('tasbeeh', 'title', 'Daily Tasbeeh');
        $tag = 'h1';
        $lead = page_copy('tasbeeh', 'lead', 'Tap the circle. After every 100, a completed count is added. Reset clears everything on this phone.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page" data-tasbeeh-root>
        <div class="ft-tasbeeh">
            <button class="ft-tasbeeh-bead" type="button" data-tasbeeh-tap aria-label="Add one">
                <span data-tasbeeh-count>0</span>
                <em>this hundred</em>
            </button>
            <div class="ft-tasbeeh-sets">
                <span>Completed hundreds</span>
                <strong data-tasbeeh-sets>0</strong>
            </div>
            <div class="ft-tasbeeh-row">
                <label class="ft-check"><input type="checkbox" data-tasbeeh-vibrate checked> Vibration</label>
                <button class="btn btn-outline" type="button" data-tasbeeh-undo>Undo</button>
                <button class="btn btn-outline" type="button" data-tasbeeh-reset>Reset</button>
            </div>
            <p class="ft-help"><?= e(page_copy('tasbeeh', 'help', '100 taps = 1 on the counter. 200 = 2, 300 = 3. The bead starts a new hundred after 100.')) ?></p>
        </div>
    </div>
</section>
<script src="<?= e(asset('assets/js/tasbeeh.js')) ?>?v=2" defer></script>

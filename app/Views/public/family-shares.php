<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('family_shares', 'kicker', 'Mirath');
        $title = page_copy('family_shares', 'title', 'Family Shares');
        $tag = 'h1';
        $lead = page_copy('family_shares', 'lead', 'After someone dies, who receives what? Enter the estate and the close family. / किसी के बाद कितना हिस्सा — जायदाद और परिवार लिखें।');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container ft-page" data-mirath-root>
        <form class="ft-mirath" data-mirath-form>
            <article class="ft-mirath-card">
                <h2>Estate / जायदाद</h2>
                <div class="ft-mirath-estate">
                    <label>Amount in rupees / राशि (₹)
                        <input name="estate" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0">
                    </label>
                </div>
                <p class="ft-help" style="margin:0 0 0.7rem">Who died? / किसका इन्तिकाल हुआ?</p>
                <div class="ft-choice">
                    <button type="button" class="is-on" data-deceased="male">Man / मर्द</button>
                    <button type="button" data-deceased="female">Woman / औरत</button>
                </div>
                <input type="hidden" name="deceased" value="male">
            </article>
            <article class="ft-mirath-card">
                <h2>Close family / क़रीबी परिवार</h2>
                <div class="ft-family">
                    <div class="ft-family-row">
                        <div>
                            <strong>Spouse is alive</strong><span>पति / पत्नी ज़िंदा हैं</span>
                            <input name="spouse" type="checkbox" value="1" hidden>
                        </div>
                        <button class="ft-switch" type="button" data-switch="spouse" aria-label="Spouse"></button>
                    </div>
                    <div class="ft-family-row">
                        <div>
                            <strong>Father is alive</strong><span>पिता ज़िंदा हैं</span>
                            <input name="father" type="checkbox" value="1" hidden>
                        </div>
                        <button class="ft-switch" type="button" data-switch="father" aria-label="Father"></button>
                    </div>
                    <div class="ft-family-row">
                        <div>
                            <strong>Mother is alive</strong><span>माँ ज़िंदा हैं</span>
                            <input name="mother" type="checkbox" value="1" hidden>
                        </div>
                        <button class="ft-switch" type="button" data-switch="mother" aria-label="Mother"></button>
                    </div>
                    <div class="ft-family-row">
                        <div><strong>Sons / बेटे</strong><span>How many sons</span></div>
                        <div class="ft-stepper">
                            <button type="button" data-step="sons" data-dir="-1" aria-label="Fewer sons">−</button>
                            <input name="sons" type="number" min="0" step="1" value="0">
                            <button type="button" data-step="sons" data-dir="1" aria-label="More sons">+</button>
                        </div>
                    </div>
                    <div class="ft-family-row">
                        <div><strong>Daughters / बेटियाँ</strong><span>How many daughters</span></div>
                        <div class="ft-stepper">
                            <button type="button" data-step="daughters" data-dir="-1" aria-label="Fewer daughters">−</button>
                            <input name="daughters" type="number" min="0" step="1" value="0">
                            <button type="button" data-step="daughters" data-dir="1" aria-label="More daughters">+</button>
                        </div>
                    </div>
                    <div class="ft-family-row">
                        <div><strong>Full brothers / भाई</strong><span>Only if no son and no father</span></div>
                        <div class="ft-stepper">
                            <button type="button" data-step="brothers" data-dir="-1" aria-label="Fewer brothers">−</button>
                            <input name="brothers" type="number" min="0" step="1" value="0">
                            <button type="button" data-step="brothers" data-dir="1" aria-label="More brothers">+</button>
                        </div>
                    </div>
                    <div class="ft-family-row">
                        <div><strong>Full sisters / बहनें</strong><span>Only if no son and no father</span></div>
                        <div class="ft-stepper">
                            <button type="button" data-step="sisters" data-dir="-1" aria-label="Fewer sisters">−</button>
                            <input name="sisters" type="number" min="0" step="1" value="0">
                            <button type="button" data-step="sisters" data-dir="1" aria-label="More sisters">+</button>
                        </div>
                    </div>
                </div>
            </article>
            <button class="btn btn-walnut" type="submit">Show shares / हिस्सा देखें</button>
        </form>
        <div class="ft-mirath-card zakat-result" data-mirath-result hidden>
            <p class="zakat-due" data-mirath-due></p>
            <div class="ft-shares" data-mirath-lines></div>
        </div>
        <aside class="zakat-note">
            <h2><?= e(page_copy('family_shares', 'notes_title', 'How to use this')) ?></h2>
            <p><?= e(page_copy('family_shares', 'notes', 'This is a Hanafi estimate for common cases — not a fatwa. Ask a teacher at the center before you divide property. / यह आम हनफ़ी अंदाज़ा है, फ़तवा नहीं। जायदाद बाँटने से पहले केंद्र के उस्ताद से पूछें।')) ?></p>
        </aside>
    </div>
</section>
<script src="<?= e(asset('assets/js/family-shares.js')) ?>?v=2" defer></script>

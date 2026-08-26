<?php
$spot = is_array($spot ?? null) ? $spot : [];
$fmt = static function (float $n): string {
    return '₹' . number_format($n, 2);
};
?>
<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('zakat', 'kicker', 'Purify what you keep');
        $title = page_copy('zakat', 'title', 'Zakat Calculator');
        $tag = 'h1';
        $lead = page_copy('zakat', 'lead', 'Enter gold, silver, cash, business stock, and debts. Nisab updates itself from today’s metal prices. Zakat is 2.5% of what is above nisab.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container zakat-page" data-zakat-root
        data-nisab-url="<?= e(url('/api/zakat/nisab')) ?>"
        data-calc-url="<?= e(url('/api/zakat/calculate')) ?>">
        <div class="zakat-nisab">
            <article>
                <span><?= e(ft('Gold nisab')) ?> (<?= e((string) ($spot['gold_nisab_g'] ?? '87.48')) ?> g · 7.5 tola)</span>
                <strong data-gold-nisab><?= e($fmt((float) ($spot['gold_nisab_inr'] ?? 0))) ?></strong>
                <em data-gold-10g><?= e($fmt((float) ($spot['gold_per_10g_inr'] ?? 0))) ?> / 10g 24k</em>
            </article>
            <article>
                <span><?= e(ft('Silver nisab')) ?> (<?= e((string) ($spot['silver_nisab_g'] ?? '612.36')) ?> g · 52.5 tola)</span>
                <strong data-silver-nisab><?= e($fmt((float) ($spot['silver_nisab_inr'] ?? 0))) ?></strong>
                <em data-silver-kg><?= e($fmt((float) ($spot['silver_per_kg_inr'] ?? 0))) ?> / kg</em>
            </article>
            <article>
                <span>Today’s rates</span>
                <strong data-spot-date><?= e((string) ($spot['for_date'] ?? '')) ?></strong>
                <em data-spot-note><?= !empty($spot['stale']) ? 'Last saved rates' : 'Live spot · India (INR)' ?></em>
            </article>
        </div>
        <?php if (!empty($spot['error'])): ?>
            <p class="salah-error" data-zakat-error><?= e((string) $spot['error']) ?></p>
        <?php else: ?>
            <p class="salah-error" hidden data-zakat-error></p>
        <?php endif; ?>

        <form class="zakat-form" data-zakat-form>
            <div class="zakat-grid">
                <label>Gold (grams)<input name="gold_grams" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Gold karat
                    <select name="gold_karat">
                        <option value="24" selected>24k</option>
                        <option value="22">22k</option>
                        <option value="21">21k</option>
                        <option value="18">18k</option>
                    </select>
                </label>
                <label>Silver (grams)<input name="silver_grams" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Cash in hand (₹)<input name="cash" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Bank and savings (₹)<input name="bank" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Business stock (₹)<input name="business" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Money owed to you (₹)<input name="receivables" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Shares and funds (₹)<input name="investments" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Crypto and digital (₹)<input name="crypto" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Other zakatable wealth (₹)<input name="other" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
                <label>Debts you must pay now (₹)<input name="debts" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0"></label>
            </div>
            <button class="btn btn-walnut" type="submit"><?= e(ft('Calculate zakat')) ?></button>
        </form>

        <div class="zakat-result" data-zakat-result hidden>
            <p class="zakat-due" data-zakat-due></p>
            <ul class="event-list" data-zakat-lines></ul>
        </div>

        <aside class="zakat-note">
            <h2><?= e(page_copy('zakat', 'notes_title', 'How this is worked out')) ?></h2>
            <p><?= e(page_copy('zakat', 'notes', $spot['notes'] ?: 'Nisab is 87.48 g of gold or 612.36 g of silver. This page uses the lower of the two (the usual Hanafi practice in India) unless the administration changes it. The rate is 2.5% of net zakatable wealth held for one lunar year. Jewellery is valued by the gold or silver it contains, not by making charges. Livestock, crops, and minerals follow other rules — ask a teacher at the center if that is your case.')) ?></p>
        </aside>
    </div>
</section>
<script src="<?= e(asset('assets/js/zakat.js')) ?>?v=2" defer></script>

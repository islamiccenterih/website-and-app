<?php
$prayer = $prayer ?? ['ok' => false, 'prayers' => [], 'city' => 'Firozabad', 'state' => 'Uttar Pradesh'];
$prayers = is_array($prayer['prayers'] ?? null) ? $prayer['prayers'] : [];
if (!$prayers) {
    $prayers = [
        ['key' => 'fajr', 'name' => 'Fajr', 'time' => '—'],
        ['key' => 'zuhr', 'name' => 'Zuhr', 'time' => '—'],
        ['key' => 'asr', 'name' => 'Asr', 'time' => '—'],
        ['key' => 'maghrib', 'name' => 'Maghrib', 'time' => '—'],
        ['key' => 'isha', 'name' => 'Isha', 'time' => '—'],
        ['key' => 'jummah', 'name' => 'Jummah', 'time' => '—'],
    ];
}
$current = $prayer['current'] ?? null;
$icon = '<svg viewBox="0 0 31 30" aria-hidden="true"><path d="M25 15.3A9.5 9.5 0 1 0 6.2 17.2A24 24 0 0 0 .7 19.2c.2.6.4 1.1.6 1.6A14.8 14.8 0 0 0 15.5 30a14.8 14.8 0 0 0 14.1-9.2c.2-.5.4-1.1.6-1.6a24 24 0 0 0-5.4-2 9.3 9.3 0 0 0 .2-3.9zm-17.3 0a7.8 7.8 0 1 1 15.6 0 20 20 0 0 1-15.6 1.5 7 7 0 0 1 0-1.5z" fill="currentColor"/><path d="M7.5 6.9 4.6 4 3.4 5.2l3 3c.3-.5.7-.9 1.1-1.3zm20.5-1.7-1.2-1.2-3.2 3.2c.4.4.8.8 1.1 1.3zM16.3 3.8V0h-1.7v3.8c.3 0 .6 0 .8 0 .3 0 .6 0 .9 0zM3.9 15.4c0-.3 0-.6 0-1H0v1.7h3.9c0-.2 0-.5 0-.7zm23.1-1c0 .3 0 .6 0 1 0 .2 0 .5 0 .7H31v-1.7z" fill="currentColor"/></svg>';
?>
<section class="section salah-sec" id="prayer-times" data-prayer-root
    data-city="<?= e((string) ($prayer['city'] ?? 'Firozabad')) ?>"
    data-state="<?= e((string) ($prayer['state'] ?? 'Uttar Pradesh')) ?>"
    data-date="<?= e((string) ($prayer['for_date'] ?? '')) ?>"
    data-api="<?= e(url('/api/prayer-times')) ?>"
    data-cities="<?= e(asset('assets/data/india-cities.json')) ?>">
    <div class="container">
        <?php
        $kicker = page_copy('salah', 'kicker', 'Time');
        $title = page_copy('salah', 'title', 'Prayer Times (Salah Timings)');
        $tag = 'h2';
        $lead = page_copy('salah', 'lead', 'Stay connected with your daily prayers. Choose a city in India to see today’s namaz times.');
        $align = 'center';
        $light = false;
        require APP_PATH . '/Views/components/section-head.php';
        ?>

        <div class="salah-toolbar">
            <div class="salah-city" data-city-picker>
                <button class="salah-city-btn" type="button" data-city-toggle aria-expanded="false" aria-haspopup="listbox">
                    <span>
                        <em>Select your city</em>
                        <strong data-city-label><?= e(($prayer['city'] ?? 'Firozabad') . ', ' . ($prayer['state'] ?? 'Uttar Pradesh')) ?></strong>
                    </span>
                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M5 7l5 6 5-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
                <div class="salah-city-panel" hidden data-city-panel>
                    <input type="search" data-city-search placeholder="Search city or state" autocomplete="off" aria-label="Search city">
                    <ul data-city-list role="listbox"></ul>
                    <p class="salah-city-empty" hidden data-city-empty>No city matches that search.</p>
                </div>
            </div>
            <p class="salah-meta" data-prayer-meta>
                <?= e((string) ($prayer['weekday'] ?? '')) ?><?= !empty($prayer['date']) ? ' · ' . e((string) $prayer['date']) : '' ?>
                · Confirming live times…
            </p>
        </div>

        <div class="salah-error" data-prayer-error <?= empty($prayer['ok']) && !empty($prayer['error']) ? '' : 'hidden' ?>>
            <?= e((string) ($prayer['error'] ?? '')) ?>
        </div>

        <div class="salah-grid" data-prayer-grid>
            <?php foreach ($prayers as $item): ?>
                <article class="salah-card<?= ($current && $item['key'] === $current) ? ' is-now' : '' ?>" data-prayer-card="<?= e($item['key']) ?>">
                    <div class="salah-icon"><?= $icon ?></div>
                    <h3><?= e(tt($item['name'])) ?></h3>
                    <div class="salah-times">
                        <span>Time</span>
                        <strong data-prayer-time><?= e($item['time']) ?></strong>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
$r = is_array($ramadan ?? null) ? $ramadan : [];
$todayRow = is_array($r['today_row'] ?? null) ? $r['today_row'] : [];
$days = is_array($r['days'] ?? null) ? $r['days'] : [];
$duas = is_array($r['duas'] ?? null) ? $r['duas'] : [];
$start = is_array($r['ramadan_start'] ?? null) ? $r['ramadan_start'] : [];
$isRamadan = !empty($r['is_ramadan']);
$gregToday = (string) ($todayRow['gregorian_label'] ?? '');
$hijriToday = (string) ($todayRow['hijri_label'] ?? '');
$countSec = (int) ($start['seconds'] ?? 0);
$countDays = intdiv($countSec, 86400);
$countHours = intdiv($countSec % 86400, 3600);
$countMins = intdiv($countSec % 3600, 60);
$countSecs = $countSec % 60;
?>
<section class="page-hero ramadan-hero<?= $isRamadan ? ' is-live' : '' ?>">
    <div class="ramadan-sky" aria-hidden="true">
        <span class="ramadan-star s1"></span>
        <span class="ramadan-star s2"></span>
        <span class="ramadan-star s3"></span>
        <span class="ramadan-crescent"></span>
        <span class="ramadan-lantern l1"></span>
        <span class="ramadan-lantern l2"></span>
        <span class="ramadan-lantern l3"></span>
    </div>
    <div class="container">
        <?php
        $kicker = page_copy('ramadan', 'kicker', $isRamadan ? 'Ramadan Mubarak' : 'Prepare for the blessed month');
        $title = page_copy('ramadan', 'title', 'Ramadan Mode');
        $tag = 'h1';
        $lead = page_copy('ramadan', 'lead', 'Sehri and Iftar for every city in India, a full roza calendar, and the duas recited at the table and in the night prayer.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
        <p class="ramadan-flag" data-ramadan-flag><?= e((string) ($r['next_ramadan_label'] ?? '')) ?></p>
    </div>
</section>
<section class="section ramadan-sec" data-ramadan-root
    data-city="<?= e((string) ($r['city'] ?? 'Firozabad')) ?>"
    data-state="<?= e((string) ($r['state'] ?? 'Uttar Pradesh')) ?>"
    data-is-ramadan="<?= $isRamadan ? '1' : '0' ?>"
    data-api="<?= e(url('/api/ramadan')) ?>"
    data-cities="<?= e(asset('assets/data/india-cities.json')) ?>">
    <div class="container">
        <div class="salah-toolbar">
            <div class="salah-city" data-city-picker>
                <button class="salah-city-btn" type="button" data-city-toggle aria-expanded="false" aria-haspopup="listbox">
                    <span>
                        <em>Select your city</em>
                        <strong data-city-label><?= e(($r['city'] ?? 'Firozabad') . ', ' . ($r['state'] ?? 'Uttar Pradesh')) ?></strong>
                    </span>
                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M5 7l5 6 5-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
                <div class="salah-city-panel" hidden data-city-panel>
                    <input type="search" data-city-search placeholder="Search city or state" autocomplete="off" aria-label="Search city">
                    <ul data-city-list role="listbox"></ul>
                    <p class="salah-city-empty" hidden data-city-empty>No city matches that search.</p>
                </div>
            </div>
            <p class="salah-meta" data-ramadan-meta>
                Today · <?= e($gregToday) ?> · <?= e($hijriToday) ?>
            </p>
        </div>

        <div class="salah-error" data-ramadan-error <?= empty($r['ok']) && !empty($r['error']) ? '' : 'hidden' ?>>
            <?= e((string) ($r['error'] ?? '')) ?>
        </div>

        <div class="si-intro">
            <p class="si-kicker">Today</p>
            <h2><?= e(ft('Sehri ends & Iftar')) ?></h2>
            <p data-today-dates><?= e($gregToday) ?> · <?= e($hijriToday) ?></p>
        </div>

        <div class="si-board">
            <article class="si-card sehri">
                <div class="si-card-head">
                    <span class="si-label"><?= e(ft('Sehri ends')) ?></span>
                    <span class="si-icon" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none"><circle cx="16" cy="22" r="10" fill="#c9a227"/><path d="M28 12c4 4 6 9 6 14s-2 10-6 14c8-2 14-9 14-16S36 14 28 12z" fill="#f4ead6" opacity=".9"/></svg>
                    </span>
                </div>
                <p class="si-time" data-sehri-time><?= e((string) ($todayRow['fajr'] ?? '—')) ?></p>
                <p class="si-sub"><?= e(ft('Fajr')) ?> · stop eating</p>
                <dl class="si-dates">
                    <div>
                        <dt>English calendar</dt>
                        <dd data-greg-date><?= e($gregToday !== '' ? $gregToday : '—') ?></dd>
                    </div>
                    <div>
                        <dt><?= e(ft('Islamic calendar')) ?></dt>
                        <dd data-hijri-date><?= e($hijriToday !== '' ? $hijriToday : '—') ?></dd>
                    </div>
                </dl>
                <p class="si-imsak" data-imsak-time><?= e(ft('Imsak')) ?> <?= e((string) ($todayRow['imsak'] ?? '—')) ?></p>
                <p class="si-live" data-sehri-live></p>
            </article>
            <article class="si-card iftar">
                <div class="si-card-head">
                    <span class="si-label"><?= e(ft('Iftar')) ?></span>
                    <span class="si-icon" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none"><path d="M24 8l3 9h9l-7 5 3 9-8-6-8 6 3-9-7-5h9z" fill="#c9a227"/><rect x="22" y="30" width="4" height="10" fill="#f4ead6"/><path d="M14 42h20" stroke="#c9a227" stroke-width="2"/></svg>
                    </span>
                </div>
                <p class="si-time" data-iftar-time><?= e((string) ($todayRow['maghrib'] ?? '—')) ?></p>
                <p class="si-sub"><?= e(ft('Maghrib')) ?> · open the fast</p>
                <dl class="si-dates">
                    <div>
                        <dt>English calendar</dt>
                        <dd data-iftar-greg><?= e($gregToday !== '' ? $gregToday : '—') ?></dd>
                    </div>
                    <div>
                        <dt><?= e(ft('Islamic calendar')) ?></dt>
                        <dd data-iftar-hijri><?= e($hijriToday !== '' ? $hijriToday : '—') ?></dd>
                    </div>
                </dl>
                <p class="si-imsak" data-iftar-place>Sunset in <?= e((string) ($r['city'] ?? 'Firozabad')) ?></p>
                <p class="si-live" data-iftar-live></p>
            </article>
        </div>

        <div class="ramadan-count-panel<?= $isRamadan ? ' is-live' : '' ?>"
             data-ramadan-count
             data-start-unix="<?= (int) ($start['unix'] ?? 0) ?>"
             data-is-ramadan="<?= $isRamadan ? '1' : '0' ?>"
             data-remaining="<?= (int) ($r['remaining_rozas'] ?? 0) ?>">
            <p class="si-kicker" data-count-kicker><?= e(ft($isRamadan ? 'The blessed month' : 'Until Ramadan')) ?></p>
            <h2 data-count-title><?= e(ft($isRamadan ? 'Ramadan is here' : 'Ramadan begins in')) ?></h2>
            <div class="count-units" data-count-units <?= $isRamadan ? 'hidden' : '' ?>>
                <div><strong data-c-days><?= (int) $countDays ?></strong><span>Days</span></div>
                <div><strong data-c-hours><?= (int) $countHours ?></strong><span>Hours</span></div>
                <div><strong data-c-mins><?= (int) $countMins ?></strong><span>Minutes</span></div>
                <div><strong data-c-secs><?= (int) $countSecs ?></strong><span>Seconds</span></div>
            </div>
            <p class="count-live-note" data-count-note <?= $isRamadan ? '' : 'hidden' ?>>
                <?= $isRamadan ? 'Day ' . (int) ($todayRow['hijri_day'] ?? 0) . ' of Ramadan · ' . (int) ($r['remaining_rozas'] ?? 0) . ' fasts remain, including today.' : '' ?>
            </p>
            <div class="count-dates">
                <div>
                    <span>English calendar</span>
                    <strong data-start-greg><?= e((string) ($start['gregorian_label'] ?? '—')) ?></strong>
                </div>
                <div>
                    <span><?= e(ft('Islamic calendar')) ?></span>
                    <strong data-start-hijri><?= e((string) ($start['hijri_label'] ?? '—')) ?></strong>
                </div>
            </div>
        </div>

        <div class="ramadan-cal-wrap">
            <?php
            $kicker = page_copy('ramadan', 'calendar_kicker', 'The month');
            $title = page_copy('ramadan', 'calendar_title', 'Roza calendar');
            $tag = 'h2';
            $lead = page_copy('ramadan', 'calendar_lead', 'Every fast of Ramadan with Sehri (Fajr) and Iftar (Maghrib) for the city you chose.');
            $align = 'center';
            $light = false;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <div class="ramadan-cal" data-roza-cal>
                <?php if (!$days): ?>
                    <div class="empty-state" style="grid-column:1/-1"><h3>Roza calendar will appear here</h3><p>The Ramadan timetable for this city is still loading, or could not be fetched just now.</p></div>
                <?php endif; ?>
                <?php foreach ($days as $day): ?>
                    <article class="roza-day<?= !empty($day['is_today']) ? ' is-today' : '' ?>">
                        <span class="roza-num"><?= (int) $day['hijri_day'] ?></span>
                        <strong><?= e((string) $day['weekday']) ?></strong>
                        <em><?= e((string) $day['gregorian_label']) ?></em>
                        <p><span><?= e(ft('Sehri')) ?></span> <?= e((string) $day['fajr']) ?></p>
                        <p><span><?= e(ft('Iftar')) ?></span> <?= e((string) $day['maghrib']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ramadan-duas">
            <?php
            $kicker = page_copy('ramadan', 'duas_kicker', 'Words of the month');
            $title = page_copy('ramadan', 'duas_title', 'Ramadan duas');
            $tag = 'h2';
            $lead = page_copy('ramadan', 'duas_lead', 'Read at Sehri, at Iftar, after Taraweeh, and on Laylat al-Qadr. Arabic, transliteration, and meaning.');
            $align = 'center';
            $light = false;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
            <div class="dua-grid">
                <?php foreach ($duas as $dua): ?>
                    <article class="dua-card">
                        <h3><?= e(ft((string) $dua['title'])) ?></h3>
                        <p class="dua-ar" lang="ar" dir="rtl"><?= e((string) $dua['arabic']) ?></p>
                        <p class="dua-tr"><?= e((string) $dua['translit']) ?></p>
                        <p><?= e((string) $dua['meaning']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<script src="<?= e(asset('assets/js/ramadan.js')) ?>?v=3" defer></script>

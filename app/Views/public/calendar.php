<?php
$cal = is_array($calendar ?? null) ? $calendar : [];
$hy = (int) ($cal['hijri_year'] ?? 0);
$hm = (int) ($cal['hijri_month'] ?? 0);
$prev = $cal['prev'] ?? ['hy' => $hy, 'hm' => max(1, $hm - 1)];
$next = $cal['next'] ?? ['hy' => $hy, 'hm' => min(12, $hm + 1)];
$weeks = is_array($cal['weeks'] ?? null) ? $cal['weeks'] : [];
$weekdays = is_array($cal['weekdays'] ?? null) ? $cal['weekdays'] : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$observances = is_array($cal['observances'] ?? null) ? $cal['observances'] : [];
$monthNames = is_array($cal['months'] ?? null) ? $cal['months'] : [];
$years = is_array($cal['years'] ?? null) ? $cal['years'] : [$hy];
$centerMonths = is_array($months ?? null) ? $months : [];
?>
<section class="page-hero">
    <div class="container">
        <?php
        $kicker = page_copy('calendar', 'kicker', 'Hijri months');
        $title = page_copy('calendar', 'title', 'Islamic Calendar');
        $tag = 'h1';
        $lead = page_copy('calendar', 'lead', 'Today’s Hijri date, the full month, and the days of worship — kept on this site so the calendar stays with the center.');
        $align = 'center';
        $light = true;
        require APP_PATH . '/Views/components/section-head.php';
        ?>
    </div>
</section>
<section class="section section-sand">
    <div class="container">
        <p class="hijri-today">
            <span>Today</span>
            <strong data-hijri-today-label><?= e((string) ($cal['today_label'] ?? '')) ?></strong>
        </p>

        <?php if (!empty($cal['error']) && empty($cal['ok'])): ?>
            <div class="empty-state">
                <h3>Calendar could not be opened</h3>
                <p><?= e((string) $cal['error']) ?></p>
                <p><a class="btn btn-walnut" href="<?= e(url('/islamic-calendar')) ?>">Open this month</a></p>
            </div>
        <?php else: ?>
            <article class="hijri-cal" aria-label="Hijri month calendar">
                <header class="hijri-cal-head">
                    <div class="hijri-cal-nav">
                        <a class="btn btn-outline" href="<?= e(url('/islamic-calendar') . '?hy=' . (int) $prev['hy'] . '&hm=' . (int) $prev['hm']) ?>">Previous</a>
                        <?php if (empty($cal['is_current_month'])): ?>
                            <a class="btn btn-gold" href="<?= e(url('/islamic-calendar')) ?>">Today</a>
                        <?php endif; ?>
                        <a class="btn btn-outline" href="<?= e(url('/islamic-calendar') . '?hy=' . (int) $next['hy'] . '&hm=' . (int) $next['hm']) ?>">Next</a>
                    </div>
                    <p class="hijri-cal-ar" lang="ar" dir="rtl"><?= e((string) ($cal['hijri_month_ar'] ?? '')) ?></p>
                    <h2><?= e(trim((string) ($cal['hijri_month_en'] ?? '') . ' ' . $hy)) ?></h2>
                    <?php if (!empty($cal['gregorian_span'])): ?>
                        <p class="hijri-cal-span"><?= e((string) $cal['gregorian_span']) ?></p>
                    <?php endif; ?>
                    <form class="hijri-jump" method="get" action="<?= e(url('/islamic-calendar')) ?>">
                        <label>
                            <span>Month</span>
                            <select name="hm">
                                <?php foreach ($monthNames as $num => $label): ?>
                                    <option value="<?= (int) $num ?>"<?= (int) $num === $hm ? ' selected' : '' ?>><?= e((string) $label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Year AH</span>
                            <select name="hy">
                                <?php foreach ($years as $year): ?>
                                    <option value="<?= (int) $year ?>"<?= (int) $year === $hy ? ' selected' : '' ?>><?= (int) $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button class="btn btn-walnut" type="submit">Open month</button>
                    </form>
                </header>

                <div class="hijri-grid-wrap">
                    <table class="hijri-grid">
                        <caption class="visually-hidden"><?= e(trim((string) ($cal['hijri_month_en'] ?? 'Hijri month') . ' ' . $hy)) ?></caption>
                        <thead>
                            <tr>
                                <?php foreach ($weekdays as $label): ?>
                                    <th scope="col"><?= e((string) $label) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($weeks as $week): ?>
                                <tr>
                                    <?php foreach ($week as $cell): ?>
                                        <?php if ($cell === null): ?>
                                            <td class="is-empty"></td>
                                        <?php else:
                                            $classes = [];
                                            if (!empty($cell['is_today'])) {
                                                $classes[] = 'is-today';
                                            }
                                            if (!empty($cell['is_friday'])) {
                                                $classes[] = 'is-friday';
                                            }
                                            if (!empty($cell['is_holiday'])) {
                                                $classes[] = 'is-holiday';
                                            }
                                            $title = $cell['gregorian_label'] ?? '';
                                            if (!empty($cell['holidays'])) {
                                                $title = trim($title . ' — ' . implode(', ', $cell['holidays']));
                                            }
                                            ?>
                                            <td class="<?= e(implode(' ', $classes)) ?>" title="<?= e((string) $title) ?>">
                                                <span class="hijri-num"><?= (int) $cell['hijri_day'] ?></span>
                                                <span class="greg-num"><?= (int) $cell['gregorian_day'] ?> <?= e(substr((string) ($cell['gregorian_month_en'] ?? ''), 0, 3)) ?></span>
                                                <?php if (!empty($cell['holidays'])): ?>
                                                    <span class="hijri-mark"><?= e((string) $cell['holidays'][0]) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="hijri-dates">
                <?php
                $kicker = 'This month';
                $title = 'Important dates';
                $tag = 'h2';
                $lead = $observances
                    ? 'Days of worship and the well-known dates in ' . ($cal['hijri_month_en'] ?? 'this month') . '.'
                    : 'No major Islamic dates fall in this month.';
                $align = 'left';
                $light = false;
                require APP_PATH . '/Views/components/section-head.php';
                ?>
                <?php if ($observances): ?>
                    <ul class="event-list">
                        <?php foreach ($observances as $item): ?>
                            <li<?= !empty($item['is_today']) ? ' class="is-today"' : '' ?>>
                                <div class="when">
                                    <?= (int) $item['hijri_day'] ?> <?= e((string) ($cal['hijri_month_en'] ?? '')) ?><br>
                                    <?= e((string) ($item['gregorian_label'] ?? '')) ?>
                                </div>
                                <div>
                                    <strong><?= e((string) $item['title']) ?></strong>
                                    <?php if (!empty($item['is_today'])): ?><span class="pill">Today</span><?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>
        <?php endif; ?>

        <?php if ($centerMonths): ?>
            <div class="hijri-center">
                <?php
                $kicker = 'From the center';
                $title = 'Announced months';
                $tag = 'h2';
                $lead = 'Months and dates published by the administration.';
                $align = 'left';
                $light = false;
                require APP_PATH . '/Views/components/section-head.php';
                ?>
                <?php foreach ($centerMonths as $month): ?>
                    <article class="calendar-frame">
                        <?php if (!empty($month['image_path']) && !str_contains((string) $month['image_path'], 'placeholder')): ?>
                            <img src="<?= e(upload_url($month['image_path'])) ?>" alt="<?= e((string) $month['title']) ?>">
                        <?php endif; ?>
                        <div class="calendar-frame-body">
                            <?php
                            $kicker = trim(($month['hijri_month'] ?? '') . ' ' . ($month['hijri_year'] ?? ''));
                            $title = $month['title'];
                            $tag = 'h3';
                            $lead = $month['gregorian_label'] ?? '';
                            $align = 'left';
                            $light = false;
                            require APP_PATH . '/Views/components/section-head.php';
                            ?>
                            <?php if (!empty($month['notes'])): ?>
                                <p><?= e((string) $month['notes']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($month['events'])): ?>
                                <ul class="event-list">
                                    <?php foreach ($month['events'] as $event): ?>
                                        <li>
                                            <div class="when">
                                                <?= e((string) ($event['hijri_date'] ?: '')) ?><br>
                                                <?= e((string) ($event['gregorian_date'] ?: '')) ?>
                                            </div>
                                            <div>
                                                <strong><?= e((string) $event['title']) ?></strong>
                                                <?php if (!empty($event['is_important'])): ?> <span class="pill">Important</span><?php endif; ?>
                                                <?php if (!empty($event['description'])): ?><p><?= e((string) $event['description']) ?></p><?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<script src="<?= e(asset('assets/js/calendar-live.js')) ?>?v=2" defer></script>

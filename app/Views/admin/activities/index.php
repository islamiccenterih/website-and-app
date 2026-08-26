<?php $cmsKey = 'activities'; require APP_PATH . '/Views/admin/partials/cms-bar.php'; ?>
<div class="dash-top">
    <h1>Social activities</h1>
    <?php if (!empty($sections)): ?>
        <a class="btn btn-walnut" href="<?= e(url('/admin/activities/create')) ?>">Add an activity</a>
    <?php endif; ?>
</div>

<ol class="activity-admin-steps">
    <li><strong>Heading</strong> — a group name on the public page, such as Workshops or Welfare. Each heading is one card below.</li>
    <li><strong>Activity</strong> — one programme that sits under that heading. Use <em>Add activity here</em> on the card it belongs to, so it is never unclear which group you are adding to.</li>
</ol>

<details class="panel-card activity-add-heading"<?= empty($sections) ? ' open' : '' ?>>
    <summary>Add a new heading</summary>
    <p class="help">This name becomes a title on the public Social Activities page. After you save it, add programmes inside that heading’s card — not in a separate list.</p>
    <form class="form stack-form" method="post" action="<?= e(url('/admin/activities/sections')) ?>">
        <?= csrf_field() ?>
        <div class="row-2">
            <div class="field"><label>Heading name</label><input name="name" required placeholder="Workshops"></div>
            <div class="field"><label>Slug</label><input name="slug" placeholder="workshops"></div>
        </div>
        <div class="row-2">
            <div class="field"><label>Gold tag</label><input name="kicker" placeholder="Training"></div>
            <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="<?= e((string) ((int) count($sections))) ?>"></div>
        </div>
        <div class="field"><label>Short introduction</label><textarea name="lead" rows="2" maxlength="400"></textarea></div>
        <div class="field"><label>Status</label>
            <select name="status">
                <option value="published">Published — visitors can see this heading</option>
                <option value="draft">Draft — hidden on the website</option>
            </select>
        </div>
        <button class="btn btn-walnut" type="submit">Save heading</button>
    </form>
</details>

<?php if (empty($groups)): ?>
    <div class="empty-state">
        <h3>No headings yet</h3>
        <p>Open <strong>Add a new heading</strong> above, save a group name, then add the first activity inside that card.</p>
    </div>
<?php endif; ?>

<?php foreach ($groups as $group):
    $section = $group['section'];
    $activities = $group['activities'];
    $sectionId = (int) ($section['id'] ?? 0);
    $count = count($activities);
    $isUnassigned = $sectionId === 0;
    ?>
    <article class="activity-section-card<?= $isUnassigned ? ' is-unassigned' : '' ?>" id="section-<?= $sectionId ?>">
        <header class="activity-section-card__head">
            <div>
                <?php if (!$isUnassigned && trim((string) ($section['kicker'] ?? '')) !== ''): ?>
                    <p class="activity-section-card__kicker"><?= e((string) $section['kicker']) ?></p>
                <?php endif; ?>
                <h2 dir="auto"><?= e($isUnassigned ? 'Not in a heading yet' : (string) $section['name']) ?></h2>
                <p class="activity-section-card__meta">
                    <span><?= $count === 1 ? '1 activity in this heading' : $count . ' activities in this heading' ?></span>
                    <?php if (!$isUnassigned): ?>
                        <span class="badge <?= ($section['status'] ?? '') === 'published' ? 'badge-on' : 'badge-off' ?>"><?= ($section['status'] ?? '') === 'published' ? 'Heading published' : 'Heading draft' ?></span>
                    <?php endif; ?>
                </p>
                <?php if (!$isUnassigned && trim((string) ($section['lead'] ?? '')) !== ''): ?>
                    <p class="activity-section-card__lead"><?= e((string) $section['lead']) ?></p>
                <?php endif; ?>
                <?php if ($isUnassigned): ?>
                    <p class="help">These programmes have no heading. Open Edit on each one and choose which heading they belong to.</p>
                <?php endif; ?>
            </div>
            <?php if (!$isUnassigned): ?>
                <a class="btn btn-walnut btn-sm" href="<?= e(url('/admin/activities/create?section=' . $sectionId)) ?>">Add activity here</a>
            <?php endif; ?>
        </header>

        <?php if (!$isUnassigned): ?>
            <details class="activity-section-settings">
                <summary>Edit this heading’s name, order, or publish state</summary>
                <form class="form stack-form" method="post" action="<?= e(url('/admin/activities/sections/' . $sectionId)) ?>">
                    <?= csrf_field() ?>
                    <div class="row-2">
                        <div class="field"><label>Heading name</label><input name="name" required dir="auto" value="<?= e((string) $section['name']) ?>"></div>
                        <div class="field"><label>Slug</label><input name="slug" value="<?= e((string) $section['slug']) ?>"></div>
                    </div>
                    <div class="row-2">
                        <div class="field"><label>Gold tag</label><input name="kicker" value="<?= e((string) $section['kicker']) ?>"></div>
                        <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="<?= e((string) $section['sort_order']) ?>"></div>
                    </div>
                    <div class="field"><label>Short introduction</label><textarea name="lead" rows="2" maxlength="400"><?= e((string) $section['lead']) ?></textarea></div>
                    <div class="field"><label>Status</label>
                        <select name="status">
                            <option value="published"<?= selected($section['status'], 'published') ?>>Published — visitors can see this heading</option>
                            <option value="draft"<?= selected($section['status'], 'draft') ?>>Draft — hidden on the website</option>
                        </select>
                    </div>
                    <div class="dash-actions">
                        <button class="btn btn-walnut btn-sm" type="submit">Save heading</button>
                    </div>
                </form>
                <?php if ($count > 0): ?>
                    <p class="help">Move or delete the <?= $count === 1 ? 'activity' : $count . ' activities' ?> in this heading before you can delete the heading.</p>
                <?php else: ?>
                    <form method="post" action="<?= e(url('/admin/activities/sections/' . $sectionId . '/delete')) ?>" data-confirm="Delete this heading?">
                        <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete empty heading</button>
                    </form>
                <?php endif; ?>
            </details>
        <?php endif; ?>

        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Image</th><th>Activity in this heading</th><th>Year</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php if (!$activities): ?>
                    <tr><td colspan="5">No activities in this heading yet.<?= $isUnassigned ? '' : ' Use Add activity here.' ?></td></tr>
                <?php endif; ?>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><img class="thumb" src="<?= e(upload_url($activity['main_image'])) ?>" alt=""></td>
                        <td>
                            <strong dir="auto"><?= e((string) $activity['title']) ?></strong><br>
                            <span class="help">/social-activities/<?= e($activity['slug']) ?></span>
                        </td>
                        <td><?= e($activity['event_year'] ?: '') ?></td>
                        <td><span class="badge <?= $activity['status'] === 'published' ? 'badge-on' : 'badge-off' ?>"><?= e($activity['status']) ?></span></td>
                        <td class="dash-actions">
                            <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/activities/' . $activity['id'] . '/edit')) ?>">Edit</a>
                            <form method="post" action="<?= e(url('/admin/activities/' . $activity['id'] . '/delete')) ?>" data-confirm="Delete this activity?">
                                <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
<?php endforeach; ?>

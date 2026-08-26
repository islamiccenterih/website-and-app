<?php $cmsKey = 'calendar'; require APP_PATH . '/Views/admin/partials/cms-bar.php'; ?>
<div class="dash-top">
    <h1>Islamic calendar</h1>
    <a class="btn btn-walnut" href="<?= e(url('/admin/calendar/create')) ?>">Add month</a>
</div>
<p>Only published months appear on the public Islamic Calendar page. Review any extracted text before publishing.</p>
<div class="table-wrap">
<table class="data">
    <thead><tr><th>Title</th><th>Hijri</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$months): ?><tr><td colspan="4">No calendar months yet.</td></tr><?php endif; ?>
    <?php foreach ($months as $month): ?>
        <tr>
            <td><?= e($month['title']) ?><?= $month['is_current'] ? ' · current' : '' ?></td>
            <td><?= e(trim($month['hijri_month'] . ' ' . $month['hijri_year'])) ?></td>
            <td><span class="badge <?= $month['status'] === 'published' ? 'badge-on' : 'badge-off' ?>"><?= e($month['status']) ?></span></td>
            <td class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/calendar/' . $month['id'] . '/edit')) ?>">Edit</a>
                <form method="post" action="<?= e(url('/admin/calendar/' . $month['id'] . '/delete')) ?>" data-confirm="Delete this calendar month?">
                    <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

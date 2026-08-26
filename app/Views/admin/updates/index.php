<?php $cmsKey = 'updates'; require APP_PATH . '/Views/admin/partials/cms-bar.php'; ?>
<div class="dash-top">
    <h1>Center Updates</h1>
    <a class="btn btn-walnut" href="<?= e(url('/admin/updates/create')) ?>">Write today’s update</a>
</div>
<p>Each published update appears on the public <a href="<?= e(url('/center-updates')) ?>">Center Updates</a> page. Compose text, pictures, and video in the editor — the website shows that layout as you wrote it.</p>
<div class="table-wrap">
<table class="data">
    <thead>
        <tr>
            <th>Date</th>
            <th>Title</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($updates)): ?>
        <tr><td colspan="4">No updates yet. Write one for today.</td></tr>
    <?php endif; ?>
    <?php foreach ($updates as $row): ?>
        <tr>
            <td><?= e((string) $row['published_on']) ?></td>
            <td>
                <strong dir="auto"><?= e(ftc((string) $row['title'])) ?></strong><br>
                <span class="help">/center-updates/<?= e((string) $row['slug']) ?></span>
            </td>
            <td><span class="badge <?= $row['status'] === 'published' ? 'badge-on' : 'badge-off' ?>"><?= e((string) $row['status']) ?></span></td>
            <td class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/updates/' . $row['id'] . '/edit')) ?>">Edit</a>
                <?php if ($row['status'] === 'published'): ?>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/center-updates/' . $row['slug'])) ?>">View</a>
                <?php endif; ?>
                <form method="post" action="<?= e(url('/admin/updates/' . $row['id'] . '/delete')) ?>" data-confirm="Delete this update?">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

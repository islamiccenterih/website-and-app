<?php $cmsKey = 'fatawa'; require APP_PATH . '/Views/admin/partials/cms-bar.php'; ?>
<div class="dash-top">
    <h1>Fatawa</h1>
    <a class="btn btn-walnut" href="<?= e(url('/admin/fatawa/create')) ?>">Publish today’s fatwa</a>
</div>
<p>Each published fatwa appears on the public <a href="<?= e(url('/fatawa')) ?>">Fatawa</a> page. Fill Arabic, English, and/or Hindi — empty languages stay hidden. Visitors can ask a question on that fatwa; answer here and it shows under the question.</p>
<?php if (($pendingTotal ?? 0) > 0): ?>
    <p class="alert alert-success"><?= (int) $pendingTotal ?> question<?= (int) $pendingTotal === 1 ? '' : 's' ?> waiting for an answer. Open the fatwa to reply.</p>
<?php endif; ?>
<div class="table-wrap">
<table class="data">
    <thead>
        <tr>
            <th>Date</th>
            <th>Title</th>
            <th>Languages</th>
            <th>Status</th>
            <th>Questions</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$fatawa): ?>
        <tr><td colspan="6">No fatawa yet. Publish one for today.</td></tr>
    <?php endif; ?>
    <?php foreach ($fatawa as $row): ?>
        <?php
        $langs = [];
        if (trim((string) ($row['title_ar'] ?? '')) !== '' || trim((string) ($row['body_ar'] ?? '')) !== '') {
            $langs[] = 'AR';
        }
        if (trim((string) ($row['title_en'] ?? '')) !== '' || trim((string) ($row['body_en'] ?? '')) !== '') {
            $langs[] = 'EN';
        }
        if (trim((string) ($row['title_hi'] ?? '')) !== '' || trim((string) ($row['body_hi'] ?? '')) !== '') {
            $langs[] = 'HI';
        }
        $waiting = (int) ($unanswered[(int) $row['id']] ?? 0);
        ?>
        <tr>
            <td><?= e((string) $row['issued_on']) ?></td>
            <td>
                <strong dir="auto"><?= e(ftc(\App\Models\Fatwa::cardTitle($row))) ?></strong><br>
                <span class="help">/fatawa/<?= e((string) $row['slug']) ?></span>
            </td>
            <td><?= e(implode(' · ', $langs) ?: '—') ?></td>
            <td><span class="badge <?= $row['status'] === 'published' ? 'badge-on' : 'badge-off' ?>"><?= e((string) $row['status']) ?></span></td>
            <td>
                <?php if ($waiting > 0): ?>
                    <span class="badge badge-on"><?= $waiting ?> new</span>
                <?php else: ?>
                    <span class="help">None waiting</span>
                <?php endif; ?>
            </td>
            <td class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/fatawa/' . $row['id'] . '/edit')) ?>">Edit / answer</a>
                <?php if ($row['status'] === 'published'): ?>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/fatawa/' . $row['slug'])) ?>">View</a>
                <?php endif; ?>
                <form method="post" action="<?= e(url('/admin/fatawa/' . $row['id'] . '/delete')) ?>" data-confirm="Delete this fatwa and its questions?">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

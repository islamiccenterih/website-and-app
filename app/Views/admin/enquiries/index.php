<div class="dash-top">
    <h1>Course enquiries</h1>
    <?php if ($enquiries): ?>
        <form id="enquiry-export" class="enquiry-export" method="post" action="<?= e(url('/admin/enquiries/export')) ?>" data-enquiry-export>
            <?= csrf_field() ?>
            <span class="help" data-enquiry-count><?= e(tt('None selected')) ?></span>
            <button class="btn btn-walnut" type="submit" data-enquiry-download><?= e(tt('Export to Excel')) ?></button>
        </form>
    <?php endif; ?>
</div>
<p>Applications sent from a public course page. These stay here — they are not mixed with Contact messages, and students cannot see them. Tick rows and export an Excel file of the ones you need, or use Select all.</p>
<div class="table-wrap">
<table class="data">
    <thead>
        <tr>
            <?php if ($enquiries): ?>
                <th class="check-col">
                    <label class="enquiry-check">
                        <input type="checkbox" form="enquiry-export" data-enquiry-all>
                        <span class="visually-hidden"><?= e(tt('Select all')) ?></span>
                    </label>
                </th>
            <?php endif; ?>
            <th>When</th>
            <th>Name</th>
            <th>Phone / WhatsApp</th>
            <th>Course</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$enquiries): ?><tr><td colspan="6">No course enquiries yet.</td></tr><?php endif; ?>
    <?php foreach ($enquiries as $enquiry): ?>
        <tr>
            <td class="check-col">
                <label class="enquiry-check">
                    <input type="checkbox" form="enquiry-export" name="ids[]" value="<?= e((string) $enquiry['id']) ?>" data-enquiry-row>
                    <span class="visually-hidden"><?= e(tt('Select')) ?> <?= e($enquiry['name']) ?></span>
                </label>
            </td>
            <td><?= e($enquiry['created_at']) ?></td>
            <td>
                <?= e($enquiry['name']) ?><br>
                <span class="help"><?= e($enquiry['email']) ?></span>
            </td>
            <td>
                <?= e($enquiry['phone']) ?><br>
                <span class="help"><?= e($enquiry['whatsapp']) ?></span>
            </td>
            <td><?= e(ftc((string) $enquiry['course_title'])) ?></td>
            <td>
                <span class="status-pill status-<?= e($enquiry['status']) ?>"><?= e($enquiry['status'] === 'new' ? 'New' : 'Contacted') ?></span>
            </td>
            <td class="dash-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/enquiries/' . $enquiry['id'])) ?>">View</a>
                <form method="post" action="<?= e(url('/admin/enquiries/' . $enquiry['id'] . '/delete')) ?>" data-confirm="Delete this enquiry?">
                    <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if ($enquiries): ?>
<script>
(function () {
    var form = document.querySelector('[data-enquiry-export]');
    if (!form) return;
    var all = form.ownerDocument.querySelector('[data-enquiry-all]');
    var countEl = form.querySelector('[data-enquiry-count]');
    var btn = form.querySelector('[data-enquiry-download]');
    var noneLabel = <?= json_encode(tt('None selected'), JSON_UNESCAPED_UNICODE) ?>;
    var selectedLabel = <?= json_encode(tt('selected'), JSON_UNESCAPED_UNICODE) ?>;
    function boxes() {
        return form.ownerDocument.querySelectorAll('[data-enquiry-row]');
    }
    function sync() {
        var list = boxes();
        var n = 0;
        list.forEach(function (el) { if (el.checked) n += 1; });
        if (all) {
            all.checked = list.length > 0 && n === list.length;
            all.indeterminate = n > 0 && n < list.length;
        }
        if (countEl) countEl.textContent = n ? (n + ' ' + selectedLabel) : noneLabel;
        if (btn) btn.disabled = n === 0;
    }
    if (all) {
        all.addEventListener('change', function () {
            boxes().forEach(function (el) { el.checked = all.checked; });
            sync();
        });
    }
    document.addEventListener('change', function (e) {
        if (e.target && e.target.hasAttribute && e.target.hasAttribute('data-enquiry-row')) {
            sync();
        }
    });
    form.addEventListener('submit', function (e) {
        var n = 0;
        boxes().forEach(function (el) { if (el.checked) n += 1; });
        if (n === 0) e.preventDefault();
    });
    sync();
})();
</script>
<?php endif; ?>

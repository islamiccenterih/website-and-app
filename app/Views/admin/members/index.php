<div class="dash-top">
    <h1>Panel members</h1>
    <a class="btn btn-walnut" href="<?= e(url('/admin/members/create')) ?>">Add a panel member</a>
</div>
<p>Create an email and password for a manager, editor, or other helper. Tick only the sections they should open. They sign in at the same Admin login page and never see the rest of the panel.</p>
<div class="table-wrap">
<table class="data">
    <thead>
        <tr>
            <th>Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Access</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$members): ?>
        <tr><td colspan="6">No admin accounts yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($members as $member): ?>
        <?php $isOwnerRow = ($member['panel_role'] ?? '') === 'owner'; ?>
        <tr>
            <td>
                <strong><?= e($member['name']) ?></strong>
                <?php if ($member['last_login_at']): ?>
                    <br><span class="help">Last sign-in <?= e($member['last_login_at']) ?></span>
                <?php endif; ?>
            </td>
            <td><?= $isOwnerRow ? 'Owner' : e((string) ($member['job_title'] ?: 'Panel member')) ?></td>
            <td><?= e($member['email']) ?></td>
            <td><?= e($member['access_label']) ?></td>
            <td><span class="badge <?= $member['status'] === 'active' ? 'badge-on' : 'badge-off' ?>"><?= e($member['status']) ?></span></td>
            <td class="dash-actions">
                <?php if ($isOwnerRow): ?>
                    <span class="help">Full access</span>
                <?php else: ?>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/members/' . $member['id'] . '/edit')) ?>">Edit access</a>
                    <form method="post" action="<?= e(url('/admin/members/' . $member['id'] . '/delete')) ?>" data-confirm="Remove this panel member? They will not be able to sign in.">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline btn-sm" type="submit">Remove</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<h1>Contact messages</h1>
<div class="table-wrap">
<table class="data">
    <thead><tr><th>When</th><th>From</th><th>Message</th><th></th></tr></thead>
    <tbody>
    <?php if (!$messages): ?><tr><td colspan="4">No messages yet.</td></tr><?php endif; ?>
    <?php foreach ($messages as $message): ?>
        <tr>
            <td><?= e($message['created_at']) ?></td>
            <td><?= e($message['name']) ?><br><?= e($message['email']) ?><br><?= e($message['phone']) ?></td>
            <td><?= nl2br(e($message['message'])) ?></td>
            <td>
                <form method="post" action="<?= e(url('/admin/messages/' . $message['id'] . '/delete')) ?>" data-confirm="Delete this message?">
                    <?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

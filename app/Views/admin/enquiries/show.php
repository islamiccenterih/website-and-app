<?php
$enquiry = $enquiry ?? [];
$isNew = ($enquiry['status'] ?? '') === 'new';
?>
<div class="dash-top">
    <h1>Course enquiry</h1>
    <div class="dash-actions">
        <?php if ($isNew): ?>
            <form method="post" action="<?= e(url('/admin/enquiries/' . $enquiry['id'] . '/contacted')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-walnut" type="submit">Mark as contacted</button>
            </form>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/admin/enquiries/' . $enquiry['id'] . '/delete')) ?>" data-confirm="Delete this enquiry?">
            <?= csrf_field() ?>
            <button class="btn btn-outline" type="submit">Delete</button>
        </form>
        <a class="btn btn-outline" href="<?= e(url('/admin/enquiries')) ?>">Back to list</a>
    </div>
</div>
<p>
    <span class="status-pill status-<?= e($enquiry['status'] ?? '') ?>"><?= e(($enquiry['status'] ?? '') === 'new' ? 'New' : 'Contacted') ?></span>
    Received <?= e($enquiry['created_at'] ?? '') ?>
</p>
<dl class="enquiry-dl">
    <dt>Course</dt>
    <dd dir="auto"><?= e(ftc((string) ($enquiry['course_title'] ?? ''))) ?></dd>
    <dt>Name</dt>
    <dd><?= e($enquiry['name'] ?? '') ?></dd>
    <dt>Email</dt>
    <dd><a href="mailto:<?= e($enquiry['email'] ?? '') ?>"><?= e($enquiry['email'] ?? '') ?></a></dd>
    <dt>Phone</dt>
    <dd><?= e($enquiry['phone'] ?? '') ?></dd>
    <dt>WhatsApp</dt>
    <dd><?= e($enquiry['whatsapp'] ?? '') ?></dd>
    <dt>Address</dt>
    <dd><?= nl2br(e($enquiry['address'] ?? '')) ?></dd>
</dl>

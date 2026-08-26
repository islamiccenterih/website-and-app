<?php
$item = $item ?? null;
$u = $item ?? [];
$body = (string) ($u['body_html'] ?? '');
?>
<h1><?= $item ? 'Edit update' : 'Write an update' ?></h1>
<p>The gold box below is the same layout visitors see. Type in it. Drop pictures and video into the article. What you compose here is what Center Updates shows.</p>
<form class="form stack-form" method="post" action="<?= e(url($item ? '/admin/updates/' . $item['id'] : '/admin/updates')) ?>" enctype="multipart/form-data" data-update-form>
    <?= csrf_field() ?>
    <div class="row-2">
        <div class="field">
            <label for="title">Title</label>
            <input id="title" name="title" required maxlength="240" value="<?= e((string) ($u['title'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="published_on">Date</label>
            <input id="published_on" type="date" name="published_on" required value="<?= e((string) ($u['published_on'] ?? date('Y-m-d'))) ?>">
        </div>
    </div>
    <div class="row-2">
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft"<?= selected($u['status'] ?? 'published', 'draft') ?>>Draft — not public</option>
                <option value="published"<?= selected($u['status'] ?? 'published', 'published') ?>>Published on the website</option>
            </select>
        </div>
        <div class="field">
            <label for="excerpt">Short line for the list (optional)</label>
            <input id="excerpt" name="excerpt" maxlength="400" value="<?= e((string) ($u['excerpt'] ?? '')) ?>" placeholder="One sentence shown on the Center Updates list">
        </div>
    </div>
    <?php if ($item): ?>
        <div class="field">
            <label for="slug">URL slug</label>
            <input id="slug" name="slug" value="<?= e((string) ($u['slug'] ?? '')) ?>">
            <p class="help">Public address: /center-updates/<?= e((string) ($u['slug'] ?? '')) ?></p>
        </div>
    <?php endif; ?>

    <div class="compose" data-compose-root data-upload="<?= e(url('/admin/updates/upload')) ?>" data-embed="<?= e(url('/admin/updates/embed')) ?>" data-csrf="<?= e(csrf_token()) ?>">
        <div class="compose-bar" role="toolbar" aria-label="Format">
            <button type="button" data-cmd="bold"><strong>B</strong></button>
            <button type="button" data-cmd="italic"><em>I</em></button>
            <button type="button" data-block="h2">Heading</button>
            <button type="button" data-block="p">Text</button>
            <button type="button" data-cmd="insertUnorderedList">List</button>
            <button type="button" data-block="blockquote">Quote</button>
            <button type="button" data-insert-image>Picture</button>
            <button type="button" data-insert-video>Video file</button>
            <button type="button" data-insert-embed>YouTube / Vimeo</button>
        </div>
        <p class="compose-hint">This box is the public article. Add text here, then pictures or video in between the paragraphs. After a picture is inserted, drag the gold corner to make that picture larger or smaller — each picture has its own size.</p>
        <div class="update-body compose-canvas" data-compose contenteditable="true" role="textbox" aria-label="Update body"><?= $body !== '' ? $body : '<p></p>' ?></div>
        <input type="hidden" name="body_html" data-compose-html value="">
        <input type="file" data-image-file accept="image/jpeg,image/png,image/webp,image/gif" hidden>
        <input type="file" data-video-file accept="video/mp4,video/webm" hidden>
    </div>

    <button class="btn btn-walnut" type="submit"><?= $item ? 'Save update' : 'Publish update' ?></button>
    <a class="btn btn-outline" href="<?= e(url('/admin/updates')) ?>">Back to list</a>
</form>
<script src="<?= e(asset('assets/js/update-compose.js')) ?>?v=2" defer></script>

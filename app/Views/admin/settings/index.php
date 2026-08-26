<h1>Settings</h1>
<form class="form" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/settings')) ?>">
    <?= csrf_field() ?>
    <div class="field"><label>Site name</label><input name="site_name" value="<?= e((string) setting('site_name')) ?>"></div>
    <div class="field"><label>Tagline</label><input name="site_tagline" value="<?= e((string) setting('site_tagline')) ?>"></div>
    <div class="field">
        <label>Arabic and Urdu terms on the English website</label>
        <label class="help"><input type="hidden" name="faith_terms" value="0"><input type="checkbox" name="faith_terms" value="1"<?= faith_terms_enabled() ? ' checked' : '' ?>> On the English public website, show Islamic words in Arabic or Urdu with English in brackets. Pages, courses, and activities in this panel show that same text so you can edit it. Untick for plain English on the public site too.</label>
    </div>
    <div class="field"><label>Footer note</label><textarea name="footer_note" rows="3"><?= e((string) setting('footer_note')) ?></textarea></div>
    <p class="help">Rename public pages under <a href="<?= e(url('/admin/pages')) ?>">Pages</a>.</p>
    <div class="field"><label>Home SEO title</label><input name="seo_home_title" value="<?= e((string) setting('seo_home_title')) ?>"></div>
    <div class="field"><label>Home meta description</label><textarea name="seo_home_description" rows="2"><?= e((string) setting('seo_home_description')) ?></textarea></div>
    <div class="field">
        <label>Logo image (optional)</label>
        <?php if (setting('logo_image')): ?><img class="thumb" src="<?= e(upload_url((string) setting('logo_image'))) ?>" alt=""><?php endif; ?>
        <input type="file" name="logo_image" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <h2>Moon Timing location</h2>
    <p class="help">Moon Timing uses Firozabad, Uttar Pradesh, India 283203 (27.1591, 78.3957, Asia/Kolkata) unless you change these values.</p>
    <div class="row-2">
        <div class="field"><label>Latitude</label><input name="location_lat" value="<?= e((string) setting('location_lat')) ?>"></div>
        <div class="field"><label>Longitude</label><input name="location_lng" value="<?= e((string) setting('location_lng')) ?>"></div>
    </div>
    <div class="field"><label>Location label</label><input name="location_label" value="<?= e((string) setting('location_label')) ?>"></div>
    <div class="field"><label>Timezone</label><input name="timezone" value="<?= e((string) setting('timezone')) ?>"></div>
    <button class="btn btn-walnut" type="submit">Save settings</button>
</form>

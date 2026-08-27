<?php
$live = $live ?? null;
$viewerCount = (int) ($viewerCount ?? 0);
$isHost = !empty($isHost);
$secure = !empty($secure);
?>
<div class="plive-admin"
     data-plive-host
     data-csrf="<?= e(csrf_token()) ?>"
     data-start-url="<?= e(url('/api/public-live/host/start')) ?>"
     data-state-url="<?= e(url('/api/public-live/host/state')) ?>"
     data-signal-url="<?= e(url('/api/public-live/host/signal')) ?>"
     data-media-url="<?= e(url('/api/public-live/host/media')) ?>"
     data-push-url="<?= e(url('/api/public-live/host/push')) ?>"
     data-end-url="<?= e(url('/api/public-live/host/end')) ?>"
     data-watch-url="<?= e(url('/live')) ?>"
     data-live="<?= $live ? '1' : '0' ?>"
     data-is-host="<?= $isHost ? '1' : '0' ?>">

    <div class="plive-admin-idle" data-plive-idle <?= $live && !$isHost ? 'hidden' : '' ?>>
        <div class="plive-go-card">
            <p class="plive-go-kicker">Broadcast</p>
            <h1>Live now</h1>
            <p class="plive-go-lead">Go live from this phone or laptop camera. After you are on, share the screen if you need to. Students’ live classes stay separate.</p>
            <?php if (!$secure): ?>
                <p class="alert alert-error">Camera needs HTTPS. Open this Admin page on https:// after hosting.</p>
            <?php endif; ?>
            <?php if ($live && $isHost): ?>
                <p class="alert alert-success">This live is already on. Press Go live again to send this device’s camera.</p>
            <?php endif; ?>
            <div class="field">
                <label for="plive-title">Title on the public Live page</label>
                <input id="plive-title" data-plive-title maxlength="180" value="Live from <?= e(site_name()) ?>">
            </div>
            <div class="plive-start">
                <button type="button" class="btn btn-gold" data-plive-start="camera">Go live</button>
                <?php if ($live && $isHost): ?>
                    <button class="btn btn-walnut" type="button" data-plive-end>End live</button>
                <?php endif; ?>
            </div>
            <p class="help">Keep this tab open — the public Live page plays from this phone, with picture and mic. The picture is widescreen 16:9. Mute only turns off the mic.</p>
            <p><a class="btn btn-outline" href="<?= e(url('/live')) ?>" target="_blank" rel="noopener">Open the public Live page</a></p>
        </div>
    </div>

    <div class="plive-admin-busy" data-plive-busy <?= $live && !$isHost ? '' : 'hidden' ?>>
        <p class="alert alert-success">The website is live now. <?= (int) $viewerCount ?> watching.</p>
        <p class="dash-actions">
            <a class="btn btn-gold" href="<?= e(url('/live')) ?>" target="_blank" rel="noopener">View public page</a>
            <button class="btn btn-outline" type="button" data-plive-end>End live</button>
        </p>
    </div>

    <div class="plive-studio-layout" data-plive-studio hidden>
        <div class="plive-studio">
            <div class="plive-studio-stage" data-plive-stage>
                <video data-plive-preview playsinline webkit-playsinline muted autoplay></video>
                <span class="plive-live-pill" data-plive-pill>LIVE</span>
                <span class="plive-viewers" data-plive-viewers>0 watching</span>
                <span class="plive-mic-pill" data-plive-mic hidden>Mic off</span>
            </div>
            <p class="plive-note" data-plive-note></p>
            <div class="plive-studio-bar">
                <button type="button" class="btn btn-outline" data-plive-mute>Mute mic</button>
                <button type="button" class="btn btn-outline" data-plive-cam>Switch camera</button>
                <button type="button" class="btn btn-outline" data-plive-mode>Share screen</button>
                <button type="button" class="btn btn-outline" data-plive-host-fs>Fullscreen</button>
                <a class="btn btn-gold" href="<?= e(url('/live')) ?>" target="_blank" rel="noopener">Public page</a>
                <button type="button" class="btn btn-walnut" data-plive-end>End live</button>
            </div>
        </div>
        <aside class="plive-chat plive-chat-host" data-plive-chat data-empty="Comments from the public Live page appear here as they are sent.">
            <h2>Live comments</h2>
            <div class="plive-chat-list" data-plive-chat-list>
                <p class="plive-chat-empty">Comments from the public Live page appear here as they are sent.</p>
            </div>
        </aside>
    </div>
</div>
<script src="<?= e(asset('assets/js/public-live.js')) ?>?v=9"></script>

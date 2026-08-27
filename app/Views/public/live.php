<?php
$status = $status ?? ['live' => false, 'viewers' => 0, 'title' => ''];
$isLive = !empty($status['live']);
?>
<div class="plive-page" data-plive-watch
     data-csrf="<?= e(csrf_token()) ?>"
     data-status-url="<?= e(url('/api/public-live/status')) ?>"
     data-join-url="<?= e(url('/api/public-live/watch/join')) ?>"
     data-state-url="<?= e(url('/api/public-live/watch/state')) ?>"
     data-signal-url="<?= e(url('/api/public-live/watch/signal')) ?>"
     data-leave-url="<?= e(url('/api/public-live/watch/leave')) ?>"
     data-frame-url="<?= e(url('/api/public-live/watch/frame')) ?>"
     data-audio-url="<?= e(url('/api/public-live/watch/audio')) ?>"
     data-comment-url="<?= e(url('/api/public-live/watch/comment')) ?>"
     data-watching-label="<?= e(tt('Watching')) ?>">
    <section class="page-hero">
        <div class="container">
            <?php
            $kicker = page_copy('live', 'kicker', 'On this website');
            $title = page_copy('live', 'title', 'Live');
            $tag = 'h1';
            $lead = page_copy('live', 'lead', 'When the center goes live from a phone or laptop, the stream plays here. No YouTube account is needed.');
            $align = 'center';
            $light = true;
            require APP_PATH . '/Views/components/section-head.php';
            ?>
        </div>
    </section>

    <section class="section">
        <div class="container plive-wrap">
            <div class="plive-player" data-plive-player>
                <video data-plive-video playsinline webkit-playsinline autoplay muted></video>
                <img class="plive-fallback" data-plive-fallback alt="" hidden>
                <button type="button" class="plive-unmute" data-plive-unmute hidden><?= e(tt('Tap for sound')) ?></button>
                <div class="plive-offline" data-plive-offline <?= $isLive ? 'hidden' : '' ?>>
                    <p class="plive-offline-kicker"><?= e(tt('Live')) ?></p>
                    <h2><?= e(tt('The center is not live right now')) ?></h2>
                    <p><?= e(tt('When administration starts Live now, the video appears on this page for everyone.')) ?></p>
                </div>
                <div class="plive-offline plive-connecting" data-plive-connecting <?= $isLive ? '' : 'hidden' ?>>
                    <p class="plive-offline-kicker"><?= e(tt('Live')) ?></p>
                    <h2><?= e(tt('Connecting to the live…')) ?></h2>
                    <p><?= e(tt('The stream is on. The picture appears in a moment.')) ?></p>
                </div>
                <div class="plive-chrome" data-plive-chrome hidden>
                    <span class="plive-live-pill" data-plive-pill>LIVE</span>
                    <span class="plive-viewers" data-plive-viewers><?= e(tt('Watching')) ?>: 0</span>
                    <div class="plive-controls">
                        <button type="button" data-plive-play aria-label="<?= e(tt('Play')) ?>">Play</button>
                        <span class="plive-clock" data-plive-clock>00:00</span>
                        <input type="range" min="0" max="1" step="0.05" value="1" data-plive-vol aria-label="<?= e(tt('Volume')) ?>">
                        <span class="plive-spacer"></span>
                        <button type="button" data-plive-pip hidden aria-label="<?= e(tt('Mini player')) ?>"><?= e(tt('Mini player')) ?></button>
                        <button type="button" data-plive-fs aria-label="<?= e(tt('Fullscreen')) ?>"><?= e(tt('Fullscreen')) ?></button>
                    </div>
                </div>
            </div>
            <p class="plive-caption" data-plive-caption><?= $isLive ? e((string) $status['title']) : '' ?></p>
            <p class="plive-status" data-plive-status></p>

            <aside class="plive-chat" data-plive-chat data-empty="<?= e(tt('Comments appear here while the center is live.')) ?>" <?= $isLive ? '' : 'hidden' ?>>
                <h2><?= e(tt('Live comments')) ?></h2>
                <div class="plive-chat-list" data-plive-chat-list>
                    <p class="plive-chat-empty"><?= e(tt('Comments appear here while the center is live.')) ?></p>
                </div>
                <form class="plive-chat-form" data-plive-chat-form>
                    <input type="text" name="name" maxlength="40" autocomplete="nickname" placeholder="<?= e(tt('Your name')) ?>" data-plive-chat-name>
                    <input type="text" name="body" maxlength="200" required placeholder="<?= e(tt('Write a comment')) ?>" data-plive-chat-body>
                    <button type="submit" class="btn btn-gold"><?= e(tt('Send')) ?></button>
                </form>
            </aside>
        </div>
    </section>
</div>
<script src="<?= e(asset('assets/js/public-live.js')) ?>?v=9"></script>

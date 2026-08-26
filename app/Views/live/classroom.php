<?php
$class = $class ?? [];
$role = $role ?? 'student';
$leaveUrl = $leaveUrl ?? url('/');
$avatarUrl = $avatarUrl ?? '';
$logoSetting = trim((string) setting('logo_image', ''));
$logoSrc = $logoSetting !== '' ? upload_url($logoSetting) : asset('assets/img/logo.png');
$isHost = $role === 'host';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle ?? 'Live class') ?></title>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="robots" content="noindex,nofollow">
    <?php if (request_is_https()): ?>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <?php endif; ?>
    <link rel="icon" href="<?= e(asset('assets/img/favicon.png')) ?>">
    <link rel="preload" href="<?= e(asset('assets/fonts/merriweather-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>?v=74">
    <link rel="stylesheet" href="<?= e(asset('assets/css/live-class.css')) ?>?v=18">
</head>
<body class="live-body">
<main class="live-app"
      data-live-class
      data-class-id="<?= (int) $class['id'] ?>"
      data-role="<?= e($role) ?>"
      data-name="<?= e(auth_user()['name'] ?? '') ?>"
      data-avatar="<?= e($avatarUrl) ?>"
      data-title="<?= e($class['title'] ?? 'Live class') ?>"
      data-course="<?= e(ftc((string) ($class['course_title'] ?? ''))) ?>"
      data-leave="<?= e($leaveUrl) ?>"
      data-join-url="<?= e(url('/api/live-class/' . $class['id'] . '/join')) ?>"
      data-state-url="<?= e(url('/api/live-class/' . $class['id'] . '/state')) ?>"
      data-signal-url="<?= e(url('/api/live-class/' . $class['id'] . '/signal')) ?>"
      data-chat-url="<?= e(url('/api/live-class/' . $class['id'] . '/chat')) ?>"
      data-media-url="<?= e(url('/api/live-class/' . $class['id'] . '/media')) ?>"
      data-hand-url="<?= e(url('/api/live-class/' . $class['id'] . '/hand')) ?>"
      data-leave-url="<?= e(url('/api/live-class/' . $class['id'] . '/leave')) ?>"
      data-kick-url="<?= e(url('/api/live-class/' . $class['id'] . '/kick')) ?>"
      data-presenter-url="<?= e(url('/api/live-class/' . $class['id'] . '/presenter')) ?>"
      data-push-url="<?= e(url('/api/live-class/' . $class['id'] . '/push')) ?>"
      data-frame-url="<?= e(url('/api/live-class/' . $class['id'] . '/watch/frame')) ?>"
      data-audio-url="<?= e(url('/api/live-class/' . $class['id'] . '/watch/audio')) ?>"
      data-mute-all-url="<?= e(url('/api/live-class/' . $class['id'] . '/mute-all')) ?>">
    <header class="live-top">
        <a class="live-brand" href="<?= e(url('/')) ?>">
            <img src="<?= e($logoSrc) ?>" alt="<?= e(site_name()) ?>">
            <span><?= e(site_name()) ?></span>
        </a>
        <div class="live-top-title">
            <p class="live-kicker"><span class="live-dot"></span> Live · <?= e(ftc((string) ($class['course_title'] ?? ''))) ?></p>
            <h1><?= e($class['title'] ?? 'Live class') ?></h1>
        </div>
        <div class="live-top-meta">
            <span class="live-chip" id="live-elapsed">00:00</span>
            <button type="button" class="live-chip live-chip-btn" id="live-count" data-ctl="people" title="Show everyone in this class">1 in class</button>
            <a class="btn btn-ghost btn-sm" href="<?= e($leaveUrl) ?>" id="live-exit">Back</a>
        </div>
    </header>

    <div class="live-sound-bar" id="live-sound-bar" hidden>
        <p>The browser blocked class audio. Click once to hear the teacher and other students.</p>
        <button type="button" class="btn btn-gold btn-sm" id="live-enable-sound">Turn on sound</button>
    </div>

    <section class="live-lobby" id="live-lobby">
        <span class="sec-kicker">Classroom</span>
        <h2>Ready to join?</h2>
        <span class="sec-rule" aria-hidden="true"><img src="<?= e(asset('assets/img/heading-rule.svg')) ?>" alt=""></span>
        <p>Allow the camera and microphone when the browser asks. You can switch either one off after you join. Only the teacher — or a student they appoint as host — can share a screen. Students do not send a screen unless that happens.</p>
        <video id="live-lobby-preview" autoplay muted playsinline webkit-playsinline hidden></video>
        <p class="help" id="live-preview-note">Checking camera and microphone…</p>
        <div class="live-lobby-actions">
            <button class="btn btn-gold" type="button" data-join="av">Join with camera &amp; mic</button>
            <button class="btn btn-walnut" type="button" data-join="audio">Join with mic only</button>
        </div>
        <p class="help" id="live-lobby-error" hidden></p>
    </section>

    <section class="live-stage" id="live-stage" hidden>
        <div class="live-grid" id="live-grid" data-count="1"></div>
        <aside class="live-side" id="live-side">
            <div class="live-panel" id="live-panel-people" data-panel="people" hidden>
                <div class="live-panel-head">
                    <div>
                        <p class="live-panel-kicker">In this class</p>
                        <h3>People</h3>
                    </div>
                    <span class="live-panel-badge" id="live-people-count">0</span>
                    <button class="live-panel-close" type="button" data-ctl="close-panel" aria-label="Close people">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>
                <p class="live-panel-hint"><?= $isHost ? 'Everyone in this class. You can mute a student, make them host (so they can share a screen), or remove them.' : 'Everyone currently in this class. Raise your hand from the bar below if you need the teacher.' ?></p>
                <div class="live-people-list" id="live-people"></div>
            </div>
            <div class="live-panel" id="live-panel-chat" data-panel="chat" hidden>
                <div class="live-panel-head">
                    <div>
                        <p class="live-panel-kicker">Class</p>
                        <h3>Chat</h3>
                    </div>
                    <button class="live-panel-close" type="button" data-ctl="close-panel" aria-label="Close chat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>
                <div class="live-chat-log" id="live-chat-log"></div>
                <form class="live-chat-form" id="live-chat-form">
                    <input id="live-chat-input" maxlength="400" placeholder="Write a message…" autocomplete="off">
                    <button class="live-chat-send" type="submit" aria-label="Send">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3.4 20.6 21 12 3.4 3.4 3 10l11 2-11 2z"/></svg>
                    </button>
                </form>
            </div>
        </aside>
    </section>

    <footer class="live-dock" id="live-dock" hidden>
        <button type="button" data-ctl="mic" class="live-ctl is-on" aria-pressed="true" title="Turn microphone off">
            <span class="live-ctl-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3Z"/><path d="M5 11a7 7 0 0 0 14 0"/><path d="M12 18v3"/></svg>
            </span>
            <span class="live-ctl-copy">
                <span class="live-ctl-label">Mic</span>
                <span class="live-ctl-state">On</span>
            </span>
        </button>
        <button type="button" data-ctl="cam" class="live-ctl is-on" aria-pressed="true" title="Turn camera off">
            <span class="live-ctl-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="12" height="10" rx="2"/><path d="m15 10 6-3v10l-6-3"/></svg>
            </span>
            <span class="live-ctl-copy">
                <span class="live-ctl-label">Camera</span>
                <span class="live-ctl-state">On</span>
            </span>
        </button>
        <button type="button" data-ctl="people" class="live-ctl" aria-pressed="false" title="Show people">
            <span class="live-ctl-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 19a6 6 0 0 1 12 0"/><circle cx="17" cy="9" r="2.4"/><path d="M21 19a5 5 0 0 0-6-4.6"/></svg>
            </span>
            <span class="live-ctl-copy">
                <span class="live-ctl-label">People</span>
                <span class="live-ctl-state" id="live-people-ctl">List</span>
            </span>
        </button>
        <button type="button" data-ctl="chat" class="live-ctl" aria-pressed="false" title="Open chat">
            <span class="live-ctl-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 6h14v9H8l-3 3V6Z"/></svg>
            </span>
            <span class="live-badge" id="live-chat-badge" hidden>0</span>
            <span class="live-ctl-copy">
                <span class="live-ctl-label">Chat</span>
                <span class="live-ctl-state">Open</span>
            </span>
        </button>
        <button type="button" data-ctl="screen" class="live-ctl" id="live-share-btn" aria-pressed="false"<?= $isHost ? '' : ' hidden' ?> title="Share screen">
            <span class="live-ctl-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
            </span>
            <span class="live-ctl-copy">
                <span class="live-ctl-label">Share</span>
                <span class="live-ctl-state">Screen</span>
            </span>
        </button>
        <?php if ($isHost): ?>
            <button type="button" data-ctl="mute-all" class="live-ctl" title="Mute every student">
                <span class="live-ctl-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3Z"/><path d="m4 4 16 16"/></svg>
                </span>
                <span class="live-ctl-copy">
                    <span class="live-ctl-label">Mute</span>
                    <span class="live-ctl-state">All</span>
                </span>
            </button>
        <?php else: ?>
            <button type="button" data-ctl="hand" class="live-ctl" aria-pressed="false" title="Raise hand">
                <span class="live-ctl-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 11V6a1.5 1.5 0 0 1 3 0v5"/><path d="M12 11V5a1.5 1.5 0 0 1 3 0v6"/><path d="M15 11V7a1.5 1.5 0 0 1 3 0v8a6 6 0 0 1-6 6H9a5 5 0 0 1-5-5v-3a1.5 1.5 0 0 1 3 0"/></svg>
                </span>
                <span class="live-ctl-copy">
                    <span class="live-ctl-label">Hand</span>
                    <span class="live-ctl-state">Down</span>
                </span>
            </button>
        <?php endif; ?>
        <button type="button" data-ctl="leave" class="live-ctl live-ctl-end" title="<?= $isHost ? 'End class for everyone' : 'Leave class' ?>">
            <span class="live-ctl-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 4h4v16h-4"/><path d="M15 12H4"/><path d="m8 8-4 4 4 4"/></svg>
            </span>
            <span class="live-ctl-copy">
                <span class="live-ctl-label"><?= $isHost ? 'End' : 'Leave' ?></span>
                <span class="live-ctl-state"><?= $isHost ? 'Class' : 'Exit' ?></span>
            </span>
        </button>
    </footer>
</main>
<script src="<?= e(asset('assets/js/live-class.js')) ?>?v=19" data-cfasync="false"></script>
</body>
</html>

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAccess;
use App\Core\Controller;
use App\Services\PublicLiveService;

final class PublicLiveApiController extends Controller
{
    public function status(): void
    {
        json_response(['ok' => true] + (new PublicLiveService())->publicStatus());
    }

    public function hostStart(): void
    {
        $this->guardCsrf();
        $admin = $this->requireHostAdmin();
        $body = read_json_body();
        $svc = new PublicLiveService();
        try {
            $started = $svc->start(
                (int) $admin['id'],
                (string) ($body['title'] ?? ''),
                (string) ($body['source'] ?? 'camera')
            );
        } catch (\RuntimeException $e) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 409);
        }
        json_response([
            'ok' => true,
            'session_id' => (int) $started['session']['id'],
            'peer_id' => $started['host']['peer_id'],
            'token' => $started['host']['token'],
            'title' => $started['session']['title'],
            'source' => $started['session']['source'],
            'resumed' => !empty($started['resumed']),
            'viewers' => $svc->viewerIds((int) $started['session']['id']),
            'comments' => $svc->recentComments((int) $started['session']['id']),
            'watch_url' => url('/live'),
        ]);
    }

    public function hostState(): void
    {
        $ctx = $this->hostContext();
        $this->releaseSession();
        $body = read_json_body();
        $svc = $ctx['svc'];
        $sessionId = $ctx['session_id'];
        $svc->heartbeat($sessionId, $ctx['peer_id']);
        $svc->sweepStaleViewers($sessionId);
        $session = $svc->find($sessionId);
        if (!$session || ($session['status'] ?? '') !== 'live') {
            json_response(['ok' => true, 'ended' => true, 'viewers' => [], 'signals' => [], 'comments' => []]);
        }
        json_response([
            'ok' => true,
            'ended' => false,
            'viewers' => $svc->viewerIds($sessionId),
            'viewer_count' => $svc->viewerCount($sessionId),
            'signals' => $svc->takeSignals($sessionId, $ctx['peer_id']),
            'source' => $session['source'] ?? 'camera',
            'comments' => $svc->recentComments($sessionId, (int) ($body['after'] ?? 0)),
        ]);
    }

    public function hostSignal(): void
    {
        $ctx = $this->hostContext();
        $this->releaseSession();
        $body = read_json_body();
        $to = trim((string) ($body['to'] ?? ''));
        $kind = trim((string) ($body['kind'] ?? ''));
        if ($to === '' || !in_array($kind, ['offer', 'ice', 'bye'], true)) {
            json_response(['ok' => false, 'error' => 'Invalid signal.'], 422);
        }
        $ctx['svc']->sendSignal($ctx['session_id'], $ctx['peer_id'], $to, $kind, $body['payload'] ?? null);
        json_response(['ok' => true]);
    }

    public function hostMedia(): void
    {
        $ctx = $this->hostContext();
        $body = request_payload();
        $ctx['svc']->setSource($ctx['session_id'], (string) ($body['source'] ?? 'camera'));
        json_response(['ok' => true]);
    }

    public function hostPush(): void
    {
        $ctx = $this->hostContext();
        $this->releaseSession();
        $svc = $ctx['svc'];
        $sessionId = $ctx['session_id'];
        $svc->heartbeat($sessionId, $ctx['peer_id']);
        $frame = false;
        $audio = false;
        $frameTmp = (string) ($_FILES['frame']['tmp_name'] ?? '');
        if ($frameTmp !== '') {
            $frame = $svc->saveFrame($sessionId, $frameTmp);
        }
        $audioTmp = (string) ($_FILES['audio']['tmp_name'] ?? '');
        if ($audioTmp !== '') {
            $audio = $svc->saveAudio($sessionId, $audioTmp);
        }
        json_response(['ok' => true, 'frame' => $frame, 'audio' => $audio]);
    }

    public function watchFrame(): void
    {
        $this->releaseSession();
        $svc = new PublicLiveService();
        $session = $svc->current();
        if (!$session) {
            http_response_code(404);
            exit;
        }
        $file = $svc->framePath((int) $session['id']);
        if (!$file) {
            http_response_code(204);
            exit;
        }
        header('Content-Type: image/jpeg');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . (string) filesize($file));
        readfile($file);
        exit;
    }

    public function watchAudio(): void
    {
        $this->releaseSession();
        $svc = new PublicLiveService();
        $session = $svc->current();
        if (!$session) {
            http_response_code(404);
            exit;
        }
        $sessionId = (int) $session['id'];
        $file = $svc->audioPath($sessionId);
        if (!$file) {
            http_response_code(204);
            exit;
        }
        $seq = $svc->audioSeq($sessionId);
        header('Content-Type: application/octet-stream');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        header('X-Live-Seq: ' . $seq);
        header('X-Live-Rate: 16000');
        header('Content-Length: ' . (string) filesize($file));
        readfile($file);
        exit;
    }

    public function hostEnd(): void
    {
        $this->guardCsrf();
        $admin = $this->requireHostAdmin();
        $svc = new PublicLiveService();
        $session = $svc->current();
        if ($session) {
            $svc->end((int) $session['id']);
        }
        unset($_SESSION['public_live_host']);
        json_response(['ok' => true, 'ended' => true, 'admin_id' => (int) $admin['id']]);
    }

    public function watchJoin(): void
    {
        $svc = new PublicLiveService();
        $session = $svc->current();
        if (!$session) {
            json_response(['ok' => false, 'error' => 'The center is not live right now.', 'live' => false], 409);
        }
        try {
            $you = $svc->joinViewer((int) $session['id']);
        } catch (\RuntimeException $e) {
            json_response(['ok' => false, 'error' => $e->getMessage(), 'live' => false], 409);
        }
        json_response([
            'ok' => true,
            'live' => true,
            'session_id' => (int) $session['id'],
            'title' => $session['title'],
            'peer_id' => $you['peer_id'],
            'token' => $you['token'],
            'host_id' => $you['host_id'],
            'viewers' => $svc->viewerCount((int) $session['id']),
            'comments' => $svc->recentComments((int) $session['id']),
        ]);
    }

    public function watchState(): void
    {
        $ctx = $this->watchContext();
        $this->releaseSession();
        $body = read_json_body();
        $svc = $ctx['svc'];
        $svc->heartbeat($ctx['session_id'], $ctx['peer_id']);
        $svc->sweepStaleViewers($ctx['session_id']);
        $session = $svc->find($ctx['session_id']);
        if (!$session || ($session['status'] ?? '') !== 'live') {
            json_response(['ok' => true, 'ended' => true, 'live' => false, 'signals' => [], 'comments' => []]);
        }
        $host = $svc->hostPeer($ctx['session_id']);
        json_response([
            'ok' => true,
            'ended' => false,
            'live' => true,
            'host_id' => $host['peer_id'] ?? '',
            'viewers' => $svc->viewerCount($ctx['session_id']),
            'signals' => $svc->takeSignals($ctx['session_id'], $ctx['peer_id']),
            'comments' => $svc->recentComments($ctx['session_id'], (int) ($body['after'] ?? 0)),
        ]);
    }

    public function watchSignal(): void
    {
        $ctx = $this->watchContext();
        $body = read_json_body();
        $to = trim((string) ($body['to'] ?? ''));
        $kind = trim((string) ($body['kind'] ?? ''));
        if ($to === '' || !in_array($kind, ['answer', 'ice', 'bye', 'need'], true)) {
            json_response(['ok' => false, 'error' => 'Invalid signal.'], 422);
        }
        $host = $ctx['svc']->hostPeer($ctx['session_id']);
        if (!$host || $to !== (string) $host['peer_id']) {
            json_response(['ok' => false, 'error' => 'Signal must go to the live host.'], 422);
        }
        $ctx['svc']->sendSignal($ctx['session_id'], $ctx['peer_id'], $to, $kind, $body['payload'] ?? null);
        json_response(['ok' => true]);
    }

    public function watchComment(): void
    {
        $ctx = $this->watchContext();
        $body = read_json_body();
        try {
            $row = $ctx['svc']->addComment(
                $ctx['session_id'],
                $ctx['peer_id'],
                (string) ($body['name'] ?? ''),
                (string) ($body['body'] ?? '')
            );
        } catch (\RuntimeException $e) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 422);
        }
        json_response(['ok' => true, 'comment' => $row]);
    }

    public function watchLeave(): void
    {
        $body = read_json_body();
        $svc = new PublicLiveService();
        $sessionId = (int) ($body['session_id'] ?? 0);
        $peerId = trim((string) ($body['peer_id'] ?? ''));
        $token = trim((string) ($body['token'] ?? ''));
        if ($sessionId && $svc->peer($sessionId, $peerId, $token)) {
            $svc->leave($sessionId, $peerId);
        }
        unset($_SESSION['public_live_watch']);
        json_response(['ok' => true]);
    }

    /**
     * @return array{svc:PublicLiveService,session_id:int,peer_id:string}
     */
    private function hostContext(): array
    {
        $this->requireHostAdmin();
        $svc = new PublicLiveService();
        $body = request_payload();
        $saved = $_SESSION['public_live_host'] ?? [];
        $sessionId = (int) ($body['session_id'] ?? ($saved['session_id'] ?? 0));
        $peerId = trim((string) ($body['peer_id'] ?? ($saved['peer_id'] ?? '')));
        $token = trim((string) ($body['token'] ?? ($saved['token'] ?? '')));
        $row = $svc->peer($sessionId, $peerId, $token);
        if (!$row || ($row['role'] ?? '') !== 'host') {
            json_response(['ok' => false, 'error' => 'Start live from this page first.'], 409);
        }
        return ['svc' => $svc, 'session_id' => $sessionId, 'peer_id' => $peerId];
    }

    /**
     * @return array{svc:PublicLiveService,session_id:int,peer_id:string}
     */
    private function watchContext(): array
    {
        $svc = new PublicLiveService();
        $body = request_payload();
        $saved = $_SESSION['public_live_watch'] ?? [];
        $sessionId = (int) ($body['session_id'] ?? ($saved['session_id'] ?? 0));
        $peerId = trim((string) ($body['peer_id'] ?? ($saved['peer_id'] ?? '')));
        $token = trim((string) ($body['token'] ?? ($saved['token'] ?? '')));
        $row = $svc->peer($sessionId, $peerId, $token);
        if (!$row || ($row['role'] ?? '') !== 'viewer') {
            json_response(['ok' => false, 'error' => 'Join the live first.', 'live' => false], 409);
        }
        return ['svc' => $svc, 'session_id' => $sessionId, 'peer_id' => $peerId];
    }

    private function releaseSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    private function requireHostAdmin(): array
    {
        $user = auth_user();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            json_response(['ok' => false, 'error' => 'Sign in to the Admin Panel to go live.'], 401);
        }
        if (!AdminAccess::canModule('live-now', $user)) {
            json_response(['ok' => false, 'error' => 'You do not have access to Live now.'], 403);
        }
        return $user;
    }

    private function guardCsrf(): void
    {
        $body = read_json_body();
        $token = isset($body['_csrf']) && is_string($body['_csrf']) ? $body['_csrf'] : null;
        if (!verify_csrf($token)) {
            json_response(['ok' => false, 'error' => 'Your session expired. Refresh and try again.'], 419);
        }
    }
}

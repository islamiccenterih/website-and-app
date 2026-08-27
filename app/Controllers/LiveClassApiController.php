<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\LiveClassService;

final class LiveClassApiController extends Controller
{
    public function join(string $id): void
    {
        $this->guardCsrf();
        $body = read_json_body();
        $ctx = $this->context((int) $id, true);
        $svc = $ctx['svc'];
        $class = $ctx['class'];
        $role = $ctx['panelRole'];
        $user = $ctx['user'];

        if (($class['status'] ?? '') !== 'live') {
            json_response(['ok' => false, 'error' => 'This class is not live.'], 409);
        }

        $peerId = $svc->peerKey((int) $class['id']);
        $audio = !empty($body['audio']);
        $video = !empty($body['video']);
        $you = $svc->upsertPeer(
            (int) $class['id'],
            $peerId,
            $role,
            (int) $user['id'],
            (string) $user['name'],
            $audio,
            $video
        );
        $peers = $svc->peers((int) $class['id']);

        json_response([
            'ok' => true,
            'peer_id' => $peerId,
            'you' => $you,
            'class' => $svc->publicState($class),
            'peers' => $peers,
        ]);
    }

    public function state(string $id): void
    {
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $class = $ctx['class'];
        $peerId = $svc->peerKey((int) $class['id']);
        $svc->heartbeat((int) $class['id'], $peerId);
        $this->releaseSession();
        $svc->sweep((int) $class['id']);
        $fresh = $svc->find((int) $class['id']) ?: $class;
        $afterChat = (int) ($_GET['after_chat'] ?? 0);

        json_response([
            'ok' => true,
            'class' => $svc->publicState($fresh),
            'peers' => $svc->peers((int) $class['id']),
            'signals' => $svc->takeSignals((int) $class['id'], $peerId),
            'messages' => $svc->messages((int) $class['id'], $afterChat),
        ]);
    }

    public function push(string $id): void
    {
        $this->guardCsrf();
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $classId = (int) $ctx['class']['id'];
        $peerId = $svc->peerKey($classId);
        $peer = $svc->peerRow($classId, $peerId);
        if (!$peer) {
            json_response(['ok' => false, 'error' => 'Join the class first.'], 409);
        }
        $this->releaseSession();
        $svc->heartbeat($classId, $peerId);
        $frame = false;
        $audio = false;
        $frameTmp = (string) ($_FILES['frame']['tmp_name'] ?? '');
        if ($frameTmp !== '') {
            $frame = $svc->saveFrame($classId, $peerId, $frameTmp);
        }
        $audioTmp = (string) ($_FILES['audio']['tmp_name'] ?? '');
        if ($audioTmp !== '') {
            $audio = $svc->saveAudio($classId, $peerId, $audioTmp);
        }
        json_response(['ok' => true, 'frame' => $frame, 'audio' => $audio]);
    }

    public function watchFrame(string $id): void
    {
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $classId = (int) $ctx['class']['id'];
        $peerId = trim((string) ($_GET['peer'] ?? ''));
        $this->releaseSession();
        if ($svc->safePeerId($peerId) === '' || !$svc->peerRow($classId, $peerId)) {
            http_response_code(404);
            live_no_store_headers('image/jpeg');
            exit;
        }
        $file = $svc->framePath($classId, $peerId);
        if (!$file) {
            http_response_code(204);
            live_no_store_headers('image/jpeg');
            exit;
        }
        live_no_store_headers('image/jpeg');
        header('Content-Length: ' . (string) filesize($file));
        readfile($file);
        exit;
    }

    public function watchAudio(string $id): void
    {
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $classId = (int) $ctx['class']['id'];
        $peerId = trim((string) ($_GET['peer'] ?? ''));
        $this->releaseSession();
        if ($svc->safePeerId($peerId) === '' || !$svc->peerRow($classId, $peerId)) {
            http_response_code(404);
            live_no_store_headers('application/octet-stream');
            exit;
        }
        $file = $svc->audioPath($classId, $peerId);
        if (!$file) {
            http_response_code(204);
            live_no_store_headers('application/octet-stream');
            exit;
        }
        $seq = $svc->audioSeq($classId, $peerId);
        live_no_store_headers('application/octet-stream');
        header('X-Live-Seq: ' . $seq);
        header('X-Live-Rate: 16000');
        header('Content-Length: ' . (string) filesize($file));
        readfile($file);
        exit;
    }

    public function signal(string $id): void
    {
        $this->guardCsrf();
        $body = read_json_body();
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $from = $svc->peerKey((int) $ctx['class']['id']);
        $to = trim((string) ($body['to'] ?? ''));
        $kind = trim((string) ($body['kind'] ?? ''));
        $payload = $body['payload'] ?? null;
        $allowed = ['offer', 'answer', 'ice', 'bye', 'control'];
        if ($to === '' || !in_array($kind, $allowed, true)) {
            json_response(['ok' => false, 'error' => 'Invalid signal.'], 422);
        }
        if ($kind === 'control') {
            $action = is_array($payload) ? (string) ($payload['action'] ?? '') : '';
            $studentOk = $action === 'renegotiate';
            if ($ctx['panelRole'] !== 'host' && !$studentOk) {
                json_response(['ok' => false, 'error' => 'Only the teacher can send that control.'], 403);
            }
        }
        $svc->sendSignal((int) $ctx['class']['id'], $from, $to, $kind, $payload);
        json_response(['ok' => true]);
    }

    public function chat(string $id): void
    {
        $this->guardCsrf();
        $body = read_json_body();
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $peerId = $svc->peerKey((int) $ctx['class']['id']);
        $msg = $svc->addMessage(
            (int) $ctx['class']['id'],
            $peerId,
            (string) $ctx['user']['name'],
            (string) ($body['body'] ?? '')
        );
        if (!$msg) {
            json_response(['ok' => false, 'error' => 'Message cannot be empty.'], 422);
        }
        json_response(['ok' => true, 'message' => $msg]);
    }

    public function media(string $id): void
    {
        $this->guardCsrf();
        $body = read_json_body();
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $peerId = $svc->peerKey((int) $ctx['class']['id']);
        $peer = $svc->peerRow((int) $ctx['class']['id'], $peerId);
        if (!$peer) {
            json_response(['ok' => false, 'error' => 'Join the class first.'], 409);
        }
        $fields = [
            'audio_on' => !empty($body['audio']) ? 1 : 0,
            'video_on' => !empty($body['video']) ? 1 : 0,
            'last_seen_at' => date('Y-m-d H:i:s'),
        ];
        if (array_key_exists('screen', $body)) {
            $wantScreen = !empty($body['screen']);
            if ($wantScreen && !$svc->canShareScreen($peer, $ctx['panelRole'])) {
                json_response(['ok' => false, 'error' => 'Only the teacher or a student the teacher makes host can share the screen.'], 403);
            }
            $svc->setScreenOn((int) $ctx['class']['id'], $peerId, $wantScreen);
        }
        $this->db()->update('live_class_peers', $fields, 'class_id = ? AND peer_id = ?', [(int) $ctx['class']['id'], $peerId]);
        json_response(['ok' => true]);
    }

    public function hand(string $id): void
    {
        $this->guardCsrf();
        $body = read_json_body();
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $peerId = $svc->peerKey((int) $ctx['class']['id']);
        $raised = !empty($body['raised']);
        $this->db()->update('live_class_peers', [
            'hand_raised' => $raised ? 1 : 0,
            'last_seen_at' => date('Y-m-d H:i:s'),
        ], 'class_id = ? AND peer_id = ?', [(int) $ctx['class']['id'], $peerId]);
        json_response(['ok' => true, 'raised' => $raised]);
    }

    public function presenter(string $id): void
    {
        $this->guardCsrf();
        $body = read_json_body();
        $ctx = $this->context((int) $id, false);
        if ($ctx['panelRole'] !== 'host') {
            json_response(['ok' => false, 'error' => 'Only the teacher can make a student host.'], 403);
        }
        $target = trim((string) ($body['peer_id'] ?? ''));
        $on = !empty($body['on']);
        $row = $ctx['svc']->setPresenter(
            (int) $ctx['class']['id'],
            $ctx['svc']->peerKey((int) $ctx['class']['id']),
            $target,
            $on
        );
        if (!$row) {
            json_response(['ok' => false, 'error' => 'Choose a student in this class.'], 404);
        }
        json_response(['ok' => true, 'peer' => $row]);
    }

    public function leave(string $id): void
    {
        $body = read_json_body();
        $bodyToken = isset($body['_csrf']) && is_string($body['_csrf']) && $body['_csrf'] !== ''
            ? $body['_csrf']
            : null;
        if (!verify_csrf($bodyToken)) {
            json_response(['ok' => false, 'error' => 'Your session expired. Refresh and try again.'], 419);
        }
        $ctx = $this->context((int) $id, false);
        $svc = $ctx['svc'];
        $endRequested = !empty($body['end']) && $ctx['panelRole'] === 'host';
        if ($endRequested && ($ctx['class']['status'] ?? '') === 'live') {
            $svc->endClass((int) $ctx['class']['id']);
            json_response(['ok' => true, 'ended' => true]);
        }
        if (($ctx['class']['status'] ?? '') === 'ended') {
            json_response(['ok' => true, 'ended' => true]);
        }
        $peerId = $svc->peerKey((int) $ctx['class']['id']);
        $svc->leave((int) $ctx['class']['id'], $peerId, (int) $ctx['user']['id'], $ctx['panelRole']);
        json_response(['ok' => true, 'ended' => false]);
    }

    public function muteAll(string $id): void
    {
        $this->guardCsrf();
        $ctx = $this->context((int) $id, false);
        if ($ctx['panelRole'] !== 'host') {
            json_response(['ok' => false, 'error' => 'Only the teacher can mute everyone.'], 403);
        }
        $svc = $ctx['svc'];
        $classId = (int) $ctx['class']['id'];
        $muted = $svc->muteAllStudents($classId, $svc->peerKey($classId));
        json_response(['ok' => true, 'muted' => $muted]);
    }

    public function kick(string $id): void
    {
        $this->guardCsrf();
        $body = read_json_body();
        $ctx = $this->context((int) $id, false);
        if ($ctx['panelRole'] !== 'host') {
            json_response(['ok' => false, 'error' => 'Only the teacher can remove a student.'], 403);
        }
        $target = trim((string) ($body['peer_id'] ?? ''));
        $row = $this->db()->fetch(
            'SELECT * FROM live_class_peers WHERE class_id = ? AND peer_id = ? LIMIT 1',
            [(int) $ctx['class']['id'], $target]
        );
        if (!$row || ($row['role'] ?? '') === 'host') {
            json_response(['ok' => false, 'error' => 'Student not found in this room.'], 404);
        }
        $svc = $ctx['svc'];
        $svc->sendSignal((int) $ctx['class']['id'], $svc->peerKey((int) $ctx['class']['id']), $target, 'control', ['action' => 'kick']);
        $svc->leave((int) $ctx['class']['id'], $target, (int) $row['user_id'], 'student');
        json_response(['ok' => true]);
    }

    /**
     * @return array{svc:LiveClassService,class:array,user:array,panelRole:string}
     */
    private function context(int $id, bool $mustBeLive): array
    {
        $user = auth_user();
        if (!$user) {
            json_response(['ok' => false, 'error' => 'Please sign in.'], 401);
        }
        $svc = new LiveClassService();
        $class = $svc->find($id);
        if (!$class) {
            json_response(['ok' => false, 'error' => 'Class not found.'], 404);
        }
        $role = (string) ($user['role'] ?? '');
        $panelRole = $role === 'admin' ? 'host' : 'student';
        if (!$svc->canEnter($class, $role, (int) $user['id'])) {
            json_response(['ok' => false, 'error' => 'You are not enrolled in this course.'], 403);
        }
        if ($mustBeLive && ($class['status'] ?? '') !== 'live' && $panelRole !== 'host') {
            json_response(['ok' => false, 'error' => 'This class is not live.'], 409);
        }
        return compact('svc', 'class', 'user', 'panelRole');
    }

    private function releaseSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    private function guardCsrf(): void
    {
        if (!verify_csrf()) {
            json_response(['ok' => false, 'error' => 'Your session expired. Refresh and try again.'], 419);
        }
    }
}

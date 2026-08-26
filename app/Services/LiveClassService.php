<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class LiveClassService
{
    public const PEER_TTL_SECONDS = 1800;

    public function db(): Database
    {
        return Database::get();
    }

    public function find(int $id): ?array
    {
        return $this->db()->fetch(
            'SELECT lc.*, c.title AS course_title, c.slug AS course_slug, a.name AS host_name
             FROM live_classes lc
             INNER JOIN courses c ON c.id = lc.course_id
             LEFT JOIN admins a ON a.id = lc.admin_id
             WHERE lc.id = ? LIMIT 1',
            [$id]
        );
    }

    public function liveForCourse(int $courseId): ?array
    {
        return $this->db()->fetch(
            "SELECT lc.*, c.title AS course_title
             FROM live_classes lc
             INNER JOIN courses c ON c.id = lc.course_id
             WHERE lc.course_id = ? AND lc.status = 'live'
             ORDER BY lc.id DESC LIMIT 1",
            [$courseId]
        );
    }

    public function studentEnrolled(int $studentId, int $courseId): bool
    {
        if ($studentId < 1 || $courseId < 1) {
            return false;
        }
        $row = $this->db()->fetch(
            'SELECT s.id
             FROM students s
             INNER JOIN student_courses sc ON sc.student_id = s.id AND sc.course_id = ?
             WHERE s.id = ? AND s.status = ?
             LIMIT 1',
            [$courseId, $studentId, 'active']
        );
        return $row !== null;
    }

    public function liveForStudent(int $studentId): array
    {
        return $this->db()->fetchAll(
            "SELECT lc.*, c.title AS course_title
             FROM live_classes lc
             INNER JOIN courses c ON c.id = lc.course_id
             INNER JOIN student_courses sc ON sc.course_id = lc.course_id AND sc.student_id = ?
             INNER JOIN students s ON s.id = sc.student_id
             WHERE lc.status = 'live' AND s.status = 'active'
             ORDER BY lc.started_at DESC",
            [$studentId]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function enrolledCourses(int $studentId): array
    {
        return \App\Models\StudentEnrollment::courses($studentId);
    }

    public function enrolledCourse(int $studentId): ?array
    {
        $courses = $this->enrolledCourses($studentId);
        return $courses[0] ?? null;
    }

    public function scheduledForStudent(int $studentId): array
    {
        return $this->db()->fetchAll(
            "SELECT lc.*, c.title AS course_title
             FROM live_classes lc
             INNER JOIN courses c ON c.id = lc.course_id
             INNER JOIN student_courses sc ON sc.course_id = lc.course_id AND sc.student_id = ?
             INNER JOIN students s ON s.id = sc.student_id
             WHERE lc.status = 'scheduled' AND s.status = 'active'
             ORDER BY lc.id DESC",
            [$studentId]
        );
    }

    public function attendanceForStudent(int $studentId, int $limit = 12): array
    {
        return $this->db()->fetchAll(
            'SELECT a.joined_at, a.left_at, lc.title, lc.status, lc.started_at, lc.ended_at, c.title AS course_title
             FROM live_class_attendance a
             INNER JOIN live_classes lc ON lc.id = a.class_id
             LEFT JOIN courses c ON c.id = lc.course_id
             WHERE a.student_id = ?
             ORDER BY a.joined_at DESC
             LIMIT ' . max(1, $limit),
            [$studentId]
        );
    }

    public function canEnter(array $class, string $role, int $userId): bool
    {
        if ($role === 'admin') {
            return true;
        }
        if ($role !== 'student') {
            return false;
        }
        return $this->studentEnrolled($userId, (int) $class['course_id']);
    }

    public function peerKey(int $classId): string
    {
        $store = $_SESSION['live_peer'] ?? [];
        if (!empty($store[$classId]) && is_string($store[$classId])) {
            return $store[$classId];
        }
        $id = bin2hex(random_bytes(8));
        $_SESSION['live_peer'][$classId] = $id;
        return $id;
    }

    public function upsertPeer(int $classId, string $peerId, string $role, int $userId, string $name, bool $audio, bool $video): array
    {
        $now = date('Y-m-d H:i:s');
        $existing = $this->db()->fetch(
            'SELECT * FROM live_class_peers WHERE class_id = ? AND peer_id = ? LIMIT 1',
            [$classId, $peerId]
        );
        if ($existing) {
            $this->db()->update('live_class_peers', [
                'display_name' => $name,
                'audio_on' => $audio ? 1 : 0,
                'video_on' => $video ? 1 : 0,
                'screen_on' => 0,
                'last_seen_at' => $now,
            ], 'id = ?', [(int) $existing['id']]);
        } else {
            $this->db()->insert('live_class_peers', [
                'class_id' => $classId,
                'peer_id' => $peerId,
                'role' => $role,
                'user_id' => $userId,
                'display_name' => $name,
                'audio_on' => $audio ? 1 : 0,
                'video_on' => $video ? 1 : 0,
                'screen_on' => 0,
                'is_presenter' => $role === 'host' ? 1 : 0,
                'hand_raised' => 0,
                'last_seen_at' => $now,
                'joined_at' => $now,
            ]);
            if ($role === 'student') {
                $open = $this->db()->fetch(
                    'SELECT id FROM live_class_attendance WHERE class_id = ? AND student_id = ? AND left_at IS NULL LIMIT 1',
                    [$classId, $userId]
                );
                if (!$open) {
                    $this->db()->insert('live_class_attendance', [
                        'class_id' => $classId,
                        'student_id' => $userId,
                        'joined_at' => $now,
                        'left_at' => null,
                    ]);
                }
            }
        }

        $row = $this->db()->fetch(
            'SELECT * FROM live_class_peers WHERE class_id = ? AND peer_id = ? LIMIT 1',
            [$classId, $peerId]
        );
        return $row ?: [];
    }

    public function heartbeat(int $classId, string $peerId): void
    {
        $this->db()->update('live_class_peers', [
            'last_seen_at' => date('Y-m-d H:i:s'),
        ], 'class_id = ? AND peer_id = ?', [$classId, $peerId]);
    }

    public function peers(int $classId): array
    {
        $cut = date('Y-m-d H:i:s', time() - self::PEER_TTL_SECONDS);
        $rows = $this->db()->fetchAll(
            'SELECT p.peer_id, p.role, p.user_id, p.display_name, p.audio_on, p.video_on, p.screen_on, p.is_presenter, p.hand_raised, p.joined_at, s.avatar
             FROM live_class_peers p
             LEFT JOIN students s ON p.role = \'student\' AND s.id = p.user_id
             WHERE p.class_id = ? AND p.last_seen_at >= ?
             ORDER BY FIELD(p.role, "host", "student"), p.joined_at ASC',
            [$classId, $cut]
        );
        foreach ($rows as &$row) {
            $path = trim((string) ($row['avatar'] ?? ''));
            $row['avatar'] = $path !== '' ? upload_url($path) : '';
            $row['initials'] = self::initials((string) $row['display_name']);
            $row['audio_on'] = (int) ($row['audio_on'] ?? 0);
            $row['video_on'] = (int) ($row['video_on'] ?? 0);
            $row['screen_on'] = (int) ($row['screen_on'] ?? 0);
            $row['is_presenter'] = (int) ($row['is_presenter'] ?? 0);
            $row['can_share'] = ($row['role'] ?? '') === 'host' || $row['is_presenter'] === 1;
            $row['hand_raised'] = (int) ($row['hand_raised'] ?? 0);
            $row['has_frame'] = $this->frameFresh($classId, (string) $row['peer_id']);
            $row['audio_seq'] = $this->audioSeq($classId, (string) $row['peer_id']);
        }
        unset($row);
        return $rows;
    }

    public static function initials(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'IC';
        }
        $parts = preg_split('/\s+/', $name) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $letters !== '' ? $letters : 'IC';
    }

    public function takeSignals(int $classId, string $peerId): array
    {
        $rows = $this->db()->fetchAll(
            'SELECT id, from_peer, kind, payload, created_at
             FROM live_class_signals
             WHERE class_id = ? AND to_peer = ?
             ORDER BY id ASC LIMIT 200',
            [$classId, $peerId]
        );
        if ($rows) {
            $ids = array_map(static fn($r) => (int) $r['id'], $rows);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $this->db()->execute(
                "DELETE FROM live_class_signals WHERE id IN ($placeholders)",
                $ids
            );
        }
        $out = [];
        foreach ($rows as $row) {
            $decoded = json_decode((string) $row['payload'], true);
            $out[] = [
                'from' => $row['from_peer'],
                'kind' => $row['kind'],
                'payload' => $decoded,
            ];
        }
        return $out;
    }

    public function sendSignal(int $classId, string $from, string $to, string $kind, mixed $payload): void
    {
        $this->db()->insert('live_class_signals', [
            'class_id' => $classId,
            'from_peer' => $from,
            'to_peer' => $to,
            'kind' => $kind,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function broadcast(int $classId, string $from, string $kind, mixed $payload): void
    {
        foreach ($this->peers($classId) as $peer) {
            if (($peer['peer_id'] ?? '') === $from) {
                continue;
            }
            $this->sendSignal($classId, $from, (string) $peer['peer_id'], $kind, $payload);
        }
    }

    public function muteAllStudents(int $classId, string $fromPeer): int
    {
        $muted = 0;
        foreach ($this->peers($classId) as $peer) {
            $peerId = (string) ($peer['peer_id'] ?? '');
            if ($peerId === '' || $peerId === $fromPeer || ($peer['role'] ?? '') === 'host') {
                continue;
            }
            $this->db()->update('live_class_peers', [
                'audio_on' => 0,
            ], 'class_id = ? AND peer_id = ?', [$classId, $peerId]);
            $this->sendSignal($classId, $fromPeer, $peerId, 'control', ['action' => 'mute']);
            $muted++;
        }
        return $muted;
    }

    public function messages(int $classId, int $afterId = 0): array
    {
        return $this->db()->fetchAll(
            'SELECT id, peer_id, display_name, body, created_at
             FROM live_class_messages
             WHERE class_id = ? AND id > ?
             ORDER BY id ASC LIMIT 200',
            [$classId, $afterId]
        );
    }

    public function addMessage(int $classId, string $peerId, string $name, string $body): ?array
    {
        $body = trim($body);
        if ($body === '') {
            return null;
        }
        $body = mb_substr($body, 0, 400);
        $id = $this->db()->insert('live_class_messages', [
            'class_id' => $classId,
            'peer_id' => $peerId,
            'display_name' => $name,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db()->fetch('SELECT id, peer_id, display_name, body, created_at FROM live_class_messages WHERE id = ?', [$id]);
    }

    public function leave(int $classId, string $peerId, int $userId, string $role): void
    {
        $this->broadcast($classId, $peerId, 'bye', ['reason' => 'left']);
        $this->db()->delete('live_class_peers', 'class_id = ? AND peer_id = ?', [$classId, $peerId]);
        $this->db()->delete('live_class_signals', 'class_id = ? AND (from_peer = ? OR to_peer = ?)', [$classId, $peerId, $peerId]);
        $this->clearPeerMedia($classId, $peerId);
        if ($role === 'student') {
            $this->db()->update('live_class_attendance', [
                'left_at' => date('Y-m-d H:i:s'),
            ], 'class_id = ? AND student_id = ? AND left_at IS NULL', [$classId, $userId]);
        }
    }

    public function endClass(int $classId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db()->update('live_classes', [
            'status' => 'ended',
            'ended_at' => $now,
            'updated_at' => $now,
        ], 'id = ?', [$classId]);
        $this->db()->update('live_class_attendance', [
            'left_at' => $now,
        ], 'class_id = ? AND left_at IS NULL', [$classId]);
        $this->db()->delete('live_class_signals', 'class_id = ?', [$classId]);
        $this->db()->delete('live_class_peers', 'class_id = ?', [$classId]);
        $this->clearClassMedia($classId);
    }

    public function sweep(int $classId): void
    {
        $cut = date('Y-m-d H:i:s', time() - self::PEER_TTL_SECONDS);
        $stale = $this->db()->fetchAll(
            'SELECT * FROM live_class_peers WHERE class_id = ? AND last_seen_at < ?',
            [$classId, $cut]
        );
        foreach ($stale as $peer) {
            $this->leave($classId, (string) $peer['peer_id'], (int) $peer['user_id'], (string) $peer['role']);
        }
    }

    public function publicState(array $class): array
    {
        return [
            'id' => (int) $class['id'],
            'title' => $class['title'],
            'status' => $class['status'],
            'course_title' => $class['course_title'] ?? '',
            'host_name' => $class['host_name'] ?? 'Teacher',
            'started_at' => $class['started_at'] ?? null,
        ];
    }

    public function peerRow(int $classId, string $peerId): ?array
    {
        return $this->db()->fetch(
            'SELECT * FROM live_class_peers WHERE class_id = ? AND peer_id = ? LIMIT 1',
            [$classId, $peerId]
        );
    }

    public function canShareScreen(array $peer, string $panelRole): bool
    {
        if ($panelRole === 'host' || ($peer['role'] ?? '') === 'host') {
            return true;
        }
        return (int) ($peer['is_presenter'] ?? 0) === 1;
    }

    public function setScreenOn(int $classId, string $peerId, bool $on): void
    {
        if ($on) {
            $this->db()->update('live_class_peers', [
                'screen_on' => 0,
            ], 'class_id = ? AND peer_id != ?', [$classId, $peerId]);
        }
        $this->db()->update('live_class_peers', [
            'screen_on' => $on ? 1 : 0,
            'last_seen_at' => date('Y-m-d H:i:s'),
        ], 'class_id = ? AND peer_id = ?', [$classId, $peerId]);
    }

    public function setPresenter(int $classId, string $fromPeer, string $targetPeer, bool $on): ?array
    {
        $target = $this->peerRow($classId, $targetPeer);
        if (!$target || ($target['role'] ?? '') === 'host') {
            return null;
        }
        $previous = $this->db()->fetchAll(
            'SELECT peer_id, screen_on FROM live_class_peers WHERE class_id = ? AND is_presenter = 1 AND role = ?',
            [$classId, 'student']
        );
        $this->db()->update('live_class_peers', [
            'is_presenter' => 0,
        ], 'class_id = ? AND role = ?', [$classId, 'student']);
        if ($on) {
            $this->db()->update('live_class_peers', [
                'is_presenter' => 1,
                'last_seen_at' => date('Y-m-d H:i:s'),
            ], 'class_id = ? AND peer_id = ?', [$classId, $targetPeer]);
        }
        foreach ($previous as $row) {
            $oldId = (string) $row['peer_id'];
            if ($on && $oldId === $targetPeer) {
                continue;
            }
            $this->sendSignal($classId, $fromPeer, $oldId, 'control', [
                'action' => 'presenter',
                'on' => false,
                'peer_id' => $oldId,
            ]);
            $this->setScreenOn($classId, $oldId, false);
            $this->sendSignal($classId, $fromPeer, $oldId, 'control', ['action' => 'stop-screen']);
        }
        $this->sendSignal($classId, $fromPeer, $targetPeer, 'control', [
            'action' => 'presenter',
            'on' => $on,
            'peer_id' => $targetPeer,
        ]);
        if (!$on) {
            $this->setScreenOn($classId, $targetPeer, false);
            $this->sendSignal($classId, $fromPeer, $targetPeer, 'control', ['action' => 'stop-screen']);
        }
        return $this->peerRow($classId, $targetPeer);
    }

    public function safePeerId(string $peerId): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $peerId) ?? '';
    }

    public function mediaDir(int $classId, string $peerId): string
    {
        $safe = $this->safePeerId($peerId);
        $dir = STORAGE_PATH . '/live-class/' . $classId . '/' . $safe;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function saveFrame(int $classId, string $peerId, string $tmpPath): bool
    {
        if ($this->safePeerId($peerId) === '' || !is_file($tmpPath)) {
            return false;
        }
        $size = (int) filesize($tmpPath);
        if ($size < 32 || $size > 1200000) {
            return false;
        }
        $info = @getimagesize($tmpPath);
        if (!$info || (int) ($info[2] ?? 0) !== IMAGETYPE_JPEG) {
            return false;
        }
        $dir = $this->mediaDir($classId, $peerId);
        $tmp = $dir . '/frame.jpg.tmp';
        $dest = $dir . '/frame.jpg';
        if (!@move_uploaded_file($tmpPath, $tmp) && !@copy($tmpPath, $tmp)) {
            return false;
        }
        return @rename($tmp, $dest);
    }

    public function saveAudio(int $classId, string $peerId, string $tmpPath): bool
    {
        if ($this->safePeerId($peerId) === '' || !is_file($tmpPath)) {
            return false;
        }
        $size = (int) filesize($tmpPath);
        if ($size < 8 || $size > 400000) {
            return false;
        }
        $dir = $this->mediaDir($classId, $peerId);
        $seq = ((int) @file_get_contents($dir . '/seq.txt')) + 1;
        @file_put_contents($dir . '/seq.txt', (string) $seq);
        $tmp = $dir . '/audio.bin.tmp';
        $dest = $dir . '/audio.bin';
        if (!@move_uploaded_file($tmpPath, $tmp) && !@copy($tmpPath, $tmp)) {
            return false;
        }
        if (!@rename($tmp, $dest)) {
            return false;
        }
        @file_put_contents($dir . '/audio.seq', (string) $seq);
        return true;
    }

    public function framePath(int $classId, string $peerId): ?string
    {
        $safe = $this->safePeerId($peerId);
        if ($safe === '') {
            return null;
        }
        $file = STORAGE_PATH . '/live-class/' . $classId . '/' . $safe . '/frame.jpg';
        return is_file($file) ? $file : null;
    }

    public function audioPath(int $classId, string $peerId): ?string
    {
        $safe = $this->safePeerId($peerId);
        if ($safe === '') {
            return null;
        }
        $file = STORAGE_PATH . '/live-class/' . $classId . '/' . $safe . '/audio.bin';
        return is_file($file) ? $file : null;
    }

    public function audioSeq(int $classId, string $peerId): int
    {
        $safe = $this->safePeerId($peerId);
        if ($safe === '') {
            return 0;
        }
        $file = STORAGE_PATH . '/live-class/' . $classId . '/' . $safe . '/audio.seq';
        return is_file($file) ? (int) file_get_contents($file) : 0;
    }

    public function frameFresh(int $classId, string $peerId, int $maxAge = 8): bool
    {
        $file = $this->framePath($classId, $peerId);
        if (!$file) {
            return false;
        }
        return (time() - (int) filemtime($file)) <= $maxAge;
    }

    public function clearPeerMedia(int $classId, string $peerId): void
    {
        $safe = $this->safePeerId($peerId);
        if ($safe === '') {
            return;
        }
        $dir = STORAGE_PATH . '/live-class/' . $classId . '/' . $safe;
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($dir);
    }

    public function clearClassMedia(int $classId): void
    {
        $dir = STORAGE_PATH . '/live-class/' . $classId;
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*', GLOB_ONLYDIR) ?: [] as $peerDir) {
            foreach (glob($peerDir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($peerDir);
        }
        @rmdir($dir);
    }
}

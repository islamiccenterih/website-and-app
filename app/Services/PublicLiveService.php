<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Public website broadcast (Admin Live now). Separate from student live classes.
 * Sized for about 10–100 viewers on ordinary hosting: one publisher, PHP signalling.
 */
final class PublicLiveService
{
    public const MAX_VIEWERS = 100;
    public const HOST_STALE_SEC = 90;
    public const VIEWER_STALE_SEC = 18;

    public function db(): Database
    {
        return Database::get();
    }

    public function current(): ?array
    {
        $row = $this->db()->fetch(
            "SELECT * FROM public_live_sessions WHERE status = 'live' ORDER BY id DESC LIMIT 1"
        );
        return $row ?: null;
    }

    /**
     * @return array{live:bool,title:string,viewers:int,started_at:?string,source:string}|null
     */
    public function publicStatus(): array
    {
        $session = $this->current();
        if (!$session) {
            return [
                'live' => false,
                'title' => '',
                'viewers' => 0,
                'started_at' => null,
                'source' => '',
            ];
        }
        // Never end a broadcast from a public page hit — header/status polls
        // would kill a live if the host heartbeat lagged. Only drop stale watchers.
        $this->sweepStaleViewers((int) $session['id']);
        return [
            'live' => true,
            'title' => (string) $session['title'],
            'viewers' => $this->viewerCount((int) $session['id']),
            'started_at' => (string) $session['started_at'],
            'source' => (string) $session['source'],
        ];
    }

    public function find(int $id): ?array
    {
        return $this->db()->fetch('SELECT * FROM public_live_sessions WHERE id = ?', [$id]);
    }

    public function start(int $adminId, string $title, string $source): array
    {
        $existing = $this->current();
        if ($existing) {
            $this->sweep((int) $existing['id']);
            $existing = $this->current();
        }
        if ($existing) {
            if ((int) $existing['admin_id'] === $adminId) {
                $host = $this->upsertHost((int) $existing['id'], $adminId);
                $this->setSource((int) $existing['id'], $source);
                $fresh = $this->find((int) $existing['id']) ?: $existing;
                return ['session' => $fresh, 'host' => $host, 'resumed' => true];
            }
            throw new \RuntimeException('A public live is already on. End it before starting another.');
        }

        $title = mb_substr(trim($title) !== '' ? trim($title) : 'Live from ' . site_name(), 0, 180);
        $source = $source === 'screen' ? 'screen' : 'camera';
        $now = date('Y-m-d H:i:s');
        $id = $this->db()->insert('public_live_sessions', [
            'admin_id' => $adminId,
            'title' => $title,
            'source' => $source,
            'status' => 'live',
            'started_at' => $now,
            'ended_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $host = $this->upsertHost($id, $adminId);
        $session = $this->find($id);
        return ['session' => $session, 'host' => $host, 'resumed' => false];
    }

    public function setSource(int $sessionId, string $source): void
    {
        $source = $source === 'screen' ? 'screen' : 'camera';
        $this->db()->update('public_live_sessions', [
            'source' => $source,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$sessionId]);
    }

    public function end(int $sessionId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db()->update('public_live_sessions', [
            'status' => 'ended',
            'ended_at' => $now,
            'updated_at' => $now,
        ], 'id = ?', [$sessionId]);
        $this->db()->delete('public_live_signals', 'session_id = ?', [$sessionId]);
        $this->db()->delete('public_live_comments', 'session_id = ?', [$sessionId]);
        $this->db()->delete('public_live_peers', 'session_id = ?', [$sessionId]);
        $this->clearMedia($sessionId);
    }

    public function mediaDir(int $sessionId): string
    {
        $dir = STORAGE_PATH . '/live/' . $sessionId;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function saveFrame(int $sessionId, string $tmpPath): bool
    {
        if (!is_file($tmpPath)) {
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
        $dir = $this->mediaDir($sessionId);
        $tmp = $dir . '/frame.jpg.tmp';
        $dest = $dir . '/frame.jpg';
        if (!@move_uploaded_file($tmpPath, $tmp) && !@copy($tmpPath, $tmp)) {
            return false;
        }
        return @rename($tmp, $dest);
    }

    public function saveAudio(int $sessionId, string $tmpPath): bool
    {
        if (!is_file($tmpPath)) {
            return false;
        }
        $size = (int) filesize($tmpPath);
        if ($size < 8 || $size > 250000) {
            return false;
        }
        $dir = $this->mediaDir($sessionId);
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

    public function framePath(int $sessionId): ?string
    {
        $file = STORAGE_PATH . '/live/' . $sessionId . '/frame.jpg';
        return is_file($file) ? $file : null;
    }

    public function audioPath(int $sessionId): ?string
    {
        $file = STORAGE_PATH . '/live/' . $sessionId . '/audio.bin';
        return is_file($file) ? $file : null;
    }

    public function audioSeq(int $sessionId): int
    {
        $file = STORAGE_PATH . '/live/' . $sessionId . '/audio.seq';
        return is_file($file) ? (int) file_get_contents($file) : 0;
    }

    public function clearMedia(int $sessionId): void
    {
        $dir = STORAGE_PATH . '/live/' . $sessionId;
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($dir);
    }

    /**
     * @return array{peer_id:string,token:string,role:string}
     */
    public function upsertHost(int $sessionId, int $adminId): array
    {
        $existing = $this->db()->fetch(
            "SELECT * FROM public_live_peers WHERE session_id = ? AND role = 'host' LIMIT 1",
            [$sessionId]
        );
        $now = date('Y-m-d H:i:s');
        if ($existing) {
            $this->db()->update('public_live_peers', [
                'last_seen_at' => $now,
            ], 'id = ?', [(int) $existing['id']]);
            $_SESSION['public_live_host'] = [
                'session_id' => $sessionId,
                'peer_id' => $existing['peer_id'],
                'token' => $existing['token'],
                'admin_id' => $adminId,
            ];
            return [
                'peer_id' => (string) $existing['peer_id'],
                'token' => (string) $existing['token'],
                'role' => 'host',
            ];
        }
        $peerId = 'h' . bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(16));
        $this->db()->insert('public_live_peers', [
            'session_id' => $sessionId,
            'peer_id' => $peerId,
            'token' => $token,
            'role' => 'host',
            'last_seen_at' => $now,
            'created_at' => $now,
        ]);
        $_SESSION['public_live_host'] = [
            'session_id' => $sessionId,
            'peer_id' => $peerId,
            'token' => $token,
            'admin_id' => $adminId,
        ];
        return ['peer_id' => $peerId, 'token' => $token, 'role' => 'host'];
    }

    /**
     * @return array{peer_id:string,token:string,role:string,host_id:string}
     */
    public function joinViewer(int $sessionId): array
    {
        $session = $this->find($sessionId);
        if (!$session || ($session['status'] ?? '') !== 'live') {
            throw new \RuntimeException('The center is not live right now.');
        }
        $this->sweepStaleViewers($sessionId);
        if ($this->viewerCount($sessionId) >= self::MAX_VIEWERS) {
            throw new \RuntimeException('This live is full (100 people). Try again in a moment.');
        }
        $host = $this->hostPeer($sessionId);
        if (!$host) {
            throw new \RuntimeException('The live is starting. Refresh in a moment.');
        }
        $now = date('Y-m-d H:i:s');
        $saved = $_SESSION['public_live_watch'] ?? null;
        if (is_array($saved) && (int) ($saved['session_id'] ?? 0) === $sessionId) {
            $row = $this->peer($sessionId, (string) ($saved['peer_id'] ?? ''), (string) ($saved['token'] ?? ''));
            if ($row && ($row['role'] ?? '') === 'viewer') {
                $this->db()->update('public_live_peers', ['last_seen_at' => $now], 'id = ?', [(int) $row['id']]);
                return [
                    'peer_id' => (string) $row['peer_id'],
                    'token' => (string) $row['token'],
                    'role' => 'viewer',
                    'host_id' => (string) $host['peer_id'],
                ];
            }
        }
        $peerId = 'v' . bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(16));
        $this->db()->insert('public_live_peers', [
            'session_id' => $sessionId,
            'peer_id' => $peerId,
            'token' => $token,
            'role' => 'viewer',
            'last_seen_at' => $now,
            'created_at' => $now,
        ]);
        $_SESSION['public_live_watch'] = [
            'session_id' => $sessionId,
            'peer_id' => $peerId,
            'token' => $token,
        ];
        return [
            'peer_id' => $peerId,
            'token' => $token,
            'role' => 'viewer',
            'host_id' => (string) $host['peer_id'],
        ];
    }

    public function peer(int $sessionId, string $peerId, string $token): ?array
    {
        if ($peerId === '' || $token === '') {
            return null;
        }
        $row = $this->db()->fetch(
            'SELECT * FROM public_live_peers WHERE session_id = ? AND peer_id = ? LIMIT 1',
            [$sessionId, $peerId]
        );
        if (!$row || !hash_equals((string) $row['token'], $token)) {
            return null;
        }
        return $row;
    }

    public function hostPeer(int $sessionId): ?array
    {
        return $this->db()->fetch(
            "SELECT * FROM public_live_peers WHERE session_id = ? AND role = 'host' LIMIT 1",
            [$sessionId]
        );
    }

    public function heartbeat(int $sessionId, string $peerId): void
    {
        $this->db()->update('public_live_peers', [
            'last_seen_at' => date('Y-m-d H:i:s'),
        ], 'session_id = ? AND peer_id = ?', [$sessionId, $peerId]);
    }

    /**
     * @return list<string>
     */
    public function viewerIds(int $sessionId): array
    {
        $rows = $this->db()->fetchAll(
            "SELECT peer_id FROM public_live_peers WHERE session_id = ? AND role = 'viewer' ORDER BY id ASC",
            [$sessionId]
        );
        return array_map(static fn(array $r): string => (string) $r['peer_id'], $rows);
    }

    public function viewerCount(int $sessionId): int
    {
        return (int) $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM public_live_peers WHERE session_id = ? AND role = 'viewer'",
            [$sessionId]
        );
    }

    public function sendSignal(int $sessionId, string $from, string $to, string $kind, mixed $payload): void
    {
        $this->db()->insert('public_live_signals', [
            'session_id' => $sessionId,
            'from_peer' => $from,
            'to_peer' => $to,
            'kind' => $kind,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return list<array{from:string,kind:string,payload:mixed}>
     */
    public function takeSignals(int $sessionId, string $peerId): array
    {
        $rows = $this->db()->fetchAll(
            'SELECT id, from_peer, kind, payload FROM public_live_signals
             WHERE session_id = ? AND to_peer = ? ORDER BY id ASC LIMIT 250',
            [$sessionId, $peerId]
        );
        if ($rows) {
            $ids = array_map(static fn($r) => (int) $r['id'], $rows);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $this->db()->execute("DELETE FROM public_live_signals WHERE id IN ($placeholders)", $ids);
        }
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'from' => (string) $row['from_peer'],
                'kind' => (string) $row['kind'],
                'payload' => json_decode((string) $row['payload'], true),
            ];
        }
        return $out;
    }

    public function leave(int $sessionId, string $peerId): void
    {
        $this->db()->delete('public_live_peers', 'session_id = ? AND peer_id = ?', [$sessionId, $peerId]);
        $this->db()->delete(
            'public_live_signals',
            'session_id = ? AND (from_peer = ? OR to_peer = ?)',
            [$sessionId, $peerId, $peerId]
        );
    }

    /**
     * @return array{id:int,name:string,body:string,at:string}
     */
    public function addComment(int $sessionId, string $peerId, string $name, string $body): array
    {
        $session = $this->find($sessionId);
        if (!$session || ($session['status'] ?? '') !== 'live') {
            throw new \RuntimeException('The center is not live right now.');
        }
        $body = trim((string) preg_replace('/\s+/u', ' ', $body));
        if ($body === '') {
            throw new \RuntimeException('Write a comment first.');
        }
        if (mb_strlen($body) > 200) {
            $body = mb_substr($body, 0, 200);
        }
        $name = trim($name);
        if ($name === '') {
            $name = 'Viewer';
        }
        $name = mb_substr($name, 0, 40);
        $last = $this->db()->fetch(
            'SELECT created_at FROM public_live_comments WHERE session_id = ? AND peer_id = ? ORDER BY id DESC LIMIT 1',
            [$sessionId, $peerId]
        );
        if ($last && (time() - strtotime((string) $last['created_at'])) < 2) {
            throw new \RuntimeException('Wait a moment before sending another comment.');
        }
        $now = date('Y-m-d H:i:s');
        $id = $this->db()->insert('public_live_comments', [
            'session_id' => $sessionId,
            'peer_id' => $peerId,
            'name' => $name,
            'body' => $body,
            'created_at' => $now,
        ]);
        return [
            'id' => (int) $id,
            'name' => $name,
            'body' => $body,
            'at' => $now,
        ];
    }

    /**
     * @return list<array{id:int,name:string,body:string,at:string}>
     */
    public function recentComments(int $sessionId, int $afterId = 0): array
    {
        if ($afterId > 0) {
            $rows = $this->db()->fetchAll(
                'SELECT id, name, body, created_at FROM public_live_comments
                 WHERE session_id = ? AND id > ? ORDER BY id ASC LIMIT 80',
                [$sessionId, $afterId]
            );
        } else {
            $rows = $this->db()->fetchAll(
                'SELECT id, name, body, created_at FROM public_live_comments
                 WHERE session_id = ? ORDER BY id DESC LIMIT 50',
                [$sessionId]
            );
            $rows = array_reverse($rows);
        }
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'body' => (string) $row['body'],
                'at' => (string) $row['created_at'],
            ];
        }
        return $out;
    }

    public function sweepStaleViewers(int $sessionId): void
    {
        $viewCut = date('Y-m-d H:i:s', time() - self::VIEWER_STALE_SEC);
        $staleViewers = $this->db()->fetchAll(
            "SELECT peer_id FROM public_live_peers WHERE session_id = ? AND role = 'viewer' AND last_seen_at < ?",
            [$sessionId, $viewCut]
        );
        foreach ($staleViewers as $row) {
            $this->leave($sessionId, (string) $row['peer_id']);
        }
    }

    public function sweep(int $sessionId): void
    {
        $this->sweepStaleViewers($sessionId);
        $hostCut = date('Y-m-d H:i:s', time() - self::HOST_STALE_SEC);
        $host = $this->hostPeer($sessionId);
        if (!$host || (string) $host['last_seen_at'] < $hostCut) {
            $this->end($sessionId);
        }
    }
}

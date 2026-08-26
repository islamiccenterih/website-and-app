<?php

declare(strict_types=1);

namespace App\Models;

final class Fatwa extends Model
{
    protected static string $table = 'fatawa';

    public static function published(): array
    {
        return static::db()->fetchAll(
            "SELECT * FROM fatawa WHERE status = 'published' ORDER BY issued_on DESC, id DESC"
        );
    }

    public static function todayDate(): string
    {
        $name = (string) (function_exists('setting') ? setting('timezone', cfg('app.timezone', 'Asia/Kolkata')) : cfg('app.timezone', 'Asia/Kolkata'));
        try {
            $tz = new \DateTimeZone($name !== '' ? $name : 'Asia/Kolkata');
        } catch (\Exception) {
            $tz = new \DateTimeZone('Asia/Kolkata');
        }
        return (new \DateTimeImmutable('now', $tz))->format('Y-m-d');
    }

    public static function archive(?int $exceptId = null): array
    {
        $sql = "SELECT * FROM fatawa WHERE status = 'published' AND issued_on < ?";
        $params = [self::todayDate()];
        if ($exceptId) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }
        $sql .= ' ORDER BY issued_on DESC, id DESC';
        return static::db()->fetchAll($sql, $params);
    }

    public static function today(): ?array
    {
        return static::db()->fetch(
            "SELECT * FROM fatawa WHERE status = 'published' AND issued_on = ? ORDER BY id DESC LIMIT 1",
            [self::todayDate()]
        );
    }

    public static function bySlug(string $slug): ?array
    {
        return static::db()->fetch(
            "SELECT * FROM fatawa WHERE slug = ? AND status = 'published' LIMIT 1",
            [$slug]
        );
    }

    public static function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug !== '' ? $slug : 'fatwa';
        $candidate = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM fatawa WHERE slug = ?';
            $params = [$candidate];
            if ($ignoreId) {
                $sql .= ' AND id != ?';
                $params[] = $ignoreId;
            }
            $exists = static::db()->fetch($sql . ' LIMIT 1', $params);
            if (!$exists) {
                return $candidate;
            }
            $candidate = $base . '-' . $i;
            $i++;
        }
    }

    /**
     * @return list<array{code:string,label:string,dir:string,lang:string,title:string,body:string,primary:bool}>
     */
    public static function languageBlocks(array $row): array
    {
        $lang = current_lang();
        $order = match ($lang) {
            'ar' => ['ar', 'en', 'hi'],
            'hi' => ['hi', 'ar', 'en'],
            'ur' => ['ar', 'hi', 'en'],
            default => ['en', 'ar', 'hi'],
        };
        $labels = ['ar' => 'العربية', 'en' => 'English', 'hi' => 'हिन्दी'];
        $dirs = ['ar' => 'rtl', 'en' => 'ltr', 'hi' => 'ltr'];
        $blocks = [];
        foreach ($order as $code) {
            $title = trim((string) ($row['title_' . $code] ?? ''));
            $body = trim((string) ($row['body_' . $code] ?? ''));
            if ($title === '' && $body === '') {
                continue;
            }
            $blocks[] = [
                'code' => $code,
                'label' => $labels[$code],
                'dir' => $dirs[$code],
                'lang' => $code,
                'title' => $title,
                'body' => $body,
                'primary' => $blocks === [],
            ];
        }
        return $blocks;
    }

    /**
     * @return list<string>
     */
    public static function langCodes(array $row): array
    {
        $codes = [];
        foreach (['ar' => 'AR', 'en' => 'EN', 'hi' => 'HI'] as $code => $label) {
            if (trim((string) ($row['title_' . $code] ?? '')) !== '' || trim((string) ($row['body_' . $code] ?? '')) !== '') {
                $codes[] = $label;
            }
        }
        return $codes;
    }

    public static function excerpt(array $row, int $len = 240): string
    {
        $blocks = self::languageBlocks($row);
        $body = trim((string) ($blocks[0]['body'] ?? ''));
        if ($body === '') {
            return '';
        }
        if (mb_strlen($body) <= $len) {
            return $body;
        }
        return rtrim(mb_substr($body, 0, $len)) . '…';
    }

    public static function cardTitle(array $row): string
    {
        $blocks = self::languageBlocks($row);
        if ($blocks && $blocks[0]['title'] !== '') {
            return $blocks[0]['title'];
        }
        if ($blocks && $blocks[0]['body'] !== '') {
            return mb_substr($blocks[0]['body'], 0, 80);
        }
        return 'Fatwa';
    }

    public static function unansweredCount(int $fatwaId = 0): int
    {
        if ($fatwaId > 0) {
            return (int) static::db()->fetchColumn(
                "SELECT COUNT(*) FROM fatwa_questions WHERE fatwa_id = ? AND status = 'new'",
                [$fatwaId]
            );
        }
        return (int) static::db()->fetchColumn(
            "SELECT COUNT(*) FROM fatwa_questions WHERE status = 'new'"
        );
    }
}

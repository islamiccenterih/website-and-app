<?php

declare(strict_types=1);

namespace App\Models;

final class CalendarMonth extends Model
{
    protected static string $table = 'calendar_months';

    public static function published(): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM calendar_months WHERE status = ? ORDER BY is_current DESC, sort_order ASC, id DESC',
            ['published']
        );
    }

    public static function events(int $monthId): array
    {
        return static::db()->fetchAll(
            'SELECT * FROM calendar_events WHERE calendar_month_id = ? ORDER BY sort_order ASC, id ASC',
            [$monthId]
        );
    }
}

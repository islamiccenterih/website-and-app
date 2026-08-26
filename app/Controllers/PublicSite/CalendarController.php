<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Models\CalendarMonth;
use App\Services\IslamicCalendarService;

final class CalendarController extends Controller
{
    public function index(): void
    {
        $hy = isset($_GET['hy']) ? (int) $_GET['hy'] : null;
        $hm = isset($_GET['hm']) ? (int) $_GET['hm'] : null;
        $calendar = (new IslamicCalendarService())->month($hy, $hm);

        $months = [];
        foreach (CalendarMonth::published() as $month) {
            if ($this->isPlaceholderMonth($month)) {
                continue;
            }
            $events = [];
            foreach (CalendarMonth::events((int) $month['id']) as $event) {
                if ($this->isPlaceholderText((string) ($event['title'] ?? ''))
                    || $this->isPlaceholderText((string) ($event['description'] ?? ''))
                ) {
                    continue;
                }
                $events[] = $event;
            }
            $month['events'] = $events;
            $months[] = $month;
        }

        $this->view('public/calendar', [
            'pageTitle' => 'Islamic Calendar — ' . site_name(),
            'metaDescription' => 'Hijri calendar with today’s Islamic date, month grid, and important days of worship.',
            'calendar' => $calendar,
            'months' => $months,
        ]);
    }

    /**
     * @param array<string, mixed> $month
     */
    private function isPlaceholderMonth(array $month): bool
    {
        $blob = trim((string) ($month['title'] ?? '') . ' ' . ($month['notes'] ?? '') . ' ' . ($month['image_path'] ?? ''));
        return $this->isPlaceholderText($blob);
    }

    private function isPlaceholderText(string $text): bool
    {
        $lower = mb_strtolower($text);
        return str_contains($lower, 'demo data')
            || str_contains($lower, 'placeholder')
            || str_contains($lower, 'ocr, if available');
    }
}

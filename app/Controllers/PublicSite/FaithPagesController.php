<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Services\FaithContentService;
use App\Services\InheritanceService;
use App\Services\QuranDuaService;

final class FaithPagesController extends Controller
{
    public function dailyQuran(): void
    {
        $faith = (new FaithContentService())->bundle();
        $this->view('public/daily-quran', [
            'pageTitle' => page_copy('daily_quran', 'title', 'Daily Quran') . ' — ' . site_name(),
            'metaDescription' => page_copy('daily_quran', 'lead', 'An ayah, a short tafsir, and a hadith for today.'),
            'fallbackAyah' => QuranDuaService::current(),
            'hadith' => $faith['hadith'],
        ]);
    }

    public function dailyDuas(): void
    {
        $faith = (new FaithContentService())->bundle();
        $this->view('public/daily-duas', [
            'pageTitle' => page_copy('daily_duas', 'title', 'Daily Duas') . ' — ' . site_name(),
            'metaDescription' => page_copy('daily_duas', 'lead', 'Duas for morning, evening, food, travel, home, illness, and janazah.'),
            'groups' => $faith['duas'],
        ]);
    }

    public function allahNames(): void
    {
        $faith = (new FaithContentService())->bundle();
        $this->view('public/allah-names', [
            'pageTitle' => page_copy('allah_names', 'title', '99 Allah Names') . ' — ' . site_name(),
            'metaDescription' => page_copy('allah_names', 'lead', 'The ninety-nine names of Allah with meaning and recitation.'),
            'names' => $faith['names'],
        ]);
    }

    public function quranReader(): void
    {
        $this->view('public/quran-reader', [
            'pageTitle' => page_copy('quran_reader', 'title', 'Quran Reader') . ' — ' . site_name(),
            'metaDescription' => page_copy('quran_reader', 'lead', 'Read, search, and listen to the Qur’an with Urdu and Hindi meaning.'),
        ]);
    }

    public function familyShares(): void
    {
        $this->view('public/family-shares', [
            'pageTitle' => page_copy('family_shares', 'title', 'Family Shares') . ' — ' . site_name(),
            'metaDescription' => page_copy('family_shares', 'lead', 'A simple mirath calculator for spouse, children, and parents.'),
        ]);
    }

    public function janazah(): void
    {
        $faith = (new FaithContentService())->bundle();
        $this->view('public/janazah', [
            'pageTitle' => page_copy('janazah', 'title', 'Janazah Steps') . ' — ' . site_name(),
            'metaDescription' => page_copy('janazah', 'lead', 'Ghusl, kafan, janazah salah, and burial with duas.'),
            'steps' => $faith['janazah'],
        ]);
    }

    public function hajjUmrah(): void
    {
        $faith = (new FaithContentService())->bundle();
        $this->view('public/hajj-umrah', [
            'pageTitle' => page_copy('hajj_umrah', 'title', 'Hajj & Umrah') . ' — ' . site_name(),
            'metaDescription' => page_copy('hajj_umrah', 'lead', 'Checklist and duas for Hajj and Umrah.'),
            'hajj' => $faith['hajj'],
        ]);
    }

    public function tasbeeh(): void
    {
        $this->view('public/tasbeeh', [
            'pageTitle' => page_copy('tasbeeh', 'title', 'Daily Tasbeeh') . ' — ' . site_name(),
            'metaDescription' => page_copy('tasbeeh', 'lead', 'Digital tasbeeh with 33, 99, or a custom count.'),
        ]);
    }

    public function dailyApi(): void
    {
        $faith = (new FaithContentService())->bundle();
        $ayah = QuranDuaService::current();
        $hadith = $faith['hadith'];
        $tz = new \DateTimeZone('Asia/Kolkata');
        $day = (int) (new \DateTimeImmutable('now', $tz))->format('z');
        $row = $hadith[$day % max(1, count($hadith))] ?? null;
        json_response([
            'ok' => true,
            'ayah' => $ayah,
            'hadith' => $row,
            'hadith_count' => count($hadith),
        ]);
    }
}

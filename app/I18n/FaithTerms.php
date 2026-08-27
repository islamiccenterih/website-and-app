<?php

declare(strict_types=1);

namespace App\I18n;

/**
 * Optional wrappers for fixed English phrases in templates (ft()).
 * Admin-typed content is stored and shown exactly after ContentTerms::bake().
 */
final class FaithTerms
{
    /** @var list<array{0:string,1:string}>|null */
    private static ?array $sorted = null;

    /** @var list<array{0:string,1:string}>|null */
    private static ?array $sortedOut = null;

    public static function apply(string $text): string
    {
        if ($text === '') {
            return $text;
        }
        $text = self::collapseNested($text);
        $slots = [];
        foreach (self::sortedByOutput() as [$needle, $out]) {
            if ($out === '' || $out === $needle) {
                continue;
            }
            if (!str_contains($text, $out)) {
                continue;
            }
            $token = self::hold($slots, $out);
            $text = str_replace($out, $token, $text);
        }
        foreach (self::sorted() as [$needle, $out]) {
            $text = preg_replace_callback(
                '/(?<![\p{L}\p{N}])' . preg_quote($needle, '/') . '(?![\p{L}\p{N}])/iu',
                static function () use (&$slots, $out): string {
                    return self::hold($slots, $out);
                },
                $text
            ) ?? $text;
        }
        if ($slots) {
            $text = strtr($text, $slots);
        }
        return $text;
    }

    /** If someone pastes the bilingual form back into a form, store the English word. */
    public static function toStoredEnglish(string $text): string
    {
        if ($text === '') {
            return $text;
        }
        $text = self::collapseNested($text);
        foreach (self::sortedByOutput() as [$needle, $out]) {
            if ($out !== '' && $out !== $needle) {
                $text = str_replace($out, $needle, $text);
            }
        }
        return $text;
    }

    /** Flatten رمضان (رمضان (Ramadan)) and رمضان (Ramadan) (Ramadan) back to one wrap. */
    private static function collapseNested(string $text): string
    {
        $guard = 0;
        $changed = true;
        while ($changed && $guard++ < 8) {
            $changed = false;
            foreach (self::sortedByOutput() as [$needle, $out]) {
                if ($out === '' || $out === $needle) {
                    continue;
                }
                $pos = mb_strrpos($out, ' (');
                if ($pos === false) {
                    continue;
                }
                $head = mb_substr($out, 0, $pos);
                $paren = mb_substr($out, $pos);
                $nested = $head . ' (' . $out . ')';
                if (str_contains($text, $nested)) {
                    $text = str_replace($nested, $out, $text);
                    $changed = true;
                }
                $repeated = $out . $paren;
                if (str_contains($text, $repeated)) {
                    $text = str_replace($repeated, $out, $text);
                    $changed = true;
                }
            }
        }
        return $text;
    }

    /** @param array<string, string> $slots */
    private static function hold(array &$slots, string $value): string
    {
        $token = "\x1A" . count($slots) . "\x1A";
        $slots[$token] = $value;
        return $token;
    }

    /** @return list<array{0:string,1:string}> */
    private static function sortedByOutput(): array
    {
        if (self::$sortedOut !== null) {
            return self::$sortedOut;
        }
        $pairs = self::pairs();
        uasort($pairs, static function (string $a, string $b): int {
            return mb_strlen($b) <=> mb_strlen($a);
        });
        $out = [];
        foreach ($pairs as $needle => $repl) {
            $out[] = [$needle, $repl];
        }
        self::$sortedOut = $out;
        return $out;
    }

    /** @return list<array{0:string,1:string}> */
    private static function sorted(): array
    {
        if (self::$sorted !== null) {
            return self::$sorted;
        }
        $pairs = self::pairs();
        uksort($pairs, static function (string $a, string $b): int {
            return mb_strlen($b) <=> mb_strlen($a);
        });
        $out = [];
        foreach ($pairs as $needle => $repl) {
            $out[] = [$needle, $repl];
        }
        self::$sorted = $out;
        return $out;
    }

    /** @return array<string, string> */
    private static function pairs(): array
    {
        $bismillah = 'بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ';
        return [
            $bismillah => $bismillah . ' (In the name of Allah)',
            'Ummahat-ul-Mu’mineen' => 'أمهات المؤمنین (Ummahat-ul-Mu’mineen)',
            'Ummahat-ul-Mu\'mineen' => 'أمهات المؤمنین (Ummahat-ul-Mu’mineen)',
            'Khulafa-e-Rashideen' => 'خلفاء راشدین (Khulafa-e-Rashideen)',
            'Seerat-un-Nabi ﷺ' => 'سیرت النبی ﷺ (Seerat-un-Nabi ﷺ)',
            'Seerat-un-Nabi' => 'سیرت النبی (Seerat-un-Nabi)',
            'Adyaan-e-Batila' => 'ادیانِ باطلہ (Adyaan-e-Batila)',
            'Ghusl-e-Mayyit' => 'غسل میت (Ghusl-e-Mayyit)',
            'Tafseer Ul Quran' => 'تفسير القرآن (Tafseer Ul Quran)',
            'Tajweed Ul Quran' => 'تجويد القرآن (Tajweed Ul Quran)',
            'Samaat e Quran' => 'سماع القرآن (Samaat e Quran)',
            'Islamic Center Information Hub' => 'مرکزِ اسلامی معلومات (Islamic Center Information Hub)',
            'Islamic Calendar' => 'تقویمِ اسلامی (Islamic Calendar)',
            'Islamic Holidays' => 'ایامِ اسلامیہ (Islamic Holidays)',
            'Islamic New Year' => 'رأس السنة الهجرية (Islamic New Year)',
            'Islamic Center' => 'مرکزِ اسلامی (Islamic Center)',
            'Laylat al-Qadr' => 'ليلة القدر (Laylat al-Qadr)',
            'Isra and Miʿraj' => 'الإسراء والمعراج (Isra and Miʿraj)',
            'Isra and Miraj' => 'الإسراء والمعراج (Isra and Miraj)',
            'Nisf Shaʿban' => 'ليلة النصف من شعبان (Nisf Shaʿban)',
            'Nisf Shaban' => 'ليلة النصف من شعبان (Nisf Shaban)',
            'Shab-e-Barat' => 'شبِ برات (Shab-e-Barat)',
            'Mawlid an-Nabi ﷺ' => 'المولد النبوي (Mawlid an-Nabi ﷺ)',
            'Mawlid an-Nabi' => 'المولد النبوي (Mawlid an-Nabi)',
            'Day of ʿArafah' => 'يوم عرفة (Day of ʿArafah)',
            'Day of Arafah' => 'يوم عرفة (Day of Arafah)',
            'Eid al-Adha' => 'عید الاضحیٰ (Eid al-Adha)',
            'Eid ul-Adha' => 'عید الاضحیٰ (Eid ul-Adha)',
            'Eid ul-Fitr' => 'عید الفطر (Eid ul-Fitr)',
            'Eid al-Fitr' => 'عید الفطر (Eid al-Fitr)',
            'Ramadan Mubarak' => 'رمضان مبارك (Ramadan Mubarak)',
            'Qibla Direction' => 'قبلہ (Qibla) Direction',
            'Zakat Calculator' => 'زكاة (Zakat) Calculator',
            'Previous fatawa' => 'Previous فتاویٰ (fatawa)',
            'Today’s fatwa' => 'Today’s فتویٰ (fatwa)',
            'Today\'s fatwa' => 'Today’s فتویٰ (fatwa)',
            'Pillars of Islam' => 'Pillars of اسلام (Islam)',
            'Taleemi Karwan' => 'تعلیمی کاروان (Taleemi Karwan)',
            'Aasaan Nikah' => 'Aasaan نکاح (Nikah)',
            'My Partner My Jannah' => 'My Partner My جنة (Jannah)',
            'Insha’Allah' => 'ان شاء اللہ (Insha’Allah)',
            'Insha\'Allah' => 'ان شاء اللہ (Insha’Allah)',
            'Rabiʿ al-awwal' => 'ربیع الاول (Rabiʿ al-awwal)',
            'Rabi al-awwal' => 'ربیع الاول (Rabi al-awwal)',
            'Dhul Hijjah' => 'ذوالحجہ (Dhul Hijjah)',
            'Dhul-Hijjah' => 'ذوالحجہ (Dhul-Hijjah)',
            'Qur’an' => 'قرآن (Qur’an)',
            'Qur\'an' => 'قرآن (Qur’an)',
            'Quran' => 'قرآن (Quran)',
            'Sunnah' => 'سنت (Sunnah)',
            'Shahadah' => 'شهادة (Shahadah)',
            'Salah' => 'صلاة (Salah)',
            'Sawm' => 'صوم (Sawm)',
            'Zakat' => 'زكاة (Zakat)',
            'zakat' => 'زكاة (zakat)',
            'Hajj' => 'حج (Hajj)',
            'Fatawa' => 'فتاویٰ (Fatawa)',
            'fatawa' => 'فتاویٰ (fatawa)',
            'Fatwa' => 'فتویٰ (Fatwa)',
            'fatwa' => 'فتویٰ (fatwa)',
            'Fajr' => 'فجر (Fajr)',
            'Zuhr' => 'ظهر (Zuhr)',
            'Asr' => 'عصر (Asr)',
            'Maghrib' => 'مغرب (Maghrib)',
            'Isha' => 'عشاء (Isha)',
            'Jummah' => 'جمعة (Jummah)',
            'Jumuah' => 'جمعة (Jumuah)',
            'Sehri' => 'سحری (Sehri)',
            'Iftar' => 'افطار (Iftar)',
            'Imsak' => 'امساک (Imsak)',
            'Roza' => 'روزہ (Roza)',
            'Taraweeh' => 'تراويح (Taraweeh)',
            'Ramadan' => 'رمضان (Ramadan)',
            'Muharram' => 'محرم (Muharram)',
            'Ashura' => 'عاشوراء (Ashura)',
            'Kaaba' => 'الكعبة (Kaaba)',
            'Qibla' => 'قبلہ (Qibla)',
            'Deen' => 'دین (Deen)',
            'Duniya' => 'دنیا (Duniya)',
            'Allah' => 'اللہ (Allah)',
            'Duas' => 'دعاء (Duas)',
            'Dua' => 'دعاء (Dua)',
            'iman' => 'ایمان (iman)',
            'Iman' => 'ایمان (Iman)',
            'tawheed' => 'توحید (tawheed)',
            'Tawheed' => 'توحید (Tawheed)',
            'ibadah' => 'عبادة (ibadah)',
            'Ibadah' => 'عبادة (Ibadah)',
            'adab' => 'ادب (adab)',
            'Adab' => 'ادب (Adab)',
            'akhlaq' => 'اخلاق (akhlaq)',
            'Akhlaq' => 'اخلاق (Akhlaq)',
            'tajweed' => 'تجوید (tajweed)',
            'Tajweed' => 'تجوید (Tajweed)',
            'dawah' => 'دعوة (dawah)',
            'Dawah' => 'دعوة (Dawah)',
            'Haya' => 'حياء (Haya)',
            'Hijab' => 'حجاب (Hijab)',
            'Naat' => 'نعت (Naat)',
            'Ijtima' => 'اجتماع (Ijtima)',
            'Taleem' => 'تعلیم (Taleem)',
            'Khidmah' => 'خدمة (Khidmah)',
            'Waqf' => 'وقف (Waqf)',
            'Nikah' => 'نکاح (Nikah)',
            'Nisab' => 'نصاب (nisab)',
            'nisab' => 'نصاب (nisab)',
            'Hanafi' => 'حنفي (Hanafi)',
            'Jannah' => 'جنة (Jannah)',
            'sabr' => 'صبر (sabr)',
            'istiqamat' => 'استقامت (istiqamat)',
            'fadl' => 'فضل (fadl)',
            'Courses' => 'دروس (Courses)',
            'Eid' => 'عید (Eid)',
        ];
    }
}

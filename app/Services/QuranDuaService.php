<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Qur’anic ayah shown in the home hero, above Bismillah.
 * The ayah, Hindi meaning, and surah reference change every hour, Asia/Kolkata.
 */
final class QuranDuaService
{
    /**
     * @return array{arabic:string,hindi:string,surah:string,ayah:string}
     */
    public static function current(?\DateTimeInterface $now = null): array
    {
        $duas = self::all();
        try {
            $tz = new \DateTimeZone((string) (setting('timezone', cfg('app.timezone', 'Asia/Kolkata')) ?: 'Asia/Kolkata'));
        } catch (\Exception) {
            $tz = new \DateTimeZone('Asia/Kolkata');
        }
        $now = $now
            ? \DateTimeImmutable::createFromInterface($now)->setTimezone($tz)
            : new \DateTimeImmutable('now', $tz);
        $slot = ((int) $now->format('Y') * 8784) + ((int) $now->format('z') * 24) + (int) $now->format('G');
        $row = $duas[$slot % count($duas)];

        return [
            'arabic' => $row['ar'],
            'hindi' => $row['tr']['hi'] ?? $row['tr']['en'],
            'surah' => $row['surah'],
            'ayah' => $row['ayah'],
        ];
    }

    /**
     * @return list<array{ar:string,surah:string,ayah:string,tr:array<string,string>}>
     */
    public static function all(): array
    {
        return [
            [
                'ar' => 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
                'surah' => 'Surah al-Baqarah',
                'ayah' => '2:201',
                'tr' => [
                    'en' => 'Our Lord, give us good in this world and good in the Hereafter, and protect us from the punishment of the Fire.',
                    'hi' => 'ऐ हमारे रब, हमें दुनिया में भलाई दे, आख़िरत में भलाई दे, और आग के अज़ाब से बचा।',
                    'ur' => 'اے ہمارے رب، ہمیں دنیا میں بھلائی دے، آخرت میں بھلائی دے، اور آگ کے عذاب سے بچا۔',
                    'ar' => 'ربنا آتنا في الدنيا حسنة وفي الآخرة حسنة وقنا عذاب النار.',
                ],
            ],
            [
                'ar' => 'رَّبِّ زِدْنِي عِلْمًا',
                'surah' => 'Surah Taha',
                'ayah' => '20:114',
                'tr' => [
                    'en' => 'My Lord, increase me in knowledge.',
                    'hi' => 'ऐ मेरे रब, मेरे इल्म में इज़ाफ़ा फ़रमा।',
                    'ur' => 'اے میرے رب، میرے علم میں اضافہ فرما۔',
                    'ar' => 'ربي زدني علماً.',
                ],
            ],
            [
                'ar' => 'رَبَّنَا لَا تُزِغْ قُلُوبَنَا بَعْدَ إِذْ هَدَيْتَنَا وَهَبْ لَنَا مِن لَّدُنكَ رَحْمَةً ۚ إِنَّكَ أَنتَ الْوَهَّابُ',
                'surah' => 'Surah Aal Imran',
                'ayah' => '3:8',
                'tr' => [
                    'en' => 'Our Lord, do not let our hearts swerve after You have guided us, and grant us mercy from Yourself. You are the Giver.',
                    'hi' => 'ऐ हमारे रब, हिदायत के बाद हमारे दिल न बहकने देना, और अपनी तरफ़ से रहमत अता फ़रमा। तू ही बहुत देने वाला है।',
                    'ur' => 'اے ہمارے رب، ہدایت کے بعد ہمارے دل نہ بہکنے دینا، اور اپنی طرف سے رحمت عطا فرما۔ تو ہی بہت دینے والا ہے۔',
                    'ar' => 'ربنا لا تزغ قلوبنا بعد إذ هديتنا، وهب لنا من لدنك رحمة، إنك أنت الوهاب.',
                ],
            ],
            [
                'ar' => 'لَا إِلَٰهَ إِلَّا أَنتَ سُبْحَانَكَ إِنِّي كُنتُ مِنَ الظَّالِمِينَ',
                'surah' => 'Surah al-Anbiya',
                'ayah' => '21:87',
                'tr' => [
                    'en' => 'There is no god but You. Glory be to You. I have been among the wrongdoers.',
                    'hi' => 'तेरे सिवा कोई माबूद नहीं। तू पाक है। मैं ज़ालिमों में से हो गया।',
                    'ur' => 'تیرے سوا کوئی معبود نہیں۔ تو پاک ہے۔ میں ظالموں میں سے ہو گیا۔',
                    'ar' => 'لا إله إلا أنت سبحانك، إني كنت من الظالمين.',
                ],
            ],
            [
                'ar' => 'حَسْبُنَا اللَّهُ وَنِعْمَ الْوَكِيلُ',
                'surah' => 'Surah Aal Imran',
                'ayah' => '3:173',
                'tr' => [
                    'en' => 'Allah is sufficient for us, and He is the best Disposer of affairs.',
                    'hi' => 'हमारे लिए अल्लाह काफी है, और वही सबसे अच्छा कारساز है।',
                    'ur' => 'ہمارے لیے اللہ کافی ہے، اور وہی بہترین کارساز ہے۔',
                    'ar' => 'حسبنا الله ونعم الوكيل.',
                ],
            ],
            [
                'ar' => 'رَبَّنَا ظَلَمْنَا أَنفُسَنَا وَإِن لَّمْ تَغْفِرْ لَنَا وَتَرْحَمْنَا لَنَكُونَنَّ مِنَ الْخَاسِرِينَ',
                'surah' => 'Surah al-Araf',
                'ayah' => '7:23',
                'tr' => [
                    'en' => 'Our Lord, we have wronged ourselves. If You do not forgive us and have mercy on us, we will surely be among the losers.',
                    'hi' => 'ऐ हमारे रब, हमने अपने ऊपर ज़ुल्म किया। अगर तू माफ़ न करे और रहम न करे तो हम घाटे में रहेंगे।',
                    'ur' => 'اے ہمارے رب، ہم نے اپنے اوپر ظلم کیا۔ اگر تو معاف نہ کرے اور رحم نہ فرمائے تو ہم نقصان میں رہیں گے۔',
                    'ar' => 'ربنا ظلمنا أنفسنا، وإن لم تغفر لنا وترحمنا لنكونن من الخاسرين.',
                ],
            ],
            [
                'ar' => 'رَبِّ اجْعَلْنِي مُقِيمَ الصَّلَاةِ وَمِن ذُرِّيَّتِي ۚ رَبَّنَا وَتَقَبَّلْ دُعَاءِ',
                'surah' => 'Surah Ibrahim',
                'ayah' => '14:40',
                'tr' => [
                    'en' => 'My Lord, make me steadfast in prayer, and from my descendants as well. Our Lord, accept my supplication.',
                    'hi' => 'ऐ मेरे रब, मुझे नमाज़ का पाबंद बना, और मेरी औलाद को भी। ऐ हमारे रब, मेरी दुआ क़ुबूल फ़रमा।',
                    'ur' => 'اے میرے رب، مجھے نماز کا پابند بنا، اور میری اولاد کو بھی۔ اے ہمارے رب، میری دعا قبول فرما۔',
                    'ar' => 'رب اجعلني مقيم الصلاة ومن ذريتي، ربنا وتقبل دعاء.',
                ],
            ],
            [
                'ar' => 'رَبَّنَا آتِنَا مِن لَّدُنكَ رَحْمَةً وَهَيِّئْ لَنَا مِنْ أَمْرِنَا رَشَدًا',
                'surah' => 'Surah al-Kahf',
                'ayah' => '18:10',
                'tr' => [
                    'en' => 'Our Lord, grant us mercy from Yourself, and make our affair right for us.',
                    'hi' => 'ऐ हमारे रब, अपनी तरफ़ से रहमत अता फ़रमा, और हमारे काम में सीधी राह बना दे।',
                    'ur' => 'اے ہمارے رب، اپنی طرف سے رحمت عطا فرما، اور ہمارے کام میں سیدھی راہ بنا دے۔',
                    'ar' => 'ربنا آتنا من لدنك رحمة وهيئ لنا من أمرنا رشداً.',
                ],
            ],
            [
                'ar' => 'رَبَّنَا هَبْ لَنَا مِنْ أَزْوَاجِنَا وَذُرِّيَّاتِنَا قُرَّةَ أَعْيُنٍ وَاجْعَلْنَا لِلْمُتَّقِينَ إِمَامًا',
                'surah' => 'Surah al-Furqan',
                'ayah' => '25:74',
                'tr' => [
                    'en' => 'Our Lord, grant us from our spouses and children comfort of the eyes, and make us leaders for those who are mindful of You.',
                    'hi' => 'ऐ हमारे रब, हमारी बीवियों और औलाद को आँखों की ठंडक बना, और हमें परहेज़गारों का इमाम बना।',
                    'ur' => 'اے ہمارے رب، ہماری بیویوں اور اولاد کو آنکھوں کی ٹھنڈک بنا، اور ہمیں پرہیزگاروں کا امام بنا۔',
                    'ar' => 'ربنا هب لنا من أزواجنا وذرياتنا قرة أعين، واجعلنا للمتقين إماماً.',
                ],
            ],
            [
                'ar' => 'رَبَّنَا لَا تُؤَاخِذْنَا إِن نَّسِينَا أَوْ أَخْطَأْنَا',
                'surah' => 'Surah al-Baqarah',
                'ayah' => '2:286',
                'tr' => [
                    'en' => 'Our Lord, do not take us to task if we forget or make a mistake.',
                    'hi' => 'ऐ हमारे रब, अगर हम भूल जाएँ या चूक जाएँ तो हमें पकड़ में न ले।',
                    'ur' => 'اے ہمارے رب، اگر ہم بھول جائیں یا چوک جائیں تو ہمیں پکڑ میں نہ لے۔',
                    'ar' => 'ربنا لا تؤاخذنا إن نسينا أو أخطأنا.',
                ],
            ],
            [
                'ar' => 'رَّبِّ أَدْخِلْنِي مُدْخَلَ صِدْقٍ وَأَخْرِجْنِي مُخْرَجَ صِدْقٍ وَاجْعَل لِّي مِن لَّدُنكَ سُلْطَانًا نَّصِيرًا',
                'surah' => 'Surah al-Isra',
                'ayah' => '17:80',
                'tr' => [
                    'en' => 'My Lord, cause me to enter a true entrance and to leave a true exit, and grant me from Yourself supporting authority.',
                    'hi' => 'ऐ मेरे रब, मुझे सच्चाई के साथ दाख़िल कर, सच्चाई के साथ निकाल, और अपनी तरफ़ से मददगार इख़्तियार दे।',
                    'ur' => 'اے میرے رب، مجھے سچائی کے ساتھ داخل کر، سچائی کے ساتھ نکال، اور اپنی طرف سے مددگار اختیار دے۔',
                    'ar' => 'رب أدخلني مدخل صدق وأخرجني مخرج صدق، واجعل لي من لدنك سلطاناً نصيراً.',
                ],
            ],
            [
                'ar' => 'رَبِّ أَوْزِعْنِي أَنْ أَشْكُرَ نِعْمَتَكَ الَّتِي أَنْعَمْتَ عَلَيَّ وَعَلَىٰ وَالِدَيَّ وَأَنْ أَعْمَلَ صَالِحًا تَرْضَاهُ',
                'surah' => 'Surah al-Ahqaf',
                'ayah' => '46:15',
                'tr' => [
                    'en' => 'My Lord, enable me to be grateful for Your favour on me and on my parents, and to do righteous work that pleases You.',
                    'hi' => 'ऐ मेरे रब, मुझे तौफ़ीक़ दे कि तेरी नेमत का शुक्र करूँ — जो तूने मुझ पर और मेरे माँ-बाप पर की — और ऐसा नेक अमल करूँ जिससे तू राज़ी हो।',
                    'ur' => 'اے میرے رب، مجھے توفیق دے کہ تیری نعمت کا شکر ادا کروں — جو تو نے مجھ پر اور میرے ماں باپ پر کی — اور ایسا نیک عمل کروں جس سے تو راضی ہو۔',
                    'ar' => 'رب أوزعني أن أشكر نعمتك التي أنعمت علي وعلى والدي، وأن أعمل صالحاً ترضاه.',
                ],
            ],
        ];
    }
}

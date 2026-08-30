<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

/**
 * Default duas, hadith, janazah, and Hajj copy. Admin overlays live in settings.
 */
final class FaithContentService
{
    /**
     * @return array<string, mixed>
     */
    public function bundle(): array
    {
        $saved = json_setting('faith_content');
        return [
            'duas' => $this->mergeList($this->defaultDuas(), is_array($saved['duas'] ?? null) ? $saved['duas'] : []),
            'hadith' => $this->mergeList($this->defaultHadith(), is_array($saved['hadith'] ?? null) ? $saved['hadith'] : []),
            'janazah' => $this->mergeList($this->defaultJanazah(), is_array($saved['janazah'] ?? null) ? $saved['janazah'] : []),
            'hajj' => $this->mergeHajj(is_array($saved['hajj'] ?? null) ? $saved['hajj'] : []),
            'names' => $this->names(),
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     */
    public function save(string $key, array $incoming): void
    {
        $all = json_setting('faith_content');
        if ($key === 'duas' || $key === 'hadith' || $key === 'janazah') {
            $all[$key] = array_values($incoming);
        } elseif ($key === 'hajj') {
            $all['hajj'] = $incoming;
        }
        Setting::put('faith_content', json_encode($all, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function names(): array
    {
        $file = PUBLIC_PATH . '/assets/data/asma-ul-husna.json';
        if (!is_file($file)) {
            return [];
        }
        $raw = json_decode((string) file_get_contents($file), true);
        return is_array($raw) ? $raw : [];
    }

    /**
     * @param list<array<string, mixed>> $base
     * @param list<mixed> $overlay
     * @return list<array<string, mixed>>
     */
    private function mergeList(array $base, array $overlay): array
    {
        if ($overlay === []) {
            return $base;
        }
        $out = [];
        foreach ($overlay as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $src = is_array($base[$i] ?? null) ? $base[$i] : [];
            $merged = array_merge($src, $row);
            foreach (['ayah', 'hisn', 'hi', 'ur', 'text_hi', 'lead_hi'] as $keep) {
                if (trim((string) ($merged[$keep] ?? '')) === '' && trim((string) ($src[$keep] ?? '')) !== '') {
                    $merged[$keep] = $src[$keep];
                }
            }
            if (isset($src['items']) && is_array($src['items']) && isset($row['items']) && is_array($row['items'])) {
                $merged['items'] = $this->mergeList($src['items'], $row['items']);
            }
            if (isset($src['dua']) && is_array($src['dua'])) {
                $merged['dua'] = array_merge($src['dua'], is_array($row['dua'] ?? null) ? $row['dua'] : []);
                if (trim((string) ($merged['dua']['ayah'] ?? '')) === '' && trim((string) ($src['dua']['ayah'] ?? '')) !== '') {
                    $merged['dua']['ayah'] = $src['dua']['ayah'];
                }
                if (trim((string) ($merged['dua']['hisn'] ?? '')) === '' && trim((string) ($src['dua']['hisn'] ?? '')) !== '') {
                    $merged['dua']['hisn'] = $src['dua']['hisn'];
                }
            }
            $out[] = $merged;
        }
        return $out !== [] ? $out : $base;
    }

    /**
     * @param array<string, mixed> $overlay
     * @return array<string, mixed>
     */
    private function mergeHajj(array $overlay): array
    {
        $base = $this->defaultHajj();
        if ($overlay === []) {
            return $base;
        }
        foreach (['umrah', 'hajj'] as $key) {
            if (isset($overlay[$key]) && is_array($overlay[$key])) {
                $base[$key] = $this->mergeList($base[$key], $overlay[$key]);
            }
        }
        if (isset($overlay['duas']) && is_array($overlay['duas'])) {
            $base['duas'] = $this->mergeList($base['duas'], $overlay['duas']);
        }
        return $base;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultDuas(): array
    {
        return [
            [
                'id' => 'morning',
                'title' => 'Morning',
                'items' => [
                    $this->dua('Morning remembrance', 'أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ', 'Asbahna wa asbahal-mulku lillah, walhamdu lillah.', 'We have entered the morning and the dominion belongs to Allah, and all praise is for Allah.', 'हमने सुबह की और सारी बादशाहत अल्लाह की है, और सारी हम्द अल्लाह के लिए है।', 'ہم نے صبح کی اور ساری بادشاہت اللہ کی ہے، اور ساری تعریف اللہ کے لیے ہے۔', '', '77'),
                    $this->dua('Ayat al-Kursi', 'اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ', 'Allahu la ilaha illa huwal-hayyul-qayyum. La ta’khudhuhu sinatun wa la nawm.', 'Allah — there is no god but He, the Ever-Living, the Self-Subsisting. Neither slumber nor sleep overtakes Him. (2:255 — read the full ayah.)', 'अल्लाह — उसके सिवा कोई माबूद नहीं, वह हमेशा ज़िंदा और सबका सहारा है। उसे न ऊँघ आती है न नींद। (२:२५५ — पूरी आयत पढ़ें।)', 'اللہ — اس کے سوا کوئی معبود نہیں، وہ ہمیشہ زندہ اور سب کا سہارا ہے۔ اسے نہ اونگھ آتی ہے نہ نیند۔ (۲:۲۵۵ — پوری آیت پڑھیں۔)', '2:255'),
                    $this->dua('Protection of the morning', 'بِسْمِ اللَّهِ الَّذِي لَا يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الْأَرْضِ وَلَا فِي السَّمَاءِ وَهُوَ السَّمِيعُ الْعَلِيمُ', 'Bismillahil-ladhi la yadurru ma’asmihi shay’un fil-ardi wa la fis-sama’i wa huwas-sami’ul-‘alim.', 'In the name of Allah, with whose name nothing on earth or in heaven can harm. He is the All-Hearing, the All-Knowing. (Three times.)', 'अल्लाह के नाम से जिसके नाम के साथ ज़मीन और आसमान की कोई चीज़ नुकसान नहीं पहुँचा सकती। वह सब सुनने और जानने वाला है। (तीन बार।)', 'اللہ کے نام سے جس کے نام کے ساتھ زمین اور آسمان کی کوئی چیز نقصان نہیں پہنچا سکتی۔ وہ سب سننے اور جاننے والا ہے۔ (تین بار۔)', '', '86'),
                ],
            ],
            [
                'id' => 'evening',
                'title' => 'Evening',
                'items' => [
                    $this->dua('Evening remembrance', 'اللَّهُمَّ بِكَ أَمْسَيْنَا، وَبِكَ أَصْبَحْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ وَإِلَيْكَ الْمَصِيرُ', 'Allahumma bika amsayna, wa bika asbahna, wa bika nahya, wa bika namutu wa ilaykal-masir.', 'O Allah, by Your leave we have reached the evening and by Your leave the morning; by Your leave we live and die, and to You is our return.', 'ऐ अल्लाह, तेरी इजाज़त से हमने शाम की और तेरी इजाज़त से सुबह की; तेरी इजाज़त से जीते और मरते हैं, और लौटना तेरी ही तरफ़ है।', 'اے اللہ، تیری اجازت سے ہم نے شام کی اور تیری اجازت سے صبح کی؛ تیری اجازت سے جیتے اور مرتے ہیں، اور لوٹنا تیری ہی طرف ہے۔', '', '78'),
                    $this->dua('Before sleep', 'بِاسْمِكَ اللَّهُمَّ أَمُوتُ وَأَحْيَا', 'Bismika Allahumma amutu wa ahya.', 'In Your name, O Allah, I die and I live.', 'ऐ अल्लाह, तेरे नाम से मैं मरता हूँ और जीता हूँ।', 'اے اللہ، تیرے نام سے میں مرتا ہوں اور جیتا ہوں۔', '', '105'),
                    $this->dua('Last words of the night', 'اللَّهُمَّ بِاسْمِكَ أَمُوتُ وَأَحْيَا', 'Allahumma bismika amutu wa ahya.', 'O Allah, in Your name I die and I live. Recite the three Quls and Ayat al-Kursi as you are able.', 'ऐ अल्लाह, तेरे नाम से मैं मरता और जीता हूँ। तीन क़ुल और आयतुल कुर्सी भी पढ़ें जितना हो सके।', 'اے اللہ، تیرے نام سے میں مرتا اور جیتا ہوں۔ تین قل اور آیت الکرسی بھی پڑھیں جتنا ہو سکے۔', '', '105'),
                ],
            ],
            [
                'id' => 'food',
                'title' => 'Food',
                'items' => [
                    $this->dua('Before eating', 'بِسْمِ اللَّهِ', 'Bismillah.', 'In the name of Allah. If you forget at the start: Bismillahi awwalahu wa akhirahu.', 'अल्लाह के नाम से। अगर शुरू में भूल जाएँ तो कहें: बिस्मिल्लाहि अव्वलहू व आख़िरहू।', 'اللہ کے نام سے۔ اگر شروع میں بھول جائیں تو کہیں: بسم اللہ اولہ و آخرہ۔', '', '178'),
                    $this->dua('After eating', 'الْحَمْدُ لِلَّهِ الَّذِي أَطْعَمَنِي هَٰذَا وَرَزَقَنِيهِ مِنْ غَيْرِ حَوْلٍ مِنِّي وَلَا قُوَّةٍ', 'Alhamdu lillahil-ladhi at’amani hadha wa razaqanihi min ghayri hawlin minni wa la quwwah.', 'Praise be to Allah who fed me this and provided it for me with no power or strength from myself.', 'अल्लाह की हम्द जिसने मुझे यह खिलाया और मेरी ताक़त के बिना रिज़्क़ दिया।', 'اللہ کی حمد جس نے مجھے یہ کھلایا اور میری طاقت کے بغیر رزق دیا۔', '', '180'),
                    $this->dua('Drinking water', 'الْحَمْدُ لِلَّهِ', 'Alhamdulillah.', 'Praise Allah when you finish. Drink sitting, in sips, as the Sunnah teaches.', 'खत्म पर अल्लाह की हम्د कहें। सुन्नत के मुताबिक़ बैठकर, घूँट-घूँट पिएँ।', 'ختم پر اللہ کی حمد کہیں۔ سنت کے مطابق بیٹھ کر، گھونٹ گھونٹ پئیں۔'),
                ],
            ],
            [
                'id' => 'travel',
                'title' => 'Travel',
                'items' => [
                    $this->dua('Leaving the house', 'بِسْمِ اللَّهِ، تَوَكَّلْتُ عَلَى اللَّهِ، وَلَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللَّهِ', 'Bismillah, tawakkaltu ‘alallah, wa la hawla wa la quwwata illa billah.', 'In the name of Allah, I place my trust in Allah; there is no power except with Allah.', 'अल्लाह के नाम से, मैं अल्लाह पर भरोसा करता हूँ; अल्लाह के सिवा कोई ताक़त नहीं।', 'اللہ کے نام سے، میں اللہ پر بھروسہ کرتا ہوں؛ اللہ کے سوا کوئی طاقت نہیں۔', '', '16'),
                    $this->dua('Boarding a vehicle', 'سُبْحَانَ الَّذِي سَخَّرَ لَنَا هَٰذَا وَمَا كُنَّا لَهُ مُقْرِنِينَ وَإِنَّا إِلَىٰ رَبِّنَا لَمُنقَلِبُونَ', 'Subhanalladhi sakhkhara lana hadha wa ma kunna lahu muqrinin, wa inna ila rabbina lamunqalibun.', 'Glory be to the One who has subjected this to us, and we could not have done it by ourselves. To our Lord we will return. (43:13–14)', 'पाक है वह जिसने यह हमारे काम का बनाया, हम ख़ुद ऐसा न कर सकते। हम अपने रब की तरफ़ लौटने वाले हैं। (४३:१३–۱۴)', 'پاک ہے وہ جس نے یہ ہمارے کام کا بنایا، ہم خود ایسا نہ کر سکتے۔ ہم اپنے رب کی طرف لوٹنے والے ہیں۔ (۴۳:۱۳–۱۴)', '43:13-43:14'),
                    $this->dua('Returning home', 'آيِبُونَ تَائِبُونَ عَابِدُونَ لِرَبِّنَا حَامِدُونَ', 'Ayibuna ta’ibuna ‘abiduna li-rabbina hamidun.', 'We return, we repent, we worship, and we praise our Lord.', 'हम लौटे, तौबा करते हुए, इबादत करते हुए, अपने रब की हम्द करते हुए।', 'ہم لوٹے، توبہ کرتے ہوئے، عبادت کرتے ہوئے، اپنے رب کی حمد کرتے ہوئے۔', '', '217'),
                ],
            ],
            [
                'id' => 'home',
                'title' => 'Home',
                'items' => [
                    $this->dua('Entering the home', 'بِسْمِ اللَّهِ وَلَجْنَا، وَبِسْمِ اللَّهِ خَرَجْنَا، وَعَلَى رَبِّنَا تَوَكَّلْنَا', 'Bismillahi walajna, wa bismillahi kharajna, wa ‘ala rabbina tawakkalna.', 'In the name of Allah we enter, in the name of Allah we leave, and upon our Lord we rely.', 'अल्लाह के नाम से हम दाख़िल होते हैं, अल्लाह के नाम से निकलते हैं, और अपने रब पर भरोसा रखते हैं।', 'اللہ کے نام سے ہم داخل ہوتے ہیں، اللہ کے نام سے نکلتے ہیں، اور اپنے رب پر بھروسہ رکھتے ہیں۔', '', '18'),
                    $this->dua('For the family', 'رَبَّنَا هَبْ لَنَا مِنْ أَزْوَاجِنَا وَذُرِّيَّاتِنَا قُرَّةَ أَعْيُنٍ وَاجْعَلْنَا لِلْمُتَّقِينَ إِمَامًا', 'Rabbana hab lana min azwajina wa dhurriyyatina qurrata a‘yunin waj‘alna lil-muttaqina imama.', 'Our Lord, grant us comfort of the eyes from our spouses and children, and make us leaders for those who are mindful of You. (25:74)', 'ऐ हमारे रब, हमें बीवी/शौहर और औलाद से आँखों की ठंडक दे, और हमें परहेज़गारों का इमाम बना। (२५:७४)', 'اے ہمارے رب، ہمیں بیوی/شوہر اور اولاد سے آنکھوں کی ٹھنڈک دے، اور ہمیں پرہیزگاروں کا امام بنا۔ (۲۵:۷۴)', '25:74'),
                    $this->dua('When angry', 'أَعُوذُ بِاللَّهِ مِنَ الشَّيْطَانِ الرَّجِيمِ', 'A‘udhu billahi minash-shaytanir-rajim.', 'I seek refuge in Allah from the accursed Shaytan.', 'मैं लानती शैतान से अल्लाह की पनाह माँगता हूँ।', 'میں لعنتی شیطان سے اللہ کی پناہ مانگتا ہوں۔', '', '193'),
                ],
            ],
            [
                'id' => 'illness',
                'title' => 'Illness',
                'items' => [
                    $this->dua('When in pain', 'أَعُوذُ بِاللَّهِ وَقُدْرَتِهِ مِنْ شَرِّ مَا أَجِدُ وَأُحَاذِرُ', 'A‘udhu billahi wa qudratihi min sharri ma ajidu wa uhadhir.', 'Place your hand on the pain and say Bismillah three times, then this seven times: I seek refuge in Allah and His power from the evil I feel and fear.', 'दर्द वाली जगह पर हाथ रखें, तीन बार बिस्मिल्लाह, फिर सात बार: मैं अल्लाह और उसकी क़ुदरत की पनाह माँगता हूँ उस बुराई से जो मुझे महसूस होती है और जिससे मैं डरता हूँ।', 'درد والی جگہ پر ہاتھ رکھیں، تین بار بسم اللہ، پھر سات بار: میں اللہ اور اس کی قدرت کی پناہ مانگتا ہوں اس برائی سے جو مجھے محسوس ہوتی ہے اور جس سے میں ڈرتا ہوں۔', '', '243'),
                    $this->dua('Visiting the sick', 'لَا بَأْسَ، طَهُورٌ إِنْ شَاءَ اللَّهُ', 'La ba’sa, tahurun in sha Allah.', 'No harm — it is a purification, if Allah wills.', 'कोई हर्ज नहीं — इंशाअल्लाह यह पाकी/कफ़्फ़ारा है।', 'کوئی حرج نہیں — ان شاء اللہ یہ پاکی/کفارہ ہے۔', '', '147'),
                    $this->dua('For anxiety', 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْهَمِّ وَالْحَزَنِ', 'Allahumma inni a‘udhu bika minal-hammi wal-hazan.', 'O Allah, I seek refuge in You from worry and grief.', 'ऐ अल्लाह, मैं फ़िक्र और ग़म से तेरी पनाह माँगता हूँ।', 'اے اللہ، میں فکر اور غم سے تیری پناہ مانگتا ہوں۔', '', '121'),
                ],
            ],
            [
                'id' => 'janazah',
                'title' => 'Janazah',
                'items' => [
                    $this->dua('When news of death comes', 'إِنَّا لِلَّهِ وَإِنَّا إِلَيْهِ رَاجِعُونَ', 'Inna lillahi wa inna ilayhi raji‘un.', 'We belong to Allah, and to Him we return.', 'हम अल्लाह के हैं और उसी की तरफ़ लौटने वाले हैं।', 'ہم اللہ کے ہیں اور اسی کی طرف لوٹنے والے ہیں۔', '', '154'),
                    $this->dua('For the deceased', 'اللَّهُمَّ اغْفِرْ لَهُ وَارْحَمْهُ وَعَافِهِ وَاعْفُ عَنْهُ', 'Allahummaghfir lahu warhamhu wa ‘afihi wa‘fu ‘anhu.', 'O Allah, forgive him, have mercy on him, grant him well-being, and pardon him. (Use feminine forms for a woman.)', 'ऐ अल्लाह, उसे बख़्श, उस पर रहम कर, आफ़ियत दे और माफ़ कर। (औरत के लिए मुअन्नस सीग़ा।)', 'اے اللہ، اسے بخش، اس پر رحم کر، عافیت دے اور معاف کر۔ (عورت کے لیے مؤنث صیغہ۔)', '', '156'),
                    $this->dua('At the grave', 'اللَّهُمَّ اغْفِرْ لَهُ، اللَّهُمَّ ثَبِّتْهُ', 'Allahummaghfir lahu, Allahumma thabbit-hu.', 'O Allah, forgive him. O Allah, make him firm.', 'ऐ अल्लाह, उसे बख़्श। ऐ अल्लाह, उसे साबित क़दम रख।', 'اے اللہ، اسے بخش۔ اے اللہ، اسے ثابت قدم رکھ۔', '', '164'),
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public function defaultHadith(): array
    {
        return [
            ['ar' => 'إِنَّمَا الْأَعْمَالُ بِالنِّيَّاتِ', 'en' => 'Actions are only by intentions.', 'src' => 'Bukhari, Muslim'],
            ['ar' => 'مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الْآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ', 'en' => 'Whoever believes in Allah and the Last Day should speak good or remain silent.', 'src' => 'Bukhari, Muslim'],
            ['ar' => 'لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ', 'en' => 'None of you truly believes until he loves for his brother what he loves for himself.', 'src' => 'Bukhari, Muslim'],
            ['ar' => 'الدِّينُ النَّصِيحَةُ', 'en' => 'The religion is sincere counsel.', 'src' => 'Muslim'],
            ['ar' => 'الطُّهُورُ شَطْرُ الْإِيمَانِ', 'en' => 'Purity is half of faith.', 'src' => 'Muslim'],
            ['ar' => 'مَنْ لَا يَرْحَمِ النَّاسَ لَا يَرْحَمْهُ اللَّهُ', 'en' => 'Whoever does not show mercy to people, Allah will not show mercy to him.', 'src' => 'Bukhari, Muslim'],
            ['ar' => 'خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ', 'en' => 'The best of you are those who learn the Qur’an and teach it.', 'src' => 'Bukhari'],
            ['ar' => 'الْمُسْلِمُ مَنْ سَلِمَ الْمُسْلِمُونَ مِنْ لِسَانِهِ وَيَدِهِ', 'en' => 'The Muslim is the one from whose tongue and hand other Muslims are safe.', 'src' => 'Bukhari, Muslim'],
            ['ar' => 'اتَّقِ اللَّهَ حَيْثُمَا كُنْتَ', 'en' => 'Fear Allah wherever you are.', 'src' => 'Tirmidhi'],
            ['ar' => 'لَا ضَرَرَ وَلَا ضِرَارَ', 'en' => 'There is no harming and no reciprocating harm.', 'src' => 'Ibn Majah'],
            ['ar' => 'مَنْ حُسْنِ إِسْلَامِ الْمَرْءِ تَرْكُهُ مَا لَا يَعْنِيهِ', 'en' => 'Part of the excellence of a person’s Islam is leaving what does not concern him.', 'src' => 'Tirmidhi'],
            ['ar' => 'أَحَبُّ النَّاسِ إِلَى اللَّهِ أَنْفَعُهُمْ لِلنَّاسِ', 'en' => 'The most beloved people to Allah are those most beneficial to people.', 'src' => 'Tabarani'],
            ['ar' => 'يَسِّرُوا وَلَا تُعَسِّرُوا، وَبَشِّرُوا وَلَا تُنَفِّرُوا', 'en' => 'Make things easy and do not make them difficult; give good news and do not drive people away.', 'src' => 'Bukhari, Muslim'],
            ['ar' => 'الصَّدَقَةُ تُطْفِئُ الْخَطِيئَةَ كَمَا يُطْفِئُ الْمَاءُ النَّارَ', 'en' => 'Charity extinguishes sin as water extinguishes fire.', 'src' => 'Tirmidhi'],
            ['ar' => 'أَكْمَلُ الْمُؤْمِنِينَ إِيمَانًا أَحْسَنُهُمْ خُلُقًا', 'en' => 'The most complete of the believers in faith are those with the best character.', 'src' => 'Tirmidhi'],
            ['ar' => 'مَنْ سَلَكَ طَرِيقًا يَلْتَمِسُ فِيهِ عِلْمًا سَهَّلَ اللَّهُ لَهُ بِهِ طَرِيقًا إِلَى الْجَنَّةِ', 'en' => 'Whoever travels a path seeking knowledge, Allah makes easy for him a path to Paradise.', 'src' => 'Muslim'],
            ['ar' => 'الدُّعَاءُ هُوَ الْعِبَادَةُ', 'en' => 'Du‘a is worship.', 'src' => 'Tirmidhi'],
            ['ar' => 'نَظِّفُوا أَفْنِيَتَكُمْ', 'en' => 'Keep your courtyards clean. (A reminder of cleanliness in the Sunnah.)', 'src' => 'Tirmidhi'],
            ['ar' => 'الْبَيِّعَانِ بِالْخِيَارِ مَا لَمْ يَتَفَرَّقَا', 'en' => 'The two parties to a sale have the option so long as they have not separated.', 'src' => 'Bukhari, Muslim'],
            ['ar' => 'مَا نَقَصَتْ صَدَقَةٌ مِنْ مَالٍ', 'en' => 'Charity does not decrease wealth.', 'src' => 'Muslim'],
            ['ar' => 'ارْحَمُوا مَنْ فِي الْأَرْضِ يَرْحَمْكُمْ مَنْ فِي السَّمَاءِ', 'en' => 'Show mercy to those on earth, and the One in the heaven will show mercy to you.', 'src' => 'Tirmidhi'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultJanazah(): array
    {
        return [
            [
                'title' => '1. Ghusl',
                'lead' => 'Wash the body with dignity, behind a screen. Those of the same gender wash, except a spouse. Begin with wudu parts, then the whole body, odd number of washes, last with camphor if you have it. Do not cut hair or nails.',
                'lead_hi' => 'पर्दे के पीछे इज़्ज़त के साथ शरीर धोएँ। एक ही लिंग वाले नहलाएँ, पति/पत्नी को छोड़कर। पहले वुज़ू के अंग, फिर पूरा बदन; ताक़ बार धोएँ; आख़िरी बार कपूर हो तो उसमें। बाल या नाख़ून न काटें।',
                'dua' => $this->dua('Intention', 'نَوَيْتُ الْغُسْلَ لِهَٰذَا الْمَيِّتِ لِلَّهِ تَعَالَى', 'Nawaytul-ghusla li-hadhal-mayyiti lillahi ta‘ala.', 'I intend the funeral bath for this deceased for the sake of Allah.', 'मैं इस मय्यित का ग़ुस्ल अल्लाह के लिए नीयत करता/करती हूँ।', 'میں اس میت کا غسل اللہ کے لیے نیت کرتا/کرتی ہوں۔'),
            ],
            [
                'title' => '2. Kafan',
                'lead' => 'Shroud simply. A man: three white cloths. A woman: five (including a scarf and a chest wrap). Perfume the shroud. Tie gently. Place the body facing Qibla if you can while preparing.',
                'lead_hi' => 'सादा कफ़न। मर्द: तीन सफ़ेद कपड़े। औरत: पाँच (दुपट्टा और सीने की पट्टी समेत)। कफ़न पर ख़ुशबू। नरमी से बाँधें। तैयारी में मुमकिन हो तो क़िबला रुख़ रखें।',
                'dua' => $this->dua('For a good shroud', 'اللَّهُمَّ اغْفِرْ لَهُ وَأَكْرِمْ نُزُلَهُ', 'Allahummaghfir lahu wa akrim nuzulahu.', 'O Allah, forgive him and honour his place of arrival.', 'ऐ अल्लाह, उसे बख़्श और उसकी मेहमानी इज़्ज़त वाली बना।', 'اے اللہ، اسے بخش اور اس کی مہمانی عزت والی بنا۔'),
            ],
            [
                'title' => '3. Janazah salah',
                'lead' => 'Stand, face Qibla. Four takbirs, standing throughout — no ruku or sujud. After the first: Thana / Fatiha as your imam teaches. After the second: salawat on the Prophet ﷺ. After the third: du‘a for the deceased. After the fourth: a short pause, then salam to the right.',
                'lead_hi' => 'क़िबला रुख़ खड़े रहें। चार तकबीरें, सब खड़े-खड़े — रुकू/सजदा नहीं। पहली के बाद थना/फ़ातिहा जैसा इमाम सिखाए। दूसरी के बाद दरूद। तीसरी के बाद मय्यित की दुआ। चौथी के बाद ज़रा रुकें, फिर दाएँ सलाम।',
                'dua' => $this->dua('Du‘a in the third takbir', 'اللَّهُمَّ اغْفِرْ لِحَيِّنَا وَمَيِّتِنَا وَشَاهِدِنَا وَغَائِبِنَا وَصَغِيرِنَا وَكَبِيرِنَا وَذَكَرِنَا وَأُنْثَانَا', 'Allahummaghfir li-hayyina wa mayyitina wa shahidina wa gha’ibina wa saghirina wa kabirina wa dhakarina wa unthana.', 'O Allah, forgive our living and our dead, those present and those absent, our young and our old, our men and our women.', 'ऐ अल्लाह, हमारे ज़िंदा और मुर्दा, हाज़िर और ग़ायब, छोटे और बड़े, मर्द और औरतों को बख़्श दे।', 'اے اللہ، ہمارے زندہ اور مردہ، حاضر اور غائب، چھوٹے اور بڑے، مرد اور عورتوں کو بخش دے۔'),
            ],
            [
                'title' => '4. Burial (dafn)',
                'lead' => 'Carry the bier calmly. At the grave, lower the body on its right side, face to Qibla. Untie the shroud knots. Place unbaked bricks or wood, then earth. Stay a while and make du‘a for firmness. Do not sit on graves or plaster them for show.',
                'lead_hi' => 'जनाज़ा आहिस्ता ले जाएँ। क़ब्र में दाएँ पहलू पर उतारें, चेहरा क़िबला। कफ़न की गाँठें खोलें। कच्ची ईंट या लकड़ी, फिर मिट्टी। थोड़ी देर ठहरकर साबित क़दमी की दुआ करें। क़ब्रों पर न बैठें, न दिखावे के लिए पक्का करें।',
                'dua' => $this->dua('When placing in the grave', 'بِسْمِ اللَّهِ وَعَلَىٰ سُنَّةِ رَسُولِ اللَّهِ', 'Bismillahi wa ‘ala sunnati rasulillah.', 'In the name of Allah, and upon the way of the Messenger of Allah ﷺ.', 'अल्लाह के नाम से, और रसूल अल्लाह ﷺ की सुन्नत पर।', 'اللہ کے نام سے، اور رسول اللہ ﷺ کی سنت پر۔'),
            ],
        ];
    }

    /**
     * @return array{umrah:list<array<string,string>>,hajj:list<array<string,string>>,duas:list<array<string,string>>}
     */
    public function defaultHajj(): array
    {
        return [
            'umrah' => [
                ['title' => 'Ihram', 'text' => 'At the miqat: ghusl, ihram clothes, two rak‘ahs if you can, then niyyah for Umrah and talbiyah. Men: no stitched garments, no covering the head. Women: ordinary modest clothes, face uncovered, no niqab/gloves in ihram.', 'text_hi' => 'मीक़ात पर: ग़ुस्ल, एहराम के कपड़े, मुमकिन हो तो दो रकअत, फिर उमरा की नीयत और तलबिया। मर्द: सिला कपड़ा नहीं, सिर न ढकें। औरत: साधारण पर्दादार कपड़े, चेहरा खुला, एहराम में नक़ाब/दस्ताने नहीं।'],
                ['title' => 'Tawaf', 'text' => 'Enter Masjid al-Haram with the right foot. Start at the Black Stone (or in line with it). Seven circuits, Kaaba on your left. Idtiba and ramal for men in the first three if it is the arrival tawaf. Two rak‘ahs at Maqam Ibrahim if possible, then Zamzam.', 'text_hi' => 'मस्जिद हराम में दाएँ पाँव से दाख़िल हों। हजरे अस्वद (या उसकी लाइन) से शुरू करें। सात चक्कर, काबा बाईं तरफ़। आने वाले तवाफ़ में मर्दों के पहले तीन चक्कर में इज़्तिबा और रमल। मुमकिन हो तो मक़ामे इबराहीम पर दो रकअत, फिर ज़मज़म।'],
                ['title' => 'Sa‘i', 'text' => 'Safa to Marwah seven legs (Safa → Marwah is one). Start at Safa, recite the ayah of Sa‘i, make du‘a. Men jog between the green lights.', 'text_hi' => 'सफ़ा से मरवा सात दौड़ (सफ़ा से मरवा एक गिनती)। सफ़ा से शुरू करें, साई की आयत पढ़ें, दुआ करें। मर्द हरी बत्तियों के बीच तेज़ चलें।'],
                ['title' => 'Halq or taqsir', 'text' => 'Men: shave or shorten the hair. Women: cut a fingertip’s length. Ihram ends. Umrah is complete.', 'text_hi' => 'मर्द: सिर मुँडवाएँ या बाल छोटे करें। औरत: उँगली के पोर जितना काटें। एहराम ख़त्म। उमरा पूरा।'],
            ],
            'hajj' => [
                ['title' => '8 Dhul Hijjah — Mina', 'text' => 'Ihram for Hajj if you are not already in ihram. Go to Mina. Pray Zuhr, Asr, Maghrib, Isha, and Fajr shortened where applicable, without combining except as your group teaches.', 'text_hi' => 'अगर पहले से एहराम में नहीं हैं तो हज का एहराम बाँधें। मिना जाएँ। ज़ुहर, अस्र, मग़रिब, इशा और फ़ज्र क़صر जहाँ लागू हो; जमा सिर्फ़ जैसा आपका ग्रुप सिखाए।'],
                ['title' => '9 Dhul Hijjah — Arafah', 'text' => 'After sunrise, to Arafah. Stay until Maghrib. This is the pillar of Hajj. Make much du‘a. Then to Muzdalifah for Maghrib+Isha and collect pebbles.', 'text_hi' => 'सूरज निकलने के बाद अरफ़ा। मग़रिब तक ठहरें। यही हज का रुक्न है। बहुत दुआ करें। फिर मुज़दलिफ़ा — मग़रिब+इशा और कंकड़ इकट्ठा करें।'],
                ['title' => '10 Dhul Hijjah — Nahr', 'text' => 'To Mina: jamarat al-Aqabah (7 pebbles), then qurbani if required, then shave/shorten. Tawaf al-Ifadah and Sa‘i as your type of Hajj requires. Then return to Mina.', 'text_hi' => 'मिना: जमराते अक़बा (७ कंकड़), फिर क़ुर्बानी अगर ज़रूरी हो, फिर हल्क/तक़्सीर। तवाफ़े इफ़ादा और साई अपने हज के किस्म के मुताबिक़। फिर मिना लौटें।'],
                ['title' => '11–12 (or 13) — tashriq', 'text' => 'Stone all three jamarat after Zawal each day. You may leave on the 12th after stoning if you wish, or stay to the 13th. Farewell tawaf (wada‘) before leaving Makkah.', 'text_hi' => 'हर रोज़ ज़वाल के बाद तीनों जमरात पर कंकड़ मारें। चाहें तो १२ तारीख़ रमी के बाद निकल सकते हैं, या १३ तक रुकें। मक्का छोड़ने से पहले तवाफ़े विदा।'],
            ],
            'duas' => [
                $this->dua('Talbiyah', 'لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ، لَبَّيْكَ لَا شَرِيكَ لَكَ لَبَّيْكَ، إِنَّ الْحَمْدَ وَالنِّعْمَةَ لَكَ وَالْمُلْكَ، لَا شَرِيكَ لَكَ', 'Labbayk Allahumma labbayk, labbayka la sharika laka labbayk, innal-hamda wan-ni‘mata laka wal-mulk, la sharika lak.', 'Here I am, O Allah, here I am. You have no partner. Truly all praise, blessing, and dominion are Yours. You have no partner.', 'मैं हाज़िर हूँ ऐ अल्लाह, मैं हाज़िर हूँ। तेरा कोई शरीक नहीं। हम्द, नेअमत और बादशाहत तेरी ही है। तेरा कोई शरीक नहीं।', 'میں حاضر ہوں اے اللہ، میں حاضر ہوں۔ تیرا کوئی شریک نہیں۔ حمد، نعمت اور بادشاہت تیری ہی ہے۔ تیرا کوئی شریک نہیں۔'),
                $this->dua('Between Rukn Yamani and the Black Stone', 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ', 'Rabbana atina fid-dunya hasanatan wa fil-akhirati hasanatan wa qina ‘adhaban-nar.', 'Our Lord, give us good in this world and good in the Hereafter, and protect us from the Fire. (2:201)', 'ऐ हमारे रब, हमें दुनिया में भलाई दे और आख़िरत में भलाई दे, और आग से बचा। (२:२०१)', 'اے ہمارے رب، ہمیں دنیا میں بھلائی دے اور آخرت میں بھلائی دے، اور آگ سے بچا۔ (۲:۲۰۱)', '2:201'),
                $this->dua('On Safa and Marwah', 'إِنَّ الصَّفَا وَالْمَرْوَةَ مِنْ شَعَائِرِ اللَّهِ', 'Innas-safa wal-marwata min sha‘a’irillah.', 'Safa and Marwah are among the symbols of Allah. (2:158) Then face the Qibla, say takbir, and make your own du‘a.', 'सफ़ा और मरवा अल्लाह की निशानियों में से हैं। (२:१५८) फिर क़िबला रुख़ तकबीर कहें और अपनी दुआ करें।', 'صفا اور مروہ اللہ کی نشانیوں میں سے ہیں۔ (۲:۱۵۸) پھر قبلہ رخ تکبیر کہیں اور اپنی دعا کریں۔', '2:158'),
                $this->dua('At Arafah', 'لَا إِلَٰهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ', 'La ilaha illallahu wahdahu la sharika lah, lahul-mulku wa lahul-hamd, wa huwa ‘ala kulli shay’in qadir.', 'There is no god but Allah alone, without partner. His is the dominion and His is the praise, and He is over all things capable.', 'अल्लाह के सिवा कोई माबूद नहीं, अकेला, बिना शरीक। बादशाहत उसी की, हम्द उसी की, और वह हर चीज़ पर क़ादिर है।', 'اللہ کے سوا کوئی معبود نہیں، اکیلا، بغیر شریک۔ بادشاہت اسی کی، حمد اسی کی، اور وہ ہر چیز پر قادر ہے۔', '2:163'),
            ],
        ];
    }

    /**
     * @return array{title:string,arabic:string,translit:string,meaning:string,hi:string,ur:string}
     */
    private function dua(string $title, string $arabic, string $translit, string $meaning, string $hi = '', string $ur = '', string $ayah = '', string $hisn = ''): array
    {
        $row = compact('title', 'arabic', 'translit', 'meaning', 'hi', 'ur');
        if ($ayah !== '') {
            $row['ayah'] = $ayah;
        }
        if ($hisn !== '') {
            $row['hisn'] = $hisn;
        }
        return $row;
    }
}

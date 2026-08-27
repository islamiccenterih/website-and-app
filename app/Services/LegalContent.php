<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Public legal pages. English is the official text; headings are translated in the view.
 */
final class LegalContent
{
    public const UPDATED = '27 August 2026';

    /**
     * @return array{key:string,path:string,kicker:string,title:string,lead:string,meta:string,sections:list<array{id:string,title:string,paragraphs:list<string>}>}
     */
    public static function page(string $key): array
    {
        return match ($key) {
            'privacy' => self::privacy(),
            'terms' => self::terms(),
            'disclaimer' => self::disclaimer(),
            default => self::privacy(),
        };
    }

    /**
     * @return list<array{key:string,path:string,title:string}>
     */
    public static function siblings(string $key): array
    {
        $all = [
            ['key' => 'privacy', 'path' => '/privacy-policy', 'title' => 'Privacy Policy'],
            ['key' => 'terms', 'path' => '/terms-and-conditions', 'title' => 'Terms & Conditions'],
            ['key' => 'disclaimer', 'path' => '/disclaimer', 'title' => 'Disclaimer'],
        ];
        return array_values(array_filter($all, static fn (array $row): bool => $row['key'] !== $key));
    }

    private static function org(): string
    {
        return site_name();
    }

    private static function email(): string
    {
        $email = trim((string) setting('contact_email', 'info@example.com'));
        return $email !== '' ? $email : 'info@example.com';
    }

    private static function address(): string
    {
        $address = trim((string) setting('contact_address', 'Madina Colony, Firozabad, Uttar Pradesh, India'));
        return $address !== '' ? $address : 'Madina Colony, Firozabad, Uttar Pradesh, India';
    }

    /**
     * @return array{key:string,path:string,kicker:string,title:string,lead:string,meta:string,sections:list<array{id:string,title:string,paragraphs:list<string>}>}
     */
    private static function privacy(): array
    {
        $name = self::org();
        $email = self::email();
        $address = self::address();

        return [
            'key' => 'privacy',
            'path' => '/privacy-policy',
            'kicker' => 'Your information',
            'title' => 'Privacy Policy',
            'lead' => 'This policy explains what ' . $name . ' collects on this website, why we collect it, and the choices you have. It is written for visitors, students, and parents in India.',
            'meta' => 'How Islamic Center Information Hub collects, uses, and stores personal information on this website.',
            'sections' => [
                [
                    'id' => 'who',
                    'title' => '1. Who we are',
                    'paragraphs' => [
                        $name . ' (“we”, “us”, “the center”) operates this website from ' . $address . '. For questions about this policy, write to ' . $email . ' or use the Contact Us page.',
                        'This policy covers the public website, student login, live classes, Live now, course enquiries, fatawa questions, and the administration panel. It does not cover other sites we may link to.',
                        'We follow the Digital Personal Data Protection Act, 2023 (India) for personal data we process in India. The English text of this page is the official version.',
                    ],
                ],
                [
                    'id' => 'collect',
                    'title' => '2. Information we collect',
                    'paragraphs' => [
                        'You can read most of this website without creating an account. We collect personal information only when you give it to us or when the page you use needs it to work.',
                        'Contact messages: name, email address, phone number if you add one, your message, the date, and the IP address used to send the form. We keep the IP address to slow down spam.',
                        'Course enquiries: name, contact details, and the course you asked about, so the administration can reply.',
                        'Student accounts: name, email, login password (stored only as a secure hash), course enrolment, results the teachers publish, and an optional “remember me” cookie after you sign in. Student accounts are created by the center, not by public self-registration.',
                        'Fatawa questions: the name, contact details, and question you submit, so a teacher can answer you and, if published, so the public can read the ruling with personal details removed as the administration decides.',
                        'Live class and Live now: if you turn on camera or microphone, that media is sent so the class or public stream can run. We do not keep a public video archive of Live now on this site unless the administration later posts a recording through Center Updates or an embedded video.',
                        'Location: the Qibla page may ask your browser for location so the compass can point to the Kaaba from where you are. That position is used to calculate direction. We do not build a movement history or a map of where you pray.',
                        'Prayer times, Ramadan, and moon tools may remember the city you last chose on your own device (see Cookies below). Zakat figures you type are used to calculate an estimate. We do not keep a wealth profile or a zakat account for you.',
                    ],
                ],
                [
                    'id' => 'cookies',
                    'title' => '3. Cookies and similar storage',
                    'paragraphs' => [
                        'This site uses a small number of cookies and local device storage so the website can work. We do not use Google Analytics, advertising pixels, or cross-site marketing cookies.',
                        'Session cookie: keeps you signed in while you use the student or admin panel, and stores language choice in the session when needed. It is essential.',
                        'Language cookie (ic_lang): remembers English, Hindi, Urdu, or Arabic so the next visit opens in the same language. It lasts about one year.',
                        'Student remember-me cookie: only if you use the remember option on student login, so you do not have to type your password on every visit from that browser.',
                        'On-device storage: your browser may keep the last city for prayer times or Ramadan, and a compass reverse setting on the Qibla page. That data stays on your device unless you clear the site data.',
                        'You can block cookies in your browser. If you block essential cookies, login and some tools may stop working.',
                    ],
                ],
                [
                    'id' => 'use',
                    'title' => '4. How we use information',
                    'paragraphs' => [
                        'We use personal information to run the website and the center’s programmes: to answer messages and course enquiries, to provide student login, classes, results, and live sessions, to publish fatawa the administration approves, to keep the site secure, and to meet a legal request if one is made.',
                        'We do not sell your personal information. We do not use it to show you third-party adverts on this website.',
                    ],
                ],
                [
                    'id' => 'share',
                    'title' => '5. Who else may receive information',
                    'paragraphs' => [
                        'Teachers and panel members see only what they need for their work (for example a course enquiry or a live class roster).',
                        'To show prayer times, Hijri dates, moon timing, and Ramadan calendars we request data from AlAdhan and related calculation services, using a city or date, not your name. Metal prices for the zakat nisab come from public market APIs. Those providers see the technical request, not your student file.',
                        'Live audio and video may pass through a STUN/TURN relay (including Metered Open Relay) when a direct connection between devices is not possible. That is how a class or Live now can work on some networks. Do not share anything in a live session that you would not say in the room.',
                        'If a Center Update or activity page includes a YouTube or Vimeo embed, that provider’s cookies and policies apply when you play the video.',
                        'We may share information if the law requires it, or to protect the center, students, or the public from serious harm.',
                    ],
                ],
                [
                    'id' => 'keep',
                    'title' => '6. How long we keep it',
                    'paragraphs' => [
                        'Contact messages and course enquiries are kept until the administration has dealt with them and for a reasonable record period after that.',
                        'Student records, results, and live-class attendance stay while the student is enrolled and for as long as the center needs an academic record.',
                        'Fatawa questions stay while they are being answered; published rulings stay on the site until the administration removes them.',
                        'Live media is for the session. We do not use this website as a permanent CCTV store.',
                    ],
                ],
                [
                    'id' => 'rights',
                    'title' => '7. Your rights',
                    'paragraphs' => [
                        'You may ask us for a copy of the personal information we hold about you, ask us to correct it, or ask us to delete it where the law allows. Parents or guardians may write on behalf of a child whose student account we manage.',
                        'Send the request to ' . $email . ' from the email we have on file where possible, and allow reasonable time for the administration to reply. We may need to confirm that the request is from you.',
                        'You may also withdraw consent for optional items (for example camera in a live class) by turning them off or leaving the session.',
                    ],
                ],
                [
                    'id' => 'children',
                    'title' => '8. Children',
                    'paragraphs' => [
                        'This website is for the community, including families. Student accounts for minors are created by the center with the involvement of a parent or guardian. Children should not send a contact form or fatawa question with another person’s private details.',
                        'Live class camera and microphone for a minor should be used with a parent or guardian’s knowledge, on a device the family controls.',
                    ],
                ],
                [
                    'id' => 'security',
                    'title' => '9. Security',
                    'paragraphs' => [
                        'We use signed-in sessions, hashed passwords, and access limits on the admin panel. No website can promise perfect security. Please use a strong password and sign out on a shared computer.',
                    ],
                ],
                [
                    'id' => 'changes',
                    'title' => '10. Changes to this policy',
                    'paragraphs' => [
                        'We may update this page when the website or the law changes. The date at the top is the latest version. Continued use of the site after a change means you have read the updated policy.',
                    ],
                ],
                [
                    'id' => 'contact',
                    'title' => '11. How to reach us',
                    'paragraphs' => [
                        'Privacy requests: ' . $email . '. Postal and visit address: ' . $address . '. You can also use Contact Us on this website.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{key:string,path:string,kicker:string,title:string,lead:string,meta:string,sections:list<array{id:string,title:string,paragraphs:list<string>}>}
     */
    private static function terms(): array
    {
        $name = self::org();
        $email = self::email();
        $address = self::address();

        return [
            'key' => 'terms',
            'path' => '/terms-and-conditions',
            'kicker' => 'Using this website',
            'title' => 'Terms & Conditions',
            'lead' => 'These terms are the agreement between you and ' . $name . ' when you use this website, student login, live classes, or Live now.',
            'meta' => 'Rules for using the Islamic Center Information Hub website, student accounts, and live sessions.',
            'sections' => [
                [
                    'id' => 'accept',
                    'title' => '1. Acceptance',
                    'paragraphs' => [
                        'By opening this website you agree to these Terms & Conditions, the Privacy Policy, and the Disclaimer. If you do not agree, please do not use the site.',
                        'The English text is the official version. The center is ' . $name . ', ' . $address . '.',
                    ],
                ],
                [
                    'id' => 'use',
                    'title' => '2. What this website is for',
                    'paragraphs' => [
                        'This site shares information about the center: about us, courses, social activities, gallery, contact, prayer and moon tools, qibla, zakat estimate, Ramadan, fatawa, holidays, updates, and live sessions.',
                        'It is not a bank, a government portal, a hospital, or a substitute for being present at the masjid or classroom when the center requires attendance.',
                    ],
                ],
                [
                    'id' => 'accounts',
                    'title' => '3. Accounts and access',
                    'paragraphs' => [
                        'Student and administration accounts are issued by the center. You must keep your password secret and tell the administration if you think someone else used it.',
                        'We may suspend or close an account if these terms are broken, if the enrolment ends, or if we must do so for safety or the law.',
                        'You must be 18 or have a parent or guardian’s authority to use a student account. The center may set extra classroom rules for live sessions.',
                    ],
                ],
                [
                    'id' => 'conduct',
                    'title' => '4. Acceptable use',
                    'paragraphs' => [
                        'Do not try to break into the site, overload it, scrape it in a way that harms the service, or post malware.',
                        'Do not use contact forms, course enquiries, fatawa questions, or live chat to abuse, threaten, or share someone else’s private information without a right to do so.',
                        'Do not record, copy, or republish a live class or Live now stream without written permission from the administration.',
                        'Course materials, logos, photographs, and text on this site belong to the center or their listed owners. You may share a public page link. You may not copy the site as your own product.',
                    ],
                ],
                [
                    'id' => 'content',
                    'title' => '5. What you send us',
                    'paragraphs' => [
                        'Messages, enquiries, and questions must be truthful to the best of your knowledge. You give the center permission to store them and to use them to reply, to teach, or to publish a fatawa answer with personal details removed as the administration decides.',
                        'You must not send content you do not have the right to send.',
                    ],
                ],
                [
                    'id' => 'tools',
                    'title' => '6. Calculators and worship tools',
                    'paragraphs' => [
                        'Qibla, prayer times, moon timing, Islamic calendar, holidays, Ramadan times, and the zakat calculator are aids. Local moon sighting, the center’s announcement, and a qualified teacher take priority when they differ from a calculated time or amount.',
                        'The zakat page is an estimate, not a personal fatwa on your wealth.',
                    ],
                ],
                [
                    'id' => 'live',
                    'title' => '7. Live classes and Live now',
                    'paragraphs' => [
                        'Live sessions depend on your internet, device, and browser permissions for camera and microphone. Quality can vary. The center is not liable for a missed class solely because of a network problem at your end.',
                        'Join from a respectful place. Follow the teacher’s instructions. The administration may mute, remove, or end a session.',
                    ],
                ],
                [
                    'id' => 'liability',
                    'title' => '8. Liability',
                    'paragraphs' => [
                        'The website is provided as available. We work to keep it running, but we do not promise uninterrupted access, error-free pages, or that a third-party time or price feed will always be correct.',
                        'To the fullest extent allowed under Indian law, ' . $name . ' is not liable for indirect loss, or for decisions you make only from a page on this site, including worship times or zakat estimates. Nothing in these terms limits liability that the law does not allow us to limit, including fraud.',
                    ],
                ],
                [
                    'id' => 'law',
                    'title' => '9. Law and disputes',
                    'paragraphs' => [
                        'These terms are governed by the laws of India. Courts at Firozabad, Uttar Pradesh, have jurisdiction, without affecting any right you have under mandatory consumer law.',
                    ],
                ],
                [
                    'id' => 'changes',
                    'title' => '10. Changes',
                    'paragraphs' => [
                        'We may update these terms. The date at the top shows the current version. If you continue to use the site, the new terms apply.',
                    ],
                ],
                [
                    'id' => 'contact',
                    'title' => '11. Contact',
                    'paragraphs' => [
                        'Questions about these terms: ' . $email . '. Address: ' . $address . '.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{key:string,path:string,kicker:string,title:string,lead:string,meta:string,sections:list<array{id:string,title:string,paragraphs:list<string>}>}
     */
    private static function disclaimer(): array
    {
        $name = self::org();
        $email = self::email();

        return [
            'key' => 'disclaimer',
            'path' => '/disclaimer',
            'kicker' => 'Please read',
            'title' => 'Disclaimer',
            'lead' => 'Please read this with the Privacy Policy and Terms & Conditions. It explains the limits of the information and tools on this website.',
            'meta' => 'Limits of religious, calendar, zakat, qibla, and live information published by Islamic Center Information Hub.',
            'sections' => [
                [
                    'id' => 'general',
                    'title' => '1. General',
                    'paragraphs' => [
                        'Pages on this website are for information, education, and community use connected with ' . $name . '. They are not a contract for a specific result, and they are not professional legal, medical, or financial advice.',
                    ],
                ],
                [
                    'id' => 'deen',
                    'title' => '2. Deen, fatawa, and teaching',
                    'paragraphs' => [
                        'Articles, fatawa, duas, and course descriptions are shared in good faith. A published fatwa is not automatically your personal ruling. Facts differ from case to case. For a personal matter, ask a qualified teacher at the center or a scholar you trust, and follow the Qur’an and Sunnah as taught there.',
                        'Translations of Arabic or Urdu on the site are aids. The original wording they point to is not replaced by a short English line on a web page.',
                    ],
                ],
                [
                    'id' => 'times',
                    'title' => '3. Prayer times, moon, calendar, and holidays',
                    'paragraphs' => [
                        'Prayer times, Sehri and Iftar, moonrise and moonset, Hijri dates, and Islamic holidays are calculated from published methods and third-party data (including AlAdhan and sunrise/sunset feeds). Weather, location, and moon-sighting can change what a community actually observes.',
                        'For Ramadan, Eid, and other days bound to sighting, the announcement of ' . $name . ' or the local hilal committee you follow is the practical rule, not a timestamp on this website.',
                    ],
                ],
                [
                    'id' => 'qibla',
                    'title' => '4. Qibla compass',
                    'paragraphs' => [
                        'The Qibla tool uses your device location and sensors when you allow them. Cheap compass hardware, metal, cases, and calibration errors can throw the needle off. Confirm with a reliable physical compass or the mihrab at the masjid when accuracy matters.',
                    ],
                ],
                [
                    'id' => 'zakat',
                    'title' => '5. Zakat calculator',
                    'paragraphs' => [
                        'Gold and silver prices and currency conversion are fetched from public market sources and may lag or fail. The calculator does not know your full situation (livestock, crops, business partnerships, or madhhab differences). Treat the number as a study aid and confirm with a teacher before you pay zakat on that basis.',
                    ],
                ],
                [
                    'id' => 'live',
                    'title' => '6. Live now and live classes',
                    'paragraphs' => [
                        'A live stream may start late, drop, or be unavailable. Appearance on Live now or in a class is not an endorsement of every comment typed in the session. The center may end a stream at any time.',
                    ],
                ],
                [
                    'id' => 'third',
                    'title' => '7. Third parties',
                    'paragraphs' => [
                        'Links, maps, video embeds, and payment-free calculators that call other organisations are outside our control. Their terms and privacy rules apply when you use them. We are not responsible for their content or downtime.',
                    ],
                ],
                [
                    'id' => 'warranty',
                    'title' => '8. No warranty',
                    'paragraphs' => [
                        'Content is provided “as is”. ' . $name . ' does not warrant that every page is complete, current, or free of typing mistakes. If you rely on a date, a time, or an amount, confirm it with the administration at ' . $email . '.',
                    ],
                ],
            ],
        ];
    }
}

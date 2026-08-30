<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AboutSection;
use App\Models\HomeSection;
use App\Models\Setting;

/**
 * Real About Us and Home preview copy for Islamic Center Information Hub.
 */
final class AboutCatalog
{
    public static function sync(): void
    {
        $aboutNow = AboutSection::keyed();
        foreach (self::about() as $key => $row) {
            $existing = $aboutNow[$key] ?? null;
            $payload = [
                'title' => $row['title'],
                'content' => $row['content'],
                'extra_json' => json_encode($row['extra'] ?? new \stdClass(), JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (!empty($row['image']) && (empty($existing['image']) || str_contains((string) $existing['image'], 'placeholder'))) {
                $payload['image'] = $row['image'];
            }
            AboutSection::upsert($key, $payload);
        }

        $homeNow = HomeSection::keyed();
        foreach (self::home() as $key => $row) {
            $existing = $homeNow[$key] ?? null;
            $extra = $row['extra'] ?? [];
            if ($existing) {
                $prev = json_decode((string) ($existing['extra_json'] ?? ''), true);
                if (is_array($prev)) {
                    $extra = array_merge($prev, $extra);
                }
            }
            $payload = [
                'title' => $row['title'],
                'subtitle' => $row['subtitle'] ?? null,
                'content' => $row['content'],
                'extra_json' => json_encode($extra, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            HomeSection::upsert($key, $payload);
        }

        Setting::put('seo_home_title', 'Islamic Center Information Hub | Faith, Knowledge & Character');
        Setting::put(
            'seo_home_description',
            'Islamic Center Information Hub in Madina Colony teaches Qur’an and Sunnah with contemporary learning. Faith, knowledge, character, skills, and service since 2013.'
        );
        Setting::put('seo_about_title', 'About Islamic Center Information Hub | Our Story Since 2013');
        Setting::put(
            'seo_about_description',
            'From a room at Abu Hurairah High School in 2013 to Madina Colony in 2021 and Islamic Children Academy in 2025 — Deen and Duniya together in Firozabad.'
        );
        Setting::put('site_tagline', 'Where Faith Guides Learning, and Learning Inspires Purpose');
        Setting::put('footer_note', 'Islamic Center Information Hub, Madina Colony — faith, knowledge, character, skills, and service.');
        Setting::put('contact_address', 'Madina Colony, Firozabad, Uttar Pradesh, India');

        AboutSection::db()->execute(
            "UPDATE founders SET status = 'draft', updated_at = ? WHERE name LIKE '%Placeholder%'",
            [date('Y-m-d H:i:s')]
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function about(): array
    {
        return [
            'page_hero' => [
                'title' => 'About Islamic Center Information Hub',
                'content' => 'Where Faith Guides Learning, and Learning Inspires Purpose — Islamic education and character in Madina Colony, Firozabad, since 2013.',
                'image' => null,
                'extra' => ['kicker' => 'Our story'],
            ],
            'foundation' => [
                'title' => 'A strong future begins with strong foundations',
                'image' => 'assets/img/about-placeholder.svg',
                'content' => '<p>Islamic Center Information Hub is an Islamic education and community institution in Madina Colony, Firozabad, Uttar Pradesh. We are built around a simple idea: education should do more than pass on information. It should shape how a person thinks, lives, behaves, and serves.</p>'
                    . '<p>That foundation begins with faith — the Qur’an and Sunnah, sound Islamic understanding, and the values that give life direction. At the same time we recognise the world our youth are growing up in. Modern education, technology, communication, and practical skills belong to that world; they are not a rival to Deen.</p>'
                    . '<p>Our aim is not to place Deen and Duniya on two paths, but to bring them together in Firozabad. Through Islamic education, contemporary learning, character development, youth work, and community life, we seek people who are rooted in faith, confident in their identity, capable in knowledge, and beneficial to those around them.</p>',
                'extra' => [
                    'kicker' => 'The Center',
                    'established' => '2013',
                    'location' => 'Madina Colony, Firozabad',
                    'values' => ['Faith', 'Knowledge', 'Character', 'Skills', 'Service'],
                ],
            ],
            'founders_intro' => [
                'title' => 'Coordinators',
                'content' => 'The coordinators who guide Islamic Center Information Hub — in faith, education, and service.',
                'image' => null,
                'extra' => ['kicker' => 'Leadership'],
            ],
            'history' => [
                'title' => 'Our Journey',
                'image' => null,
                'content' => '<p>Every meaningful journey begins with a small step. Islamic Center Information Hub began in 2013, from a small room at Abu Hurairah High School. Resources were limited; the vision was clear — a place in this city where Islamic education could shape lives.</p>'
                    . '<p>With sabr, istiqamat, and trust in Allah ﷻ, the institution grew one step at a time: a building of our own in Madina Colony in 2021, a turn from studies toward skills, and Islamic Children Academy in 2025. What began in a small room is now a growing vision for Firozabad. Insha’Allah, there is more to come.</p>',
                'extra' => [
                    'kicker' => 'From a small room to a growing vision',
                    'timeline' => [
                        [
                            'year' => '2013',
                            'title' => 'The Beginning',
                            'text' => 'A small room at Abu Hurairah High School in Firozabad, a sincere intention, and a vision that refused to stop.',
                        ],
                        [
                            'year' => '2021',
                            'title' => 'A Home of Our Own',
                            'text' => 'By the fadl of Allah ﷻ, the Center established its own building in Madina Colony, Firozabad — a lasting place for Islamic learning and community.',
                        ],
                        [
                            'year' => 'Skills',
                            'title' => 'From Knowledge to Skills',
                            'text' => 'Islamic Studies expanded towards practical skills, confidence, and talent — growth beyond the textbook for the youth of Firozabad.',
                        ],
                        [
                            'year' => '2025',
                            'title' => 'Islamic Children Academy',
                            'text' => 'ICA opened in Firozabad for children: Islamic values with contemporary learning, communication, creativity, and essential life skills.',
                        ],
                        [
                            'year' => 'Today',
                            'title' => 'Growing, Innovating & Moving Forward',
                            'text' => 'From studies to skills, talent, and innovation — rooted in Deen as we meet a changing world, with new branches and communities ahead, Insha’Allah.',
                        ],
                    ],
                ],
            ],
            'mission' => [
                'title' => 'Educating Hearts. Empowering Minds. Building Character.',
                'image' => null,
                'content' => '<p>The mission of Islamic Center Information Hub is to make education a means of personal growth and purposeful living — in this city and for the Hereafter.</p>'
                    . '<p>We nurture a sincere relationship with Allah, a living connection with the Qur’an, and a clear grasp of Islamic values, while preparing learners for the intellectual, professional, and social demands of the modern world.</p>'
                    . '<p>Knowledge carries responsibility. We aim not only for successful individuals, but for people in Firozabad who use what they know with integrity, wisdom, and a sense of service.</p>',
                'extra' => ['kicker' => 'Our Mission'],
            ],
            'vision' => [
                'title' => 'A Generation Rooted in Faith, Ready for the Future',
                'image' => null,
                'content' => '<p>We envision a generation in Firozabad that knows who they are, what they believe, and what they can become — carrying Deen with confidence and meeting the world with wisdom.</p>'
                    . '<p>We want an education where Islamic knowledge and contemporary learning strengthen one another: Qur’an beside critical thinking, values beside technology, faith beside real potential.</p>'
                    . '<p>Beyond the classroom, Islamic Center Information Hub seeks to be a trusted center for learning, character, youth, and community in Madina Colony — so families can grow spiritually, intellectually, and socially.</p>',
                'extra' => ['kicker' => 'Our Vision'],
            ],
            'who_we_are' => [
                'title' => 'Deen & Duniya — Together, Not Apart',
                'image' => null,
                'content' => '<p>A balanced education in Firozabad does not require choosing between faith and the modern world.</p>'
                    . '<ul>'
                    . '<li>A student can learn the Qur’an and understand technology.</li>'
                    . '<li>They can develop Islamic character and strong communication.</li>'
                    . '<li>They can value tradition while thinking critically about the future.</li>'
                    . '<li>They can prepare for a career while preparing for the Hereafter.</li>'
                    . '</ul>'
                    . '<p>Islam gives direction. Knowledge gives understanding. Skills create capability. Character gives it meaning. Learning at Islamic Center Information Hub is a journey of becoming a wiser, more responsible human being.</p>',
                'extra' => ['kicker' => 'Our Approach'],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function home(): array
    {
        return [
            'hero' => [
                'title' => 'Where Faith Guides Learning, and Learning Inspires Purpose',
                'subtitle' => 'Islamic Center Information Hub',
                'content' => 'Islamic Center Information Hub brings Deen and Duniya together in Madina Colony — faith, knowledge, character, skills, and service. A strong future begins with strong foundations.',
                'extra' => [
                    'cta_label' => 'Explore Courses',
                    'cta_url' => '/courses',
                    'cta2_label' => 'About the Center',
                    'cta2_url' => '/about-us',
                ],
            ],
            'about_preview' => [
                'title' => 'About Islamic Center Information Hub',
                'subtitle' => 'Faith • Knowledge • Character • Skills • Service',
                'content' => 'Since 2013, Islamic Center Information Hub has taught that education should shape how a person thinks, lives, and serves. Faith is the foundation; contemporary learning and practical skills belong on the same path in Madina Colony. Our aim is people rooted in Deen, capable in knowledge, and beneficial to Firozabad.',
                'extra' => [
                    'cta_label' => 'Learn More',
                    'points' => [
                        'Qur’an, Sunnah, and Islamic character as the foundation',
                        'Contemporary learning, technology, and practical skills',
                        'Youth, families, and a life of sincere service',
                    ],
                ],
            ],
            'cta' => [
                'title' => 'Visit Islamic Center Information Hub',
                'subtitle' => 'You are welcome in Madina Colony',
                'content' => 'Students, families, and neighbours in Firozabad are invited to learn with purpose — in faith, knowledge, and service. Come for a course, a workshop, or a sitting with the community.',
                'extra' => [
                    'cta_label' => 'Contact Us',
                    'cta_url' => '/contact-us',
                ],
            ],
            'programs_intro' => [
                'title' => 'Center Activities',
                'subtitle' => 'What We Offer',
                'content' => 'Qur’an classes, community service, and youth work at Islamic Center Information Hub — the same path of Deen and Duniya.',
            ],
            'pillars' => [
                'title' => 'Pillars of Islam',
                'subtitle' => 'Foundations of faith',
                'content' => 'Shahadah, Salah, Sawm, Zakat and Hajj — taught as living practice at Islamic Center Information Hub, not only as names to memorise.',
            ],
            'courses_intro' => [
                'title' => 'Courses in Firozabad',
                'subtitle' => 'Education',
                'content' => 'Qur’an, tajweed, family life and practical skills — on-site in Madina Colony and online from Islamic Center Information Hub.',
                'extra' => [
                    'more_label' => 'View all courses',
                    'more_url' => '/courses',
                ],
            ],
            'activities_intro' => [
                'title' => 'Social Activities in Firozabad',
                'subtitle' => 'Community',
                'content' => 'Workshops, seminars, welfare and awareness programmes for students, youth and families at Islamic Center Information Hub.',
                'extra' => [
                    'more_label' => 'All social activities',
                    'more_url' => '/social-activities',
                ],
            ],
            'gallery_intro' => [
                'title' => 'Gallery',
                'subtitle' => 'Moments',
                'content' => 'Glimpses of classes, gatherings and campus life at Islamic Center Information Hub in Madina Colony.',
                'extra' => [
                    'more_label' => 'View Full Gallery',
                    'more_url' => '/gallery',
                ],
            ],
        ];
    }
}

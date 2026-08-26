<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Unique programme copy for Social Activities (images stay as placeholders).
 */
final class ActivityCopy
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(): array
    {
        return [
            [
                'name' => 'Workshops',
                'slug' => 'workshops',
                'kicker' => 'Training in Firozabad',
                'lead' => 'Practical workshops at Islamic Center Information Hub on character, teaching, technology, civic issues, and the Qur’an — for students, teachers, and families in Madina Colony and across the city.',
                'items' => [
                    self::item(
                        'Personality Development Workshop',
                        'Personality development workshop in Firozabad on Islamic adab, confidence, speech, and how a Muslim carries themselves at home, in class, and in public.',
                        [
                            'The Personality Development Workshop at Islamic Center Information Hub is a sitting on character, not a motivational slogan. Youth and students from Madina Colony and nearby neighbourhoods practise adab, clear speech, and the way a Muslim stands, greets, and listens — at home, in the classroom, and in the street.',
                            'Sessions move from niyyah and self-respect to practical drills: introducing yourself, answering a question without haste, dressing with haya, and keeping the tongue from what does not benefit. Teachers of Islamic Children Academy and senior students often sit with younger participants so the adab is seen, not only described.',
                            'Families looking for personality development in Firozabad that stays inside the Qur’an and Sunnah are welcome when a batch is announced. Each sitting aims at the same end: a confident Muslim who is useful at home, in class, and in the street.',
                        ],
                        true
                    ),
                    self::item(
                        'Teachers’ Training & Capacity-Building Workshop',
                        'Teacher training in Firozabad for Islamic Center and Islamic Children Academy staff: lesson craft, classroom adab, and teaching with patience.',
                        [
                            'This workshop is for teachers of Islamic Center Information Hub and Islamic Children Academy. It treats teaching as an amanah: lesson planning, classroom adab, how to correct a child without humiliating them, and how to keep Qur’an, akhlaq, and contemporary subjects on one timetable.',
                            'Participants work on lesson openings, questioning, board work, and the difference between covering a chapter and making a student understand. Senior teachers share what has worked in Madina Colony classrooms since the academy opened, including mixed-ability groups and parents who need clear feedback.',
                            'Schools and madrasas in Firozabad that want capacity-building rooted in Islamic ethics may be invited when seats remain. The Center runs this training so that every new teacher inherits the same standard of sabr, clarity, and respect for the student.',
                        ]
                    ),
                    self::item(
                        'Artificial Intelligence & Emerging Technologies Workshop',
                        'AI workshop in Firozabad for Muslim students and parents: what artificial intelligence is, where it helps study and work, and where a believer should be careful.',
                        [
                            'Islamic Center Information Hub hosts this workshop so students are not left alone with headlines about artificial intelligence. In plain language we explain what current tools can do — writing, images, search, translation — and what they cannot do: they have no taqwa, no accountability before Allah ﷻ, and no right to replace a teacher’s judgement.',
                            'Young people practise using a tool for research and note-making, then compare that output with Qur’an, hadith, and a teacher’s correction. Parents hear how to set house rules for phones, accounts, and images, and how to talk about cheating, deepfakes, and wasted hours without panic.',
                            'The Center’s view is the same as our wider work: Deen and Duniya together. Technology is a means. This workshop in Firozabad exists so the next generation can use new tools with skill and still know when to close the screen.',
                        ]
                    ),
                    self::item(
                        'Journalism & Media Skills Workshop',
                        'Journalism and media skills workshop at Islamic Center Information Hub: writing, recording, and speaking with facts, fairness, and a calm Islamic voice.',
                        [
                            'This workshop trains students and volunteers of Islamic Center Information Hub to write, record, and speak for the Center’s own work — reports of a programme, a short interview, a caption that is true. Journalism here means amanah: names spelled correctly, claims checked, and no sensationalism about the community.',
                            'Participants practise a lead paragraph, a fair quote, basic phone video, and the adab of asking a guest for time. We discuss how Muslim voices are often framed in Indian media, and how a local center in Firozabad can still tell its own story without copying hostility or slogans.',
                            'Graduates of the sitting often help document workshops, welfare drives, and academy functions. The skill is for khidmah, not celebrity. When a batch is open, youth who write or speak well are encouraged to apply through the administration.',
                        ]
                    ),
                    self::item(
                        'Waqf Board Bill: Awareness & Understanding Workshop',
                        'Waqf Board Bill awareness workshop in Firozabad: a plain reading of the debate so families understand what waqf is and what is being discussed in India.',
                        [
                            'Waqf is a trust for Allah’s sake — land, a masjid, a graveyard, a school — held so it cannot be treated as private property. Islamic Center Information Hub runs this workshop so residents of Firozabad can follow the Waqf Board Bill discussion with facts, not rumours forwarded on a phone.',
                            'The sitting explains, in simple Urdu and Hindi-friendly English, what a waqf board does, why documentation matters, and which questions a family should ask before sharing a claim on social media. Teachers avoid party slogans. The aim is understanding, not a rally inside the hall.',
                            'Parents, students of civic studies, and committee members from local masajid are the usual audience. When Parliament or the courts move, the Center updates the briefing and holds another sitting so the community is not left in the dark.',
                        ]
                    ),
                    self::item(
                        'One Nation, One Election: Awareness Workshop',
                        'Civic awareness workshop in Firozabad on the One Nation, One Election proposal, explained in language the community can follow.',
                        [
                            'Islamic Center Information Hub offers this civic briefing so voters in our city can hear what “One Nation, One Election” is proposing — simultaneous polls, possible changes to terms of assemblies, and arguments made for and against — without a party banner in the room.',
                            'The workshop uses a short presentation, a glossary of terms, and time for questions. We connect the topic to a Muslim citizen’s duty: to understand the law of the land, to reject rumour, and to vote as an adult responsible before Allah ﷻ and the Constitution.',
                            'Youth preparing for competitive exams and first-time voters from Madina Colony often attend. The Center does not tell anyone which party to support. It does insist that ignorance is not piety.',
                        ]
                    ),
                    self::item(
                        'Science & Qur’an Workshop',
                        'Science and Qur’an workshop in Firozabad: selected ayat and observation of creation, without forcing the Book of Allah to fit a headline.',
                        [
                            'This workshop at Islamic Center Information Hub reads selected ayat that speak of the heavens, the earth, the embryo, and the night and day — then sets beside them what a careful science lesson actually says. We refuse two errors: treating the Qur’an as a science textbook, and treating science as a rival to wahy.',
                            'Students of Islamic Children Academy and older learners look at diagrams, simple experiments, and tafsir notes so wonder leads to ibadah, not to cheap “miracle” clips. Teachers stress the limits of both the school syllabus and a viral post.',
                            'Families who want their children to love the Qur’an and still study physics or biology in Firozabad find this sitting a model of the Center’s method: Deen gives direction; knowledge gives understanding; neither is thrown away.',
                        ]
                    ),
                    self::item(
                        'Ramadan Special Workshops on Contemporary & Islamic Topics',
                        'Ramadan workshops in Firozabad on fasting, the heart, the home, and questions that arise in the blessed month at Islamic Center Information Hub.',
                        [
                            'Each Ramadan, Islamic Center Information Hub holds short workshops between the daily routine of Sehri, salah, and Tarawih. Topics rotate: the fiqh of fasting for students and workers, anger in the home, screens at night, zakat before Eid, and how to keep a child’s routine gentle but serious.',
                            'Sittings are kept brief so a fasting person can attend without exhaustion. Sisters’ and brothers’ groups may be held separately where needed. The tone is tarbiyah — building the month, not shaming those who are struggling.',
                            'Anyone in Firozabad who wants Ramadan to be more than a change of meal times is invited when the timetable is posted. These workshops sit alongside our Ramadan Mode page, duas, and the Center’s other community programmes for the month.',
                        ]
                    ),
                    self::item(
                        'Life & Teachings of Prophet Muhammad ﷺ',
                        'Seerah workshop in Firozabad on the life and character of Prophet Muhammad ﷺ and how those teachings shape a Muslim home and street today.',
                        [
                            'This seerah workshop at Islamic Center Information Hub is not a date-list. Participants walk through the character of Rasulullah ﷺ — his mercy, honesty, patience with children, justice with opponents, and worship in private — and then ask what that looks like in a Firozabad lane in this century.',
                            'Teachers use maps, selected ahadith, and stories that children can retell at home. Older students write a short reflection: one sunnah they will practise for forty days. The sitting is part of a wider seerah culture at the Center, including the annual Seerat-un-Nabi ﷺ competition.',
                            'Parents who want their children to love the Prophet ﷺ with knowledge, not only naat, should watch for the next batch. The Center’s foundation is the Qur’an and Sunnah; this workshop is one of the ways that foundation is taught in public.',
                        ]
                    ),
                    self::item(
                        'Stress Management & Emotional Well-Being Workshop',
                        'Stress and emotional well-being workshop in Firozabad: Islamic counsel and practical habits for anxiety, exams, and a steady heart.',
                        [
                            'Students in Firozabad sit exams, family pressure, and a phone that never sleeps. This workshop at Islamic Center Information Hub names that load without turning the deen into a slogan. We speak of tawakkul, salah as a pause, sleep, food, talking to a parent or teacher, and when to ask for medical help.',
                            'Sessions include breathing that does not pretend to be a substitute for ruqyah or a psychiatrist, dua for anxiety, and the prohibition of harming oneself. Sisters’ groups address the particular silence many girls carry. Nothing shared in the room is treated as gossip.',
                            'The Center believes character includes the heart. Youth, parents, and teachers are welcome. If a participant needs longer support, staff point them toward trusted local help rather than leaving them with a one-hour talk.',
                        ]
                    ),
                ],
            ],
            [
                'name' => 'Seminars',
                'slug' => 'seminars',
                'kicker' => 'Study & questions',
                'lead' => 'Seminars at Islamic Center Information Hub open a subject with evidence, adab, and time for the questions students and sisters actually ask — from seerah and haya to media and false ideologies.',
                'items' => [
                    self::item(
                        'Feminism: Perspectives, Questions & Islamic Understanding',
                        'Seminar in Firozabad on feminism, women’s dignity in Islam, and the real questions sisters and students ask at Islamic Center Information Hub.',
                        [
                            'This seminar at Islamic Center Information Hub does not begin with insults and does not end with slogans. We set out, in order, what major feminist claims are saying about law, the body, the family, and work — and then read the Qur’an and Sunnah on a woman’s dignity, inheritance, education, consent in nikah, and protection from zulm.',
                            'Sisters and female students are given space to ask what they hear in college, on Instagram, and at home: Is hijab oppression? Is a housewife “less”? What about domestic violence? What about a girl who wants both a degree and a Muslim marriage? Male students sit in a separate or mixed session as announced, with the same texts and a stricter adab of speech.',
                            'The Center’s position is consistent with our wider work on haya, hijab, and girls’ education: Islam honours women; culture sometimes does not. This sitting in Firozabad exists so that honour is argued from wahy, not from borrowed anger or borrowed fashion. It remains a study, not a debate-show.',
                        ]
                    ),
                    self::item(
                        'Seerat-un-Nabi ﷺ & Its Relevance to Our Lives',
                        'Seerat-un-Nabi ﷺ seminar in Firozabad on the life of the Prophet ﷺ for the home, the street, and the difficulties of this generation.',
                        [
                            'Islamic Center Information Hub returns to the seerah in this seminar because a generation can recite naat and still not know how Rasulullah ﷺ treated a neighbour, a servant, or an enemy. Speakers take selected incidents — Makkah, Madinah, Hudaybiyyah, the home of the Mothers of the Believers — and draw lines to anger on WhatsApp, exams, money, and marriage.',
                            'The hall is open to families. Children hear stories; older youth hear taklif and responsibility. We avoid cinema-style exaggeration. Every claim is tied to a known report or a clear Qur’anic description of his ﷺ character.',
                            'This seminar feeds the annual Seerat-un-Nabi ﷺ competition and the monthly taleemi sittings of the Center. Anyone in Firozabad who wants the Prophet ﷺ as a lived imam, not only a name on a poster, should attend when the date is announced.',
                        ],
                        true
                    ),
                    self::item(
                        'Haya & Hijab: Values, Identity & Responsibility',
                        'Haya and hijab seminar in Firozabad: haya as a quality of the heart, and hijab as identity, protection, and ibadah at Islamic Center Information Hub.',
                        [
                            'Haya in this seminar is treated as a quality of the heart for men and women, not a rule only for girls’ scarves. Islamic Center Information Hub speaks about the gaze, the joke, the photograph, the way brothers sit in a market, and then about hijab as identity, protection, and ibadah — not as fashion and not as a punishment.',
                            'Sisters describe, with adab, what makes hijab difficult in a Firozabad college or workplace, and what support actually helps: a father’s honour, a mother’s consistency, a school that does not mock. Brothers are asked what haya looks like in their own dress, speech, and timelines.',
                            'The sitting sits next to our Hijab Day conference and Haya Day programmes. Families who want a serious, kind word on this subject — without humiliation — will find that tone here.',
                        ]
                    ),
                    self::item(
                        'Cyber Security Awareness Seminar',
                        'Cyber security seminar in Firozabad for students, parents, and staff: passwords, scams, photos, and what to keep private.',
                        [
                            'Phones in Firozabad carry bank apps, family photos, and children’s chats. This seminar at Islamic Center Information Hub teaches passwords, two-step checks, fake job and “lottery” messages, and why a profile picture of a sister or a child should not be public property.',
                            'Staff walk through a typical scam call, a cloned website, and the habit of forwarding a “forward this or you will suffer” chain. We connect privacy to haya and to the legal duty not to circulate someone’s image without right.',
                            'Parents, academy students, and office volunteers are the core audience. The Center would rather prevent a emptied account or a leaked chat than comfort a family afterwards. A one-page checklist is usually given to take home.',
                        ]
                    ),
                    self::item(
                        'Women Empowerment & Education',
                        'Women’s education seminar in Firozabad: skill, schooling, and public life for women within the honour and limits Islam gives.',
                        [
                            'Islamic Center Information Hub uses “empowerment” in this seminar to mean what the deen actually gives: the right to learn, to own, to be safe from zulm, and to serve the ummah without being told that a degree makes her less of a Muslim or that ignorance makes her more pious.',
                            'Speakers — often sisters who teach at Islamic Children Academy or who completed courses at the Center — talk about schooling barriers, early marriage pressure, and how a family can support a girl’s Taleem without abandoning haya. Skill programmes and literacy sit next to tafsir and fiqh of the home.',
                            'This is the same vision as our Girls’ Education conference: problems named, solutions that parents can carry out in Madina Colony, not imported scripts that erase the deen. Women and the men who are responsible for them are both invited as announced.',
                        ]
                    ),
                    self::item(
                        'Mobile & Digital Security Awareness',
                        'Mobile and digital security awareness in Firozabad: how phones are used against families, and simple habits that close those doors.',
                        [
                            'This seminar is more personal than the general cyber sitting. Islamic Center Information Hub talks about location sharing, unknown friend requests, blackmail using photos, and children left alone with a cheap smartphone. The examples are from Indian cities; the adab is from the deen.',
                            'Participants practise turning off unnecessary permissions, locking a gallery, and agreeing a family rule for devices after Isha. We do not pretend a setting replaces tarbiyah. We do insist that leaving every door open is not tawakkul.',
                            'Mothers’ groups and youth batches are often separate. When a new scam pattern appears in Firozabad, the Center repeats a shorter version of this briefing so the community is not always a year behind the harm.',
                        ]
                    ),
                    self::item(
                        'Adyaan-e-Batila: Understanding False Ideologies & Beliefs',
                        'Seminar in Firozabad on false religions and modern ideologies, so iman is taught with knowledge, not left unguarded.',
                        [
                            'A student in Firozabad will meet atheism, Qadiani claims, crude “all religions are the same” talk, and political religions of race and nation. This seminar at Islamic Center Information Hub maps those claims — Adyaan-e-Batila and modern ideologies — so iman is guarded with knowledge, not with fear of every book.',
                            'Teachers state the Islamic aqeedah first, then show where a false claim contradicts tawhid, nubuwwah, or the finality of Prophethood ﷺ. The tone is serious, not insulting of persons. The goal is that a youth can answer a classmate without rage and without compromise.',
                            'Older students and teachers are the usual audience. The Center’s courses in Islamic studies and this seminar work together: aqeedah in the classroom, applied questions in the hall.',
                        ]
                    ),
                ],
            ],
            [
                'name' => 'Conferences',
                'slug' => 'conferences',
                'kicker' => 'Gatherings',
                'lead' => 'Larger programmes of Islamic Center Information Hub — haya, hijab, girls’ education, and the Islamic months — with talks, guests, and a shared niyyah.',
                'items' => [
                    self::item(
                        'Haya Conference',
                        'Haya Conference in Firozabad on modesty in dress, speech, screens, and company — a flagship gathering of Islamic Center Information Hub.',
                        [
                            'The Haya Conference is one of the Center’s larger yearly gatherings in Firozabad. Talks, reminders, and student pieces address haya in dress, speech, what we watch, and the company we keep — for brothers and sisters, with the adab of a mixed or parallel programme as announced.',
                            'Guests from the teaching staff and invited speakers keep the subject in the Qur’an and Sunnah. The conference is not a fashion show and not a scolding. It is a public statement that Islamic Center Information Hub still teaches haya as a living value in Madina Colony.',
                            'Families across the city are invited. The conference pairs with Haya Day and the haya-and-hijab seminar so the message is not a single afternoon, but a year of teaching in Firozabad.',
                        ],
                        true
                    ),
                    self::item(
                        'Hijab Day Conference',
                        'Hijab Day Conference in Firozabad: sisters and families honour hijab as faith, with talks and testimonies at Islamic Center Information Hub.',
                        [
                            'On Hijab Day, Islamic Center Information Hub gathers sisters, mothers, and supporting families to honour hijab as an act of ibadah and identity. Talks cover fiqh, social pressure in Indian colleges, and the difference between a cloth and a heart that has accepted the command of Allah ﷻ.',
                            'Testimonies, when given, are voluntary and modest. Stalls and a small book table often sit outside the hall. Brothers attend the portions meant for them: how to support, not police, and how their own haya must match what they expect.',
                            'The conference is part of the Center’s public work on women’s dignity, alongside the feminism seminar and girls’ education programmes. Visitors from other parts of Firozabad are welcome; the door of the Center is for the city, not only one lane.',
                        ]
                    ),
                    self::item(
                        'Girls’ Education: Problems, Challenges & Solutions',
                        'Girls’ education conference in Firozabad: barriers to schooling and madrasa learning, and what parents and Islamic Center Information Hub can do.',
                        [
                            'This conference names what actually stops a girl in Firozabad from finishing school or a solid Islamic course: safety on the road, money, early nikah talk, a house that thinks Taleem is only for sons, or a campus that mocks hijab. Islamic Center Information Hub puts those problems on the table with parents in the room.',
                            'Solutions are local: accompaniment, academy admissions, skill classes, PTMs at Islamic Children Academy, and a community that treats a daughter’s mind as an amanah. Speakers include teachers who have sat with families in Madina Colony since the Center’s early years.',
                            'The Center’s own story — from a small room in 2013 to ICA in 2025 — is proof that girls’ education is not a borrowed slogan here. It is part of Deen and Duniya together. The conference is held so that proof becomes a habit in more homes.',
                        ]
                    ),
                    self::item(
                        'The Significance & Lessons of Islamic Months',
                        'Conference in Firozabad on the Islamic months from Muharram to Dhul Hijjah: what each month asks of a Muslim and how the Center marks it.',
                        [
                            'The hijri year is not a calendar widget. This conference at Islamic Center Information Hub walks from Muharram to Dhul Hijjah: Ashura, Rabi al-Awwal and seerah, Rajab and Sha’ban as preparation, Ramadan, Shawwal, Dhul Hijjah and Hajj — what each month asks of a Muslim in Firozabad.',
                            'Teachers connect the month to the Center’s own programmes: Ramadan workshops, Hajj training camps, Foundation Day in late October, and competitions tied to seerah. Families leave with a simple year-plan rather than a pile of unconnected events.',
                            'Anyone who wants their home to follow the Islamic year with knowledge — not only Eid shopping — will find this gathering useful. It is one of the ways the Center teaches time as ibadah.',
                        ]
                    ),
                ],
            ],
            [
                'name' => 'Competitions',
                'slug' => 'competitions',
                'kicker' => 'Talent & knowledge',
                'lead' => 'Annual competitions at Islamic Center Information Hub in seerah, Qur’an, naat, writing, and speech — so students are rewarded for knowledge, voice, and effort, not only for marks.',
                'items' => [
                    self::item(
                        'Annual Seerat-un-Nabi ﷺ Competition',
                        'Annual Seerat-un-Nabi ﷺ competition in Firozabad: speeches, writing, and quizzes on the life of Rasulullah ﷺ at Islamic Center Information Hub.',
                        [
                            'The Annual Seerat-un-Nabi ﷺ Competition is the Center’s flagship student contest. Children and youth from Islamic Center Information Hub and Islamic Children Academy prepare speeches, written pieces, and quizzes on the life of Rasulullah ﷺ — his ﷺ birth, mission, character, and relevance to a student’s day.',
                            'Judges from the teaching staff score knowledge, adab on stage, and honesty of sources. The hall fills with parents. Prizes are announced at the award ceremony. The point is not a trophy only: it is that seerah becomes a year’s work, not a 12 Rabi poster.',
                            'Registrations open when the syllabus is posted. Students from other schools in Firozabad may be invited in some years. This competition is how the Center publicly celebrates love of the Prophet ﷺ with study.',
                        ],
                        true
                    ),
                    self::item(
                        'Khulafa-e-Rashideen Competition',
                        'Khulafa-e-Rashideen competition in Firozabad on Abu Bakr, Umar, Uthman and Ali (may Allah be pleased with them) and the lessons of those years.',
                        [
                            'Students prepare the lives of the four Khulafa-e-Rashideen: their khilafat, justice, simplicity, and trials. Islamic Center Information Hub uses this competition to teach history as uswah — how a community was led after Rasulullah ﷺ — not as a TV drama.',
                            'Rounds may include oral answers, short speeches, and written identification of events. Younger and older groups are split so a child is not crushed by a senior syllabus.',
                            'The contest sits with our seerah and Ummahat competitions as a curriculum of the first generations. Families in Firozabad who want their children to know the Khulafa by more than four names should enter them when registration opens.',
                        ]
                    ),
                    self::item(
                        'Ummahat-ul-Mu’mineen Competition',
                        'Ummahat-ul-Mu’mineen competition in Firozabad: students present the lives, knowledge, and adab of the Mothers of the Believers.',
                        [
                            'This competition honours the Mothers of the Believers — their knowledge, sabr, and the ahadith they carried to the ummah. Girls especially are encouraged, though the Center may open mixed written rounds. Islamic Center Information Hub wants students to take the Ummahat as models of ilm and haya, not as distant titles.',
                            'Presentations must be sourced. Fancy clothes do not score marks; clarity and respect do. Teachers help with reading lists so no child is left copying a random website.',
                            'Held in Firozabad as part of the yearly contest calendar, this event strengthens the same message as our hijab and girls’ education work: Muslim women in the first generation were people of knowledge.',
                        ]
                    ),
                    self::item(
                        'Qur’an Quiz – Junior Category',
                        'Junior Qur’an quiz in Firozabad for younger children: short surahs, meanings, and adab of the mushaf at Islamic Children Academy and the Center.',
                        [
                            'The junior Qur’an quiz is for younger children of Islamic Children Academy and the Center’s weekend circles. Questions cover short surahs, basic meanings, and the adab of touching and sitting with the mushaf.',
                            'The room is kept kind. A wrong answer is corrected, not mocked. Parents see that hifz and understanding can start early without turning a child into a showpiece.',
                            'Islamic Center Information Hub runs this quiz so that love of the Qur’an is a public joy in our city, not only a private class. Ages and syllabus are announced with each year’s circular.',
                        ]
                    ),
                    self::item(
                        'Qur’an Quiz – Advanced / Senior Category',
                        'Senior Qur’an quiz in Firozabad: ayat, stories of the prophets, and selected tafsir for older students of Islamic Center Information Hub.',
                        [
                            'The senior quiz is harder: identification of ayat, stories of the prophets, selected tafsir, and connections between a passage and a ruling or a lesson of adab. It is meant for older students who already sit a proper Qur’an class at Islamic Center Information Hub.',
                            'Preparation itself is the benefit. Teams revise together. Teachers publish a topic list so the contest is study, not a trick.',
                            'Winners are recognised at the annual function. The Center’s aim is a generation in Firozabad that can open the Book with familiarity, not fear of a trophy round.',
                        ]
                    ),
                    self::item(
                        'Dua & Salah Competition',
                        'Dua and salah competition in Firozabad: correct prayer, selected duas, and the adab of standing before Allah ﷻ.',
                        [
                            'Children demonstrate wudu, the positions of salah, and selected duas with meaning — not a race to shout. Islamic Center Information Hub judges correctness and khushu’ as far as a public hall can see it, and always the adab of the child on stage.',
                            'Parents often realise what has been skipped at home. Teachers use the weeks before the contest to repair common mistakes in Fajr and the sitting duas.',
                            'This competition is tarbiyah. A prize is a means. The end is that a student in Firozabad can pray as they were taught, with a heart that knows Whom they face.',
                        ]
                    ),
                    self::item(
                        'Naat Competition – Online & Offline',
                        'Naat competition in Firozabad, online and in the hall, judged on voice, husn, and respect for the words in praise of Rasulullah ﷺ.',
                        [
                            'The naat competition has an in-hall round at Islamic Center Information Hub and, when needed, an online round for those who cannot travel. Judging is on voice, pronunciation, choice of kalam, and respect — no music that crosses the Center’s limits, no performance that forgets the one ﷺ being praised.',
                            'Young voices from Madina Colony and further in the district take part. Teachers remind participants that naat is ibadah of the tongue, not a talent show copied from television.',
                            'Recordings, when published, are used to remember the event, not to chase fame. The Center’s seerah culture includes this competition as a door for students whose gift is the voice.',
                        ]
                    ),
                    self::item(
                        'Islamic Thought Competition',
                        'Islamic thought competition in Firozabad: short papers and talks on iman, current questions, and thinking as a Muslim.',
                        [
                            'Older students write or speak on a set question: an issue of iman, a social habit, or a headline that needs a Muslim mind. Islamic Center Information Hub scores argument, adab, and use of Qur’an and Sunnah — not volume.',
                            'This contest trains the same muscle as our seminars on ideologies and media. A student who can think here will not be helpless in a college canteen argument.',
                            'Topics are announced in advance. Firozabad youth enrolled at the Center or ICA are the core; others may be invited. Winning pieces may be read at a later ijtima.',
                        ]
                    ),
                    self::item(
                        'Slogan Writing Competition',
                        'Slogan writing competition in Firozabad for haya, education, environment, and the public campaigns of Islamic Center Information Hub.',
                        [
                            'One line, carefully chosen: for haya, for girls’ education, for a cleaner Firozabad, for a simple nikah. The slogan competition teaches students that public words can be true, short, and still Islamic.',
                            'Winning lines sometimes appear on Center campaign materials. That is a trust. Slogans that insult or copy a political party are rejected.',
                            'Juniors and seniors have separate prompts. The exercise looks small; it is how a community learns to speak in the street without losing adab.',
                        ]
                    ),
                    self::item(
                        'Creative Writing Competition',
                        'Creative writing competition in Firozabad: stories and sketches with adab — imagination that does not leave the deen behind.',
                        [
                            'Students write a story or a sketch that could be read in a Muslim home. Islamic Center Information Hub asks for imagination with a boundary: no vulgarity, no mockery of the deen, no copied film plot with Islamic names stuck on.',
                            'Teachers comment on language, structure, and honesty of feeling. English, Urdu, and Hindi may be allowed as announced so a child is not locked out by medium.',
                            'This is part of our wider writing culture — journalism workshop, essays, baitbaazi. A writer from Firozabad should be able to create without leaving the Qur’an at the door.',
                        ]
                    ),
                    self::item(
                        'Essay Writing Competition',
                        'Essay writing competition in Firozabad on seerah, society, and the topics Islamic Center Information Hub announces each year.',
                        [
                            'Longer essays on seerah, social habits, education, or a set contemporary question. Word limits and language are published with the circular. Islamic Center Information Hub wants evidence, structure, and a conclusion a Muslim can stand by.',
                            'Plagiarism from the internet is a disqualification. Teachers would rather a shorter honest essay than a pasted page.',
                            'Prize essays may be displayed at the annual function. The competition feeds the same intellectual work as our thought contest and seminars.',
                        ]
                    ),
                    self::item(
                        'Talent Hunt',
                        'Talent hunt in Firozabad for qirat, speech, crafts, and other gifts of students at Islamic Center Information Hub and Islamic Children Academy.',
                        [
                            'The talent hunt is a stage for qirat, speech, crafts, and other gifts that do not always fit a quiz. Islamic Center Information Hub uses it to see the child who recites well, the one who can explain a craft, the one who organises a stall — and to thank Allah ﷻ for variety in the ummah.',
                            'Acts that break haya or copy vulgar performance are not accepted. Teachers help students choose a piece that honours the hall.',
                            'Parents from Firozabad fill the room. For many children this is the first time their skill is named in public as something the deen can hold.',
                        ]
                    ),
                    self::item(
                        'Speech Competition on Diverse Topics',
                        'Speech competition in Firozabad on Islamic and civic topics, judged by teaching staff of Islamic Center Information Hub.',
                        [
                            'Prepared speeches on Islamic and civic subjects — seerah, education, cleanliness, the rights of parents, the duty of a student. Judges from the teaching staff score content, timing, and adab of address.',
                            'Nervous first speakers are coached, not crushed. The Center would rather a shaking truthful speech than a copied performance.',
                            'This contest sits with journalism training and Islamic thought. A voice trained here can later represent the Center in a school function or a smaller ijtima in the city.',
                        ]
                    ),
                ],
            ],
            [
                'name' => 'Important Days & Annual Events',
                'slug' => 'important-days',
                'kicker' => 'The Center’s year',
                'lead' => 'Days Islamic Center Information Hub returns to every year: Foundation Day on 29 October, awards, Hijab Day, Haya Day, and the annual function of Islamic Children Academy.',
                'items' => [
                    self::item(
                        'Foundation Day — 29 October',
                        'Foundation Day of Islamic Center Information Hub on 29 October: a gathering in Madina Colony to remember 2013, give thanks, and renew the vision.',
                        [
                            'Islamic Center Information Hub marks Foundation Day each year on 29 October. The gathering remembers the small room at Abu Hurairah High School in 2013, the move to our own building in Madina Colony in 2021, and the opening of Islamic Children Academy in 2025 — by the fadl of Allah ﷻ.',
                            'The programme usually includes Qur’an, a short history, dua, and thanks to teachers and workers who carried the years. It is not a boast. It is a public shukr and a reminder that the vision — Deen and Duniya together — is still the work.',
                            'Alumni, neighbours, and new families in Firozabad are welcome. The next year of workshops, classes, and khidmah is quietly intended in that sitting.',
                        ],
                        true,
                        '2026-10-29',
                        '29 October'
                    ),
                    self::item(
                        'Annual Award Ceremony',
                        'Annual award ceremony in Firozabad: certificates and thanks for students and workers of Islamic Center Information Hub and Islamic Children Academy.',
                        [
                            'The annual award ceremony thanks students who completed courses, won competitions, or showed consistent adab, and workers who kept the Center running. Islamic Center Information Hub treats a certificate as a reminder of amanah, not as a reason for kibr.',
                            'Parents hear names they already know from PTMs and functions. Guests see that the year’s quizzes, seerah contests, and academy work had an ending in public.',
                            'Held in Firozabad, usually near the end of the academic cycle, this ceremony is how the community watches its own children grow. Dates are announced each year from the administration.',
                        ]
                    ),
                    self::item(
                        'Hijab Day',
                        'Hijab Day in Firozabad: talks, stalls, and sister-led programmes honouring hijab at Islamic Center Information Hub.',
                        [
                            'Hijab Day at the Center is a full-day or half-day programme of talks, a small exhibition, and sister-led sittings. It is the more public, festive companion to the Hijab Day Conference — still serious, still modest.',
                            'Stalls may share literature and answers to common questions students hear in Firozabad colleges. The Center does not turn the day into a photoshoot. Cameras, when used, follow the Center’s haya guidelines.',
                            'Mothers bring daughters; teachers bring classes. The day says, in this city, that hijab is honoured at Islamic Center Information Hub as faith.',
                        ]
                    ),
                    self::item(
                        'Haya Day',
                        'Haya Day in Firozabad: a day to teach modesty to children, youth, and families in dress, gaze, and speech.',
                        [
                            'Haya Day is set aside to teach children and youth what haya looks like in a joke, a thumbnail, a cricket ground, and a classroom. Islamic Center Information Hub uses stories, short talks, and age-split groups so a six-year-old and a sixteen-year-old are not given the same speech.',
                            'The day supports the Haya Conference and the haya-and-hijab seminar. Together they make haya a calendar item, not a scolding when something has already gone wrong.',
                            'Families from Madina Colony and beyond are invited. The Center’s character work — personality workshop, academy adab, this day — is one project with many doors.',
                        ]
                    ),
                    self::item(
                        'Facilitation & Recognition Ceremony',
                        'Recognition ceremony in Firozabad for guests, teachers, and volunteers who serve Islamic Center Information Hub.',
                        [
                            'This ceremony receives guests, teachers, and volunteers with public thanks. Islamic Center Information Hub believes khidmah should be named: the person who kept the gate, the sister who taught a circle unpaid, the donor who asked for no post.',
                            'Speeches stay short. Dua is the main gift. A small token, when given, is a reminder, not a price on the work.',
                            'The city sees that the Center is a web of people, not a building only. Neighbours in Firozabad who have helped in a drive or a camp are often called onto the same floor as the teaching staff.',
                        ]
                    ),
                    self::item(
                        'Annual Function of Islamic Children Academy',
                        'Annual function of Islamic Children Academy, Firozabad: recitation, sketches, results, and a sitting with parents.',
                        [
                            'Islamic Children Academy (ICA), opened in 2025 under Islamic Center Information Hub, holds a yearly function: Qur’an recitation, brief sketches that keep haya, a report of learning, and time with parents. The function shows what “Islamic values with contemporary learning” looks like in a child’s year.',
                            'Communication, creativity, and essential skills sit next to adab on stage. Teachers avoid turning children into a spectacle. The point is a shared sitting between home and school.',
                            'Parents of ICA and well-wishers from Firozabad fill the hall. This function is now part of the Center’s public year, alongside Foundation Day and the award ceremony.',
                        ]
                    ),
                ],
            ],
            [
                'name' => 'Welfare & Community Service',
                'slug' => 'welfare',
                'kicker' => 'Khidmah',
                'lead' => 'Welfare from Islamic Center Information Hub: blood donation, fruit and winter drives, zakat, books, Hajj kits, and support for the sick — khidmah that reaches homes and hospitals in our city.',
                'items' => [
                    self::item(
                        'Blood Donation & Community Support Initiatives',
                        'Blood donation camps in Firozabad organised by Islamic Center Information Hub, plus emergency donor support when a family needs blood quickly.',
                        [
                            'Islamic Center Information Hub organises blood donation camps and keeps a way to call donors when a family in the city faces an emergency. Saving a life is from the deen; the camp is how that sentence becomes a queue and a form.',
                            'Doctors or trained staff oversee collection. The Center does not play with anyone’s health. Donors hear a short reminder that this is sadaqah, then they are looked after with rest and water.',
                            'Neighbours in Firozabad who can donate, or who need a donor, should contact the administration. The camps are ongoing khidmah, not a once-a-year photograph.',
                        ],
                        true
                    ),
                    self::item(
                        'Fruit Distribution Drives',
                        'Fruit distribution in Firozabad hospitals, gatherings, and homes, organised as sadaqah by Islamic Center Information Hub.',
                        [
                            'Fruit is bought or collected and given in hospitals, after programmes, and to households that asked for help. Islamic Center Information Hub treats this as a small, repeatable mercy — not a camera event.',
                            'Volunteers pack bags with adab. Recipients are not made to pose. A banana in a ward is still a banana; it does not need a speech.',
                            'When season and funds allow, the drive repeats. Donors from Firozabad who want their sadaqah to reach a bedside can give through the Center’s welfare desk.',
                        ]
                    ),
                    self::item(
                        'Tree Plantation Drives',
                        'Tree plantation in Firozabad by Islamic Center Information Hub: planting on campus and in the city as sadaqah and a duty to the street.',
                        [
                            'Planting a tree is a sadaqah that continues. Islamic Center Information Hub runs plantation drives on campus and with municipal or neighbourhood partners so Madina Colony and nearby roads gain shade, not only posters about the environment.',
                            'Students of ICA often take part so a child learns that “anti-pollution” is a shovel, not a slogan. Aftercare — water, a guard against stray goats — is discussed, not ignored.',
                            'These drives sit with our anti-pollution rally. The Center’s environmental work is civic and Islamic at once: the earth is an amanah.',
                        ]
                    ),
                    self::item(
                        'Helmet Distribution Drives',
                        'Helmet distribution in Firozabad: Islamic Center Information Hub gives helmets so riders leave a programme safer than they arrived.',
                        [
                            'Two-wheelers fill Firozabad’s roads. After selected programmes, Islamic Center Information Hub has distributed helmets so a student or a father does not ride home uncovered as if tawakkul meant carelessness.',
                            'A short reminder on road adab — speed, mobile phones, a sister on the back seat — goes with the helmet. The gift is protection, not a brand stunt.',
                            'Partners and donors who want to fund a batch should speak to the administration. Safety is part of not throwing oneself into destruction; this drive is that fiqh in plastic and foam.',
                        ]
                    ),
                    self::item(
                        'Winter Jacket Distribution',
                        'Winter jacket distribution in Firozabad for children and elders before the cold, organised by Islamic Center Information Hub.',
                        [
                            'Before the cold, the Center collects or purchases jackets and warm cloth for children and elders who would otherwise face Firozabad’s winter under-dressed. Distribution is done with a list, not a scramble that humiliates.',
                            'Volunteers size garments quickly and respectfully. Names are not announced as a spectacle of poverty.',
                            'This is seasonal khidmah. Families who can give a jacket, and families who need one, both come through the same office of Islamic Center Information Hub.',
                        ]
                    ),
                    self::item(
                        'Mask Distribution',
                        'Mask distribution in Firozabad during illness seasons and public programmes of Islamic Center Information Hub.',
                        [
                            'During illness seasons and crowded programmes, Islamic Center Information Hub issues masks so a gathering does not become a harm. The deen includes removing harm from the road; a mask in a queue is a small form of that.',
                            'Staff demonstrate a fit when needed. Waste is collected so the drive does not leave a new mess.',
                            'The Center will repeat this as public health requires. It is not a substitute for dua; it is a means next to dua.',
                        ]
                    ),
                    self::item(
                        'TB Patient Support & Adoption Initiative',
                        'TB patient support in Firozabad: visits, medicine help, and dua for patients Islamic Center Information Hub has taken on.',
                        [
                            'Tuberculosis still sits in Indian homes. Islamic Center Information Hub has taken on support for selected TB patients: visits, help with medicine where we can, food support, and dua — an “adoption” of responsibility, not of publicity.',
                            'Confidentiality is a rule. A patient’s name is not a banner. Volunteers who visit are taught the adab of a sickroom.',
                            'Donors who want their zakat or sadaqah in this channel should ask the welfare desk. The Center’s service work is as much this quiet file as it is a rally.',
                        ]
                    ),
                    self::item(
                        'Zakat Distribution',
                        'Zakat distribution in Firozabad: zakat of Islamic Center Information Hub and of donors placed with eligible families, with records kept.',
                        [
                            'Zakat is a pillar. Islamic Center Information Hub collects and places zakat with eligible families in and around Firozabad, with records kept by the administration so the amanah is traceable.',
                            'Eligibility follows fiqh, not friendship. The Center would rather delay a file than give a pillar to the wrong house. Recipients are treated as honourable, not as a photoshoot.',
                            'Our public Zakat Calculator helps a giver estimate; this programme is the next step — actually placing the due. Ramadan and the rest of the year both see distributions as funds and cases allow.',
                        ]
                    ),
                    self::item(
                        'School Bag & Educational Books Distribution',
                        'School bags and books distribution in Firozabad for children who would start the term without them, from Islamic Center Information Hub.',
                        [
                            'A child without a bag or a copy is already behind. Islamic Center Information Hub distributes bags, copies, and books before term so that poverty is not the first lesson of the year.',
                            'Lists come from ICA, local schools that ask, and families known to the welfare desk. Brand-new is not required; usable and respectful is.',
                            'This drive matches our girls’ education and academy work: Taleem needs tools. Donors in Firozabad can fund a bag as easily as they fund a speech.',
                        ]
                    ),
                    self::item(
                        'Hajj Kit Distribution',
                        'Hajj kit distribution in Firozabad: ihram, booklets, and a small kit for hujjaj leaving from the area, from Islamic Center Information Hub.',
                        [
                            'For hujjaj leaving from our area, Islamic Center Information Hub has prepared small Hajj kits — ihram where needed, a booklet of rites, and practical items for the journey. The kit is a help, not a replacement for training.',
                            'Recipients are those going to Hajj, not a random crowd. The adab of the ihram is explained when the kit is handed over.',
                            'This sits with our Hajj training camps in the province. Firozabad families preparing for the House of Allah should ask the Center what is being offered that year.',
                        ]
                    ),
                    self::item(
                        'Hajj Support & Facilitation Initiatives',
                        'Hajj support in Firozabad: help with forms, training, and questions for those preparing for Hajj, from Islamic Center Information Hub.',
                        [
                            'Forms, health questions, and fear of the unknown stop some families from even asking about Hajj. Islamic Center Information Hub offers facilitation: explaining a form, pointing to training, answering fiqh questions at the level we can, and sending people to qualified help when we cannot.',
                            'This is not a travel agency. It is a community desk for a pillar. The Center’s Hajj camps in different cities of the province are the larger version of the same khidmah.',
                            'Anyone in Firozabad intending Hajj may enquire. The administration will say clearly what we can do this season and what we cannot.',
                        ]
                    ),
                ],
            ],
            [
                'name' => 'Educational & Community Activities',
                'slug' => 'educational',
                'kicker' => 'Taleem in the city',
                'lead' => 'Regular taleem at Islamic Center Information Hub: monthly girls’ ijtima since 2013, Taleemi Karwan, Hajj camps, sunnah campaigns, fairs, Eid milan, PTMs, and online ijtima.',
                'items' => [
                    self::item(
                        'Monthly Girls Ijtima since 2013',
                        'Monthly girls’ ijtima in Firozabad since 2013: Qur’an, talks, and a sitting that has become a habit of Islamic Center Information Hub.',
                        [
                            'Since 2013 — the year Islamic Center Information Hub began — a monthly ijtima for girls has continued: Qur’an, a talk, and a sitting that many households now treat as a date on the calendar. It is one of the longest-running public habits of the Center.',
                            'Topics rotate through aqeedah, haya, study, and the home. The consistency matters as much as any single speech. Girls grow up inside a room that expected them to learn.',
                            'New families in Madina Colony are invited to send daughters. The ijtima is a living proof of the Center’s commitment to girls’ Taleem, long before ICA opened in 2025.',
                        ]
                    ),
                    self::item(
                        'Monthly Taleemi Karwan',
                        'Monthly Taleemi Karwan from Islamic Center Information Hub: travelling classes and reminders in neighbourhoods around the city.',
                        [
                            'Taleemi Karwan takes the class to the neighbourhood. Teachers and volunteers of Islamic Center Information Hub travel with a short lesson, a reminder, and sometimes books, so that Taleem is not only for those who can reach Madina Colony every day.',
                            'Stops change with the month. The karwan is how a lane that never entered the Center still hears the Qur’an and a clear word on adab.',
                            'Volunteers from Firozabad who can give a Sunday morning are needed. This is the Center moving, not the city always coming to the Center.',
                        ]
                    ),
                    self::item(
                        'Hajj Training & Awareness Camp different places in the province',
                        'Hajj training camps in Uttar Pradesh organised by Islamic Center Information Hub: rites, health, and the adab of the journey to the House of Allah.',
                        [
                            'Islamic Center Information Hub has held Hajj training and awareness camps in different cities of the province — not only in Firozabad — so that intending hujjaj can learn the rites, health precautions, and adab of the journey before ihram.',
                            'Sessions cover the pillars of Hajj, common mistakes, women’s questions, and the spiritual point of standing in Arafah. Local hosts provide the hall; the Center provides teachers and a syllabus.',
                            'This provincial khidmah matches our Hajj kits and facilitation desk. Announcements go out when a city date is fixed. Anyone preparing for Hajj in Uttar Pradesh may ask to be told of the next camp.',
                        ]
                    ),
                    self::item(
                        'Sunnah Revival Campaign',
                        'Sunnah revival campaign in Firozabad: bringing selected sunnahs back into eating, greeting, dress, and the home.',
                        [
                            'The campaign chooses a few sunnahs — greeting, eating with the right hand, miswak, a dress length, a dua of morning — and works them into homes and the academy for a set period. Islamic Center Information Hub wants revival to mean practice, not a hashtag.',
                            'Posters, a short talk, and a checklist for parents are typical tools. Teachers at ICA model the sunnah in the school day.',
                            'Firozabad families tired of a deen that exists only on the phone find this campaign a relief: small, repeatable, watched in public with kindness.',
                        ]
                    ),
                    self::item(
                        'Sunnah & Science Sessions',
                        'Sunnah and science sessions in Firozabad: a sunnah read next to what we now know of health and nature, at Islamic Center Information Hub.',
                        [
                            'A short session takes one sunnah — honey, sleep, the timing of a meal, cleanliness — and sets it beside a careful note from health or nature, without turning Rasulullah ﷺ into a lab mascot. Islamic Center Information Hub uses this to increase love of the sunnah, not to bargain with it.',
                            'Students ask questions. Teachers distinguish a hadith from a weak claim circulating in forwards.',
                            'These sessions pair with the Science and Qur’an workshop. Together they teach that the Center is not afraid of knowledge and not careless with wahy.',
                        ]
                    ),
                    self::item(
                        'Qur’an & Science Sessions',
                        'Qur’an and science sessions in Firozabad: ayat of creation paired with clear science, without stretching either.',
                        [
                            'Shorter than the full workshop, these sessions take one theme — water, mountains, the night sky — and read the ayat with a simple scientific picture. Islamic Center Information Hub refuses both mockery of the Book and fake “scientific miracles” that collapse under a schoolteacher’s question.',
                            'ICA students and older learners both attend as announced. Wonder is the intended fruit, then ibadah.',
                            'Held in Firozabad as part of the educational calendar, the sessions show visitors that our academy’s “contemporary learning” is not a threat to the Qur’an.',
                        ]
                    ),
                    self::item(
                        'Life of Prophet Muhammad ﷺ Through Educational Models',
                        'Seerah through models in Firozabad: maps and displays so children of Islamic Center Information Hub see the journey of the Prophet ﷺ, not only hear it.',
                        [
                            'Children remember what they can see. Islamic Center Information Hub uses models, maps, and displays of the seerah — the Hijrah route, a simple Madinah layout, a battlefield explained without gore — so the life of Rasulullah ﷺ is a journey in the mind, not only a sound in the ear.',
                            'Teachers walk a class around the display. Questions are invited. The models are teaching tools, not toys for a fair only.',
                            'This method supports our seerah workshops and competitions. Parents in Firozabad often say this is when a child first “saw” the seerah.',
                        ]
                    ),
                    self::item(
                        'Educational & Entrepreneurship Trade Fair',
                        'Education and entrepreneurship fair in Firozabad: student projects, small businesses, and skills families can see at Islamic Center Information Hub.',
                        [
                            'The fair shows student projects, small halal businesses, and skills the Center wants families to see: crafts, food within our limits, calligraphy, a simple service stall. Islamic Center Information Hub links this to our belief that skills create capability and that a Muslim can trade with adab.',
                            'Youth practise pricing, greeting a customer, and honesty of weight. Visitors from the neighbourhood walk the stalls without a ticket barrier when the event is open.',
                            'The fair is Deen and Duniya on one floor. It is not a mall. It is a teaching market in Firozabad.',
                        ]
                    ),
                    self::item(
                        'Community & Educational Fairs',
                        'Community and educational fairs in Firozabad: open days with stalls, books, and demonstrations at Islamic Center Information Hub.',
                        [
                            'Open days with book stalls, demonstrations, and a welcome for neighbours who have never entered the Center. Islamic Center Information Hub uses these fairs so Madina Colony and the wider city can meet the staff, see ICA’s work, and pick up a pamphlet without a formal admission process.',
                            'Children’s corners stay modest. The fair is an invitation, not a carnival that forgets the masjid next door.',
                            'If you have only heard of the Center, a community fair is a gentle first visit. Dates are posted with the year’s programmes.',
                        ]
                    ),
                    self::item(
                        'Get-Together & Community Engagement Programmes',
                        'Community get-togethers in Firozabad so parents, students, and neighbours of Islamic Center Information Hub know one another by name.',
                        [
                            'Not every programme needs a stage. These get-togethers are for names, faces, and a short reminder. Islamic Center Information Hub wants a community, not only a customer list of students.',
                            'Tea, a brief talk, and time to ask a teacher something you would not ask from a microphone. Adab of mixing is kept.',
                            'Neighbours in Firozabad who feel the Center is “for others” are the people we hope walk in. Engagement here means knowing who lives on the next turning.',
                        ]
                    ),
                    self::item(
                        'Eid Milan Programme',
                        'Eid milan in Firozabad after salah: greetings, a short talk, and time for families at Islamic Center Information Hub.',
                        [
                            'After Eid salah, Islamic Center Information Hub holds an Eid milan: greetings, a short talk, and time for families to meet who may not sit together the rest of the year. Joy is sunnah; waste and mixing without haya are not.',
                            'Children are welcome. The programme is kept within a decent length so elders can go home.',
                            'Both Eids may see a version of this sitting. It is one of the Center’s ways of being a family in Firozabad, not only a school.',
                        ]
                    ),
                    self::item(
                        'Parents–Teachers Meetings (PTM) – Islamic Children Academy',
                        'PTMs at Islamic Children Academy, Firozabad: progress, adab, and what to practise at home, hosted with Islamic Center Information Hub.',
                        [
                            'Scheduled PTMs at Islamic Children Academy give a parent a clear word on progress, adab, and what to practise at home. Islamic Center Information Hub insists that a meeting is for the child, not for a teacher’s speech and a parent’s silence.',
                            'Teachers prepare notes. Parents are asked to come on time and to speak of the child, not of another family’s child.',
                            'ICA opened in 2025; PTMs are how the academy stays a partnership. Missing them is missing half of the education we claim to offer in Firozabad.',
                        ]
                    ),
                    self::item(
                        'Baitbaazi Competitions',
                        'Baitbaazi in Firozabad on naat, hamd, and Islamic verse — memory, voice, and quick reply at Islamic Center Information Hub.',
                        [
                            'Baitbaazi on naat, hamd, and Islamic verse trains memory and ear. Islamic Center Information Hub runs it as a competition of adab: you answer with a line, not with a taunt.',
                            'Teams practise for weeks. The hall laughs in the right places. Language may be Urdu-heavy, which is a gift in our city.',
                            'This is culture with a spine. Firozabad students who love poetry find a door here that does not require a film song.',
                        ]
                    ),
                    self::item(
                        'Ghusl-e-Mayyit Awareness & Training Campaign for Women',
                        'Ghusl-e-mayyit training for women in Firozabad: fiqh and practical adab of washing the deceased, organised by Islamic Center Information Hub.',
                        [
                            'When a sister dies, other women must know ghusl of the deceased. Islamic Center Information Hub runs awareness and practical training for women: the fiqh, the cloth, the dua, and the dignity of the body. It is knowledge our community cannot outsource in a panic at night.',
                            'Training is for women, with female instructors. Models and descriptions are used with haya. No one is forced to demonstrate beyond their comfort in the first sitting.',
                            'This campaign is among the most needed services we offer in Firozabad. Mosques and families who want a batch for their lane should ask the administration.',
                        ]
                    ),
                    self::item(
                        'Online Ijtima Series',
                        'Online ijtima from Islamic Center Information Hub for those who cannot sit in the hall — same topics, from the house.',
                        [
                            'The online ijtima series is for the sister who cannot leave a sick child, the student in another town, and the elder who cannot sit on the floor of the hall. Islamic Center Information Hub streams or records a sitting so Taleem is not only for those on site in Madina Colony.',
                            'Adab of the camera is taught: dress, background, not making a child’s face a thumbnail. The topic matches the Center’s usual syllabus — Qur’an, seerah, contemporary questions.',
                            'Links are shared through the Center’s channels when a session is live. It is the same institution, a second door.',
                        ]
                    ),
                ],
            ],
            [
                'name' => 'Awareness Rallies & Campaigns',
                'slug' => 'awareness',
                'kicker' => 'In the streets',
                'lead' => 'Public campaigns from Islamic Center Information Hub: safety, anti-pollution, anti-smoking, domestic violence, aasaan nikah, and Ramadan — messages that leave the hall and enter the road.',
                'items' => [
                    self::item(
                        'Safe Environment & Community Safety Campaign',
                        'Community safety campaign in Firozabad: safe streets, lighting, and looking after neighbours, especially women and children.',
                        [
                            'Islamic Center Information Hub campaigns for a safer neighbourhood: lighting, how we walk a sister to a point, what to do if a child is missing a minute, and the duty of men to make a lane less frightening after Maghrib.',
                            'The campaign uses talks, a short rally when permitted, and meetings with people who actually live on the turning. It is civic and Islamic: removing harm is deen.',
                            'Residents of Firozabad who want to join a safety walk or a briefing should contact the Center. We would rather prevent a headline than comment on one.',
                        ]
                    ),
                    self::item(
                        'Anti-Pollution Awareness Rally',
                        'Anti-pollution rally in Firozabad against waste, smoke, and dirty water and air, led with students of Islamic Center Information Hub.',
                        [
                            'The rally speaks against waste in drains, smoke, and the habit of throwing plastic where children play. Islamic Center Information Hub puts students at the front so the next generation owns the street they will inherit.',
                            'Slogans come from our own writing contest when possible. Permission and police notice are respected. A rally that blocks an ambulance is not dawah.',
                            'Plantation drives are the quieter twin of this rally. Together they are the Center’s environmental khidmah in Firozabad.',
                        ]
                    ),
                    self::item(
                        'Anti-Smoking Awareness Campaign',
                        'Anti-smoking campaign in Firozabad for youth, with talks and a public drive from Islamic Center Information Hub.',
                        [
                            'Tobacco pulls youth in Firozabad as it does everywhere. This campaign uses talks, a stall, and frank conversation about harm to the body, the wallet, and the example set for a younger brother. Islamic Center Information Hub does not romanticise the habit as “stress relief.”',
                            'Health facts sit next to the fiqh of not harming oneself. Ex-users, when they speak, are not displayed as trophies.',
                            'Schools that want a session can ask. The Center would like fewer first cigarettes, not more lectures after addiction has set.',
                        ]
                    ),
                    self::item(
                        'Domestic Violence Awareness Campaign',
                        'Domestic violence awareness in Firozabad: a clear Islamic word on harm in the home, and where a woman or child can ask for help.',
                        [
                            'Islamic Center Information Hub states clearly that harm in the home is not qawwamiyyah and not a private joke. This campaign names physical, verbal, and financial abuse, and tells a woman or child where they can ask for help without being sent back with “sabr” as a weapon.',
                            'Talks for men insist that the Prophet ﷺ never beat a woman and that a home of fear is a failed trust. Talks for women refuse both silence and a script that tears the deen. Local legal and medical doors are mentioned when we know them.',
                            'This is among the hardest public work we do in Firozabad. It matches our feminism seminar and girls’ education conference: dignity is not a slogan if the house is unsafe.',
                        ]
                    ),
                    self::item(
                        'Aasaan Nikah Awareness Campaign',
                        'Aasaan nikah campaign in Firozabad: simple marriage, less waste, less delay — a sunnah families can actually follow.',
                        [
                            'Nikah in our culture is often delayed by jahez talk, hall prices, and pride. Islamic Center Information Hub campaigns for aasaan nikah: a simple contract, a feasible mahr, a walimah that does not bankrupt, and a timeline that does not trap a girl or boy for years.',
                            'The campaign is for parents as much as for youth. Speakers use seerah and the practice of the salaf, not a foreign pamphlet.',
                            'Firozabad families tired of marriage as a show will find allies here. The Center will keep saying this until the street is less expensive to enter with honour.',
                        ]
                    ),
                    self::item(
                        'Ramadan Awareness Campaign',
                        'Ramadan awareness in Firozabad before the month: Sehri, salah, Tarawih, and how the city can receive Ramadan together.',
                        [
                            'Before Ramadan, Islamic Center Information Hub runs an awareness campaign: Sehri and Iftar times (also on our Ramadan Mode page), the fiqh of fasting for students and labourers, Tarawih, and how shops and homes can receive the month without wasting food.',
                            'Leaflets and short sittings go out in Madina Colony and further. The campaign is an invitation to the mosque and to the Center’s Ramadan workshops.',
                            'Firozabad should feel the month together. This drive is how the Center says so in the street, not only inside a hall.',
                        ]
                    ),
                    self::item(
                        'Social & Community Awareness Drives',
                        'Social awareness drives in Firozabad through the year on public adab, cleanliness, and civic duty, from Islamic Center Information Hub.',
                        [
                            'Shorter drives through the year cover adab in queues, cleanliness after a programme, speaking to elders, and not blocking a lane with a car. Islamic Center Information Hub treats civic duty as part of a Muslim’s public face in Firozabad.',
                            'A drive may be a week of reminders, a stall, or a school visit. The point is repetition until a habit moves.',
                            'These drives fill the gaps between large rallies. Neighbours who want a topic raised — a dumping corner, a noisy night — can bring it to the administration.',
                        ]
                    ),
                    self::item(
                        'And Various Other Educational, Islamic & Social Initiatives',
                        'Further educational, Islamic, and social initiatives of Islamic Center Information Hub as the year asks — always in Firozabad’s service.',
                        [
                            'Not every need fits a named annual programme. Islamic Center Information Hub takes up further educational, Islamic, and social initiatives as the year asks: a relief collection, a guest lesson, a response to a local crisis, a new circle for a neighbourhood that asked.',
                            'Each initiative is still measured by the same vision: faith, knowledge, character, skills, and service — Deen and Duniya together, from a small room in 2013 to the work you see now in Madina Colony.',
                            'Follow the Center’s announcements. If your lane needs a sitting we have not listed, write to us. The catalogue on this page is a door, not a fence.',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * @param list<string> $paragraphs
     * @return array<string, mixed>
     */
    public static function item(string $title, string $short, array $paragraphs, bool $featured = false, ?string $date = null, ?string $year = null): array
    {
        $full = '';
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            $full .= '<p>' . htmlspecialchars($p, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }
        $row = [
            'title' => $title,
            'short' => $short,
            'full' => $full,
        ];
        if ($featured) {
            $row['featured'] = true;
        }
        if ($date) {
            $row['date'] = $date;
        }
        if ($year) {
            $row['year'] = $year;
        }
        return $row;
    }
}

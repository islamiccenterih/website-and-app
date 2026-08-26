-- Demo / placeholder seed data for Islamic Center
-- Replace all placeholder copy from the Admin Panel once real content is available.
-- Default passwords (change immediately after first login):
--   Admin:   admin@example.com / Admin@12345
--   Student: student@example.com / Student@12345

SET NAMES utf8mb4;

INSERT INTO `admins` (`name`, `email`, `password_hash`, `status`, `created_at`, `updated_at`) VALUES
('Site Administrator', 'admin@example.com', '$2y$10$Oy9fNHCudmOvFPpO6f/RvuygPtJDUWYNv/4Q6ME2nKJNXfJaIURnq', 'active', NOW(), NOW());

INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('site_name', 'Islamic Center Information Hub', NOW()),
('site_tagline', 'Where Faith Guides Learning, and Learning Inspires Purpose', NOW()),
('contact_address', 'Madina Colony, Firozabad, Uttar Pradesh, India', NOW()),
('contact_email', 'info@example.com', NOW()),
('contact_phone', '+00 000 000 0000', NOW()),
('contact_hours', 'Placeholder hours: Saturday–Thursday, 8:00 am – 6:00 pm', NOW()),
('footer_note', 'Islamic Center Information Hub — faith, knowledge, character, skills, and service.', NOW()),
('location_lat', '27.1591', NOW()),
('location_lng', '78.3957', NOW()),
('location_label', 'Firozabad, Uttar Pradesh, India 283203', NOW()),
('timezone', 'Asia/Kolkata', NOW()),
('seo_home_title', 'Islamic Center Information Hub — Where Faith Guides Learning', NOW()),
('seo_home_description', 'Islamic Center Information Hub brings Deen and Duniya together: Qur’an and Sunnah, contemporary learning, character, skills, and service.', NOW()),
('logo_text', 'Islamic Center Information Hub', NOW()),
('logo_image', '', NOW());

INSERT INTO `home_sections` (`section_key`, `title`, `subtitle`, `content`, `image`, `extra_json`, `updated_at`) VALUES
('hero', 'Where Faith Guides Learning, and Learning Inspires Purpose', 'Islamic Center Information Hub', 'A strong future begins with strong foundations. We bring Deen and Duniya together — faith, knowledge, character, skills, and service.', 'assets/img/hero-placeholder.svg', '{"cta_label":"Explore Courses","cta_url":"/courses","cta2_label":"About the Center","cta2_url":"/about-us"}', NOW()),
('about_preview', 'About Islamic Center Information Hub', 'Faith • Knowledge • Character • Skills • Service', 'We believe education should shape how a person thinks, lives, and serves. Faith is the foundation; contemporary learning and practical skills belong on the same path. Our aim is people rooted in Deen, capable in knowledge, and beneficial to those around them.', 'assets/img/about-placeholder.svg', '{"cta_label":"Learn More","points":["Qur’an, Sunnah, and Islamic character as the foundation","Contemporary learning, technology, and practical skills","Youth, families, and a life of sincere service"]}', NOW()),
('cta', 'Visit, learn, and take part', 'You are welcome', 'Students, families, and neighbours are invited to learn with purpose — in faith, knowledge, and service.', NULL, '{"cta_label":"Contact Us","cta_url":"/contact-us"}', NOW());

-- About Us body copy is applied on first load from App\\Services\\AboutCatalog.

INSERT INTO `programs` (`title`, `short_description`, `image`, `link_url`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('Quran Classes', '', 'assets/img/program-quran.svg', '/courses', 1, 'published', NOW(), NOW()),
('Community Service', '', 'assets/img/program-community.svg', '/social-activities', 2, 'published', NOW(), NOW()),
('Youth Program', '', 'assets/img/program-youth.svg', '/courses', 3, 'published', NOW(), NOW());

INSERT INTO `courses` (`title`, `slug`, `short_description`, `full_description`, `fees`, `duration`, `mode`, `additional_info`, `main_image`, `status`, `featured`, `sort_order`, `created_at`, `updated_at`) VALUES
('Fundamental of Our Deen', 'fundamental-of-our-deen', 'A 40-day course on the core of iman — what a Muslim believes, how we worship, and how we live with others.', '<p>This course walks through the foundations of our deen in a clear, practical way: tawheed, the pillars of iman and Islam, salah, and the manners that shape daily life.</p><p>It is meant for brothers and sisters who want a firm start, or who wish to refresh what they already know, over a focused 40-day period. Class days and batch timings are confirmed at enrollment.</p>', '₹999', '40 Days', 'hybrid', 'Complete course fee: ₹999.', 'assets/img/course-fundamental-of-our-deen.jpg', 'published', 1, 1, NOW(), NOW()),
('My Partner My Jannah', 'my-partner-my-jannah', 'A 40-day course for spouses on building a marriage that is a source of sakoon, mercy, and reward.', '<p>My Partner My Jannah looks at marriage as an ibadah: rights and responsibilities, speech in the home, conflict, in-laws, and keeping Allah ﷻ at the centre of the relationship.</p><p>Couples and those preparing for marriage are welcome. The course runs for 40 days. Class days and batch timings are confirmed at enrollment.</p>', '₹999', '40 Days', 'hybrid', 'Complete course fee: ₹999.', 'assets/img/course-my-partner-my-jannah.jpg', 'published', 1, 2, NOW(), NOW()),
('Nurture of Your Children', 'nurture-of-your-children', 'A 40-day course for parents on raising children with iman, adab, and a calm, guided home.', '<p>Nurture of Your Children is for mothers and fathers who want Islamic direction in the years that shape a child’s character — salah, screens, friends, respect, and speaking about Allah ﷻ at home.</p><p>The course is 40 days. Bring questions from your own family life; class days and batch timings are confirmed at enrollment.</p>', '₹999', '40 Days', 'hybrid', 'Complete course fee: ₹999.', 'assets/img/course-nurture-of-your-children.jpg', 'published', 1, 3, NOW(), NOW()),
('Tafseer Ul Quran', 'tafseer-ul-quran', 'A one-year study of the meaning of the Quran — what the ayat say, and how they guide a Muslim life.', '<p>Tafseer Ul Quran is a year-long sitting with the Book of Allah ﷻ. Each month we read, explain selected passages, and connect the meaning to belief and practice.</p><p>The course is suited to students who can commit to regular classes across the year. A registration fee is paid once; tuition is monthly.</p>', '₹500 monthly + ₹200 registration', 'One Year', 'hybrid', '₹200 registration (once) and ₹500 each month.', 'assets/img/course-tafseer-ul-quran.jpg', 'published', 1, 4, NOW(), NOW()),
('Tajweed Ul Quran', 'tajweed-ul-quran', 'A two-month course to recite the Quran with correct makharij and the rules of tajweed.', '<p>Tajweed Ul Quran trains the tongue and the ear: letters from their points of articulation, the common rules of noon, meem, madd, and waqf, and regular recitation with a teacher.</p><p>The full course is two months. It is open to beginners and to those who already read but want to correct their recitation.</p>', '₹700 full course', '2 Month', 'hybrid', 'One fee of ₹700 covers the complete two-month course.', 'assets/img/course-tajweed-ul-quran.jpg', 'published', 1, 5, NOW(), NOW()),
('Samaat e Quran', 'samaat-e-quran', 'Listen to and revise Quran recitation at a pace that suits you, billed month by month.', '<p>Samaat e Quran is for students who wish to hear the Quran, follow along, and strengthen their listening and revision — whether they are new to recitation or already memorising.</p><p>You may continue month to month as you wish. Timings are arranged with the teacher after enrollment.</p>', '₹300 per month', 'As per as your wish', 'hybrid', '₹300 each month. Continue for as long as you wish.', 'assets/img/course-samaat-e-quran.jpg', 'published', 1, 6, NOW(), NOW()),
('Video Editing', 'video-editing', 'A four-month practical course in video editing for classes, dawah clips, and center programs.', '<p>Video Editing covers the tools and habits needed to cut, title, and finish short videos for the center — lesson recordings, announcements, and simple dawah clips.</p><p>The course runs for four months. Students should have access to a computer for practice. Software and class days are confirmed at enrollment.</p>', '₹600 per month', '4 Month', 'online', '₹600 each month for four months.', 'assets/img/course-video-editing.jpg', 'published', 1, 7, NOW(), NOW());

-- Social activities are created on first site load from App\Services\ActivityCatalog.

INSERT INTO `gallery_categories` (`name`, `slug`, `description`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('Campus', 'campus', 'Placeholder album: buildings and grounds.', 1, 'published', NOW(), NOW()),
('Classes', 'classes', 'Placeholder album: classroom and study photographs.', 2, 'published', NOW(), NOW()),
('Community Events', 'community-events', 'Placeholder album: gatherings and programs.', 3, 'published', NOW(), NOW());

INSERT INTO `gallery_images` (`category_id`, `image_path`, `title`, `alt_text`, `sort_order`, `featured`, `status`, `created_at`) VALUES
(1, 'assets/img/gallery-1.svg', 'Campus view (placeholder)', 'Placeholder campus photograph', 1, 1, 'published', NOW()),
(1, 'assets/img/gallery-2.svg', 'Prayer hall (placeholder)', 'Placeholder prayer hall photograph', 2, 1, 'published', NOW()),
(2, 'assets/img/gallery-3.svg', 'Classroom (placeholder)', 'Placeholder classroom photograph', 1, 1, 'published', NOW()),
(2, 'assets/img/gallery-4.svg', 'Study circle (placeholder)', 'Placeholder study photograph', 2, 0, 'published', NOW()),
(3, 'assets/img/gallery-5.svg', 'Community gathering (placeholder)', 'Placeholder community photograph', 1, 1, 'published', NOW()),
(3, 'assets/img/gallery-6.svg', 'Program day (placeholder)', 'Placeholder program photograph', 2, 1, 'published', NOW()),
(3, 'assets/img/gallery-7.svg', 'Visitor day (placeholder)', 'Placeholder visitor photograph', 3, 0, 'published', NOW()),
(1, 'assets/img/gallery-8.svg', 'Courtyard (placeholder)', 'Placeholder courtyard photograph', 3, 0, 'published', NOW());

INSERT INTO `students` (`name`, `email`, `password_hash`, `phone`, `enrollment_no`, `course_id`, `status`, `created_at`, `updated_at`) VALUES
('Demo Student', 'student@example.com', '$2y$10$fPRV.W.AJZ/nOj2VJDdZS.0Hv23qdmp8WCdYh9m24q2aFmW5FEV8.', '0000000000', 'IC-0001', 5, 'active', NOW(), NOW());

INSERT INTO `student_courses` (`student_id`, `course_id`, `created_at`) VALUES
(1, 5, NOW()),
(1, 1, NOW());

INSERT INTO `results` (`student_id`, `course_id`, `title`, `term`, `score`, `grade`, `remarks`, `status`, `issued_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'Placeholder term result', 'Term 1 (demo)', '—', 'Pending', 'This is a published demo result visible only to the logged-in student. Replace from Admin → Results.', 'published', '2026-06-01', NOW(), NOW());

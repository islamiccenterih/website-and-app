<?php

declare(strict_types=1);

use App\Controllers\Admin;
use App\Controllers\PublicSite;
use App\Controllers\Student;
use App\Core\Router;

/** @var Router $router */

$router->get('/language/{code}', [PublicSite\LanguageController::class, 'set']);
$router->get('/', [PublicSite\HomeController::class, 'index']);
$router->get('/about-us', [PublicSite\AboutController::class, 'index']);
$router->get('/gallery', [PublicSite\GalleryController::class, 'index']);
$router->get('/courses', [PublicSite\CourseController::class, 'index']);
$router->get('/courses/{slug}', [PublicSite\CourseController::class, 'show']);
$router->post('/courses/{slug}', [PublicSite\CourseController::class, 'enquire']);
$router->get('/social-activities', [PublicSite\ActivityController::class, 'index']);
$router->get('/social-activities/{slug}', [PublicSite\ActivityController::class, 'show']);
$router->get('/contact-us', [PublicSite\ContactController::class, 'index']);
$router->post('/contact-us', [PublicSite\ContactController::class, 'submit']);
$router->get('/moon-timing', [PublicSite\MoonController::class, 'index']);
$router->get('/api/prayer-times', [PublicSite\PrayerController::class, 'times']);
$router->get('/islamic-calendar', [PublicSite\CalendarController::class, 'index']);
$router->get('/islamic-holidays', [PublicSite\HolidayController::class, 'index']);
$router->get('/qibla-direction', [PublicSite\QiblaController::class, 'index']);
$router->get('/zakat-calculator', [PublicSite\ZakatController::class, 'index']);
$router->get('/ramadan-mode', [PublicSite\RamadanController::class, 'index']);
$router->get('/fatawa', [PublicSite\FatwaController::class, 'index']);
$router->get('/fatawa/{slug}', [PublicSite\FatwaController::class, 'show']);
$router->post('/fatawa/{slug}', [PublicSite\FatwaController::class, 'ask']);
$router->get('/live', [PublicSite\PublicLiveController::class, 'index']);
$router->get('/api/public-live/status', [\App\Controllers\PublicLiveApiController::class, 'status']);
$router->post('/api/public-live/host/start', [\App\Controllers\PublicLiveApiController::class, 'hostStart'], ['admin']);
$router->post('/api/public-live/host/state', [\App\Controllers\PublicLiveApiController::class, 'hostState'], ['admin']);
$router->post('/api/public-live/host/signal', [\App\Controllers\PublicLiveApiController::class, 'hostSignal'], ['admin']);
$router->post('/api/public-live/host/media', [\App\Controllers\PublicLiveApiController::class, 'hostMedia'], ['admin']);
$router->post('/api/public-live/host/push', [\App\Controllers\PublicLiveApiController::class, 'hostPush'], ['admin']);
$router->post('/api/public-live/host/end', [\App\Controllers\PublicLiveApiController::class, 'hostEnd'], ['admin']);
$router->get('/api/public-live/watch/frame', [\App\Controllers\PublicLiveApiController::class, 'watchFrame']);
$router->get('/api/public-live/watch/audio', [\App\Controllers\PublicLiveApiController::class, 'watchAudio']);
$router->post('/api/public-live/watch/join', [\App\Controllers\PublicLiveApiController::class, 'watchJoin']);
$router->post('/api/public-live/watch/state', [\App\Controllers\PublicLiveApiController::class, 'watchState']);
$router->post('/api/public-live/watch/signal', [\App\Controllers\PublicLiveApiController::class, 'watchSignal']);
$router->post('/api/public-live/watch/leave', [\App\Controllers\PublicLiveApiController::class, 'watchLeave']);
$router->post('/api/public-live/watch/comment', [\App\Controllers\PublicLiveApiController::class, 'watchComment']);
$router->get('/center-updates', [PublicSite\UpdateController::class, 'index']);
$router->get('/center-updates/{slug}', [PublicSite\UpdateController::class, 'show']);
$router->get('/api/qibla', [PublicSite\QiblaController::class, 'api']);
$router->get('/api/zakat/nisab', [PublicSite\ZakatController::class, 'nisab']);
$router->get('/api/zakat/calculate', [PublicSite\ZakatController::class, 'calculate']);
$router->post('/api/zakat/calculate', [PublicSite\ZakatController::class, 'calculate']);
$router->get('/api/ramadan', [PublicSite\RamadanController::class, 'api']);
$router->get('/privacy-policy', [PublicSite\LegalController::class, 'privacy']);
$router->get('/privacy', [PublicSite\LegalController::class, 'privacy']);
$router->get('/terms-and-conditions', [PublicSite\LegalController::class, 'terms']);
$router->get('/terms', [PublicSite\LegalController::class, 'terms']);
$router->get('/disclaimer', [PublicSite\LegalController::class, 'disclaimer']);
$router->get('/robots.txt', [PublicSite\SeoController::class, 'robots']);
$router->get('/sitemap.xml', [PublicSite\SeoController::class, 'sitemap']);

$router->get('/admin/login', [Admin\AuthController::class, 'showLogin'], ['guest-admin']);
$router->post('/admin/login', [Admin\AuthController::class, 'login'], ['guest-admin']);
$router->post('/admin/logout', [Admin\AuthController::class, 'logout'], ['admin']);
$router->get('/admin', [Admin\DashboardController::class, 'index'], ['admin']);

$router->get('/admin/members', [Admin\MemberController::class, 'index'], ['admin']);
$router->get('/admin/members/create', [Admin\MemberController::class, 'create'], ['admin']);
$router->post('/admin/members', [Admin\MemberController::class, 'store'], ['admin']);
$router->get('/admin/members/{id}/edit', [Admin\MemberController::class, 'edit'], ['admin']);
$router->post('/admin/members/{id}', [Admin\MemberController::class, 'update'], ['admin']);
$router->post('/admin/members/{id}/delete', [Admin\MemberController::class, 'destroy'], ['admin']);

$router->get('/admin/courses', [Admin\CourseController::class, 'index'], ['admin']);
$router->get('/admin/courses/create', [Admin\CourseController::class, 'create'], ['admin']);
$router->post('/admin/courses', [Admin\CourseController::class, 'store'], ['admin']);
$router->get('/admin/courses/{id}/edit', [Admin\CourseController::class, 'edit'], ['admin']);
$router->post('/admin/courses/{id}', [Admin\CourseController::class, 'update'], ['admin']);
$router->post('/admin/courses/{id}/delete', [Admin\CourseController::class, 'destroy'], ['admin']);
$router->post('/admin/courses/{id}/images/{imageId}/delete', [Admin\CourseController::class, 'deleteImage'], ['admin']);

$router->get('/admin/activities', [Admin\ActivityController::class, 'index'], ['admin']);
$router->get('/admin/activities/create', [Admin\ActivityController::class, 'create'], ['admin']);
$router->post('/admin/activities', [Admin\ActivityController::class, 'store'], ['admin']);
$router->post('/admin/activities/sections', [Admin\ActivityController::class, 'storeSection'], ['admin']);
$router->post('/admin/activities/sections/{id}', [Admin\ActivityController::class, 'updateSection'], ['admin']);
$router->post('/admin/activities/sections/{id}/delete', [Admin\ActivityController::class, 'destroySection'], ['admin']);
$router->get('/admin/activities/{id}/edit', [Admin\ActivityController::class, 'edit'], ['admin']);
$router->post('/admin/activities/{id}', [Admin\ActivityController::class, 'update'], ['admin']);
$router->post('/admin/activities/{id}/delete', [Admin\ActivityController::class, 'destroy'], ['admin']);
$router->post('/admin/activities/{id}/images/{imageId}/delete', [Admin\ActivityController::class, 'deleteImage'], ['admin']);

$router->get('/admin/gallery', [Admin\GalleryController::class, 'index'], ['admin']);
$router->post('/admin/gallery/images', [Admin\GalleryController::class, 'storeImages'], ['admin']);
$router->post('/admin/gallery/images/{id}', [Admin\GalleryController::class, 'updateImage'], ['admin']);
$router->post('/admin/gallery/images/{id}/delete', [Admin\GalleryController::class, 'destroyImage'], ['admin']);

$router->get('/admin/about', [Admin\AboutController::class, 'index'], ['admin']);
$router->post('/admin/about', [Admin\AboutController::class, 'update'], ['admin']);

$router->get('/admin/coordinators', [Admin\CoordinatorController::class, 'index'], ['admin']);
$router->post('/admin/coordinators', [Admin\CoordinatorController::class, 'updateIntro'], ['admin']);
$router->post('/admin/coordinators/add', [Admin\CoordinatorController::class, 'store'], ['admin']);
$router->post('/admin/coordinators/{id}/delete', [Admin\CoordinatorController::class, 'destroy'], ['admin']);
$router->post('/admin/coordinators/{id}', [Admin\CoordinatorController::class, 'update'], ['admin']);

$router->get('/admin/home', [Admin\HomeController::class, 'index'], ['admin']);
$router->post('/admin/home', [Admin\HomeController::class, 'update'], ['admin']);

$router->get('/admin/programs', [Admin\ProgramController::class, 'index'], ['admin']);
$router->post('/admin/programs', [Admin\ProgramController::class, 'store'], ['admin']);
$router->post('/admin/programs/{id}', [Admin\ProgramController::class, 'update'], ['admin']);
$router->post('/admin/programs/{id}/delete', [Admin\ProgramController::class, 'destroy'], ['admin']);

$router->get('/admin/contact', [Admin\ContactController::class, 'index'], ['admin']);
$router->post('/admin/contact', [Admin\ContactController::class, 'update'], ['admin']);
$router->get('/admin/footer', [Admin\FooterController::class, 'index'], ['admin']);
$router->post('/admin/footer', [Admin\FooterController::class, 'update'], ['admin']);
$router->get('/admin/pages', [Admin\PagesController::class, 'index'], ['admin']);
$router->post('/admin/pages', [Admin\PagesController::class, 'update'], ['admin']);
$router->get('/admin/pages/{key}', [Admin\PagesController::class, 'edit'], ['admin']);
$router->post('/admin/pages/{key}', [Admin\PagesController::class, 'updatePage'], ['admin']);
$router->get('/admin/qibla', [Admin\QiblaController::class, 'index'], ['admin']);
$router->post('/admin/qibla', [Admin\QiblaController::class, 'update'], ['admin']);
$router->get('/admin/zakat', [Admin\ZakatController::class, 'index'], ['admin']);
$router->post('/admin/zakat', [Admin\ZakatController::class, 'update'], ['admin']);
$router->get('/admin/ramadan', [Admin\RamadanController::class, 'index'], ['admin']);
$router->post('/admin/ramadan', [Admin\RamadanController::class, 'update'], ['admin']);
$router->get('/admin/fatawa', [Admin\FatwaController::class, 'index'], ['admin']);
$router->get('/admin/fatawa/create', [Admin\FatwaController::class, 'create'], ['admin']);
$router->post('/admin/fatawa', [Admin\FatwaController::class, 'store'], ['admin']);
$router->post('/admin/fatawa/questions/{id}/answer', [Admin\FatwaController::class, 'answer'], ['admin']);
$router->post('/admin/fatawa/questions/{id}/hide', [Admin\FatwaController::class, 'hide'], ['admin']);
$router->post('/admin/fatawa/questions/{id}/delete', [Admin\FatwaController::class, 'destroyQuestion'], ['admin']);
$router->get('/admin/fatawa/{id}/edit', [Admin\FatwaController::class, 'edit'], ['admin']);
$router->post('/admin/fatawa/{id}', [Admin\FatwaController::class, 'update'], ['admin']);
$router->post('/admin/fatawa/{id}/delete', [Admin\FatwaController::class, 'destroy'], ['admin']);
$router->get('/admin/updates', [Admin\UpdateController::class, 'index'], ['admin']);
$router->get('/admin/updates/create', [Admin\UpdateController::class, 'create'], ['admin']);
$router->post('/admin/updates/upload', [Admin\UpdateController::class, 'upload'], ['admin']);
$router->post('/admin/updates/embed', [Admin\UpdateController::class, 'embed'], ['admin']);
$router->post('/admin/updates', [Admin\UpdateController::class, 'store'], ['admin']);
$router->get('/admin/updates/{id}/edit', [Admin\UpdateController::class, 'edit'], ['admin']);
$router->post('/admin/updates/{id}', [Admin\UpdateController::class, 'update'], ['admin']);
$router->post('/admin/updates/{id}/delete', [Admin\UpdateController::class, 'destroy'], ['admin']);
$router->get('/admin/worship', [Admin\WorshipController::class, 'index'], ['admin']);
$router->post('/admin/worship', [Admin\WorshipController::class, 'update'], ['admin']);
$router->get('/admin/messages', [Admin\MessageController::class, 'index'], ['admin']);
$router->post('/admin/messages/{id}/delete', [Admin\MessageController::class, 'destroy'], ['admin']);
$router->get('/admin/enquiries', [Admin\EnquiryController::class, 'index'], ['admin']);
$router->post('/admin/enquiries/export', [Admin\EnquiryController::class, 'export'], ['admin']);
$router->get('/admin/enquiries/{id}', [Admin\EnquiryController::class, 'show'], ['admin']);
$router->post('/admin/enquiries/{id}/contacted', [Admin\EnquiryController::class, 'markContacted'], ['admin']);
$router->post('/admin/enquiries/{id}/delete', [Admin\EnquiryController::class, 'destroy'], ['admin']);

$router->get('/admin/calendar', [Admin\CalendarController::class, 'index'], ['admin']);
$router->get('/admin/calendar/create', [Admin\CalendarController::class, 'create'], ['admin']);
$router->post('/admin/calendar', [Admin\CalendarController::class, 'store'], ['admin']);
$router->get('/admin/calendar/{id}/edit', [Admin\CalendarController::class, 'edit'], ['admin']);
$router->post('/admin/calendar/{id}', [Admin\CalendarController::class, 'update'], ['admin']);
$router->post('/admin/calendar/{id}/delete', [Admin\CalendarController::class, 'destroy'], ['admin']);
$router->post('/admin/calendar/{id}/events', [Admin\CalendarController::class, 'storeEvent'], ['admin']);
$router->post('/admin/calendar/events/{id}/delete', [Admin\CalendarController::class, 'destroyEvent'], ['admin']);

$router->get('/admin/students', [Admin\StudentManageController::class, 'index'], ['admin']);
$router->get('/admin/students/create', [Admin\StudentManageController::class, 'create'], ['admin']);
$router->post('/admin/students', [Admin\StudentManageController::class, 'store'], ['admin']);
$router->get('/admin/students/{id}/edit', [Admin\StudentManageController::class, 'edit'], ['admin']);
$router->post('/admin/students/{id}', [Admin\StudentManageController::class, 'update'], ['admin']);
$router->post('/admin/students/{id}/delete', [Admin\StudentManageController::class, 'destroy'], ['admin']);

$router->get('/admin/results', [Admin\ResultController::class, 'index'], ['admin']);
$router->get('/admin/results/create', [Admin\ResultController::class, 'create'], ['admin']);
$router->post('/admin/results', [Admin\ResultController::class, 'store'], ['admin']);
$router->get('/admin/results/{id}/edit', [Admin\ResultController::class, 'edit'], ['admin']);
$router->post('/admin/results/{id}', [Admin\ResultController::class, 'update'], ['admin']);
$router->post('/admin/results/{id}/delete', [Admin\ResultController::class, 'destroy'], ['admin']);

$router->get('/admin/live-classes', [Admin\LiveClassController::class, 'index'], ['admin']);
$router->get('/admin/live-classes/create', [Admin\LiveClassController::class, 'create'], ['admin']);
$router->post('/admin/live-classes', [Admin\LiveClassController::class, 'store'], ['admin']);
$router->post('/admin/live-classes/{id}/start', [Admin\LiveClassController::class, 'start'], ['admin']);
$router->post('/admin/live-classes/{id}/end', [Admin\LiveClassController::class, 'end'], ['admin']);
$router->get('/admin/live-classes/{id}/room', [Admin\LiveClassController::class, 'room'], ['admin']);
$router->get('/admin/live-classes/{id}/attendance', [Admin\LiveClassController::class, 'attendance'], ['admin']);
$router->get('/admin/live-now', [Admin\LiveNowController::class, 'index'], ['admin']);

$router->get('/admin/settings', [Admin\SettingsController::class, 'index'], ['admin']);
$router->post('/admin/settings', [Admin\SettingsController::class, 'update'], ['admin']);

$router->get('/student/login', [Student\AuthController::class, 'showLogin'], ['guest-student']);
$router->post('/student/login', [Student\AuthController::class, 'login'], ['guest-student']);
$router->post('/student/logout', [Student\AuthController::class, 'logout'], ['student']);
$router->get('/student', [Student\DashboardController::class, 'index'], ['student']);
$router->get('/student/course', [Student\CourseController::class, 'index'], ['student']);
$router->get('/student/profile', [Student\ProfileController::class, 'index'], ['student']);
$router->post('/student/profile', [Student\ProfileController::class, 'update'], ['student']);
$router->get('/student/results', [Student\ResultController::class, 'index'], ['student']);
$router->get('/student/join-class', [Student\LiveClassController::class, 'index'], ['student']);
$router->get('/api/student/live', [Student\LiveClassController::class, 'status'], ['student']);
$router->get('/student/join-class/{id}', [Student\LiveClassController::class, 'room'], ['student']);

$router->post('/api/live-class/{id}/join', [\App\Controllers\LiveClassApiController::class, 'join'], ['panel']);
$router->get('/api/live-class/{id}/state', [\App\Controllers\LiveClassApiController::class, 'state'], ['panel']);
$router->post('/api/live-class/{id}/push', [\App\Controllers\LiveClassApiController::class, 'push'], ['panel']);
$router->get('/api/live-class/{id}/watch/frame', [\App\Controllers\LiveClassApiController::class, 'watchFrame'], ['panel']);
$router->get('/api/live-class/{id}/watch/audio', [\App\Controllers\LiveClassApiController::class, 'watchAudio'], ['panel']);
$router->post('/api/live-class/{id}/signal', [\App\Controllers\LiveClassApiController::class, 'signal'], ['panel']);
$router->post('/api/live-class/{id}/chat', [\App\Controllers\LiveClassApiController::class, 'chat'], ['panel']);
$router->post('/api/live-class/{id}/media', [\App\Controllers\LiveClassApiController::class, 'media'], ['panel']);
$router->post('/api/live-class/{id}/hand', [\App\Controllers\LiveClassApiController::class, 'hand'], ['panel']);
$router->post('/api/live-class/{id}/leave', [\App\Controllers\LiveClassApiController::class, 'leave'], ['panel']);
$router->post('/api/live-class/{id}/kick', [\App\Controllers\LiveClassApiController::class, 'kick'], ['panel']);
$router->post('/api/live-class/{id}/mute-all', [\App\Controllers\LiveClassApiController::class, 'muteAll'], ['panel']);
$router->post('/api/live-class/{id}/presenter', [\App\Controllers\LiveClassApiController::class, 'presenter'], ['panel']);

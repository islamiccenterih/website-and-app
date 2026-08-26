<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Core\Html;
use App\Models\Course;
use App\Models\CourseEnquiry;

final class CourseController extends Controller
{
    public function index(): void
    {
        $this->view('public/courses', [
            'pageTitle' => page_copy('courses', 'title', 'Courses') . ' — ' . site_name(),
            'metaDescription' => page_copy('courses', 'lead', 'Online and on-site courses offered by the Islamic Center.'),
            'courses' => Course::published(),
        ]);
    }

    public function show(string $slug): void
    {
        $course = Course::bySlug($slug);
        if (!$course) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }

        $this->renderDetail($course);
    }

    public function enquire(string $slug): void
    {
        $course = Course::bySlug($slug);
        if (!$course) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }

        $this->requireCsrf();

        $back = '/courses/' . $course['slug'] . '#apply';

        if (!empty($_POST['website'])) {
            flash('success', 'Thank you. Your enquiry has been received.');
            redirect($back);
        }

        $result = $this->validate([
            'name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'required|phone|max:40',
            'whatsapp' => 'required|phone|max:40',
            'address' => 'required|min:8|max:400',
        ], $_POST);

        if (!$result['ok']) {
            $_SESSION['_old'] = $result['data'];
            $_SESSION['_errors'] = $result['errors'];
            flash('error', 'Please correct the highlighted fields.');
            redirect($back);
        }

        if (CourseEnquiry::recentCountForIp(request_ip()) >= 3) {
            flash('error', 'Please wait a few minutes before sending another enquiry.');
            redirect($back);
        }

        CourseEnquiry::create([
            'course_id' => (int) $course['id'],
            'course_title' => (string) $course['title'],
            'name' => $result['data']['name'],
            'email' => $result['data']['email'],
            'phone' => $result['data']['phone'],
            'whatsapp' => $result['data']['whatsapp'],
            'address' => $result['data']['address'],
            'ip_address' => request_ip(),
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
        flash('success', 'Thank you. The administration has received your enquiry for ' . $course['title'] . ' and will contact you.');
        redirect($back);
    }

    /**
     * @param array<string, mixed> $course
     */
    private function renderDetail(array $course): void
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        $this->view('public/course-detail', [
            'pageTitle' => $course['title'] . ' — ' . site_name(),
            'metaDescription' => excerpt($course['short_description'], 155),
            'ogImage' => upload_url($course['main_image']),
            'course' => $course,
            'images' => Course::images((int) $course['id']),
            'bodyHtml' => Html::clean($course['full_description'] ?? ''),
            'errors' => $errors,
            'old' => $old,
        ]);
    }
}

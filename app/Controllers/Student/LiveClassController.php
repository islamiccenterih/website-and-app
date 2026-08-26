<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Services\LiveClassService;

final class LiveClassController extends Controller
{
    public function status(): void
    {
        $id = (int) auth_user()['id'];
        $live = (new LiveClassService())->liveForStudent($id);
        json_response([
            'ok' => true,
            'live' => array_map(static function (array $row): array {
                return [
                    'id' => (int) $row['id'],
                    'title' => $row['title'],
                    'course_title' => $row['course_title'] ?? '',
                ];
            }, $live),
        ]);
    }

    public function index(): void
    {
        $id = (int) auth_user()['id'];
        $svc = new LiveClassService();
        $courses = $svc->enrolledCourses($id);
        $live = $svc->liveForStudent($id);
        $this->view('student/join-class', [
            'pageTitle' => 'Join class',
            'courses' => $courses,
            'live' => $live,
            'scheduled' => $svc->scheduledForStudent($id),
            'attendance' => $svc->attendanceForStudent($id, 8),
        ], 'dashboard');
    }

    public function room(string $id): void
    {
        $svc = new LiveClassService();
        $class = $svc->find((int) $id);
        $userId = (int) auth_user()['id'];
        if (!$class || !$svc->canEnter($class, 'student', $userId)) {
            flash('error', 'You can only join a live class for a course you are enrolled in.');
            redirect('/student/join-class');
        }
        if (($class['status'] ?? '') !== 'live') {
            flash('error', 'That class is not live yet.');
            redirect('/student/join-class');
        }
        $student = $this->db()->fetch('SELECT avatar FROM students WHERE id = ?', [$userId]);
        $this->view('live/classroom', [
            'pageTitle' => $class['title'] . ' — Join class',
            'class' => $class,
            'role' => 'student',
            'leaveUrl' => url('/student/join-class'),
            'avatarUrl' => !empty($student['avatar']) ? upload_url($student['avatar']) : '',
        ], '');
    }
}

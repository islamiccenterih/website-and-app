<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\StudentEnrollment;
use App\Services\IslamicCalendarService;
use App\Services\LiveClassService;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $id = (int) auth_user()['id'];
        $svc = new LiveClassService();
        $student = $this->db()->fetch('SELECT * FROM students WHERE id = ?', [$id]);
        if ($student) {
            $student = StudentEnrollment::hydrate([$student])[0];
        }
        $results = $this->db()->fetchAll(
            "SELECT r.*, c.title AS course_title FROM results r LEFT JOIN courses c ON c.id = r.course_id
             WHERE r.student_id = ? AND r.status = 'published' ORDER BY r.issued_at DESC, r.id DESC LIMIT 5",
            [$id]
        );
        $events = (new IslamicCalendarService())->upcoming(6);
        $this->view('student/dashboard', [
            'pageTitle' => 'Student dashboard',
            'student' => $student,
            'courses' => $student['courses'] ?? [],
            'results' => $results,
            'live' => $svc->liveForStudent($id),
            'scheduled' => $svc->scheduledForStudent($id),
            'attendance' => $svc->attendanceForStudent($id, 6),
            'events' => $events,
        ], 'dashboard');
    }
}

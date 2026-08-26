<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Services\LiveClassService;

final class CourseController extends Controller
{
    public function index(): void
    {
        $id = (int) auth_user()['id'];
        $svc = new LiveClassService();
        $this->view('student/course', [
            'pageTitle' => 'My courses',
            'courses' => $svc->enrolledCourses($id),
            'live' => $svc->liveForStudent($id),
            'scheduled' => $svc->scheduledForStudent($id),
        ], 'dashboard');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Controller;

final class ResultController extends Controller
{
    public function index(): void
    {
        $id = (int) auth_user()['id'];
        $results = $this->db()->fetchAll(
            "SELECT r.*, c.title AS course_title FROM results r
             LEFT JOIN courses c ON c.id = r.course_id
             WHERE r.student_id = ? AND r.status = 'published'
             ORDER BY r.issued_at DESC, r.id DESC",
            [$id]
        );
        $this->view('student/results', [
            'pageTitle' => 'My results',
            'results' => $results,
        ], 'dashboard');
    }
}

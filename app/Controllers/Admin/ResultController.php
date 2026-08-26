<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class ResultController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/results/index', [
            'pageTitle' => 'Results — Admin',
            'results' => $this->db()->fetchAll(
                'SELECT r.*, s.name AS student_name, s.email AS student_email, c.title AS course_title
                 FROM results r
                 INNER JOIN students s ON s.id = r.student_id
                 LEFT JOIN courses c ON c.id = r.course_id
                 ORDER BY r.id DESC'
            ),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $row = $this->db()->fetch('SELECT * FROM results WHERE id = ?', [(int) $id]);
        if (!$row) {
            redirect('/admin/results');
        }
        $this->form($row);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $this->db()->delete('results', 'id = ?', [(int) $id]);
        flash('success', 'Result deleted.');
        redirect('/admin/results');
    }

    private function form(?array $result): void
    {
        $students = \App\Models\StudentEnrollment::hydrate(
            $this->db()->fetchAll('SELECT id, name, email FROM students ORDER BY name')
        );
        $enrollments = [];
        foreach ($students as $student) {
            $list = [];
            foreach ($student['courses'] ?? [] as $course) {
                $list[] = [
                    'id' => (int) $course['id'],
                    'title' => ftc((string) ($course['title'] ?? '')),
                ];
            }
            $enrollments[(int) $student['id']] = $list;
        }
        $this->screen('admin/results/form', [
            'pageTitle' => ($result ? 'Edit' : 'Create') . ' result — Admin',
            'result' => $result,
            'students' => $students,
            'courses' => $this->db()->fetchAll('SELECT id, title FROM courses ORDER BY title'),
            'enrollments' => $enrollments,
        ]);
    }

    private function save(?int $id): void
    {
        $this->requireCsrf();
        $result = $this->validate([
            'student_id' => 'required',
            'title' => 'required|max:180',
            'status' => 'required|in:draft,published',
            'term' => 'max:80',
            'score' => 'max:40',
            'grade' => 'max:40',
        ], $_POST);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        if (!$result['ok'] || !$this->db()->fetch('SELECT id FROM students WHERE id = ?', [$studentId])) {
            flash('error', 'Please assign the result to a valid student.');
            redirect($id ? '/admin/results/' . $id . '/edit' : '/admin/results/create');
        }
        $courseId = ($_POST['course_id'] ?? '') !== '' ? (int) $_POST['course_id'] : null;
        if ($courseId && !\App\Models\StudentEnrollment::isEnrolled($studentId, $courseId)) {
            flash('error', 'Pick a course this student is enrolled in, or leave course empty.');
            redirect($id ? '/admin/results/' . $id . '/edit' : '/admin/results/create');
        }
        $payload = [
            'student_id' => $studentId,
            'course_id' => $courseId,
            'title' => $result['data']['title'],
            'term' => $result['data']['term'] ?: null,
            'score' => $result['data']['score'] ?: null,
            'grade' => $result['data']['grade'] ?: null,
            'remarks' => trim((string) ($_POST['remarks'] ?? '')) ?: null,
            'status' => $result['data']['status'],
            'issued_at' => ($_POST['issued_at'] ?? '') !== '' ? $_POST['issued_at'] : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id) {
            $this->db()->update('results', $payload, 'id = ?', [$id]);
            flash('success', 'Result updated. The assigned student can see it only if it is published.');
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db()->insert('results', $payload);
            flash('success', 'Result created.');
        }
        redirect('/admin/results');
    }
}

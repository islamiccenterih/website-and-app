<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;

final class StudentManageController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/students/index', [
            'pageTitle' => 'Students — Admin',
            'students' => \App\Models\StudentEnrollment::hydrate(
                $this->db()->fetchAll('SELECT * FROM students ORDER BY id DESC')
            ),
        ]);
    }

    public function create(): void
    {
        $this->screen('admin/students/form', [
            'pageTitle' => 'Add student — Admin',
            'student' => null,
            'enrolledIds' => [],
            'courses' => $this->db()->fetchAll('SELECT id, title FROM courses ORDER BY title'),
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $student = $this->db()->fetch('SELECT * FROM students WHERE id = ?', [(int) $id]);
        if (!$student) {
            redirect('/admin/students');
        }
        $this->screen('admin/students/form', [
            'pageTitle' => 'Edit student — Admin',
            'student' => $student,
            'enrolledIds' => \App\Models\StudentEnrollment::ids((int) $id),
            'courses' => $this->db()->fetchAll('SELECT id, title FROM courses ORDER BY title'),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM students WHERE id = ?', [(int) $id]);
        if ($row) {
            Uploader::delete($row['avatar']);
            $this->db()->delete('students', 'id = ?', [(int) $id]);
        }
        flash('success', 'Student deleted.');
        redirect('/admin/students');
    }

    private function save(?int $id): void
    {
        $this->requireCsrf();
        $result = $this->validate([
            'name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'phone|max:40',
            'enrollment_no' => 'max:60',
            'status' => 'required|in:active,disabled',
        ], $_POST);
        if (!$result['ok']) {
            flash('error', 'Please correct the student form.');
            redirect($id ? '/admin/students/' . $id . '/edit' : '/admin/students/create');
        }

        $email = mb_strtolower($result['data']['email']);
        $dupSql = 'SELECT id FROM students WHERE email = ?';
        $dupParams = [$email];
        if ($id) {
            $dupSql .= ' AND id != ?';
            $dupParams[] = $id;
        }
        if ($this->db()->fetch($dupSql, $dupParams)) {
            flash('error', 'That email is already used by another student.');
            redirect($id ? '/admin/students/' . $id . '/edit' : '/admin/students/create');
        }

        $existing = $id ? $this->db()->fetch('SELECT * FROM students WHERE id = ?', [$id]) : null;
        $avatar = $this->storeImage('avatar', 'students', $existing['avatar'] ?? null);

        $payload = [
            'name' => $result['data']['name'],
            'email' => $email,
            'phone' => $result['data']['phone'] ?: null,
            'enrollment_no' => $result['data']['enrollment_no'] ?: null,
            'course_id' => null,
            'avatar' => $avatar,
            'status' => $result['data']['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $courseIds = $_POST['course_ids'] ?? [];
        if (!is_array($courseIds)) {
            $courseIds = [];
        }

        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '') {
            if (strlen($password) < 8) {
                flash('error', 'Password must be at least 8 characters.');
                redirect($id ? '/admin/students/' . $id . '/edit' : '/admin/students/create');
            }
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        } elseif (!$id) {
            flash('error', 'A password is required for new students.');
            redirect('/admin/students/create');
        }

        if ($id) {
            $this->db()->update('students', $payload, 'id = ?', [$id]);
            if ($password !== '' || ($payload['status'] ?? '') === 'disabled') {
                \App\Core\StudentRemember::forgetAll($id);
            }
            \App\Models\StudentEnrollment::sync($id, $courseIds);
            flash('success', 'Student updated.');
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $newId = $this->db()->insert('students', $payload);
            \App\Models\StudentEnrollment::sync($newId, $courseIds);
            flash('success', 'Student created.');
        }
        redirect('/admin/students');
    }
}

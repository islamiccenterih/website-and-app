<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Controller;
use App\Core\Uploader;

final class ProfileController extends Controller
{
    public function index(): void
    {
        $id = (int) auth_user()['id'];
        $student = $this->db()->fetch('SELECT * FROM students WHERE id = ?', [$id]);
        $this->view('student/profile', [
            'pageTitle' => 'My profile',
            'student' => $student,
            'courses' => \App\Models\StudentEnrollment::courses($id),
        ], 'dashboard');
    }

    public function update(): void
    {
        $this->requireCsrf();
        $id = (int) auth_user()['id'];
        $student = $this->db()->fetch('SELECT * FROM students WHERE id = ?', [$id]);
        $result = $this->validate([
            'name' => 'required|max:120',
            'phone' => 'phone|max:40',
        ], $_POST);
        if (!$result['ok']) {
            flash('error', 'Please correct your profile details.');
            redirect('/student/profile');
        }

        $avatar = $student['avatar'] ?? null;
        if (!empty($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $new = Uploader::store($_FILES['avatar'], 'students');
                if ($new) {
                    if ($avatar && !str_starts_with($avatar, 'assets/')) {
                        Uploader::delete($avatar);
                    }
                    $avatar = $new;
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('/student/profile');
            }
        }

        $payload = [
            'name' => $result['data']['name'],
            'phone' => $result['data']['phone'] ?: null,
            'avatar' => $avatar,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '') {
            if (strlen($password) < 8) {
                flash('error', 'Password must be at least 8 characters.');
                redirect('/student/profile');
            }
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $this->db()->update('students', $payload, 'id = ?', [$id]);
        $fresh = $this->db()->fetch('SELECT * FROM students WHERE id = ?', [$id]);
        if ($fresh) {
            $_SESSION['auth'] = array_merge($_SESSION['auth'] ?? [], \App\Core\Auth::sessionPayload('student', $fresh));
        }
        if ($password !== '') {
            \App\Core\StudentRemember::forgetAll($id);
            \App\Core\StudentRemember::issue($id);
        }
        flash('success', 'Profile saved. Your name, photo, and details stay on this account until you or the administration change them.');
        redirect('/student/profile');
    }
}

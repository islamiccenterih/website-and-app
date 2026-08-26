<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Auth;
use App\Core\Controller;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('student/login', [
            'pageTitle' => 'Student login — ' . site_name(),
        ], 'auth');
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (Auth::isLocked($email)) {
            flash('error', 'Too many attempts. Please wait before trying again.');
            redirect('/student/login');
        }
        if (!Auth::attempt('student', $email, $password)) {
            flash('error', 'Those credentials were not accepted.');
            redirect('/student/login');
        }
        redirect('/student');
    }

    public function logout(): void
    {
        $this->requireCsrf();
        Auth::logout();
        redirect('/student/login');
    }
}

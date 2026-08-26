<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('admin/login', [
            'pageTitle' => 'Admin login — ' . site_name(),
        ], 'auth');
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (Auth::isLocked($email)) {
            flash('error', 'Too many attempts. Please wait before trying again.');
            redirect('/admin/login');
        }

        if (!Auth::attempt('admin', $email, $password)) {
            flash('error', 'Those credentials were not accepted.');
            redirect('/admin/login');
        }

        redirect('/admin');
    }

    public function logout(): void
    {
        $this->requireCsrf();
        Auth::logout();
        redirect('/admin/login');
    }
}

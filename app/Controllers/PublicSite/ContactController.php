<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;

final class ContactController extends Controller
{
    public function index(): void
    {
        $this->renderForm();
    }

    public function submit(): void
    {
        $this->requireCsrf();

        if (!empty($_POST['website'])) {
            flash('success', 'Thank you. Your message has been received.');
            redirect('/contact-us');
        }

        $result = $this->validate([
            'name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'phone|max:40',
            'message' => 'required|min:10|max:3000',
        ], $_POST);

        if (!$result['ok']) {
            $_SESSION['_old'] = $result['data'];
            $_SESSION['_errors'] = $result['errors'];
            flash('error', 'Please correct the highlighted fields.');
            redirect('/contact-us');
        }

        $recent = $this->db()->fetchColumn(
            'SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)',
            [request_ip()]
        );
        if ((int) $recent >= 3) {
            flash('error', 'Please wait a few minutes before sending another message.');
            redirect('/contact-us');
        }

        $this->db()->insert('contact_messages', [
            'name' => $result['data']['name'],
            'email' => $result['data']['email'],
            'phone' => $result['data']['phone'] ?: null,
            'message' => $result['data']['message'],
            'ip_address' => request_ip(),
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
        flash('success', 'Thank you. Your message has been received and will be reviewed by the administration.');
        redirect('/contact-us');
    }

    private function renderForm(): void
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        $this->view('public/contact', [
            'pageTitle' => 'Contact Us — ' . site_name(),
            'metaDescription' => 'Contact the Islamic Center by message, phone, or email.',
            'errors' => $errors,
            'old' => $old,
        ]);
    }
}

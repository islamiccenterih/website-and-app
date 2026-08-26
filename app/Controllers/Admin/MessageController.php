<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class MessageController extends BaseController
{
    public function index(): void
    {
        $this->db()->execute("UPDATE contact_messages SET status = 'read' WHERE status = 'new'");
        $this->screen('admin/messages/index', [
            'pageTitle' => 'Messages — Admin',
            'messages' => $this->db()->fetchAll('SELECT * FROM contact_messages ORDER BY created_at DESC'),
        ]);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $this->db()->delete('contact_messages', 'id = ?', [(int) $id]);
        flash('success', 'Message deleted.');
        redirect('/admin/messages');
    }
}

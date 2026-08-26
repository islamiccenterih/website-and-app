<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminAccess;

final class MemberController extends BaseController
{
    public function index(): void
    {
        $this->requireOwner();
        $rows = $this->db()->fetchAll(
            'SELECT id, name, job_title, email, status, panel_role, permissions, last_login_at
             FROM admins
             ORDER BY panel_role ASC, name ASC'
        );
        foreach ($rows as &$row) {
            $row['access_keys'] = AdminAccess::fromJson($row['permissions'] ?? null);
            $row['access_label'] = ($row['panel_role'] ?? '') === 'owner'
                ? 'Full panel'
                : AdminAccess::labelsFor($row['access_keys']);
        }
        unset($row);

        $this->screen('admin/members/index', [
            'pageTitle' => 'Panel members — Admin',
            'members' => $rows,
        ]);
    }

    public function create(): void
    {
        $this->requireOwner();
        $this->screen('admin/members/form', [
            'pageTitle' => 'Add panel member — Admin',
            'member' => null,
            'modules' => AdminAccess::modules(),
            'access' => [],
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $this->requireOwner();
        $member = $this->findMember((int) $id);
        $this->screen('admin/members/form', [
            'pageTitle' => 'Edit panel member — Admin',
            'member' => $member,
            'modules' => AdminAccess::modules(),
            'access' => AdminAccess::fromJson($member['permissions'] ?? null),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireOwner();
        $this->requireCsrf();
        $member = $this->findMember((int) $id);
        $this->db()->delete('admins', 'id = ?', [(int) $member['id']]);
        flash('success', 'Panel member removed. They can no longer sign in.');
        redirect('/admin/members');
    }

    private function save(?int $id): void
    {
        $this->requireOwner();
        $this->requireCsrf();
        $back = $id ? '/admin/members/' . $id . '/edit' : '/admin/members/create';

        $result = $this->validate([
            'name' => 'required|max:120',
            'job_title' => 'max:80',
            'email' => 'required|email|max:190',
            'status' => 'required|in:active,disabled',
        ], $_POST);
        if (!$result['ok']) {
            flash('error', 'Please correct the panel member form.');
            redirect($back);
        }

        if ($id) {
            $this->findMember($id);
        }

        $email = mb_strtolower($result['data']['email']);
        $dupSql = 'SELECT id FROM admins WHERE email = ?';
        $dupParams = [$email];
        if ($id) {
            $dupSql .= ' AND id != ?';
            $dupParams[] = $id;
        }
        if ($this->db()->fetch($dupSql, $dupParams)) {
            flash('error', 'That email is already used by another admin account.');
            redirect($back);
        }

        $access = AdminAccess::sanitizeKeys(is_array($_POST['access'] ?? null) ? $_POST['access'] : []);
        $jobTitle = trim((string) $result['data']['job_title']);
        $payload = [
            'name' => $result['data']['name'],
            'job_title' => $jobTitle !== '' ? $jobTitle : 'Manager',
            'email' => $email,
            'status' => $result['data']['status'],
            'panel_role' => 'member',
            'permissions' => json_encode($access, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '') {
            if (strlen($password) < 8) {
                flash('error', 'Password must be at least 8 characters.');
                redirect($back);
            }
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        } elseif (!$id) {
            flash('error', 'Set a password to give this person. They will use it with their email to sign in.');
            redirect('/admin/members/create');
        }

        if ($id) {
            $this->db()->update('admins', $payload, 'id = ?', [$id]);
            flash('success', 'Panel member updated. Access changes apply the next time they open a page.');
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db()->insert('admins', $payload);
            flash('success', 'Panel member created. Give them this email and password so they can sign in at Admin login.');
        }
        redirect('/admin/members');
    }

    /**
     * @return array<string, mixed>
     */
    private function findMember(int $id): array
    {
        $row = $this->db()->fetch('SELECT * FROM admins WHERE id = ?', [$id]);
        if (!$row) {
            flash('error', 'That panel member was not found.');
            redirect('/admin/members');
        }
        if (($row['panel_role'] ?? 'owner') !== 'member') {
            flash('error', 'The owner account cannot be changed from Panel members.');
            redirect('/admin/members');
        }
        return $row;
    }

    private function requireOwner(): void
    {
        if (!AdminAccess::isOwner()) {
            AdminAccess::deny();
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\LiveClassService;

final class LiveClassController extends BaseController
{
    public function index(): void
    {
        $live = $this->db()->fetchAll(
            "SELECT lc.*, c.title AS course_title,
                    (SELECT COUNT(*) FROM live_class_peers p WHERE p.class_id = lc.id) AS in_room
             FROM live_classes lc
             INNER JOIN courses c ON c.id = lc.course_id
             WHERE lc.status = 'live'
             ORDER BY lc.started_at DESC"
        );
        $recent = $this->db()->fetchAll(
            "SELECT lc.*, c.title AS course_title
             FROM live_classes lc
             INNER JOIN courses c ON c.id = lc.course_id
             WHERE lc.status != 'live'
             ORDER BY lc.id DESC
             LIMIT 20"
        );
        $this->screen('admin/live-classes/index', [
            'pageTitle' => 'Live classes — Admin',
            'live' => $live,
            'recent' => $recent,
        ]);
    }

    public function create(): void
    {
        $this->screen('admin/live-classes/create', [
            'pageTitle' => 'Create live class — Admin',
            'courses' => $this->db()->fetchAll('SELECT id, title, status FROM courses ORDER BY title'),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $course = $courseId ? $this->db()->fetch('SELECT * FROM courses WHERE id = ?', [$courseId]) : null;
        if (!$course) {
            flash('error', 'Choose the course this class is for.');
            redirect('/admin/live-classes/create');
        }

        $existing = (new LiveClassService())->liveForCourse($courseId);
        if ($existing) {
            flash('error', $course['title'] . ' already has a live class. Join that room or end it first.');
            redirect('/admin/live-classes/' . $existing['id'] . '/room');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            $title = $course['title'] . ' live class';
        }
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $startNow = isset($_POST['start_now']);
        $now = date('Y-m-d H:i:s');
        $id = $this->db()->insert('live_classes', [
            'course_id' => $courseId,
            'admin_id' => (int) auth_user()['id'],
            'title' => mb_substr($title, 0, 180),
            'notes' => $notes !== '' ? mb_substr($notes, 0, 400) : null,
            'status' => $startNow ? 'live' : 'scheduled',
            'started_at' => $startNow ? $now : null,
            'ended_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($startNow) {
            flash('success', 'Class is live. Enrolled students can join now.');
            redirect('/admin/live-classes/' . $id . '/room');
        }
        flash('success', 'Class saved. Start it when you are ready to go live.');
        redirect('/admin/live-classes');
    }

    public function start(string $id): void
    {
        $this->requireCsrf();
        $class = $this->db()->fetch('SELECT * FROM live_classes WHERE id = ?', [(int) $id]);
        if (!$class) {
            redirect('/admin/live-classes');
        }
        $live = (new LiveClassService())->liveForCourse((int) $class['course_id']);
        if ($live && (int) $live['id'] !== (int) $class['id']) {
            flash('error', 'That course already has another live class.');
            redirect('/admin/live-classes');
        }
        $now = date('Y-m-d H:i:s');
        $this->db()->update('live_classes', [
            'status' => 'live',
            'started_at' => $class['started_at'] ?: $now,
            'updated_at' => $now,
        ], 'id = ?', [(int) $id]);
        flash('success', 'Students enrolled in this course can now join.');
        redirect('/admin/live-classes/' . (int) $id . '/room');
    }

    public function end(string $id): void
    {
        $this->requireCsrf();
        $svc = new LiveClassService();
        $class = $svc->find((int) $id);
        if ($class && ($class['status'] ?? '') !== 'ended') {
            $svc->endClass((int) $id);
            flash('success', 'Class ended.');
        }
        redirect('/admin/live-classes');
    }

    public function room(string $id): void
    {
        $svc = new LiveClassService();
        $class = $svc->find((int) $id);
        if (!$class) {
            flash('error', 'That class was not found.');
            redirect('/admin/live-classes');
        }
        if (($class['status'] ?? '') === 'ended') {
            flash('error', 'This class has already ended.');
            redirect('/admin/live-classes');
        }
        if (($class['status'] ?? '') !== 'live') {
            $now = date('Y-m-d H:i:s');
            $this->db()->update('live_classes', [
                'status' => 'live',
                'started_at' => $class['started_at'] ?: $now,
                'updated_at' => $now,
            ], 'id = ?', [(int) $id]);
            $class = $svc->find((int) $id) ?: $class;
        }
        $this->view('live/classroom', [
            'pageTitle' => $class['title'] . ' — Live class',
            'class' => $class,
            'role' => 'host',
            'leaveUrl' => url('/admin/live-classes'),
            'avatarUrl' => '',
        ], '');
    }

    public function attendance(string $id): void
    {
        $class = (new LiveClassService())->find((int) $id);
        if (!$class) {
            redirect('/admin/live-classes');
        }
        $rows = $this->db()->fetchAll(
            'SELECT a.*, s.name, s.email, s.enrollment_no
             FROM live_class_attendance a
             INNER JOIN students s ON s.id = a.student_id
             WHERE a.class_id = ?
             ORDER BY a.joined_at ASC',
            [(int) $id]
        );
        $this->screen('admin/live-classes/attendance', [
            'pageTitle' => 'Attendance — ' . $class['title'],
            'class' => $class,
            'rows' => $rows,
        ]);
    }
}

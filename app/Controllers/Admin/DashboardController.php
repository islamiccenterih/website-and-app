<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();
        $stats = [];
        $actions = [];

        if (admin_can('courses')) {
            $stats['Courses'] = (int) $db->fetchColumn('SELECT COUNT(*) FROM courses');
            $stats['Published courses'] = (int) $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'published'");
            $actions[] = ['href' => '/admin/courses/create', 'label' => 'Add a course', 'class' => 'btn btn-walnut'];
        }
        if (admin_can('activities')) {
            $stats['Social activities'] = (int) $db->fetchColumn('SELECT COUNT(*) FROM social_activities');
        }
        if (admin_can('gallery')) {
            $stats['Gallery images'] = (int) $db->fetchColumn('SELECT COUNT(*) FROM gallery_images');
            $actions[] = ['href' => '/admin/gallery', 'label' => 'Upload gallery images', 'class' => 'btn btn-outline'];
        }
        if (admin_can('students')) {
            $stats['Students'] = (int) $db->fetchColumn('SELECT COUNT(*) FROM students');
        }
        if (admin_can('live-classes')) {
            $stats['Live classes'] = (int) $db->fetchColumn("SELECT COUNT(*) FROM live_classes WHERE status = 'live'");
            $actions[] = ['href' => '/admin/live-classes/create', 'label' => 'Start a live class', 'class' => 'btn btn-walnut'];
        }
        if (admin_can('live-now')) {
            $on = (new \App\Services\PublicLiveService())->current();
            $stats['Public live'] = $on ? 'On' : 'Off';
            $actions[] = ['href' => '/admin/live-now', 'label' => 'Live now', 'class' => 'btn btn-gold'];
        }
        if (admin_can('messages')) {
            $stats['New messages'] = (int) $db->fetchColumn("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
        }
        if (admin_can('enquiries')) {
            $stats['New course enquiries'] = \App\Models\CourseEnquiry::countNew();
            $actions[] = ['href' => '/admin/enquiries', 'label' => 'Course enquiries', 'class' => 'btn btn-outline'];
        }
        if (admin_can('results')) {
            $stats['Published results'] = (int) $db->fetchColumn("SELECT COUNT(*) FROM results WHERE status = 'published'");
        }
        if (admin_can('calendar')) {
            $stats['Calendar months'] = (int) $db->fetchColumn('SELECT COUNT(*) FROM calendar_months');
        }
        if (admin_can('updates')) {
            $stats['Published updates'] = (int) $db->fetchColumn("SELECT COUNT(*) FROM center_updates WHERE status = 'published'");
            $actions[] = ['href' => '/admin/updates/create', 'label' => 'Write today’s update', 'class' => 'btn btn-walnut'];
        }
        if (admin_can('fatawa')) {
            $stats['Published fatawa'] = (int) $db->fetchColumn("SELECT COUNT(*) FROM fatawa WHERE status = 'published'");
            $waiting = \App\Models\Fatwa::unansweredCount();
            if ($waiting > 0) {
                $stats['Fatwa questions waiting'] = $waiting;
            }
            $actions[] = ['href' => '/admin/fatawa/create', 'label' => 'Publish today’s fatwa', 'class' => 'btn btn-walnut'];
        }
        if (admin_can('footer')) {
            $actions[] = ['href' => '/admin/footer', 'label' => 'Edit header & footer', 'class' => 'btn btn-outline'];
        }
        if (admin_can('pages') || \App\Core\SitePages::canList()) {
            array_unshift($actions, ['href' => '/admin/pages', 'label' => 'Website pages', 'class' => 'btn btn-walnut']);
        }
        if (is_panel_owner()) {
            $actions[] = ['href' => '/admin/members/create', 'label' => 'Add a panel member', 'class' => 'btn btn-gold'];
        }
        $actions[] = ['href' => '/', 'label' => 'Open public website', 'class' => 'btn btn-outline'];

        $owner = is_panel_owner();
        $title = (string) (auth_user()['job_title'] ?? '');
        $lead = $owner
            ? 'Open Pages to change a public page’s name and content. Panel members see only the sections you assign.'
            : 'You can open only the sections assigned to you. Ask the site owner if you need access to something else.';

        $this->screen('admin/dashboard', [
            'pageTitle' => 'Dashboard — Admin',
            'stats' => $stats,
            'actions' => $actions,
            'isOwner' => $owner,
            'roleLabel' => $owner ? 'Owner' : ($title !== '' ? $title : 'Panel member'),
            'lead' => $lead,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;
use App\Models\Activity;
use App\Models\ActivitySection;

final class ActivityController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/activities/index', [
            'pageTitle' => 'Social activities — Admin',
            'groups' => present_copy_tree(Activity::groupedForAdmin()),
            'sections' => present_copy_tree(ActivitySection::all('sort_order ASC, id ASC')),
        ]);
    }

    public function create(): void
    {
        $sections = ActivitySection::all('sort_order ASC, id ASC');
        if ($sections === []) {
            flash('error', 'Add a heading first, then add activities inside that heading.');
            redirect('/admin/activities');
        }
        $this->screen('admin/activities/form', [
            'pageTitle' => 'Add an activity — Admin',
            'activity' => null,
            'images' => [],
            'sections' => present_copy_tree($sections),
            'sectionCounts' => $this->sectionCounts(),
            'prefillSection' => (int) ($_GET['section'] ?? 0),
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $activity = Activity::find((int) $id);
        if (!$activity) {
            flash('error', 'Activity not found.');
            redirect('/admin/activities');
        }
        $this->screen('admin/activities/form', [
            'pageTitle' => 'Edit activity — Admin',
            'activity' => present_copy_tree($activity),
            'images' => Activity::images((int) $id),
            'sections' => present_copy_tree(ActivitySection::all('sort_order ASC, id ASC')),
            'sectionCounts' => $this->sectionCounts(),
            'prefillSection' => (int) ($activity['section_id'] ?? 0),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $row = Activity::find((int) $id);
        if ($row) {
            foreach (Activity::images((int) $id) as $img) {
                Uploader::delete($img['image_path']);
            }
            Uploader::delete($row['main_image'] ?? null);
            Activity::deleteById((int) $id);
        }
        flash('success', 'Activity deleted.');
        redirect('/admin/activities');
    }

    public function deleteImage(string $id, string $imageId): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM social_activity_images WHERE id = ? AND activity_id = ?', [(int) $imageId, (int) $id]);
        if ($row) {
            Uploader::delete($row['image_path']);
            $this->db()->delete('social_activity_images', 'id = ?', [(int) $imageId]);
        }
        flash('success', 'Image removed.');
        redirect('/admin/activities/' . (int) $id . '/edit');
    }

    public function storeSection(): void
    {
        $this->requireCsrf();
        $result = $this->validate([
            'name' => 'required|max:160',
            'slug' => 'max:160',
            'kicker' => 'max:80',
            'lead' => 'max:400',
            'status' => 'required|in:draft,published',
        ], $_POST);
        if (!$result['ok']) {
            flash('error', 'Please complete the heading name and keep the fields within the limits.');
            redirect('/admin/activities');
        }
        $slug = ActivitySection::uniqueSlug(
            $result['data']['slug'] !== '' ? slugify($result['data']['slug']) : slugify($result['data']['name'])
        );
        ActivitySection::create([
            'name' => faith_terms_store($result['data']['name']),
            'slug' => $slug,
            'kicker' => faith_terms_store($result['data']['kicker'] ?: '') ?: null,
            'lead' => faith_terms_store($result['data']['lead'] ?: '') ?: null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => $result['data']['status'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Heading added. Use Add activity here on that card to put programmes under it.');
        redirect('/admin/activities');
    }

    public function updateSection(string $id): void
    {
        $this->requireCsrf();
        $section = ActivitySection::find((int) $id);
        if (!$section) {
            flash('error', 'Heading not found.');
            redirect('/admin/activities');
        }
        $result = $this->validate([
            'name' => 'required|max:160',
            'slug' => 'max:160',
            'kicker' => 'max:80',
            'lead' => 'max:400',
            'status' => 'required|in:draft,published',
        ], $_POST);
        if (!$result['ok']) {
            flash('error', 'Please complete the heading name and keep the fields within the limits.');
            redirect('/admin/activities');
        }
        $slug = ActivitySection::uniqueSlug(
            $result['data']['slug'] !== '' ? slugify($result['data']['slug']) : slugify($result['data']['name']),
            (int) $id
        );
        ActivitySection::updateById((int) $id, [
            'name' => faith_terms_store($result['data']['name']),
            'slug' => $slug,
            'kicker' => faith_terms_store($result['data']['kicker'] ?: '') ?: null,
            'lead' => faith_terms_store($result['data']['lead'] ?: '') ?: null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'status' => $result['data']['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Heading updated.');
        redirect('/admin/activities');
    }

    public function destroySection(string $id): void
    {
        $this->requireCsrf();
        $count = (int) $this->db()->fetchColumn(
            'SELECT COUNT(*) FROM social_activities WHERE section_id = ?',
            [(int) $id]
        );
        if ($count > 0) {
            flash('error', 'Move or delete the activities in this heading first.');
            redirect('/admin/activities');
        }
        ActivitySection::deleteById((int) $id);
        flash('success', 'Heading deleted.');
        redirect('/admin/activities');
    }

    private function save(?int $id): void
    {
        $this->requireCsrf();
        $existing = $id ? Activity::find($id) : null;
        $result = $this->validate([
            'title' => 'required|max:240',
            'slug' => 'max:180',
            'section_id' => 'required',
            'short_description' => 'required|max:500',
            'full_description' => 'max:20000',
            'status' => 'required|in:draft,published',
            'event_year' => 'max:12',
        ], $_POST);
        if (!$result['ok']) {
            flash('error', 'Please correct the activity form.');
            redirect($id ? '/admin/activities/' . $id . '/edit' : '/admin/activities/create');
        }
        $sectionId = (int) $result['data']['section_id'];
        if (!ActivitySection::find($sectionId)) {
            flash('error', 'Choose a valid heading for this activity.');
            redirect($id ? '/admin/activities/' . $id . '/edit' : '/admin/activities/create');
        }
        $storedTitle = faith_terms_store($result['data']['title']);
        $slug = Activity::uniqueSlug(
            $result['data']['slug'] !== '' ? slugify($result['data']['slug']) : slugify($storedTitle),
            $id
        );
        try {
            $main = $this->storeImage('main_image', 'activities', $existing['main_image'] ?? null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect($id ? '/admin/activities/' . $id . '/edit' : '/admin/activities/create');
        }

        $payload = [
            'title' => $storedTitle,
            'slug' => $slug,
            'section_id' => $sectionId,
            'short_description' => faith_terms_store($result['data']['short_description']),
            'full_description' => faith_terms_store($result['data']['full_description']),
            'event_date' => $_POST['event_date'] !== '' ? $_POST['event_date'] : null,
            'event_year' => $result['data']['event_year'] ?: null,
            'main_image' => $main,
            'status' => $result['data']['status'],
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id) {
            Activity::updateById($id, $payload);
            $activityId = $id;
            flash('success', 'Activity updated. The public website now shows this data.');
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $activityId = Activity::create($payload);
            flash('success', 'Activity created.');
        }
        foreach ($this->storeMany('extra_images', 'activities') as $path) {
            $this->db()->insert('social_activity_images', [
                'activity_id' => $activityId,
                'image_path' => $path,
                'caption' => null,
                'sort_order' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        redirect('/admin/activities/' . $activityId . '/edit');
    }

    /** @return array<int, int> */
    private function sectionCounts(): array
    {
        $rows = $this->db()->fetchAll(
            'SELECT section_id, COUNT(*) AS c FROM social_activities GROUP BY section_id'
        );
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['section_id']] = (int) $row['c'];
        }
        return $out;
    }
}

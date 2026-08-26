<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Uploader;
use App\Models\Course;

final class CourseController extends BaseController
{
    public function index(): void
    {
        $this->screen('admin/courses/index', [
            'pageTitle' => 'Courses — Admin',
            'courses' => Course::all('sort_order ASC, id DESC'),
        ]);
    }

    public function create(): void
    {
        $this->screen('admin/courses/form', [
            'pageTitle' => 'Create course — Admin',
            'course' => null,
            'images' => [],
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(string $id): void
    {
        $course = Course::find((int) $id);
        if (!$course) {
            flash('error', 'Course not found.');
            redirect('/admin/courses');
        }
        $this->screen('admin/courses/form', [
            'pageTitle' => 'Edit course — Admin',
            'course' => present_copy_tree($course),
            'images' => Course::images((int) $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->save((int) $id);
    }

    public function destroy(string $id): void
    {
        $this->requireCsrf();
        $course = Course::find((int) $id);
        if ($course) {
            foreach (Course::images((int) $id) as $img) {
                Uploader::delete($img['image_path']);
            }
            if (!empty($course['main_image'])) {
                Uploader::delete($course['main_image']);
            }
            Course::deleteById((int) $id);
        }
        flash('success', 'Course deleted.');
        redirect('/admin/courses');
    }

    public function deleteImage(string $id, string $imageId): void
    {
        $this->requireCsrf();
        $row = $this->db()->fetch('SELECT * FROM course_images WHERE id = ? AND course_id = ?', [(int) $imageId, (int) $id]);
        if ($row) {
            Uploader::delete($row['image_path']);
            $this->db()->delete('course_images', 'id = ?', [(int) $imageId]);
        }
        flash('success', 'Image removed.');
        redirect('/admin/courses/' . (int) $id . '/edit');
    }

    private function save(?int $id): void
    {
        $this->requireCsrf();
        $existing = $id ? Course::find($id) : null;
        if ($id && !$existing) {
            flash('error', 'Course not found.');
            redirect('/admin/courses');
        }

        $result = $this->validate([
            'title' => 'required|max:240',
            'slug' => 'max:180',
            'short_description' => 'required|max:500',
            'full_description' => 'max:20000',
            'fees' => 'max:80',
            'duration' => 'max:80',
            'mode' => 'required|in:online,offline,hybrid',
            'status' => 'required|in:draft,published',
            'additional_info' => 'max:5000',
        ], $_POST);

        if (!$result['ok']) {
            flash('error', 'Please correct the course form.');
            redirect($id ? '/admin/courses/' . $id . '/edit' : '/admin/courses/create');
        }

        $storedTitle = faith_terms_store($result['data']['title']);
        $slug = $result['data']['slug'] !== '' ? slugify($result['data']['slug']) : slugify($storedTitle);
        $slug = Course::uniqueSlug($slug, $id);

        try {
            $main = $this->storeImage('main_image', 'courses', $existing['main_image'] ?? null);
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect($id ? '/admin/courses/' . $id . '/edit' : '/admin/courses/create');
        }

        $payload = [
            'title' => $storedTitle,
            'slug' => $slug,
            'short_description' => faith_terms_store($result['data']['short_description']),
            'full_description' => faith_terms_store($result['data']['full_description']),
            'fees' => $result['data']['fees'],
            'duration' => $result['data']['duration'],
            'mode' => $result['data']['mode'],
            'additional_info' => faith_terms_store($result['data']['additional_info']),
            'main_image' => $main,
            'status' => $result['data']['status'],
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            Course::updateById($id, $payload);
            $courseId = $id;
            flash('success', 'Course updated. The public website now shows this data.');
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $courseId = Course::create($payload);
            flash('success', 'Course created. Publish it to show it on the website.');
        }

        try {
            foreach ($this->storeMany('extra_images', 'courses') as $path) {
                $this->db()->insert('course_images', [
                    'course_id' => $courseId,
                    'image_path' => $path,
                    'caption' => null,
                    'sort_order' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        }

        redirect('/admin/courses/' . $courseId . '/edit');
    }
}

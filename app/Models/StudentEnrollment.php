<?php

declare(strict_types=1);

namespace App\Models;

final class StudentEnrollment
{
    /**
     * @return list<int>
     */
    public static function ids(int $studentId): array
    {
        $rows = static::db()->fetchAll(
            'SELECT course_id FROM student_courses WHERE student_id = ? ORDER BY course_id ASC',
            [$studentId]
        );
        return array_map(static fn(array $row): int => (int) $row['course_id'], $rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function courses(int $studentId): array
    {
        return static::db()->fetchAll(
            'SELECT c.*
             FROM courses c
             INNER JOIN student_courses sc ON sc.course_id = c.id
             WHERE sc.student_id = ?
             ORDER BY c.sort_order ASC, c.title ASC',
            [$studentId]
        );
    }

    /**
     * @return list<string>
     */
    public static function titles(int $studentId): array
    {
        $out = [];
        foreach (self::courses($studentId) as $course) {
            $title = trim((string) ($course['title'] ?? ''));
            if ($title !== '') {
                $out[] = $title;
            }
        }
        return $out;
    }

    public static function isEnrolled(int $studentId, int $courseId): bool
    {
        if ($studentId < 1 || $courseId < 1) {
            return false;
        }
        $row = static::db()->fetch(
            'SELECT student_id FROM student_courses WHERE student_id = ? AND course_id = ? LIMIT 1',
            [$studentId, $courseId]
        );
        return $row !== null;
    }

    /**
     * @param list<int|string> $courseIds
     * @return list<int>
     */
    public static function sync(int $studentId, array $courseIds): array
    {
        $valid = [];
        foreach ($courseIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $valid[$id] = $id;
            }
        }
        $ids = array_values($valid);
        if ($ids !== []) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $existing = static::db()->fetchAll(
                "SELECT id FROM courses WHERE id IN ($placeholders)",
                $ids
            );
            $ids = array_map(static fn(array $row): int => (int) $row['id'], $existing);
        }
        static::db()->delete('student_courses', 'student_id = ?', [$studentId]);
        $now = date('Y-m-d H:i:s');
        foreach ($ids as $courseId) {
            static::db()->insert('student_courses', [
                'student_id' => $studentId,
                'course_id' => $courseId,
                'created_at' => $now,
            ]);
        }
        $primary = $ids[0] ?? null;
        static::db()->update('students', [
            'course_id' => $primary,
            'updated_at' => $now,
        ], 'id = ?', [$studentId]);
        return $ids;
    }

    /**
     * @param list<array<string, mixed>> $students
     * @return list<array<string, mixed>>
     */
    public static function hydrate(array $students): array
    {
        if ($students === []) {
            return $students;
        }
        $ids = [];
        foreach ($students as $row) {
            $ids[] = (int) ($row['id'] ?? 0);
        }
        $ids = array_values(array_filter($ids));
        $grouped = [];
        if ($ids !== []) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $rows = static::db()->fetchAll(
                "SELECT sc.student_id, c.id, c.title, c.slug, c.mode, c.duration, c.fees
                 FROM student_courses sc
                 INNER JOIN courses c ON c.id = sc.course_id
                 WHERE sc.student_id IN ($placeholders)
                 ORDER BY c.sort_order ASC, c.title ASC",
                $ids
            );
            foreach ($rows as $row) {
                $sid = (int) $row['student_id'];
                $grouped[$sid][] = $row;
            }
        }
        foreach ($students as &$student) {
            $sid = (int) ($student['id'] ?? 0);
            $list = $grouped[$sid] ?? [];
            $student['courses'] = $list;
            $student['course_titles'] = array_values(array_filter(array_map(
                static fn(array $c): string => trim((string) ($c['title'] ?? '')),
                $list
            )));
            $student['course_title'] = $student['course_titles'][0] ?? ($student['course_title'] ?? '');
        }
        unset($student);
        return $students;
    }

    private static function db(): \App\Core\Database
    {
        return \App\Core\Database::get();
    }
}

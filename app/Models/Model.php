<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

abstract class Model
{
    protected static string $table;

    public static function db(): Database
    {
        return Database::get();
    }

    public static function find(int $id): ?array
    {
        return static::db()->fetch('SELECT * FROM `' . static::$table . '` WHERE id = ? LIMIT 1', [$id]);
    }

    public static function all(string $order = 'id DESC'): array
    {
        return static::db()->fetchAll('SELECT * FROM `' . static::$table . '` ORDER BY ' . $order);
    }

    public static function create(array $data): int
    {
        return static::db()->insert(static::$table, $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return static::db()->update(static::$table, $data, 'id = ?', [$id]);
    }

    public static function deleteById(int $id): int
    {
        return static::db()->delete(static::$table, 'id = ?', [$id]);
    }
}

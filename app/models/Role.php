<?php
declare(strict_types=1);

class Role
{
    public static function all(): array
    {
        $db = Database::getConnection();
        return $db->query('SELECT * FROM roles ORDER BY id')->fetchAll();
    }

    public static function findByCode(string $code): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM roles WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

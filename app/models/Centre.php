<?php
declare(strict_types=1);

class Centre
{
    public static function all(bool $activeOnly = false): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT * FROM centres';
        if ($activeOnly) {
            $sql .= ' WHERE actif = 1';
        }
        $sql .= ' ORDER BY nom';
        return $db->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM centres WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $code, string $nom, ?string $ville): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO centres (code, nom, ville) VALUES (?, ?, ?)');
        $stmt->execute([$code, $nom, $ville]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $code, string $nom, ?string $ville, int $actif): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE centres SET code = ?, nom = ?, ville = ?, actif = ? WHERE id = ?');
        $stmt->execute([$code, $nom, $ville, $actif, $id]);
    }
}

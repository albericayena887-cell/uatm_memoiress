<?php
declare(strict_types=1);

class Filiere
{
    public static function all(bool $activeOnly = false): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT * FROM filieres';
        if ($activeOnly) {
            $sql .= ' WHERE actif = 1';
        }
        $sql .= ' ORDER BY nom';
        return $db->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM filieres WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $code, string $nom): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO filieres (code, nom) VALUES (?, ?)');
        $stmt->execute([$code, $nom]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $code, string $nom, int $actif): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE filieres SET code = ?, nom = ?, actif = ? WHERE id = ?');
        $stmt->execute([$code, $nom, $actif, $id]);
    }
}

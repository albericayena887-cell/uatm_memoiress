<?php
declare(strict_types=1);

class Favori
{
    public static function toggle(int $memoireId, int $userId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM favoris WHERE memoire_id = ? AND user_id = ?');
        $stmt->execute([$memoireId, $userId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $db->prepare('DELETE FROM favoris WHERE id = ?')->execute([$existing['id']]);
            return false;
        }

        $db->prepare('INSERT INTO favoris (memoire_id, user_id) VALUES (?, ?)')
            ->execute([$memoireId, $userId]);
        return true;
    }

    public static function isFavorite(int $memoireId, int $userId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT 1 FROM favoris WHERE memoire_id = ? AND user_id = ?');
        $stmt->execute([$memoireId, $userId]);
        return (bool) $stmt->fetch();
    }

    public static function forUser(int $userId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT m.*, f.nom AS filiere_nom, fav.created_at AS favori_at
             FROM favoris fav
             JOIN memoires m ON m.id = fav.memoire_id
             JOIN filieres f ON f.id = m.filiere_id
             WHERE fav.user_id = ? ORDER BY fav.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

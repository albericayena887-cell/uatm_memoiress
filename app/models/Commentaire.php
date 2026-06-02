<?php
declare(strict_types=1);

class Commentaire
{
    public static function forMemoire(int $memoireId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT c.*, u.nom, u.prenom FROM commentaires c
             JOIN users u ON u.id = c.user_id
             WHERE c.memoire_id = ? ORDER BY c.created_at DESC'
        );
        $stmt->execute([$memoireId]);
        return $stmt->fetchAll();
    }

    public static function create(int $memoireId, int $userId, string $contenu): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO commentaires (memoire_id, user_id, contenu) VALUES (?, ?, ?)'
        );
        $stmt->execute([$memoireId, $userId, $contenu]);
        return (int) $db->lastInsertId();
    }
}

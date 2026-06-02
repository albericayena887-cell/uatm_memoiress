<?php
declare(strict_types=1);

class Note
{
    public static function set(int $memoireId, int $userId, int $note): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO notes (memoire_id, user_id, note) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE note = VALUES(note), updated_at = NOW()'
        );
        $stmt->execute([$memoireId, $userId, $note]);
        Memoire::updateRating($memoireId);
    }

    public static function getUserNote(int $memoireId, int $userId): ?int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT note FROM notes WHERE memoire_id = ? AND user_id = ?');
        $stmt->execute([$memoireId, $userId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['note'] : null;
    }
}

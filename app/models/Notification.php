<?php
declare(strict_types=1);

class Notification
{
    public static function create(int $userId, string $type, string $titre, string $message, ?int $memoireId = null): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO notifications (user_id, memoire_id, type, titre, message) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $memoireId, $type, $titre, $message]);
    }

    public static function forUser(int $userId, int $limit = 10): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lu = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function markRead(int $id, int $userId): void
    {
        $db = Database::getConnection();
        $db->prepare('UPDATE notifications SET lu = 1 WHERE id = ? AND user_id = ?')
            ->execute([$id, $userId]);
    }
}

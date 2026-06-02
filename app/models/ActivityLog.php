<?php
declare(strict_types=1);

class ActivityLog
{
    public static function create(?int $userId, string $action, ?string $details, ?string $ip): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $action, $details, $ip]);
    }

    public static function recent(int $limit = 20): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT al.*, u.nom, u.prenom FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

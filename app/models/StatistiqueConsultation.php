<?php
declare(strict_types=1);

class StatistiqueConsultation
{
    public static function record(int $memoireId, ?int $userId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO statistiques_consultation (memoire_id, user_id, ip_address, user_agent)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $memoireId,
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
        Memoire::incrementViews($memoireId);
    }
}

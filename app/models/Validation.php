<?php
declare(strict_types=1);

class Validation
{
    public static function create(int $memoireId, int $profId, string $action, ?string $motif = null): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO validations (memoire_id, professeur_id, action, motif) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$memoireId, $profId, $action, $motif]);
    }

    public static function history(int $memoireId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT v.*, u.nom, u.prenom FROM validations v
             JOIN users u ON u.id = v.professeur_id
             WHERE v.memoire_id = ? ORDER BY v.created_at DESC'
        );
        $stmt->execute([$memoireId]);
        return $stmt->fetchAll();
    }

    public static function pendingForProfessor(int $profId): array
    {
        return Memoire::search(['encadreur_id' => $profId, 'statut' => 'en_attente'], 'professeur');
    }
}

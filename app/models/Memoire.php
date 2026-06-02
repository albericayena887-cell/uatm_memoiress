<?php
declare(strict_types=1);

class Memoire
{
    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT m.*, f.nom AS filiere_nom, c.nom AS centre_nom,
                    u.nom AS etudiant_nom, u.prenom AS etudiant_prenom, u.email AS etudiant_email
             FROM memoires m
             JOIN filieres f ON f.id = m.filiere_id
             JOIN centres c ON c.id = m.centre_id
             JOIN users u ON u.id = m.user_id
             WHERE m.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function search(array $filters, ?string $roleCode = null): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT m.*, f.nom AS filiere_nom, c.nom AS centre_nom
                FROM memoires m
                JOIN filieres f ON f.id = m.filiere_id
                JOIN centres c ON c.id = m.centre_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['statut'])) {
            // filtre explicite
        } elseif ($roleCode === 'etudiant_consultant') {
            $sql .= " AND m.statut = 'valide'";
        } elseif ($roleCode === 'professeur' && empty($filters['encadreur_id'])) {
            $sql .= " AND m.statut = 'valide'";
        }

        if (!empty($filters['titre'])) {
            $sql .= ' AND m.titre LIKE ?';
            $params[] = '%' . $filters['titre'] . '%';
        }
        if (!empty($filters['auteur'])) {
            $sql .= ' AND m.auteur LIKE ?';
            $params[] = '%' . $filters['auteur'] . '%';
        }
        if (!empty($filters['filiere_id'])) {
            $sql .= ' AND m.filiere_id = ?';
            $params[] = (int) $filters['filiere_id'];
        }
        if (!empty($filters['centre_id'])) {
            $sql .= ' AND m.centre_id = ?';
            $params[] = (int) $filters['centre_id'];
        }
        if (!empty($filters['annee_academique'])) {
            $sql .= ' AND m.annee_academique = ?';
            $params[] = $filters['annee_academique'];
        }
        if (!empty($filters['ville'])) {
            $sql .= ' AND m.ville LIKE ?';
            $params[] = '%' . $filters['ville'] . '%';
        }
        if (!empty($filters['encadreur'])) {
            $sql .= ' AND m.encadreur_nom LIKE ?';
            $params[] = '%' . $filters['encadreur'] . '%';
        }
        if (!empty($filters['mots_cles'])) {
            $sql .= ' AND (m.mots_cles LIKE ? OR m.resume LIKE ?)';
            $term = '%' . $filters['mots_cles'] . '%';
            array_push($params, $term, $term);
        }
        if (!empty($filters['statut'])) {
            $sql .= ' AND m.statut = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= ' AND m.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }
        if (!empty($filters['encadreur_id'])) {
            $sql .= ' AND m.encadreur_id = ?';
            $params[] = (int) $filters['encadreur_id'];
        }

        $sql .= ' ORDER BY m.date_depot DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO memoires (user_id, filiere_id, centre_id, encadreur_id, encadreur_nom,
             titre, auteur, annee_academique, ville, resume, mots_cles, fichier_path, fichier_type, couverture_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id'],
            $data['filiere_id'],
            $data['centre_id'],
            $data['encadreur_id'] ?? null,
            $data['encadreur_nom'],
            $data['titre'],
            $data['auteur'],
            $data['annee_academique'],
            $data['ville'],
            $data['resume'],
            $data['mots_cles'],
            $data['fichier_path'],
            $data['fichier_type'],
            $data['couverture_path'] ?? null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::getConnection();
        $fields = [];
        $params = [];

        $allowed = [
            'filiere_id', 'centre_id', 'encadreur_id', 'encadreur_nom', 'titre', 'auteur',
            'annee_academique', 'ville', 'resume', 'mots_cles', 'fichier_path', 'fichier_type',
            'couverture_path', 'statut', 'motif_rejet', 'date_validation',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!$fields) {
            return;
        }

        $params[] = $id;
        $stmt = $db->prepare('UPDATE memoires SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function incrementViews(int $id): void
    {
        $db = Database::getConnection();
        $db->prepare('UPDATE memoires SET nb_vues = nb_vues + 1 WHERE id = ?')->execute([$id]);
    }

    public static function updateRating(int $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE memoires SET note_moyenne = (
                SELECT COALESCE(AVG(note), 0) FROM notes WHERE memoire_id = ?
             ), nb_notes = (
                SELECT COUNT(*) FROM notes WHERE memoire_id = ?
             ) WHERE id = ?'
        );
        $stmt->execute([$id, $id, $id]);
    }

    public static function stats(): array
    {
        $db = Database::getConnection();
        return [
            'total' => (int) $db->query('SELECT COUNT(*) FROM memoires')->fetchColumn(),
            'valides' => (int) $db->query("SELECT COUNT(*) FROM memoires WHERE statut = 'valide'")->fetchColumn(),
            'en_attente' => (int) $db->query("SELECT COUNT(*) FROM memoires WHERE statut = 'en_attente'")->fetchColumn(),
            'rejetes' => (int) $db->query("SELECT COUNT(*) FROM memoires WHERE statut = 'rejete'")->fetchColumn(),
        ];
    }

    public static function topViewed(int $limit = 5): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT m.*, f.nom AS filiere_nom FROM memoires m
             JOIN filieres f ON f.id = m.filiere_id
             WHERE m.statut = 'valide' ORDER BY m.nb_vues DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function topRated(int $limit = 5): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT m.*, f.nom AS filiere_nom FROM memoires m
             JOIN filieres f ON f.id = m.filiere_id
             WHERE m.statut = 'valide' AND m.nb_notes > 0
             ORDER BY m.note_moyenne DESC, m.nb_notes DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function statsByFiliere(): array
    {
        $db = Database::getConnection();
        return $db->query(
            "SELECT f.nom, COUNT(m.id) AS total,
                    SUM(CASE WHEN m.statut = 'valide' THEN 1 ELSE 0 END) AS valides
             FROM filieres f
             LEFT JOIN memoires m ON m.filiere_id = f.id
             GROUP BY f.id, f.nom ORDER BY total DESC"
        )->fetchAll();
    }

    public static function statsByAnnee(): array
    {
        $db = Database::getConnection();
        return $db->query(
            "SELECT annee_academique, COUNT(*) AS total,
                    SUM(CASE WHEN statut = 'valide' THEN 1 ELSE 0 END) AS valides
             FROM memoires GROUP BY annee_academique ORDER BY annee_academique DESC"
        )->fetchAll();
    }

    public static function delete(int $id): void
    {
        $db = Database::getConnection();
        $db->prepare('DELETE FROM memoires WHERE id = ?')->execute([$id]);
    }
}

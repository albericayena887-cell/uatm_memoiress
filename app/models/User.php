<?php
declare(strict_types=1);

class User
{
    public static function findByEmail(string $email): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT u.*, r.code AS role_code, r.nom AS role_nom
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT u.*, r.code AS role_code, r.nom AS role_nom
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(array $filters = []): array
    {
        $db = Database::getConnection();
        $sql = 'SELECT u.*, r.code AS role_code, r.nom AS role_nom
                FROM users u JOIN roles r ON r.id = u.role_id WHERE 1=1';
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= ' AND r.code = ?';
            $params[] = $filters['role'];
        }
        if (isset($filters['actif'])) {
            $sql .= ' AND u.actif = ?';
            $params[] = (int) $filters['actif'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR u.matricule LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term, $term);
        }

        $sql .= ' ORDER BY u.created_at DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO users (role_id, nom, prenom, matricule, email, password, must_change_password, niveau_etude)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)'
        );
        $stmt->execute([
            $data['role_id'],
            $data['nom'],
            $data['prenom'],
            $data['matricule'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['niveau_etude'] ?? null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::getConnection();
        $fields = [];
        $params = [];

        foreach (['role_id', 'nom', 'prenom', 'matricule', 'email', 'actif', 'niveau_etude'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        if (!empty($data['password'])) {
            $fields[] = 'password = ?';
            $fields[] = 'must_change_password = 1';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!$fields) {
            return;
        }

        $params[] = $id;
        $stmt = $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function updatePassword(int $id, string $password, bool $mustChange = false): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE users SET password = ?, must_change_password = ? WHERE id = ?'
        );
        $stmt->execute([
            password_hash($password, PASSWORD_DEFAULT),
            $mustChange ? 1 : 0,
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function promoteAcademicLevel(int $id): ?array
    {
        $user = self::findById($id);
        if (!$user) {
            return null;
        }

        $current = strtoupper((string) ($user['niveau_etude'] ?? ''));
        $next = match ($current) {
            'L1' => 'L2',
            'L2' => 'L3',
            'L3' => 'L3',
            'M1' => 'M2',
            'M2' => 'M2',
            default => null,
        };

        if ($next === null) {
            return ['changed' => false, 'message' => 'Niveau académique non défini pour cet utilisateur.'];
        }

        $db = Database::getConnection();
        $targetRoleCode = in_array($next, ['L3', 'M2'], true) ? 'etudiant_diplome' : 'etudiant_consultant';
        $roleStmt = $db->prepare('SELECT id FROM roles WHERE code = ? LIMIT 1');
        $roleStmt->execute([$targetRoleCode]);
        $targetRoleId = (int) ($roleStmt->fetchColumn() ?: 0);
        if ($targetRoleId <= 0) {
            return ['changed' => false, 'message' => 'Rôle cible introuvable.'];
        }

        self::update($id, [
            'niveau_etude' => $next,
            'role_id' => $targetRoleId,
        ]);

        return [
            'changed' => true,
            'old_level' => $current ?: 'N/A',
            'new_level' => $next,
            'new_role_code' => $targetRoleCode,
            'can_submit' => in_array($next, ['L3', 'M2'], true),
        ];
    }

    public static function count(): int
    {
        $db = Database::getConnection();
        return (int) $db->query('SELECT COUNT(*) FROM users WHERE actif = 1')->fetchColumn();
    }

    public static function professors(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT u.* FROM users u JOIN roles r ON r.id = u.role_id
             WHERE r.code = 'professeur' AND u.actif = 1 ORDER BY u.nom, u.prenom"
        );
        return $stmt->fetchAll();
    }

    public static function importBatch(array $rows): array
    {
        $db = Database::getConnection();
        $roles = $db->query('SELECT code, id FROM roles')->fetchAll(PDO::FETCH_KEY_PAIR);
        $imported = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $roleCode = strtolower(trim($row['role'] ?? ''));
            if (!isset($roles[$roleCode])) {
                $errors[] = "Ligne $line : rôle invalide ($roleCode)";
                continue;
            }
            try {
                self::create([
                    'role_id' => (int) $roles[$roleCode],
                    'nom' => trim($row['nom'] ?? ''),
                    'prenom' => trim($row['prenom'] ?? ''),
                    'matricule' => trim($row['matricule'] ?? ''),
                    'email' => trim($row['email'] ?? ''),
                    'password' => trim($row['password'] ?? 'Temp@2026'),
                ]);
                $imported++;
            } catch (PDOException $e) {
                $errors[] = "Ligne $line : " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}

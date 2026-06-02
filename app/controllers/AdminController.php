<?php
declare(strict_types=1);

class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin']);
        redirect('admin', ['action' => 'users']);
    }

    public function users(): void
    {
        $user = $this->requireRole(['admin']);
        $users = User::all(['search' => $_GET['q'] ?? '']);
        $roles = Role::all();
        $this->view('admin/users', compact('users', 'roles', 'user'));
    }

    public function userCreate(): void
    {
        $this->requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            try {
                $roleId = (int) ($_POST['role_id'] ?? 0);
                User::create([
                    'role_id' => $roleId,
                    'nom' => clean_string($_POST['nom'] ?? '', 100),
                    'prenom' => clean_string($_POST['prenom'] ?? '', 100),
                    'matricule' => clean_string($_POST['matricule'] ?? '', 50),
                    'email' => clean_string($_POST['email'] ?? '', 190),
                    'password' => $_POST['password'] ?? 'Temp@2026',
                    'niveau_etude' => clean_string($_POST['niveau_etude'] ?? '', 3) ?: null,
                ]);
                $this->logActivity('creation_utilisateur', 'Utilisateur créé : ' . $_POST['email']);
                flash('success', 'Utilisateur créé avec succès.');
            } catch (PDOException $e) {
                flash('danger', 'Erreur : email ou matricule déjà utilisé.');
            }
            redirect('admin', ['action' => 'users']);
        }

        $roles = Role::all();
        $this->view('admin/user_form', ['roles' => $roles, 'editUser' => null]);
    }

    public function userEdit(): void
    {
        $this->requireRole(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        $editUser = User::findById($id);

        if (!$editUser) {
            flash('danger', 'Utilisateur introuvable.');
            redirect('admin', ['action' => 'users']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            try {
                User::update($id, [
                    'role_id' => (int) ($_POST['role_id'] ?? $editUser['role_id']),
                    'nom' => clean_string($_POST['nom'] ?? '', 100),
                    'prenom' => clean_string($_POST['prenom'] ?? '', 100),
                    'matricule' => clean_string($_POST['matricule'] ?? '', 50),
                    'email' => clean_string($_POST['email'] ?? '', 190),
                    'actif' => isset($_POST['actif']) ? 1 : 0,
                    'niveau_etude' => clean_string($_POST['niveau_etude'] ?? '', 3) ?: null,
                    'password' => $_POST['password'] ?? '',
                ]);
                $this->logActivity('modification_utilisateur', "Utilisateur #$id modifié");
                flash('success', 'Utilisateur mis à jour.');
            } catch (PDOException $e) {
                flash('danger', 'Erreur lors de la mise à jour.');
            }
            redirect('admin', ['action' => 'users']);
        }

        $roles = Role::all();
        $this->view('admin/user_form', compact('roles', 'editUser'));
    }

    public function userImport(): void
    {
        $this->requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $file = $_FILES['csv_file'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                flash('danger', 'Fichier CSV requis.');
                redirect('admin', ['action' => 'users']);
            }

            $handle = fopen($file['tmp_name'], 'r');
            $header = fgetcsv($handle, 0, ';') ?: fgetcsv($handle, 0, ',');
            if (!$header) {
                flash('danger', 'Fichier CSV vide ou invalide.');
                redirect('admin', ['action' => 'users']);
            }

            $header = array_map('strtolower', array_map('trim', $header));
            $rows = [];
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                if (count($data) < 2) {
                    $data = str_getcsv($data[0] ?? '', ',');
                }
                $row = [];
                foreach ($header as $i => $col) {
                    $row[$col] = $data[$i] ?? '';
                }
                $rows[] = $row;
            }
            fclose($handle);

            $result = User::importBatch($rows);
            $this->logActivity('import_utilisateurs', $result['imported'] . ' utilisateurs importés');
            flash('success', $result['imported'] . ' utilisateur(s) importé(s).');
            if ($result['errors']) {
                flash('warning', implode(' | ', array_slice($result['errors'], 0, 5)));
            }
            redirect('admin', ['action' => 'users']);
        }

        $this->view('admin/user_import');
    }

    public function userDelete(): void
    {
        verify_csrf();
        $admin = $this->requireRole(['admin']);
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            flash('danger', 'Utilisateur invalide.');
            redirect('admin', ['action' => 'users']);
        }
        if ((int) $admin['id'] === $id) {
            flash('warning', 'Vous ne pouvez pas supprimer votre propre compte.');
            redirect('admin', ['action' => 'users']);
        }

        $target = User::findById($id);
        if (!$target) {
            flash('danger', 'Utilisateur introuvable.');
            redirect('admin', ['action' => 'users']);
        }

        try {
            User::delete($id);
            $this->logActivity('suppression_utilisateur', "Utilisateur #$id supprimé");
            flash('success', 'Compte utilisateur supprimé.');
        } catch (PDOException $e) {
            flash('danger', 'Suppression impossible : cet utilisateur est lié à d\'autres données (mémoires, validations, commentaires, etc.).');
        }

        redirect('admin', ['action' => 'users']);
    }

    public function userPromote(): void
    {
        verify_csrf();
        $this->requireRole(['admin']);
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            flash('danger', 'Utilisateur invalide.');
            redirect('admin', ['action' => 'users']);
        }

        $result = User::promoteAcademicLevel($id);
        if (!$result) {
            flash('danger', 'Utilisateur introuvable.');
            redirect('admin', ['action' => 'users']);
        }
        if (empty($result['changed'])) {
            flash('warning', (string) ($result['message'] ?? 'Aucune modification.'));
            redirect('admin', ['action' => 'users']);
        }

        $canSubmit = !empty($result['can_submit']);
        $message = "Passage {$result['old_level']} -> {$result['new_level']} effectué.";
        if ($canSubmit) {
            $message .= ' L’étudiant peut désormais soumettre un mémoire.';
        }
        flash('success', $message);
        $this->logActivity('promotion_niveau_etudiant', "Utilisateur #$id {$result['old_level']}->{$result['new_level']}");
        redirect('admin', ['action' => 'users']);
    }

    public function filieres(): void
    {
        $this->requireRole(['admin']);
        $filieres = Filiere::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = $_POST['form_action'] ?? 'create';
            if ($action === 'create') {
                Filiere::create(
                    clean_string($_POST['code'] ?? '', 30),
                    clean_string($_POST['nom'] ?? '', 150)
                );
                flash('success', 'Filière ajoutée.');
            } else {
                Filiere::update(
                    (int) $_POST['id'],
                    clean_string($_POST['code'] ?? '', 30),
                    clean_string($_POST['nom'] ?? '', 150),
                    isset($_POST['actif']) ? 1 : 0
                );
                flash('success', 'Filière mise à jour.');
            }
            redirect('admin', ['action' => 'filieres']);
        }

        $this->view('admin/filieres', compact('filieres'));
    }

    public function centres(): void
    {
        $this->requireRole(['admin']);
        $centres = Centre::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = $_POST['form_action'] ?? 'create';
            if ($action === 'create') {
                Centre::create(
                    clean_string($_POST['code'] ?? '', 30),
                    clean_string($_POST['nom'] ?? '', 150),
                    clean_string($_POST['ville'] ?? '', 100)
                );
                flash('success', 'Centre ajouté.');
            } else {
                Centre::update(
                    (int) $_POST['id'],
                    clean_string($_POST['code'] ?? '', 30),
                    clean_string($_POST['nom'] ?? '', 150),
                    clean_string($_POST['ville'] ?? '', 100),
                    isset($_POST['actif']) ? 1 : 0
                );
                flash('success', 'Centre mis à jour.');
            }
            redirect('admin', ['action' => 'centres']);
        }

        $this->view('admin/centres', compact('centres'));
    }

    public function memoires(): void
    {
        $this->requireRole(['admin']);
        $memoires = Memoire::search(array_filter([
            'statut' => $_GET['statut'] ?? null,
            'titre' => clean_string($_GET['titre'] ?? '', 300),
        ]), 'admin');
        $this->view('admin/memoires', compact('memoires'));
    }

    public function memoireDelete(): void
    {
        verify_csrf();
        $this->requireRole(['admin']);
        $id = (int) ($_POST['id'] ?? 0);
        $memoire = Memoire::find($id);
        if ($memoire) {
            FileUpload::delete($memoire['fichier_path']);
            FileUpload::delete($memoire['couverture_path']);
            Memoire::delete($id);
            $this->logActivity('suppression_memoire', "Mémoire #$id supprimé");
            flash('success', 'Mémoire supprimé.');
        }
        redirect('admin', ['action' => 'memoires']);
    }

    public function memoireImport(): void
    {
        $this->requireRole(['admin']);
        $config = require APP_PATH . '/config.php';
        $filieres = Filiere::all(true);
        $centres = Centre::all(true);
        $professeurs = User::professors();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            try {
                $filiereId = (int) ($_POST['filiere_id'] ?? 0);
                $centreId = (int) ($_POST['centre_id'] ?? 0);
                $encadreurId = (int) ($_POST['encadreur_id'] ?? 0) ?: null;
                $userId = (int) ($_POST['user_id'] ?? 0);

                if (!$filiereId || !$centreId || !$userId) {
                    throw new RuntimeException('Filière, centre et étudiant requis.');
                }

                $fichierPath = null;
                $fichierType = 'pdf';
                if (!empty($_FILES['fichier']['name'])) {
                    $mime = mime_content_type($_FILES['fichier']['tmp_name']);
                    if (in_array($mime, $config['allowed_pdf'], true)) {
                        $fichierType = 'pdf';
                        $fichierPath = FileUpload::store($_FILES['fichier'], 'memoires', $config['allowed_pdf'], $config['upload_max_size']);
                    } elseif (in_array($mime, $config['allowed_word'], true)) {
                        $fichierType = 'word';
                        $fichierPath = FileUpload::store($_FILES['fichier'], 'memoires', $config['allowed_word'], $config['upload_max_size']);
                    } else {
                        throw new RuntimeException('Format de fichier non supporté.');
                    }
                } else {
                    throw new RuntimeException('Fichier mémoire requis.');
                }

                $couverturePath = null;
                if (!empty($_FILES['couverture']['name'])) {
                    $couverturePath = FileUpload::store($_FILES['couverture'], 'couvertures', $config['allowed_images'], 5 * 1024 * 1024);
                }

                $memoireId = Memoire::create([
                    'user_id' => $userId,
                    'filiere_id' => $filiereId,
                    'centre_id' => $centreId,
                    'encadreur_id' => $encadreurId,
                    'encadreur_nom' => clean_string($_POST['encadreur_nom'] ?? '', 200),
                    'titre' => clean_string($_POST['titre'] ?? '', 300),
                    'auteur' => clean_string($_POST['auteur'] ?? '', 200),
                    'annee_academique' => clean_string($_POST['annee_academique'] ?? '', 20),
                    'ville' => clean_string($_POST['ville'] ?? '', 100),
                    'resume' => trim($_POST['resume'] ?? ''),
                    'mots_cles' => clean_string($_POST['mots_cles'] ?? '', 500),
                    'fichier_path' => $fichierPath,
                    'fichier_type' => $fichierType,
                    'couverture_path' => $couverturePath,
                ]);

                if (isset($_POST['auto_validate'])) {
                    Memoire::update($memoireId, ['statut' => 'valide', 'date_validation' => date('Y-m-d H:i:s')]);
                }

                $this->logActivity('import_memoire', "Mémoire #$memoireId importé");
                flash('success', 'Mémoire importé avec succès.');
            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
            }
            redirect('admin', ['action' => 'memoireImport']);
        }

        $etudiants = User::all(['role' => 'etudiant_diplome', 'actif' => 1]);
        $this->view('admin/memoire_import', compact('filieres', 'centres', 'professeurs', 'etudiants'));
    }
}

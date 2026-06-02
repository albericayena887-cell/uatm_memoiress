<?php
declare(strict_types=1);

class MemoireController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $filters = [
            'titre' => clean_string($_GET['titre'] ?? '', 300),
            'auteur' => clean_string($_GET['auteur'] ?? '', 200),
            'filiere_id' => (int) ($_GET['filiere_id'] ?? 0) ?: null,
            'centre_id' => (int) ($_GET['centre_id'] ?? 0) ?: null,
            'annee_academique' => clean_string($_GET['annee_academique'] ?? '', 20),
            'ville' => clean_string($_GET['ville'] ?? '', 100),
            'encadreur' => clean_string($_GET['encadreur'] ?? '', 200),
            'mots_cles' => clean_string($_GET['mots_cles'] ?? '', 500),
        ];

        $roleCode = $user['role_code'] ?? null;
        if (in_array($roleCode, ['admin', 'directeur_etudes'], true)) {
            $filters['statut'] = $_GET['statut'] ?? null;
        }

        $memoires = Memoire::search(array_filter($filters), $roleCode);
        $filieres = Filiere::all(true);
        $centres = Centre::all(true);

        $this->view('memoire/index', compact('memoires', 'filieres', 'centres', 'filters', 'user'));
    }

    public function show(): void
    {
        $user = $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $memoire = Memoire::find($id);

        if (!$memoire) {
            flash('danger', 'Mémoire introuvable.');
            redirect('memoires');
        }

        if ($memoire['statut'] !== 'valide' && !in_array($user['role_code'], ['admin', 'directeur_etudes', 'professeur', 'etudiant_diplome'], true)) {
            if ($user['role_code'] === 'etudiant_diplome' && (int) $memoire['user_id'] !== (int) $user['id']) {
                flash('danger', 'Accès refusé.');
                redirect('memoires');
            } elseif ($user['role_code'] === 'etudiant_consultant') {
                flash('danger', 'Ce mémoire n\'est pas encore validé.');
                redirect('memoires');
            }
        }

        if ($user['role_code'] === 'etudiant_diplome' && (int) $memoire['user_id'] !== (int) $user['id'] && $memoire['statut'] !== 'valide') {
            flash('danger', 'Accès refusé.');
            redirect('memoires');
        }

        if ($memoire['statut'] === 'valide' || in_array($user['role_code'], ['admin', 'directeur_etudes', 'professeur'], true)) {
            StatistiqueConsultation::record($id, (int) $user['id']);
            $memoire = Memoire::find($id);
        }

        $commentaires = Commentaire::forMemoire($id);
        $isFavorite = Favori::isFavorite($id, (int) $user['id']);
        $userNote = Note::getUserNote($id, (int) $user['id']);
        $validations = Validation::history($id);

        $this->view('memoire/show', compact('memoire', 'commentaires', 'isFavorite', 'userNote', 'validations', 'user'));
    }

    public function viewer(): void
    {
        $user = $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $memoire = Memoire::find($id);

        if (!$memoire || $memoire['fichier_type'] !== 'pdf') {
            http_response_code(404);
            exit('Document introuvable.');
        }

        if ($user['role_code'] === 'etudiant_consultant' && $memoire['statut'] !== 'valide') {
            http_response_code(403);
            exit('Accès refusé.');
        }

        $path = STORAGE_PATH . '/' . ltrim($memoire['fichier_path'], '/');
        if (!is_file($path)) {
            http_response_code(404);
            exit('Fichier introuvable.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="memoire.pdf"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public function cover(): void
    {
        $user = $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $memoire = Memoire::find($id);

        if (!$memoire || !$memoire['couverture_path']) {
            http_response_code(404);
            exit;
        }

        $path = STORAGE_PATH . '/' . ltrim($memoire['couverture_path'], '/');
        if (!is_file($path)) {
            http_response_code(404);
            exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . ($finfo->file($path) ?: 'image/jpeg'));
        header('Cache-Control: private, max-age=3600');
        readfile($path);
        exit;
    }

    public function comment(): void
    {
        verify_csrf();
        $user = $this->requireAuth();
        $id = (int) ($_POST['memoire_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');

        if (strlen($contenu) < 3) {
            flash('danger', 'Commentaire trop court.');
            redirect('memoires', ['action' => 'show', 'id' => $id]);
        }

        $memoire = Memoire::find($id);
        if (!$memoire || $memoire['statut'] !== 'valide') {
            flash('danger', 'Impossible de commenter ce mémoire.');
            redirect('memoires');
        }
        if ((int) $memoire['user_id'] === (int) $user['id']) {
            flash('warning', 'Vous ne pouvez pas commenter votre propre mémoire.');
            redirect('memoires', ['action' => 'show', 'id' => $id]);
        }

        Commentaire::create($id, (int) $user['id'], $contenu);
        $this->logActivity('commentaire', "Commentaire sur mémoire #$id");
        flash('success', 'Commentaire publié.');
        redirect('memoires', ['action' => 'show', 'id' => $id]);
    }

    public function favorite(): void
    {
        verify_csrf();
        $user = $this->requireRole(['etudiant_consultant']);
        $id = (int) ($_POST['memoire_id'] ?? 0);
        $added = Favori::toggle($id, (int) $user['id']);
        flash('success', $added ? 'Ajouté aux favoris.' : 'Retiré des favoris.');
        redirect('memoires', ['action' => 'show', 'id' => $id]);
    }

    public function rate(): void
    {
        verify_csrf();
        $user = $this->requireAuth();
        if ($user['role_code'] === 'etudiant_diplome') {
            flash('danger', 'Vous ne pouvez pas noter un mémoire.');
            redirect('dashboard');
        }
        $id = (int) ($_POST['memoire_id'] ?? 0);
        $note = (int) ($_POST['note'] ?? 0);

        if ($note < 1 || $note > 5) {
            flash('danger', 'Note invalide.');
            redirect('memoires', ['action' => 'show', 'id' => $id]);
        }

        Note::set($id, (int) $user['id'], $note);
        flash('success', 'Note enregistrée.');
        redirect('memoires', ['action' => 'show', 'id' => $id]);
    }
}

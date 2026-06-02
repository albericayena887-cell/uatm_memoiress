<?php
declare(strict_types=1);

class EtudiantController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole(['etudiant_diplome']);
        $memoires = Memoire::search(['user_id' => $user['id']], 'admin');
        $this->view('etudiant/index', compact('user', 'memoires'));
    }

    public function deposit(): void
    {
        $user = $this->requireRole(['etudiant_diplome']);
        $config = require APP_PATH . '/config.php';
        $filieres = Filiere::all(true);
        $centres = Centre::all(true);
        $professeurs = User::professors();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            try {
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
                        throw new RuntimeException('Seuls les fichiers PDF et Word sont acceptés.');
                    }
                } else {
                    throw new RuntimeException('Le fichier du mémoire est obligatoire.');
                }

                $couverturePath = null;
                if (!empty($_FILES['couverture']['name'])) {
                    $couverturePath = FileUpload::store($_FILES['couverture'], 'couvertures', $config['allowed_images'], 5 * 1024 * 1024);
                }

                $encadreurId = (int) ($_POST['encadreur_id'] ?? 0) ?: null;
                $encadreurNom = clean_string($_POST['encadreur_nom'] ?? '', 200);

                $memoireId = Memoire::create([
                    'user_id' => (int) $user['id'],
                    'filiere_id' => (int) $_POST['filiere_id'],
                    'centre_id' => (int) $_POST['centre_id'],
                    'encadreur_id' => $encadreurId,
                    'encadreur_nom' => $encadreurNom,
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

                if ($encadreurId) {
                    $prof = User::findById($encadreurId);
                    $memoire = Memoire::find($memoireId);
                    if ($prof && $memoire) {
                        Mailer::notifyNewMemoire($prof, $user, $memoire);
                        Notification::create(
                            $encadreurId, 'soumission', 'Nouveau mémoire à valider',
                            $user['prenom'] . ' ' . $user['nom'] . ' a soumis : ' . $memoire['titre'], $memoireId
                        );
                    }
                }

                $this->logActivity('depot_memoire', "Mémoire #$memoireId déposé");
                flash('success', 'Mémoire déposé avec succès. En attente de validation.');
                redirect('etudiant');
            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
            }
        }

        $this->view('etudiant/deposit', compact('filieres', 'centres', 'professeurs', 'user'));
    }

    public function edit(): void
    {
        $user = $this->requireRole(['etudiant_diplome']);
        $config = require APP_PATH . '/config.php';
        $id = (int) ($_GET['id'] ?? 0);
        $memoire = Memoire::find($id);

        if (!$memoire || (int) $memoire['user_id'] !== (int) $user['id']) {
            flash('danger', 'Mémoire introuvable.');
            redirect('etudiant');
        }

        if (!in_array($memoire['statut'], ['en_attente', 'rejete'], true)) {
            flash('warning', 'Ce mémoire ne peut plus être modifié.');
            redirect('etudiant');
        }

        $filieres = Filiere::all(true);
        $centres = Centre::all(true);
        $professeurs = User::professors();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            try {
                $data = [
                    'filiere_id' => (int) $_POST['filiere_id'],
                    'centre_id' => (int) $_POST['centre_id'],
                    'encadreur_id' => (int) ($_POST['encadreur_id'] ?? 0) ?: null,
                    'encadreur_nom' => clean_string($_POST['encadreur_nom'] ?? '', 200),
                    'titre' => clean_string($_POST['titre'] ?? '', 300),
                    'auteur' => clean_string($_POST['auteur'] ?? '', 200),
                    'annee_academique' => clean_string($_POST['annee_academique'] ?? '', 20),
                    'ville' => clean_string($_POST['ville'] ?? '', 100),
                    'resume' => trim($_POST['resume'] ?? ''),
                    'mots_cles' => clean_string($_POST['mots_cles'] ?? '', 500),
                    'statut' => 'en_attente',
                    'motif_rejet' => null,
                ];

                if (!empty($_FILES['fichier']['name'])) {
                    $mime = mime_content_type($_FILES['fichier']['tmp_name']);
                    if (in_array($mime, $config['allowed_pdf'], true)) {
                        FileUpload::delete($memoire['fichier_path']);
                        $data['fichier_type'] = 'pdf';
                        $data['fichier_path'] = FileUpload::store($_FILES['fichier'], 'memoires', $config['allowed_pdf'], $config['upload_max_size']);
                    } elseif (in_array($mime, $config['allowed_word'], true)) {
                        FileUpload::delete($memoire['fichier_path']);
                        $data['fichier_type'] = 'word';
                        $data['fichier_path'] = FileUpload::store($_FILES['fichier'], 'memoires', $config['allowed_word'], $config['upload_max_size']);
                    }
                }

                if (!empty($_FILES['couverture']['name'])) {
                    FileUpload::delete($memoire['couverture_path']);
                    $data['couverture_path'] = FileUpload::store($_FILES['couverture'], 'couvertures', $config['allowed_images'], 5 * 1024 * 1024);
                }

                Memoire::update($id, $data);
                $this->logActivity('modification_memoire', "Mémoire #$id modifié");
                flash('success', 'Mémoire mis à jour et resoumis pour validation.');
                redirect('etudiant');
            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
            }
        }

        $this->view('etudiant/edit', compact('memoire', 'filieres', 'centres', 'professeurs', 'user'));
    }

    public function favoris(): void
    {
        $user = $this->requireRole(['etudiant_consultant']);
        $favoris = Favori::forUser((int) $user['id']);
        $this->view('etudiant/favoris', compact('favoris', 'user'));
    }
}

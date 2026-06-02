<?php
declare(strict_types=1);

class ProfesseurController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole(['professeur']);
        $pending = Validation::pendingForProfessor((int) $user['id']);
        $validated = Memoire::search(['encadreur_id' => $user['id'], 'statut' => 'valide'], 'professeur');
        $this->view('professeur/index', compact('user', 'pending', 'validated'));
    }

    public function validation(): void
    {
        $user = $this->requireRole(['professeur']);
        $id = (int) ($_GET['id'] ?? 0);
        $memoire = Memoire::find($id);

        if (!$memoire || (int) ($memoire['encadreur_id'] ?? 0) !== (int) $user['id']) {
            flash('danger', 'Mémoire introuvable ou non assigné.');
            redirect('professeur');
        }

        $validations = Validation::history($id);
        $this->view('professeur/validation', compact('memoire', 'validations', 'user'));
    }

    public function validate(): void
    {
        verify_csrf();
        $user = $this->requireRole(['professeur']);
        $id = (int) ($_POST['memoire_id'] ?? 0);
        $action = $_POST['validation_action'] ?? '';
        $motif = trim($_POST['motif'] ?? '');

        $memoire = Memoire::find($id);
        if (!$memoire || (int) ($memoire['encadreur_id'] ?? 0) !== (int) $user['id']) {
            flash('danger', 'Action non autorisée.');
            redirect('professeur');
        }

        if ($action === 'valide') {
            Memoire::update($id, ['statut' => 'valide', 'motif_rejet' => null, 'date_validation' => date('Y-m-d H:i:s')]);
            Validation::create($id, (int) $user['id'], 'valide');
            $etudiant = User::findById((int) $memoire['user_id']);
            if ($etudiant) {
                Mailer::notifyValidation($etudiant, $memoire, true);
                Notification::create(
                    (int) $etudiant['id'], 'validation', 'Mémoire validé',
                    'Votre mémoire "' . $memoire['titre'] . '" a été validé.', $id
                );
            }
            $this->logActivity('validation_memoire', "Mémoire #$id validé");
            flash('success', 'Mémoire validé. L\'étudiant a été notifié.');
        } elseif ($action === 'rejete') {
            if (strlen($motif) < 5) {
                flash('danger', 'Veuillez indiquer un motif de rejet.');
                redirect('professeur', ['action' => 'validation', 'id' => $id]);
            }
            Memoire::update($id, ['statut' => 'rejete', 'motif_rejet' => $motif]);
            Validation::create($id, (int) $user['id'], 'rejete', $motif);
            $etudiant = User::findById((int) $memoire['user_id']);
            if ($etudiant) {
                Mailer::notifyValidation($etudiant, $memoire, false, $motif);
                Notification::create(
                    (int) $etudiant['id'], 'rejet', 'Mémoire rejeté',
                    'Votre mémoire nécessite des corrections : ' . $motif, $id
                );
            }
            $this->logActivity('rejet_memoire', "Mémoire #$id rejeté");
            flash('warning', 'Mémoire rejeté. L\'étudiant a été notifié.');
        }

        redirect('professeur');
    }
}

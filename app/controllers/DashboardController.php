<?php
declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $stats = Memoire::stats();
        $stats['users'] = User::count();
        $topViewed = Memoire::topViewed(5);
        $topRated = Memoire::topRated(5);
        $byFiliere = Memoire::statsByFiliere();
        $byAnnee = Memoire::statsByAnnee();
        $notifications = Notification::forUser($user['id']);
        $recentLogs = in_array($user['role_code'], ['admin', 'directeur_etudes'], true)
            ? ActivityLog::recent(10) : [];

        $pendingValidations = [];
        if ($user['role_code'] === 'professeur') {
            $pendingValidations = Validation::pendingForProfessor($user['id']);
        }

        $myMemoires = [];
        if ($user['role_code'] === 'etudiant_diplome') {
            $myMemoires = Memoire::search(['user_id' => $user['id']], 'admin');
        }

        $favoris = [];
        if ($user['role_code'] === 'etudiant_consultant') {
            $favoris = Favori::forUser($user['id']);
        }

        $this->view('dashboard/index', compact(
            'user', 'stats', 'topViewed', 'topRated', 'byFiliere', 'byAnnee',
            'notifications', 'recentLogs', 'pendingValidations', 'myMemoires', 'favoris'
        ));
    }

    public function report(): void
    {
        $user = $this->requireRole(['admin', 'directeur_etudes']);
        $memoires = Memoire::search([], 'admin');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="rapport_memoires_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Titre', 'Auteur', 'Filière', 'Centre', 'Année', 'Statut', 'Vues', 'Note moyenne'], ';');
        foreach ($memoires as $m) {
            fputcsv($out, [
                $m['id'], $m['titre'], $m['auteur'], $m['filiere_nom'], $m['centre_nom'],
                $m['annee_academique'], statut_label($m['statut']), $m['nb_vues'], $m['note_moyenne'],
            ], ';');
        }
        fclose($out);
        $this->logActivity('export_rapport', 'Export CSV des mémoires');
        exit;
    }
}

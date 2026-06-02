<h1 class="h3 mb-4"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Import utilisateurs (CSV/Excel)</h1>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-body p-4">
        <p>Exportez votre fichier Excel en CSV (séparateur <code>;</code>) avec les colonnes :</p>
        <code>nom; prenom; matricule; email; role; password</code>
        <p class="mt-2 small text-muted">Rôles valides : admin, directeur_etudes, professeur, etudiant_diplome, etudiant_consultant</p>
        <form method="post" enctype="multipart/form-data" class="mt-4">
            <?= csrf_field() ?>
            <div class="mb-3">
                <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
            </div>
            <button type="submit" class="btn btn-uatm">Importer</button>
            <a href="<?= url('admin', ['action' => 'users']) ?>" class="btn btn-outline-secondary">Retour</a>
        </form>
    </div>
</div>

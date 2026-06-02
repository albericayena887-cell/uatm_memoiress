<h1 class="h3 mb-4"><i class="bi bi-cloud-upload me-2"></i>Import de mémoire</h1>
<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-body p-4">
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Titre *</label><input type="text" name="titre" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Auteur *</label><input type="text" name="auteur" class="form-control" required></div>
                <div class="col-md-4">
                    <label class="form-label">Étudiant *</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Choisir...</option>
                        <?php foreach ($etudiants as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= e($e['prenom'] . ' ' . $e['nom'] . ' (' . $e['matricule'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filière *</label>
                    <select name="filiere_id" class="form-select" required>
                        <?php foreach ($filieres as $f): ?><option value="<?= $f['id'] ?>"><?= e($f['nom']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Centre *</label>
                    <select name="centre_id" class="form-select" required>
                        <?php foreach ($centres as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['nom']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Professeur encadreur</label>
                    <select name="encadreur_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($professeurs as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['prenom'] . ' ' . $p['nom']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Nom encadreur *</label><input type="text" name="encadreur_nom" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Année académique *</label><input type="text" name="annee_academique" class="form-control" placeholder="2024-2025" required></div>
                <div class="col-md-4"><label class="form-label">Ville *</label><input type="text" name="ville" class="form-control" required placeholder="Cotonou"></div>
                <div class="col-12"><label class="form-label">Résumé *</label><textarea name="resume" class="form-control" rows="4" required></textarea></div>
                <div class="col-12"><label class="form-label">Mots-clés *</label><input type="text" name="mots_cles" class="form-control" required></div>
                <div class="col-md-6">
                    <label class="form-label">Fichier mémoire (PDF/Word) *</label>
                    <input type="file" name="fichier" class="form-control" accept=".pdf,.doc,.docx" required>
                    <small class="text-muted">Taille maximale : <?= e(upload_max_size_label()) ?></small>
                </div>
                <div class="col-md-6"><label class="form-label">Image de couverture</label><input type="file" name="couverture" class="form-control" accept="image/*"></div>
                <div class="col-12"><div class="form-check"><input type="checkbox" name="auto_validate" class="form-check-input" id="auto"><label class="form-check-label" for="auto">Valider automatiquement</label></div></div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-uatm">Importer</button>
                <a href="<?= url('admin', ['action' => 'memoires']) ?>" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>

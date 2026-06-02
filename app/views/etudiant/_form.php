<h1 class="h3 mb-4"><?= e($title) ?></h1>
<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-body p-4">
        <form method="post" action="<?= $formAction ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Titre *</label><input type="text" name="titre" class="form-control" required value="<?= e($memoire['titre'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Auteur *</label><input type="text" name="auteur" class="form-control" required value="<?= e($memoire['auteur'] ?? ($user['prenom'] . ' ' . $user['nom'])) ?>"></div>
                <div class="col-md-6"><label class="form-label">Année académique *</label><input type="text" name="annee_academique" class="form-control" placeholder="2024-2025" required value="<?= e($memoire['annee_academique'] ?? '') ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Filière *</label>
                    <select name="filiere_id" class="form-select" required>
                        <?php foreach ($filieres as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= ($memoire['filiere_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= e($f['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Centre *</label>
                    <select name="centre_id" class="form-select" required>
                        <?php foreach ($centres as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($memoire['centre_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Ville *</label><input type="text" name="ville" class="form-control" required placeholder="Cotonou" value="<?= e($memoire['ville'] ?? 'Cotonou') ?>"></div>
                <div class="col-md-6">
                    <label class="form-label">Professeur encadreur (compte)</label>
                    <select name="encadreur_id" class="form-select">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($professeurs as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($memoire['encadreur_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['prenom'] . ' ' . $p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Nom du professeur encadreur *</label><input type="text" name="encadreur_nom" class="form-control" required value="<?= e($memoire['encadreur_nom'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label">Résumé *</label><textarea name="resume" class="form-control" rows="5" required><?= e($memoire['resume'] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label">Mots-clés *</label><input type="text" name="mots_cles" class="form-control" placeholder="Séparés par des virgules" required value="<?= e($memoire['mots_cles'] ?? '') ?>"></div>
                <div class="col-md-6">
                    <label class="form-label">Fichier mémoire (PDF ou Word) <?= $isEdit ? '' : '*' ?></label>
                    <input type="file" name="fichier" class="form-control" accept=".pdf,.doc,.docx" <?= $isEdit ? '' : 'required' ?>>
                    <small class="text-muted d-block">Taille maximale : <?= e(upload_max_size_label()) ?> (PDF ou Word).</small>
                    <?php if ($isEdit): ?><small class="text-muted">Laisser vide pour conserver le fichier actuel</small><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image de couverture</label>
                    <input type="file" name="couverture" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-uatm"><?= $isEdit ? 'Mettre à jour et resoumettre' : 'Soumettre pour validation' ?></button>
                <a href="<?= url('etudiant') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

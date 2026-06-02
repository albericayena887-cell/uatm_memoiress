<h1 class="h3 mb-4" data-aos="fade-right"><i class="bi bi-search me-2"></i>Recherche avancée</h1>

<div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-3">
            <input type="hidden" name="page" value="memoires">
            <div class="col-md-6"><label class="form-label">Titre</label><input type="text" name="titre" class="form-control" value="<?= e($filters['titre'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Auteur</label><input type="text" name="auteur" class="form-control" value="<?= e($filters['auteur'] ?? '') ?>"></div>
            <div class="col-md-4">
                <label class="form-label">Filière</label>
                <select name="filiere_id" class="form-select">
                    <option value="">Toutes</option>
                    <?php foreach ($filieres as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= ($filters['filiere_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= e($f['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Centre</label>
                <select name="centre_id" class="form-select">
                    <option value="">Tous</option>
                    <?php foreach ($centres as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['centre_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Année académique</label><input type="text" name="annee_academique" class="form-control" placeholder="2024-2025" value="<?= e($filters['annee_academique'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Ville</label><input type="text" name="ville" class="form-control" placeholder="Cotonou, Porto-Novo..." value="<?= e($filters['ville'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Encadreur</label><input type="text" name="encadreur" class="form-control" value="<?= e($filters['encadreur'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Mots-clés</label><input type="text" name="mots_cles" class="form-control" value="<?= e($filters['mots_cles'] ?? '') ?>"></div>
            <?php if ($user && in_array($user['role_code'], ['admin', 'directeur_etudes'], true)): ?>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="valide" <?= ($_GET['statut'] ?? '') === 'valide' ? 'selected' : '' ?>>Validé</option>
                    <option value="en_attente" <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="rejete" <?= ($_GET['statut'] ?? '') === 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-12">
                <button type="submit" class="btn btn-uatm"><i class="bi bi-search me-1"></i>Rechercher</button>
                <a href="<?= url('memoires') ?>" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<p class="text-muted mb-3"><?= count($memoires) ?> résultat(s)</p>

<div class="row g-4">
    <?php foreach ($memoires as $i => $m): ?>
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= min($i * 50, 300) ?>">
        <div class="card memoire-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <span class="badge bg-<?= statut_badge($m['statut']) ?> mb-2"><?= e(statut_label($m['statut'])) ?></span>
                <h5 class="card-title h6"><?= e($m['titre']) ?></h5>
                <p class="small text-muted mb-1"><i class="bi bi-person"></i> <?= e($m['auteur']) ?></p>
                <p class="small text-muted mb-2"><i class="bi bi-building"></i> <?= e($m['filiere_nom']) ?> — <?= e($m['centre_nom']) ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted"><i class="bi bi-eye"></i> <?= (int) $m['nb_vues'] ?></span>
                    <a href="<?= url('memoires', ['action' => 'show', 'id' => $m['id']]) ?>" class="btn btn-sm btn-uatm">Voir</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!$memoires): ?>
<div class="alert alert-info" data-aos="fade-in">Aucun mémoire trouvé pour ces critères.</div>
<?php endif; ?>

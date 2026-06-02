<h1 class="h3 mb-4"><?= $editUser ? 'Modifier' : 'Créer' ?> un utilisateur</h1>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-body p-4">
        <form method="post">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required value="<?= e($editUser['nom'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Prénom</label><input type="text" name="prenom" class="form-control" required value="<?= e($editUser['prenom'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Matricule</label><input type="text" name="matricule" class="form-control" required value="<?= e($editUser['matricule'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= e($editUser['email'] ?? '') ?>"></div>
                <div class="col-md-6">
                    <label class="form-label">Niveau d'étude</label>
                    <?php $niveau = strtoupper((string) ($editUser['niveau_etude'] ?? '')); ?>
                    <select name="niveau_etude" class="form-select">
                        <option value="">— Non défini —</option>
                        <?php foreach (['L1', 'L2', 'L3', 'M1', 'M2'] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= $niveau === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">L3/M2 = accès dépôt mémoire automatique.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rôle</label>
                    <select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($editUser['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mot de passe<?= $editUser ? ' (laisser vide pour conserver)' : '' ?></label>
                    <input type="text" name="password" class="form-control" <?= $editUser ? '' : 'required' ?> placeholder="<?= $editUser ? 'Nouveau mot de passe temporaire' : 'Mot de passe temporaire' ?>">
                </div>
                <?php if ($editUser): ?>
                <div class="col-12"><div class="form-check"><input type="checkbox" name="actif" class="form-check-input" id="actif" <?= $editUser['actif'] ? 'checked' : '' ?>><label class="form-check-label" for="actif">Compte actif</label></div></div>
                <?php endif; ?>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-uatm">Enregistrer</button>
                <a href="<?= url('admin', ['action' => 'users']) ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

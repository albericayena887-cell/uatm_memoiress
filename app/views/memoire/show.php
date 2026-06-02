<div class="row g-4">
    <div class="col-lg-4" data-aos="fade-right">
        <div class="card border-0 shadow-sm">
            <?php if ($memoire['couverture_path']): ?>
            <img src="<?= url('memoires', ['action' => 'cover', 'id' => $memoire['id']]) ?>" class="card-img-top cover-img" alt="Couverture">
            <?php else: ?>
            <div class="cover-placeholder d-flex align-items-center justify-content-center">
                <i class="bi bi-journal-text display-3 text-muted"></i>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <span class="badge bg-<?= statut_badge($memoire['statut']) ?> mb-2"><?= e(statut_label($memoire['statut'])) ?></span>
                <h1 class="h4"><?= e($memoire['titre']) ?></h1>
                <p class="mb-1"><strong>Auteur :</strong> <?= e($memoire['auteur']) ?></p>
                <p class="mb-1"><strong>Filière :</strong> <?= e($memoire['filiere_nom']) ?></p>
                <p class="mb-1"><strong>Centre :</strong> <?= e($memoire['centre_nom']) ?></p>
                <p class="mb-1"><strong>Année :</strong> <?= e($memoire['annee_academique']) ?></p>
                <p class="mb-1"><strong>Ville :</strong> <?= e($memoire['ville']) ?></p>
                <p class="mb-1"><strong>Encadreur :</strong> <?= e($memoire['encadreur_nom']) ?></p>
                <p class="mb-2"><strong>Mots-clés :</strong> <?= e($memoire['mots_cles']) ?></p>
                <div class="d-flex gap-3 text-muted small mb-3">
                    <span><i class="bi bi-eye"></i> <?= (int) $memoire['nb_vues'] ?> vues</span>
                    <?php if ($memoire['nb_notes'] > 0): ?>
                    <span class="text-warning"><i class="bi bi-star-fill"></i> <?= number_format((float) $memoire['note_moyenne'], 1) ?> (<?= (int) $memoire['nb_notes'] ?>)</span>
                    <?php endif; ?>
                </div>

                <?php if ($memoire['statut'] === 'rejete' && (int) $memoire['user_id'] === (int) $user['id']): ?>
                <div class="alert alert-danger small">Motif du rejet : <?= e($memoire['motif_rejet']) ?></div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2">
                    <?php if ($memoire['fichier_type'] === 'pdf' && ($memoire['statut'] === 'valide' || in_array($user['role_code'], ['admin', 'directeur_etudes', 'professeur'], true) || ((int) $memoire['user_id'] === (int) $user['id']))): ?>
                    <button class="btn btn-uatm btn-sm" data-bs-toggle="modal" data-bs-target="#pdfModal">
                        <i class="bi bi-file-earmark-pdf"></i> Consulter en ligne
                    </button>
                    <?php endif; ?>
                    <?php if ($user['role_code'] === 'etudiant_consultant' && $memoire['statut'] === 'valide'): ?>
                    <form method="post" action="<?= url('memoires', ['action' => 'favorite']) ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="memoire_id" value="<?= $memoire['id'] ?>">
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-heart<?= $isFavorite ? '-fill' : '' ?>"></i> Favoris
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <?php if (can_rate($user) && $memoire['statut'] === 'valide'): ?>
                <form method="post" action="<?= url('memoires', ['action' => 'rate']) ?>" class="mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="memoire_id" value="<?= $memoire['id'] ?>">
                    <label class="form-label small">Noter ce mémoire</label>
                    <div class="input-group input-group-sm">
                        <select name="note" class="form-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= $userNote === $i ? 'selected' : '' ?>><?= $i ?> étoile<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                        <button class="btn btn-uatm" type="submit">Noter</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8" data-aos="fade-left">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Résumé</div>
            <div class="card-body"><p class="mb-0"><?= nl2br(e($memoire['resume'])) ?></p></div>
        </div>

        <?php if ($validations): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Historique de validation</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($validations as $v): ?>
                <li class="list-group-item">
                    <span class="badge bg-<?= $v['action'] === 'valide' ? 'success' : 'danger' ?>"><?= e($v['action']) ?></span>
                    <?= e($v['prenom'] . ' ' . $v['nom']) ?> — <?= e(date('d/m/Y H:i', strtotime($v['created_at']))) ?>
                    <?php if ($v['motif']): ?><br><small class="text-muted"><?= e($v['motif']) ?></small><?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Commentaires (<?= count($commentaires) ?>)</div>
            <div class="card-body">
                <?php if ($memoire['statut'] === 'valide' && can_comment($user) && (int) $memoire['user_id'] !== (int) $user['id']): ?>
                <form method="post" action="<?= url('memoires', ['action' => 'comment']) ?>" class="mb-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="memoire_id" value="<?= $memoire['id'] ?>">
                    <textarea name="contenu" class="form-control mb-2" rows="3" placeholder="Votre commentaire..." required></textarea>
                    <button type="submit" class="btn btn-uatm btn-sm">Publier</button>
                </form>
                <?php elseif ((int) $memoire['user_id'] === (int) $user['id']): ?>
                <div class="alert alert-secondary py-2 small mb-4">Vous êtes l'auteur de ce mémoire, vous ne pouvez pas le commenter.</div>
                <?php endif; ?>
                <?php foreach ($commentaires as $c): ?>
                <div class="border-bottom pb-3 mb-3">
                    <strong><?= e($c['prenom'] . ' ' . $c['nom']) ?></strong>
                    <small class="text-muted ms-2"><?= e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></small>
                    <p class="mb-0 mt-1"><?= nl2br(e($c['contenu'])) ?></p>
                </div>
                <?php endforeach; ?>
                <?php if (!$commentaires): ?>
                <p class="text-muted mb-0">Aucun commentaire pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($memoire['fichier_type'] === 'pdf'): ?>
<div class="modal fade" id="pdfModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-uatm text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-pdf me-2"></i>Consultation en ligne — <?= e($memoire['titre']) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 pdf-viewer-container" oncontextmenu="return false;">
                <iframe id="pdfViewer" src="<?= url('memoires', ['action' => 'viewer', 'id' => $memoire['id']]) ?>#toolbar=0&navpanes=0"
                        class="pdf-viewer" title="Lecteur PDF"></iframe>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto"><i class="bi bi-shield-lock"></i> Consultation en ligne uniquement — téléchargement désactivé</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<h1 class="h3 mb-4">Validation du mémoire</h1>

<div class="row g-4">
    <div class="col-lg-5" data-aos="fade-right">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5"><?= e($memoire['titre']) ?></h2>
                <p><strong>Auteur :</strong> <?= e($memoire['auteur']) ?></p>
                <p><strong>Filière :</strong> <?= e($memoire['filiere_nom']) ?></p>
                <p><strong>Centre :</strong> <?= e($memoire['centre_nom']) ?></p>
                <p><strong>Année :</strong> <?= e($memoire['annee_academique']) ?></p>
                <p class="mb-0"><strong>Résumé :</strong><br><?= nl2br(e($memoire['resume'])) ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-7" data-aos="fade-left">
        <?php if ($memoire['fichier_type'] === 'pdf'): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-uatm text-white">Consultation du document</div>
            <div class="pdf-viewer-container" oncontextmenu="return false;">
                <iframe src="<?= url('memoires', ['action' => 'viewer', 'id' => $memoire['id']]) ?>#toolbar=0&navpanes=0" class="pdf-viewer"></iframe>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Document Word — consultation via la fiche mémoire après conversion.</div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Décision</div>
            <div class="card-body">
                <form method="post" action="<?= url('professeur', ['action' => 'validate']) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="memoire_id" value="<?= $memoire['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Motif de rejet (obligatoire en cas de rejet)</label>
                        <textarea name="motif" class="form-control" rows="3" placeholder="Corrections à apporter..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="validation_action" value="valide" class="btn btn-success"><i class="bi bi-check-lg"></i> Valider</button>
                        <button type="submit" name="validation_action" value="rejete" class="btn btn-danger"><i class="bi bi-x-lg"></i> Rejeter</button>
                        <a href="<?= url('professeur') ?>" class="btn btn-outline-secondary ms-auto">Retour</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

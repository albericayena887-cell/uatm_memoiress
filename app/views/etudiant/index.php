<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-journal-text me-2"></i>Mes mémoires</h1>
    <a href="<?= url('etudiant', ['action' => 'deposit']) ?>" class="btn btn-uatm"><i class="bi bi-plus-lg"></i> Déposer un mémoire</a>
</div>

<?php if ($memoires): ?>
<div class="row g-4">
    <?php foreach ($memoires as $i => $m): ?>
    <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?= min($i * 50, 200) ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-<?= statut_badge($m['statut']) ?> mb-2"><?= e(statut_label($m['statut'])) ?></span>
                <h5 class="h6"><?= e($m['titre']) ?></h5>
                <p class="small text-muted">Déposé le <?= e(date('d/m/Y', strtotime($m['date_depot']))) ?></p>
                <?php if ($m['statut'] === 'rejete'): ?>
                <div class="alert alert-danger py-2 small"><?= e($m['motif_rejet']) ?></div>
                <?php endif; ?>
                <div class="d-flex gap-2">
                    <a href="<?= url('memoires', ['action' => 'show', 'id' => $m['id']]) ?>" class="btn btn-sm btn-outline-uatm">Voir</a>
                    <?php if (in_array($m['statut'], ['en_attente', 'rejete'], true)): ?>
                    <a href="<?= url('etudiant', ['action' => 'edit', 'id' => $m['id']]) ?>" class="btn btn-sm btn-uatm">Modifier</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info" data-aos="fade-in">Vous n'avez pas encore déposé de mémoire. <a href="<?= url('etudiant', ['action' => 'deposit']) ?>">Déposer maintenant</a></div>
<?php endif; ?>

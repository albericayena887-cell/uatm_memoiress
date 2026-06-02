<h1 class="h3 mb-4" data-aos="fade-right">
    <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
    <?php if (in_array($user['role_code'], ['admin', 'directeur_etudes'], true)): ?>
    <a href="<?= url('dashboard', ['action' => 'report']) ?>" class="btn btn-sm btn-outline-uatm float-end"><i class="bi bi-download"></i> Rapport CSV</a>
    <?php endif; ?>
</h1>

<div class="row g-4 mb-4">
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="50">
        <div class="card stat-card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="text-muted small">Mémoires total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
        <div class="card stat-card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="stat-number text-success"><?= $stats['valides'] ?></div>
                <div class="text-muted small">Validés</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="150">
        <div class="card stat-card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="stat-number text-warning"><?= $stats['en_attente'] ?></div>
                <div class="text-muted small">En attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
        <div class="card stat-card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="stat-number"><?= $stats['users'] ?></div>
                <div class="text-muted small">Utilisateurs actifs</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php if (in_array($user['role_code'], ['admin', 'directeur_etudes'], true)): ?>
    <div class="col-lg-6" data-aos="fade-up">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Statistiques par filière</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Filière</th><th>Total</th><th>Validés</th></tr></thead>
                    <tbody>
                    <?php foreach ($byFiliere as $row): ?>
                    <tr><td><?= e($row['nom']) ?></td><td><?= (int) $row['total'] ?></td><td><?= (int) $row['valides'] ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Statistiques par année</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Année</th><th>Total</th><th>Validés</th></tr></thead>
                    <tbody>
                    <?php foreach ($byAnnee as $row): ?>
                    <tr><td><?= e($row['annee_academique']) ?></td><td><?= (int) $row['total'] ?></td><td><?= (int) $row['valides'] ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-lg-6" data-aos="fade-up">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Mémoires les plus consultés</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($topViewed as $m): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span><?= e(mb_strimwidth($m['titre'], 0, 50, '...')) ?></span>
                    <span class="badge bg-secondary"><?= (int) $m['nb_vues'] ?> vues</span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Mémoires les mieux notés</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($topRated as $m): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span><?= e(mb_strimwidth($m['titre'], 0, 50, '...')) ?></span>
                    <span class="text-warning"><i class="bi bi-star-fill"></i> <?= number_format((float) $m['note_moyenne'], 1) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php if ($pendingValidations): ?>
    <div class="col-12" data-aos="fade-up">
        <div class="card border-0 shadow-sm border-warning">
            <div class="card-header bg-warning-subtle fw-semibold">Validations en attente</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Titre</th><th>Auteur</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($pendingValidations as $m): ?>
                    <tr>
                        <td><?= e($m['titre']) ?></td>
                        <td><?= e($m['auteur']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($m['date_depot']))) ?></td>
                        <td><a href="<?= url('professeur', ['action' => 'validation', 'id' => $m['id']]) ?>" class="btn btn-sm btn-uatm">Valider</a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($notifications): ?>
    <div class="col-12" data-aos="fade-up">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Notifications récentes</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                <li class="list-group-item <?= $n['lu'] ? '' : 'fw-semibold' ?>">
                    <div class="d-flex justify-content-between">
                        <span><?= e($n['titre']) ?></span>
                        <small class="text-muted"><?= e(date('d/m/Y H:i', strtotime($n['created_at']))) ?></small>
                    </div>
                    <small class="text-muted"><?= e($n['message']) ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

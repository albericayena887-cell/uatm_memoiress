<section class="hero-uatm text-white py-5 mb-4 rounded-4" data-aos="fade-up">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold">Bibliothèque numérique des mémoires soutenus</h1>
            <p class="lead mb-4">Consultez, recherchez et gérez les mémoires de l'UATM en toute sécurité.</p>
            <?php if (!current_user()): ?>
            <a href="<?= url('login') ?>" class="btn btn-light btn-lg me-2"><i class="bi bi-box-arrow-in-right me-2"></i>Se connecter</a>
            <?php else: ?>
            <a href="<?= url('memoires') ?>" class="btn btn-light btn-lg me-2"><i class="bi bi-search me-2"></i>Rechercher</a>
            <a href="<?= url('dashboard') ?>" class="btn btn-outline-light btn-lg">Tableau de bord</a>
            <?php endif; ?>
        </div>
        <div class="col-lg-4 text-center d-none d-lg-block" data-aos="zoom-in" data-aos-delay="200">
            <img src="<?= app_logo() ?>" alt="Logo UATM GASA" class="brand-logo-lg opacity-90">
            <p class="mt-3 fw-semibold mb-0">UATM GASA</p>
        </div>
    </div>
</section>

<div class="row g-4 mb-5">
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-shield-check text-uatm fs-1 mb-3"></i>
                <h5>Accès sécurisé</h5>
                <p class="text-muted mb-0">Consultation en ligne protégée, sans téléchargement.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-check2-circle text-uatm fs-1 mb-3"></i>
                <h5>Validation encadrée</h5>
                <p class="text-muted mb-0">Workflow de validation par les professeurs encadreurs.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-graph-up text-uatm fs-1 mb-3"></i>
                <h5>Statistiques</h5>
                <p class="text-muted mb-0">Suivi des consultations, notes et tendances par filière.</p>
            </div>
        </div>
    </div>
</div>

<?php if ($topViewed): ?>
<section class="mb-5" data-aos="fade-up">
    <h2 class="h4 mb-3"><i class="bi bi-eye me-2"></i>Mémoires les plus consultés</h2>
    <div class="row g-3">
        <?php foreach ($topViewed as $m): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card memoire-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <span class="badge bg-uatm-light text-uatm mb-2"><?= e($m['filiere_nom']) ?></span>
                    <h6 class="card-title"><?= e($m['titre']) ?></h6>
                    <p class="small text-muted mb-2"><?= e($m['auteur']) ?> — <?= e($m['annee_academique']) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small"><i class="bi bi-eye"></i> <?= (int) $m['nb_vues'] ?></span>
                        <?php if (current_user()): ?>
                        <a href="<?= url('memoires', ['action' => 'show', 'id' => $m['id']]) ?>" class="btn btn-sm btn-uatm">Consulter</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if ($topRated): ?>
<section data-aos="fade-up">
    <h2 class="h4 mb-3"><i class="bi bi-star me-2"></i>Mémoires les mieux notés</h2>
    <div class="row g-3">
        <?php foreach ($topRated as $m): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card memoire-card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-warning mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?= $i <= round($m['note_moyenne']) ? '-fill' : '' ?>"></i>
                        <?php endfor; ?>
                        <span class="text-muted small ms-1">(<?= (int) $m['nb_notes'] ?>)</span>
                    </div>
                    <h6 class="card-title"><?= e($m['titre']) ?></h6>
                    <p class="small text-muted"><?= e($m['auteur']) ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

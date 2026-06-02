<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'UATM GASA') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="<?= asset('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<?php $currentUser = current_user(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-uatm sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold brand-block" href="<?= url('home') ?>">
            <img src="<?= app_logo() ?>" alt="Logo UATM GASA" class="brand-logo">
            <span>UATM GASA</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url('home') ?>">Accueil</a></li>
                <?php if ($currentUser): ?>
                <li class="nav-item"><a class="nav-link" href="<?= url('memoires') ?>">Recherche</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('dashboard') ?>">Tableau de bord</a></li>
                <?php if (($currentUser['role_code'] ?? '') === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Administration</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= url('admin', ['action' => 'users']) ?>">Utilisateurs</a></li>
                        <li><a class="dropdown-item" href="<?= url('admin', ['action' => 'filieres']) ?>">Filières</a></li>
                        <li><a class="dropdown-item" href="<?= url('admin', ['action' => 'centres']) ?>">Centres</a></li>
                        <li><a class="dropdown-item" href="<?= url('admin', ['action' => 'memoires']) ?>">Mémoires</a></li>
                        <li><a class="dropdown-item" href="<?= url('admin', ['action' => 'memoireImport']) ?>">Import mémoires</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if (($currentUser['role_code'] ?? '') === 'professeur'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= url('professeur') ?>">Validations</a></li>
                <?php endif; ?>
                <?php if (($currentUser['role_code'] ?? '') === 'etudiant_diplome'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= url('etudiant') ?>">Mes mémoires</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('etudiant', ['action' => 'deposit']) ?>">Déposer</a></li>
                <?php endif; ?>
                <?php if (($currentUser['role_code'] ?? '') === 'etudiant_consultant'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= url('etudiant', ['action' => 'favoris']) ?>">Favoris</a></li>
                <?php endif; ?>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="<?= url('login') ?>">Connexion</a></li>
                <?php endif; ?>
            </ul>
            <?php if ($currentUser): ?>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= e($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small"><?= e($currentUser['role_nom']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('change-password') ?>">Mot de passe</a></li>
                        <li><a class="dropdown-item" href="<?= url('logout') ?>">Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <?php foreach (get_flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert" data-aos="fade-down">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endforeach; ?>
        <?php require $contentView; ?>
    </div>
</main>

<footer class="footer-uatm py-4 mt-5">
    <div class="container text-center">
        <p class="mb-2">
            <img src="<?= app_logo() ?>" alt="Logo UATM GASA" class="brand-logo" style="height:36px;filter:brightness(1.1);">
        </p>
        <p class="mb-0">&copy; <?= date('Y') ?> UATM GASA</p>
        <small class="text-muted">Consultation sécurisée 24h/24</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>

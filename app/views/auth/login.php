<div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
    <div class="card auth-card shadow-lg border-0" data-aos="zoom-in" style="width:100%;max-width:420px;">
        <div class="card-body p-5">
            <div class="text-center mb-4 brand-block-vertical">
                <img src="<?= app_logo() ?>" alt="Logo UATM GASA" class="brand-logo-md">
                <div>
                    <h2 class="h4 mt-2 mb-0">UATM GASA</h2>
                    <p class="text-muted small mb-0">Connexion</p>
                </div>
            </div>
            <p class="text-muted small text-center mb-4">Accès réservé aux comptes créés par l'administration</p>
            <?php foreach (get_flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> py-2"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
            <form method="post" action="<?= url('login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Adresse e-mail</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-uatm w-100 btn-lg">Se connecter</button>
            </form>
            <p class="text-center mt-3 mb-0">
                <a href="<?= url('forgot-password') ?>" class="small text-uatm">Mot de passe oublié ?</a>
            </p>
            <p class="text-center mt-4 mb-0">
                <a href="<?= url('home') ?>" class="text-muted small">Retour à l'accueil</a>
            </p>
        </div>
    </div>
</div>

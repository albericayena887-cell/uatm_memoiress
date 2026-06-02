<div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
    <div class="card auth-card shadow-lg border-0" data-aos="zoom-in" style="width:100%;max-width:460px;">
        <div class="card-body p-5">
            <div class="text-center mb-4 brand-block-vertical">
                <img src="<?= app_logo() ?>" alt="Logo UATM GASA" class="brand-logo-md">
                <div>
                    <h2 class="h4 mt-2 mb-0">UATM GASA</h2>
                    <p class="text-muted small mb-0">Mot de passe oublié</p>
                </div>
            </div>
            <p class="text-muted small text-center mb-4">Récupération par code OTP envoyé par e-mail</p>
            <?php foreach (get_flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> py-2"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
            <?php if (($step ?? 'request') === 'request'): ?>
                <form method="post" action="<?= url('forgot-password') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action_type" value="send_otp">
                    <div class="mb-3">
                        <label class="form-label">Adresse e-mail</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-uatm w-100">Envoyer le code OTP par e-mail</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info py-2 small">
                    Code envoyé vers <strong><?= e($reset['email'] ?? '') ?></strong> (valide 10 minutes).
                </div>
                <form method="post" action="<?= url('forgot-password', ['step' => 'verify']) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action_type" value="verify_otp">
                    <div class="mb-3">
                        <label class="form-label">Code OTP (6 chiffres)</label>
                        <input type="text" name="otp" class="form-control" maxlength="6" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-uatm w-100">Valider et réinitialiser</button>
                </form>
                <p class="text-center mt-3 mb-0">
                    <a href="<?= url('forgot-password') ?>" class="small text-uatm">Renvoyer un nouveau code</a>
                </p>
            <?php endif; ?>
            <p class="text-center mt-4 mb-0">
                <a href="<?= url('login') ?>" class="small text-uatm">Retour à la connexion</a>
            </p>
        </div>
    </div>
</div>

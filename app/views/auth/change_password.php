<div class="row justify-content-center">
    <div class="col-lg-6" data-aos="fade-up">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-uatm text-white">
                <h1 class="h5 mb-0"><i class="bi bi-key me-2"></i>Modifier le mot de passe</h1>
            </div>
            <div class="card-body p-4">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-uatm">Enregistrer</button>
                    <a href="<?= url('dashboard') ?>" class="btn btn-outline-secondary ms-2">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>

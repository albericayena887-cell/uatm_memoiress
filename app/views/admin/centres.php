<h1 class="h3 mb-4">Gestion des centres — Bénin</h1>
<div class="row g-4">
    <div class="col-lg-4" data-aos="fade-right">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-uatm text-white">Ajouter un centre</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="create">
                    <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" required placeholder="UATM-CTN"></div>
                    <div class="mb-3"><label class="form-label">Nom du centre</label><input type="text" name="nom" class="form-control" required placeholder="UATM Cotonou"></div>
                    <div class="mb-3"><label class="form-label">Ville</label><input type="text" name="ville" class="form-control" placeholder="Cotonou, Porto-Novo, Parakou..."></div>
                    <button class="btn btn-uatm w-100">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8" data-aos="fade-left">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Code</th><th>Nom</th><th>Ville</th><th>Actif</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($centres as $c): ?>
                    <tr>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="update">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <td><input type="text" name="code" class="form-control form-control-sm" value="<?= e($c['code']) ?>"></td>
                            <td><input type="text" name="nom" class="form-control form-control-sm" value="<?= e($c['nom']) ?>"></td>
                            <td><input type="text" name="ville" class="form-control form-control-sm" value="<?= e($c['ville'] ?? '') ?>"></td>
                            <td><input type="checkbox" name="actif" <?= $c['actif'] ? 'checked' : '' ?>></td>
                            <td><button class="btn btn-sm btn-uatm">OK</button></td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

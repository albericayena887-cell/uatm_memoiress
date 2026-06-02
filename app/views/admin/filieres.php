<h1 class="h3 mb-4">Gestion des filières</h1>
<div class="row g-4">
    <div class="col-lg-4" data-aos="fade-right">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-uatm text-white">Ajouter une filière</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="create">
                    <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
                    <button class="btn btn-uatm w-100">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8" data-aos="fade-left">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Code</th><th>Nom</th><th>Statut</th><th>Modifier</th></tr></thead>
                    <tbody>
                    <?php foreach ($filieres as $f): ?>
                    <tr>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="update">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                            <td><input type="text" name="code" class="form-control form-control-sm" value="<?= e($f['code']) ?>"></td>
                            <td><input type="text" name="nom" class="form-control form-control-sm" value="<?= e($f['nom']) ?>"></td>
                            <td><input type="checkbox" name="actif" <?= $f['actif'] ? 'checked' : '' ?>></td>
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

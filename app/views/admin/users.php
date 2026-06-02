<h1 class="h3 mb-4"><i class="bi bi-people me-2"></i>Gestion des utilisateurs</h1>

<div class="d-flex justify-content-between mb-3">
    <form class="d-flex gap-2" method="get">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="action" value="users">
        <input type="search" name="q" class="form-control" placeholder="Rechercher..." value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn btn-uatm">Filtrer</button>
    </form>
    <div>
        <a href="<?= url('admin', ['action' => 'userImport']) ?>" class="btn btn-outline-uatm"><i class="bi bi-file-earmark-spreadsheet"></i> Import CSV</a>
        <a href="<?= url('admin', ['action' => 'userCreate']) ?>" class="btn btn-uatm"><i class="bi bi-plus-lg"></i> Nouveau</a>
    </div>
</div>

<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-uatm"><tr><th>Matricule</th><th>Nom</th><th>Email</th><th>Niveau</th><th>Rôle</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['matricule']) ?></td>
                <td><?= e($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge bg-light text-dark border"><?= e($u['niveau_etude'] ?: '—') ?></span></td>
                <td><span class="badge bg-secondary"><?= e($u['role_nom']) ?></span></td>
                <td><?= $u['actif'] ? '<span class="text-success">Actif</span>' : '<span class="text-danger">Inactif</span>' ?></td>
                <td class="text-nowrap">
                    <a href="<?= url('admin', ['action' => 'userEdit', 'id' => $u['id']]) ?>" class="btn btn-sm btn-outline-uatm">Modifier</a>
                    <?php if (in_array(strtoupper((string) ($u['niveau_etude'] ?? '')), ['L1', 'L2', 'M1'], true)): ?>
                    <form method="post" action="<?= url('admin', ['action' => 'userPromote']) ?>" class="d-inline" onsubmit="return confirm('Passer cet étudiant au niveau supérieur ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Promouvoir</button>
                    </form>
                    <?php endif; ?>
                    <?php if (($user['id'] ?? 0) !== (int) $u['id']): ?>
                    <form method="post" action="<?= url('admin', ['action' => 'userDelete']) ?>" class="d-inline" onsubmit="return confirm('Supprimer ce compte utilisateur ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

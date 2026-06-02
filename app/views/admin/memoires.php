<h1 class="h3 mb-4">Gestion des mémoires</h1>
<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-uatm"><tr><th>Titre</th><th>Auteur</th><th>Statut</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($memoires as $m): ?>
            <tr>
                <td><?= e(mb_strimwidth($m['titre'], 0, 60, '...')) ?></td>
                <td><?= e($m['auteur']) ?></td>
                <td><span class="badge bg-<?= statut_badge($m['statut']) ?>"><?= e(statut_label($m['statut'])) ?></span></td>
                <td><?= e(date('d/m/Y', strtotime($m['date_depot']))) ?></td>
                <td class="text-nowrap">
                    <a href="<?= url('memoires', ['action' => 'show', 'id' => $m['id']]) ?>" class="btn btn-sm btn-outline-uatm">Voir</a>
                    <form method="post" action="<?= url('admin', ['action' => 'memoireDelete']) ?>" class="d-inline" onsubmit="return confirm('Supprimer ce mémoire ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

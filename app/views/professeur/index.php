<h1 class="h3 mb-4"><i class="bi bi-check2-square me-2"></i>Validations</h1>

<?php if ($pending): ?>
<div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
    <div class="card-header bg-warning-subtle fw-semibold">En attente (<?= count($pending) ?>)</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Titre</th><th>Auteur</th><th>Date dépôt</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pending as $m): ?>
            <tr>
                <td><?= e($m['titre']) ?></td>
                <td><?= e($m['auteur']) ?></td>
                <td><?= e(date('d/m/Y', strtotime($m['date_depot']))) ?></td>
                <td><a href="<?= url('professeur', ['action' => 'validation', 'id' => $m['id']]) ?>" class="btn btn-sm btn-uatm">Examiner</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success" data-aos="fade-in">Aucun mémoire en attente de validation.</div>
<?php endif; ?>

<?php if ($validated): ?>
<div class="card border-0 shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white fw-semibold">Mémoires validés</div>
    <ul class="list-group list-group-flush">
        <?php foreach ($validated as $m): ?>
        <li class="list-group-item d-flex justify-content-between">
            <span><?= e($m['titre']) ?></span>
            <a href="<?= url('memoires', ['action' => 'show', 'id' => $m['id']]) ?>" class="btn btn-sm btn-outline-uatm">Consulter</a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

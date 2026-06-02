<h1 class="h3 mb-4"><i class="bi bi-heart me-2"></i>Mes favoris</h1>

<?php if ($favoris): ?>
<div class="row g-4">
    <?php foreach ($favoris as $i => $m): ?>
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= min($i * 50, 200) ?>">
        <div class="card memoire-card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6><?= e($m['titre']) ?></h6>
                <p class="small text-muted"><?= e($m['auteur']) ?> — <?= e($m['filiere_nom']) ?></p>
                <a href="<?= url('memoires', ['action' => 'show', 'id' => $m['id']]) ?>" class="btn btn-sm btn-uatm">Consulter</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">Aucun mémoire dans vos favoris.</div>
<?php endif; ?>

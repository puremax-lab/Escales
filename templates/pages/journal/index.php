<?php /** @var array $visits @var array $stats @var array $currentUser */ ?>
<div class="page-head">
    <div>
        <h1>Mon carnet</h1>
        <p class="muted">
            Votre page publique :
            <a href="/membres/<?= e(rawurlencode($currentUser['pseudo'])) ?>">/membres/<?= e($currentUser['pseudo']) ?></a>
        </p>
    </div>
    <a href="/carnet/ajouter" class="btn">+ Ajouter une escale</a>
</div>

<section class="stats" aria-label="Mes statistiques">
    <div class="stat"><strong><?= $stats['cities'] ?></strong><span><?= $stats['cities'] > 1 ? 'villes visitées' : 'ville visitée' ?></span></div>
    <div class="stat"><strong><?= $stats['countries'] ?></strong><span>pays</span></div>
    <div class="stat">
        <strong><?= $stats['average'] === null ? '—' : number_format($stats['average'], 1, ',', '') ?></strong>
        <span>note moyenne</span>
    </div>
</section>

<?php if ($visits): ?>
    <div class="visits">
        <?php foreach ($visits as $visit): ?>
            <?= $this->partial('partials/visit', ['visit' => $visit, 'showCity' => true, 'editable' => true]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty">
        <p>Votre carnet est vide pour l'instant.</p>
        <a href="/villes" class="btn btn-outline">Choisir une ville</a>
    </div>
<?php endif ?>

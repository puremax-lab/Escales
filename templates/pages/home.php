<?php /** @var array $stats @var array $topCities @var array $latestVisits @var ?array $currentUser */ ?>
<section class="hero">
    <p class="eyebrow">Le carnet de voyage partagé</p>
    <h1>Gardez une trace de chaque escale.</h1>
    <p class="lead">Notez les villes que vous avez visitées, donnez votre avis et découvrez où sont partis les autres voyageurs.</p>
    <div class="hero-actions">
        <?php if ($currentUser): ?>
            <a href="<?= e(url('/carnet/ajouter')) ?>" class="btn btn-large">+ Ajouter une escale</a>
        <?php else: ?>
            <a href="<?= e(url('/inscription')) ?>" class="btn btn-large">Créer mon carnet</a>
        <?php endif ?>
        <a href="<?= e(url('/villes')) ?>" class="btn btn-large btn-outline">Explorer les villes</a>
    </div>
</section>

<section class="stats" aria-label="Chiffres clés">
    <div class="stat"><strong><?= $stats['members'] ?></strong><span>voyageurs</span></div>
    <div class="stat"><strong><?= $stats['cities'] ?></strong><span>villes</span></div>
    <div class="stat"><strong><?= $stats['visits'] ?></strong><span>escales notées</span></div>
</section>

<section>
    <div class="section-head">
        <h2>Les villes les plus visitées</h2>
        <a href="<?= e(url('/villes')) ?>">Toutes les villes →</a>
    </div>
    <div class="grid">
        <?php foreach ($topCities as $city): ?>
            <?= $this->partial('partials/city-card', ['city' => $city]) ?>
        <?php endforeach ?>
    </div>
</section>

<section>
    <div class="section-head">
        <h2>Dernières escales</h2>
    </div>
    <div class="visits">
        <?php foreach ($latestVisits as $visit): ?>
            <?= $this->partial('partials/visit', ['visit' => $visit, 'showCity' => true, 'showAuthor' => true]) ?>
        <?php endforeach ?>
    </div>
</section>

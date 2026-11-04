<?php /** @var array $stats @var array $topCities @var array $latestVisits @var ?array $currentUser */ ?>
<section class="hero">
    <p class="eyebrow"><?= e(t('Le carnet de voyage partagé')) ?></p>
    <h1><?= e(t('Gardez une trace de chaque escale.')) ?></h1>
    <p class="lead"><?= e(t('Notez les villes que vous avez visitées, donnez votre avis et découvrez où sont partis les autres voyageurs.')) ?></p>
    <div class="hero-actions">
        <?php if ($currentUser): ?>
            <a href="<?= e(url('/carnet/ajouter')) ?>" class="btn btn-large"><?= e(t('+ Ajouter une escale')) ?></a>
        <?php else: ?>
            <a href="<?= e(url('/inscription')) ?>" class="btn btn-large"><?= e(t('Créer mon carnet')) ?></a>
        <?php endif ?>
        <a href="<?= e(url('/villes')) ?>" class="btn btn-large btn-outline"><?= e(t('Explorer les villes')) ?></a>
    </div>
</section>

<section class="stats" aria-label="<?= e(t('Chiffres clés')) ?>">
    <div class="stat"><strong><?= $stats['members'] ?></strong><span><?= e(t('voyageurs')) ?></span></div>
    <div class="stat"><strong><?= $stats['cities'] ?></strong><span><?= e(t('villes')) ?></span></div>
    <div class="stat"><strong><?= $stats['visits'] ?></strong><span><?= e(t('escales notées')) ?></span></div>
</section>

<section>
    <div class="section-head">
        <h2><?= e(t('Les villes les plus visitées')) ?></h2>
        <a href="<?= e(url('/villes')) ?>"><?= e(t('Toutes les villes →')) ?></a>
    </div>
    <div class="grid">
        <?php foreach ($topCities as $city): ?>
            <?= $this->partial('partials/city-card', ['city' => $city]) ?>
        <?php endforeach ?>
    </div>
</section>

<section>
    <div class="section-head">
        <h2><?= e(t('Dernières escales')) ?></h2>
    </div>
    <div class="visits">
        <?php foreach ($latestVisits as $visit): ?>
            <?= $this->partial('partials/visit', ['visit' => $visit, 'showCity' => true, 'showAuthor' => true]) ?>
        <?php endforeach ?>
    </div>
</section>

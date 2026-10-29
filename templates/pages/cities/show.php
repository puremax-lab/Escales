<?php /** @var array $city @var array $visits @var bool $inJournal @var ?array $currentUser */ ?>
<a href="<?= e(url('/villes')) ?>" class="back">← Toutes les villes</a>

<div class="city-hero">
    <span class="country-code country-code-large" aria-hidden="true"><?= e($city['country_code']) ?></span>
    <div>
        <h1><?= e($city['name']) ?></h1>
        <p class="muted">
            <a href="<?= e(url('/villes?pays=')) ?><?= $city['country_id'] ?>"><?= e($city['country']) ?></a><?= $city['is_capital'] ? ' · capitale' : '' ?>
        </p>
    </div>
    <div class="city-hero-action">
        <?php if ($inJournal): ?>
            <span class="badge">✓ Dans votre carnet</span>
        <?php else: ?>
            <a href="<?= e(url('/carnet/ajouter?ville=')) ?><?= $city['id'] ?>" class="btn">+ J'y suis allé</a>
        <?php endif ?>
    </div>
</div>

<section class="stats stats-2">
    <div class="stat"><strong><?= $city['visit_count'] ?></strong><span><?= $city['visit_count'] > 1 ? 'voyageurs' : 'voyageur' ?></span></div>
    <div class="stat">
        <strong><?= $city['visit_count'] ? number_format((float) $city['average_rating'], 1, ',', '') . ' / 5' : '—' ?></strong>
        <span>note moyenne</span>
    </div>
</section>

<h2>Avis des voyageurs</h2>
<?php if ($visits): ?>
    <div class="visits">
        <?php foreach ($visits as $visit): ?>
            <?= $this->partial('partials/visit', ['visit' => $visit, 'showAuthor' => true]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty">Personne n'a encore noté <?= e($city['name']) ?>. Soyez le premier !</div>
<?php endif ?>

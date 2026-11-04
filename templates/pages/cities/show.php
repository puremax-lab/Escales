<?php /** @var array $city @var array $visits @var bool $inJournal @var ?array $currentUser */ ?>
<a href="<?= e(url('/villes')) ?>" class="back"><?= e(t('← Toutes les villes')) ?></a>

<div class="city-hero">
    <span class="country-code country-code-large" aria-hidden="true"><?= e($city['country_code']) ?></span>
    <div>
        <h1><?= e(t($city['name'])) ?></h1>
        <p class="muted">
            <?= $city['region'] !== '' ? e($city['region']) . ' · ' : '' ?><a href="<?= e(url('/villes?pays=')) ?><?= $city['country_id'] ?>"><?= e(t($city['country'])) ?></a><?= $city['is_capital'] ? ' · ' . e(t('capitale')) : '' ?>
        </p>
    </div>
    <div class="city-hero-action">
        <?php if ($inJournal): ?>
            <span class="badge"><?= e(t('✓ Dans votre carnet')) ?></span>
        <?php else: ?>
            <a href="<?= e(url('/carnet/ajouter?ville=')) ?><?= $city['id'] ?>" class="btn"><?= e(t("+ J'y suis allé")) ?></a>
        <?php endif ?>
    </div>
</div>

<section class="stats stats-2">
    <div class="stat"><strong><?= $city['visit_count'] ?></strong><span><?= e(t($city['visit_count'] > 1 ? 'voyageurs' : 'voyageur')) ?></span></div>
    <div class="stat">
        <strong><?= $city['visit_count'] ? decimal((float) $city['average_rating']) . ' / 5' : '—' ?></strong>
        <span><?= e(t('note moyenne')) ?></span>
    </div>
</section>

<h2><?= e(t('Avis des voyageurs')) ?></h2>
<?php if ($visits): ?>
    <div class="visits">
        <?php foreach ($visits as $visit): ?>
            <?= $this->partial('partials/visit', ['visit' => $visit, 'showAuthor' => true]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty"><?= e(t("Personne n'a encore noté %s. Soyez le premier !", t($city['name']))) ?></div>
<?php endif ?>

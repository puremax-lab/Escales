<?php /** @var array $cities @var array $countries @var string $query @var ?int $countryId @var int $total @var int $page @var int $pages */ ?>
<div class="page-head">
    <h1>Villes</h1>
    <p class="muted"><?= plural($total, 'ville') ?></p>
</div>

<form method="get" action="<?= e(url('/villes')) ?>" class="filters" role="search">
    <input type="search" name="q" value="<?= e($query) ?>" placeholder="Une ville ou un pays…" aria-label="Rechercher une ville ou un pays">
    <select name="pays" aria-label="Filtrer par pays">
        <option value="">Tous les pays</option>
        <?php foreach ($countries as $country): ?>
            <option value="<?= $country['id'] ?>"<?= $countryId === $country['id'] ? ' selected' : '' ?>>
                <?= e($country['name']) ?> (<?= $country['city_count'] ?>)
            </option>
        <?php endforeach ?>
    </select>
    <button type="submit" class="btn">Rechercher</button>
    <?php if ($query !== '' || $countryId): ?>
        <a href="<?= e(url('/villes')) ?>" class="btn btn-ghost">Effacer</a>
    <?php endif ?>
</form>

<?php if ($cities): ?>
    <div class="grid" id="city-grid">
        <?php foreach ($cities as $city): ?>
            <?= $this->partial('partials/city-card', ['city' => $city]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty">
        <p>Aucune ville ne correspond à votre recherche.</p>
        <?php if ($query !== ''): ?>
            <a href="<?= e(url('/carnet/ajouter?' . http_build_query(['q' => $query]))) ?>" class="btn btn-outline">Ajouter « <?= e($query) ?> » à mon carnet</a>
        <?php endif ?>
    </div>
<?php endif ?>

<?php if ($page < $pages): ?>
    <?php $next = http_build_query(array_filter(['q' => $query, 'pays' => $countryId, 'page' => $page + 1])) ?>
    <p class="load-more">
        <a href="<?= e(url('/villes')) ?>?<?= e($next) ?>" class="btn btn-outline" data-load-more="#city-grid">Charger la suite</a>
    </p>
    <script src="<?= e(url('/assets/load-more.js')) ?>" defer></script>
<?php endif ?>

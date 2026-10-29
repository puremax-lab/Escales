<?php /** @var array $cities @var array $countries @var string $query @var ?int $countryId @var int $total @var int $page @var int $pages */ ?>
<div class="page-head">
    <h1>Villes</h1>
    <p class="muted"><?= plural($total, 'ville') ?></p>
</div>

<form method="get" action="/villes" class="filters" role="search">
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
        <a href="/villes" class="btn btn-ghost">Effacer</a>
    <?php endif ?>
</form>

<?php if ($cities): ?>
    <div class="grid">
        <?php foreach ($cities as $city): ?>
            <?= $this->partial('partials/city-card', ['city' => $city]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty">Aucune ville ne correspond à votre recherche.</div>
<?php endif ?>

<?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Pages">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php $params = http_build_query(array_filter(['q' => $query, 'pays' => $countryId, 'page' => $i > 1 ? $i : null])) ?>
            <a href="/villes<?= $params ? '?' . e($params) : '' ?>"<?= $i === $page ? ' class="active" aria-current="page"' : '' ?>><?= $i ?></a>
        <?php endfor ?>
    </nav>
<?php endif ?>

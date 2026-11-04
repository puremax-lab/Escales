<?php /** @var array $cities @var array $countries @var string $query @var ?int $countryId @var int $total @var int $page @var int $pages @var list<?int> $pageLinks */ ?>
<div class="page-head">
    <h1><?= e(t('Villes')) ?></h1>
    <p class="muted"><?= plural($total, 'ville') ?></p>
</div>

<form method="get" action="<?= e(url('/villes')) ?>" class="filters" role="search">
    <input type="search" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('Une ville ou un pays…')) ?>" aria-label="<?= e(t('Rechercher une ville ou un pays')) ?>">
    <select name="pays" aria-label="<?= e(t('Filtrer par pays')) ?>">
        <option value=""><?= e(t('Tous les pays')) ?></option>
        <?php foreach ($countries as $country): ?>
            <option value="<?= $country['id'] ?>"<?= $countryId === $country['id'] ? ' selected' : '' ?>>
                <?= e(t($country['name'])) ?> (<?= $country['city_count'] ?>)
            </option>
        <?php endforeach ?>
    </select>
    <button type="submit" class="btn"><?= e(t('Rechercher')) ?></button>
    <?php if ($query !== '' || $countryId): ?>
        <a href="<?= e(url('/villes')) ?>" class="btn btn-ghost"><?= e(t('Effacer')) ?></a>
    <?php endif ?>
</form>

<?php if ($cities): ?>
    <div class="grid">
        <?php foreach ($cities as $city): ?>
            <?= $this->partial('partials/city-card', ['city' => $city]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty">
        <p><?= e(t('Aucune ville ne correspond à votre recherche.')) ?></p>
        <?php if ($query !== ''): ?>
            <a href="<?= e(url('/carnet/ajouter?' . http_build_query(['q' => $query]))) ?>" class="btn btn-outline"><?= e(t('Ajouter « %s » à mon carnet', $query)) ?></a>
        <?php endif ?>
    </div>
<?php endif ?>

<?php if ($pages > 1): ?>
    <?php $pageUrl = fn (int $n) => url('/villes') . (($params = http_build_query(array_filter(['q' => $query, 'pays' => $countryId, 'page' => $n > 1 ? $n : null]))) ? '?' . $params : '') ?>
    <nav class="pagination" aria-label="<?= e(t('Pages')) ?>">
        <?php if ($page > 1): ?>
            <a href="<?= e($pageUrl($page - 1)) ?>" rel="prev" aria-label="<?= e(t('Page précédente')) ?>">←<span class="page-word"> <?= e(t('Précédent')) ?></span></a>
        <?php endif ?>
        <?php foreach ($pageLinks as $n): ?>
            <?php if ($n === null): ?>
                <span class="gap" aria-hidden="true">…</span>
            <?php elseif ($n === $page): ?>
                <a href="<?= e($pageUrl($n)) ?>" class="active" aria-current="page"><?= $n ?></a>
            <?php else: ?>
                <a href="<?= e($pageUrl($n)) ?>"><?= $n ?></a>
            <?php endif ?>
        <?php endforeach ?>
        <?php if ($page < $pages): ?>
            <a href="<?= e($pageUrl($page + 1)) ?>" rel="next" aria-label="<?= e(t('Page suivante')) ?>"><span class="page-word"><?= e(t('Suivant')) ?> </span>→</a>
        <?php endif ?>
    </nav>
<?php endif ?>

<?php /** @var array $visits @var array $stats @var array $currentUser */ ?>
<div class="page-head">
    <div>
        <h1><?= e(t('Mon carnet')) ?></h1>
        <p class="muted">
            <?= e(t('Votre page publique :')) ?>
            <a href="<?= e(url('/membres/')) ?><?= e(rawurlencode($currentUser['pseudo'])) ?>">/membres/<?= e($currentUser['pseudo']) ?></a>
        </p>
    </div>
    <a href="<?= e(url('/carnet/ajouter')) ?>" class="btn"><?= e(t('+ Ajouter une escale')) ?></a>
</div>

<section class="stats" aria-label="<?= e(t('Mes statistiques')) ?>">
    <div class="stat"><strong><?= $stats['cities'] ?></strong><span><?= e(t($stats['cities'] > 1 ? 'villes visitées' : 'ville visitée')) ?></span></div>
    <div class="stat"><strong><?= $stats['countries'] ?></strong><span><?= App\Core\Lang::$locale === 'en' && $stats['countries'] === 1 ? 'country' : e(t('pays')) ?></span></div>
    <div class="stat">
        <strong><?= $stats['average'] === null ? '—' : decimal($stats['average']) ?></strong>
        <span><?= e(t('note moyenne')) ?></span>
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
        <p><?= e(t("Votre carnet est vide pour l'instant.")) ?></p>
        <a href="<?= e(url('/villes')) ?>" class="btn btn-outline"><?= e(t('Choisir une ville')) ?></a>
    </div>
<?php endif ?>

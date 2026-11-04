<?php /** @var array $visit @var string $csrfToken */ ?>
<a href="<?= e(url('/carnet')) ?>" class="back"><?= e(t('← Mon carnet')) ?></a>

<div class="card form form-narrow confirm">
    <h1><?= e(t('Retirer %s de votre carnet ?', t($visit['city']))) ?></h1>
    <p class="muted">
        <?= e(t('Visité le %s, noté %d sur 5.', format_date($visit['visited_on']), $visit['rating'])) ?>
        <?= e(t('Votre avis sera supprimé définitivement.')) ?>
    </p>

    <form method="post" action="<?= e(url('/carnet/')) ?><?= $visit['id'] ?>/supprimer" class="form-actions">
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <button type="submit" class="btn btn-danger-solid"><?= e(t('Oui, retirer')) ?></button>
        <a href="<?= e(url('/carnet')) ?>" class="btn btn-ghost"><?= e(t('Annuler')) ?></a>
    </form>
</div>

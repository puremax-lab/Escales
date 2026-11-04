<?php
/**
 * One journal entry.
 * @var array $visit @var bool $showAuthor @var bool $showCity @var bool $editable
 */
?>
<article class="visit">
    <div class="visit-head">
        <div>
            <?php if (!empty($showCity)): ?>
                <a href="<?= e(url('/villes/')) ?><?= $visit['city_id'] ?>" class="visit-city">
                    <?= e(t($visit['city'])) ?>
                </a>
                <span class="muted small"><?= e(t($visit['country'])) ?></span>
            <?php endif ?>
            <?php if (!empty($showAuthor)): ?>
                <a href="<?= e(url('/membres/')) ?><?= e(rawurlencode($visit['pseudo'])) ?>" class="visit-author">@<?= e($visit['pseudo']) ?></a>
            <?php endif ?>
        </div>
        <span class="stars" aria-label="<?= e(t('%d sur 5', $visit['rating'])) ?>"><?= stars($visit['rating']) ?></span>
    </div>
    <?php if ($visit['note'] !== ''): ?>
        <p class="visit-note"><?= nl2br(e(t($visit['note']))) ?></p>
    <?php endif ?>
    <div class="visit-foot">
        <span class="muted small"><?= e(t('Visité le %s', format_date($visit['visited_on']))) ?></span>
        <?php if (!empty($editable)): ?>
            <div class="visit-actions">
                <a href="<?= e(url('/carnet/')) ?><?= $visit['id'] ?>/modifier" class="btn btn-ghost btn-small"><?= e(t('Modifier')) ?></a>
                <a href="<?= e(url('/carnet/')) ?><?= $visit['id'] ?>/supprimer" class="btn btn-ghost btn-small btn-danger"><?= e(t('Supprimer')) ?></a>
            </div>
        <?php endif ?>
    </div>
</article>

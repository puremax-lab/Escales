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
                    <?= e($visit['city']) ?>
                </a>
                <span class="muted small"><?= e($visit['country']) ?></span>
            <?php endif ?>
            <?php if (!empty($showAuthor)): ?>
                <a href="<?= e(url('/membres/')) ?><?= e(rawurlencode($visit['pseudo'])) ?>" class="visit-author">@<?= e($visit['pseudo']) ?></a>
            <?php endif ?>
        </div>
        <span class="stars" aria-label="<?= $visit['rating'] ?> sur 5"><?= stars($visit['rating']) ?></span>
    </div>
    <?php if ($visit['note'] !== ''): ?>
        <p class="visit-note"><?= nl2br(e($visit['note'])) ?></p>
    <?php endif ?>
    <div class="visit-foot">
        <span class="muted small">Visité le <?= format_date($visit['visited_on']) ?></span>
        <?php if (!empty($editable)): ?>
            <div class="visit-actions">
                <a href="<?= e(url('/carnet/')) ?><?= $visit['id'] ?>/modifier" class="btn btn-ghost btn-small">Modifier</a>
                <a href="<?= e(url('/carnet/')) ?><?= $visit['id'] ?>/supprimer" class="btn btn-ghost btn-small btn-danger">Supprimer</a>
            </div>
        <?php endif ?>
    </div>
</article>

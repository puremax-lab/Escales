<?php /** @var array $city */ ?>
<a href="<?= e(url('/villes/')) ?><?= $city['id'] ?>" class="card city-card">
    <span class="country-code" aria-hidden="true"><?= e($city['country_code']) ?></span>
    <div>
        <h3><?= e(t($city['name'])) ?></h3>
        <p class="muted small">
            <?= $city['region'] !== '' ? e($city['region']) . ' · ' : '' ?><?= e(t($city['country'])) ?><?= $city['is_capital'] ? ' · ' . e(t('capitale')) : '' ?>
        </p>
    </div>
    <div class="city-card-stats">
        <?php if ($city['visit_count'] > 0): ?>
            <span class="stars small" aria-label="<?= e(t('Note moyenne %s sur 5', decimal((float) $city['average_rating']))) ?>">★ <?= decimal((float) $city['average_rating']) ?></span>
            <span class="muted small"><?= plural((int) $city['visit_count'], 'visite') ?></span>
        <?php else: ?>
            <span class="muted small"><?= e(t('Pas encore visitée')) ?></span>
        <?php endif ?>
    </div>
</a>

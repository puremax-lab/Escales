<?php /** @var array $city */ ?>
<a href="<?= e(url('/villes/')) ?><?= $city['id'] ?>" class="card city-card">
    <span class="country-code" aria-hidden="true"><?= e($city['country_code']) ?></span>
    <div>
        <h3><?= e($city['name']) ?></h3>
        <p class="muted small">
            <?= e($city['country']) ?><?= $city['is_capital'] ? ' · capitale' : '' ?>
        </p>
    </div>
    <div class="city-card-stats">
        <?php if ($city['visit_count'] > 0): ?>
            <span class="stars small" aria-label="Note moyenne <?= number_format((float) $city['average_rating'], 1, ',', '') ?> sur 5">★ <?= number_format((float) $city['average_rating'], 1, ',', '') ?></span>
            <span class="muted small"><?= plural((int) $city['visit_count'], 'visite') ?></span>
        <?php else: ?>
            <span class="muted small">Pas encore visitée</span>
        <?php endif ?>
    </div>
</a>

<?php /** @var array $member @var array $visits @var array $stats @var bool $isMe */ ?>
<div class="profile">
    <span class="avatar avatar-large" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($member['first_name'], 0, 1))) ?></span>
    <div class="profile-info">
        <h1><?= e($member['first_name'] . ' ' . $member['last_name']) ?></h1>
        <p class="muted">@<?= e($member['pseudo']) ?> · <?= e(t('inscrit depuis le %s', format_date($member['created_at']))) ?></p>
        <ul class="profile-facts">
            <li><span><?= e(t('Ville')) ?></span><?= e(t($member['city'])) ?>, <?= e(t($member['country'])) ?></li>
            <?php if ($member['nationality'] !== ''): ?><li><span><?= e(t('Nationalité')) ?></span><?= e(t($member['nationality'])) ?></li><?php endif ?>
            <li><span><?= e(t('Âge')) ?></span><?= e(t('%d ans', age($member['birth_date']))) ?></li>
        </ul>
    </div>
    <?php if ($isMe): ?>
        <a href="<?= e(url('/profil/modifier')) ?>" class="btn btn-outline btn-small"><?= e(t('Modifier mon profil')) ?></a>
    <?php endif ?>
</div>

<section class="stats" aria-label="<?= e(t('Statistiques')) ?>">
    <div class="stat"><strong><?= $stats['cities'] ?></strong><span><?= e(t($stats['cities'] > 1 ? 'villes visitées' : 'ville visitée')) ?></span></div>
    <div class="stat"><strong><?= $stats['countries'] ?></strong><span><?= App\Core\Lang::$locale === 'en' && $stats['countries'] === 1 ? 'country' : e(t('pays')) ?></span></div>
    <div class="stat">
        <strong><?= $stats['average'] === null ? '—' : decimal($stats['average']) ?></strong>
        <span><?= e(t('note moyenne')) ?></span>
    </div>
</section>

<h2><?= e(t('Carnet de voyage')) ?></h2>
<?php if ($visits): ?>
    <div class="visits">
        <?php foreach ($visits as $visit): ?>
            <?= $this->partial('partials/visit', ['visit' => $visit, 'showCity' => true, 'editable' => $isMe]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty"><?= e(t("Aucune escale pour l'instant.")) ?></div>
<?php endif ?>

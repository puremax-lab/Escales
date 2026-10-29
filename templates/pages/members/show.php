<?php /** @var array $member @var array $visits @var array $stats @var bool $isMe */ ?>
<div class="profile">
    <span class="avatar avatar-large" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($member['first_name'], 0, 1))) ?></span>
    <div class="profile-info">
        <h1><?= e($member['first_name'] . ' ' . $member['last_name']) ?></h1>
        <p class="muted">@<?= e($member['pseudo']) ?> · inscrit depuis le <?= format_date($member['created_at']) ?></p>
        <ul class="profile-facts">
            <li><span>Ville</span><?= e($member['city']) ?>, <?= e($member['country']) ?></li>
            <li><span>Nationalité</span><?= e($member['nationality']) ?></li>
            <li><span>Âge</span><?= age($member['birth_date']) ?> ans</li>
        </ul>
    </div>
    <?php if ($isMe): ?>
        <a href="/profil/modifier" class="btn btn-outline btn-small">Modifier mon profil</a>
    <?php endif ?>
</div>

<section class="stats" aria-label="Statistiques">
    <div class="stat"><strong><?= $stats['cities'] ?></strong><span><?= $stats['cities'] > 1 ? 'villes visitées' : 'ville visitée' ?></span></div>
    <div class="stat"><strong><?= $stats['countries'] ?></strong><span>pays</span></div>
    <div class="stat">
        <strong><?= $stats['average'] === null ? '—' : number_format($stats['average'], 1, ',', '') ?></strong>
        <span>note moyenne</span>
    </div>
</section>

<h2>Carnet de voyage</h2>
<?php if ($visits): ?>
    <div class="visits">
        <?php foreach ($visits as $visit): ?>
            <?= $this->partial('partials/visit', ['visit' => $visit, 'showCity' => true, 'editable' => $isMe]) ?>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty">Aucune escale pour l'instant.</div>
<?php endif ?>

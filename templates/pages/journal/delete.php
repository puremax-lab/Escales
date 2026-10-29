<?php /** @var array $visit @var string $csrfToken */ ?>
<a href="/carnet" class="back">← Mon carnet</a>

<div class="card form form-narrow confirm">
    <h1>Retirer <?= e($visit['city']) ?> de votre carnet ?</h1>
    <p class="muted">
        Visité le <?= format_date($visit['visited_on']) ?>, noté <?= $visit['rating'] ?> sur 5.
        Votre avis sera supprimé définitivement.
    </p>

    <form method="post" action="/carnet/<?= $visit['id'] ?>/supprimer" class="form-actions">
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <button type="submit" class="btn btn-danger-solid">Oui, retirer</button>
        <a href="/carnet" class="btn btn-ghost">Annuler</a>
    </form>
</div>

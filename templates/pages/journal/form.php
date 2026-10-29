<?php /** @var ?array $visit @var array $old @var array $errors @var array $cityGroups @var string $title @var string $csrfToken */
$value = fn (string $field) => e(is_scalar($old[$field] ?? null) ? (string) $old[$field] : '');
$action = $visit ? '/carnet/' . $visit['id'] . '/modifier' : '/carnet/ajouter';
?>
<a href="<?= e(url('/carnet')) ?>" class="back">← Mon carnet</a>
<h1><?= e($title) ?></h1>

<form method="post" action="<?= e(url($action)) ?>" class="card form form-narrow" novalidate>
    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">

    <div class="field">
        <label for="city_id">Ville</label>
        <?= $this->partial('partials/city-select', [
            'cityGroups' => $cityGroups,
            'name' => 'city_id',
            'selected' => $old['city_id'] ?? '',
            'placeholder' => 'Choisir une ville',
            'errors' => $errors,
        ]) ?>
    </div>

    <div class="field-row">
        <div class="field">
            <label for="visited_on">Date de la visite</label>
            <input id="visited_on" name="visited_on" type="date" max="<?= date('Y-m-d') ?>" value="<?= $value('visited_on') ?>" required<?= invalid_attrs($errors, 'visited_on') ?>>
            <?= field_error($errors, 'visited_on') ?>
        </div>
        <fieldset class="field rating-field">
            <legend>Note</legend>
            <div class="rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="rating-<?= $i ?>" name="rating" value="<?= $i ?>"<?= (string) ($old['rating'] ?? '') === (string) $i ? ' checked' : '' ?>>
                    <label for="rating-<?= $i ?>" title="<?= $i ?> sur 5"><span class="sr-only"><?= $i ?> sur 5</span>★</label>
                <?php endfor ?>
            </div>
            <?= field_error($errors, 'rating') ?>
        </fieldset>
    </div>

    <div class="field">
        <label for="note">Votre avis <span class="muted">(facultatif)</span></label>
        <textarea id="note" name="note" rows="4" maxlength="500" placeholder="Ce que vous avez aimé, un conseil…"<?= invalid_attrs($errors, 'note') ?>><?= $value('note') ?></textarea>
        <p class="hint">500 caractères maximum.</p>
        <?= field_error($errors, 'note') ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Enregistrer</button>
        <a href="<?= e(url('/carnet')) ?>" class="btn btn-ghost">Annuler</a>
    </div>
</form>

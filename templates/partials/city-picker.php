<?php
/**
 * Home city of the sign-up and profile forms: a search box, then the cities found, to tick.
 * Works without JavaScript: "Chercher" sends the form again with find_city=1.
 * @var array $cityField @var array $errors
 */
?>
<div class="field">
    <label for="city_q"><?= e(t('Ville où vous habitez')) ?></label>
    <div class="search-row">
        <input id="city_q" name="city_q" type="search" value="<?= e($cityField['query']) ?>" placeholder="<?= e(t('Bruxelles')) ?>, Dakar, Montréal…"<?= invalid_attrs($errors, 'city') ?>>
        <button type="submit" name="find_city" value="1" class="btn btn-outline" formnovalidate><?= e(t('Chercher')) ?></button>
    </div>

    <?php if ($cityField['options']): ?>
        <fieldset class="place-choices">
            <legend class="sr-only"><?= e(t('Choisissez votre ville')) ?></legend>
            <?php foreach ($cityField['options'] as $option): ?>
                <label class="place-choice">
                    <input type="radio" name="city" value="<?= e($option['value']) ?>"<?= $option['value'] === $cityField['checked'] ? ' checked' : '' ?>>
                    <span><strong><?= e($option['name']) ?></strong> <span class="muted small"><?= e($option['detail']) ?></span></span>
                </label>
            <?php endforeach ?>
        </fieldset>
    <?php elseif ($cityField['searched']): ?>
        <p class="hint"><?= e(t("Aucune ville trouvée pour « %s ». Vérifiez l'orthographe.", $cityField['query'])) ?></p>
    <?php else: ?>
        <p class="hint"><?= t('Tapez le nom de votre ville, puis « Chercher » : des villes du monde entier, grâce à %s.', '<a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>') ?></p>
    <?php endif ?>
    <?php if ($cityField['unavailable']): ?>
        <p class="hint"><?= e(t("La recherche mondiale ne répond pas pour le moment : seules les villes déjà notées sur Escales s'affichent.")) ?></p>
    <?php endif ?>
    <?= field_error($errors, 'city') ?>
</div>

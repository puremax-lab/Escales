<?php /** @var array $old @var array $errors @var array $cityField @var array $currentUser @var string $csrfToken */
$value = fn (string $field) => e(is_scalar($old[$field] ?? null) ? (string) $old[$field] : '');
?>
<a href="<?= e(url('/membres/')) ?><?= e(rawurlencode($currentUser['pseudo'])) ?>" class="back"><?= e(t('← Mon profil')) ?></a>
<h1><?= e(t('Modifier mon profil')) ?></h1>

<form method="post" action="<?= e(url('/profil/modifier')) ?>" class="card form form-narrow" novalidate>
    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">

    <div class="field-row">
        <div class="field">
            <label for="first_name"><?= e(t('Prénom')) ?></label>
            <input id="first_name" name="first_name" value="<?= $value('first_name') ?>" required<?= invalid_attrs($errors, 'first_name') ?>>
            <?= field_error($errors, 'first_name') ?>
        </div>
        <div class="field">
            <label for="last_name"><?= e(t('Nom')) ?></label>
            <input id="last_name" name="last_name" value="<?= $value('last_name') ?>" required<?= invalid_attrs($errors, 'last_name') ?>>
            <?= field_error($errors, 'last_name') ?>
        </div>
    </div>

    <div class="field">
        <label for="birth_date"><?= e(t('Date de naissance')) ?></label>
        <input id="birth_date" name="birth_date" type="date" value="<?= $value('birth_date') ?>" required<?= invalid_attrs($errors, 'birth_date') ?>>
        <?= field_error($errors, 'birth_date') ?>
    </div>

    <?= $this->partial('partials/city-picker', ['cityField' => $cityField, 'errors' => $errors]) ?>

    <p class="hint"><?= e(t('Le pseudo ne peut pas être modifié.')) ?></p>

    <div class="form-actions">
        <button type="submit" class="btn"><?= e(t('Enregistrer')) ?></button>
        <a href="<?= e(url('/membres/')) ?><?= e(rawurlencode($currentUser['pseudo'])) ?>" class="btn btn-ghost"><?= e(t('Annuler')) ?></a>
    </div>
</form>

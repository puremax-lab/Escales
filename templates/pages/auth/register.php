<?php /** @var array $old @var array $errors @var array $cityField @var string $csrfToken */
$value = fn (string $field) => e(is_string($old[$field] ?? null) ? $old[$field] : '');
?>
<div class="auth auth-wide">
    <h1><?= e(t('Créer mon carnet')) ?></h1>
    <p class="muted"><?= e(t("Gratuit, en moins d'une minute.")) ?></p>

    <form method="post" action="<?= e(url('/inscription')) ?>" class="card form" novalidate>
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">

        <div class="field-row">
            <div class="field">
                <label for="first_name"><?= e(t('Prénom')) ?></label>
                <input id="first_name" name="first_name" value="<?= $value('first_name') ?>" autocomplete="given-name" required<?= invalid_attrs($errors, 'first_name') ?>>
                <?= field_error($errors, 'first_name') ?>
            </div>
            <div class="field">
                <label for="last_name"><?= e(t('Nom')) ?></label>
                <input id="last_name" name="last_name" value="<?= $value('last_name') ?>" autocomplete="family-name" required<?= invalid_attrs($errors, 'last_name') ?>>
                <?= field_error($errors, 'last_name') ?>
            </div>
        </div>

        <div class="field">
            <label for="pseudo"><?= e(t('Pseudo')) ?></label>
            <input id="pseudo" name="pseudo" value="<?= $value('pseudo') ?>" autocomplete="username" required<?= invalid_attrs($errors, 'pseudo') ?>>
            <p class="hint"><?= e(t('Visible par les autres voyageurs. Lettres, chiffres, - et _.')) ?></p>
            <?= field_error($errors, 'pseudo') ?>
        </div>

        <div class="field">
            <label for="birth_date"><?= e(t('Date de naissance')) ?></label>
            <input id="birth_date" name="birth_date" type="date" value="<?= $value('birth_date') ?>" required<?= invalid_attrs($errors, 'birth_date') ?>>
            <?= field_error($errors, 'birth_date') ?>
        </div>

        <?= $this->partial('partials/city-picker', ['cityField' => $cityField, 'errors' => $errors]) ?>

        <div class="field-row">
            <div class="field">
                <label for="password"><?= e(t('Mot de passe')) ?></label>
                <input id="password" name="password" type="password" autocomplete="new-password" required<?= invalid_attrs($errors, 'password') ?>>
                <p class="hint"><?= e(t('8 caractères minimum.')) ?></p>
                <?= field_error($errors, 'password') ?>
            </div>
            <div class="field">
                <label for="password_confirm"><?= e(t('Confirmation')) ?></label>
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required<?= invalid_attrs($errors, 'password_confirm') ?>>
                <?= field_error($errors, 'password_confirm') ?>
            </div>
        </div>

        <button type="submit" class="btn btn-block"><?= e(t('Créer mon compte')) ?></button>
    </form>
    <p class="center"><?= e(t('Déjà inscrit ?')) ?> <a href="<?= e(url('/connexion')) ?>"><?= e(t('Connectez-vous')) ?></a></p>
</div>

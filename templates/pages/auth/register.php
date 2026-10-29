<?php /** @var array $old @var array $errors @var array $cityGroups @var string $csrfToken */
$value = fn (string $field) => e(is_string($old[$field] ?? null) ? $old[$field] : '');
?>
<div class="auth auth-wide">
    <h1>Créer mon carnet</h1>
    <p class="muted">Gratuit, en moins d'une minute.</p>

    <form method="post" action="<?= e(url('/inscription')) ?>" class="card form" novalidate>
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">

        <div class="field-row">
            <div class="field">
                <label for="first_name">Prénom</label>
                <input id="first_name" name="first_name" value="<?= $value('first_name') ?>" autocomplete="given-name" required<?= invalid_attrs($errors, 'first_name') ?>>
                <?= field_error($errors, 'first_name') ?>
            </div>
            <div class="field">
                <label for="last_name">Nom</label>
                <input id="last_name" name="last_name" value="<?= $value('last_name') ?>" autocomplete="family-name" required<?= invalid_attrs($errors, 'last_name') ?>>
                <?= field_error($errors, 'last_name') ?>
            </div>
        </div>

        <div class="field">
            <label for="pseudo">Pseudo</label>
            <input id="pseudo" name="pseudo" value="<?= $value('pseudo') ?>" autocomplete="username" required<?= invalid_attrs($errors, 'pseudo') ?>>
            <p class="hint">Visible par les autres voyageurs. Lettres, chiffres, - et _.</p>
            <?= field_error($errors, 'pseudo') ?>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="birth_date">Date de naissance</label>
                <input id="birth_date" name="birth_date" type="date" value="<?= $value('birth_date') ?>" required<?= invalid_attrs($errors, 'birth_date') ?>>
                <?= field_error($errors, 'birth_date') ?>
            </div>
            <div class="field">
                <label for="city_id">Ville où vous habitez</label>
                <?= $this->partial('partials/city-select', [
                    'cityGroups' => $cityGroups,
                    'name' => 'city_id',
                    'selected' => $old['city_id'] ?? '',
                    'placeholder' => 'Choisir une ville',
                    'errors' => $errors,
                ]) ?>
            </div>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required<?= invalid_attrs($errors, 'password') ?>>
                <p class="hint">8 caractères minimum.</p>
                <?= field_error($errors, 'password') ?>
            </div>
            <div class="field">
                <label for="password_confirm">Confirmation</label>
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required<?= invalid_attrs($errors, 'password_confirm') ?>>
                <?= field_error($errors, 'password_confirm') ?>
            </div>
        </div>

        <button type="submit" class="btn btn-block">Créer mon compte</button>
    </form>
    <p class="center">Déjà inscrit ? <a href="<?= e(url('/connexion')) ?>">Connectez-vous</a></p>
</div>

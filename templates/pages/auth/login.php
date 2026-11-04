<?php /** @var string $pseudo @var ?string $error @var string $csrfToken */ ?>
<div class="auth">
    <h1><?= e(t('Connexion')) ?></h1>
    <p class="muted"><?= t('Compte de démonstration : %s', '<strong>camille</strong> / <strong>password</strong>') ?></p>

    <form method="post" action="<?= e(url('/connexion')) ?>" class="card form" novalidate>
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <?php if (!empty($error)): ?>
            <div class="flash flash-error" role="alert"><?= e($error) ?></div>
        <?php endif ?>
        <div class="field">
            <label for="pseudo"><?= e(t('Pseudo')) ?></label>
            <input id="pseudo" name="pseudo" value="<?= e($pseudo) ?>" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label for="password"><?= e(t('Mot de passe')) ?></label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-block"><?= e(t('Se connecter')) ?></button>
    </form>
    <p class="center"><?= e(t('Pas encore de compte ?')) ?> <a href="<?= e(url('/inscription')) ?>"><?= e(t('Inscrivez-vous')) ?></a></p>
</div>

<?php /** @var string $pseudo @var ?string $error @var string $csrfToken */ ?>
<div class="auth">
    <h1>Connexion</h1>
    <p class="muted">Compte de démonstration : <strong>camille</strong> / <strong>password</strong></p>

    <form method="post" action="/connexion" class="card form" novalidate>
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <?php if (!empty($error)): ?>
            <div class="flash flash-error" role="alert"><?= e($error) ?></div>
        <?php endif ?>
        <div class="field">
            <label for="pseudo">Pseudo</label>
            <input id="pseudo" name="pseudo" value="<?= e($pseudo) ?>" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-block">Se connecter</button>
    </form>
    <p class="center">Pas encore de compte ? <a href="/inscription">Inscrivez-vous</a></p>
</div>

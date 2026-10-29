<?php
/** @var string $content @var string $title @var ?array $currentUser @var string $csrfToken @var string $currentPath */
$active = fn (string $prefix) => ($prefix === '/' ? $currentPath === '/' : str_starts_with($currentPath, $prefix)) ? ' class="active" aria-current="page"' : '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Escales</title>
    <meta name="description" content="Escales, le carnet de voyage partagé : notez les villes que vous avez visitées et découvrez celles des autres voyageurs.">
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <a href="/" class="brand"><span class="brand-mark" aria-hidden="true">✈</span> Escales</a>
        <nav class="nav" aria-label="Navigation principale">
            <a href="/"<?= $active('/') ?>>Accueil</a>
            <a href="/villes"<?= $active('/villes') ?>>Villes</a>
            <?php if ($currentUser): ?>
                <a href="/carnet"<?= $active('/carnet') ?>>Mon carnet</a>
            <?php endif ?>
        </nav>
        <div class="account">
            <?php if ($currentUser): ?>
                <a href="/membres/<?= e(rawurlencode($currentUser['pseudo'])) ?>" class="account-name">
                    <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($currentUser['first_name'], 0, 1))) ?></span>
                    <span class="hide-mobile"><?= e($currentUser['pseudo']) ?></span>
                </a>
                <form method="post" action="/deconnexion">
                    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
                    <button type="submit" class="btn btn-ghost btn-small">Déconnexion</button>
                </form>
            <?php else: ?>
                <a href="/connexion" class="btn btn-ghost btn-small">Connexion</a>
                <a href="/inscription" class="btn btn-small">Inscription</a>
            <?php endif ?>
        </div>
    </div>
</header>

<main class="container page">
    <?= $this->partial('partials/flash') ?>
    <?= $content ?>
</main>

<footer class="footer">
    <div class="container footer-inner">
        <p>Escales · projet portfolio de puremax-lab · les données sont fictives</p>
        <nav class="footer-links" aria-label="Liens du pied de page">
            <a href="https://github.com/puremax-lab/Escales" target="_blank" rel="noopener">Code source</a>
            <a href="https://github.com/puremax-lab" target="_blank" rel="noopener">GitHub puremax-lab</a>
            <span>© <?= date('Y') ?></span>
        </nav>
    </div>
</footer>
</body>
</html>

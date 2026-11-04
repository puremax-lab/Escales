<?php
/** @var string $content @var string $title @var ?array $currentUser @var string $csrfToken @var string $currentPath @var string $currentUri */
$active = fn (string $prefix) => ($prefix === '/' ? $currentPath === '/' : str_starts_with($currentPath, $prefix)) ? ' class="active" aria-current="page"' : '';
?>
<!doctype html>
<html lang="<?= App\Core\Lang::$locale ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Escales</title>
    <meta name="description" content="<?= e(t('Escales, le carnet de voyage partagé : notez les villes que vous avez visitées et découvrez celles des autres voyageurs.')) ?>">
    <link rel="icon" href="<?= e(url('/assets/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(url('/assets/style.css')) ?>">
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <a href="<?= e(url('/')) ?>" class="brand"><span class="brand-mark" aria-hidden="true">✈</span> Escales</a>
        <nav class="nav" aria-label="<?= e(t('Navigation principale')) ?>">
            <a href="<?= e(url('/')) ?>"<?= $active('/') ?>><?= e(t('Accueil')) ?></a>
            <a href="<?= e(url('/villes')) ?>"<?= $active('/villes') ?>><?= e(t('Villes')) ?></a>
            <?php if ($currentUser): ?>
                <a href="<?= e(url('/carnet')) ?>"<?= $active('/carnet') ?>><?= e(t('Mon carnet')) ?></a>
            <?php endif ?>
        </nav>
        <nav class="lang-switch" aria-label="<?= e(t('Langue')) ?>">
            <?php foreach (App\Core\Lang::LOCALES as $locale): ?>
                <a href="<?= e(url('/langue/' . $locale . '?retour=' . rawurlencode($currentUri))) ?>" lang="<?= $locale ?>"<?= $locale === App\Core\Lang::$locale ? ' class="active" aria-current="true"' : '' ?>><?= strtoupper($locale) ?></a>
            <?php endforeach ?>
        </nav>
        <div class="account">
            <?php if ($currentUser): ?>
                <a href="<?= e(url('/membres/')) ?><?= e(rawurlencode($currentUser['pseudo'])) ?>" class="account-name">
                    <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($currentUser['first_name'], 0, 1))) ?></span>
                    <span class="hide-mobile"><?= e($currentUser['pseudo']) ?></span>
                </a>
                <form method="post" action="<?= e(url('/deconnexion')) ?>">
                    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
                    <button type="submit" class="btn btn-ghost btn-small"><?= e(t('Déconnexion')) ?></button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('/connexion')) ?>" class="btn btn-ghost btn-small"><?= e(t('Connexion')) ?></a>
                <a href="<?= e(url('/inscription')) ?>" class="btn btn-small"><?= e(t('Inscription')) ?></a>
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
        <p><?= e(t('Escales · projet portfolio de puremax-lab · les données sont fictives')) ?></p>
        <nav class="footer-links" aria-label="<?= e(t('Liens du pied de page')) ?>">
            <a href="https://github.com/puremax-lab/Escales" target="_blank" rel="noopener"><?= e(t('Code source')) ?></a>
            <a href="https://github.com/puremax-lab" target="_blank" rel="noopener">GitHub puremax-lab</a>
            <a href="<?= e(url('/mentions-legales')) ?>"><?= e(t('Mentions légales')) ?></a>
            <span>© <?= date('Y') ?></span>
        </nav>
    </div>
</footer>
</body>
</html>

<?php /** @var int $status @var string $title @var string $message */ ?>
<div class="error-page">
    <p class="error-code"><?= $status ?></p>
    <h1><?= e($title) ?></h1>
    <p class="muted"><?= e($message) ?></p>
    <a href="/" class="btn">Retour à l'accueil</a>
</div>

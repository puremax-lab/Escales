<?php /** One-time messages; read only when a page is really displayed. @var callable $takeFlashes */ ?>
<?php foreach ($takeFlashes() as $flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endforeach ?>

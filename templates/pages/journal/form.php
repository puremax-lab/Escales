<?php
/**
 * Two steps: find the city (search form), then rate it (visit form).
 * @var ?array $visit @var string $path @var ?array $chosen @var string $query @var ?array $search
 * @var array $old @var array $errors @var string $title @var string $csrfToken
 */
$value = fn (string $field) => e(is_scalar($old[$field] ?? null) ? (string) $old[$field] : '');
?>
<a href="<?= e(url('/carnet')) ?>" class="back">← Mon carnet</a>
<h1><?= e($title) ?></h1>

<?php if ($chosen === null): ?>
    <form method="get" action="<?= e(url($path)) ?>" class="card form form-narrow" role="search">
        <input type="hidden" name="changer" value="1">
        <div class="field">
            <label for="q">Quelle ville avez-vous visitée ?</label>
            <div class="search-row">
                <input id="q" name="q" type="search" value="<?= e($query) ?>" placeholder="Kyoto, Dakar, Valparaíso…" required autofocus<?= invalid_attrs($errors, 'city_id') ?>>
                <button type="submit" class="btn">Chercher</button>
            </div>
            <p class="hint">Toutes les villes du monde, grâce aux données d'<a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>.</p>
            <?= field_error($errors, 'city_id') ?>
        </div>

        <?php if ($search !== null): ?>
            <?php if ($search['results']): ?>
                <ul class="place-results">
                    <?php foreach ($search['results'] as $result): ?>
                        <li>
                            <a href="<?= e(url($result['href'])) ?>">
                                <strong><?= e($result['name']) ?></strong>
                                <span class="muted small"><?= e($result['detail']) ?></span>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php else: ?>
                <div class="empty">Aucune ville trouvée pour « <?= e($query) ?> ». Vérifiez l'orthographe.</div>
            <?php endif ?>
            <?php if ($search['unavailable']): ?>
                <p class="hint">La recherche mondiale ne répond pas pour le moment : seules les villes déjà notées sur Escales s'affichent.</p>
            <?php endif ?>
        <?php endif ?>
    </form>
<?php else: ?>
    <form method="post" action="<?= e(url($path)) ?>" class="card form form-narrow" novalidate>
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <?php foreach ($chosen['fields'] as $name => $fieldValue): ?>
            <input type="hidden" name="<?= e($name) ?>" value="<?= e($fieldValue) ?>">
        <?php endforeach ?>

        <div class="field">
            <span class="label">Ville</span>
            <p class="chosen-city">
                <span><strong><?= e($chosen['label']) ?></strong> <span class="muted small"><?= e($chosen['detail']) ?></span></span>
                <a href="<?= e(url($path . '?changer=1')) ?>">Changer de ville</a>
            </p>
            <?= field_error($errors, 'city_id') ?>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="visited_on">Date de la visite</label>
                <input id="visited_on" name="visited_on" type="date" max="<?= date('Y-m-d') ?>" value="<?= $value('visited_on') ?>" required<?= invalid_attrs($errors, 'visited_on') ?>>
                <?= field_error($errors, 'visited_on') ?>
            </div>
            <fieldset class="field rating-field">
                <legend>Note</legend>
                <div class="rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="rating-<?= $i ?>" name="rating" value="<?= $i ?>"<?= (string) ($old['rating'] ?? '') === (string) $i ? ' checked' : '' ?>>
                        <label for="rating-<?= $i ?>" title="<?= $i ?> sur 5"><span class="sr-only"><?= $i ?> sur 5</span>★</label>
                    <?php endfor ?>
                </div>
                <?= field_error($errors, 'rating') ?>
            </fieldset>
        </div>

        <div class="field">
            <label for="note">Votre avis <span class="muted">(facultatif)</span></label>
            <textarea id="note" name="note" rows="4" maxlength="500" placeholder="Ce que vous avez aimé, un conseil…"<?= invalid_attrs($errors, 'note') ?>><?= $value('note') ?></textarea>
            <p class="hint">500 caractères maximum.</p>
            <?= field_error($errors, 'note') ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Enregistrer</button>
            <a href="<?= e(url('/carnet')) ?>" class="btn btn-ghost">Annuler</a>
        </div>
    </form>
<?php endif ?>

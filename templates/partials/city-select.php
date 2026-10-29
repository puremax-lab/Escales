<?php
/**
 * Select of all cities grouped by country.
 * @var array $cityGroups @var string $name @var string|int $selected @var string $placeholder @var array $errors
 */
?>
<select id="<?= e($name) ?>" name="<?= e($name) ?>" required<?= invalid_attrs($errors, $name) ?>>
    <option value=""><?= e($placeholder) ?></option>
    <?php foreach ($cityGroups as $country => $cities): ?>
        <optgroup label="<?= e($country) ?>">
            <?php foreach ($cities as $city): ?>
                <option value="<?= $city['id'] ?>"<?= (string) $city['id'] === (string) $selected ? ' selected' : '' ?>><?= e($city['name']) ?></option>
            <?php endforeach ?>
        </optgroup>
    <?php endforeach ?>
</select>
<?= field_error($errors, $name) ?>

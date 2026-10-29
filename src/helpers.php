<?php

/** Escapes text for HTML output. Every value printed in a template goes through it. */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** App path with the folder the site is served from: url('/villes') gives '/escales/villes' online. */
function url(string $path): string
{
    return App\Core\Request::$basePath . $path;
}

/** French date such as "12 mars 2026", without depending on the intl extension. */
function format_date(string $isoDate): string
{
    static $months = [
        1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
    ];
    [$year, $month, $day] = array_map('intval', explode('-', substr($isoDate, 0, 10)));

    return sprintf('%d %s %d', $day, $months[$month], $year);
}

/** Error message shown under a form field, linked to it by aria-describedby. */
function field_error(array $errors, string $field): string
{
    return isset($errors[$field])
        ? sprintf('<p class="error" id="%s-error">%s</p>', e($field), e($errors[$field]))
        : '';
}

/** Attributes that mark a field as invalid. */
function invalid_attrs(array $errors, string $field): string
{
    return isset($errors[$field])
        ? sprintf(' class="is-invalid" aria-invalid="true" aria-describedby="%s-error"', e($field))
        : '';
}

function age(string $birthDate): int
{
    return (new DateTimeImmutable($birthDate))->diff(new DateTimeImmutable('today'))->y;
}

/** "1 ville", "3 villes". */
function plural(int $count, string $singular, ?string $plural = null): string
{
    return $count . ' ' . ($count > 1 ? ($plural ?? $singular . 's') : $singular);
}

/** Filled and empty stars for a rating from 1 to 5. */
function stars(int $rating): string
{
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

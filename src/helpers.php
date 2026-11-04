<?php

/** Escapes text for HTML output. Every value printed in a template goes through it. */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** The text in the visitor's language, with %s and %d filled in. French is the source text (see lang/en.php). */
function t(string $text, string|int ...$values): string
{
    $text = App\Core\Lang::translate($text);

    return $values ? vsprintf($text, $values) : $text;
}

/** App path with the folder the site is served from: url('/villes') gives '/escales/villes' online. */
function url(string $path): string
{
    return App\Core\Request::$basePath . $path;
}

/** Date such as "12 mars 2026" or "12 March 2026", without depending on the intl extension. */
function format_date(string $isoDate): string
{
    static $months = [
        'fr' => [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ],
        'en' => [
            1 => 'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ],
    ];
    [$year, $month, $day] = array_map('intval', explode('-', substr($isoDate, 0, 10)));

    return sprintf('%d %s %d', $day, $months[App\Core\Lang::$locale][$month], $year);
}

/** One decimal, with a comma in French: "4,5" or "4.5". */
function decimal(float $number): string
{
    return number_format($number, 1, App\Core\Lang::$locale === 'en' ? '.' : ',', '');
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

/** "1 ville", "3 villes", "0 ville" in French; "1 city", "3 cities", "0 cities" in English. */
function plural(int $count, string $singular, ?string $plural = null): string
{
    $isPlural = App\Core\Lang::$locale === 'en' ? $count !== 1 : $count > 1;

    return $count . ' ' . t($isPlural ? ($plural ?? $singular . 's') : $singular);
}

/** Filled and empty stars for a rating from 1 to 5. */
function stars(int $rating): string
{
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

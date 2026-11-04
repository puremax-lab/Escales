<?php

namespace App\Core;

/**
 * The language of the current page. French is the source language: the code and the
 * templates hold the French text, and lang/en.php gives its English version.
 */
final class Lang
{
    public const LOCALES = ['fr', 'en'];
    public const SESSION_KEY = 'locale';

    public static string $locale = 'fr';

    private static ?array $english = null;

    /** An unknown language falls back to French. */
    public static function set(mixed $locale): void
    {
        self::$locale = in_array($locale, self::LOCALES, true) ? $locale : 'fr';
    }

    /** The text in the current language. A text with no translation is returned as it is. */
    public static function translate(string $text): string
    {
        return self::$locale === 'en' ? (self::english()[$text] ?? $text) : $text;
    }

    /** The French name behind an English one ("brussels" gives "Bruxelles"), for searching the database. */
    public static function toFrench(string $text): string
    {
        if (self::$locale !== 'en') {
            return $text;
        }
        foreach (self::english() as $french => $english) {
            if (mb_strtolower($english) === mb_strtolower($text)) {
                return $french;
            }
        }

        return $text;
    }

    /** @return array<string, string> French text => English text */
    public static function english(): array
    {
        return self::$english ??= require dirname(__DIR__, 2) . '/lang/en.php';
    }
}

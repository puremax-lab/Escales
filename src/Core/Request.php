<?php

namespace App\Core;

/** The incoming HTTP request, built from the PHP superglobals or by hand in tests. */
final class Request
{
    /** Folder the site is served from, such as '/escales' ('' at the root of a domain). */
    public static string $basePath = '';

    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $query = [],
        public readonly array $body = [],
        public readonly string $ip = '127.0.0.1',
    ) {
    }

    public static function fromGlobals(): self
    {
        self::$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (self::$basePath !== '' && preg_match('#^' . preg_quote(self::$basePath, '#') . '(?=/|\?|$)#', $uri)) {
            $uri = substr($uri, strlen(self::$basePath)) ?: '/';
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $uri,
            $_GET,
            $_POST,
            $_SERVER['REMOTE_ADDR'] ?? '',
        );
    }

    /** A query string value as trimmed text ('' when missing or not a string). */
    public function query(string $key): string
    {
        $value = $this->query[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    public function input(string $key): string
    {
        $value = $this->body[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }
}

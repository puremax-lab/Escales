<?php

namespace App\Core;

/** The incoming HTTP request, built from the PHP superglobals or by hand in tests. */
final class Request
{
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
        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $_SERVER['REQUEST_URI'] ?? '/',
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

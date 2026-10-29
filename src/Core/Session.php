<?php

namespace App\Core;

/**
 * Thin wrapper around the session data, with one-time flash messages.
 * It works on an array passed by reference: $_SESSION in the app, a plain array in tests.
 */
final class Session
{
    public function __construct(private array &$data)
    {
    }

    /** Starts the PHP session with safe cookie settings and wraps $_SESSION. */
    public static function start(): self
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => ($_SERVER['HTTPS'] ?? '') === 'on',
            ]);
            session_start();
        }

        return new self($_SESSION);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    /** New session id after login, against session fixation. */
    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function clear(): void
    {
        $this->data = [];
        $this->regenerate();
    }

    public function flash(string $type, string $message): void
    {
        $this->data['_flash'][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type: string, message: string}> */
    public function takeFlashes(): array
    {
        $flashes = $this->data['_flash'] ?? [];
        unset($this->data['_flash']);

        return $flashes;
    }
}

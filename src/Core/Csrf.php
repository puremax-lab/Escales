<?php

namespace App\Core;

/** One random token per session, sent with every form and checked on every POST. */
final class Csrf
{
    private const KEY = '_csrf';

    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get(self::KEY);
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $this->session->set(self::KEY, $token);
        }

        return $token;
    }

    public function isValid(mixed $token): bool
    {
        $expected = $this->session->get(self::KEY);

        return is_string($expected) && is_string($token) && hash_equals($expected, $token);
    }
}

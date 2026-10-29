<?php

namespace App\Core;

/** Stops the request with an HTTP error page (404, 403, 405…). */
final class HttpException extends \RuntimeException
{
    public function __construct(public readonly int $status, string $message = '')
    {
        parent::__construct($message, $status);
    }

    public static function notFound(): self
    {
        return new self(404, 'Page introuvable');
    }
}

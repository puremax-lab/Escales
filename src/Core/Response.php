<?php

namespace App\Core;

/** What a controller returns: an HTML page or a redirect. */
final class Response
{
    private function __construct(
        public readonly string $body,
        public readonly int $status,
        public readonly array $headers,
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /** 303 so the browser follows with a GET after a form submission. */
    public static function redirect(string $location): self
    {
        return new self('', 303, ['Location' => $location]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }
}

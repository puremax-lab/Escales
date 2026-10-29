<?php

namespace App\Controller;

/** Lets a helper such as requireUser() end the request with a redirect. */
final class RedirectException extends \RuntimeException
{
    public function __construct(public readonly string $location)
    {
        parent::__construct("Redirect to $location");
    }
}

<?php

namespace App\Core;

/** Renders a PHP template from templates/ inside the shared layout. */
final class View
{
    /** Values available in every template (current user, CSRF token, flash messages…). */
    private array $shared = [];

    public function __construct(private readonly string $directory)
    {
    }

    public function share(string $name, mixed $value): void
    {
        $this->shared[$name] = $value;
    }

    public function render(string $template, array $data = []): string
    {
        $content = $this->renderFile($template, $data);

        return $this->renderFile('layout', ['content' => $content] + $data);
    }

    /** Used inside templates: <?= $this->partial('partials/visit', ['visit' => $visit]) ?> */
    public function partial(string $template, array $data = []): string
    {
        return $this->renderFile($template, $data);
    }

    public function renderFile(string $template, array $data = []): string
    {
        extract($this->shared + $data, EXTR_SKIP);
        ob_start();
        try {
            require $this->directory . '/' . $template . '.php';
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}

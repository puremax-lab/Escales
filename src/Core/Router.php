<?php

namespace App\Core;

/**
 * Maps a method and a path to a handler. Paths can contain parameters such as
 * "/villes/{id}"; a parameter named "id" only matches digits.
 */
final class Router
{
    /** @var array<int, array{method: string, regex: string, handler: callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $uri): Response
    {
        $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $pathExists = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            $pathExists = true;
            if ($route['method'] === $method) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return ($route['handler'])(...$params);
            }
        }

        throw $pathExists ? new HttpException(405, 'Méthode non autorisée') : HttpException::notFound();
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $regex = preg_replace_callback(
            '#\{(\w+)\}#',
            fn (array $m) => sprintf('(?P<%s>%s)', $m[1], $m[1] === 'id' ? '\d+' : '[^/]+'),
            $path,
        );
        $this->routes[] = ['method' => $method, 'regex' => '#^' . $regex . '$#u', 'handler' => $handler];
    }
}

<?php

declare(strict_types=1);

namespace MatinKiani\SimpleRouter;

class Router
{
    /** @var array<array<callable>> */
    private array $routes = [];

    public function add(string $method, string $path, callable $callback): void
    {
        $this->routes[$method][$path] = $callback;
    }

    public function dispatch(string $method, string $path): string
    {
        if (! isset($this->routes[$method][$path])) {
            return '404 Not Found';
        }

        return ($this->routes[$method][$path])();
    }
}

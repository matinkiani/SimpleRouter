<?php

declare(strict_types=1);

namespace MatinKiani\SimpleRouter;

class Router
{
    /** @var array<array<callable>> */
    private array $routes = [];

    public function add(string $method, string $path, callable $callback): void
    {
        $path = rtrim($path, '/');
        // Convert path placeholders to regex
        $pattern = preg_replace('/\{(\w+)}/', '(?P<\1>[^/]+)', $path);
        $this->routes[$method][$pattern] = $callback;
    }

    public function dispatch(string $method, string $path): string
    {
        if (! isset($this->routes[$method])) {
            return '404 Not Found';
        }

        // Strip query parameters before matching
        $pathWithoutQuery = parse_url($path, PHP_URL_PATH);
        $pathWithoutQuery = rtrim($pathWithoutQuery ?: $path, '/');

        foreach ($this->routes[$method] as $pattern => $callback) {
            // Match path with regex
            if (preg_match('#^'.$pattern.'$#', $pathWithoutQuery, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $callback(...$params);
            }
        }

        return '404 Not Found';
    }
}

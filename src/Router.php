<?php

declare(strict_types=1);

namespace MatinKiani\SimpleRouter;

use Closure;

class Router
{
    /**
     * @var array<string, array<int, Route>>
     */
    private array $routes = [];

    /**
     * @var array<int, Closure>
     */
    private array $globalMiddlewares = [];

    /**
     * @var array<int, Closure>
     */
    private array $groupMiddlewaresStack = [];

    private string $prefix = '';

    public function add(string $method, string $path, Closure $callback): self
    {
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $method = strtoupper($method);
        if (! in_array($method, $allowedMethods)) {
            throw new \InvalidArgumentException('Invalid method');
        }
        $path = rtrim($path, '/');
        if ($this->prefix) {
            $path = $this->prefix.$path;
        }
        $pattern = preg_replace('/\{(\w+)}/', '(?P<\1>[^/]+)', $path);

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                if ($route->pattern === $pattern) {
                    throw new \InvalidArgumentException('Pattern already exists');
                }
            }
        }
        if (! $pattern) {
            $pattern = '/';
        }

        $this->routes[$method][] = new Route($callback, $pattern, $this->groupMiddlewaresStack);

        return $this;
    }

    public function get(string $path, Closure $callback): self
    {
        return $this->add('GET', $path, $callback);
    }

    public function post(string $path, Closure $callback): self
    {
        return $this->add('POST', $path, $callback);
    }

    public function put(string $path, Closure $callback): self
    {
        return $this->add('PUT', $path, $callback);
    }

    public function patch(string $path, Closure $callback): self
    {
        return $this->add('PATCH', $path, $callback);
    }

    public function delete(string $path, Closure $callback): self
    {
        return $this->add('DELETE', $path, $callback);
    }

    /**
     * @param  array{prefix?:string , middleware?:array<Closure>|Closure}  $attributes
     */
    public function group(array $attributes, Closure $callback): void
    {
        $tmpMiddlewares = $this->groupMiddlewaresStack;
        $tmpPrefix = $this->prefix;

        if (isset($attributes['middleware'])) {
            if (! is_array($attributes['middleware'])) {
                $attributes['middleware'] = [$attributes['middleware']];
            }
            $this->groupMiddlewaresStack = array_merge($this->groupMiddlewaresStack, $attributes['middleware']);
        }
        if (isset($attributes['prefix'])) {
            $this->prefix .= $attributes['prefix'];
        }

        $callback();

        $this->groupMiddlewaresStack = $tmpMiddlewares;
        $this->prefix = $tmpPrefix;
    }

    public function addGlobalMiddleware(Closure $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }

    public function dispatch(string $method, string $path): string
    {
        if (! isset($this->routes[$method])) {
            return $this->showNotFound();
        }

        $pathWithoutQuery = parse_url($path, PHP_URL_PATH);
        $pathWithoutQuery = rtrim($pathWithoutQuery ?: $path, '/');
        if ($pathWithoutQuery == '') {
            $pathWithoutQuery = '/';
        }

        foreach ($this->routes[$method] as $nameOrId => $route) {
            if (preg_match('#^'.$route->pattern.'$#', $pathWithoutQuery, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                // Middleware chain
                $callback = $route->callback;
                foreach (array_reverse($route->middlewares) as $middleware) {
                    $next = $callback;
                    $callback = fn () => $middleware($next);
                }
                foreach (array_reverse($this->globalMiddlewares) as $middleware) {
                    $next = $callback;
                    $callback = fn () => $middleware($next);
                }

                // Execute the final callback or middleware chain
                return $callback(...$params);
            }
        }

        return $this->showNotFound();
    }

    private function showNotFound(): string
    {
        return '404 Not Found';
    }
}

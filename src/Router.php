<?php

declare(strict_types=1);

namespace MatinKiani\SimpleRouter;

use Closure;

class Router
{
    /**
     * @var array<string> $ALLOWED_METHODS
     */
    private const ALLOWED_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];


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

    public function add(string $method, string $path, Closure $callback): Route
    {
        $method = strtoupper($method);
        if (!in_array($method, self::ALLOWED_METHODS)) {
            throw new \InvalidArgumentException('Invalid method');
        }
        $path = rtrim($path, '/');
        if ($this->prefix) {
            $path = $this->prefix . $path;
        }
        $pattern = preg_replace('/\{(\w+)}/', '(?P<\1>[^/]+)', $path);

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                if ($route->pattern === $pattern) {
                    throw new \InvalidArgumentException('Pattern already exists');
                }
            }
        }
        if (!$pattern) {
            $pattern = '/';
        }

        $route = new Route($callback, $pattern, $this->groupMiddlewaresStack);
        $this->routes[$method][] = $route;

        return $route;
    }

    public function get(string $path, Closure $callback): Route
    {
        return $this->add('GET', $path, $callback);
    }

    public function post(string $path, Closure $callback): Route
    {
        return $this->add('POST', $path, $callback);
    }

    public function put(string $path, Closure $callback): Route
    {
        return $this->add('PUT', $path, $callback);
    }

    public function patch(string $path, Closure $callback): Route
    {
        return $this->add('PATCH', $path, $callback);
    }

    public function delete(string $path, Closure $callback): Route
    {
        return $this->add('DELETE', $path, $callback);
    }

    /**
     * @param array{prefix?:string , middleware?:array<Closure>|Closure} $attributes
     */
    public function group(array $attributes, Closure $callback): void
    {
        $tmpMiddlewares = $this->groupMiddlewaresStack;
        $tmpPrefix = $this->prefix;

        if (isset($attributes['middleware'])) {
            if (!is_array($attributes['middleware'])) {
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

    public function addGlobalMiddleware(Closure $middleware): self
    {
        $this->globalMiddlewares[] = $middleware;

        return $this;
    }

    public function dispatch(string $method, string $path): string
    {
        if (!isset($this->routes[$method])) {
            return $this->showNotFound();
        }

        $pathWithoutQuery = parse_url($path, PHP_URL_PATH);
        $pathWithoutQuery = rtrim($pathWithoutQuery ?: $path, '/');
        if ($pathWithoutQuery == '') {
            $pathWithoutQuery = '/';
        }

        foreach ($this->routes[$method] as $nameOrId => $route) {
            if (preg_match('#^' . $route->pattern . '$#', $pathWithoutQuery, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Middleware chain
                return $this->wrapCallbackWithMiddlewares($route, $params);
            }
        }

        return $this->showNotFound();
    }

    public function route(string $name): string
    {
        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                if ($route->name === $name) {
                    return $this->wrapCallbackWithMiddlewares($route, []);
                }
            }
        }

        throw new \InvalidArgumentException('Route not found');
    }

    /**
     * @param array<string,mixed> $params
     */
    public function wrapCallbackWithMiddlewares(Route $route, array $params): string
    {
        $callback = $route->callback;

        $middlewareStack = array_merge(
            $this->globalMiddlewares,
            $route->middlewares
        );

        foreach (array_reverse($middlewareStack) as $middleware) {
            $next = $callback;
            $callback = fn() => $middleware($next);
        }

        // Execute the final callback or middleware chain
        return $callback(...$params);
    }

    protected function showNotFound(): string
    {
        return '404 Not Found';
    }
}

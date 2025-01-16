<?php

declare(strict_types=1);

namespace MatinKiani\SimpleRouter;

class Router
{
    /**
     * @var array<string, array<string|int, array{callback: callable, pattern: string}>> $routes
     */
    private array $routes = [];

    public function add(string $method, string $path, callable $callback): self
    {
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $method = strtoupper($method);
        if (!in_array($method, $allowedMethods)) {
            throw new \InvalidArgumentException('Invalid method');
        }
        $path = rtrim($path, '/');
        $pattern = preg_replace('/\{(\w+)}/', '(?P<\1>[^/]+)', $path);

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                if ($route['pattern'] === $pattern) {
                    throw new \InvalidArgumentException('Pattern already exists');
                }
            }
        }
        if (!$pattern) {
            $pattern = '/';
        }

        $this->routes[$method][] = ['callback' => $callback, 'pattern' => $pattern];
        return $this;
    }

    public function get(string $path, callable $callback): self
    {
        return $this->add('GET', $path, $callback);
    }

    public function post(string $path, callable $callback): self
    {
        return $this->add('POST', $path, $callback);
    }

    public function put(string $path, callable $callback): self
    {
        return $this->add('PUT', $path, $callback);
    }

    public function patch(string $path, callable $callback): self
    {
        return $this->add('PATCH', $path, $callback);
    }

    public function delete(string $path, callable $callback): self
    {
        return $this->add('DELETE', $path, $callback);
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
            if (preg_match('#^' . $route['pattern'] . '$#', $pathWithoutQuery, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $route['callback'](...$params);
            }
        }

        return $this->showNotFound();
    }

    private function showNotFound(): string
    {
        return '404 Not Found';
    }

}

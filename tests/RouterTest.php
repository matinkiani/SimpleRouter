<?php

declare(strict_types=1);

namespace Tests;

use MatinKiani\SimpleRouter\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function test_add_and_dispatch_route(): void
    {
        $router = new Router;
        $router->add('GET', '/test', fn (): string => 'Test Route');

        $response = $router->dispatch('GET', '/test');

        $this->assertEquals('Test Route', $response);
    }

    public function test_add_and_dispatch_route_with_dynamic_parameter(): void
    {
        $router = new Router;
        $router->add('GET', '/test/{id}', fn ($id): string => 'Test Route '.$id);

        $response = $router->dispatch('GET', '/test/123');

        $this->assertEquals('Test Route 123', $response);
    }

    public function test_404_for_unmatched_route(): void
    {
        $router = new Router;
        $response = $router->dispatch('GET', '/non-existent');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unmatched_route_with_dynamic_parameter(): void
    {
        $router = new Router;
        $response = $router->dispatch('GET', '/non-existent/123');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unmatched_method(): void
    {
        $router = new Router;
        $router->add('GET', '/test', fn (): string => 'Test Route');
        $response = $router->dispatch('POST', '/test');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unmatched_method_with_dynamic_parameter(): void
    {
        $router = new Router;
        $router->add('GET', '/test/{id}', fn ($id): string => 'Test Route '.$id);
        $response = $router->dispatch('POST', '/test/123');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unset_dynamic_parameter(): void
    {
        $router = new Router;
        $router->add('GET', '/test/{id}', fn ($id): string => 'Test Route '.$id);
        $response = $router->dispatch('GET', '/test');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_add_and_dispatch_route_with_multiple_methods(): void
    {
        $router = new Router;
        $router->add('GET', '/test', fn (): string => 'Test GET Route');
        $router->add('POST', '/test', fn (): string => 'Test POST Route');

        $responseGet = $router->dispatch('GET', '/test');
        $responsePost = $router->dispatch('POST', '/test');

        $this->assertEquals('Test GET Route', $responseGet);
        $this->assertEquals('Test POST Route', $responsePost);
    }

    public function test_add_and_dispatch_route_with_query_parameters(): void
    {
        $router = new Router;
        $router->add('GET', '/test', fn (): string => 'Test Route with Query');

        $response = $router->dispatch('GET', '/test?param=value');

        $this->assertEquals('Test Route with Query', $response);
    }

    public function test_add_and_dispatch_route_with_trailing_slash(): void
    {
        $router = new Router;
        $router->add('GET', '/test', fn (): string => 'Test Route');

        $response = $router->dispatch('GET', '/test/');

        $this->assertEquals('Test Route', $response);
    }

    public function test_add_and_dispatch_route_with_subdirectory(): void
    {
        $router = new Router;
        $router->add('GET', '/test/sub', fn (): string => 'Test Subdirectory Route');

        $response = $router->dispatch('GET', '/test/sub');

        $this->assertEquals('Test Subdirectory Route', $response);
    }

    public function test_add_and_dispatch_route_with_multiple_dynamic_parameters(): void
    {
        $router = new Router;
        $router->add('GET', '/test/{id}/{name}', fn ($id, $name): string => 'Test Route '.$id.' '.$name);

        $response = $router->dispatch('GET', '/test/123/john');

        $this->assertEquals('Test Route 123 john', $response);
    }
}

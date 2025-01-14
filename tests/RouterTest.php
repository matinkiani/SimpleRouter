<?php

declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
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

    public function test_add_wrong_route_method(): void
    {
        $router = new Router;
        $this->expectException(InvalidArgumentException::class);
        $router->add('WrOnG', '/test', fn (): string => 'Test POST Route');
    }

    public function test_add_and_dispatch_route_with_empty_path(): void
    {
        $router = new Router;
        $router->add('GET', '', fn (): string => 'Test Empty Path Route');

        $response = $router->dispatch('GET', '');

        $this->assertEquals('Test Empty Path Route', $response);
    }

    public function test_add_and_dispatch_route_with_numeric_path(): void
    {
        $router = new Router;
        $router->add('GET', '/123', fn (): string => 'Test Numeric Path Route');

        $response = $router->dispatch('GET', '/123');

        $this->assertEquals('Test Numeric Path Route', $response);
    }

    public function test_add_and_dispatch_route_with_mixed_case_path(): void
    {
        $router = new Router;
        $router->add('GET', '/TestPath', fn (): string => 'Test Mixed Case Path Route');

        $response = $router->dispatch('GET', '/TestPath');

        $this->assertEquals('Test Mixed Case Path Route', $response);
    }

    public function test_get_method(): void
    {
        $router = new Router;
        $router->get('/test', fn (): string => 'Test GET Route');

        $response = $router->dispatch('GET', '/test');

        $this->assertEquals('Test GET Route', $response);
    }

    public function test_post_method(): void
    {
        $router = new Router;
        $router->post('/test', fn (): string => 'Test POST Route');

        $response = $router->dispatch('POST', '/test');

        $this->assertEquals('Test POST Route', $response);
    }

    public function test_put_method(): void
    {
        $router = new Router;
        $router->put('/test', fn (): string => 'Test PUT Route');

        $response = $router->dispatch('PUT', '/test');

        $this->assertEquals('Test PUT Route', $response);
    }

    public function test_patch_method(): void
    {
        $router = new Router;
        $router->patch('/test', fn (): string => 'Test PATCH Route');

        $response = $router->dispatch('PATCH', '/test');

        $this->assertEquals('Test PATCH Route', $response);
    }

    public function test_delete_method(): void
    {
        $router = new Router;
        $router->delete('/test', fn (): string => 'Test DELETE Route');

        $response = $router->dispatch('DELETE', '/test');

        $this->assertEquals('Test DELETE Route', $response);
    }
}

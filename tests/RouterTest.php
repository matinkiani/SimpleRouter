<?php

declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use MatinKiani\SimpleRouter\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router;
    }

    public function test_add_and_dispatch_route(): void
    {
        $this->router->add('GET', '/test', fn (): string => 'Test Route');

        $response = $this->router->dispatch('GET', '/test');

        $this->assertEquals('Test Route', $response);
    }

    public function test_add_and_dispatch_route_with_dynamic_parameter(): void
    {
        $this->router->add('GET', '/test/{id}', fn ($id): string => 'Test Route '.$id);

        $response = $this->router->dispatch('GET', '/test/123');

        $this->assertEquals('Test Route 123', $response);
    }

    public function test_404_for_unmatched_route(): void
    {
        $response = $this->router->dispatch('GET', '/non-existent');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unmatched_route_with_dynamic_parameter(): void
    {
        $response = $this->router->dispatch('GET', '/non-existent/123');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unmatched_method(): void
    {
        $this->router->add('GET', '/test', fn (): string => 'Test Route');
        $response = $this->router->dispatch('POST', '/test');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unmatched_method_with_dynamic_parameter(): void
    {
        $this->router->add('GET', '/test/{id}', fn ($id): string => 'Test Route '.$id);
        $response = $this->router->dispatch('POST', '/test/123');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_404_for_unset_dynamic_parameter(): void
    {
        $this->router->add('GET', '/test/{id}', fn ($id): string => 'Test Route '.$id);
        $response = $this->router->dispatch('GET', '/test');
        $this->assertEquals('404 Not Found', $response);
    }

    public function test_add_and_dispatch_route_with_multiple_methods(): void
    {
        $this->router->add('GET', '/test', fn (): string => 'Test GET Route');
        $this->router->add('POST', '/test', fn (): string => 'Test POST Route');

        $responseGet = $this->router->dispatch('GET', '/test');
        $responsePost = $this->router->dispatch('POST', '/test');

        $this->assertEquals('Test GET Route', $responseGet);
        $this->assertEquals('Test POST Route', $responsePost);
    }

    public function test_add_and_dispatch_route_with_query_parameters(): void
    {
        $this->router->add('GET', '/test', fn (): string => 'Test Route with Query');

        $response = $this->router->dispatch('GET', '/test?param=value');

        $this->assertEquals('Test Route with Query', $response);
    }

    public function test_add_and_dispatch_route_with_trailing_slash(): void
    {
        $this->router->add('GET', '/test', fn (): string => 'Test Route');

        $response = $this->router->dispatch('GET', '/test/');

        $this->assertEquals('Test Route', $response);
    }

    public function test_add_and_dispatch_route_with_subdirectory(): void
    {
        $this->router->add('GET', '/test/sub', fn (): string => 'Test Subdirectory Route');

        $response = $this->router->dispatch('GET', '/test/sub');

        $this->assertEquals('Test Subdirectory Route', $response);
    }

    public function test_add_and_dispatch_route_with_multiple_dynamic_parameters(): void
    {
        $this->router->add('GET', '/test/{id}/{name}', fn ($id, $name): string => 'Test Route '.$id.' '.$name);

        $response = $this->router->dispatch('GET', '/test/123/john');

        $this->assertEquals('Test Route 123 john', $response);
    }

    public function test_add_wrong_route_method(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->router->add('WrOnG', '/test', fn (): string => 'Test POST Route');
    }

    public function test_add_and_dispatch_route_with_empty_path(): void
    {
        $this->router->add('GET', '', fn (): string => 'Test Empty Path Route');
        $response = $this->router->dispatch('GET', '');
        $this->assertEquals('Test Empty Path Route', $response);
    }

    public function test_add_and_dispatch_root_route(): void
    {
        $this->router->add('GET', '/', fn (): string => 'Test root Path Route');
        $response = $this->router->dispatch('GET', '');
        $this->assertEquals('Test root Path Route', $response);
    }

    public function test_add_and_dispatch_route_with_numeric_path(): void
    {
        $this->router->add('GET', '/123', fn (): string => 'Test Numeric Path Route');

        $response = $this->router->dispatch('GET', '/123');

        $this->assertEquals('Test Numeric Path Route', $response);
    }

    public function test_add_and_dispatch_route_with_mixed_case_path(): void
    {
        $this->router->add('GET', '/TestPath', fn (): string => 'Test Mixed Case Path Route');

        $response = $this->router->dispatch('GET', '/TestPath');

        $this->assertEquals('Test Mixed Case Path Route', $response);
    }

    public function test_get_method(): void
    {
        $this->router->get('/test', fn (): string => 'Test GET Route');

        $response = $this->router->dispatch('GET', '/test');

        $this->assertEquals('Test GET Route', $response);
    }

    public function test_post_method(): void
    {
        $this->router->post('/test', fn (): string => 'Test POST Route');

        $response = $this->router->dispatch('POST', '/test');

        $this->assertEquals('Test POST Route', $response);
    }

    public function test_put_method(): void
    {
        $this->router->put('/test', fn (): string => 'Test PUT Route');

        $response = $this->router->dispatch('PUT', '/test');

        $this->assertEquals('Test PUT Route', $response);
    }

    public function test_patch_method(): void
    {
        $this->router->patch('/test', fn (): string => 'Test PATCH Route');

        $response = $this->router->dispatch('PATCH', '/test');

        $this->assertEquals('Test PATCH Route', $response);
    }

    public function test_delete_method(): void
    {
        $this->router->delete('/test', fn (): string => 'Test DELETE Route');

        $response = $this->router->dispatch('DELETE', '/test');

        $this->assertEquals('Test DELETE Route', $response);
    }

    public function test_add_duplicate_routes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->router->add('GET', '/test', fn (): string => 'Test Route 1');
        $this->router->add('GET', '/test', fn (): string => 'Test Route 2');

    }

    public function test_add_simple_middleware_through_a_group(): void
    {
        $tmpMiddleware = function ($next): string {
            return 'Middleware 1'.$next();
        };
        $this->router->group(['middleware' => [$tmpMiddleware]], function () {
            $this->router->add('GET', '/test', fn (): string => 'Test Route');
        });

        $response = $this->router->dispatch('GET', '/test');

        $this->assertEquals('Middleware 1Test Route', $response);
    }

    public function test_add_simple_middleware_through_a_group_without_an_array(): void
    {
        $tmpMiddleware = function ($next): string {
            return 'Middleware 1'.$next();
        };
        $this->router->group(['middleware' => $tmpMiddleware], function () {
            $this->router->add('GET', '/test', fn (): string => 'Test Route');
        });

        $response = $this->router->dispatch('GET', '/test');

        $this->assertEquals('Middleware 1Test Route', $response);
    }

    public function test_add_a_global_middleware(): void
    {
        $tmpMiddleware = function ($next): string {
            return 'Global Middleware'.$next();
        };
        $this->router->addGlobalMiddleware($tmpMiddleware);
        $this->router->add('GET', '/test', fn (): string => 'Test Route');

        $response = $this->router->dispatch('GET', '/test');

        $this->assertEquals('Global MiddlewareTest Route', $response);
    }

    public function test_add_global_and_normal_middleware(): void
    {
        $tmpMiddleware = function ($next): string {
            return 'Global Middleware'.$next();
        };
        $tmpMiddleware2 = function ($next): string {
            return 'Middleware 1'.$next();
        };
        $this->router->addGlobalMiddleware($tmpMiddleware);
        $this->router->group(['middleware' => $tmpMiddleware2], function () {
            $this->router->add('GET', '/test', fn (): string => 'Test Route');
        });

        $response = $this->router->dispatch('GET', '/test');

        $this->assertEquals('Global MiddlewareMiddleware 1Test Route', $response);
    }

    public function test_add_prefix_through_a_group(): void
    {
        $this->router->group(['prefix' => '/test'], function () {
            $this->router->add('GET', '/sub', fn (): string => 'Test Route');
        });

        $response = $this->router->dispatch('GET', '/test/sub');
        $this->assertEquals('Test Route', $response);

    }

    public function test_route_can_be_named(): void
    {
        $route = $this->router->get('/users', fn () => 'Users List')
            ->name('users.index');

        $this->assertEquals('users.index', $route->name);
    }

    public function test_can_find_route_by_name(): void
    {
        $this->router->get('/users', fn () => 'Users List')
            ->name('users.index');

        $response = $this->router->route('users.index');
        $this->assertEquals('Users List', $response);
    }

    public function test_route_not_found_by_name_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route not found');

        $this->router->route('non.existent.route');
    }

    public function test_multiple_named_routes(): void
    {
        $this->router->get('/users', fn () => 'Users List')
            ->name('users.index');

        $this->router->get('/users/create', fn () => 'Create User')
            ->name('users.create');

        $this->assertEquals('Users List', $this->router->route('users.index'));
        $this->assertEquals('Create User', $this->router->route('users.create'));
    }

    public function test_named_route_with_middleware(): void
    {
        $middleware = fn ($next) => 'Before '.$next().' After';

        $this->router->get('/users', fn () => 'Users List')
            ->name('users.index');

        $this->router->addGlobalMiddleware($middleware);

        $response = $this->router->route('users.index');
        $this->assertEquals('Before Users List After', $response);
    }

    public function test_named_route_in_group(): void
    {
        $this->router->group(['prefix' => '/admin'], function () {
            $this->router->get('/users', fn () => 'Admin Users')
                ->name('admin.users');
        });

        $response = $this->router->route('admin.users');
        $this->assertEquals('Admin Users', $response);
    }

    //    public function test_named_route_with_parameters(): void TODO params for named routes
    //    {
    //        $this->router->get('/users/{id}', fn ($id) => "User {$id}")
    //            ->name('users.show');
    //
    //        $response = $this->router->dispatch('GET', '/users/123');
    //        $this->assertEquals('User 123', $response);
    //
    //        $namedResponse = $this->router->route('users.show');
    //        $this->assertEquals('User ', $namedResponse); // No parameters provided
    //    }

    public function test_different_methods_with_same_named_routes(): void
    {
        $this->router->get('/users', fn () => 'Get Users')
            ->name('users');

        $this->router->post('/users', fn () => 'Create User')
            ->name('users');

        $this->assertEquals('Get Users', $this->router->route('users'));
    }
}

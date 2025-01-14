<?php


use NunoMaduro\SkeletonPhp\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function test_register_and_dispatch_route()
    {
        $router = new Router();
        $router->add('GET', '/test', function () {
            return 'Test Route';
        });

        $response = $router->dispatch('GET', '/test');

        $this->assertEquals('Test Route', $response);
    }

    public function test_404_for_unmatched_route()
    {
        $router = new Router();
        $response = $router->dispatch('GET', '/non-existent');
        $this->assertEquals('404 Not Found', $response);
    }
}

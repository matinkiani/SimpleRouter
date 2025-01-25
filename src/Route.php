<?php

namespace MatinKiani\SimpleRouter;

use Closure;

class Route
{
    /**
     * @param  array<int, Closure>  $middlewares
     */
    public function __construct(public Closure $callback, public string $pattern, public array $middlewares)
    {
        //
    }
}

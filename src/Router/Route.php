<?php

namespace Oladesoftware\Httpcrafter\Router;

/**
 * Define a route structure
 * Used by Oladesoftware\Httpcrafter\Router\Router
 */
readonly class Route
{
    public function __construct(
        public array $methods,
        public string $path,
        public mixed  $target,
        public string $middleware = ""
    ){}
}
<?php

namespace Oladesoftware\Httpcrafter\Router;

readonly class Route
{
    public function __construct(
        public array $methods,
        public string $path,
        public mixed  $target,
        public string $middleware = ""
    ){}
}
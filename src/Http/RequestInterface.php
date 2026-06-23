<?php

namespace Oladesoftware\Httpcrafter\Http;

interface RequestInterface
{
    public function getPath(): string;
    public function getMethod(): string;
    public function getServer(?string $key = null): string|array;
    public function getQuery(?string $name = null): string|array;
    public function getPost(?string $name = null): string|array;
}
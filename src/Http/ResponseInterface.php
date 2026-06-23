<?php

namespace Oladesoftware\Httpcrafter\Http;

interface ResponseInterface
{
    public function setCode(int $code): ResponseInterface;
    public function getCode(): int;
    public function setContentType(string $contentType): ResponseInterface;
    public function getContentType(): string;
    public function setHeaders(string $name, string $value): ResponseInterface;
    public function getHeaders(?string $name): string|array;
    public function setBody(mixed $body): ResponseInterface;
    public function getBody(): mixed;
    public function send(): string;
}
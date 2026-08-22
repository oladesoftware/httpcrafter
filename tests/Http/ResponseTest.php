<?php

namespace Tests\Http;

use Oladesoftware\Httpcrafter\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Response::class, new Response());
    }
}
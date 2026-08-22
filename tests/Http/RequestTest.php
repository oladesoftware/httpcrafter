<?php

namespace Tests\Http;

use Oladesoftware\Httpcrafter\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Request::removeInstance();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(Request::class, new Request());
    }

    public function testSingleton(): void
    {
        $this->assertInstanceOf(Request::class, Request::getInstance());
    }

    public function testConstructInitSingleton(): void
    {
        $this->assertSame(new Request(initSingleton: true), Request::getInstance());
    }

    public function testConstructWithoutDefaults(): void
    {
        $request = new Request(initDefault: false);
        $this->assertSame([], $request->server);
        $this->assertSame([], $request->query);
        $this->assertSame([], $request->post);
        $this->assertSame([], $request->cookies);
        $this->assertSame([], $request->files);
        $this->assertSame([], $request->session);
        $this->assertSame([], $request->headers);
    }

    public function testSingletonWithoutDefaults(): void
    {
        $request = Request::getInstance(initDefault: false);
        $this->assertSame([], $request->server);
        $this->assertSame([], $request->query);
        $this->assertSame([], $request->post);
        $this->assertSame([], $request->cookies);
        $this->assertSame([], $request->files);
        $this->assertSame([], $request->session);
        $this->assertSame([], $request->headers);
    }
}
<?php

namespace Http;

use Oladesoftware\Httpcrafter\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testGetServer()
    {
        $request = new Request([]);
        $this->assertIsArray($request->getServer());
    }

    public function testGetPath()
    {
        $request = new Request([
            "REQUEST_URI" => "/"
        ]);
        $this->assertIsString($request->getPath());
    }

    public function testGetMethod()
    {
        $request = new Request([
            "REQUEST_METHOD" => "GET"
        ]);
        $this->assertEquals("GET", $request->getMethod());
    }

    public function testGetQuery()
    {
        $request = new Request([], []);
        $this->assertIsArray($request->getQuery());
    }

    public function testGetQueryWithParams()
    {
        $request = new Request([], ["foo" => "bar"]);
        $this->assertEquals("bar", $request->getQuery("foo"));
    }

    public function testPostQuery()
    {
        $request = new Request([], [], []);
        $this->assertIsArray($request->getPost());
    }

    public function testGetPostWithParams()
    {
        $request = new Request([], [], ["foo" => "bar"]);
        $this->assertEquals("bar", $request->getPost("foo"));
    }
}
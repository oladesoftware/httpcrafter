<?php

namespace Router;

use Oladesoftware\Httpcrafter\Router\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    /*
     * @before
     * */
    protected function setUp(): void
    {
        parent::setUp();
        $this->router = Router::getInstance();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testAddRoute()
    {
        $result = $this->router->addRoute("GET", "/test", function () {
            return "Test";
        });
        $this->assertInstanceOf(Router::class, $result);
    }

    public function testAddRouteWithMiddleware()
    {
        $result = $this->router
            ->addRoute("GET", "/test", function () {
                return "Test";
            })->middleware("authenticated");
        $this->assertInstanceOf(Router::class, $result);
    }

    public function testAddGroup()
    {
        $result = $this->router->addGroup("/blog", [
            ["method" => "GET", "path" => "/", "target" => ["controller" => "BlogController", "method" => "index"]],
            ["method" => "GET", "path" => "/posts", "target" => ["controller" => "BlogController", "method" => "posts"], "name" => "blog.posts"]
        ]);
        $this->assertInstanceOf(Router::class, $result);
    }

    public function testRouteMatched()
    {
        $this->router->addRoute("GET", "/test", function () {
            return "It works!";
        });
        $result = $this->router->match("/test", "GET");
        $this->assertIsArray($result, "The router match result should be an array.");
    }

    public function testRouteNotMatched()
    {
        $result = $this->router->match("/this-route-doesnot-exist", "GET");
        $this->assertIsBool($result, "The router match result should be an boolean.");
    }

    public function testRouteWithMiddleware()
    {
        $this->router->addRoute("GET", "/routeWithMiddleware", ["Class", "Method"])->middleware("authentificated");
        $result = $this->router->match("/routeWithMiddleware", "GET");
        $this->assertArrayHasKey("middleware", $result);
    }

    public function testGeneratePath()
    {
        $this->router->addRoute("GET", "/testGeneratePath", function () {
        }, "testGeneratePath");
        $path = $this->router->generatePath("testGeneratePath");
        $this->assertEquals("/testGeneratePath", $path, "The generated path should be a string.");
    }

    public function testRunCallback()
    {
        $this->router->addRoute("GET", "/testCallback", function () {
            return "It works!";
        });
        $result = $this->router->match("/testCallback", "GET");
        $this->assertEquals("It works!", $this->router->run($result));
    }

    public function testRunCallbackWithParams()
    {
        $this->router->addRoute("GET", "/testCallback/(?<name>[a-z]+)", function (string $name) {
            return "$name";
        });
        $result = $this->router->match("/testCallback/kondo", "GET");
        $this->assertEquals("kondo", $this->router->run($result));
    }
}
<?php

namespace Oladesoftware\Httpcrafter\Router;

/**
 * Class Router
 *
 * A simple router implementation for handling HTTP routes.
 */
class Router{
    /**
     * @var array $routes An array containing the configured routes.
     *
     * This array stores the routes configured in the router. Each route is represented as an associative array
     * with keys:
     * - 'method' for the HTTP method,
     * - 'path' for the URI path,
     * - 'target' for the route's target.
     *
     * Example:
     * [
     *    ['method' => 'GET', 'path' => '/example', 'target' => ['controller' => 'ExampleController, 'method' => 'index'],
     *    ['method' => 'POST', 'path' => '/submit', 'target' => function() { ... }],
     *    ...
     * ]
     *
     * @access private
     */
    private array $routes = [];
    /**
     * @var array $namedRoutes An array mapping route names to their corresponding paths.
     *
     * This array is used to associate names with specific routes, allowing for easy route generation.
     *
     * Example:
     * [
     *    'home' => '/home',
     *    'profile' => '/user/profile',
     *    ...
     * ]
     *
     * @access private
     */
    private array $namedRoutes = [];

    /**
     * @var ?Router $_instance An instance of the this Router
     * @access private
     */
    private static ?Router $_instance = null;

    /**
     * If it is not set, an instance of this router will be create
     * @return Router
     * @access public
     */
    public static function getInstance(): Router
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new Router();
        }
        return self::$_instance;
    }

    /**
     * Prevent instantiation from outside
     * @access private
     */
    private function __construct() {}

    /**
     * Add a new route to the router configuration.
     *
     * @param string $method The HTTP method for the route (e.g., 'GET', 'POST' or 'GET|POST' for multiple methods)
     * @param string $path The URI path for the route, which may include placeholders.
     * @param mixed $target The target of the route, such as a controller class or a closure.
     * @param string|null $name The optional name for the route to be used in route generation.
     * @return self Returns an instance of the router for method chaining
     */
    public function addRoute(string $method, string $path, mixed $target, string $name = null): self
    {
        $this->routes[] = [
            "method" => strtoupper($method),
            "path" => $path,
            "target" => $target
        ];
        if($name){
            if(!array_key_exists($name, $this->namedRoutes)){
                $this->namedRoutes[$name] = $path;
            }
        }
        return $this;
    }

    /**
     * Add a group of many routes with a same basepath to the router configuration.
     *
     * @param string $basepath Base path of the group (e.g. '/blog', '/product')
     * @param array $routes The array of routes that will begin with the basepath
     * (e.g.
     * [
     *   ["method" => "GET", "path" => "/", "target"  => ["controller" => "BlogController", "method" => "index"], "name" => "blog.index"],
     *   ["GET", "/", ["BlogController", "post"]],
     *   ["GET|POST", "/articles/new", ["BlogController", "newPost"], "middleware" => "authenticated"]
     * ]
     * )
     * @return Router Returns an instance of the router for method chaining
     */
    public function addGroup(string $basepath, array $routes): self
    {
        foreach ($routes as $route)
        {
            $path = "/" . trim($basepath, "/ ") . "/" . trim($route['path'] ?? $route[1],"/ ");
            if(array_key_exists("name", $route))
            {
                $this->addRoute($route["method"] ?? $route[0], $path, $route["target"] ?? $route[2], $route["name"] ?? $route[3]);
            }
            else
            {
                $this->addRoute($route["method"] ?? $route[0], $path, $route["target"] ?? $route[2]);
            }
            if(array_key_exists("middleware", $route))
            {
                $this->middleware($route["middleware"]);
            }
        }
        return $this;
    }

    /**
     * Assign a middleware to the last added route in the router configuration.
     *
     * @param string $name The name of the middleware to be assigned.
     *
     * @return self Returns an instance of the router for method chaining.
     *
     * Example:
     * $router->addRoute('GET', '/example', ['controller' => 'ExampleController, 'method' => 'index'])->middleware('auth');
     */
    public function middleware(string $name): self
    {
        $this->routes[array_key_last($this->routes)]["middleware"] = $name;
        return $this;
    }

    /**
     * Generate a route path from a given name
     * @param string $name
     * @return string|null
     * @access public
     */
    public function generatePath(string $name): string|null
    {
        if(!array_key_exists($name, $this->namedRoutes))
        {
            return null;
        }
        return $this->namedRoutes[$name];
    }

    /**
     * Match a requested path and method against configured routes.
     *
     * @param string $path The requested URI path.
     * @param string $method The requested HTTP method (e.g., 'GET', 'POST').
     *
     * @return array|false Returns an array containing matched route details or false if no match is found.
     *
     * This method iterates over the configured routes and attempts to match the provided URI path and HTTP method.
     * If a match is found, an array is returned with the following keys:
     * - 'target': The target of the matched route, such as a controller class or a closure.
     * - 'params': An associative array of placeholder values extracted from the path.
     * - 'middleware': The middleware assigned to the matched route (if any).
     *
     * If no match is found, the method returns false.
     */
    public function match(string $path, string $method): array|false{
        $params = [];
        foreach($this->routes as $route){

            if(!preg_match("%^(" . $route["method"] . ")$%", strtoupper($method)))
            {
                continue;
            }

            if(preg_match("%^" . $route["path"] . "$%", $path, $params))
            {
                foreach ($params as $key => $item){
                    if(is_numeric($key)){
                        unset($params[$key]);
                    }
                }
                return [
                    "target" => $route["target"],
                    "params" => $params,
                    "middleware" => $route["middleware"] ?? null
                ];
            }
        }

        return false;
    }

    /**
     * Execute the specified action based on the matched route details.
     *
     * @param array $action An array containing details of the matched route, including the target, params, and middleware.
     *
     * @return mixed Returns the result of the executed action.
     *
     * This method executes the specified action based on the details of the matched route. The action can be a callable
     * (e.g., a closure or a function), or an array specifying a controller and method to be invoked. The method handles
     * different types of targets and their associated parameters.
     */
    public function run(array $action): mixed{
        if (empty($action["params"]))
        {
            if (is_callable($action["target"]))
            {
                return call_user_func($action["target"]);
            }

            if (is_array($action["target"]))
            {
                $contoller = $action["target"]["controller"] ?? $action["target"][0];
                $method = $action["target"]["method"] ?? $action["target"][1];

                return call_user_func([new $contoller(), $method]);
            }
        }

        if (is_callable($action["target"]))
        {
            return call_user_func_array($action["target"], $action["params"]);
        }

        if (is_array($action["target"]))
        {
            $contoller = $action["target"]["controller"] ?? $action["target"][0];
            $method = $action["target"]["method"] ?? $action["target"][1];

            return call_user_func_array([new $contoller(), $method], $action["params"]);
        }

        if (is_string($action["target"]))
        {
            $contoller = explode("@", $action["target"])[0];
            $method = explode("@", $action["target"])[1];

            return call_user_func_array([new $contoller(), $method], $action["params"]);
        }

        return false;
    }
}
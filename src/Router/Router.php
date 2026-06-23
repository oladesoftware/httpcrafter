<?php

namespace Oladesoftware\Httpcrafter\Router;

use Exception;
use RuntimeException;

class Router{
    private static ?Router $_instance = null;

    private array $pattern_type = [
        'alpha' => '[a-zA-Z-_]+',
        'numeric' => '[0-9]+',
        'alphanum' => '[a-zA-Z0-9-_]+',
    ] {
        get {
            return $this->pattern_type;
        }

        set (array $pattern_type) {
            $this->pattern_type = $pattern_type;
        }
    }

    private array $resolvers = [] {
        get {
            return $this->resolvers;
        }

        set (array $resolvers) {
            $this->resolvers = $resolvers;
        }
    }

    private array $routes = [] {
        get {
            return $this->routes;
        }

        set (array $routes) {
            $this->routes = $routes;
        }
    }

    private array $groupPrefixStack = [];
    private array $groupMiddlewareStack = [];

    /**
     * Prevent instantiation from outside
     * @access private
     */
    private function __construct() {
        $this->addResolver(
            'callable',
            function (mixed $target, array $parameters = []): mixed {
                if (!is_callable($target)) {
                    return null;
                }
                return call_user_func_array($target, $parameters);
            }
        );

        $this->addResolver(
            'string',
            function (mixed $target, array $parameters = []): mixed {
                if (!is_string($target)) {
                    return null;
                }

                $separators = ['@'];

                foreach ($separators as $separator) {
                    if (str_contains($target, $separator)) {
                        [$class, $method] = explode($separator, $target, 2);
                        return call_user_func_array([new $class(), $method], $parameters);
                    }
                }

                return null;
            }
        );

        $this->addResolver(
            'array',
            function (mixed $target, array $parameters = []): mixed {
                if (!is_array($target)) {
                    return null;
                }

                return call_user_func_array([new $target[0](), $target[1]], $parameters);
            }
        );
    }

    public static function getInstance(): Router
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new Router();
        }
        return self::$_instance;
    }

    public function addPatternType(string $name, string $pattern): self
    {
        $this->pattern_type = [...$this->pattern_type, $name => $pattern];
        return $this;
    }

    public function addResolver(string $name, mixed $callable): self
    {
        $this->resolvers = [...$this->resolvers, $name => $callable];
        return $this;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function add(array $methods, string $path, mixed $target, string $name = "", string $middleware = ""): self
    {
        $path = $this->applyGroupPrefix($path);
        $middleware = $middleware !== '' ? $middleware : $this->currentGroupMiddleware();

        if (empty($name)) {
            try {
                $name = 'route-' . bin2hex(random_bytes(4));
            } catch (Exception) {
                $time = microtime();
                $name = 'route-' . substr($time, rand(0, strlen($time) -1), 8);
            }
        }

        foreach ($methods as $key => $method) {
            $methods[$key] = strtoupper($method);
        }

        $this->addRoute(
            $name,
            new Route(
                $methods,
                $this->compilePath($path),
                $target,
                $middleware
            )
        );

        return $this;
    }

    public function get(string $path, mixed $target, string $name = "", string $middleware = ""): self
    {
        $this->add(['GET'], $path, $target, $name, $middleware);
        return $this;
    }

    public function post(string $path, mixed $target, string $name = "", string $middleware = ""): self
    {
        $this->add(['POST'], $path, $target, $name, $middleware);
        return $this;
    }

    public function form(string $path, mixed $target, string $name = "", string $middleware = ""): self
    {
        $this->add(['GET', 'POST'], $path, $target, $name, $middleware);
        return $this;
    }

    public function group(string $base, array $callbacks, string $middleware = ''): self
    {
        $this->groupPrefixStack[] = trim($base, '/ ');
        $this->groupMiddlewareStack[] = $middleware;

        try {
            foreach ($callbacks as $callback) {
                $callback();
            }
        } finally {
            array_pop($this->groupPrefixStack);
            array_pop($this->groupMiddlewareStack);
        }

        return $this;
    }

    public function path(string $name, array $params = [], array $queries = []): string
    {
        if (!array_key_exists($name, $this->routes)) {
            return '';
        }

        $path  = $this->routes[$name]->path;

        $pattern = "%\(\?<([^>]+)>[^)]+\)%";
        if (preg_match_all($pattern, $path, $matches) && !empty($params)) {
            foreach ($matches[1] as $catch) {
                if (array_key_exists($catch, $params)) {
                    $path = preg_replace(
                        "%\(\?<{$catch}>[^)]+\)%",
                        $params[$catch],
                        $path
                    );
                }
            }
        }

        if (!empty($queries)) {
            $path = $path . "?" . http_build_query($queries);
        }

        return $path;
    }

    public function match(string $method, string $path): array
    {
        foreach ($this->routes as $route) {
            if (in_array(strtoupper($method), $route->methods) && preg_match('#^' . $route->path . '$#', $path, $matches)) {
                array_shift($matches);
                $params = [];
                if (!empty($matches)) {
                    foreach ($matches as $key => $match) {
                        if (is_string($key)) {
                            $params[$key] = $match;
                        }
                    }
                }
                return [
                    'route' => $route,
                    'params' => $params
                ];
            }
        }

        return [];
    }

    public function run(mixed $target, array $params = []): mixed
    {
        if (is_callable($target)) {
            return $this->resolvers['callable']($target, $params);
        } elseif (is_string($target)) {
            return $this->resolvers['string']($target, $params);
        } elseif (is_array($target)) {
            return $this->resolvers['array']($target, $params);
        } else {
            throw new RuntimeException('No resolver found for target: ' . gettype($target));
        }
    }

    public function handle(string $method, string $path): mixed
    {
        $matched = $this->match($method, $path);
        if (empty($matched)) {
            return false;
        }
        return $this->run($matched['route']->target, $matched['params']);
    }

    private function addRoute(string $name, Route $route): void
    {
        $this->routes = [
            ...$this->routes,
            $name => $route
        ];
    }

    private function compilePath(string $path): string
    {
        if (!preg_match_all('#{([a-z]+:[a-z]+)}#', $path, $matches)) {
            return $path;
        }
        $patterns = $matches[1];

        foreach ($patterns as $pattern) {
            [$name, $type] = explode(':', $pattern);
            if (!array_key_exists($type, $this->pattern_type)) {
                throw new RuntimeException('Unknown pattern type: ' . $type);
            }
            $path = preg_replace(
                '({' . $pattern . '})',
                '(?<' . $name . '>' . $this->pattern_type[$type] .')',
                $path
            );
        }

        return $path;
    }

    private function applyGroupPrefix(string $path): string
    {
        if (empty($this->groupPrefixStack)) {
            return $path;
        }
        return '/'
            . trim(
                implode(
                    '/',
                    $this->groupPrefixStack
                ),
                '/ '
            )
            . '/'
            . trim($path, '/ ');
    }

    private function currentGroupMiddleware(): string
    {
        return end($this->groupMiddlewareStack) ?: '';
    }
}
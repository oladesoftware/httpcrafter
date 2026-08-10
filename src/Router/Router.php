<?php

namespace Oladesoftware\Httpcrafter\Router;

use Exception;
use RuntimeException;

class Router{
    public bool $strict_mode = true;
    protected static ?Router $_instance = null;
    public const string UNRESOLVED = "\0__unresolved__\0";

    public array $placeholder_pattern = [] {
        get {
            return $this->placeholder_pattern;
        }

        set (array $placeholder_pattern) {
            if ($this->strict_mode && !empty($placeholder_pattern)) {
                $required = ['open', 'close', 'separator', 'name', 'type'];
                $missing = array_diff($required, array_keys($placeholder_pattern));

                if (!empty($missing)) {
                    throw new RuntimeException(
                        'Some required keys (' . implode(', ', $missing) . ') are missing.'
                    );
                }
            }

            $this->placeholder_pattern = $placeholder_pattern;
        }
    }

    public array $pattern_types = [] {
        get {
            return $this->pattern_types;
        }

        set (array $pattern_types) {
            if ($this->strict_mode) {
                foreach ($pattern_types as $name => $pattern) {
                    if (@preg_match('#' . $pattern . '#', '') === false) {
                        throw new RuntimeException(
                            "pattern_type '$name' contains an invalid regex pattern: $pattern"
                        );
                    }
                }
            }

            $this->pattern_types = $pattern_types;
        }
    }

    public array $target_separators = [] {
        get {
            return $this->target_separators;
        }

        set (array $target_separators) {
            if ($this->strict_mode) {
                foreach ($target_separators as $separator) {
                    if (!is_string($separator)) {
                        throw new RuntimeException(
                            "target_separator '$separator' must be a string"
                        );
                    }
                }
            }

            $this->target_separators = $target_separators;
        }
    }

    public array $resolvers = [] {
        get {
            return $this->resolvers;
        }

        set (array $resolvers) {
            if ($this->strict_mode) {
                foreach ($resolvers as $name => $resolver) {
                    if (!is_callable($resolver)) {
                        throw new RuntimeException(
                            "resolver '$name' must be callable"
                        );
                    }
                }
            }

            $this->resolvers = $resolvers;
        }
    }

    public array $routes = [] {
        get {
            return $this->routes;
        }

        set (array $routes) {
            if ($this->strict_mode) {
                foreach ($routes as $name => $route) {
                    if (!$route instanceof Route) {
                        throw new RuntimeException(
                            "Route named '$name' must be an instance of Route"
                        );
                    }
                }
            }

            $this->routes = $routes;
        }
    }

    public array $middleware_pattern = [] {
        get {
            return $this->middleware_pattern;
        }

        set (array $middleware_pattern) {
            if ($this->strict_mode && !empty($middleware_pattern)) {
                $valid_stack_order = ['fifo', 'lifo'];
                $valid_order = ['group_first', 'path_first'];

                if (isset($middleware_pattern['stack_order'])
                    && !in_array($middleware_pattern['stack_order'], $valid_stack_order, true)) {
                    throw new RuntimeException(
                        "middleware_pattern.stack_order must be 'fifo' or 'lifo'"
                    );
                }

                if (isset($middleware_pattern['order'])
                    && !in_array($middleware_pattern['order'], $valid_order, true)) {
                    throw new RuntimeException(
                        "middleware_pattern.order must be 'group_first' or 'path_first'"
                    );
                }
            }

            $this->middleware_pattern = $middleware_pattern;
        }
    }

    protected array $groupPrefixStack = [];
    protected array $groupMiddlewareStack = [];

    public function __construct(bool $withDefaults = true, bool $initSingleton = false) {
        if ($withDefaults) {
            $this
                ->addDefaultPlaceholderPattern()
                ->addDefaultTargetSeparators()
                ->addDefaultResolvers()
                ->addDefaultPatternTypes()
                ->addDefaultMiddlewarePattern();
        } else {
            $this
                ->clearPlaceholderPattern()
                ->clearTargetSeparators()
                ->clearResolvers()
                ->clearPatternTypes()
                ->clearMiddlewarePattern();
        }

        if ($initSingleton) {
            self::$_instance = $this;
        }
    }

    public function enableStrictMode(): self
    {
        $this->strict_mode = true;
        return $this;
    }

    public function disableStrictMode(): self
    {
        $this->strict_mode = false;
        return $this;
    }

    public function clearPlaceholderPattern(): Router
    {
        $this->placeholder_pattern = [

        ];
        return $this;
    }

    public function clearResolvers(): Router
    {
        $this->resolvers = [];
        return $this;
    }

    public function clearPatternTypes(): Router
    {
        $this->pattern_types = [];
        return $this;
    }

    public function clearTargetSeparators(): Router
    {
        $this->target_separators = [];
        return $this;
    }

    public function clearMiddlewarePattern(): Router
    {
        $this->middleware_pattern = [];
        return $this;
    }

    public function addDefaultPlaceholderPattern(): Router
    {
        $this->placeholder_pattern = [
            'open' => '{',
            'close' => '}',
            'separator' => ':',
            'name' => '[a-z_-]+',
            'type' => '[a-z]+'
        ];
        return $this;
    }

    public function addDefaultTargetSeparators(): Router
    {
        $this->target_separators = ['@'];
        return $this;
    }

    public function addDefaultPatternTypes(): Router
    {
        $this->pattern_types = [
            'alpha' => '[a-zA-Z-_]+',
            'numeric' => '[0-9]+',
            'alphanum' => '[a-zA-Z0-9-_]+',
        ];
        return $this;
    }

    public function addDefaultResolvers(): Router
    {
        $this->addResolver(
            'array',
            function (mixed $target, array $parameters = []): mixed {
                if (!is_array($target)) {
                    return self::UNRESOLVED;
                }

                $class = is_array($target[0]) ? $target[0][0] : $target[0];
                $constructor_params = is_array($target[0]) ? $target[0][1] : null;
                $method = $target[1];

                $instance = match(true) {
                    (is_array($constructor_params)) => new $class(...$constructor_params),
                    default => new $class()
                };

                return call_user_func_array([$instance, $method], $parameters);
            }
        );

        if (empty($this->target_separators)) {
            $this->addDefaultTargetSeparators();
        }

        $this->addResolver(
            'string',
            function (mixed $target, array $parameters = []): mixed {
                if (!is_string($target)) {
                    return self::UNRESOLVED;
                }

                foreach ($this->target_separators as $separator) {
                    if (str_contains($target, $separator)) {
                        [$class, $method] = explode($separator, $target, 2);
                        return call_user_func_array([new $class(), $method], $parameters);
                    }
                }

                return self::UNRESOLVED;
            }
        );

        $this->addResolver(
            'callable',
            function (mixed $target, array $parameters = []): mixed {
                if (!is_callable($target)) {
                    return self::UNRESOLVED;
                }
                return call_user_func_array($target, $parameters);
            }
        );

        return $this;
    }

    public function addDefaultMiddlewarePattern(): Router
    {
        $this->middleware_pattern = [
            'stack_order' => 'fifo',
            'order' => 'group_first',
            'separator' => '+',
        ];
        return $this;
    }

    public static function getInstance(bool $withDefaults = true): Router
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new Router(
                $withDefaults,
                true
            );
        }
        return self::$_instance;
    }

    public static function removeInstance(): void
    {
        self::$_instance = null;
    }

    public function addPatternType(string $name, string $pattern): self
    {
        $existing = $this->pattern_types;
        $new = [];

        if (array_key_exists($name, $existing)) {
            $existing[$name] = $pattern;
        } else {
            $new[$name] = $pattern;
        }

        $this->pattern_types = [...$new, ...$existing];
        return $this;
    }

    public function removePatternType(string $name): self
    {
        $pattern_types = $this->pattern_types;

        if (array_key_exists($name, $pattern_types)) {
            unset($pattern_types[$name]);
        }
        $this->pattern_types = $pattern_types;
        return $this;
    }

    public function addTargetSeparator(string $target_separator): self
    {
        $target_separators = $this->target_separators;
        $target_separators = array_unique([...$target_separators ,$target_separator]);
        $this->target_separators = $target_separators;
        return $this;
    }

    public function removeTargetSeparator(string $target_separator): self
    {
        $target_separators = $this->target_separators;
        $target_separators = array_diff($target_separators, [$target_separator]);
        $this->target_separators = $target_separators;
        return $this;
    }

    public function addResolver(string $name, mixed $callable): self
    {
        $existing = $this->resolvers;
        $new = [];

        if (array_key_exists($name, $existing)) {
            $existing[$name] = $callable;
        } else {
            $new[$name] = $callable;
        }

        $this->resolvers = [...$new, ...$existing];
        return $this;
    }

    public function removeResolver(string $name): self
    {
        if (array_key_exists($name, $this->resolvers)) {
            $resolvers = $this->resolvers;
            unset($resolvers[$name]);
            $this->resolvers = $resolvers;
        }
        return $this;
    }

    public function removeByName(string $name): self
    {
        $routes = $this->routes;
        if (array_key_exists($name, $routes)) {
            unset($routes[$name]);
        }
        $this->routes = $routes;
        return $this;
    }

    public function removeByPath(string $path): self
    {
        $routes = $this->routes;
        foreach ($routes as $name => $route) {
            if ($this->compilePath($path) === $route->path) {
                unset($routes[$name]);
            }
        }
        $this->routes = $routes;
        return $this;
    }

    protected function addRoute(string $name, Route $route): void
    {
        $this->routes = [
            ...$this->routes,
            $name => $route
        ];
    }

    public function add(array $methods, string $path, mixed $target, string $name = "", string $middleware = ""): self
    {
        $path = $this->applyGroupPrefix($path);
        $middleware = $this->combineMiddleware($middleware, $this->currentGroupMiddleware());

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

        $path = $this->routes[$name]->path;

        if (preg_match_all('%\(\?<([^>]+)>[^)]+\)%', $path, $matches) && !empty($params)) {
            foreach ($matches[1] as $catch) {
                if (array_key_exists($catch, $params)) {
                    $pattern = '%\(\?<' . preg_quote($catch) . '>[^)]+\)%';
                    $path = preg_replace(
                        $pattern,
                        $params[$catch],
                        $path
                    );
                }
            }
        }

        return !empty($queries)
            ? $path . '?' . http_build_query($queries)
            : $path
        ;
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
        foreach ($this->resolvers as $resolver) {
            $result = $resolver($target, $params);
            if ($result !== self::UNRESOLVED) {
                return $result;
            }
        }

        throw new RuntimeException(
            'No resolver found for target: ' . gettype($target)
        );
    }

    public function handle(string $method, string $path): mixed
    {
        $matched = $this->match($method, $path);
        if (empty($matched)) {
            return false;
        }
        return $this->run($matched['route']->target, $matched['params']);
    }

    protected function compilePath(string $path): string
    {
        if (empty($this->placeholder_pattern)) {
            return $path;
        }

        $p = $this->placeholder_pattern;
        $regex =
            '#'
            . preg_quote($p['open'], '#')
            . '('
            . $p['name']
            . $p['separator']
            . $p['type']
            . ')'
            . preg_quote($p['close'], '#')
            . '#';

        if (!preg_match_all($regex, $path, $matches)) {
            return $path;
        }
        $patterns = $matches[1];

        foreach ($patterns as $pattern) {
            [$name, $type] = explode($p['separator'], $pattern, 2);
            if (!array_key_exists($type, $this->pattern_types)) {
                throw new RuntimeException('Unknown pattern type: ' . $type);
            }
            $path = preg_replace(
                '('
                . preg_quote($p['open'], '(')
                . $pattern
                . preg_quote($p['close'], '(')
                . ')',
                '(?<' . $name . '>' . $this->pattern_types[$type] .')',
                $path
            );
        }

        return $path;
    }

    protected function applyGroupPrefix(string $path): string
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

    protected function currentGroupMiddleware(): string
    {
        $stack = array_values(array_filter(
            $this->groupMiddlewareStack,
            fn(string $m): bool => $m !== ''
        ));

        if (empty($stack)) {
            return '';
        }

        if (($this->middleware_pattern['stack_order'] ?? 'fifo') === 'lifo') {
            $stack = array_reverse($stack);
        }

        return implode($this->middleware_pattern['separator'] ?? '+', $stack);
    }

    protected function combineMiddleware(string $routeMiddleware, string $groupMiddleware): string
    {
        if ($routeMiddleware === '') {
            return $groupMiddleware;
        }
        if ($groupMiddleware === '') {
            return $routeMiddleware;
        }

        $separator = $this->middleware_pattern['separator'] ?? '+';
        $order = $this->middleware_pattern['order'] ?? 'group_first';

        $parts = $order === 'path_first'
            ? [$routeMiddleware, $groupMiddleware]
            : [$groupMiddleware, $routeMiddleware];

        return implode($separator, $parts);
    }
}
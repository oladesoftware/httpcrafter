<?php

namespace Oladesoftware\Httpcrafter\Router;

class RouterFacade
{
    private static ?Router $router = null;
    private static array $patternMaps = [
        "i" => "[0-9]+",
        "s" => "[a-zA-Z]+",
        "a" => "[a-zA-Z0-9]+"
    ];
    private static string $pattern = "%{([is]):([^}]+)}%";

    private static function processPath(string $path): string
    {
        if (preg_match_all(self::$pattern, $path, $matches)) {
            foreach ($matches[1] as $key => $match) {
                $path = str_replace(
                    "%{" . $match . ":" . $matches[2][$key] . "}%",
                    "(?<" . $matches[2][$key] . ">" . self::$patternMaps[$match] . ")",
                    $path
                );
            }
        }
        return $path;
    }

    public static function getRouter(): Router
    {
        if (self::$router === null) {
            self::$router = Router::getInstance();
        }
        return self::$router;
    }

    public static function generatePath(string $name, array $params = [], array $queries = []): string
    {
        return self::getRouter()->generatePath($name, $params, $queries);
    }

    public static function get($path, $target, $name): void
    {
        self::getRouter()->addRoute("GET", self::processPath($path), $target, $name);
    }

    public static function post($path, $target, $name): void
    {
        self::getRouter()->addRoute("POST", self::processPath($path), $target, $name);
    }

    public static function put($path, $target, $name): void
    {
        self::getRouter()->addRoute("PUT", self::processPath($path), $target, $name);
    }

    public static function delete($path, $target, $name): void
    {
        self::getRouter()->addRoute("DELETE", self::processPath($path), $target, $name);
    }

    public static function any($path, $target, $name): void
    {
        self::getRouter()->addRoute("GET|POST|PUT|DELETE", self::processPath($path), $target, $name);
    }

    public static function middleware(string $name): void
    {
        self::getRouter()->middleware($name);
    }
}
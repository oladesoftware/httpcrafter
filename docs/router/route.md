# ` Route ` class

- **Namespace:** ` Oladesoftware\Httpcrafter\Router `
- **Purpose:** Defines the structure of a route handler in an HTTP routing system, enabling request matching and dispatching.

## Overview

The ` Route ` readonly class represents a route configuration within the [` Router `](./router.md) singleton class. It holds no logic. It contains plain data produced by methods ` Router->add() `, ` Router->get() `, ` Router->post() ` and ` Router->form() `. It encapsulates:

- Supported HTTP methods (GET, POST, PUT, DELETE, etc.)
- The path pattern to match incoming requests
- The targeted handler
- Optional middleware for request processing

## Class signature

```php
readonly class Route
{
    public function __construct(
        public array  $methods,
        public string $path,
        public mixed  $target,
        public string $middleware = ""
    ){}
}
```

## Class properties

| Parameter     | Type            | Required | Default | Description                                                                                                                                                                                                 |
|---------------|-----------------|----------|---------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$methods`    | `array<string>` | Yes      | -       | HTTP methods accepted by this route (e.g. ` ['GET'], ['GET', 'POST'] `). Always stored uppercase by ` Router->add() `.                                                                                      |
| `$path`       | `string`        | Yes      | -       | The compiled path pattern, i.e. a regular expression fragment (placeholders like ` {id:numeric} ` are already turned into named capture groups such as ` (?<id>[0-9]+)) `. Not the raw path the user wrote. |
| `$target`     | `mixed`         | Yes      | -       | The route handler. Can be a `callable`, a `string` (`"Class@method"`), or an `array` (`[Class::class, 'method']`). Resolved later by `Router::run()`.                                                       |
| `$middleware` | `string`        | No       | `""`    | Identifier of the middleware to apply to this route. Empty string means no middleware. Not enforced by `Route` itself, enforcement is the caller's responsibility.                                          |

## Usage notes

- **Read-only access**: properties are public but cannot be reassigned (`$route->path = '...'` throws an `Error`).
- **No behavior**: `Route` does not match, resolve, or execute anything by itself. It is purely descriptive data, to keep responsibilities separated from `Router`.
- **Path format**: do not assume `path` is the original string passed to `get()`/`post()`/etc. It is already a PCRE-ready pattern (no leading `^`/`#` delimiters — those are added at match time by `Router::match()`).

## Usage example

```php
$route = new Route(['GET'], '/users/(?<id>[0-9]+)', 'UserController@show', 'auth');
 
$route->methods;    // ['GET']
$route->path;       // '/users/(?<id>[0-9]+)'
$route->target;     // 'UserController@show'
$route->middleware; // 'auth'
```
# Router

- **Namespace:** ` Oladesoftware\Httpcrafter\Router `
- **Purpose:** Routing system, request matching and dispatching.

`Router` is a **singleton** class. It is responsible for 

- registering routes,
- compiling their paths into regular expressions, 
- matching an incoming HTTP method end path pair against the registered routes, and 
- resolving/executing the matched route's target.

It depends on the [`Route`](./route.md) to store each registered route.

## Accessing the instance

The constructor is private. The only entry point is:

```php
$router = Router::getInstance();
```

This returns the single shared instance for the whole process (lazily created on first call).

## Internal state

| Property               | Type                     | Purpose                                                                                                                                        |
|------------------------|--------------------------|------------------------------------------------------------------------------------------------------------------------------------------------|
| `pattern_type`         | `array<string,string>`   | Named regex fragments usable in route placeholders. Defaults: `alpha`, `numeric`, `alphanum`. Extendable via `addPatternType()`.               |
| `resolvers`            | `array<string,callable>` | Strategies used to execute a route's `target`, keyed by the target's PHP type (`callable`, `string`, `array`). Extendable via `addResolver()`. |
| `routes`               | `array<string,Route>`    | All registered routes, keyed by route name.                                                                                                    |
| `groupPrefixStack`     | `array<string>`          | Stack of path prefixes currently active (used by `group()`).                                                                                   |
| `groupMiddlewareStack` | `array<string>`          | Stack of middleware names currently active (used by `group()`).                                                                                |

## Built-in target resolvers

Registered in the constructor, keyed by type:

- **`callable`**: calls the target directly with `call_user_func_array()`.
- **`string`**: expects the format `"Class@method"`. Splits on `@`, instantiates `Class` with no constructor arguments, and calls `method`.
- **`array`**: expects `[ClassName, 'method']`. Instantiates `ClassName` with no constructor arguments and calls `method`.

## Public API

### `addPatternType(string $name, string $pattern): self`
Registers a new named pattern (e.g. `slug` => `[a-z0-9-]+`) usable in route placeholders as `{param:slug}`.

### `addResolver(string $name, callable $callable): self`
Registers a custom resolver for a target type.

### `getRoutes(): array`
Returns all registered `Route` instances, keyed by name.

### `add(array $methods, string $path, mixed $target, string $name = "", string $middleware = ""): self`
Core registration method. All HTTP-verb helpers (`get`, `post`, `form`) delegate to it.

- `$path` is first prefixed with the current group prefix (if inside a `group()` call), then compiled (placeholders → named capture groups).
- `$middleware` falls back to the current group's middleware if not explicitly given.
- `$name` is auto-generated (`route-<8 hex chars>`) if left empty, using `random_bytes()` (falls back to a non-cryptographic value only if `random_bytes()` throws).
- Methods are normalized to uppercase.

### `get(string $path, mixed $target, string $name = "", string $middleware = ""): self`
Registers a `GET` route. Shortcut for `add(['GET'], ...)`.

### `post(string $path, mixed $target, string $name = "", string $middleware = ""): self`
Registers a `POST` route. Shortcut for `add(['POST'], ...)`.

### `form(string $path, mixed $target, string $name = "", string $middleware = ""): self`
Registers a route accepting both `GET` and `POST`. Shortcut for `add(['GET', 'POST'], ...)`.

### `group(string $base, array $callbacks, string $middleware = ''): self`
Groups route registrations under a common path prefix and/or middleware.

```php
$router->group('admin', [
    fn() => $router->get('/dashboard', 'AdminController@dashboard'),
    fn() => $router->get('/users', 'AdminController@users'),
], 'auth');
```

- `$base` is pushed onto `groupPrefixStack`; `$middleware` onto `groupMiddlewareStack`.
- Each entry in `$callbacks` is invoked (each should call route-registration methods on `$router`).
- The stacks are popped in a `finally` block, so nested groups and exceptions inside callbacks don't leave stale state.
- Groups can be nested; prefixes accumulate (joined with `/`).

### `path(string $name, array $params = [], array $queries = []): string`
Reverse-routing: builds a URL for a named route.

- Looks up the route by `$name`; returns `''` if not found.
- Replaces each `(?<param>...)` capture group in the compiled path with the corresponding value from `$params`.
- Appends `$queries` as a query string via `http_build_query()` if provided.

### `match(string $method, string $path): array`
Finds the first registered route whose `methods` contains `$method` (case-insensitive) and whose compiled `path` regex matches `$path`.

Returns `[]` if nothing matches, otherwise:

```php
[
    'route'  => Route,   // the matched Route instance
    'params' => array,   // named capture groups extracted from $path
]
```

### `run(mixed $target, array $params = []): mixed`
Dispatches `$target` to the appropriate resolver based on its PHP type (`callable`, `string`, `array`). Throws `RuntimeException` if the type has no matching resolver.

### `handle(string $method, string $path): mixed`
Convenience method combining `match()` and `run()`:

1. Calls `match($method, $path)`.
2. Returns `false` if nothing matched.
3. Otherwise calls `run()` on the matched route's target with the extracted params.
   This is the typical single entry point for handling an incoming request.

## Internal (private) methods

### `addRoute(string $name, Route $route): void`
Appends a `Route` to the `routes` array under `$name`.

### `compilePath(string $path): string`
Transforms `{name:type}` placeholders into named PCRE capture groups `(?<name>PATTERN)`, where `PATTERN` comes from `pattern_type[type]`. Throws `RuntimeException` if `type` is not a known pattern type. Paths without placeholders are returned unchanged.

### `applyGroupPrefix(string $path): string`
Prepends the joined `groupPrefixStack` to `$path` if any group is currently active; otherwise returns `$path` unchanged.

### `currentGroupMiddleware(): string`
Returns the middleware name at the top of `groupMiddlewareStack`, or `''` if no group is active.

## Usage exemple

```php
$router = Router::getInstance();
 
$router->get('/users/{id:numeric}', 'UserController@show', 'user.show');
 
$router->group('api', [
    fn() => $router->get('/ping', fn() => 'pong'),
], 'api-key');
 
// Dispatching an incoming request:
$result = $router->handle('GET', '/users/42');
 
// Reverse routing:
$url = $router->path('user.show', ['id' => 42]); // '/users/42'
```

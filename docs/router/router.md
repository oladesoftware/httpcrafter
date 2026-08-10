# ` Router ` class

---

## Summary

- [Overview](#overview)
- [Quick start](#quick-start)
- [Usage](#usage)
- [Make it yours](#make-it-yours)
- [Properties](#properties)
- [Methods](#methods)

---

## Overview

`Router` is a configurable, singleton-capable HTTP router for PHP 8.4+. It matches incoming requests (method + path) against a set of registered routes, extracts dynamic path parameters, and executes the matched route's target through a pluggable resolution system.

Its behavior is built around five extensible concerns, each backed by a typed property with its own validation rules:

- **Path placeholders**

dynamic segments like `{id:numeric}` are declared with a configurable open/close/separator syntax (`$placeholder_pattern`) and resolved against named regex fragments (`$pattern_types`), so paths are compiled into named capture groups before matching.

- **Target resolution**

a route's target isn't limited to a single format. `$resolvers` holds an ordered list of callables, each capable of handling one kind of target (a `[Class, method]` array, a `"Class@method"` string using a configurable separator from `$target_separators`, or a plain callable). `run()` tries each resolver in turn until one produces a result instead of the `UNRESOLVED` sentinel.

- **Route registration**

routes are added via `add()` (or the `get()`, `post()`, `form()` shortcuts), stored by name in `$routes`, and can later be looked up by name (`path()`, `removeByName()`) or by path (`removeByPath()`).

- **Grouping**

`group()` lets routes share a common path prefix and middleware, including nested groups, using internal stacks (`$groupPrefixStack`, `$groupMiddlewareStack`) that are combined per-route according to `$middleware_pattern`.

- **Strict validation**

`$strict_mode` (on by default) enforces the shape of configuration data: valid regexes, callable resolvers, `Route` instances, etc. At assignment time rather than letting bad configuration fail later during matching or execution.

The class can be used as a plain instantiated object or as an application-wide singleton via `getInstance()` / `removeInstance()`. Sensible defaults for placeholders, pattern types, separators, resolvers, and middleware behavior are loaded automatically unless explicitly disabled, so `Router` is usable out of the box while remaining fully reconfigurable for custom routing conventions.

---

## Quick start

```php
use Oladesoftware\Httpcrafter\Router\Router;

// 1. Create a router (defaults included)
$router = new Router(withDefaults: true);

// 2. Register a couple of routes
$router->get('/', fn() => 'Welcome!', 'home');
$router->get('/hello/{name:alpha}', fn(string $name) => "Hello, {$name}!", 'hello');

// 3. Get information from PHP global variable
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$result = $router->handle($method, $path);

if ($result === false) {
    http_response_code(404);
    echo 'Page not found';
    exit();
}

echo $result;
exit();
```

That's it, no configuration is required to get started, since `new Router()` loads default placeholder syntax (`{name:type}`), default pattern types (`alpha`, `numeric`, `alphanum`), and default resolvers (array, string, callable targets) automatically. 

From here, see [Usage](#usage) for grouping, URL generation, and extending the router's conventions.

---

## Usage

At its core, using `Router` involves three steps: 
- registering routes
- matching an incoming request
- running the matched target

```php
use Oladesoftware\Httpcrafter\Router\Router;

// Get (or create) the singleton instance, loaded with sensible defaults
$router = Router::getInstance(withDefaults: true);

// Register routes
$router->get('/users/{id:numeric}', [UserController::class, 'show'], 'user.show');
$router->post('/users', 'UserController@store', 'user.store');

// Match and run an incoming request
$result = $router->handle(method: $_SERVER['REQUEST_METHOD'], path: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($result === false) {
    http_response_code(404);
    exit('Not found');
}
```

`handle()` combines `match()` (find the route + extract path parameters) and `run()` (execute the target through the first compatible resolver) in a single call. Calling them separately is useful when you need to inspect the matched route before executing it. For example to run middleware, or to log which route was hit:

```php
$matched = $router->match(method: $_SERVER['REQUEST_METHOD'], path: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (!empty($matched)) {
    $result = $router->run(target: $matched['route']->target, params: $matched['params']);
}
```

### Grouping routes

Routes that share a path prefix and/or middleware can be declared inside `group()`, including nested groups:

```php
$router->group('/admin', [
    fn() => $router->get('/dashboard', [AdminController::class, 'dashboard'], 'admin.dashboard'),
    fn() => $router->group('/users', [
        fn() => $router->get('/edit/{id:numeric}', [AdminController::class, 'editUser'], 'admin.user.edit'),
    ]),
], 'auth:role=admin');
```

Here, the `admin.user.edit` route resolves to the path `/admin/users/edit/{id:numeric}` and inherits the `auth:role=admin` middleware from the outer group, combined with its own (if any) according to `$middleware_pattern`.

### Generating URLs

Named routes can be reversed into a path with `path()`:

```php
$router->path('user.show', ['id' => 42]);
//Output: "/users/42"

$router->path('user.show', ['id' => 42], ['ref' => 'email']);
//Output: "/users/42?ref=email"
```

### Adjusting configuration

Every convention `Router` relies on placeholder syntax, pattern types, target separators, resolvers, middleware behavior. It can be added to, replaced, or cleared individually:

```php
$router
    ->addPatternType('slug', '[a-z0-9-]+')
    ->addTargetSeparator('::')
    ->addResolver('invokable', function (mixed $target, array $params = []) {
        if (!is_object($target) || !method_exists($target, '__invoke')) {
            return Router::UNRESOLVED;
        }
        return $target(...$params);
    });
```

---

## Make it yours

`Router` isn't meant to be used only with its defaults. Every convention it ships with is a starting point you can extend, override, or strip away entirely.

### Start from a blank slate

Passing `false` to the constructor skips the defaults and clears every configurable property instead:

```php
$router = new Router(withDefaults: false);
```

From there, nothing is assumed: 
- no placeholder pattern syntax
- no pattern types
- no target separators
- no resolvers
- no middleware pattern rules

You build the routing conventions your application actually needs, piece by piece, using the `addDefault*()` methods selectively. 

If you want to bring some defaults back:

```php
$router
    ->addDefaultPlaceholderPattern()
    ->addPatternType('uuid', '[0-9a-f-]{36}')
    ->addResolver('invokable', $myInvokableResolver);
```

### Reshape placeholder syntax

Don't like `{name:type}`? Change every part of it. The delimiters, the separator, even what counts as a valid name or type token:

```php
$router->placeholder_pattern = [
    'open' => '<',
    'close' => '>',
    'separator' => '|',
    'name' => '[a-zA-Z_]+',
    'type' => '[a-zA-Z]+',
];
```

Paths would then look like `/users/<id|numeric>` instead of `/users/{id:numeric}`.

### Teach it new target formats

The `array`, `string`, and `callable` resolvers cover common cases, but any format you want is fair game. Invokable objects, service container bindings, string references to container entries, and so on. A resolver just needs to return `Router::UNRESOLVED` when it can't handle a target, so the next one gets a chance.

```php
$router->addResolver('container', function (mixed $target, array $params = []) use ($container) {
    if (!is_string($target) || !$container->has($target)) {
        return Router::UNRESOLVED;
    }
    return $container->get($target)(...$params);
});
```

Resolvers registered this way run before the built-in ones for the same reason `addResolver()` reorders on registration, recently added or updated resolvers move to the front, so custom logic can intercept targets before the defaults get a chance at them.

### Redefine middleware combination

If your application's middleware conventions don't match `group_first`/`fifo`, change them:

```php
$router->middleware_pattern = [
    'stack_order' => 'lifo',   // innermost group's middleware appears first
    'order' => 'path_first',   // route's own middleware takes precedence
    'separator' => '|',
];
```

### Loosen validation when needed

`$strict_mode` protects against misconfiguration by validating data as soon as it's assigned. An invalid regex, a non-callable resolver, or a route that isn't a `Route` instance will throw immediately rather than fail silently later. It's on by default, but can be turned off for cases like bulk-loading routes from a trusted, pre-validated source where the extra checks aren't needed:

```php
$router->disableStrictMode();
// ... bulk operations ...
$router->enableStrictMode();
```

Ultimately, `Router` is designed so that its defaults get you moving quickly, but no part of its behavior path syntax, target resolution, middleware rules or validation, is fixed. Configure only what your application needs, and leave the rest alone.

---

## Properties

### `$strict_mode`

Boolean controlling whether the setters of the typed properties:
  - `placeholder_pattern`
  - `pattern_types`
  - `target_separators`
  - `resolvers`
  - `routes`
  - `middleware_pattern`
validate their content before assignment. 
- It is enabled (`true`) by default. When disabled via `disableStrictMode()`, these properties accept any value without validation. Invalid data (e.g. a non-callable resolver, an invalid regex) will only surface later, when it's actually used, rather than at assignment time.

### `$_instance`

- Protected static property holding the single `Router` instance used by the singleton pattern. 
- Stays `null` until `getInstance()` creates it on first call. It can also be assigned by the constructor when parameter `$initSingleton` is set to `true`.  
- `removeInstance()` resets it to `null`, allowing a fresh singleton to be created afterward.

### `UNRESOLVED`

- Sentinel class constant. 
- Every resolver registered in `$resolvers` must return this value when it doesn't know how to handle the target it's given. 
- `run()` iterates through `$resolvers` and moves to the next one whenever a resolver returns `UNRESOLVED`, stopping at the first resolver that actually produces a result. If every resolver returns `UNRESOLVED`, `run()` throws an exception.

### `$placeholder_pattern`

- Defines the syntax used to recognize placeholders inside a route path (e.g. `{id:numeric}`). 
- Expects the keys: 
  - `open`
  - `close`
  - `separator`
  - `name`
  - `type`
- In strict mode, all five keys are required whenever the array isn't empty. 
- This property drives `compilePath()`, which scans a raw path for matches of this pattern and converts each placeholder into a named regex capture group. This is what makes dynamic segments like `{id:numeric}` matchable and extractable in `match()`.

### `$pattern_types`

- Dictionary mapping a type name (e.g. `alpha`, `numeric`, `alphanum`) to a regular expression fragment. 
- `compilePath()` looks up the `type` portion of each placeholder in this array to know which regex to substitute in. An unknown type causes `compilePath()` to throw. 
- In strict mode, every pattern is validated as syntactically valid regex before being accepted, so a broken regex fails fast at configuration time rather than at route-matching time.

### `$target_separators`

- List of string separators recognized when a route target is provided as a plain string (e.g. `@` in `MyController@myMethod`). 
- The built-in `string` resolver (registered by `addDefaultResolvers()`) iterates this list to find a separator present in the target, then splits the string into a class name and method name. 
- In strict mode, every separator must itself be a string.

### `$resolvers`

- Ordered array of callables responsible for turning a route target into an executed result. 
- `run()` calls each resolver in order with the target and parameters, using the first one that doesn't return `UNRESOLVED`. 
- This makes target resolution extensible. The defaults handle: 
  - array targets (`[Class, method]`)
  - string targets (`Class@method`)
  - plain callables
  - custom resolvers can be added via `addResolver()` to support other target formats
- In strict mode, every entry must be `callable`.

### `$routes`

- Registered routes, indexed by route name.
- Populated by `add()` (and the `get()`, `post()`, `form()` shortcuts built on top of it) and consulted by `match()` to find a route whose method and compiled path regex matches an incoming request, and by `path()` to reverse-generate a URL from a route name. 
- In strict mode, every value must be an instance of `Route`.

### `$middleware_pattern`

- Configuration governing how middleware from nested route groups and from an individual route are combined. 
- Accepts the optional keys: 
  - `stack_order` (`fifo` or `lifo`, controlling the order in which nested group middlewares are joined),
  - `order` (`group_first` or `path_first`, controlling whether group middleware comes before or after the route's own middleware)
  - `separator` (the string used to concatenate middleware entries)
- It's read by `currentGroupMiddleware()` and `combineMiddleware()`, both used internally by `add()` when a route is registered.

### `$groupPrefixStack`

- Protected internal stack of path prefixes currently active. 
- `group()` pushes the group's base path onto this stack before running its callbacks and pops it afterward, allowing groups to be nested. 
- `applyGroupPrefix()` reads the full stack to prepend the correct combined prefix to any route path registered inside the group(s).

### `$groupMiddlewareStack`

- Protected internal stack of middleware strings currently active, one entry per open group. 
- `group()` pushes and pops this stack in tandem with `$groupPrefixStack`. 
- `currentGroupMiddleware()` filters out empty entries and joins the remaining ones (respecting `stack_order` from `$middleware_pattern`) to produce the effective group middleware applied to routes registered inside nested groups.

---

## Methods

### `constructor()`

- **Parameters:**
  - An optional boolean `$withDefaults` set at true
  - An optional boolean `$initSingleton` set at false

### `enableStrictMode()`

Enables strict validation mode.

- **Returns:**
  - The current `Router` instance.

### `disableStrictMode()`

Disables strict validation mode.

- **Returns:**
  - The current `Router` instance.

### `clearPlaceholderPattern()`

Removes the current placeholder pattern configuration.

- **Returns:**
  - The current `Router` instance.

### `clearResolvers()`

Removes every registered target resolver.

- **Returns:**
  - The current `Router` instance.

### `clearPatternTypes()`

Removes every registered placeholder pattern type.

- **Returns:**
  - The current `Router` instance.

### `clearTargetSeparators()`

Removes every target separator.

- **Returns:**
  - The current `Router` instance.

### `clearMiddlewarePattern()`

Removes the middleware configuration.

- **Returns:**
  - The current `Router` instance.

### `addDefaultPlaceholderPattern()`

Registers the default placeholder syntax.

- **Returns:**
  - The current `Router` instance.

### `addDefaultTargetSeparators()`

Registers the default target separators.

- **Returns:**
  - The current `Router` instance.

### `addDefaultPatternTypes()`

Registers the default placeholder pattern types.

- **Returns:**
  - The current `Router` instance.

### `addDefaultResolvers()`

Registers the built-in target resolvers.

- **Returns:**
  - The current `Router` instance.

### `addDefaultMiddlewarePattern()`

Registers the default middleware configuration.

- **Returns:**
  - The current `Router` instance.

### `getInstance()`

Returns the router singleton instance.

- **Parameters:**
  - An optional boolean `$withDefaults` set at `true`

- **Returns:**
  - The singleton `Router` instance.

### `removeInstance()`

Removes the current singleton instance.

### `addPatternType()`

Registers or replaces a placeholder pattern type.

- **Parameters:**
  - A string `$name`
  - A string `$pattern`

- **Returns:**
  - The current `Router` instance.

### `removePatternType()`

Removes a placeholder pattern type.

- **Parameters:**
  - A string `$name`

- **Returns:**
  - The current `Router` instance.

### `addTargetSeparator()`

Registers a new target separator.

- **Parameters:**
  - A string `$target_separator`

- **Returns:**
  - The current `Router` instance.

### `removeTargetSeparator()`

Removes a target separator.

- **Parameters:**
  - A string `$target_separator`

- **Returns:**
  - The current `Router` instance.

### `addResolver()`

Registers or replaces a target resolver.

- **Parameters:**
  - A string `$name`
  - A mixed `$callable`

- **Returns:**
  - The current `Router` instance.

---

### `removeResolver()`

Removes a resolver.

- **Parameters:**
  - A string `$name`

- **Returns:**
  - The current `Router` instance.

### `removeByName()`

Removes a route by its name.

- **Parameters:**
  - A string `$name`

- **Returns:**
  - The current `Router` instance.

### `removeByPath()`

Removes every route matching a given path.

- **Parameters:**
  - A string `$path`

- **Returns:**
  - The current `Router` instance.

### `add()`

Registers a route.

- **Parameters:**
  - An array `$methods`
  - A string `$path`
  - A mixed `$target`
  - An optional string `$name` set at `""`
  - An optional string `$middleware` set at `""`

- **Returns:**
  - The current `Router` instance.

### `get()`

Registers a `GET` route.

- **Parameters:**
  - A string `$path`
  - A mixed `$target`
  - An optional string `$name` set at `""`
  - An optional string `$middleware` set at `""`

- **Returns:**
  - The current `Router` instance.

### `post()`

Registers a `POST` route.

- **Parameters:**
  - A string `$path`
  - A mixed `$target`
  - An optional string `$name` set at `""`
  - An optional string `$middleware` set at `""`

- **Returns:**
  - The current `Router` instance.

### `form()`

Registers a route responding to both `GET` and `POST`.

- **Parameters:**
  - A string `$path`
  - A mixed `$target`
  - An optional string `$name` set at `""`
  - An optional string `$middleware` set at `""`

- **Returns:**
  - The current `Router` instance.

### `group()`

Creates a route group with a common prefix and middleware.

- **Parameters:**
  - A string `$base`
  - An array `$callbacks`
  - An optional string `$middleware` set at `''`

- **Returns:**
  - The current `Router` instance.

### `path()`

Generates the URL of a named route.

- **Parameters:**
  - A string `$name`
  - An optional array `$params` set at `[]`
  - An optional array `$queries` set at `[]`

- **Returns:**
  - The generated path as a string.

### `match()`

Matches a request against the registered routes.

- **Parameters:**
  - A string `$method`
  - A string `$path`

- **Returns:**
  - An array containing the matched route and parameters, or an empty array.

### `run()`

Executes a route target using the first compatible resolver.

- **Parameters:**
  - A mixed `$target`
  - An optional array `$params` set at `[]`

- **Returns:**
  - The resolver result.

### `handle()`

Matches and executes a request.

- **Parameters:**
  - A string `$method`
  - A string `$path`

- **Returns:**
  - The route result, or `false` if no route matches.

---
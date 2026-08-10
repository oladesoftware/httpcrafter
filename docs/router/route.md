# ` Route ` class

---

## Table of contents

- [Overview](#overview)
- [Usage](#usage)
- [Properties](#properties)

---

## Overview

`Route` class:

- represents a route definition within [`Router` class](./router.md)
- is a readonly class, making it immutable after initialisation

---

## Usage

```php
$route = new \Oladesoftware\Httpcrafter\Router\Route(
    ['GET'],
    '/users',
    [UserController::class, 'list'],
    'auth'
);
```

---

## Properties

### `$methods`

An array of allowed HTTP method.

- **Type:** `array`
- **Example:** `['GET']`

### `$path`

A string of URI path. 

> Depending of the [`Router` class](./router.md) method `compilePath()` the path may contain a regex.

- **Type:** `string`
- **Example:** 
  - `/users`
  - `/user/(?<id>[0-9a-f-]{36})`

### `$target`

The handler executed when the route matches an incoming request. 

> Depending on the [`Router` class](./router.md) registered `$resolvers`, the target can be an array, a string, a callable, or any other supported registered `$resolvers`.

- **Type:** `mixed`
- **Example:**
  - `fn () => 'Hello, world!'`
  - `function () { return 'Hello, world!'; }`
  - `UserController::class@list`
  - `[UserController::class, 'list']`

### `$middleware`

An optional string of the middleware assigned to the route.

- **Type:** `string`
- **Default:** `''` an empty string
- **Example:** 
  - `auth`
  - `guest`
  - `token-bearer`
  - `token-jwt`

---
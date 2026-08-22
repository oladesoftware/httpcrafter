# ` Request ` class

---

## Table of contents

- [overview](#overview)
- [usage](#usage)
- [use cases](#use-cases)
- [properties](#properties)
- [methods](#methods)

---

## Overview

` Request ` is an object-oriented wrapper around PHP's superglobals:
- `$_SERVER`
- `$_GET`
- `$_POST`
- `$_COOKIE`
- `$_FILES`
- `$_SESSION`
- HTTP headers

---

## Usage

` Request ` class wraps PHP superglobals. It can be used in two ways:

**direct instantiation** 

```php
$request = new Oladesoftware\Httpcrafter\Http\Request();
```

**singleton**

```php
$request = Oladesoftware\Httpcrafter\Http\Request::getInstance();
```

Both ways populate the class from the PHP superglobals variables.

If you want the session to be populate automatically, either call  `session_start()` before instantiating the class or set the class property `$autoStartSession` to `true` before creating the instance.

```php
Oladesoftware\Httpcrafter\Http\Request::$autoStartSession = true
```

---

## Use cases

```php
$request = Oladesoftware\Httpcrafter\Http\Request::getInstance();

//URL: /password-reset?t=a-very-long-token
token = $request->query['t']; // Returns: a-very-long-token

//URL: /users?page=1&limit=90
$page = $request->query['page']; // Returns: 1
$limit = $request->query['limit']; // Returns: 90
```

---

## Properties

### `$autoStartSession`

- static boolean that control if `$session` populate or not.
- default value `false`

### `$_instance`

- static nullable `Oladesoftware\Httpcrafter\Http\Request` that store an instance of `Oladesoftware\Httpcrafter\Http\Request` class
- default value `null`

### `$server`

- store PHP global `$_SERVER`
- default value `[]` empty array

### `$query`

- store PHP global `$_GET`
- default value `[]` empty array

### `$post`

- store PHP global `$_POST`
- default value `[]` empty array

### `$cookies`

- store PHP global `$_COOKIE`
- default value `[]` empty array

### `$files`

- store PHP global `$_FILES`
- default value `[]` empty array

### `$session`

- store PHP global `$_SESSION`
- default value `[]` empty array

### `$headers`

- store PHP function `getallheaders()` output
- default value `[]` empty array

---

## Methods

### `__construct(bool $initDefault = true, bool $initSingleton = false)`

### `addServer()`

- no parameter
- get a snapshot of PHP global `$_SERVER`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance 

### `addQuery()`

- no parameter
- get a snapshot of PHP global `$_GET`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `addPost()`

- no parameter
- get a snapshot of PHP global `$_POST`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `addCookies()`

- no parameter
- get a snapshot of PHP global `$_COOKIE`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `addFiles()`

- no parameter
- get a snapshot of PHP global `$_FILES`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `addSession()`

- no parameter
- get a snapshot of PHP global `$_SESSION` if `$autoStartSession` is `true` or session is active
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `addHeaders()`

- no parameter
- get a snapshot of PHP function `getallheaders()`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `clearServer()`

- no parameter
- reset `$server` property to `[]`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `clearQuery()`

- no parameter
- reset `$query` property to `[]`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `clearPost()`

- no parameter
- reset `$post` property to `[]`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `clearCookies()`

- no parameter
- reset `$cookies` property to `[]`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `clearFiles()`

- no parameter
- reset `$files` property to `[]`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `clearSession()`

- no parameter
- reset `$session` property to `[]`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `clearHeaders()`

- no parameter
- reset `$headers` property to `[]`
- return current `Oladesoftware\Httpcrafter\Http\Request` instance

### `getInstance()`

- parameter: 
  - an optional boolean `$initDefault` set to `true`
- return:
  - an instance of `Oladesoftware\Httpcrafter\Http\Request`

### `removeInstance()`

- no parameter
- reset `$_instance` property to `null`
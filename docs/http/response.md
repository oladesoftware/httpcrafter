# ` Response ` class

---

## Table of contents

- [overview](#overview)
- [usage](#usage)
- [properties](#properties)
- [methods](#methods)

---

## Overview

`Response` is an object-oriented container to store and manipulate an HTTP response.

---

## Usage

```php
$response = new Oladesoftware\Httpcrafter\Http\Response(
    body: 'Hello, World!',
    content_type: Response::HTML
);

echo $response->send(); // Return: Hello, World!
```

---

## Class constants

### `HTML`

- string constant
- value `text/html`

### `JSON`

- string constant
- value `application/json`

---

## Properties

### `$strict_mode`

- boolean that enable validation mode for setter
- default value `true`
- can be toggle using `enableStrictMode()` or `disableStrictMode()`

### `$supported_redirection_codes`

- List of HTTP status codes accepted by `redirect()`
- default value `[300, 301, 302, 303, 304, 305, 306, 307, 308]`
- setter throws an exception if `$strict_mode` is `true` and any code is not an integer between 300 and 399

### `$supported_types`

- List of content type accepted by `$content_type`
- default value `[Response::HTML, Response::JSON]`
- setter throws an exception if `$strict_mode` is `true` and any value is not a string

### `$content_type`

- string which stores the current response content type
- default value `''`
- setter throws an exception if `$strict_mode` is `true` and the value is not a supported type

### `$code`

- HTTP status code
- default value `200`

### `$headers`

- Array of http headers to be sent with response
- default value `[]`
- add more headers using `addHeader()`

### `$body`

- mixed value that encapsulate the response body
- default value `''`

---

## Methods

### `__construct()`

- parameters:
  - `mixed $body = ""` an optional mixed value which will be stored in property `$body`
  - `string $content_type = self::HTML` an optional string which will be stored in `$content_type`
  - `array $headers = []` an optional named array which will be stored in `$headers` using method `addHeader()`
- define `$code`, `$content_type`, `$headers` and `$body`

### `addHeader()`

- parameters:
  - `string $name` header name
  - `string $value` header value
- add to `$headers`
- return current `Oladesoftware\Httpcrafter\Http\Response` instance

### `editHeader()`

- parameters:
  - `string $name` header name
  - `string $value` header value
- edit `$headers` if key `$name` exist
- return current `Oladesoftware\Httpcrafter\Http\Response` instance

### `addSupportedType()`

- parameter:
  - `string $type` string of type to be supported
- prepend to `$supported_types`
- return current `Oladesoftware\Httpcrafter\Http\Response` instance

### `sendHeader()`

- loop `$headers` and send each entry using PHP `header()` function
- return current `Oladesoftware\Httpcrafter\Http\Response` instance

### `send()`

- set response code
- update `Content-Length` header
- send all headers
- throw an exception if `$content_type` is not a supported types
- return `$body` content as string

### `redirect()`

- parameters
  - `string $url` a path or an url to redirect to
  - `int $code = 302` an optional redirection code
- throw an exception if `$strict_mode` is true and `$code` is not a supported redirection code
- send a location header and terminate the script
- no return

### `updateContentLength()`

- calculate size of `$body` and update `$headers['Content-Length']`
- no return

### `enableStrictMode()`

- set `$strict_mode` to true
- return current `Oladesoftware\Httpcrafter\Http\Response` instance

### `disableStrictMode()`

- set `$strict_mode` to false
- return current `Oladesoftware\Httpcrafter\Http\Response` instance

---
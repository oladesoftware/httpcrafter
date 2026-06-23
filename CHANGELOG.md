# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [ [0.4.0](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.4.0) ] - 2025-07-02

### Added

- `RouterFacade` class to provide a simplified interface for route registration, pattern mapping and route path generation

### Fixed

- Pattern mapping fix

## [ [0.3.0](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.3.0) ] - 2025-07-01

### Changed

- `Router::generatePath()` updated to generate paths with dynamic values, allowing URL generation with parameters or dynamic segments without manual string concatenation

## [ [0.2.2](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.2.2) ] - 2025-01-14

### Changed

- Set `?` to allow nullable for `name` parameter in `Router->addRoute()`

## [ [0.2.1](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.2.1) ] - 2025-01-02

### Changed

- Set PHP minimum requirement to PHP 8.2 and greater

## [ [0.2.0](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.2.0) ] - 2024-12-09

### Added

- Redirection method to `Response` class (`redirect()`) supporting HTTP 301 and 302 status codes

## [ [0.1.3](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.1.3) ] - 2024-10-10

### Added

- Interface `Authenticator` to standardize authentication mechanisms
- Interface `Authorization` to standardize authorization mechanisms

### Changed

- Moved `Content-Length` calculation logic to a private `updateContentLength()` method in `Response` class
- Updated `send()` method to call `updateContentLength()` before sending headers

## [ [0.1.2](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.1.2) ] - 2024-10-08

### Fixed

- `Content-Length` header calculation in `Response` class — previous implementation incorrectly used `strlen()` on the length value instead of the actual body content

## [ [0.1.1](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.1.1) ] - 2024-10-07

### Fixed

- `Content-Length` calculation based on content type

## [ [0.1.0](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.1.0) ] - 2024-10-07

### Added

- `Router` class (from archived [oladesoftware/router](https://github.com/oladesoftware/router)) at `src/Router/Router.php`
- `RequestInterface` and `ResponseInterface` interfaces
- Tests for `Router` at `tests/Router/RouterTest.php`

### Changed

- `Request.php` and `Response.php` reorganized into `src/Http/` directory
- Test files reorganized : `tests/RequestTest.php` → `tests/Http/RequestTest.php`, `tests/ResponseTest.php` → `tests/Http/ResponseTest.php`

## [ [0.0.3](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.0.3) ] - 2024-06-20

### Fixed

- `content-type` not set correctly in `Response` class

### Changed

- Set PHP version requirement

## [ [0.0.2](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.0.2) ] - 2024-06-05

### Fixed

- Sanitize user data in `getPost()` method when a key is provided
- Check if array key exists in `$_POST` in `getPost()` method

## [ [0.0.1](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.0.1) ] - 2024-05-26

### Fixed

- Return value type for `getServer()` function in `Request` class

## [ [0.0.0](https://github.com/oladesoftware/httpcrafter/releases/tag/v0.0.0) ] - 2024-05-23

### Added

- Request class to manage ` $_SERVER ` , ` $_GET ` and ` $_POST `
- Response class to build and send HTML or JSON
- Basic usage with examples
# HTTPCrafter roadmap

## Roadmap

### 1. Refactoring

- [x] Rewrite Router and rely on class Route
- [x] Remove ` RequestInterface `, ` ResponseInterface `, ` Authorization `, ` Authenticator `

### 2. Foundation

- [ ] dot env file reader via ` parse_ini_file() `
- [ ] Lightweight dependancy injection container

### 3. Core feature

- [ ] Middleware pipeline (auth, CSRF, rate limiting, logging)
- [ ] Error management 
  - [ ] Http exception (404 Not found, 402, 403, 405 Method not allowed, 500 Server error)
  - [ ] Global handler

### 4. Helpers

- [ ] Session wrapper
- [ ] Cookie wrapper

### 5. Documentation & examples

- [ ] Example project (Router alone, full stack)
- [ ] CHANGELOG.md

## Backlog

- [Backlog](./BACKLOG.md)

## Folder architecture

```txt

httpcrafter/
| --- docs/ <-- Technical docs of each class
|     | --- Http/
|     | --- Middleware/
|     | --- Router/
|     | --- Container/
|     | --- Environment/
| --- src/
|     | --- Http/
|     | --- Middleware/
|     | --- Router/
|     | --- Container/
|     | --- Environment/
| --- examples/ <-- example in project alone and together
| --- tests/
|     | --- Http/
|     | --- Middleware/
|     | --- Router/
|     | --- Container/
|     | --- Environment/
| --- README.md <-- Description and quick start
| --- TODO.md <-- This file to serve as roadmad

```
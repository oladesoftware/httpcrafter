# HTTPCrafter

PHP toolkit that gives you tools, not rules.

## Philosophy

Architecture is part of development, not a step to be bypassed. HTTPCrafter gives you tools, not rules.

Every component is independent, composable, and usable on its own. Each one works out of the box through sensible default behaviors, but none of those behaviors are privileged or imposed. They can be replaced, extended, or completely redefined.

Start with a Quick Start. Grow into your own architecture whenever you feel the need.

Because the best architectures aren't the ones imposed on you, they are the ones you build.

---

## Installation

```shell
composer require oladesoftware/httpcrafter
```

Each component can also be dropped directly into a codebase without Composer.

---

## Components

HTTPCrafter is a collection of independent components. Each one lives under its own namespace, its own `src/` folder, its own docs, and its own tests, use only what you need.

- [`Router`](src/Router)
  - Configurable, singleton-capable HTTP router: path placeholders, pluggable target resolution, grouping, and named routes.
  - docs: [router](docs/router/router.md)
- [`Http`](src/Http)
  - [`Request`](src/Http/Request.php)
    - docs: [http/request.md](docs/http/request.md)
  - [`Response`](src/Http/Response.php)
    - docs: [http/response.md](docs/http/request.md)

Each component's documentation is organized the same way:

- **`installation.md`**: how to install the component, with or without Composer.
- **`<component>.md`**: overview, quick start, usage, and full API reference.
- Any supporting class (e.g. `Route` for `Router`) has its own dedicated `.md` file.

---

## Roadmap

- Ongoing and planned work is tracked in [`BACKLOG.md`](BACKLOG.md) and [`TODO.md`](TODO.md). 
- Released changes are listed in [`CHANGELOG.md`](CHANGELOG.md).

---

## License

This project is licensed under the terms of the LICENSE file included in the repository.
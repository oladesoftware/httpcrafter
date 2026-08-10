# Installation

## Via Composer

`Router` is published on [Packagist](https://packagist.org/packages/oladesoftware/httpcrafter) as the `oladesoftware/httpcrafter` package.

```shell
composer require oladesoftware/httpcrafter
```

PSR-4 autoloading takes care of the rest, classes are available under their respective `Oladesoftware\Httpcrafter\<Component>` namespaces (e.g. `Oladesoftware\Httpcrafter\Router` for the Router component).

```php
use Oladesoftware\Httpcrafter\Router\Router;

$router = new Router();
```

### Without Composer (direct integration)

If your project doesn't rely on Composer, each release also ships the raw source files for the entire toolkit. Download the files from the release, and drop them into your codebase. Then load them, either through your own autoloader or direct `require` calls for whatever components you plan to use.

For example, to use only the Router component:

```php
require_once __DIR__ . '/src/Routing/Route.php';
require_once __DIR__ . '/src/Routing/Router.php';

use Oladesoftware\Httpcrafter\Router\Router;

$router = new Router();
```

> **Note:** every class across the toolkit follows the `Oladesoftware\Httpcrafter\<Component>` namespace convention (e.g. `Router`, and other component namespaces). Keep this structure as-is or adapt it consistently, so classes within and across components keep referencing each other correctly.

### Requirements

`Router` relies on property hooks, available starting with **PHP 8.4**.
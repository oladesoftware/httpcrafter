# Request

The Request class is a part of the Oladesoftware\Httpcrafter namespace and is designed to encapsulate HTTP request data. This includes server variables, query parameters, and POST data. The class provides methods to easily retrieve this data.

## Initializing a request

To initialize the Request class, you can either pass custom arrays for server, query, and post data or let it use the default values from $_SERVER, $_GET, and $_POST superglobals.

```php
use Oladesoftware\Httpcrafter\Http\Request;

// Using default superglobals
$request = new Request();

// Using custom arrays
$customServer = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/home'];
$customQuery = ['search' => 'query'];
$customPost = ['name' => 'John Doe'];

$request = new Request($customServer, $customQuery, $customPost);
```
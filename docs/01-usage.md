# Usage

- [Request](#request)
- [Response](#response)

## Request

The Request class is a part of the Oladesoftware\Httpcrafter namespace and is designed to encapsulate HTTP request data. This includes server variables, query parameters, and POST data. The class provides methods to easily retrieve this data.

### Initializing a request

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
## Response

The Response class is part of the Oladesoftware\Httpcrafter namespace and is designed to encapsulate HTTP response data, including status codes, headers, content type, and body content. The class provides methods to easily set and retrieve these values and to send the response to the client.

### Initializing response

To initialize the Response class, you can pass the body content, content type, status code, and an optional array of headers. Default values are provided if not specified.

```php
use Oladesoftware\Httpcrafter\Http\Response;

// Using default values
$response = new Response();

$response
    ->setBody("<h1>Hello, World!</h1>")
    ->setCode(200)
    ->setContentType(Response::HTML)
    ->setHeaders("Custom-Header", "CustomValue")
;

// Send response
$response->send();
```

### Constants

The Response class defines two constants for common content types:

- `Response::HTML` - "text/html"
- `Response::JSON` - "application/json"
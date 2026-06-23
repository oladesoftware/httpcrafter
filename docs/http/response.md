# Response

The Response class is part of the Oladesoftware\Httpcrafter namespace and is designed to encapsulate HTTP response data, including status codes, headers, content type, and body content. The class provides methods to easily set and retrieve these values and to send the response to the client.

## Initializing response

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

## Constants

The Response class defines two constants for common content types:

- `Response::HTML` - "text/html"
- `Response::JSON` - "application/json"
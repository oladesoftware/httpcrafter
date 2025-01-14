<?php

namespace Oladesoftware\Httpcrafter\Http;

use InvalidArgumentException;

/**
 * Class Response
 *
 * A class to encapsulate HTTP response data, including status codes, headers, content type, and body content.
 *
 * @package Oladesoftware\Httpcrafter
 */
class Response implements ResponseInterface
{
    /**
     * The HTML content type.
     */
    const string HTML = "text/html";

    /**
     * The JSON content type.
     */
    const string JSON = "application/json";

    private const array REDIRECTCODES = [301, 302, 303, 307, 308];

    /**
     * @var int $code The HTTP status code for the response.
     */
    private int $code;

    /**
     * @var string $contentType The content type of the response.
     */
    private string $contentType;

    /**
     * @var array $headers The headers to be sent with the response.
     */
    private array $headers = [];

    /**
     * @var mixed $body The body content of the response.
     */
    private mixed $body;

    /**
     * Response constructor.
     *
     * @param mixed $body The body content of the response. Defaults to an empty string.
     * @param string $contentType The content type of the response. Defaults to Response::HTML.
     * @param int $code The HTTP status code of the response. Defaults to 200.
     * @param array|null $headers An optional array of additional headers. Defaults to null.
     */
    public function __construct(mixed $body = "", string $contentType = self::HTML, int $code = 200, ?array $headers = null)
    {
        $this->code = $code;
        $this->contentType = $contentType;
        $this->body = $body;
        $this->headers["Content-Type"] = "$contentType ; charset=utf-8";
        if (!is_null($headers))
        {
            $this->headers = array_merge($this->headers, $headers);
        }
    }

    /**
     * Sets the HTTP status code for the response.
     *
     * @param int $code The HTTP status code.
     * @return Response The current instance for method chaining.
     */
    public function setCode(int $code): Response
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Retrieves the current HTTP status code.
     *
     * @return int The HTTP status code.
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Sets the content type for the response.
     *
     * @param string $contentType The content type.
     * @return Response The current instance for method chaining.
     */
    public function setContentType(string $contentType): Response
    {
        $this->contentType = $contentType;
        $this->headers["Content-Type"] = "$contentType ; charset=utf-8";
        return $this;
    }

    /**
     * Retrieves the current content type.
     *
     * @return string The content type.
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Sets a header for the response.
     *
     * @param string $name The header name.
     * @param string $value The header value.
     * @return Response The current instance for method chaining.
     */
    public function setHeaders(string $name, string $value): Response
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Retrieves the headers. If a name is provided, returns the value for that header.
     *
     * @param string|null $name An optional specific header name to retrieve.
     * @return string|array The headers array or the value for the specified header.
     */
    public function getHeaders(?string $name): string|array
    {
        return $this->headers[$name] ?? $this->headers;
    }

    /**
     * Sets the body content for the response.
     *
     * @param mixed $body The body content.
     * @return Response The current instance for method chaining.
     */
    public function setBody(mixed $body): Response
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Retrieves the current body content.
     *
     * @return mixed The body content.
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Sends the response to the client, including headers and body content.
     *
     * @return string The body content to be sent.
     */
    public function send(): string
    {
        http_response_code($this->code);
        $this->updateContentLength();
        $this->sendHeader();
        return match ($this->contentType) {
            self::JSON => json_encode($this->body),
            self::HTML => $this->body,
        };
    }

    public function redirect(string $url, int $code = 302): void
    {
        if (!in_array($code, self::REDIRECTCODES)) {
            throw new InvalidArgumentException('Invalid HTTP status code for redirection.');
        }

        header("Location: " . $url, true, $code);
        exit();
    }

    private function updateContentLength(): void
    {
        $length = match(gettype($this->body)){
            "string" => strlen($this->body),
            default => strlen(json_encode($this->body))
        };
        $this->headers["Content-Length"] = $length;
    }

    /**
     * Sends the headers to the client. This method is called internally by the send method.
     *
     * @return void
     */
    private function sendHeader(): void
    {
        if (!empty($this->headers))
        {
            foreach ($this->headers as $name => $value)
            {
                header("$name: $value");
            }
        }
    }
}
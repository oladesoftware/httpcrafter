<?php

namespace Oladesoftware\Httpcrafter;

/**
 * Class Request
 *
 * A class to encapsulate HTTP request data, including server variables, query parameters, and POST data.
 *
 * @package Oladesoftware\Httpcrafter
 */
class Request
{
    /**
     * @var array $server The server data.
     */
    private array $server;
    /**
     * @var array $query The query data.
     */
    private array $query;

    /**
     * @var array $post The POST data.
     */
    private array $post;

    /**
     * Request constructor.
     *
     * @param array|null $server An optional array of server data. Defaults to $_SERVER.
     * @param array|null $query An optional array of query data. Defaults to $_GET.
     * @param array|null $post An optional array of POST data. Defaults to $_POST.
     */
    public function __construct(?array $server = null, ?array $query = null, ?array $post = null)
    {
        $this->setServer($server);
        $this->setQuery($query);
        $this->setPost($post);
    }

    /**
     * Sets the server data.
     *
     * @param array|null $server An optional array of server data. Defaults to $_SERVER.
     * @return Request The current instance for method chaining.
     */
    public function setServer(?array $server = null): Request
    {
        $this->server = $server ?? $_SERVER;
        return $this;
    }

    /**
     * Retrieves the server data. If a key is provided, returns the value for that key.
     *
     * @param string|null $key An optional specific key to retrieve from the server data.
     * @return array|string The server data or the value for the specified key.
     */
    public function getServer(?string $key = null): string|array
    {
        return $this->server[$key] ?? $this->server;
    }

    /**
     * Retrieves the request path from the server data.
     *
     * @return string The request path.
     */
    public function getPath(): string
    {
        return parse_url($this->server["REQUEST_URI"], PHP_URL_PATH);
    }

    /**
     * Retrieves the HTTP request method from the server data.
     *
     * @return string The HTTP request method.
     */
    public function getMethod(): string
    {
        return strtoupper($this->server["REQUEST_METHOD"]);
    }

    /**
     * Sets the query data.
     *
     * @param array|null $query An optional array of query data. Defaults to $_GET.
     * @return Request The current instance for method chaining.
     */
    public function setQuery(?array $query = null): Request
    {
        $this->query = $query ?? $_POST;
        return $this;
    }

    /**
     * Retrieves the query data. If a key is provided, returns the value for that key.
     *
     * @param string|null $name An optional specific key to retrieve from the query data.
     * @return string|array The query data or the value for the specified key.
     */
    public function getQuery(?string $name = null): string|array
    {
        return $this->query[$name] ?? $this->query;
    }

    /**
     * Sets the POST data.
     *
     * @param array|null $post An optional array of POST data. Defaults to $_POST.
     * @return Request The current instance for method chaining.
     */
    public function setPost(?array $post = null): Request
    {
        $this->post = $post ?? $_POST;
        return $this;
    }

    /**
     * Retrieves the POST data. If a key is provided, returns the value for that key.
     *
     * @param string|null $name An optional specific key to retrieve from the POST data.
     * @return string|array The POST data or the value for the specified key.
     */
    public function getPost(?string $name = null): string|array
    {
        return (is_null($name)) ? $this->post : htmlentities($this->post[$name]);
    }
}
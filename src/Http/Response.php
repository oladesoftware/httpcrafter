<?php

namespace Oladesoftware\Httpcrafter\Http;

use RuntimeException;

class Response {
    public bool $strict_mode = true;
    public array $supported_redirection_codes = [300, 301, 302, 303, 304, 305, 306, 307, 308] {
        get {
            return $this->supported_redirection_codes;
        }

        set (array $supported_redirection_codes) {
            if ($this->strict_mode) {
                foreach ($supported_redirection_codes as $code) {
                    if (!is_int($code) || $code < 300 || $code > 399) {
                        throw new RuntimeException('Supported redirection code must be an integer between "300" and "309".');
                    }
                }
            }

            $this->supported_redirection_codes = $supported_redirection_codes;
        }
    }
    public const string HTML = "text/html";
    public const string JSON = "application/json";
    public array $supported_types = [Response::HTML, Response::JSON] {
        get {
            return $this->supported_types;
        }

        set (array $supported_types) {
            if ($this->strict_mode) {
                foreach ($supported_types as $type) {
                    if (!is_string($type)) {
                        throw new RuntimeException("Value '$type' is not a string");
                    }
                }
            }

            $this->supported_types = $supported_types;
        }
    }

    public string $content_type = '' {
        get {
            return $this->content_type;
        }

        set (string $content_type) {
            if ($this->strict_mode && !in_array($content_type, $this->supported_types)) {
                throw new RuntimeException("Content type: '$content_type' is not a supported type");
            }

            $this->content_type = $content_type;
        }
    }

    public int $code = 200 {
        get {
            return $this->code;
        }

        set (int $code) {
            $this->code = $code;
        }
    }

    public array $headers = [] {
        get {
            return $this->headers;
        }

        set (array $headers) {
            $this->headers = $headers;
        }
    }

    public mixed $body = '' {
        get {
            return $this->body;
        }

        set (mixed $body) {
            $this->body = $body;
        }
    }

    public function __construct(mixed $body = "", string $content_type = self::HTML, int $code = 200, array $headers = [])
    {
        $this->code = $code;
        $this->content_type = $content_type;
        $this->body = $body;

        $this->addHeader(
            'Content-Type',
            "$content_type ; charset=utf-8"
        );

        if (!empty($headers)) {
            foreach ($headers as $name => $value) {
                $this->addHeader($name, $value);
            }
        }
    }

    public function addSupportedType(string $type): self
    {
        $types = [$type, ...$this->supported_types];
        $this->supported_types = $types;
        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $headers = $this->headers;
        $headers[$name] = $value;
        $this->headers = $headers;
        return $this;
    }

    public function editHeader(string $name, string $value): self
    {
        $headers = $this->headers;
        if (array_key_exists($name, $headers)) {
            $headers[$name] = $value;
            $this->headers = $headers;
        }
        return $this;
    }

    protected function sendHeader(): void
    {
        if (!empty($this->headers))
        {
            foreach ($this->headers as $name => $value)
            {
                header("$name: $value");
            }
        }
    }

    public function send(): string
    {
        http_response_code($this->code);
        $this->updateContentLength();
        $this->sendHeader();

        if (!in_array($this->content_type, $this->supported_types)) {
            throw new RuntimeException(
                "Content type: '$this->content_type' is not a supported type"
            );
        }

        return is_string($this->body)
            ? $this->body
            : json_encode($this->body);
    }

    public function redirect(string $url, int $code = 302): void
    {
        if ($this->strict_mode && !in_array($code, $this->supported_redirection_codes)) {
            throw new RuntimeException('Invalid HTTP status code for redirection.');
        }

        header("Location: " . $url, true, $code);
        exit();
    }

    protected function updateContentLength(): void
    {
        $length = match(gettype($this->body)){
            "string" => strlen($this->body),
            default => strlen(json_encode($this->body))
        };
        $this->editHeader('Content-Length', $length);
    }

    public function enableStrictMode(): self
    {
        $this->strict_mode = true;
        return $this;
    }

    public function disableStrictMode(): self
    {
        $this->strict_mode = false;
        return $this;
    }
}
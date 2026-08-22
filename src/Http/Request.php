<?php

namespace Oladesoftware\Httpcrafter\Http;

class Request {
    public static bool $autoStartSession = false;
    protected static ?self $_instance = null;

    public array $server = [] {
        get {
            return $this->server;
        }

        set (array $server) {
            $this->server = $server;
        }
    }

    public array $query = [] {
        get {
            return $this->query;
        }

        set (array $query) {
            $this->query = $query;
        }
    }
    public array $post = [] {
        get {
            return $this->post;
        }

        set (array $post) {
            $this->post = $post;
        }
    }
    public array $cookies = [] {
        get {
            return $this->cookies;
        }

        set (array $cookies) {
            $this->cookies = $cookies;
        }
    }
    public array $files = [] {
        get {
            return $this->files;
        }

        set (array $files) {
            $this->files = $files;
        }
    }
    public array $session = [] {
        get {
            return $this->session;
        }

        set (array $session) {
           $this->session = $session;
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

    public function __construct(bool $initDefault = true, bool $initSingleton = false)
    {
        if ($initDefault) {
            $this
                ->addServer()
                ->addQuery()
                ->addPost()
                ->addCookies()
                ->addFiles()
                ->addSession()
                ->addHeaders()
            ;
        } else {
            $this
                ->clearServer()
                ->clearQuery()
                ->clearPost()
                ->clearCookies()
                ->clearFiles()
                ->clearSession()
                ->clearHeaders()
            ;
        }

        if ($initSingleton) {
            self::$_instance = $this;
        }
    }

    public function addServer(): self
    {
        $this->server = $_SERVER;
        return $this;
    }

    public function addQuery(): self
    {
        $this->query = $_GET;
        return $this;
    }

    public function addPost(): self
    {
        $this->post = $_POST;
        return $this;
    }

    public function addCookies(): self
    {
        $this->cookies = $_COOKIE;
        return $this;
    }

    public function addFiles(): self
    {
        $this->files = $_FILES;
        return $this;
    }

    public function addSession(): self
    {
        if (self::$autoStartSession && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->session = $_SESSION;
        }

        return $this;
    }

    public function addHeaders(): self
    {
        $this->headers = (function_exists('getallheaders')) ? getallheaders() : [];
        return $this;
    }

    public function clearServer(): self
    {
        $this->server = [];
        return $this;
    }

    public function clearQuery(): self
    {
        $this->query = [];
        return $this;
    }

    public function clearPost(): self
    {
        $this->post = [];
        return $this;
    }

    public function clearCookies(): self
    {
        $this->cookies = [];
        return $this;
    }

    public function clearFiles(): self
    {
        $this->files = [];
        return $this;
    }

    public function clearSession(): self
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->session = [];
        }

        return $this;
    }

    public function clearHeaders(): self
    {
        $this->headers = [];
        return $this;
    }

    public static function getInstance(bool $initDefault = true): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self(initDefault: $initDefault, initSingleton: true);
        }
        return self::$_instance;
    }

    public static function removeInstance(): void
    {
        self::$_instance = null;
    }
}
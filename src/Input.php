<?php

declare(strict_types=1);

namespace orange\framework;

use orange\framework\base\Singleton;
use orange\framework\interfaces\InputInterface;
use orange\framework\traits\ConfigurationTrait;

class Input extends Singleton implements InputInterface
{
    /** include ConfigurationTrait methods */
    use ConfigurationTrait;

    protected array $query = [];
    protected array $request = [];
    protected array $server = [];
    protected array $cookies = [];
    protected array $files = [];
    protected array $headers = [];
    protected string $input;

    /**
     * Protected constructor to enforce the singleton pattern.
     *
     * @param array $config Configuration data.
     */
    protected function __construct(array $config)
    {
        logMsg('INFO', __METHOD__);

        $this->config = $this->mergeConfigWith($config, false);

        $this->query = $this->config['get'] ?? [];
        $this->request = $this->config['post'] ?? [];
        $this->cookies = $this->config['cookies'] ?? [];
        $this->files = $this->config['files'] ?? [];
        $this->input = $this->config['input'] ?? '';

        $this->setServer($this->config['server'] ?? []);
        $this->convertContent();
    }

    public function request(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$this->normalizeServerKey($key)] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$this->normalizeServerKey($key)] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key, mixed $default = null): mixed
    {
        return $this->files[$key] ?? $default;
    }

    public function getUrl(int $component = -1): int|string|array|null|false
    {
        logMsg('INFO', __METHOD__ . ' ' . $component);

        return parse_url($this->server('request_uri', ''), $component);
    }

    public function requestUri(): string
    {
        $uri = parse_url($this->server('request_uri', ''), self::PATH);
        logMsg('INFO', __METHOD__ . ' ' . $uri);

        return $uri;
    }

    public function uriSegment(int $segmentNumber): string
    {
        logMsg('INFO', __METHOD__ . ' ' . $segmentNumber);

        $segs = explode('/', ltrim($this->requestUri(), '/'));

        return $segs[$segmentNumber - 1] ?? '';
    }

    public function contentType(bool $asLowercase = true): string
    {
        $type = $this->server('content type', '');

        logMsg('DEBUG', __METHOD__ . $type);

        return $asLowercase ? strtolower($type) : strtoupper($type);
    }

    public function requestMethod(bool $asLowercase = true): string
    {
        /**
         * You can override the http method by setting on of the following in your http request
         */
        $null = chr(0);

        if ($this->server('http_x_http_method_override', $null) !== $null) {
            $method = $this->server('http_x_http_method_override', '');
        } elseif ($this->query('_method', $null) !== $null) {
            $method = $this->query('_method');
        } elseif ($this->request('_method', $null) !== $null) {
            $method = $this->request('_method');
        } elseif ($this->server('request_method', $null) !== $null) {
            $method = $this->server('request_method', '');
        } else {
            // I guess it's a CLI request?
            $method = 'cli';
        }

        logMsg('DEBUG', __METHOD__ . $method);

        return $asLowercase ? strtolower($method) : strtoupper($method);
    }

    public function requestType(bool $asLowercase = true): string
    {
        // default to html unless we find something else
        $requestType = 'html';

        if (($this->server('http_x_requested_with', '') == 'xmlhttprequest') || (strpos($this->server('http_accept', ''), 'application/json') !== false)) {
            $requestType = 'ajax';
        } elseif (strtolower($this->config['php_sapi'] ?? '') === 'cli' || ($this->config['stdin'] ?? false) === true) {
            $requestType = 'cli';
        }

        logMsg('DEBUG', __METHOD__ . $requestType);

        return $asLowercase ? strtolower($requestType) : strtoupper($requestType);
    }

    public function isAjaxRequest(): bool
    {
        return $this->requestType() == 'AJAX';
    }

    public function isCliRequest(): bool
    {
        return $this->requestType() == 'CLI';
    }

    public function isHttpsRequest(bool $asString = false): bool|string
    {
        logMsg('INFO', __METHOD__ . ' ' . $asString);

        // set the default to not https unless we find something else
        $isHttps = false;

        if ($this->server('https', '') == 'on' || $this->server('http_x_forwarded_proto', '') === 'https' || $this->server('http_front_end_https', '') !== '') {
            $isHttps = true;
        }

        logMsg('INFO', __METHOD__ . ' ' . ($isHttps ? 'true' : 'false'));

        $return = $isHttps;

        if ($asString) {
            $return = $isHttps ? 'https' : 'http';
        }

        return $return;
    }

    protected function setServer(array $server): void
    {
        foreach ($server as $key => $value) {
            $normalizedKey = $this->normalizeServerKey($key);

            $this->server[$normalizedKey] = $value;

            // CONTENT_* are not prefixed with HTTP_
            if (strpos($key, 'HTTP_') === 0 || in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'])) {
                $this->headers[$normalizedKey] = $value;
            }
        }
    }

    protected function normalizeServerKey(string $key): string
    {
        return str_replace('_', ' ', str_replace(['http_', 'server_'], '', strtolower($key)));
    }

    protected function convertContent(): void
    {
        $contentType = $this->contentType();
        $requestMethod = $this->requestMethod();

        if (strpos($contentType, 'application/x-www-form-urlencoded') === 0 && in_array($requestMethod, ['put', 'delete'])) {
            parse_str($this->input, $data);
            $this->request = $data;
        } elseif (strpos($contentType, 'application/json') === 0 && in_array($requestMethod, ['post', 'put', 'delete'])) {
            $data = json_decode($this->input, true);
            $this->request = $data;
        }
    }
}

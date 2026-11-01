<?php

namespace Core\Routing\Objects;

use Core\Contracts\Arrayable;
use Core\Routing\Enums\Method;

class Request implements Arrayable
{
    /**
     * Hostname (domain) of the request.
     *
     * @var string
     */
    public string $host;

    /**
     * Everything after the hostname, with query parameters stripped.
     *
     * @var string
     */
    public string $requestUri;

    /**
     * Which HTTP verb this request used
     *
     * @var Method
     */
    public Method $method;

    /**
     * The request's user-agent string.
     *
     * @var string|null
     */
    public ?string $userAgent;

    /**
     * List of accepted response types, such as `application/json` in order of most to least preferred.
     *
     * @var array<string>
     */
    public array $accepts;

    /**
     * HTTP request headers.
     *
     * @var array<string, string>
     */
    public array $headers;

    /**
     * HTTP Request parameters.
     *
     * @var array<string,string>
     */
    public array $params;

    /**
     * The Request's cookies. todo: finish
     *
     * @var array
     */
    public array $cookies;

    public function __construct()
    {
        $this->host = $_SERVER['HTTP_HOST'];
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->method = Method::tryFrom(strtolower($_SERVER['REQUEST_METHOD'])) ?? Method::UNSUPPORTED;
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $this->accepts = explode(',', $_SERVER['HTTP_ACCEPT'] ?? '') ?: null;
        $this->headers = $this->getHeaders();
        $this->params = $_GET;
    }

    protected function getHeaders(): array
    {
        $headers = [];

        foreach($_SERVER as $key => $value) {
            if(! str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $headers[$this->normalizeHeaderKey($key, stripHtml: true)] = $value;
        }

        // These aren't prefixed with HTTP_
        foreach (['CONTENT_TYPE', 'CONTENT_LENGTH'] as $key) {
            if (isset($_SERVER[$key])) {
                $headers[$this->normalizeHeaderKey($key, stripHtml: false)] = $_SERVER[$key];
            }
        }

        return $headers;
    }

    protected function normalizeHeaderKey(string $key, bool $stripHtml): string
    {
        $offset = $stripHtml ? 5 : 0;
        return strtolower(str_replace('_', '-', substr($key, $offset)));
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'requestUri' => $this->requestUri,
            'method' => $this->method->value,
            'userAgent' => $this->userAgent,
            'accepts' => $this->accepts,
            'headers' => $this->headers,
            'params' => $this->params,
            //'cookies' => $this->cookies
        ];
    }
}